<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityChecklist;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['code'=>'SPR','name'=>'Seksi Pemerintahan','short_name'=>'Pem','type'=>'seksi','color'=>'#3B82F6','order'=>1,'head_name'=>'Kasi Pemerintahan'],
            ['code'=>'SPB','name'=>'Seksi Pembangunan','short_name'=>'Bang','type'=>'seksi','color'=>'#10B981','order'=>2,'head_name'=>'Kasi Pembangunan'],
            ['code'=>'SSB','name'=>'Seksi Sosial dan Budaya','short_name'=>'Sosbud','type'=>'seksi','color'=>'#F59E0B','order'=>3,'head_name'=>'Kasi Sosial dan Budaya'],
            ['code'=>'SPM','name'=>'Seksi Pemberdayaan Masyarakat','short_name'=>'PM','type'=>'seksi','color'=>'#8B5CF6','order'=>4,'head_name'=>'Kasi Pemberdayaan Masyarakat'],
            ['code'=>'SKT','name'=>'Seksi Keamanan dan Ketertiban Umum','short_name'=>'Trantib','type'=>'seksi','color'=>'#EF4444','order'=>5,'head_name'=>'Kasi Trantibum'],
            ['code'=>'TMV','name'=>'Tim Monev Kecamatan','short_name'=>'Monev','type'=>'tim','color'=>'#06B6D4','order'=>6,'head_name'=>'Ketua Tim Monev'],
            ['code'=>'SBU','name'=>'Sub Bagian Umum dan Kepegawaian','short_name'=>'Umpeg','type'=>'sub_bagian','color'=>'#6B7280','order'=>7,'head_name'=>'Kasubbag Umum dan Kepegawaian'],
        ];
        foreach ($sections as $s) {
            Section::updateOrCreate(['code' => $s['code']], $s);
        }

        foreach (['admin', 'kasi', 'staf'] as $r) {
            Role::findOrCreate($r);
        }

        $secByName = Section::all()->keyBy('name');
        $secByCode = Section::all()->keyBy('code');

        $mkUser = function (string $name, string $email, string $role, ?string $secCode) use ($secByCode) {
            $u = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password123'),
                'section_id' => $secCode ? $secByCode[$secCode]->id : $secByCode['SBU']->id,
            ]);
            $u->syncRoles([$role]);
            return $u;
        };

        $admin = $mkUser('Administrator', 'admin@sicepatkeg.local', 'admin', 'SBU');
        $mkUser('Staf Umum', 'staf@sicepatkeg.local', 'staf', 'SBU');

        foreach ($secByCode as $code => $sec) {
            // Kasi merangkap tugas PPTK (persiapan H-7 & update progress)
            $mkUser('Kasi '.$sec->short_name.' ('.$code.')', strtolower($code).'.kasi@sicepatkeg.local', 'kasi', $code);
        }

        // Seed activities dari Data Kegiatan.xlsx
        $jsonPath = database_path('seeders/activities_seed.json');
        $rows = json_decode(file_get_contents($jsonPath), true);
        $checklistItems = ['Dokumen TOR/KAK tersedia', 'RAB/anggaran tersedia', 'SDM/PPTK siap', 'Jadwal & lokasi fix'];
        $today = now()->startOfDay();

        foreach ($rows as $row) {
            $section = $secByName[$row['section']] ?? null;
            if (!$section) continue;
            $pptk = User::role('kasi')->where('section_id', $section->id)->first();
            $date = \Carbon\Carbon::parse($row['activity_date']);
            // Status heuristik: realisasi>0 & tanggal lewat => selesai/berjalan, else draft
            if (($row['budget_realization'] ?? 0) > 0) {
                $status = $date->lt($today) ? 'selesai' : 'berjalan';
                $progress = $date->lt($today) ? 100 : 60;
            } elseif ($date->lt($today)) {
                $status = 'selesai';
                $progress = 100;
            } else {
                $status = 'disetujui';
                $progress = 10;
            }

            $act = Activity::where('account_code', $row['account_code'])
                ->where('title', $row['title'])
                ->whereDate('activity_date', $row['activity_date'])
                ->first() ?? new Activity([
                    'account_code' => $row['account_code'],
                    'title' => $row['title'],
                    'activity_date' => $row['activity_date'],
                ]);
            $act->fill([
                    'section_id' => $section->id,
                    'program_name' => $row['program_name'],
                    'requirement_qty' => $row['requirement_qty'],
                    'total_qty' => $row['total_qty'],
                    'unit' => $row['unit'],
                    'budget_pagu' => $row['budget_pagu'],
                    'budget_realization' => $row['budget_realization'],
                    'pptk_id' => $pptk?->id,
                    'status' => $status,
                    'progress' => $progress,
                    'created_by' => $admin->id,
                ]
            );
            $act->save();

            foreach ($checklistItems as $item) {
                ActivityChecklist::firstOrCreate(
                    ['activity_id' => $act->id, 'item' => $item],
                    ['is_checked' => in_array($status, ['disetujui','berjalan','selesai'])]
                );
            }
        }
    }
}
