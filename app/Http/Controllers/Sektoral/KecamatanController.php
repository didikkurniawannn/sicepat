<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\DataEntry;
use App\Models\Facility;
use App\Models\Indicator;
use App\Models\Kecamatan;
use App\Models\Village;
use App\Support\Audit;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    public function index()
    {
        $this->authorizeSuper();
        $kecamatans = Kecamatan::withCount(['villages', 'facilities'])->orderBy('name')->paginate(15);

        return view('sektoral.kecamatans.index', compact('kecamatans'));
    }

    public function create()
    {
        $this->authorizeSuper();

        return view('sektoral.kecamatans.form', ['kecamatan' => new Kecamatan(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $this->authorizeSuper();
        $data = $request->validate([
            'kode_bps' => 'required|max:10|unique:kecamatans,kode_bps',
            'code' => 'required|max:10|unique:kecamatans,code',
            'slug' => 'required|max:255|unique:kecamatans,slug',
            'nama' => 'required|max:100', 'camat' => 'nullable|max:100',
            'alamat' => 'nullable', 'telepon' => 'nullable|max:20', 'email' => 'nullable|email|max:100',
            'luas_km2' => 'nullable|numeric', 'jumlah_penduduk' => 'nullable|integer',
            'center_lat' => 'nullable|numeric|between:-90,90', 'center_lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
        ]);
        $data['name'] = self::fullName($data['nama']);
        unset($data['nama']);
        $data['is_active'] = $request->boolean('is_active', true);
        if (!empty($data['center_lat'])) {
            $data += ['min_lat' => $data['center_lat'] - 0.06, 'max_lat' => $data['center_lat'] + 0.06,
                      'min_lng' => $data['center_lng'] - 0.06, 'max_lng' => $data['center_lng'] + 0.06];
        }
        $kec = Kecamatan::create($data);
        Audit::log('create', $kec, null, $kec->toArray());

        return redirect()->route('kecamatans.show', $kec)->with('success', 'Tenant kecamatan dibuat.');
    }

    /** M-01 Profil Wilayah: detail + desa + statistik fasilitas */
    public function show(Kecamatan $kecamatan)
    {
        $this->scopeCheck($kecamatan->id);
        $kecamatan->loadCount(['villages', 'facilities']);
        $villages = Village::where('kecamatan_id', $kecamatan->id)->orderBy('nama')->get();
        $perModul = Facility::withoutGlobalScope('tenant')->where('kecamatan_id', $kecamatan->id)
            ->selectRaw('modul, COUNT(*) as total')->groupBy('modul')->pluck('total', 'modul');
        $markers = Facility::withoutGlobalScope('tenant')->where('kecamatan_id', $kecamatan->id)
            ->select('id', 'name', 'type', 'latitude', 'longitude')->limit(1000)->get();
        $entries = DataEntry::withoutGlobalScope('tenant')->with('indicator')
            ->where('kecamatan_id', $kecamatan->id)->orderByDesc('year')->limit(20)->get();

        return view('sektoral.kecamatans.show', compact('kecamatan', 'villages', 'perModul', 'markers', 'entries'));
    }

    public function edit(Kecamatan $kecamatan)
    {
        $this->authorizeSuper();

        return view('sektoral.kecamatans.form', ['kecamatan' => $kecamatan]);
    }

    public function update(Request $request, Kecamatan $kecamatan)
    {
        $this->authorizeSuper();
        $data = $request->validate([
            'nama' => 'required|max:100', 'camat' => 'nullable|max:100',
            'alamat' => 'nullable', 'telepon' => 'nullable|max:20', 'email' => 'nullable|email|max:100',
            'luas_km2' => 'nullable|numeric', 'jumlah_penduduk' => 'nullable|integer',
            'center_lat' => 'nullable|numeric|between:-90,90', 'center_lng' => 'nullable|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
        ]);
        $data['name'] = self::fullName($data['nama']);
        unset($data['nama']);
        $data['is_active'] = $request->boolean('is_active', true);
        $old = $kecamatan->toArray();
        $kecamatan->update($data);
        Audit::log('update', $kecamatan, $old, $kecamatan->fresh()->toArray());

        return redirect()->route('kecamatans.show', $kecamatan)->with('success', 'Profil tenant diperbarui.');
    }

    public function toggle(Kecamatan $kecamatan)
    {
        $this->authorizeSuper();
        $kecamatan->update(['is_active' => !$kecamatan->is_active]);

        return back()->with('success', 'Status tenant diubah.');
    }

    private function authorizeSuper(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Khusus Super Admin.');
    }

    /** Normalisasi nama pendek ("Cangkuang") ke nama resmi ("Kecamatan Cangkuang"). */
    private static function fullName(string $nama): string
    {
        $nama = trim($nama);
        return preg_match('/^kecamatan\s/i', $nama) ? $nama : 'Kecamatan '.$nama;
    }

    private function scopeCheck(int $kecId): void
    {
        $u = auth()->user();
        if (!$u->isSuperAdmin() && $u->kecamatan_id && $u->kecamatan_id !== $kecId) {
            abort(403, 'Di luar wilayah tenant Anda.');
        }
    }
}
