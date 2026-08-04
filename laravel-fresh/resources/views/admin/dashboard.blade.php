@extends('layouts.admin')
@section('title', 'الرئيسية')

@section('content')
    <h3 class="mb-4">نظرة عامة</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-stat p-3">
                <div class="text-muted small">عدد الملاعب</div>
                <div class="fs-3 fw-bold">{{ $stats['courts_count'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat p-3">
                <div class="text-muted small">الملاعب النشطة</div>
                <div class="fs-3 fw-bold">{{ $stats['active_courts_count'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat p-3">
                <div class="text-muted small">حجوزات اليوم</div>
                <div class="fs-3 fw-bold">{{ $stats['bookings_today'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat p-3">
                <div class="text-muted small">إيرادات هذا الشهر (المدفوعة)</div>
                <div class="fs-3 fw-bold">{{ number_format($stats['revenue_this_month'], 3) }} ر.ع</div>
            </div>
        </div>
    </div>

    <div class="card card-stat p-3">
        <h5 class="mb-3">آخر الحجوزات</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>الرقم المرجعي</th>
                    <th>الهاتف</th>
                    <th>عدد الساعات</th>
                    <th>الإجمالي</th>
                    <th>طريقة الدفع</th>
                    <th>حالة الدفع</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentBookings as $b)
                    <tr>
                        <td>{{ $b->booking_reference }}</td>
                        <td>{{ $b->customer_phone }}</td>
                        <td>{{ $b->slots->count() }}</td>
                        <td>{{ number_format($b->total_amount, 3) }}</td>
                        <td>{{ $b->payment_method === 'thawani' ? 'دفع إلكتروني' : 'عند الوصول' }}</td>
                        <td>
                            <span class="badge bg-{{ $b->payment_status === 'paid' ? 'success' : ($b->payment_status === 'failed' ? 'danger' : 'secondary') }}">
                                {{ ['paid' => 'مدفوع', 'pending' => 'قيد الانتظار', 'failed' => 'فشل'][$b->payment_status] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">لا توجد حجوزات بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
