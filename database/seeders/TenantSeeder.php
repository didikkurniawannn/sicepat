<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantSeeder extends Seeder
{
    public const PILOT_CODE = 'CKU'; // Cangkuang — pemilik data existing

    public static function daftar(): array
    {
        return [
            ['ARJ', 'Arjasari', 'arjasari'], ['BLE', 'Baleendah', 'baleendah'],
            ['BJR', 'Banjaran', 'banjaran'], ['BJS', 'Bojongsoang', 'bojongsoang'],
            ['CKU', 'Cangkuang', 'cangkuang'], ['CCL', 'Cicalengka', 'cicalengka'],
            ['CKC', 'Cikancung', 'cikancung'], ['CLR', 'Cilengkrang', 'cilengkrang'],
            ['CLY', 'Cileunyi', 'cileunyi'], ['CMU', 'Cimaung', 'cimaung'],
            ['CMY', 'Cimenyan', 'cimenyan'], ['CPR', 'Ciparay', 'ciparay'],
            ['CWD', 'Ciwidey', 'ciwidey'], ['DKL', 'Dayeuhkolot', 'dayeuhkolot'],
            ['IBN', 'Ibun', 'ibun'], ['KTP', 'Katapang', 'katapang'],
            ['KRT', 'Kertasari', 'kertasari'], ['KTW', 'Kutawaringin', 'kutawaringin'],
            ['MGH', 'Margaasih', 'margaasih'], ['MHY', 'Margahayu', 'margahayu'],
            ['MJL', 'Majalaya', 'majalaya'], ['NGR', 'Nagreg', 'nagreg'],
            ['PCT', 'Pacet', 'pacet'], ['PMP', 'Pameungpeuk', 'pameungpeuk'],
            ['PGL', 'Pangalengan', 'pangalengan'], ['PSH', 'Paseh', 'paseh'],
            ['PSJ', 'Pasirjambu', 'pasirjambu'], ['RCB', 'Rancabali', 'rancabali'],
            ['RCK', 'Rancaekek', 'rancaekek'], ['SLK', 'Solokanjeruk', 'solokanjeruk'],
            ['SRG', 'Soreang', 'soreang'],
        ];
    }

    public function run(): void
    {
        foreach (['admin', 'kasi', 'staf', 'superadmin', 'operator', 'pimpinan', 'viewer'] as $r) {
            Role::findOrCreate($r);
        }

        foreach (self::daftar() as $i => [$code, $name, $slug]) {
            Kecamatan::updateOrCreate(['code' => $code], [
                'name' => 'Kecamatan '.$name, 'slug' => $slug, 'order' => $i + 1,
            ]);
        }

        // Data existing (tanpa tenant) menjadi milik kecamatan perintis
        $pilot = Kecamatan::where('code', self::PILOT_CODE)->first();
        if ($pilot) {
            User::whereNull('kecamatan_id')->update(['kecamatan_id' => $pilot->id]);
            Activity::whereNull('kecamatan_id')->update(['kecamatan_id' => $pilot->id]);
        }

        // Superadmin lintas kecamatan
        $su = User::firstOrCreate(['email' => 'superadmin@sicepatkeg.local'], [
            'name' => 'Superadmin', 'password' => Hash::make('password123'),
        ]);
        $su->syncRoles(['superadmin']);

        // Admin per kecamatan (kecuali yang sudah punya admin)
        $sbu = \App\Models\Section::where('code', 'SBU')->first();
        foreach (Kecamatan::all() as $kec) {
            $hasAdmin = User::role('admin')->where('kecamatan_id', $kec->id)->exists();
            if ($hasAdmin) continue;
            $u = User::firstOrCreate(['email' => 'admin-'.$kec->slug.'@sicepatkeg.local'], [
                'name' => 'Admin Kec. '.$kec->name,
                'password' => Hash::make('password123'),
                'section_id' => $sbu?->id,
                'kecamatan_id' => $kec->id,
            ]);
            $u->syncRoles(['admin']);
        }

        // Admin tenant sekaligus operator Data Sektoral di wilayahnya
        foreach (User::role('admin')->whereNotNull('kecamatan_id')->get() as $admin) {
            if (!$admin->hasRole('operator')) {
                $admin->assignRole('operator');
            }
        }
    }
}
