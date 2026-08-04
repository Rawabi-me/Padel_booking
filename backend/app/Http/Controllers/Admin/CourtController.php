<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtRequest;
use App\Models\Court;
use App\Models\CourtWorkingHour;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function index()
    {
        $courts = Court::withCount('bookingSlots')->latest()->get();

        return view('admin.courts.index', compact('courts'));
    }

    public function create()
    {
        return view('admin.courts.create');
    }

    public function store(StoreCourtRequest $request)
    {
        $court = Court::create($request->validated());

        // إنشاء دوام افتراضي (9 صباحاً - 11 مساءً) لكل أيام الأسبوع، قابل للتعديل لاحقاً
        foreach (range(0, 6) as $day) {
            CourtWorkingHour::create([
                'court_id' => $court->id,
                'day_of_week' => $day,
                'opens_at' => '09:00',
                'closes_at' => '23:00',
                'is_closed' => false,
            ]);
        }

        return redirect()->route('admin.courts.edit', $court)->with('status', 'تم إضافة الملعب بنجاح.');
    }

    public function edit(Court $court)
    {
        $court->load('workingHours');
        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

        return view('admin.courts.edit', compact('court', 'days'));
    }

    public function update(StoreCourtRequest $request, Court $court)
    {
        $court->update($request->validated());

        return redirect()->route('admin.courts.edit', $court)->with('status', 'تم تحديث بيانات الملعب.');
    }

    public function destroy(Court $court)
    {
        $court->delete();

        return redirect()->route('admin.courts.index')->with('status', 'تم حذف الملعب.');
    }

    /**
     * تحديث ساعات العمل الأسبوعية لملعب معين.
     */
    public function updateWorkingHours(Request $request, Court $court)
    {
        $data = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
            'hours.*.is_closed' => ['nullable'],
        ]);

        foreach ($data['hours'] as $row) {
            CourtWorkingHour::updateOrCreate(
                ['court_id' => $court->id, 'day_of_week' => $row['day_of_week']],
                [
                    'opens_at' => $row['opens_at'] ?? null,
                    'closes_at' => $row['closes_at'] ?? null,
                    'is_closed' => isset($row['is_closed']),
                ]
            );
        }

        return back()->with('status', 'تم تحديث ساعات العمل.');
    }
}
