<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $fillable = ['kecamatan_id', 'title', 'content', 'reasoning', 'engine', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
