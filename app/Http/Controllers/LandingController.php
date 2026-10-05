<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Kecamatan;
use App\Models\Section;

class LandingController extends Controller
{
    public function index()
    {
        $totalKegiatan = Activity::count();
        $unitAktif = Section::active()->count();
        $h7 = Activity::whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
        $kecamatans = Kecamatan::active()->orderBy('order')->get();
        $totalFasilitas = class_exists(\App\Models\Facility::class) && \Illuminate\Support\Facades\Schema::hasTable('facilities')
            ? \App\Models\Facility::count() : 0;
        $totalDesa = class_exists(\App\Models\Village::class) && \Illuminate\Support\Facades\Schema::hasTable('villages')
            ? \App\Models\Village::count() : 0;
        $sorotan = Activity::with(['section', 'kecamatan'])
            ->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('activity_date')->limit(8)->get();

        return view('public.landing', compact('totalKegiatan', 'unitAktif', 'h7', 'kecamatans', 'totalFasilitas', 'totalDesa', 'sorotan'));
    }
}
