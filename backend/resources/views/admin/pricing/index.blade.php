@extends('layouts.admin')
@section('title', 'الأسعار والعروض')

@section('content')
    <h3 class="mb-4">الأسعار والعروض حسب عدد الساعات</h3>
    <p class="text-muted">مثال: ساعة واحدة = 10 ريال، ساعتان فأكثر = 8 ريال لكل ساعة. يتم اختيار السعر تلقائياً حسب عدد ساعات الحجز في نفس اليوم.</p>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-stat p-4">
                <h5 class="mb-3">إضافة عرض سعر</h5>
                <form method="POST" action="{{ route('admin.pricing.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">بدءاً من (عدد الساعات)</label>
                        <input type="number" min="1" name="min_hours" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السعر لكل ساعة (ر.ع)</label>
                        <input type="number" step="0.001" min="0" name="price_per_hour" class="form-control" required>
                    </div>
                    <button class="btn btn-success w-100">إضافة</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-stat p-3">
                <table class="table">
                    <thead><tr><th>بدءاً من</th><th>السعر/ساعة</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($tiers as $tier)
                        @php $formId = 'tier-form-'.$tier->id; @endphp
                        <tr>
                            <td class="align-middle">{{ $tier->min_hours }} ساعة فأكثر</td>
                            <td>
                                <input form="{{ $formId }}" type="number" step="0.001" name="price_per_hour" value="{{ $tier->price_per_hour }}" class="form-control form-control-sm" style="max-width:120px">
                            </td>
                            <td>
                                <form id="{{ $formId }}" method="POST" action="{{ route('admin.pricing.update', $tier) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <button class="btn btn-sm btn-outline-primary">حفظ</button>
                                </form>
                                <form method="POST" action="{{ route('admin.pricing.destroy', $tier) }}" class="d-inline" onsubmit="return confirm('حذف؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">لم يتم تحديد أسعار بعد</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
