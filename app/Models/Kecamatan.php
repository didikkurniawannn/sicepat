<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $fillable = [
        'code', 'name', 'slug', 'is_active', 'order',
        'kode_bps', 'camat', 'alamat', 'telepon', 'email',
        'luas_km2', 'jumlah_penduduk', 'center_lat', 'center_lng',
        'min_lat', 'max_lat', 'min_lng', 'max_lng',
    ];
    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function activities(): HasMany { return $this->hasMany(Activity::class); }
    public function villages(): HasMany { return $this->hasMany(Village::class); }
    public function facilities(): HasMany { return $this->hasMany(Facility::class); }

    /** Nama pendek tanpa prefiks "Kecamatan " (mis. "Kecamatan Cangkuang" -> "Cangkuang"). */
    public function namaSingkat(): string
    {
        return trim((string) preg_replace('/^Kecamatan\s+/i', '', $this->name));
    }

    /** Cek titik dalam bbox kecamatan (pengganti ringan ST_Contains). */
    public function containsPoint(float $lat, float $lng): bool
    {
        if ($this->min_lat === null) {
            return true;
        }

        return $lat >= (float) $this->min_lat && $lat <= (float) $this->max_lat
            && $lng >= (float) $this->min_lng && $lng <= (float) $this->max_lng;
    }
}
