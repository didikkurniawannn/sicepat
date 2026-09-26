<?php

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Section;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Mengalihkan SEMUA hal Tim Monev (TMV) ke Seksi Pemerintahan (SPR):
     * - 14 kegiatan TMV pindah ke SPR (penanggung jawab dialihkan ke Kasi SPR)
     * - user seksi TMV pindah ke SPR
     * - akun demo tmv.kasi@... dihapus (tugasnya dialihkan ke spr.kasi@...)
     * - unit TMV dinonaktifkan (tidak muncul di pilihan/filter)
     * Aman untuk production: hanya memindahkan, tanpa menghapus kegiatan.
     */
    public function up(): void
    {
        $tmv = Section::where('code', 'TMV')->first();
        $spr = Section::where('code', 'SPR')->first();
        if (!$tmv || !$spr) return;

        $sprKasi = User::where('email', 'spr.kasi@sicepatkeg.local')->first();
        $tmvKasi = User::where('email', 'tmv.kasi@sicepatkeg.local')->first();

        // 1. Kegiatan TMV → SPR
        Activity::where('section_id', $tmv->id)->update(['section_id' => $spr->id]);
        if ($sprKasi && $tmvKasi) {
            Activity::where('pptk_id', $tmvKasi->id)->update(['pptk_id' => $sprKasi->id]);
        }

        // 2. User seksi TMV → SPR
        User::where('section_id', $tmv->id)->update(['section_id' => $spr->id]);

        // 3. Hapus akun demo Monev (khusus email demo yang dikenal), alihkan jejaknya
        if ($tmvKasi && (!$sprKasi || $tmvKasi->id !== $sprKasi->id)) {
            if ($sprKasi) {
                Activity::where('created_by', $tmvKasi->id)->update(['created_by' => $sprKasi->id]);
                Verification::where('user_id', $tmvKasi->id)->update(['user_id' => $sprKasi->id]);
                ActivityLog::where('user_id', $tmvKasi->id)->update(['user_id' => $sprKasi->id]);
            }
            AppNotification::where('user_id', $tmvKasi->id)->delete();
            $tmvKasi->syncRoles([]);
            $tmvKasi->delete();
        }

        // 4. Nonaktifkan unit TMV
        $tmv->update(['is_active' => false]);
    }

    public function down(): void
    {
        $tmv = Section::where('code', 'TMV')->first();
        if ($tmv) $tmv->update(['is_active' => true]);
    }
};
