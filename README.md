## 🔗 Live Demo

- **Customer site:** https://padel-booking-pink.vercel.app
- **Admin dashboard:** https://padelbooking-production-d794.up.railway.app/admin
  - Email: `admin@padel.local`
  - Password: `Admin@1234`

⚠️ **Security note:** The credentials above belong to a demo account created by the database seeder, for preview purposes only. In a real production environment the password should be changed immediately after first login, and real credentials should never be published in a public file.

---

# Padel Booking Platform

A full-stack padel court booking platform: a Laravel backend with an admin dashboard, a React customer frontend, and an integration with the Thawani online payment gateway.

## Why this stack

- **Backend: Laravel 11 (PHP)** — chosen for how concisely it expresses the non-trivial booking logic (Eloquent ORM, database transactions, query builder), and for its built-in HTTP client, which handles the Thawani integration without pulling in extra dependencies.
- **Frontend: React (Vite)** — a single-page customer interface that works without any account registration.

## Project structure

```
padel-booking/
├── backend/     Laravel — API + admin dashboard (Blade)
└── frontend/    React (Vite) — customer booking interface
```

---

## 1) Running the backend locally

```bash
# 1. Create a fresh Laravel project in a temporary folder
composer create-project laravel/laravel laravel-fresh "^11.0"

# 2. Copy this repo's backend/ files over laravel-fresh, merging into:
#    app/Models, app/Http, app/Services, database/migrations, database/seeders,
#    routes/*.php, resources/views, .env.example

# 3. Merge these two reference snippets, then delete them:
#    - backend/config/services.thawani-snippet.php  -> into config/services.php
#    - backend/config/app.frontend-url-snippet.php  -> into config/app.php

cd laravel-fresh
cp .env.example .env
php artisan key:generate

# Laravel 11 does not register routes/api.php by default.
# Instead of install:api, add this line inside bootstrap/app.php, within withRouting():
#   api: __DIR__.'/../routes/api.php',

touch database/database.sqlite   # SQLite keeps local setup simple — no separate DB server needed

php artisan migrate --seed
php artisan serve                # runs on http://localhost:8000
```

### Local admin credentials (created automatically by the seeder)

```
URL:      http://localhost:8000/admin
Email:    admin@padel.local
Password: Admin@1234
```

### Sample data created by the seeder
- 3 courts (A, B, C), open daily 09:00–23:00
- Pricing tiers: 1 hour = 10.000 OMR; 2+ hours = 8.000 OMR per hour

---

## 2) Running the frontend locally

```bash
cd frontend
npm install
cp .env.example .env      # adjust VITE_API_URL if needed
npm run dev               # runs on http://localhost:5173
```

---

## 3) Thawani payment gateway (sandbox)

Integrated against Thawani's official sandbox/UAT API:
- Create a payment session: `POST https://uatcheckout.thawani.om/api/v1/checkout/session`
- Redirect the customer: `https://uatcheckout.thawani.om/pay/{session_id}?key={publishable_key}`
- Verify payment status: `GET https://uatcheckout.thawani.om/api/v1/checkout/session/{session_id}`

**Payment flow:**
1. The customer selects online payment → the backend creates a pending booking, then a Thawani checkout session → the customer is redirected to Thawani.
2. After payment, the customer returns to `/payment/success?ref=...` or `/payment/cancel?ref=...` on the frontend.
3. The frontend calls `GET /api/payment/verify?ref=...`, and the backend confirms the status directly with Thawani rather than trusting the redirect URL — so the payment state can't be spoofed client-side.

---

## 4) Booking logic

Full implementation: `backend/app/Services/BookingAvailabilityService.php`

| Requirement | Implementation |
|---|---|
| Court names are never shown to the customer | The public endpoint (`/api/availability`) returns only the time slot and a count of available courts. No court name or ID appears anywhere in the response. |
| A time stays bookable until every court is taken | `available_courts_count` aggregates all open, unbooked courts for each slot; the slot disappears only when that count hits zero. |
| Random assignment, only at confirmation | On submission, a court is picked at random (`shuffle`) from those actually free at that moment, inside a database transaction, backed by a `unique(court_id, date, start_time)` constraint that prevents double-booking even under concurrent requests. |
| Past and closed slots are blocked | Validated twice: past/closed slots are excluded when listing availability, and re-checked at confirmation before anything is written. |
| Multi-hour and multi-day bookings in one order | A single `Booking` holds multiple `BookingSlot` rows across different dates. Pricing is calculated per day based on that day's hour count, then totalled into one order. |
| Atomic operation | If any slot in the order fails (a last-second conflict), the whole booking rolls back and the customer is asked to retry — no partial, inconsistent bookings. |

---

## 5) Automated tests (PHPUnit)

Tests covering the core algorithm live in `backend/tests/Feature/BookingAvailabilityTest.php`:
- Availability responses never leak a court name or ID
- A slot remains available as long as at least one court is free
- Past time slots are never returned for the current day

Run them with:

```bash
php artisan test
```

---

## 6) Tech stack

**Backend:** Laravel 11, Eloquent ORM, MySQL (production) / SQLite (local), Laravel HTTP Client (Guzzle) for Thawani, Blade + Bootstrap 5 (RTL) for the dashboard.

**Frontend:** React 19, Vite, React Router, Axios.

**Hosting:** Railway (backend + MySQL), Vercel (frontend).

## 7) Notes

- Pricing is global rather than per-court, since the customer never picks or sees a specific court — this keeps the price fair regardless of which court gets assigned.
- A "Track your booking" page (`/track`) lets customers look up any booking by its reference number.
- The platform is deployed and running live at the links at the top of this file.
