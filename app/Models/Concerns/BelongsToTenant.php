<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Isolasi tenant otomatis (diadaptasi ke Spatie Permission).
 * - superadmin: bypass
 * - role lain: scope ke kecamatan_id user (bila ada)
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return;
            }
            if ($user->kecamatan_id) {
                $builder->where($builder->getModel()->getTable().'.kecamatan_id', $user->kecamatan_id);
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && empty($model->kecamatan_id)) {
                $user = auth()->user();
                if ($user->kecamatan_id && !$user->isSuperAdmin()) {
                    $model->kecamatan_id = $user->kecamatan_id;
                }
            }
        });
    }

    public function kecamatan()
    {
        return $this->belongsTo(\App\Models\Kecamatan::class);
    }
}
