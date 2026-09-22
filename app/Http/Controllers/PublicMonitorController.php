<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Section;
use Illuminate\Http\Request;

class PublicMonitorController extends Controller
{
    public function index()
    {
        $sections = Section::orderBy('order')->get()->map(function ($s) {
            $qq = Activity::where('section_id', $s->id);
            $s->activity_count = (clone $qq)->count();
            $s->total_pagu = (clone $qq)->sum('budget_pagu');
            $s->total_realisasi = (clone $qq)->sum('budget_realization');
            $s->upcoming7 = (clone $qq)->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
            return $s;
        });

        $upcoming7 = Activity::with(['section','pptk'])
            ->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('activity_date')->get();

        $total = Activity::count();
        $totalH7 = $upcoming7->count();

        return view('public.monitor', compact('sections','upcoming7','total','totalH7'));
    }

    public function events(Request $request)
    {
        $q = Activity::with(['section','pptk']);
        if ($request->filled('section_id')) $q->where('section_id', $request->section_id);

        return $q->orderBy('activity_date')->get()->map(function ($a) {
            $days = $a->days_to_event;
            $isH7 = $a->is_h7;
            return [
                'id' => $a->id,
                'title' => $a->title.' ('.$a->section->short_name.')',
                'start' => $a->activity_date->format('Y-m-d'),
                'color' => $isH7 ? '#DC2626' : ($a->section->color ?? '#3B82F6'),
                'textColor' => '#fff',
                'extendedProps' => [
                    'judul' => $a->title,
                    'tanggal' => $a->activity_date->format('d M Y'),
                    'bidang' => $a->section->name,
                    'section_color' => $a->section->color,
                    'kode_rekening' => $a->account_code,
                    'program' => $a->program_name,
                    'kebutuhan' => $a->requirement_qty.' / '.$a->total_qty.' '.$a->unit,
                    'pagu' => (float) $a->budget_pagu,
                    'realisasi' => (float) $a->budget_realization,
                    'sisa' => (float) $a->budget_pagu - (float) $a->budget_realization,
                    'status' => $a->status,
                    'progress' => $a->progress,
                    'lokasi' => $a->location ?? '-',
                    'pptk' => $a->pptk->name ?? '-',
                    'is_h7' => $isH7,
                    'days' => $days,
                ],
            ];
        });
    }
}
