@extends('layout')
@section('title','Pengingat H-7')
@section('content')
<style>
@media print {
  nav, footer, .no-print { display: none !important; }
  body { background: #fff; }
  main { max-width: 100%; padding: 0; }
}
</style>
<div class="flex flex-wrap items-center justify-between gap-2 mb-3">
  <div>
    <h1 class="text-xl font-bold">🔔 Pengingat H-7 — Rencana 7 Hari Ke Depan</h1>
    <p class="text-sm text-slate-500">Periode {{ now()->translatedFormat('d F Y') }} s.d. {{ now()->addDays(7)->translatedFormat('d F Y') }} · {{ $activities->count() }} kegiatan · untuk dibagikan ke tim pelaksana</p>
  </div>
  <div class="flex flex-wrap gap-2 text-sm no-print">
    <a href="/laporan/h7/excel?{{ http_build_query(request()->all()) }}" class="bg-green-600 text-white px-3 py-1 rounded">Export Excel</a>
    <a href="/laporan/h7/pdf?{{ http_build_query(request()->all()) }}" class="bg-red-600 text-white px-3 py-1 rounded">Export PDF</a>
    <button onclick="window.print()" class="bg-slate-700 text-white px-3 py-1 rounded">🖨️ Cetak</button>
    <button onclick="salinWA()" class="bg-emerald-600 text-white px-3 py-1 rounded">📋 Salin Teks WA</button>
  </div>
</div>
<form class="bg-white rounded shadow p-3 flex flex-wrap gap-2 text-sm mb-4 no-print" method="GET">
  <select name="section_id" class="border rounded px-2 py-1"><option value="">Semua Unit</option>@foreach($sections as $s)<option value="{{ $s->id }}" @selected(request('section_id')==$s->id)>{{ $s->name }}</option>@endforeach</select>
  <button class="bg-blue-600 text-white px-3 py-1 rounded">Filter</button>
  <a href="/laporan" class="bg-slate-200 px-3 py-1 rounded">← Laporan Umum</a>
</form>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-slate-100"><tr><th class="p-2 text-center">No</th><th class="p-2 text-left">Hari/Tanggal</th><th class="p-2 text-center">Sisa Hari</th><th class="p-2 text-left">Kegiatan</th><th class="p-2 text-left">Bidang</th><th class="p-2 text-right">Kebutuhan</th><th class="p-2 text-left">Lokasi</th><th class="p-2 text-left">Penanggung Jawab</th><th class="p-2">Status</th></tr></thead>
<tbody>
@forelse($activities as $a)
@php $cek = $a->checklists->count() ? $a->checklists->where('is_checked', true)->count().'/'.$a->checklists->count().' ceklis' : 'belum ada ceklis'; @endphp
<tr class="border-t hover:bg-slate-50">
  <td class="p-2 text-center">{{ $loop->iteration }}</td>
  <td class="p-2 whitespace-nowrap font-medium">{{ $a->activity_date->translatedFormat('l, d F Y') }}</td>
  <td class="p-2 text-center"><span class="text-xs bg-red-600 text-white px-2 py-0.5 rounded font-bold">H-{{ $a->days_to_event }}</span></td>
  <td class="p-2"><span class="font-medium">{{ $a->title }}</span><br><span class="text-xs text-slate-500">{{ $a->account_code }} · {{ \Str::limit($a->program_name,60) }}</span></td>
  <td class="p-2"><span class="text-xs px-1 rounded text-white" style="background:{{ $a->section->color }}">{{ $a->section->short_name }}</span></td>
  <td class="p-2 text-right whitespace-nowrap">{{ $a->requirement_qty }} / {{ $a->total_qty }} {{ $a->unit }}</td>
  <td class="p-2">{{ $a->location ?? '-' }}</td>
  <td class="p-2">{{ $a->pptk->name ?? '-' }}<br><span class="text-xs text-slate-500">Kesiapan: {{ $cek }}</span></td>
  <td class="p-2 text-center"><span class="text-xs bg-slate-200 px-2 py-0.5 rounded">{{ $a->status }}</span></td>
</tr>
@empty
<tr><td colspan="9" class="p-4 text-center text-slate-500">Tidak ada kegiatan dalam 7 hari ke depan. 🎉</td></tr>
@endforelse
</tbody>
</table>
</div>
<p id="pesanWA" class="no-print text-xs text-slate-500 mt-2"></p>
@endsection
@section('scripts')
<script>
const dataH7 = @json($waData);
function teksWA(){
  let t = '🔔 *PENGINGAT H-7 SiCepatKeg*\nPeriode {{ now()->translatedFormat('d F Y') }} s.d. {{ now()->addDays(7)->translatedFormat('d F Y') }}\n\n';
  if (dataH7.length === 0) t += 'Tidak ada kegiatan dalam 7 hari ke depan. 🎉';
  dataH7.forEach((a, i) => {
    t += `${i+1}. *H-${a.h}* · ${a.tgl}\n   ${a.judul} [${a.unit}]\n   Kebutuhan: ${a.butuh} · PJ: ${a.pj}\n\n`;
  });
  t += '_Mohon persiapan: dokumen, anggaran, SDM & jadwal._';
  return t;
}
function salinWA(){
  navigator.clipboard.writeText(teksWA()).then(
    () => document.getElementById('pesanWA').textContent = '✅ Teks pengingat tersalin — tinggal tempel (paste) di WhatsApp grup tim.',
    () => document.getElementById('pesanWA').textContent = '❌ Gagal menyalin otomatis — blokir browser. Alternatif: cetak atau export PDF.'
  );
}
</script>
@endsection
