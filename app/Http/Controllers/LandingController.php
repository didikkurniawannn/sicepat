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

        return view('public.landing', compact('totalKegiatan', 'unitAktif', 'h7', 'kecamatans'));
    }
}
