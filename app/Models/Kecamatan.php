<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $fillable = ['code', 'name', 'slug', 'is_active', 'order'];
    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function activities(): HasMany { return $this->hasMany(Activity::class); }
}
