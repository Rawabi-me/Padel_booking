@extends('layouts.admin')
@section('title', 'الملاعب')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>إدارة الملاعب</h3>
        <a href="{{ route('admin.courts.create') }}" class="btn btn-success">+ إضافة ملعب</a>
    </div>

    <div class="card card-stat p-3">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الحالة</th>
                    <th>عدد الحجوزات</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courts as $court)
                    <tr>
                        <td>{{ $court->name }}</td>
                        <td>
                            <span class="badge bg-{{ $court->is_active ? 'success' : 'secondary' }}">
                                {{ $court->is_active ? 'نشط' : 'معطل' }}
                            </span>
                        </td>
                        <td>{{ $court->booking_slots_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                            <form action="{{ route('admin.courts.destroy', $court) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">لا توجد ملاعب بعد، ابدأ بإضافة ملعب.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
