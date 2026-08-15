## 🔗 روابط حية (Live Demo)

- **الموقع (واجهة العميل):** https://padel-booking-pink.vercel.app
- **لوحة التحكم الإدارية:** https://padelbooking-production-d794.up.railway.app/admin
  - البريد: `admin@padel.local`
  - كلمة المرور: `Admin@1234`

⚠️ **ملاحظة أمنية:** بيانات الدخول أعلاه مخصصة لأغراض التقييم فقط (مستخدم تجريبي وهمي أنشأه الـ Seeder). في بيئة إنتاج حقيقية، يجب تغيير كلمة المرور فوراً بعد أول تسجيل دخول، وعدم نشر بيانات الدخول الفعلية في أي ملف عام.

---

# منصة حجز ملاعب البادل — Padel Booking Platform

مشروع كامل (اختبار المرحلة الثانية - نهج NAHJ): باكند Laravel + فرونت اند React لحجز ملاعب البادل، مع لوحة تحكم إدارية ودمج بوابة الدفع الإلكتروني **ثواني**.

## لماذا هذا الاختيار التقني

- **الباكند: Laravel 11 (PHP)** — أحد الخيارين المسموح بهما في متطلبات الاختبار. اخترته على .NET لسهولة التعبير عن منطق الحجز المعقد (Eloquent ORM، Transactions، Query Builder) بكود مختصر وواضح، ولتكامله الجاهز مع Laravel HTTP Client لاستدعاء بوابة ثواني بدون مكتبات خارجية إضافية.
- **الفرونت اند: React (Vite)** — واجهة ويب للعميل (SPA) بدون الحاجة لإنشاء حساب.

## هيكلة المشروع
padel-booking/
├── backend/ Laravel — API + لوحة تحكم الإدارة (Blade)
└── frontend/ React (Vite) — واجهة حجز العميل

---

## 1) تشغيل الباكند (Laravel) محلياً

تم تشغيل المشروع بالكامل واختباره فعلياً (محلياً عبر GitHub Codespaces، وحالياً منشور فعلياً على Railway كما هو موضح أعلاه).

```bash
# 1. أنشئ مشروع Laravel جديد فارغ في مجلد مؤقت
composer create-project laravel/laravel laravel-fresh "^11.0"

# 2. انسخ ملفات المشروع الحالي (backend/) فوق مجلد laravel-fresh، بحيث تحل محل/تضاف إلى:
#    app/Models, app/Http, app/Services, database/migrations, database/seeders, routes/*.php, resources/views, .env.example

# 3. ادمج محتوى هذين الملفين (موجودين هنا فقط كمرجع، احذفهما بعد الدمج):
#    - backend/config/services.thawani-snippet.php   -> داخل config/services.php
#    - backend/config/app.frontend-url-snippet.php    -> داخل config/app.php

cd laravel-fresh
cp .env.example .env
php artisan key:generate

# Laravel 11 لا يفعّل routes/api.php تلقائياً؛ استخدم بدلاً من install:api إضافة السطر التالي
# داخل bootstrap/app.php ضمن withRouting():  api: __DIR__.'/../routes/api.php',

touch database/database.sqlite   # نستخدم SQLite لتبسيط التشغيل بدون سيرفر قاعدة بيانات منفصل

php artisan migrate --seed
php artisan serve          # يعمل الآن على http://localhost:8000
```

### بيانات دخول لوحة التحكم المحلية (تُنشأ تلقائياً بواسطة الـ Seeder)

الرابط: http://localhost:8000/admin
البريد: admin@padel.local
كلمة المرور: Admin@1234

### بيانات تجريبية يُنشئها الـ Seeder
- 3 ملاعب (A, B, C) بدوام يومي 09:00 - 23:00.
- عروض أسعار: ساعة واحدة = 10.000 ر.ع، ساعتان فأكثر = 8.000 ر.ع/ساعة.

---

## 2) تشغيل الفرونت اند (React) محلياً

```bash
cd frontend
npm install
cp .env.example .env      # عدّل VITE_API_URL إذا لزم
npm run dev                # يعمل على http://localhost:5173
```

---

## 3) بوابة الدفع ثواني (Thawani) — بيئة الاختبار

تم دمج الـ API الحقيقي لثواني (Sandbox/UAT) حسب توثيقهم الرسمي:
- إنشاء جلسة دفع: `POST https://uatcheckout.thawani.om/api/v1/checkout/session`
- توجيه العميل: `https://uatcheckout.thawani.om/pay/{session_id}?key={publishable_key}`
- التحقق من حالة الدفع: `GET https://uatcheckout.thawani.om/api/v1/checkout/session/{session_id}`

