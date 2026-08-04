<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_phone' => ['required', 'string', 'min:8', 'max:20'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'payment_method' => ['required', 'in:pay_on_arrival,thawani'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'slots.*.start_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.required' => 'رقم الهاتف إجباري.',
            'slots.required' => 'الرجاء اختيار وقت واحد على الأقل.',
            'slots.*.date.after_or_equal' => 'لا يمكن الحجز في تاريخ ماضٍ.',
        ];
    }
}
