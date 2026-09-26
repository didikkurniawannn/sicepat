@extends('layout')
@section('title','Kegiatan')
@section('content')
<div class="flex items-center justify-between mb-3">
  <h1 class="text-xl font-bold">Data Kegiatan</h1>
  @if(auth()->user()->hasAnyRole(['admin','kasi']))<a href="/kegiatan/tambah" class="bg-slate-900 text-white px-3 py-2 rounded text-sm">+ Tambah Kegiatan</a>@endif
</div>
<form class="bg-white rounded shadow p-3 grid md:grid-cols-6 gap-2 mb-4 text-sm" method="GET">
  <input name="search" value="{{ request('search') }}" placeholder="Cari judul/program/rekening..." class="border rounded px-2 py-1 md:col-span-2">
  <select name="section_id" class="border rounded px-2 py-1"><option value="">Semua Unit</option>@foreach($sections as $s)<option value="{{ $s->id }}" @selected(request('section_id')==$s->id)>{{ $s->name }}</option>@endforeach</select>
  <select name="status" class="border rounded px-2 py-1"><option value="">Semua Status</option>@foreach($statuses as $st)<option @selected(request('status')==$st)>{{ $st }}</option>@endforeach</select>
  <input name="month" type="number" min="1" max="12" value="{{ request('month') }}" placeholder="Bulan" class="border rounded px-2 py-1">
  <input name="year" type="number" value="{{ request('year') }}" placeholder="Tahun" class="border rounded px-2 py-1">
  <button class="bg-blue-600 text-white rounded px-3 py-1 md:col-span-6">Filter</button>
</form>
<div class="grid grid-cols-2 gap-3 mb-4 text-sm">
  <div class="bg-white rounded shadow p-3"><p class="text-xs text-slate-500">Total Kebutuhan (filter aktif)</p><p class="text-xl font-bold">{{ number_format($totals['kebutuhan'],0,',','.') }}</p></div>
  <div class="bg-white rounded shadow p-3"><p class="text-xs text-slate-500">Total Jumlah (DPA)</p><p class="text-xl font-bold">{{ number_format($totals['jumlah'],0,',','.') }}</p></div>
</div>
<div class="bg-white rounded shadow p-3 mb-4 text-sm">
  <h2 class="font-bold mb-2">Rincian Pagu per Kode Rekening <span class="text-xs font-normal text-slate-500">(satu nilai per rekening = kegiatan paling awal · mengikuti filter aktif)</span></h2>
  <div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-100"><tr><th class="p-2 text-left">Kode Rekening</th><th class="p-2 text-center">Jml Kegiatan</th><th class="p-2 text-right">Total Pagu</th><th class="p-2 text-right">Realisasi</th><th class="p-2 text-right">Sisa</th></tr></thead>
    <tbody>
    @foreach($perRekening as $r)
    <tr class="border-t hover:bg-slate-50">
      <td class="p-2 font-mono">{{ $r['code'] }}</td>
      <td class="p-2 text-center">{{ $r['count'] }}</td>
      <td class="p-2 text-right font-semibold">{{ number_format($r['pagu'],0,',','.') }}</td>
      <td class="p-2 text-right">{{ number_format($r['realisasi'],0,',','.') }}</td>
      <td class="p-2 text-right">{{ number_format($r['sisa'],0,',','.') }}</td>
    </tr>
    @endforeach
    </tbody>
    <tfoot class="bg-slate-50 font-semibold">
    <tr class="border-t"><td class="p-2 text-right" colspan="2">Total ({{ $perRekening->count() }} rekening):</td><td class="p-2 text-right">{{ number_format($perRekening->sum('pagu'),0,',','.') }}</td><td class="p-2 text-right">{{ number_format($perRekening->sum('realisasi'),0,',','.') }}</td><td class="p-2 text-right">{{ number_format($perRekening->sum('sisa'),0,',','.') }}</td></tr>
    </tfoot>
  </table>
  </div>
</div>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-slate-100"><tr><th class="p-2 text-center">No</th><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Bidang</th><th class="p-2 text-left">Judul Kegiatan</th><th class="p-2 text-right">Kebutuhan</th><th class="p-2 text-right">Jumlah</th><th class="p-2 text-left">Satuan</th><th class="p-2 text-right">Pagu</th><th class="p-2 text-right">Realisasi</th><th class="p-2 text-right">Sisa Anggaran</th><th class="p-2">Status</th>@if(auth()->user()->hasAnyRole(['admin','kasi']))<th class="p-2">Aksi</th>@endif</tr></thead>
<tbody>
@foreach($activities as $a)
<tr class="border-t hover:bg-slate-50">
  <td class="p-2 text-center">{{ $activities->firstItem() + $loop->index }}</td>
  <td class="p-2 whitespace-nowrap">{{ $a->activity_date->translatedFormat('d F Y') }} @if($a->is_h7)<span class="text-xs bg-red-600 text-white px-1 rounded">H-{{ $a->days_to_event }}</span>@endif</td>
  <td class="p-2"><span class="text-xs px-1 rounded text-white" style="background:{{ $a->section->color }}">{{ $a->section->short_name }}</span></td>
  <td class="p-2"><a href="/kegiatan/{{ $a->id }}" class="text-blue-700 hover:underline font-medium">{{ $a->title }}</a><br><span class="text-xs text-slate-500">{{ $a->account_code }} · {{ \Str::limit($a->program_name,60) }}</span></td>
  <td class="p-2 text-right">{{ number_format($a->requirement_qty,0,',','.') }}</td>
  <td class="p-2 text-right">{{ number_format($a->total_qty,0,',','.') }}</td>
  <td class="p-2 text-xs whitespace-nowrap">{{ $a->unit }}</td>
  <td class="p-2 text-right">{{ number_format($a->budget_pagu,0,',','.') }}</td>
  <td class="p-2 text-right">{{ number_format($a->budget_realization,0,',','.') }}</td>
  <td class="p-2 text-right font-semibold">{{ number_format($a->budget_remaining,0,',','.') }}</td>
  <td class="p-2 text-center"><span class="text-xs bg-slate-200 px-2 py-0.5 rounded">{{ $a->status }}</span></td>
  @if(auth()->user()->hasAnyRole(['admin','kasi']))
  <td class="p-2 text-center whitespace-nowrap">
    <form action="/kegiatan/{{ $a->id }}" method="POST" class="inline" onsubmit="return confirm('Hapus kegiatan ini beserta dokumen & riwayat verifikasinya?')">
      @csrf @method('DELETE')
      <button class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded hover:bg-red-600 hover:text-white">Hapus</button>
    </form>
  </td>
  @endif
</tr>
@endforeach
</tbody>
<tfoot class="bg-slate-50 font-semibold">
<tr class="border-t"><td colspan="4" class="p-2 text-right">Total halaman ini:</td><td class="p-2 text-right">{{ number_format($activities->sum('requirement_qty'),0,',','.') }}</td><td class="p-2 text-right">{{ number_format($activities->sum('total_qty'),0,',','.') }}</td><td colspan="{{ auth()->user()->hasAnyRole(['admin','kasi']) ? 6 : 5 }}"></td></tr>
</tfoot>
</table>
</div>
<div class="mt-3">{{ $activities->links() }}</div>
@endsection
