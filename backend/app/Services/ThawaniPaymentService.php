<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * تكامل بسيط ونظيف مع بوابة الدفع الإلكتروني "ثواني" (بيئة الاختبار Sandbox / UAT).
 * التوثيق: https://thawani-technologies.stoplight.io/docs/thawani-ecommerce-api
 */
class ThawaniPaymentService
{
    private string $baseUrl;
    private string $secretKey;
    private string $publishableKey;

    public function __construct()
    {
        // مفاتيح بيئة الاختبار (UAT) المنشورة في توثيق ثواني للاستخدام أثناء التطوير فقط.
        // في بيئة الإنتاج ضع مفاتيحك الفعلية في ملف .env.
        $this->baseUrl = config('services.thawani.base_url', 'https://uatcheckout.thawani.om/api/v1');
        $this->secretKey = config('services.thawani.secret_key', 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et');
        $this->publishableKey = config('services.thawani.publishable_key', 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy');
    }

    /**
     * إنشاء جلسة دفع لحجز معين، وإرجاع رابط صفحة الدفع المستضافة لدى ثواني.
     */
    public function createCheckoutSession(Booking $booking, string $successUrl, string $cancelUrl): string
    {
        // ثواني تتعامل بأصغر وحدة عملة (بيسة)، وريال عماني واحد = 1000 بيسة
        $unitAmountInBaisa = (int) round(((float) $booking->total_amount) * 1000);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'thawani-api-key' => $this->secretKey,
        ])->post($this->baseUrl.'/checkout/session', [
            'client_reference_id' => $booking->booking_reference,
            'mode' => 'payment',
            'products' => [[
                'name' => 'حجز ملعب بادل - '.$booking->booking_reference,
                'quantity' => 1,
                'unit_amount' => $unitAmountInBaisa,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'booking_id' => $booking->id,
                'customer_phone' => $booking->customer_phone,
            ],
        ]);

        if (! $response->successful() || ! data_get($response->json(), 'success')) {
            throw new RuntimeException('تعذر إنشاء جلسة الدفع عبر ثواني: '.$response->body());
        }

        $sessionId = data_get($response->json(), 'data.session_id');

        $booking->update(['thawani_session_id' => $sessionId]);

        return "https://uatcheckout.thawani.om/pay/{$sessionId}?key={$this->publishableKey}";
    }

    /**
     * التحقق من حالة جلسة الدفع بعد عودة العميل من صفحة ثواني.
     */
    public function checkSessionStatus(string $sessionId): string
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey,
        ])->get($this->baseUrl."/checkout/session/{$sessionId}");

        if (! $response->successful()) {
            throw new RuntimeException('تعذر التحقق من حالة الدفع.');
        }

        // القيم المتوقعة من ثواني: unpaid | paid | expired
        return data_get($response->json(), 'data.payment_status', 'unpaid');
    }
}
