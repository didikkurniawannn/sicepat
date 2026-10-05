<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    public static function log(string $action, ?Model $model = null, $old = null, $new = null): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'kecamatan_id' => auth()->user()->kecamatan_id ?? $model?->getAttribute('kecamatan_id'),
                'action' => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model?->getKey(),
                'old_values' => $old ? json_decode(json_encode($old), true) : null,
                'new_values' => $new ? json_decode(json_encode($new), true) : null,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (\Throwable) {
            // audit tidak boleh menggagalkan transaksi utama
        }
    }
}
