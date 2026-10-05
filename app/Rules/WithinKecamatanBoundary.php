<?php

namespace App\Rules;

use App\Models\Kecamatan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * FR-GEO validasi: titik harus di dalam batas kecamatan user.
 * SQLite/MySQL ringan: cek bounding box. Di PostgreSQL/PostGIS
 * ganti dengan query ST_Contains (lihat plan.md §8.5).
 */
class WithinKecamatanBoundary implements ValidationRule
{
    public function __construct(private ?int $kecamatanId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lat = request('latitude', $attribute === 'latitude' ? $value : null);
        $lng = request('longitude', $attribute === 'longitude' ? $value : null);
        if ($lat === null || $lat === '' || $lng === null || $lng === '') {
            return;
        }
        $lat = (float) $lat;
        $lng = (float) $lng;
        if (!$this->kecamatanId) {
            return; // super admin tanpa kecamatan: skip
        }
        $kec = Kecamatan::find($this->kecamatanId);
        if (!$kec) {
            return;
        }
        // PostgreSQL/PostGIS: presisi penuh via ST_Contains (plan.md §8.5)
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            try {
                $row = \Illuminate\Support\Facades\DB::selectOne(
                    'SELECT ST_Contains(geometry, ST_SetSRID(ST_MakePoint(?, ?), 4326)) AS inside FROM kecamatans WHERE id = ?',
                    [$lng, $lat, $this->kecamatanId]
                );
                if ($row && !$row->inside) {
                    $fail('Lokasi berada di luar wilayah kecamatan Anda ('.$kec->namaSingkat().').');
                }

                return;
            } catch (\Throwable) {
                // fallback ke poligon GeoJSON bila kolom geometry belum ada
            }
        }
        // Presisi poligon asli public/maps/kecamatan.json (ray-casting)
        $inside = \App\Support\Geo::containsKecamatan($kec->namaSingkat(), $lat, $lng);
        if ($inside === null) {
            $inside = $kec->containsPoint($lat, $lng); // fallback bbox
        }
        if (!$inside) {
            $fail('Lokasi berada di luar wilayah kecamatan Anda ('.$kec->namaSingkat().').');
        }
    }
}
