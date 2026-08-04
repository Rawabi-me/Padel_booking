<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtClosure;
use Illuminate\Http\Request;

class ClosureController extends Controller
{
    public function index()
    {
        $closures = CourtClosure::with('court')->latest()->get();
        $courts = Court::orderBy('name')->get();

        return view('admin.closures.index', compact('closures', 'courts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'court_id' => ['nullable', 'exists:courts,id'], // فارغ = كل الملاعب
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        CourtClosure::create($data);

        return back()->with('status', 'تم إضافة فترة الإغلاق.');
    }

    public function destroy(CourtClosure $closure)
    {
        $closure->delete();

        return back()->with('status', 'تم حذف فترة الإغلاق.');
    }
}
