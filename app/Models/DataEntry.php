<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class DataEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kecamatan_id', 'village_id', 'indicator_id', 'year', 'value',
        'latitude', 'longitude', 'metadata', 'created_by',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
