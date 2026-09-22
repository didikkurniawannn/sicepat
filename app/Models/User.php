<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name','email','password','section_id','phone','is_active'];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function pptkActivities() { return $this->hasMany(Activity::class, 'pptk_id'); }
    public function notifications() { return $this->hasMany(AppNotification::class)->latest(); }
    public function unreadNotifications() { return $this->hasMany(AppNotification::class)->whereNull('read_at'); }
}
