<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\Facility;
use App\Models\Kecamatan;
use App\Models\Village;
use App\Support\Audit;
use Illuminate\Http\Request;

class GeoApiController extends Controller
{
    /** GET /api/kecamatans/{id}/boundary — poligon asli + center untuk auto-zoom (FR-GEO-05) */
    public function boundary(Kecamatan $kecamatan)
    {
        $feat = \App\Support\Geo::findKecamatan($kecamatan->name);
        $center = $feat ? \App\Support\Geo::centroid($feat['geometry']) : [(float) $kecamatan->center_lat, (float) $kecamatan->center_lng];
        $bbox = $feat ? \App\Support\Geo::bbox($feat['geometry']) : [(float) $kecamatan->min_lat, (float) $kecamatan->min_lng, (float) $kecamatan->max_lat, (float) $kecamatan->max_lng];

        return response()->json([
            'id' => $kecamatan->id,
            'kode_bps' => $kecamatan->kode_bps,
            'nama' => $kecamatan->name,
            'center' => $center,
            'bounds' => [[$bbox[0], $bbox[1]], [$bbox[2], $bbox[3]]],
            'geometry' => $feat['geometry'] ?? null,
            'geojson' => $kecamatan->geojson ? json_decode($kecamatan->geojson) : null,
        ]);
    }

    /** POST /api/validate-point — validasi titik dalam wilayah tenant (FR-GEO-08) */
    public function validatePoint(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'kecamatan_id' => 'nullable|exists:kecamatans,id',
        ]);
        $kecId = $data['kecamatan_id'] ?? auth()->user()->kecamatan_id;
        $kecamatan = Kecamatan::find($kecId);
        if (!$kecamatan) {
            return response()->json(['valid' => false, 'message' => 'Kecamatan tidak ditemukan.'], 404);
        }
        $valid = $kecamatan->containsPoint((float) $data['latitude'], (float) $data['longitude']);
        $village = Village::where('kecamatan_id', $kecamatan->id)->orderBy('nama')->first();

        return response()->json([
            'valid' => $valid,
            'message' => $valid ? 'Titik valid di dalam wilayah.' : 'Lokasi berada di luar wilayah kecamatan Anda.',
            'kecamatan' => ['id' => $kecamatan->id, 'kode_bps' => $kecamatan->kode_bps, 'nama' => $kecamatan->name],
            'village' => $village ? ['id' => $village->id, 'nama' => $village->nama] : null,
        ]);
    }

    /** GET /api/geocode/search — forward geocoding via Nominatim (FR-GEO-06) */
    public function search(Request $request)
    {
        $q = $request->validate(['q' => 'required|string|min:3'])['q'];
        try {
            $res = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'dash-sektoral-bandung/1.0'])
                ->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $q.', Kabupaten Bandung', 'format' => 'json', 'limit' => 5,
                ]);

            return response()->json($res->json());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Geocoding tidak tersedia: '.$e->getMessage()], 502);
        }
    }

    /** GET /api/facilities/nearby — preview existing radius 100m (FR-GEO-09) */
    public function nearby(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric', 'longitude' => 'required|numeric',
            'radius_m' => 'nullable|numeric|min:10|max:5000',
        ]);
        $radius = $data['radius_m'] ?? 100;
        // Haversine sederhana (meter)
        $lat = (float) $data['latitude'];
        $lng = (float) $data['longitude'];
        $found = Facility::select('id', 'name', 'type', 'latitude', 'longitude')
            ->get()->filter(function ($f) use ($lat, $lng, $radius) {
                $d = 6371000 * acos(min(1, cos(deg2rad($lat)) * cos(deg2rad($f->latitude))
                    * cos(deg2rad($f->longitude) - deg2rad($lng)) + sin(deg2rad($lat)) * sin(deg2rad($f->latitude))));

                return $d <= $radius;
            })->values();

        return response()->json(['count' => $found->count(), 'data' => $found, 'warning' => $found->isNotEmpty() ? 'Ada '.$found->count().' data dalam radius '.$radius.'m — cek duplikasi.' : null]);
    }
}
