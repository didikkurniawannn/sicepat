@extends('sektoral.layouts.app')
@section('title', 'Import Data Sektoral')
@section('content')
<h1 class="text-xl font-bold mb-1">Import CSV Data Sektoral</h1>
<p class="text-sm text-slate-500 mb-4"><a href="{{ route('import.entries.template') }}" class="text-blue-600 underline">Unduh template</a></p>
@if(session('import_errors'))
<div class="mb-4 p-3 bg-amber-50 border border-amber-300 rounded text-xs max-h-48 overflow-auto"><b>{{ count(session('import_errors')) }} baris gagal:</b><ul class="list-disc ml-5">@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('import.entries.store') }}" enctype="multipart/form-data" class="bg-white rounded shadow p-4 max-w-xl space-y-3">@csrf
  <div><label class="text-sm">File CSV (maks 5MB)</label><input type="file" name="file" accept=".csv,.txt" required class="w-full border rounded px-2 py-1.5"></div>
  <p class="text-xs text-slate-500">Kolom: indicator_kode, kecamatan_kode (khusus Super Admin), village_kode, year, value, latitude, longitude.</p>
  <div><button class="px-4 py-2 bg-slate-900 text-white rounded">Import</button></div>
</form>
@endsection
