<?php

namespace App\Http\Controllers\Sektoral;

use App\Http\Requests\Sektoral\StoreFacilityRequest;
use App\Models\Facility;
use App\Models\FacilityType;
use App\Models\Kecamatan;
use App\Models\Village;
use App\Support\Audit;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    private function authorizeWrite(): void
    {
        abort_unless(auth()->user()->canWrite(), 403, 'Hanya Super Admin / Admin Operator yang dapat input data.');
    }

    public function index(Request $request)
    {
        $modul = $request->get('modul');
        $query = Facility::with(['village', 'kecamatan'])->latest();
        if ($modul) {
            $query->where('modul', $modul);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }
        $facilities = $query->paginate(15)->withQueryString();

        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->limit(5000)->get());
        }

        return view('sektoral.facilities.index', [
            'facilities' => $facilities,
            'modul' => $modul,
            'modules' => Facility::MODULES,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeWrite();
        $user = auth()->user();
        $kecamatanId = $user->isSuperAdmin() ? $request->get('kecamatan_id') : $user->kecamatan_id;
        $kecamatan = $kecamatanId ? Kecamatan::find($kecamatanId) : null;

        return view('sektoral.facilities.form', [
            'facility' => new Facility(['modul' => $request->get('modul', 'M-04'), 'kecamatan_id' => $kecamatanId, 'status' => 'active', 'location_source' => 'map_click']),
            'kecamatans' => $user->isSuperAdmin() ? Kecamatan::orderBy('name')->get() : Kecamatan::where('id', $user->kecamatan_id)->get(),
            'villages' => $kecamatanId ? Village::where('kecamatan_id', $kecamatanId)->orderBy('nama')->get() : collect(),
            'types' => FacilityType::orderBy('nama')->get(),
            'modules' => Facility::MODULES,
            'kecamatan' => $kecamatan,
            'existing' => $kecamatanId ? Facility::where('kecamatan_id', $kecamatanId)->select('id', 'name', 'latitude', 'longitude')->limit(500)->get() : collect(),
        ]);
    }

    public function store(StoreFacilityRequest $request)
    {
        $facility = new Facility($request->validated());
        $facility->created_by = auth()->id();
        $facility->save();
        Audit::log('create', $facility, null, $facility->toArray());

        return redirect()->route('facilities.show', $facility)->with('success', 'Data tersimpan dengan koordinat.');
    }

    public function show(Facility $facility)
    {
        $facility->load(['village', 'kecamatan']);

        return view('sektoral.facilities.show', compact('facility'));
    }

    public function edit(Facility $facility)
    {
        $this->authorizeWrite();
        $user = auth()->user();

        return view('sektoral.facilities.form', [
            'facility' => $facility,
            'kecamatans' => $user->isSuperAdmin() ? Kecamatan::orderBy('name')->get() : Kecamatan::where('id', $user->kecamatan_id)->get(),
            'villages' => Village::where('kecamatan_id', $facility->kecamatan_id)->orderBy('nama')->get(),
            'types' => FacilityType::orderBy('nama')->get(),
            'modules' => Facility::MODULES,
            'kecamatan' => $facility->kecamatan,
            'existing' => Facility::where('kecamatan_id', $facility->kecamatan_id)->where('id', '!=', $facility->id)->select('id', 'name', 'latitude', 'longitude')->limit(500)->get(),
        ]);
    }

    public function update(StoreFacilityRequest $request, Facility $facility)
    {
        $old = $facility->toArray();
        $facility->update($request->validated());
        Audit::log('update', $facility, $old, $facility->fresh()->toArray());

        return redirect()->route('facilities.show', $facility)->with('success', 'Data diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        $this->authorizeWrite();
        $old = $facility->toArray();
        $facility->delete();
        Audit::log('delete', $facility, $old);

        return redirect()->route('facilities.index')->with('success', 'Data dihapus (soft delete).');
    }

    private function exportCsv($rows)
    {
        $out = fopen('php://temp', 'w');
        fputcsv($out, ['id', 'kecamatan', 'desa', 'nama', 'tipe', 'modul', 'latitude', 'longitude', 'status']);
        foreach ($rows as $f) {
            fputcsv($out, [$f->id, $f->kecamatan->name ?? '', $f->village->nama ?? '', $f->name, $f->type, $f->modul, $f->latitude, $f->longitude, $f->status]);
        }
        rewind($out);
        $csv = stream_get_contents($out);

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="fasilitas.csv"']);
    }
}
