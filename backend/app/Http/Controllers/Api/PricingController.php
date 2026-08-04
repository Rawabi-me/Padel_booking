<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;

class PricingController extends Controller
{
    /**
     * GET /api/pricing
     * يعيد عروض الأسعار العامة ليستخدمها الفرونت اند في حساب السعر التقديري قبل الإرسال.
     */
    public function __invoke()
    {
        return response()->json(
            PricingTier::orderBy('min_hours')->get(['min_hours', 'price_per_hour'])
        );
    }
}
