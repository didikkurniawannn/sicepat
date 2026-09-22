<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['code','name','short_name','type','color','head_name','is_active','order'];

    protected $casts = ['is_active' => 'boolean'];

    public function activities(): HasMany { return $this->hasMany(Activity::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
}
