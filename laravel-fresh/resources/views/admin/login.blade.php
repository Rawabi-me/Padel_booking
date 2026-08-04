<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { background:#0f2f26; height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',Tahoma,sans-serif; }
        .login-card { background:#fff; border-radius:1rem; padding:2.5rem; width:100%; max-width:380px; }
    </style>
</head>
<body>
    <div class="login-card shadow">
        <h4 class="mb-4 text-center">🎾 لوحة تحكم حجز الملاعب</h4>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-success w-100" type="submit">دخول</button>
        </form>
    </div>
</body>
</html>
