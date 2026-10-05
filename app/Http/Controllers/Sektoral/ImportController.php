<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\DataEntry;
use App\Models\Facility;
use App\Models\Indicator;
use App\Models\Kecamatan;
use App\Models\Village;
use App\Rules\WithinKecamatanBoundary;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Import CSV massal untuk onboarding tenant (R-06 migrasi data existing).
 * Operator terkunci ke kecamatannya; Super Admin via kolom kecamatan_kode.
 */
class ImportController extends Controller
{
    public function facilityForm()
    {
        abort_unless(auth()->user()->canWrite(), 403);

        return view('sektoral.import.facilities');
    }

    public function facilityTemplate()
    {
        $rows = [
            ['name', 'type', 'modul', 'kecamatan_kode', 'village_kode', 'address', 'latitude', 'longitude', 'status'],
            ['Posyandu Melati', 'posyandu', 'M-04', '32.04.44', '32.04.44.2005', 'Jl. Contoh No.1', '-7.051234', '107.556789', 'active'],
        ];

        return $this->csv('template-fasilitas.csv', $rows);
    }

    public function facilityStore(Request $request)
    {
        abort_unless(auth()->user()->canWrite(), 403);
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $user = auth()->user();
        $ok = 0;
        $errors = [];

        foreach ($this->rows($request->file('file')) as $i => $row) {
            $line = $i + 2;
            $kecId = $user->isSuperAdmin()
                ? (Kecamatan::where('kode_bps', $row['kecamatan_kode'] ?? '')->value('id') ?? $user->kecamatan_id)
                : $user->kecamatan_id;
            $v = Validator::make($row + ['kecamatan_id' => $kecId], [
                'kecamatan_id' => 'required|exists:kecamatans,id',
                'name' => 'required|max:150',
                'type' => 'required|max:50',
                'modul' => 'required|in:M-03,M-04,M-05,M-06,M-07,M-08',
                'latitude' => ['required', 'numeric', 'between:-90,90', new WithinKecamatanBoundary((int) $kecId)],
                'longitude' => 'required|numeric|between:-180,180',
                'status' => 'nullable|in:active,inactive,pending',
            ]);
            if ($v->fails()) {
                $errors[] = "Baris {$line}: ".implode('; ', $v->errors()->all());
                continue;
            }
            $village = !empty($row['village_kode']) ? Village::where('kode_bps', $row['village_kode'])->where('kecamatan_id', $kecId)->first() : null;
            $f = Facility::create([
                'kecamatan_id' => $kecId, 'village_id' => $village?->id,
                'name' => $row['name'], 'type' => $row['type'], 'modul' => $row['modul'],
                'address' => $row['address'] ?? null,
                'latitude' => $row['latitude'], 'longitude' => $row['longitude'],
                'location_source' => 'manual_input', 'status' => $row['status'] ?? 'active',
                'created_by' => $user->id,
            ]);
            Audit::log('create', $f, null, ['import' => true, 'name' => $f->name]);
            $ok++;
        }

        return back()->with('success', "Import selesai: {$ok} berhasil, ".count($errors).' gagal.')->with('import_errors', $errors);
    }

    public function entryForm()
    {
        abort_unless(auth()->user()->canWrite(), 403);

        return view('sektoral.import.entries');
    }

    public function entryTemplate()
    {
        $rows = [
            ['indicator_kode', 'kecamatan_kode', 'village_kode', 'year', 'value', 'latitude', 'longitude'],
            ['PEND_TOTAL', '32.04.44', '', '2026', '83650', '', ''],
        ];

        return $this->csv('template-data-sektoral.csv', $rows);
    }

    public function entryStore(Request $request)
    {
        abort_unless(auth()->user()->canWrite(), 403);
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $user = auth()->user();
        $ok = 0;
        $errors = [];

        foreach ($this->rows($request->file('file')) as $i => $row) {
            $line = $i + 2;
            $kecId = $user->isSuperAdmin()
                ? (Kecamatan::where('kode_bps', $row['kecamatan_kode'] ?? '')->value('id') ?? $user->kecamatan_id)
                : $user->kecamatan_id;
            $indicator = Indicator::where('kode', $row['indicator_kode'] ?? '')->first();
            $v = Validator::make($row + ['kecamatan_id' => $kecId, 'indicator_id' => $indicator?->id], [
                'kecamatan_id' => 'required|exists:kecamatans,id',
                'indicator_id' => 'required|exists:indicators,id',
                'year' => 'required|integer|min:2000|max:2100',
                'value' => 'nullable|numeric',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);
            if ($v->fails()) {
                $errors[] = "Baris {$line}: ".implode('; ', $v->errors()->all());
                continue;
            }
            $village = !empty($row['village_kode']) ? Village::where('kode_bps', $row['village_kode'])->where('kecamatan_id', $kecId)->first() : null;
            $e = DataEntry::create([
                'kecamatan_id' => $kecId, 'village_id' => $village?->id,
                'indicator_id' => $indicator->id, 'year' => $row['year'], 'value' => $row['value'] ?? null,
                'latitude' => $row['latitude'] ?: null, 'longitude' => $row['longitude'] ?: null,
                'created_by' => $user->id,
            ]);
            Audit::log('create', $e, null, ['import' => true, 'indicator' => $indicator->kode]);
            $ok++;
        }

        return back()->with('success', "Import selesai: {$ok} berhasil, ".count($errors).' gagal.')->with('import_errors', $errors);
    }

    /** Parse CSV -> array assoc per header baris pertama. */
    private function rows($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = array_map(fn ($h) => strtolower(trim($h)), fgetcsv($handle));
        $out = [];
        while (($line = fgetcsv($handle)) !== false) {
            if (count(array_filter($line, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $out[] = array_combine($header, array_pad($line, count($header), null));
        }
        fclose($handle);

        return $out;
    }

    private function csv(string $name, array $rows)
    {
        $out = fopen('php://temp', 'w');
        foreach ($rows as $r) {
            fputcsv($out, $r);
        }
        rewind($out);

        return response(stream_get_contents($out), 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$name}\""]);
    }
}
