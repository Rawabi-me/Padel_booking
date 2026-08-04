<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'لوحة التحكم') - حجز ملاعب البادل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .sidebar { min-height: 100vh; background:#0f2f26; color:#fff; }
        .sidebar a { color:#d7e8e2; display:block; padding:.6rem 1rem; border-radius:.4rem; text-decoration:none; }
        .sidebar a:hover, .sidebar a.active { background:#16463a; color:#fff; }
        .brand { color:#8bd3ae; font-weight:700; }
        .card-stat { border:none; border-radius:1rem; box-shadow:0 2px 10px rgba(0,0,0,.05); }
    </style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-3" style="width:230px;">
        <div class="brand fs-4 mb-4">🎾 لوحة التحكم</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">الرئيسية</a>
        <a href="{{ route('admin.courts.index') }}" class="{{ request()->routeIs('admin.courts.*') ? 'active' : '' }}">الملاعب</a>
        <a href="{{ route('admin.closures.index') }}" class="{{ request()->routeIs('admin.closures.*') ? 'active' : '' }}">الإغلاقات</a>
        <a href="{{ route('admin.pricing.index') }}" class="{{ request()->routeIs('admin.pricing.*') ? 'active' : '' }}">الأسعار والعروض</a>
        <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">الحجوزات</a>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-4">
            @csrf
            <button class="btn btn-outline-light btn-sm w-100">تسجيل الخروج</button>
        </form>
    </nav>

    <main class="flex-grow-1 p-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
