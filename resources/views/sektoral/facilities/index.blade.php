@extends('sektoral.layouts.app')
@section('title', 'Data Fasilitas')
@section('content')
<div class="flex items-center justify-between mb-4">
  <div><h1 class="text-xl font-bold">Data Fasilitas @if($modul) — {{ $modules[$modul] ?? $modul }} @endif</h1>
  <p class="text-sm text-slate-500">Terisolasi per tenant otomatis.</p></div>
  @if(auth()->user()->canWrite())
  <a href="{{ route('facilities.create', ['modul' => $modul]) }}" class="px-3 py-2 bg-slate-900 text-white rounded text-sm">+ Tambah Data</a>
  @endif
</div>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex flex-wrap gap-2 text-sm">
  <select name="modul" class="border rounded px-2 py-1"><option value="">Semua modul</option>@foreach($modules as $kode => $nama)<option value="{{ $kode }}" @selected($modul == $kode)>{{ $kode }} — {{ $nama }}</option>@endforeach</select>
  <input name="q" value="{{ request('q') }}" placeholder="Cari nama..." class="border rounded px-2 py-1">
  <button class="bg-slate-200 rounded px-3">Filter</button>
  <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-1 border rounded">Export CSV</a>
</form>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-slate-50"><tr><th class="p-2 text-left">Nama</th><th class="p-2">Kecamatan</th><th class="p-2">Desa</th><th class="p-2">Modul/Tipe</th><th class="p-2">Koordinat</th><th class="p-2">Status</th><th class="p-2"></th></tr></thead>
<tbody>
@forelse($facilities as $f)
<tr class="border-t"><td class="p-2"><a href="{{ route('facilities.show', $f) }}" class="text-blue-700 underline">{{ $f->name }}</a></td>
<td class="p-2">{{ $f->kecamatan->name ?? '-' }}</td><td class="p-2">{{ $f->village->nama ?? '-' }}</td>
<td class="p-2">{{ $f->modul }} / {{ $f->type }}</td><td class="p-2 font-mono text-xs">{{ $f->latitude }}, {{ $f->longitude }}</td>
<td class="p-2">{{ $f->status }}</td>
<td class="p-2 whitespace-nowrap"><a href="{{ route('facilities.edit', $f) }}" class="text-blue-600">Edit</a></td></tr>
@empty<tr><td colspan="7" class="p-4 text-center text-slate-500">Belum ada data.</td></tr>@endforelse
</tbody></table>
</div>
<div class="mt-3">{{ $facilities->links() }}</div>
@endsection
