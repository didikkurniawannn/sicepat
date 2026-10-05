<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\Kecamatan;
use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function store(Request $request, Kecamatan $kecamatan)
    {
        abort_unless(auth()->user()->canWrite(), 403);
        $data = $request->validate([
            'kode_bps' => 'required|max:15|unique:villages,kode_bps',
            'nama' => 'required|max:100', 'luas_ha' => 'nullable|numeric',
        ]);
        Village::create($data + ['kecamatan_id' => $kecamatan->id]);

        return back()->with('success', 'Desa ditambahkan.');
    }

    public function destroy(Village $village)
    {
        abort_unless(auth()->user()->canWrite(), 403);
        $village->delete();

        return back()->with('success', 'Desa dihapus.');
    }
}
