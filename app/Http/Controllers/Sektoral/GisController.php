<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\AiRecommendation;
use App\Models\DataEntry;
use App\Models\Facility;
use App\Models\Indicator;
use App\Models\Kecamatan;
use App\Models\Village;
use App\Support\Geo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard GIS: visualisasi peta batas kecamatan/desa dari
 * public/maps/*.json + choropleth indikator + marker fasilitas.
 */
class GisController extends Controller
{
    public function index(Request $request)
    {
        return view('sektoral.gis.dark', self::dashboardData($request) + [
            'guest' => false,
            'formAction' => route('gis.index'),
        ]);
    }

    /** Data bersama untuk halaman GIS publik (/ ) & internal (/gis). */
    public static function dashboardData(Request $request): array
    {
        $user = auth()->user();
        $focusKec = ($user && $user->kecamatan_id) ? Kecamatan::find($user->kecamatan_id) : null;

        $indicators = Indicator::orderBy('name')->get();
        $indicator = $indicators->find($request->get('indicator_id'))
            ?? Indicator::where('kode', 'PEND_TOTAL')->first()
            ?? $indicators->first();

        $q = fn ($model) => $focusKec ? $model::where('kecamatan_id', $focusKec->id) : $model::query();

        $stats = [
            'desa' => $q(Village::class)->count(),
            'kesehatan' => $q(Facility::class)->where('modul', 'M-04')->count(),
            'pendidikan' => $q(Facility::class)->where('modul', 'M-03')->count(),
            'umkm' => $q(Facility::class)->where('modul', 'M-06')->count(),
        ];

        $aiQuery = AiRecommendation::with('kecamatan')->latest();
        if ($focusKec) {
            $aiQuery->where(fn ($w) => $w->where('kecamatan_id', $focusKec->id)->orWhereNull('kecamatan_id'));
        }
        $latestAi = (clone $aiQuery)->whereNull('kecamatan_id')->first() ?? $aiQuery->first();

        return [
            'indicators' => $indicators,
            'indicator' => $indicator,
            'year' => (int) $request->get('year', 2026),
            'modules' => Facility::MODULES,
            'stats' => $stats,
            'latestAi' => $latestAi,
            'focusKec' => $focusKec,
            'focusGeoName' => $focusKec ? Geo::geoName($focusKec->namaSingkat()) : null,
            'subtitle' => $focusKec ? $focusKec->name.', Kab. Bandung' : 'Kabupaten Bandung, Jawa Barat',
        ];
    }

    /** Choropleth: nilai indikator per kecamatan (+ jumlah fasilitas). */
    public function choropleth(Request $request)
    {
        $indicator = Indicator::find($request->get('indicator_id'))
            ?? Indicator::where('kode', 'PEND_TOTAL')->first();
        $year = (int) $request->get('year', 2026);

        $values = $indicator
            ? DataEntry::withoutGlobalScope('tenant')->where('indicator_id', $indicator->id)
                ->where('year', $year)->pluck('value', 'kecamatan_id')
            : collect();
        $facCount = Facility::withoutGlobalScope('tenant')->selectRaw('kecamatan_id, COUNT(*) as c')
            ->groupBy('kecamatan_id')->pluck('c', 'kecamatan_id');

        $rows = Kecamatan::where('is_active', true)->orderBy('name')->get()->map(fn ($k) => [
            'id' => $k->id,
            'nama' => $k->namaSingkat(),
            'geo_nama' => Geo::geoName($k->namaSingkat()),
            'value' => $values[$k->id] ?? null,
            'facilities' => $facCount[$k->id] ?? 0,
            'penduduk' => $k->jumlah_penduduk,
        ]);

        return response()->json([
            'indicator' => $indicator ? ['kode' => $indicator->kode, 'nama' => $indicator->nama, 'satuan' => $indicator->satuan] : null,
            'year' => $year,
            'data' => $rows,
        ]);
    }

    /** Batas desa per kecamatan (agar browser tak perlu memuat desa.json 12MB). */
    public function desa(Request $request)
    {
        $data = $request->validate(['kecamatan' => 'required|string|max:100']);
        $kec = Kecamatan::where('nama', $data['kecamatan'])->first();
        $geoName = $kec ? Geo::geoName($kec->namaSingkat()) : $data['kecamatan'];
        $key = 'gis:desa:'.Geo::norm($geoName);

        $fc = Cache::remember($key, 3600, function () use ($geoName, $kec) {
            $feats = Geo::desaByKecamatan($kec?->namaSingkat() ?? $geoName);

            return ['type' => 'FeatureCollection', 'features' => $feats];
        });

        return response()->json($fc);
    }

    /** Marker fasilitas (dibatasi 3000, ter-filter tenant & modul). */
    public function facilities(Request $request)
    {
        $q = Facility::with(['village:id,nama', 'kecamatan:id,nama'])
            ->select('id', 'name', 'type', 'modul', 'latitude', 'longitude', 'kecamatan_id', 'village_id');
        if ($request->filled('modul')) {
            $q->where('modul', $request->modul);
        }
        if ($request->filled('kecamatan_id')) {
            $q->where('kecamatan_id', $request->kecamatan_id);
        }

        return response()->json($q->limit(3000)->get());
    }
}
