@extends('layouts.admin')
@section('title', 'إضافة ملعب')

@section('content')
    <h3 class="mb-4">إضافة ملعب جديد</h3>
    <div class="card card-stat p-4" style="max-width:500px;">
        <form method="POST" action="{{ route('admin.courts.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">اسم الملعب</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ملاحظات (اختياري)</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
                <label class="form-check-label" for="is_active">الملعب نشط</label>
            </div>
            <button class="btn btn-success">حفظ ومتابعة إعداد ساعات العمل</button>
        </form>
    </div>
@endsection
