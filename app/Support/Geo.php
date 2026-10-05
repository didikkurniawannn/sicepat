<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Pembaca batas wilayah dari public/maps/*.json (GeoJSON WGS84).
 * Dipakai untuk: centroid seeder, validasi titik presisi (pengganti
 * bbox kasar), API boundary, dan dashboard GIS.
 */
class Geo
{
    /** Alias nama DB -> nama di GeoJSON. */
    public const NAMA_ALIAS = [
        'Solokanjeruk' => 'Solokan Jeruk',
    ];

    public static function geoName(string $namaDb): string
    {
        return self::NAMA_ALIAS[$namaDb] ?? $namaDb;
    }

    /** Normalisasi untuk pencocokan: lowercase + tanpa spasi. */
    public static function norm(string $nama): string
    {
        return str_replace(' ', '', mb_strtolower(trim($nama)));
    }

    private static function load(string $file): array
    {
        $path = public_path('maps/'.$file);
        $key = 'geojson:'.$file.':'.@filemtime($path);

        return Cache::remember($key, 3600, function () use ($path) {
            return json_decode(file_get_contents($path), true) ?? [];
        });
    }

    public static function kecamatans(): array
    {
        return self::load('kecamatan.json')['features'] ?? [];
    }

    public static function findKecamatan(string $namaDb): ?array
    {
        $target = self::norm(self::geoName($namaDb));
        foreach (self::kecamatans() as $f) {
            if (self::norm($f['properties']['KECAMATAN'] ?? '') === $target) {
                return $f;
            }
        }

        return null;
    }

    public static function desaByKecamatan(string $namaDb): array
    {
        $target = self::norm(self::geoName($namaDb));
        $out = [];
        foreach ((self::load('desa.json')['features'] ?? []) as $f) {
            if (self::norm($f['properties']['KECAMATAN'] ?? '') === $target) {
                $out[] = $f;
            }
        }

        return $out;
    }

    /** Bounding box [minLat, minLng, maxLat, maxLng] dari geometry. */
    public static function bbox(array $geometry): array
    {
        $minLat = $minLng = PHP_FLOAT_MAX;
        $maxLat = $maxLng = -PHP_FLOAT_MAX;
        foreach (self::rings($geometry) as $ring) {
            foreach ($ring as $c) {
                $minLng = min($minLng, $c[0]);
                $maxLng = max($maxLng, $c[0]);
                $minLat = min($minLat, $c[1]);
                $maxLat = max($maxLat, $c[1]);
            }
        }

        return [$minLat, $minLng, $maxLat, $maxLng];
    }

    /** Centroid kasar (rata-rata titik ring terluar) — cukup untuk zoom peta. */
    public static function centroid(array $geometry): array
    {
        $rings = self::rings($geometry);
        $outer = collect($rings)->sortByDesc(fn ($r) => count($r))->first() ?? [];
        if (!$outer) {
            return [-7.03, 107.6];
        }
        $n = count($outer);

        return [
            collect($outer)->avg(fn ($c) => $c[1]),
            collect($outer)->avg(fn ($c) => $c[0]),
        ];
    }

    /** Ray-casting point-in-polygon. Mendukung Polygon & MultiPolygon. */
    public static function contains(array $geometry, float $lat, float $lng): bool
    {
        $type = $geometry['type'] ?? '';
        $polys = $type === 'MultiPolygon' ? ($geometry['coordinates'] ?? []) : [$geometry['coordinates'] ?? []];
        foreach ($polys as $poly) {
            if (empty($poly)) {
                continue;
            }
            if (self::ringContains($poly[0], $lng, $lat)) {
                // pastikan tidak di dalam hole
                $inHole = false;
                foreach (array_slice($poly, 1) as $hole) {
                    if (self::ringContains($hole, $lng, $lat)) {
                        $inHole = true;
                        break;
                    }
                }
                if (!$inHole) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function containsKecamatan(string $namaDb, float $lat, float $lng): ?bool
    {
        $f = self::findKecamatan($namaDb);
        if (!$f) {
            return null;
        }

        return self::contains($f['geometry'], $lat, $lng);
    }

    private static function rings(array $geometry): array
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];
        if ($type === 'MultiPolygon') {
            $out = [];
            foreach ($coords as $poly) {
                foreach ($poly as $ring) {
                    $out[] = $ring;
                }
            }

            return $out;
        }

        return $coords; // Polygon: [outer, hole...]
    }

    private static function ringContains(array $ring, float $x, float $y): bool
    {
        $inside = false;
        $n = count($ring);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $xj = $xj ?? $xi;
            $yj = $ring[$j][1] ?? $yi;
            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
