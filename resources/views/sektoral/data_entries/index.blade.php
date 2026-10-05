@extends('sektoral.layouts.app')
@section('title', 'Data Sektoral')
@section('content')
<div class="flex justify-between items-center mb-4">
  <h1 class="text-xl font-bold">Data Sektoral / Kependudukan</h1>
  @if(auth()->user()->canWrite())<a href="{{ route('data-entries.create') }}" class="px-3 py-2 bg-slate-900 text-white rounded text-sm">+ Tambah</a>@endif
</div>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex gap-2 text-sm">
  <select name="indicator_id" class="border rounded px-2 py-1"><option value="">Semua indikator</option>@foreach($indicators as $i)<option value="{{ $i->id }}" @selected(request('indicator_id')==$i->id)>{{ $i->nama }}</option>@endforeach</select>
  <select name="year" class="border rounded px-2 py-1"><option value="">Semua tahun</option>@foreach([2024,2025,2026] as $y)<option @selected(request('year')==$y)>{{ $y }}</option>@endforeach</select>
  <button class="bg-slate-200 rounded px-3">Filter</button>
  <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-1 border rounded">Export CSV</a>
</form>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm">
<thead class="bg-slate-50"><tr><th class="p-2 text-left">Kecamatan</th><th class="p-2 text-left">Indikator</th><th class="p-2">Tahun</th><th class="p-2 text-right">Nilai</th><th class="p-2">Koordinat</th><th class="p-2"></th></tr></thead>
<tbody>@forelse($entries as $e)<tr class="border-t"><td class="p-2">{{ $e->kecamatan->name ?? '-' }}</td><td class="p-2">{{ $e->indicator->nama ?? '-' }}</td><td class="p-2 text-center">{{ $e->year }}</td><td class="p-2 text-right">{{ number_format($e->value, 2) }}</td><td class="p-2 text-xs font-mono">{{ $e->latitude ? $e->latitude.', '.$e->longitude : '-' }}</td><td class="p-2"><a href="{{ route('data-entries.edit', $e) }}" class="text-blue-600">Edit</a></td></tr>@empty<tr><td colspan="6" class="p-4 text-center text-slate-400">Belum ada data.</td></tr>@endforelse</tbody></table></div>
<div class="mt-3">{{ $entries->links() }}</div>
@endsection
