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
     * Merapikan akun demo peninggalan struktur role lama (hanya menyentuh email demo
     * yang sudah dikenal — akun asli buatan user TIDAK tersentuh):
     * - *.pptk@sicepatkeg.local → kegiatannya dialihkan ke Kasi seunitnya, lalu dihapus
     * - verifikator@... & pimpinan@... → riwayatnya dialihkan ke admin, lalu dihapus
     * Hasil akhir: admin + 7 Kasi (+ staf bila ada). Data kegiatan utuh.
     */
    public function up(): void
    {
        $admin = User::where('email', 'admin@sicepatkeg.local')->first();

        $takeOver = function (?User $legacy, ?User $heir) {
            if (!$legacy || !$heir || $legacy->id === $heir->id) return false;
            Activity::where('pptk_id', $legacy->id)->update(['pptk_id' => $heir->id]);
            Activity::where('created_by', $legacy->id)->update(['created_by' => $heir->id]);
            Verification::where('user_id', $legacy->id)->update(['user_id' => $heir->id]);
            ActivityLog::where('user_id', $legacy->id)->update(['user_id' => $heir->id]);
            AppNotification::where('user_id', $legacy->id)->delete();
            $legacy->syncRoles([]);
            $legacy->delete();
            return true;
        };

        foreach (Section::all() as $sec) {
            $code = strtolower($sec->code);
            $takeOver(
                User::where('email', $code.'.pptk@sicepatkeg.local')->first(),
                User::where('email', $code.'.kasi@sicepatkeg.local')->first()
            );
        }

        if ($admin) {
            foreach (['verifikator@sicepatkeg.local', 'pimpinan@sicepatkeg.local'] as $email) {
                $takeOver(User::where('email', $email)->first(), $admin);
            }
        }
    }

    public function down(): void
    {
        // Tidak dapat dikembalikan (akun yang dihapus tidak bisa direkonstruksi otomatis).
    }
};
