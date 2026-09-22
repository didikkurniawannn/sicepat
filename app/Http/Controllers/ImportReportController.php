<?php

namespace App\Http\Controllers;

use App\Exports\ActivitiesExport;
use App\Imports\ActivitiesImport;
use App\Models\ImportLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportReportController extends Controller
{
    // --- Import ---
    public function showImport()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $logs = ImportLog::with('user')->latest()->paginate(10);
        return view('import.index', compact('logs'));
    }

    public function preview(Request $request)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        $rows = Excel::toCollection(new ActivitiesImport, $request->file('file'))[0]->take(10);
        $request->session()->put('import_file', $request->file('file')->store('imports'));
        $request->session()->put('import_name', $request->file('file')->getClientOriginalName());
        return view('import.preview', ['rows' => $rows]);
    }

    public function process(Request $request)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $path = $request->session()->get('import_file');
        abort_if(!$path, 400, 'Tidak ada file untuk diproses. Upload ulang.');
        $import = new ActivitiesImport;
        Excel::import($import, storage_path('app/'.$path));
        $total = count($import->errors) + $import->success;
        ImportLog::create([
            'user_id' => auth()->id(), 'file_name' => $request->session()->get('import_name', basename($path)),
            'total_rows' => $total, 'success_rows' => $import->success, 'failed_rows' => count($import->errors), 'errors' => $import->errors,
        ]);
        $request->session()->forget(['import_file','import_name']);
        return redirect('/import')->with('success', "Import selesai: {$import->success} sukses, ".count($import->errors).' gagal.');
    }

    public function template()
    {
        return Excel::download(new ActivitiesExport(['section_id' => null]), 'template-import-kegiatan.xlsx');
    }

    // --- Laporan ---
    public function reports(Request $request)
    {
        $q = \App\Models\Activity::with('section');
        if ($request->filled('section_id')) $q->where('section_id', $request->section_id);
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('month')) $q->whereMonth('activity_date', $request->month);
        if ($request->filled('year')) $q->whereYear('activity_date', $request->year);
        // Catatan: rekap dihitung SEBELUM paginate karena paginate() menempelkan limit/offset ke query builder
        $sections = \App\Models\Section::orderBy('order')->get();
        $summary = [
            'pagu' => (clone $q)->sum('budget_pagu'),
            'realisasi' => (clone $q)->sum('budget_realization'),
        ];
        $summary['sisa'] = $summary['pagu'] - $summary['realisasi'];
        $summary['pct'] = $summary['pagu'] > 0 ? round($summary['realisasi'] / $summary['pagu'] * 100, 1) : 0;
        $perRekening = (clone $q)->get()->groupBy('account_code')->map(fn($g) => [
            'code' => $g->first()->account_code, 'count' => $g->count(),
            'pagu' => $g->sum('budget_pagu'), 'realisasi' => $g->sum('budget_realization'),
        ])->values();
        $activities = $q->orderBy('activity_date')->paginate(20)->withQueryString();
        return view('reports.index', compact('activities','sections','summary','perRekening'));
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ActivitiesExport($request->only(['section_id','status','month','year'])), 'laporan-kegiatan.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $q = \App\Models\Activity::with('section')->orderBy('activity_date');
        if ($request->filled('section_id')) $q->where('section_id', $request->section_id);
        if ($request->filled('status')) $q->where('status', $request->status);
        $activities = $q->get();
        $pdf = Pdf::loadView('reports.pdf', compact('activities'))->setPaper('a4', 'landscape');
        return $pdf->download('laporan-kegiatan.pdf');
    }
}
