@extends('sektoral.layouts.app')
@section('title', ($kecamatan->exists ? $kecamatan->name : 'Tenant Baru'))
@section('content')
<h1 class="text-xl font-bold mb-4">{{ $kecamatan->exists ? 'Edit Tenant: '.$kecamatan->name : 'Tenant Baru' }}</h1>
<form method="POST" action="{{ $kecamatan->exists ? route('kecamatans.update', $kecamatan) : route('kecamatans.store') }}" class="bg-white rounded shadow p-4 space-y-3 max-w-2xl">
@csrf @if($kecamatan->exists) @method('PUT') @endif
  <div class="grid md:grid-cols-2 gap-3">
    @unless($kecamatan->exists)<div><label class="text-sm">Kode BPS *</label><input name="kode_bps" value="{{ old('kode_bps', $kecamatan->kode_bps) }}" class="w-full border rounded px-2 py-1.5" placeholder="32.04.xx"></div>@endunless
    @unless($kecamatan->exists)<div><label class="text-sm">Kode Internal *</label><input name="code" value="{{ old('code', $kecamatan->code) }}" required class="w-full border rounded px-2 py-1.5" placeholder="CBX"></div>@endunless
    @unless($kecamatan->exists)<div><label class="text-sm">Slug *</label><input name="slug" value="{{ old('slug', $kecamatan->slug) }}" required class="w-full border rounded px-2 py-1.5" placeholder="cibiru"></div>@endunless
    <div><label class="text-sm">Nama *</label><input name="nama" value="{{ old('nama', $kecamatan->exists ? $kecamatan->namaSingkat() : '') }}" required class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Camat</label><input name="camat" value="{{ old('camat', $kecamatan->camat) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Telepon</label><input name="telepon" value="{{ old('telepon', $kecamatan->telepon) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Email</label><input name="email" type="email" value="{{ old('email', $kecamatan->email) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Luas (km²)</label><input name="luas_km2" type="number" step="0.01" value="{{ old('luas_km2', $kecamatan->luas_km2) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Penduduk</label><input name="jumlah_penduduk" type="number" value="{{ old('jumlah_penduduk', $kecamatan->jumlah_penduduk) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Center Lat</label><input name="center_lat" value="{{ old('center_lat', $kecamatan->center_lat) }}" class="w-full border rounded px-2 py-1.5"></div>
    <div><label class="text-sm">Center Lng</label><input name="center_lng" value="{{ old('center_lng', $kecamatan->center_lng) }}" class="w-full border rounded px-2 py-1.5"></div>
  </div>
  <div><label class="text-sm">Alamat</label><textarea name="alamat" rows="2" class="w-full border rounded px-2 py-1.5">{{ old('alamat', $kecamatan->alamat) }}</textarea></div>
  <label class="text-sm flex gap-2 items-center"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $kecamatan->is_active ?? true))> Aktif</label>
  <div><button class="px-4 py-2 bg-slate-900 text-white rounded">Simpan</button></div>
</form>
@endsection
