<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const MODULES = [
        'M-03' => 'Pendidikan',
        'M-04' => 'Kesehatan',
        'M-05' => 'Pertanian & Tata Guna Lahan',
        'M-06' => 'Ekonomi Lokal',
        'M-07' => 'Sarana Sosial & Infrastruktur',
        'M-08' => 'KDMP & SPPG',
    ];

    protected $fillable = [
        'kecamatan_id', 'village_id', 'name', 'type', 'modul', 'address',
        'latitude', 'longitude', 'accuracy_meters', 'location_source',
        'coordinate_verified_at', 'coordinate_verified_by', 'status',
        'metadata', 'created_by',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'coordinate_verified_at' => 'datetime'];
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
