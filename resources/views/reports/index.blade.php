@extends('layout')
@section('title','Laporan')
@section('content')
<h1 class="text-xl font-bold mb-3">Laporan Realisasi Anggaran</h1>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
  <div class="bg-white rounded shadow p-3"><p class="text-xs text-slate-500">Total Pagu</p><p class="font-bold">Rp {{ number_format($summary['pagu'],0,',','.') }}</p></div>
  <div class="bg-white rounded shadow p-3"><p class="text-xs text-slate-500">Realisasi</p><p class="font-bold text-green-700">Rp {{ number_format($summary['realisasi'],0,',','.') }}</p></div>
  <div class="bg-white rounded shadow p-3"><p class="text-xs text-slate-500">Sisa</p><p class="font-bold">Rp {{ number_format($summary['sisa'],0,',','.') }}</p></div>
  <div class="bg-white rounded shadow p-3"><p class="text-xs text-slate-500">% Realisasi</p><p class="font-bold">{{ $summary['pct'] }}%</p></div>
</div>
<form class="bg-white rounded shadow p-3 flex flex-wrap gap-2 text-sm mb-4" method="GET">
  <select name="section_id" class="border rounded px-2 py-1"><option value="">Semua Unit</option>@foreach($sections as $s)<option value="{{ $s->id }}" @selected(request('section_id')==$s->id)>{{ $s->name }}</option>@endforeach</select>
  <select name="status" class="border rounded px-2 py-1"><option value="">Semua Status</option>@foreach(['draft','diajukan','diverifikasi','disetujui','berjalan','selesai','ditolak'] as $st)<option @selected(request('status')==$st)>{{ $st }}</option>@endforeach</select>
  <input name="month" type="number" min="1" max="12" value="{{ request('month') }}" placeholder="Bulan" class="border rounded px-2 py-1 w-24">
  <input name="year" type="number" value="{{ request('year') }}" placeholder="Tahun" class="border rounded px-2 py-1 w-24">
  <button class="bg-blue-600 text-white px-3 py-1 rounded">Filter</button>
  <a href="/laporan/excel?{{ http_build_query(request()->all()) }}" class="bg-green-600 text-white px-3 py-1 rounded">Export Excel</a>
  <a href="/laporan/pdf?{{ http_build_query(request()->all()) }}" class="bg-red-600 text-white px-3 py-1 rounded">Export PDF</a>
</form>
<h2 class="font-semibold mb-2">Per Kode Rekening</h2>
<div class="bg-white rounded shadow overflow-x-auto mb-4"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-left">Kode</th><th class="p-2">Jml</th><th class="p-2 text-right">Pagu</th><th class="p-2 text-right">Realisasi</th></tr></thead>
<tbody>@foreach($perRekening as $r)<tr class="border-t"><td class="p-2 font-mono">{{ $r['code'] }}</td><td class="p-2 text-center">{{ $r['count'] }}</td><td class="p-2 text-right">{{ number_format($r['pagu'],0,',','.') }}</td><td class="p-2 text-right">{{ number_format($r['realisasi'],0,',','.') }}</td></tr>@endforeach</tbody></table></div>
<h2 class="font-semibold mb-2">Detail Kegiatan</h2>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Judul</th><th class="p-2 text-right">Pagu</th><th class="p-2 text-right">Realisasi</th><th class="p-2 text-right">Sisa</th></tr></thead>
<tbody>@foreach($activities as $a)<tr class="border-t"><td class="p-2">{{ $a->activity_date->format('Y-m-d') }}</td><td class="p-2"><a class="text-blue-700" href="/kegiatan/{{ $a->id }}">{{ $a->title }}</a><br><span class="text-xs text-slate-500">{{ $a->section->name }}</span></td><td class="p-2 text-right">{{ number_format($a->budget_pagu,0,',','.') }}</td><td class="p-2 text-right">{{ number_format($a->budget_realization,0,',','.') }}</td><td class="p-2 text-right font-semibold">{{ number_format($a->budget_pagu - $a->budget_realization,0,',','.') }}</td></tr>@endforeach</tbody></table></div>
<div class="mt-2">{{ $activities->links() }}</div>
@endsection
