<?php

namespace App\Http\Controllers\Sektoral;

use App\Http\Requests\Sektoral\StoreDataEntryRequest;
use App\Models\DataEntry;
use App\Models\Indicator;
use App\Models\Kecamatan;
use App\Models\Village;
use App\Support\Audit;
use Illuminate\Http\Request;

class DataEntryController extends Controller
{
    private function authorizeWrite(): void
    {
        abort_unless(auth()->user()->canWrite(), 403, 'Hanya Super Admin / Admin Operator yang dapat input data.');
    }

    public function index(Request $request)
    {
        $q = DataEntry::with(['indicator', 'village', 'kecamatan'])->latest();
        if ($request->filled('indicator_id')) {
            $q->where('indicator_id', $request->indicator_id);
        }
        if ($request->filled('year')) {
            $q->where('year', $request->year);
        }
        if ($request->get('export') === 'csv') {
            $out = fopen('php://temp', 'w');
            fputcsv($out, ['id', 'kecamatan', 'indikator', 'tahun', 'nilai', 'lat', 'lng']);
            foreach ($q->limit(5000)->get() as $e) {
                fputcsv($out, [$e->id, $e->kecamatan->name ?? '', $e->indicator->kode ?? '', $e->year, $e->value, $e->latitude, $e->longitude]);
            }
            rewind($out);

            return response(stream_get_contents($out), 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="data-sektoral.csv"']);
        }

        return view('sektoral.data_entries.index', [
            'entries' => $q->paginate(15)->withQueryString(),
            'indicators' => Indicator::orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        $this->authorizeWrite();
        $user = auth()->user();
        $kecId = $user->isSuperAdmin() ? null : $user->kecamatan_id;

        return view('sektoral.data_entries.form', [
            'entry' => new DataEntry(['year' => date('Y'), 'kecamatan_id' => $kecId]),
            'kecamatans' => $user->isSuperAdmin() ? Kecamatan::orderBy('name')->get() : Kecamatan::where('id', $kecId)->get(),
            'indicators' => Indicator::orderBy('nama')->get(),
            'villages' => $kecId ? Village::where('kecamatan_id', $kecId)->orderBy('nama')->get() : collect(),
        ]);
    }

    public function store(StoreDataEntryRequest $request)
    {
        $entry = DataEntry::create($request->validated() + ['created_by' => auth()->id()]);
        Audit::log('create', $entry, null, $entry->toArray());

        return redirect()->route('data-entries.index')->with('success', 'Data sektoral tersimpan.');
    }

    public function edit(DataEntry $dataEntry)
    {
        $this->authorizeWrite();
        $user = auth()->user();

        return view('sektoral.data_entries.form', [
            'entry' => $dataEntry,
            'kecamatans' => $user->isSuperAdmin() ? Kecamatan::orderBy('name')->get() : Kecamatan::where('id', $dataEntry->kecamatan_id)->get(),
            'indicators' => Indicator::orderBy('nama')->get(),
            'villages' => Village::where('kecamatan_id', $dataEntry->kecamatan_id)->orderBy('nama')->get(),
        ]);
    }

    public function update(StoreDataEntryRequest $request, DataEntry $dataEntry)
    {
        $old = $dataEntry->toArray();
        $dataEntry->update($request->validated());
        Audit::log('update', $dataEntry, $old, $dataEntry->fresh()->toArray());

        return redirect()->route('data-entries.index')->with('success', 'Data diperbarui.');
    }

    public function destroy(DataEntry $dataEntry)
    {
        $this->authorizeWrite();
        $old = $dataEntry->toArray();
        $dataEntry->delete();
        Audit::log('delete', $dataEntry, $old);

        return redirect()->route('data-entries.index')->with('success', 'Data dihapus.');
    }
}
