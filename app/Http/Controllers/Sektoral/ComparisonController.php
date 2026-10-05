<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\DataEntry;
use App\Models\Indicator;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    /** M-09 Analisis Komparatif Regional */
    public function index(Request $request)
    {
        $indicators = Indicator::orderBy('nama')->get();
        $indicator = $indicators->find($request->get('indicator_id'))
            ?? Indicator::where('kode', 'PEND_TOTAL')->first()
            ?? $indicators->first();
        $year = (int) $request->get('year', 2026);

        $rows = collect();
        if ($indicator) {
            $rows = DataEntry::withoutGlobalScope('tenant')->with('kecamatan')
                ->where('indicator_id', $indicator->id)->where('year', $year)
                ->get()->sortByDesc('value')->values();
        }
        $max = $rows->max('value') ?: 1;
        $avg = $rows->avg('value');

        if ($request->get('export') === 'csv' && $indicator) {
            $out = fopen('php://temp', 'w');
            fputcsv($out, ['rank', 'kecamatan', 'kode_bps', 'nilai', 'satuan']);
            foreach ($rows as $i => $r) {
                fputcsv($out, [$i + 1, $r->kecamatan->name ?? '-', $r->kecamatan->kode_bps ?? '-', $r->value, $indicator->satuan]);
            }
            rewind($out);

            return response(stream_get_contents($out), 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="komparasi.csv"']);
        }

        return view('sektoral.comparison.index', [
            'indicators' => $indicators, 'indicator' => $indicator,
            'year' => $year, 'rows' => $rows, 'max' => $max, 'avg' => $avg,
            'kecamatans' => Kecamatan::orderBy('name')->get(),
        ]);
    }
}
