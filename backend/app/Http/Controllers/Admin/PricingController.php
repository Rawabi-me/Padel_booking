<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $tiers = PricingTier::orderBy('min_hours')->get();

        return view('admin.pricing.index', compact('tiers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'min_hours' => ['required', 'integer', 'min:1', 'unique:pricing_tiers,min_hours'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
        ]);

        PricingTier::create($data);

        return back()->with('status', 'تمت إضافة السعر.');
    }

    public function update(Request $request, PricingTier $pricing)
    {
        $data = $request->validate([
            'price_per_hour' => ['required', 'numeric', 'min:0'],
        ]);

        $pricing->update($data);

        return back()->with('status', 'تم تحديث السعر.');
    }

    public function destroy(PricingTier $pricing)
    {
        $pricing->delete();

        return back()->with('status', 'تم حذف السعر.');
    }
}
