<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Section;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Activity::with('section');

        // Kasi/PPTK/Staf hanya lihat unit sendiri, lainnya global
        if ($user->hasAnyRole(['kasi','pptk','staf'])) {
            $q->where('section_id', $user->section_id);
        }

        $all = (clone $q)->get();
        $total = $all->count();
        $berjalan = $all->whereIn('status', ['berjalan','disetujui','diverifikasi','diajukan'])->count();
        $selesai = $all->where('status', 'selesai')->count();
        $ditolak = $all->where('status', 'ditolak')->count();
        $h7 = Activity::with('section')
            ->when($user->hasAnyRole(['kasi','pptk','staf']), fn($qq) => $qq->where('section_id', $user->section_id))
            ->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        $pagu = (clone $q)->sum('budget_pagu');
        $realisasi = (clone $q)->sum('budget_realization');
        $sisa = $pagu - $realisasi;

        $perSection = Section::orderBy('order')->get()->map(function ($s) use ($user) {
            $qq = Activity::where('section_id', $s->id);
            if ($user->hasAnyRole(['kasi','pptk','staf'])) {
                if ($s->id !== $user->section_id) return null;
            }
            return [
                'section' => $s,
                'count' => (clone $qq)->count(),
                'pagu' => (clone $qq)->sum('budget_pagu'),
                'realisasi' => (clone $qq)->sum('budget_realization'),
            ];
        })->filter()->values();

        $upcoming = (clone $q)->with('section')->where('activity_date', '>=', now()->toDateString())
            ->orderBy('activity_date')->limit(10)->get();

        $top5 = (clone $q)->with('section')->orderByDesc('budget_pagu')->limit(5)->get();
        $zeroRealisasi = (clone $q)->with('section')->where('budget_realization', 0)->orderBy('activity_date')->limit(10)->get();

        // Data grafik
        $chartLabels = $perSection->map(fn($x) => $x['section']->short_name)->values();
        $chartCounts = $perSection->map(fn($x) => $x['count'])->values();
        $chartRealisasi = $perSection->map(fn($x) => (float) $x['realisasi'])->values();

        return view('dashboard', compact(
            'total','berjalan','selesai','ditolak','h7','pagu','realisasi','sisa',
            'perSection','upcoming','top5','zeroRealisasi','chartLabels','chartCounts','chartRealisasi'
        ));
    }
}
