<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Kecamatan;
use App\Models\Section;
use Illuminate\Http\Request;

class PublicMonitorController extends Controller
{
    private function tenant(string $slug): Kecamatan
    {
        return Kecamatan::active()->where('slug', $slug)->firstOrFail();
    }

    public function index(string $slug)
    {
        $kecamatan = $this->tenant($slug);
        $sections = Section::active()->orderBy('order')->get()->map(function ($s) use ($kecamatan) {
            $qq = Activity::where('section_id', $s->id)->where('kecamatan_id', $kecamatan->id);
            $s->activity_count = (clone $qq)->count();
            $s->upcoming7 = (clone $qq)->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
            return $s;
        });

        $upcoming7 = Activity::with(['section','pptk'])
            ->where('kecamatan_id', $kecamatan->id)
            ->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('activity_date')->get();

        $total = Activity::where('kecamatan_id', $kecamatan->id)->count();

        return view('public.monitor', [
            'kecamatan' => $kecamatan, 'sections' => $sections,
            'upcoming7' => $upcoming7, 'total' => $total, 'totalH7' => $upcoming7->count(),
        ]);
    }

    public function events(Request $request, string $slug)
    {
        $kecamatan = $this->tenant($slug);
        $q = Activity::with(['section','pptk'])->where('kecamatan_id', $kecamatan->id);
        if ($request->filled('section_id')) $q->where('section_id', $request->section_id);

        // Nominal (pagu/realisasi/sisa) SENGAJA tidak dikirim ke publik
        return $q->orderBy('activity_date')->get()->map(function ($a) {
            $days = $a->days_to_event;
            $isH7 = $a->is_h7;
            $done = $a->status === 'selesai';
            return [
                'id' => $a->id,
                'title' => ($done ? '✓ ' : '').$a->title.' ('.$a->section->short_name.')',
                'start' => $a->activity_date->format('Y-m-d'),
                'color' => $done ? '#16A34A' : ($isH7 ? '#DC2626' : ($a->section->color ?? '#3B82F6')),
                'textColor' => '#fff',
                'extendedProps' => [
                    'judul' => $a->title,
                    'tanggal' => $a->activity_date->translatedFormat('l, d F Y'),
                    'bidang' => $a->section->name,
                    'section_color' => $a->section->color,
                    'kode_rekening' => $a->account_code,
                    'program' => $a->program_name,
                    'kebutuhan' => $a->requirement_qty.' / '.$a->total_qty.' '.$a->unit,
                    'status' => $a->status,
                    'progress' => $a->progress,
                    'lokasi' => $a->location ?? '-',
                    'pptk' => $a->pptk->name ?? '-',
                    'is_h7' => $isH7,
                    'days' => $days,
                    'is_past' => $a->activity_date->lt(now()->startOfDay()),
                ],
            ];
        });
    }
}
