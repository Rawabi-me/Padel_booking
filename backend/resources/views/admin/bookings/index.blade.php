@extends('layouts.admin')
@section('title', 'الحجوزات')

@section('content')
    <h3 class="mb-4">جميع الحجوزات</h3>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-2">
            <select name="court_id" class="form-select">
                <option value="">كل الملاعب</option>
                @foreach ($courts as $court)
                    <option value="{{ $court->id }}" {{ request('court_id') == $court->id ? 'selected' : '' }}>{{ $court->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-md-2">
            <select name="payment_method" class="form-select">
                <option value="">كل طرق الدفع</option>
                <option value="pay_on_arrival" {{ request('payment_method') == 'pay_on_arrival' ? 'selected' : '' }}>عند الوصول</option>
                <option value="thawani" {{ request('payment_method') == 'thawani' ? 'selected' : '' }}>دفع إلكتروني</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="payment_status" class="form-select">
                <option value="">كل حالات الدفع</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>فشل</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="phone" class="form-control" placeholder="رقم الهاتف" value="{{ request('phone') }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100">فلترة</button>
        </div>
    </form>

    <div class="card card-stat p-3">
        <table class="table">
            <thead>
                <tr>
                    <th>الرقم المرجعي</th>
                    <th>الهاتف</th>
                    <th>الملاعب/الأوقات</th>
                    <th>الإجمالي</th>
                    <th>طريقة الدفع</th>
                    <th>حالة الدفع</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($bookings as $b)
                <tr>
                    <td>{{ $b->booking_reference }}</td>
                    <td>{{ $b->customer_phone }}</td>
                    <td class="small">
                        @foreach ($b->slots as $slot)
                            {{ $slot->court->name }} — {{ $slot->date->format('Y-m-d') }} {{ substr($slot->start_time,0,5) }}<br>
                        @endforeach
                    </td>
                    <td>{{ number_format($b->total_amount, 3) }}</td>
                    <td>{{ $b->payment_method === 'thawani' ? 'دفع إلكتروني' : 'عند الوصول' }}</td>
                    <td>
                        <span class="badge bg-{{ $b->payment_status === 'paid' ? 'success' : ($b->payment_status === 'failed' ? 'danger' : 'secondary') }}">
                            {{ ['paid' => 'مدفوع', 'pending' => 'قيد الانتظار', 'failed' => 'فشل'][$b->payment_status] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">لا توجد نتائج</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $bookings->links() }}
    </div>
@endsection
