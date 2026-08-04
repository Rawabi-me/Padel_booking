<?php
/*
 |--------------------------------------------------------------------------
 | أضف هذا المصفوفة داخل ملف config/services.php الموجود لديك أصلاً في
 | مشروع Laravel (داخل return [ ... ] الرئيسية)، ثم احذف هذا الملف.
 |--------------------------------------------------------------------------
 */

'thawani' => [
    'base_url' => env('THAWANI_BASE_URL', 'https://uatcheckout.thawani.om/api/v1'),
    'secret_key' => env('THAWANI_SECRET_KEY', 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et'),
    'publishable_key' => env('THAWANI_PUBLISHABLE_KEY', 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy'),
],
