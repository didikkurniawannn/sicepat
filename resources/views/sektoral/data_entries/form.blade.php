@extends('sektoral.layouts.app')
@section('title', ($entry->exists ? 'Edit' : 'Tambah').' Data Sektoral')
@section('content')
<h1 class="text-xl font-bold mb-4">{{ $entry->exists ? 'Edit' : 'Tambah' }} Data Sektoral</h1>
<form method="POST" action="{{ $entry->exists ? route('data-entries.update', $entry) : route('data-entries.store') }}" class="bg-white rounded shadow p-4 space-y-3 max-w-2xl">
@csrf @if($entry->exists) @method('PUT') @endif
  <div><label class="text-sm">Kecamatan *</label><select name="kecamatan_id" class="w-full border rounded px-2 py-1.5">@foreach($kecamatans as $k)<option value="{{ $k->id }}" @selected(old('kecamatan_id', $entry->kecamatan_id)==$k->id)>{{ $k->name }}</option>@endforeach</select></div>
  <div><label class="text-sm">Desa (opsional)</label><select name="village_id" class="w-full border rounded px-2 py-1.5"><option value="">— Kabupaten/Kecamatan —</option>@foreach($villages as $v)<option value="{{ $v->id }}" @selected(old('village_id', $entry->village_id)==$v->id)>{{ $v->nama }}</option>@endforeach</select></div>
  <div><label class="text-sm">Indikator *</label><select name="indicator_id" class="w-full border rounded px-2 py-1.5">@foreach($indicators as $i)<option value="{{ $i->id }}" @selected(old('indicator_id', $entry->indicator_id)==$i->id)>{{ $i->modul }} — {{ $i->nama }} ({{ $i->satuan }})</option>@endforeach</select></div>
  <div class="grid grid-cols-2 gap-3">
    <div><label class="text-sm">Tahun *</label><input name="year" type="number" min="2000" max="2100" value="{{ old('year', $entry->year) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Nilai</label><input name="value" type="number" step="0.01" value="{{ old('value', $entry->value) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Latitude (opsional)</label><input name="latitude" value="{{ old('latitude', $entry->latitude) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Longitude (opsional)</label><input name="longitude" value="{{ old('longitude', $entry->longitude) }}" class="w-full border rounded px-2 py-1.5"></div>
  </div>
  <div class="flex gap-2"><a href="{{ route('data-entries.index') }}" class="px-4 py-2 border rounded">Batal</a><button class="px-4 py-2 bg-slate-900 text-white rounded">Simpan</button></div>
</form>
@endsection
