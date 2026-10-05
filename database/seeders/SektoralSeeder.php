<?php

namespace Database\Seeders;

use App\Models\DataEntry;
use App\Models\Facility;
use App\Models\FacilityType;
use App\Models\Indicator;
use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Village;
use App\Support\Geo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SektoralSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['operator', 'pimpinan', 'viewer'] as $r) {
            Role::findOrCreate($r);
        }

        $this->mergeProfiles();
        $this->seedVillages();
        $this->seedMasters();
        $this->seedSample();
        $this->seedDemoUsers();
    }

    private function short(Kecamatan $kec): string
    {
        return $kec->namaSingkat();
    }

    /** Profil wilayah: kode BPS, camat, bbox/center dari peta. Dicocokkan per nama. */
    private function mergeProfiles(): void
    {
        $rows = [
            ['32.04.44', 'Cangkuang'], ['32.04.20', 'Ciwidey'], ['32.04.42', 'Rancabali'],
            ['32.04.22', 'Pasirjambu'], ['32.04.23', 'Cimaung'], ['32.04.18', 'Pangalengan'],
            ['32.04.35', 'Kertasari'], ['32.04.30', 'Pacet'], ['32.04.26', 'Ciparay'],
            ['32.04.32', 'Baleendah'], ['32.04.16', 'Arjasari'], ['32.04.13', 'Banjaran'],
            ['32.04.33', 'Pameungpeuk'], ['32.04.11', 'Katapang'], ['32.04.37', 'Soreang'],
            ['32.04.43', 'Kutawaringin'], ['32.04.10', 'Margaasih'], ['32.04.12', 'Margahayu'],
            ['32.04.09', 'Dayeuhkolot'], ['32.04.08', 'Bojongsoang'], ['32.04.06', 'Cileunyi'],
            ['32.04.07', 'Cilengkrang'], ['32.04.05', 'Cimenyan'], ['32.04.24', 'Rancaekek'],
            ['32.04.28', 'Nagreg'], ['32.04.25', 'Cicalengka'], ['32.04.27', 'Cikancung'],
            ['32.04.15', 'Majalaya'], ['32.04.34', 'Solokanjeruk'], ['32.04.31', 'Ibun'],
            ['32.04.29', 'Paseh'],
        ];
        foreach ($rows as [$kode, $nama]) {
            $kec = Kecamatan::all()->first(fn($k) => Geo::norm($this->short($k)) === Geo::norm($nama));
            if (!$kec) continue;
            $feat = Geo::findKecamatan($nama);
            $data = ['kode_bps' => $kode, 'camat' => $kec->camat ?? 'Camat '.$nama];
            if ($feat) {
                [$minLat, $minLng, $maxLat, $maxLng] = Geo::bbox($feat['geometry']);
                [$lat, $lng] = Geo::centroid($feat['geometry']);
                $data += [
                    'center_lat' => $lat, 'center_lng' => $lng,
                    'min_lat' => $minLat, 'max_lat' => $maxLat,
                    'min_lng' => $minLng, 'max_lng' => $maxLng,
                    'luas_km2' => round(($feat['properties']['Luas_Ha'] ?? 0) / 100, 2) ?: null,
                ];
            }
            $kec->update($data);
        }
    }

    /** 279 desa riil dari peta. */
    private function seedVillages(): void
    {
        $byKec = [];
        foreach (Kecamatan::all() as $kec) {
            $byKec[Geo::norm($this->short($kec))][] = $kec;
            if ($kec->kode_bps) $byKec[Geo::norm($kec->kode_bps)][] = $kec;
        }
        $path = public_path('maps/desa.json');
        if (!file_exists($path)) return;
        $data = json_decode(file_get_contents($path), true);
        foreach ($data['features'] ?? [] as $f) {
            $p = $f['properties'] ?? [];
            $kode = str_replace(' ', '', $p['CODE'] ?? '');
            if (!$kode) continue;
            $kec = $byKec[Geo::norm($p['KECAMATAN'] ?? '')][0]
                ?? $byKec[Geo::norm(substr($kode, 0, 8))][0] ?? null;
            if (!$kec) continue;
            Village::updateOrCreate(['kode_bps' => $kode], [
                'kecamatan_id' => $kec->id,
                'nama' => $p['DESA'] ?? $kode,
                'luas_ha' => isset($p['Luas_Ha']) ? round((float) $p['Luas_Ha'], 2) : null,
            ]);
        }
    }

    private function seedMasters(): void
    {
        $indicators = [
            ['M-02', 'PEND_TOTAL', 'Jumlah Penduduk', 'jiwa', 'Total penduduk per kecamatan'],
            ['M-02', 'PEND_KK', 'Jumlah Kepala Keluarga', 'KK', null],
            ['M-02', 'PEND_PADAT', 'Kepadatan Penduduk', 'jiwa/km2', null],
            ['M-03', 'PEND_APS_SD', 'Angka Partisipasi Sekolah SD', '%', null],
            ['M-03', 'PEND_APS_SMP', 'Angka Partisipasi Sekolah SMP', '%', null],
            ['M-04', 'KES_STUNTING', 'Prevalensi Stunting', '%', null],
            ['M-04', 'KES_POSYANDU', 'Cakupan Posyandu Aktif', '%', null],
            ['M-05', 'TANI_LUAS', 'Luas Lahan Pertanian', 'ha', null],
            ['M-06', 'EKO_UMKM', 'Jumlah UMKM Aktif', 'unit', null],
            ['M-07', 'SOS_MASJID', 'Jumlah Tempat Ibadah', 'unit', null],
            ['M-08', 'KDMP_AKTIF', 'KDMP Aktif', 'unit', null],
        ];
        foreach ($indicators as [$modul, $kode, $nama, $satuan, $desk]) {
            Indicator::updateOrCreate(['kode' => $kode], compact('modul', 'nama', 'satuan') + ['deskripsi' => $desk]);
        }
        $types = [
            ['M-03', 'sd', 'Sekolah Dasar'], ['M-03', 'smp', 'SMP'], ['M-03', 'pontren', 'Pondok Pesantren'],
            ['M-04', 'puskesmas', 'Puskesmas'], ['M-04', 'posyandu', 'Posyandu'], ['M-04', 'klinik', 'Klinik'],
            ['M-05', 'sentra_tani', 'Sentra Komoditas'], ['M-05', 'lahan', 'Lahan Pertanian'],
            ['M-06', 'umkm', 'UMKM'], ['M-06', 'pasar', 'Pasar'], ['M-06', 'koperasi', 'Koperasi'],
            ['M-07', 'masjid', 'Masjid/Mushola'], ['M-07', 'olahraga', 'Sarana Olahraga'], ['M-07', 'bts', 'Menara BTS'],
            ['M-08', 'kdmp', 'KDMP'], ['M-08', 'sppg', 'SPPG'],
        ];
        foreach ($types as [$modul, $slug, $nama]) {
            FacilityType::updateOrCreate(['slug' => $slug], compact('modul', 'nama'));
        }
    }

    private function seedSample(): void
    {
        $cku = Kecamatan::where('kode_bps', '32.04.44')->first();
        if (!$cku) return;
        $samples = [
            ['posyandu', 'M-04', 'Posyandu Melati', 'Bandasari'],
            ['sd', 'M-03', 'SDN Cangkuang 01', 'Cangkuang'],
            ['umkm', 'M-06', 'UMKM Kopi Cangkuang', 'Pananjung'],
            ['masjid', 'M-07', 'Masjid Al-Ikhlas', 'Ciluncat'],
            ['kdmp', 'M-08', 'KDMP Bandasari', 'Bandasari'],
        ];
        foreach ($samples as [$type, $modul, $nama, $desaNama]) {
            $desa = Village::where('kecamatan_id', $cku->id)->where('nama', $desaNama)->first();
            Facility::firstOrCreate(['name' => $nama, 'kecamatan_id' => $cku->id], [
                'village_id' => $desa?->id, 'type' => $type, 'modul' => $modul,
                'address' => 'Jl. Contoh No. 1, '.$desaNama,
                'latitude' => $cku->center_lat + rand(-400, 400) / 10000,
                'longitude' => $cku->center_lng + rand(-400, 400) / 10000,
                'location_source' => 'map_click', 'status' => 'active',
            ]);
        }
        $ind = Indicator::where('kode', 'PEND_TOTAL')->first();
        if ($ind) {
            foreach ([2024 => 81230, 2025 => 82410, 2026 => 83650] as $year => $val) {
                DataEntry::firstOrCreate([
                    'kecamatan_id' => $cku->id, 'indicator_id' => $ind->id, 'year' => $year, 'village_id' => null,
                ], ['value' => $val]);
            }
        }
    }

    private function seedDemoUsers(): void
    {
        $cku = Kecamatan::where('kode_bps', '32.04.44')->first();
        $mk = function (string $email, string $name, string $role, $kecId) {
            $u = User::updateOrCreate(['email' => $email], [
                'name' => $name, 'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'kecamatan_id' => $kecId,
            ]);
            $u->syncRoles([$role]);
            return $u;
        };
        $mk('superadmin@bandung.go.id', 'Super Admin Kabupaten', 'superadmin', null);
        $mk('operator@cangkuang.go.id', 'Operator Cangkuang', 'operator', $cku?->id);
        $mk('pimpinan@bandung.go.id', 'Pimpinan Daerah', 'pimpinan', null);
        $mk('viewer@cangkuang.go.id', 'Viewer Cangkuang', 'viewer', $cku?->id);
    }
}
