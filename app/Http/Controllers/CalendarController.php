<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Section;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $sections = Section::orderBy('order')->get();
        return view('calendar.index', compact('sections'));
    }

    public function events(Request $request)
    {
        $user = auth()->user();
        $q = Activity::with('section');
        if ($user->hasAnyRole(['kasi','staf']) && $user->section_id) {
            $q->where('section_id', $user->section_id);
        }
        if ($request->filled('section_id')) $q->where('section_id', $request->section_id);
        if ($request->filled('status')) $q->where('status', $request->status);

        return $q->get()->map(function ($a) {
            $days = $a->days_to_event;
            $isH7 = $a->is_h7;
            return [
                'id' => $a->id,
                'title' => ($isH7 ? '⚠ H-'.$days.' | ' : '').$a->title.' ('.$a->section->short_name.')',
                'start' => $a->activity_date->format('Y-m-d'),
                'color' => $isH7 ? '#DC2626' : ($a->section->color ?? '#3B82F6'),
                'textColor' => '#fff',
                'url' => url('/kegiatan/'.$a->id),
                'extendedProps' => [
                    'section' => $a->section->name,
                    'status' => $a->status,
                    'pagu' => (float) $a->budget_pagu,
                    'is_h7' => $isH7,
                    'days' => $days,
                ],
            ];
        });
    }
}
