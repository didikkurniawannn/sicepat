@extends('sektoral.layouts.app')
@section('title', ($facility->exists ? 'Edit' : 'Tambah').' Fasilitas')
@section('content')
<h1 class="text-xl font-bold mb-1">{{ $facility->exists ? 'Edit' : 'Tambah' }} Data Fasilitas</h1>
<p class="text-sm text-slate-500 mb-4">Koordinat <b class="text-red-600">wajib</b> diisi melalui map picker (FR-GEO-01).</p>
<form method="POST" action="{{ $facility->exists ? route('facilities.update', $facility) : route('facilities.store') }}" class="bg-white rounded shadow p-4 space-y-3">
  @csrf @if($facility->exists) @method('PUT') @endif
  <div class="grid md:grid-cols-2 gap-3">
    <div><label class="text-sm">Kecamatan *</label>
      <select name="kecamatan_id" id="kecamatan_id" class="w-full border rounded px-2 py-1.5" {{ auth()->user()->isSuperAdmin() ? '' : 'disabled' }}>
        @foreach($kecamatans as $k)<option value="{{ $k->id }}" @selected(old('kecamatan_id', $facility->kecamatan_id) == $k->id)>{{ $k->name }}</option>@endforeach
      </select>
      @unless(auth()->user()->isSuperAdmin())<input type="hidden" name="kecamatan_id" value="{{ $facility->kecamatan_id }}">@endunless
    </div>
    <div><label class="text-sm">Desa (auto dari koordinat bila memungkinkan)</label>
      <select name="village_id" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
        @foreach($villages as $v)<option value="{{ $v->id }}" @selected(old('village_id', $facility->village_id) == $v->id)>{{ $v->nama }}</option>@endforeach
      </select>
    </div>
    <div><label class="text-sm">Nama *</label><input name="name" value="{{ old('name', $facility->name) }}" required maxlength="150" class="w-full border rounded px-2 py-1.5" placeholder="cth: Posyandu Melati"></div>
    <div><label class="text-sm">Modul *</label>
      <select name="modul" class="w-full border rounded px-2 py-1.5">@foreach($modules as $kode => $nama)<option value="{{ $kode }}" @selected(old('modul', $facility->modul) == $kode)>{{ $kode }} — {{ $nama }}</option>@endforeach</select>
    </div>
    <div><label class="text-sm">Jenis *</label>
      <select name="type" class="w-full border rounded px-2 py-1.5">@foreach($types as $t)<option value="{{ $t->slug }}" @selected(old('type', $facility->type) == $t->slug)>{{ $t->nama }} ({{ $t->modul }})</option>@endforeach</select>
    </div>
    <div><label class="text-sm">Status</label>
      <select name="status" class="w-full border rounded px-2 py-1.5">@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'pending' => 'Pending'] as $s => $l)<option value="{{ $s }}" @selected(old('status', $facility->status) == $s)>{{ $l }}</option>@endforeach</select>
    </div>
  </div>
  <div><label class="text-sm">Alamat</label><textarea name="address" rows="2" class="w-full border rounded px-2 py-1.5">{{ old('address', $facility->address) }}</textarea></div>
  <div>
    <label class="text-sm font-semibold">Lokasi (WAJIB) <span class="text-red-600">*</span></label>
    <div class="grid md:grid-cols-4 gap-2 mb-2">
      <div><input id="latitude" name="latitude" value="{{ old('latitude', $facility->latitude) }}" required placeholder="Latitude cth: -7.051234" class="w-full border rounded px-2 py-1.5"></div>
      <div><input id="longitude" name="longitude" value="{{ old('longitude', $facility->longitude) }}" required placeholder="Longitude cth: 107.556789" class="w-full border rounded px-2 py-1.5"></div>
      <div><input id="accuracy_meters" name="accuracy_meters" value="{{ old('accuracy_meters', $facility->accuracy_meters) }}" placeholder="Akurasi GPS (m)" class="w-full border rounded px-2 py-1.5"></div>
      <div><select id="location_source" name="location_source" class="w-full border rounded px-2 py-1.5">@foreach(['map_click' => 'Klik peta', 'gps' => 'GPS', 'manual_input' => 'Manual', 'geocoding' => 'Geocoding'] as $s => $l)<option value="{{ $s }}" @selected(old('location_source', $facility->location_source) == $s)>{{ $l }}</option>@endforeach</select></div>
    </div>
    @include('sektoral.components.map-picker', ['kecamatan' => $kecamatan, 'existing' => $existing])
  </div>
  <div class="flex gap-2"><a href="{{ route('facilities.index') }}" class="px-4 py-2 border rounded">Batal</a><button class="px-4 py-2 bg-slate-900 text-white rounded">Simpan Data</button></div>
</form>
@endsection
