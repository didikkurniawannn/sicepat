<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\DataEntry;
use App\Models\Facility;
use App\Models\Indicator;
use App\Models\Kecamatan;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isKab = $user->isSuperAdmin() || ($user->hasRole('pimpinan') && !$user->kecamatan_id);
        $year = (int) request('year', 2026);

        if ($isKab) {
            $kecamatans = Kecamatan::where('is_active', true)->orderBy('name')->get();
            $totalFacilities = Facility::count();
            $perModul = Facility::selectRaw('modul, COUNT(*) as total')->groupBy('modul')->pluck('total', 'modul');
            $pendInd = Indicator::where('kode', 'PEND_TOTAL')->first();
            $ranking = $pendInd
                ? DataEntry::withoutGlobalScope('tenant')->with('kecamatan')
                    ->where('indicator_id', $pendInd->id)->where('year', $year)
                    ->orderByDesc('value')->get()
                : collect();
            $totalPenduduk = (clone $ranking)->sum('value');
            $markers = Facility::select('id', 'name', 'type', 'modul', 'latitude', 'longitude', 'kecamatan_id')
                ->limit(2000)->get();

            return view('sektoral.dashboard.kabupaten', compact('kecamatans', 'totalFacilities', 'perModul', 'ranking', 'totalPenduduk', 'markers', 'year'));
        }

        $kecamatan = $user->kecamatan ?? Kecamatan::find($user->kecamatan_id);
        abort_if(!$kecamatan, 403, 'Akun Anda belum terhubung ke kecamatan.');
        $facilities = Facility::with('village')->latest()->limit(10)->get();
        $perModul = Facility::selectRaw('modul, COUNT(*) as total')->groupBy('modul')->pluck('total', 'modul');
        $perType = Facility::selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type');
        $entries = DataEntry::with('indicator')->where('year', $year)->orderBy('indicator_id')->get();
        $markers = Facility::select('id', 'name', 'type', 'modul', 'latitude', 'longitude')->limit(2000)->get();

        return view('sektoral.dashboard.kecamatan', compact('kecamatan', 'facilities', 'perModul', 'perType', 'entries', 'markers', 'year'));
    }
}
