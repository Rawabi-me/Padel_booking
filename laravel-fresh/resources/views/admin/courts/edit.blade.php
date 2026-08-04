@extends('layouts.admin')
@section('title', 'تعديل ملعب')

@section('content')
    <h3 class="mb-4">تعديل: {{ $court->name }}</h3>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card card-stat p-4">
                <h5 class="mb-3">بيانات الملعب</h5>
                <form method="POST" action="{{ route('admin.courts.update', $court) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">اسم الملعب</label>
                        <input type="text" name="name" class="form-control" value="{{ $court->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="description" class="form-control">{{ $court->description }}</textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ $court->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">الملعب نشط</label>
                    </div>
                    <button class="btn btn-success">حفظ</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-stat p-4">
                <h5 class="mb-3">ساعات العمل الأسبوعية</h5>
                <form method="POST" action="{{ route('admin.courts.working-hours', $court) }}">
                    @csrf
                    <table class="table">
                        <thead><tr><th>اليوم</th><th>من</th><th>إلى</th><th>مغلق</th></tr></thead>
                        <tbody>
                        @foreach ($days as $i => $dayName)
                            @php $wh = $court->workingHours->firstWhere('day_of_week', $i); @endphp
                            <tr>
                                <td class="align-middle">{{ $dayName }}</td>
                                <td>
                                    <input type="hidden" name="hours[{{ $i }}][day_of_week]" value="{{ $i }}">
                                    <input type="time" name="hours[{{ $i }}][opens_at]" class="form-control form-control-sm" value="{{ $wh?->opens_at ? substr($wh->opens_at,0,5) : '09:00' }}">
                                </td>
                                <td>
                                    <input type="time" name="hours[{{ $i }}][closes_at]" class="form-control form-control-sm" value="{{ $wh?->closes_at ? substr($wh->closes_at,0,5) : '23:00' }}">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="hours[{{ $i }}][is_closed]" value="1" {{ $wh?->is_closed ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button class="btn btn-success">حفظ ساعات العمل</button>
                </form>
            </div>
        </div>
    </div>
@endsection