**تدفق الدفع:**
1. العميل يختار "الدفع الإلكتروني" ⟶ الباكند ينشئ الحجز (pending) ثم جلسة دفع ثواني ⟶ يُعاد توجيه العميل لصفحة ثواني.
2. بعد الدفع، يُعاد العميل إلى `/payment/success?ref=...` أو `/payment/cancel?ref=...` في الفرونت اند.
3. الفرونت اند يستدعي `GET /api/payment/verify?ref=...` والذي يتحقق من الباكند مباشرة مع ثواني قبل تأكيد حالة الدفع — لمنع أي تلاعب من جهة العميل.

---

## 4) شرح منطق الحجز (النقطة الأهم في التقييم)

الكود الكامل في: `backend/app/Services/BookingAvailabilityService.php`

| المتطلب | كيف تم تنفيذه |
|---|---|
| لا يظهر اسم أي ملعب للعميل | الـ API العام (`/api/availability`) يعيد فقط الوقت وعدد الملاعب المتاحة (رقم فقط)، لا يوجد أي حقل باسم الملعب في الاستجابة. |
| الوقت يبقى ظاهراً حتى تُستنفد كل الملاعب | يتم تجميع (`available_courts_count`) لكل وقت من كل الملاعب المفتوحة وغير المحجوزة؛ الوقت يُستبعد فقط عندما يصل العدد لصفر. |
| تخصيص عشوائي عند التأكيد فقط | عند إرسال الحجز، يتم اختيار ملعب عشوائي (`shuffle`) من قائمة الملاعب المتاحة فعلياً في تلك اللحظة، داخل Database Transaction، مع قيد `unique(court_id, date, start_time)` على مستوى قاعدة البيانات لمنع أي تعارض حتى في حال الضغط المتزامن. |
| منع حجز وقت ماضٍ أو مغلق | يتم التحقق مرتين: عند عرض الأوقات (تُستبعد الأوقات الماضية والمغلقة من الأساس)، وعند التأكيد الفعلي (تحقق نهائي قبل الحفظ). |
| حجز أكثر من ساعة وأكثر من يوم بنفس العملية | الحجز الواحد (`Booking`) يحتوي على عدة `BookingSlot` قد تكون لتواريخ مختلفة؛ السعر يُحتسب لكل يوم على حدة حسب عدد ساعاته (العرض)، ثم تُجمع كل الأيام في فاتورة واحدة. |
| العملية ذرية (atomic) | إن فشل حجز أي وقت ضمن الطلب (تعارض لحظي)، تُلغى العملية بالكامل (rollback) ويُطلب من العميل إعادة المحاولة، بدل حجز جزئي غير متسق. |

---

## 5) الاختبارات الآلية (PHPUnit)

اختبارات فعلية للتحقق من صحة الخوارزمية الأساسية، موجودة في `backend/tests/Feature/BookingAvailabilityTest.php`:
- التأكد من عدم كشف أي اسم/معرّف ملعب في الاستجابة.
- التأكد أن الوقت يبقى متاحاً طالما هناك ملعب واحد حر.
- التأكد من منع عرض أوقات ماضية لليوم الحالي.

تشغيلها:
```bash
php artisan test
```

---

## 6) التقنيات المستخدمة

**الباكند:** Laravel 11, Eloquent ORM, MySQL (إنتاج) / SQLite (تطوير محلي), Laravel HTTP Client (Guzzle) لثواني, Blade + Bootstrap 5 (RTL) للوحة التحكم.

**الفرونت اند:** React 19, Vite, React Router, Axios.

**النشر:** Railway (باكند + قاعدة بيانات MySQL)، Vercel (فرونت اند).

## 7) ملاحظات إضافية

- الأسعار مصممة بشكل عام وليست مرتبطة بملعب محدد، لأن العميل لا يختار/يعرف الملعب أصلاً — هذا يضمن عدالة السعر بغض النظر عن الملعب الذي يُخصَّص له عشوائياً.
- تمت إضافة صفحة "تتبع حجزي" (`/track`) للعميل، تتيح الاستعلام عن حالة أي حجز باستخدام الرقم المرجعي فقط.
- المشروع منشور فعلياً وقيد التشغيل على الروابط الموضحة أعلى هذا الملف.