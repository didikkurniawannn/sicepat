<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Section;

class LandingController extends Controller
{
    public function index()
    {
        $totalKegiatan = Activity::count();
        $unitAktif = Section::active()->count();
        $h7 = Activity::whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
        $sums = Activity::budgetSums(Activity::query());

        return view('public.landing', compact('totalKegiatan', 'unitAktif', 'h7') + ['pagu' => $sums['pagu']]);
    }
}
