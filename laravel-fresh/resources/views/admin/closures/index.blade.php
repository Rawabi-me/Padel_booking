@extends('layouts.admin')
@section('title', 'الإغلاقات')

@section('content')
    <h3 class="mb-4">إغلاق الملاعب</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-stat p-4">
                <h5 class="mb-3">إضافة إغلاق جديد</h5>
                <form method="POST" action="{{ route('admin.closures.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">الملعب</label>
                        <select name="court_id" class="form-select">
                            <option value="">كل الملاعب</option>
                            @foreach ($courts as $court)
                                <option value="{{ $court->id }}">{{ $court->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السبب (اختياري)</label>
                        <input type="text" name="reason" class="form-control">
                    </div>
                    <button class="btn btn-success w-100">إغلاق</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-stat p-3">
                <table class="table">
                    <thead><tr><th>الملعب</th><th>من</th><th>إلى</th><th>السبب</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($closures as $c)
                        <tr>
                            <td>{{ $c->court->name ?? 'كل الملاعب' }}</td>
                            <td>{{ $c->start_date->format('Y-m-d') }}</td>
                            <td>{{ $c->end_date->format('Y-m-d') }}</td>
                            <td>{{ $c->reason ?: '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.closures.destroy', $c) }}" onsubmit="return confirm('حذف؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">لا توجد إغلاقات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
