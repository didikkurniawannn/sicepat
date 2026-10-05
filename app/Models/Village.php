<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = ['kecamatan_id', 'kode_bps', 'nama', 'luas_ha'];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
