<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    private function guard(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya superadmin.');
    }

    public function index()
    {
        $this->guard();
        $kecamatans = Kecamatan::orderBy('order')->get()->map(function ($k) {
            $k->jml_kegiatan = \App\Models\Activity::where('kecamatan_id', $k->id)->count();
            $k->jml_user = \App\Models\User::where('kecamatan_id', $k->id)->count();
            return $k;
        });
        return view('tenants.index', compact('kecamatans'));
    }

    public function store(Request $request)
    {
        $this->guard();
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:kecamatans,code',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kecamatans,slug',
        ]);
        $data['order'] = (Kecamatan::max('order') ?? 0) + 1;
        Kecamatan::create($data);
        return back()->with('success', 'Kecamatan ditambahkan.');
    }

    public function toggle(Kecamatan $kecamatan)
    {
        $this->guard();
        $kecamatan->update(['is_active' => !$kecamatan->is_active]);
        return back()->with('success', $kecamatan->name.($kecamatan->is_active ? ' diaktifkan.' : ' dinonaktifkan.'));
    }
}
