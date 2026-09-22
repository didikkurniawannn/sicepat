<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Penyederhanaan role:
     * - pptk dilebur ke kasi (Kasi merangkap tugas PPTK: persiapan H-7 & update progress)
     * - verifikator & pimpinan dihapus, diakomodir admin (verifikasi + approve final)
     * Aman dijalankan di production: hanya memindahkan role user, tanpa menghapus data.
     */
    public function up(): void
    {
        foreach (['admin', 'kasi', 'staf'] as $name) {
            Role::findOrCreate($name);
        }

        $move = function (string $from, string $to) {
            if (!Role::where('name', $from)->exists()) return;
            foreach (User::role($from)->get() as $user) {
                $user->syncRoles([$to]);
            }
        };

        $move('pptk', 'kasi');
        $move('verifikator', 'admin');
        $move('pimpinan', 'admin');

        $staleIds = Role::whereIn('name', ['pptk', 'verifikator', 'pimpinan'])->pluck('id');
        if ($staleIds->isNotEmpty()) {
            DB::table('model_has_roles')->whereIn('role_id', $staleIds)->delete();
            DB::table('role_has_permissions')->whereIn('role_id', $staleIds)->delete();
            Role::whereIn('id', $staleIds)->delete();
        }
    }

    public function down(): void
    {
        foreach (['pptk', 'verifikator', 'pimpinan'] as $name) {
            Role::findOrCreate($name);
        }
    }
};
