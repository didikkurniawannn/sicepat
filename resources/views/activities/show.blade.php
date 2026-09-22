@extends('layout')
@section('title', $activity->title)
@section('content')
<div class="flex items-center justify-between mb-3">
  <h1 class="text-xl font-bold">{{ $activity->title }} @if($activity->is_h7)<span class="text-sm bg-red-600 text-white px-2 py-0.5 rounded">⚠ H-{{ $activity->days_to_event }} perlu persiapan Kasi</span>@endif</h1>
  <div class="flex gap-2 text-sm">
    @if(auth()->user()->hasAnyRole(['admin','kasi']))<a href="/kegiatan/{{ $activity->id }}/edit" class="bg-blue-600 text-white px-3 py-1 rounded">Edit</a>@endif
    <a href="/kegiatan" class="bg-slate-200 px-3 py-1 rounded">Kembali</a>
  </div>
</div>

<div class="grid md:grid-cols-3 gap-4">
<div class="md:col-span-2 space-y-4">
  <div class="bg-white rounded shadow p-4 text-sm">
    <h2 class="font-bold mb-2">Informasi Kegiatan (sesuai struktur Excel)</h2>
    <table class="w-full">
      <tr class="border-t"><td class="py-1 text-slate-500 w-40">Tanggal/Bulan</td><td class="font-medium">{{ $activity->activity_date->translatedFormat('l, d F Y') }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Bidang</td><td><span class="px-1 rounded text-white text-xs" style="background:{{ $activity->section->color }}">{{ $activity->section->name }}</span></td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Kode Rekening</td><td class="font-mono">{{ $activity->account_code }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Kegiatan/Program</td><td>{{ $activity->program_name }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Kebutuhan / Jumlah / Satuan</td><td>{{ $activity->requirement_qty }} / {{ $activity->total_qty }} {{ $activity->unit }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Pagu</td><td>Rp {{ number_format($activity->budget_pagu,0,',','.') }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Realisasi ({{ $activity->realization_percent }}%)</td><td>Rp {{ number_format($activity->budget_realization,0,',','.') }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Sisa (auto)</td><td class="font-bold">Rp {{ number_format($activity->budget_remaining,0,',','.') }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Penanggung Jawab / Lokasi</td><td>{{ $activity->pptk->name ?? '-' }} / {{ $activity->location ?? '-' }}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Status / Progress</td><td><span class="bg-slate-200 px-2 rounded text-xs">{{ $activity->status }}</span> · {{ $activity->progress }}%</td></tr>
    </table>
    <div class="mt-2 bg-slate-100 rounded h-2"><div class="bg-green-600 h-2 rounded" style="width:{{ $activity->progress }}%"></div></div>
    @if($activity->description)<p class="mt-2 text-slate-600">{{ $activity->description }}</p>@endif
    @if(auth()->user()->hasAnyRole(['admin','kasi']))
    <form action="/kegiatan/{{ $activity->id }}/ajukan" method="POST" class="mt-3">@csrf<button class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Ajukan Verifikasi</button></form>
    @endif
  </div>

  <div class="bg-white rounded shadow p-4 text-sm">
    <h2 class="font-bold mb-2">Checklist Persiapan</h2>
    <ul class="space-y-1">@foreach($activity->checklists as $c)<li>{{ $c->is_checked ? '✅' : '⬜' }} {{ $c->item }}</li>@endforeach</ul>
  </div>

  <div class="bg-white rounded shadow p-4 text-sm">
    <h2 class="font-bold mb-2">Dokumen Pendukung</h2>
    <ul class="space-y-1 mb-3">@foreach($activity->documents as $d)<li><a class="text-blue-700 hover:underline" href="/storage/{{ $d->file_path }}" target="_blank">{{ $d->name }}</a> <span class="text-xs text-slate-500">({{ $d->type }})</span></li>@endforeach</ul>
    <form action="/kegiatan/{{ $activity->id }}/dokumen" method="POST" enctype="multipart/form-data" class="flex flex-wrap gap-2">@csrf
      <input name="name" required placeholder="Nama dokumen" class="border rounded px-2 py-1 text-sm">
      <select name="type" class="border rounded px-2 py-1 text-sm"><option>TOR</option><option>RAB</option><option>KAK</option><option>SPD</option><option>lainnya</option></select>
      <input type="file" name="file" required class="text-sm"><button class="bg-slate-900 text-white px-3 py-1 rounded text-sm">Upload</button>
    </form>
  </div>

  <div class="bg-white rounded shadow p-4 text-sm">
    <h2 class="font-bold mb-2">Riwayat Verifikasi</h2>
    <ul class="space-y-2">@forelse($activity->verifications as $v)<li class="border-t pt-1"><span class="font-medium">{{ $v->decision }}</span> oleh {{ $v->user->name }} ({{ $v->role_at_time }}) · <span class="text-slate-500">{{ $v->created_at }}</span><br>{{ $v->note }}</li>@empty<li class="text-slate-500">Belum ada.</li>@endforelse</ul>
  </div>
</div>

<div class="space-y-4">
  <div class="bg-white rounded shadow p-4 text-sm">
    <h2 class="font-bold mb-2">Update Progress & Realisasi (Kasi)</h2>
    <form action="/kegiatan/{{ $activity->id }}/progress" method="POST" class="space-y-2">@csrf
      <div><label>Progress %</label><input type="number" name="progress" min="0" max="100" value="{{ $activity->progress }}" class="w-full border rounded px-2 py-1"></div>
      <div><label>Realisasi (Rp)</label><input type="number" name="budget_realization" value="{{ $activity->budget_realization }}" class="w-full border rounded px-2 py-1"></div>
      <button class="bg-green-600 text-white px-3 py-1 rounded w-full">Simpan Progress</button>
    </form>
  </div>
  @if(auth()->user()->hasRole('admin'))
  <div class="bg-white rounded shadow p-4 text-sm">
    <h2 class="font-bold mb-2">Keputusan Verifikasi</h2>
    <form action="/verifikasi/{{ $activity->id }}" method="POST" class="space-y-2">@csrf
      <select name="decision" class="w-full border rounded px-2 py-1"><option value="diverifikasi">Diverifikasi</option><option value="disetujui">Disetujui (final)</option><option value="revisi">Minta Revisi</option><option value="ditolak">Ditolak</option></select>
      <textarea name="note" placeholder="Catatan..." class="w-full border rounded px-2 py-1"></textarea>
      <button class="bg-slate-900 text-white px-3 py-1 rounded w-full">Kirim Keputusan</button>
    </form>
  </div>
  @endif
</div>
</div>
@endsection
