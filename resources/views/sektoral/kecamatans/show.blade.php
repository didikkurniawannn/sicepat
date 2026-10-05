@extends('sektoral.layouts.app')
@section('title', 'Profil '.$kecamatan->name)
@section('content')
<h1 class="text-xl font-bold">Profil Wilayah — {{ $kecamatan->name }}</h1>
<p class="text-sm text-slate-500 mb-4">{{ $kecamatan->kode_bps }} · Camat: {{ $kecamatan->camat ?? '-' }} · {{ $kecamatan->alamat ?? '' }}</p>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Penduduk</div><div class="text-xl font-bold">{{ number_format($kecamatan->jumlah_penduduk ?? 0) }}</div></div>
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Luas</div><div class="text-xl font-bold">{{ $kecamatan->luas_km2 }} km²</div></div>
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Desa</div><div class="text-xl font-bold">{{ $kecamatan->villages_count }}</div></div>
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Fasilitas</div><div class="text-xl font-bold">{{ $kecamatan->facilities_count }}</div></div>
</div>
<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-white rounded shadow p-3"><h2 class="font-semibold text-sm mb-2">Peta fasilitas</h2><div id="prof-map" style="height:320px"></div></div>
  <div class="bg-white rounded shadow p-3">
    <h2 class="font-semibold text-sm mb-2">Daftar Desa ({{ $villages->count() }})</h2>
    <ul class="text-sm divide-y">@foreach($villages as $v)<li class="py-1.5 flex justify-between"><span>{{ $v->nama }} <span class="text-slate-400 font-mono text-xs">{{ $v->kode_bps }}</span></span><span class="text-slate-500">{{ $v->luas_ha }} ha</span></li>@endforeach</ul>
    @if(auth()->user()->canWrite())
    <form method="POST" action="{{ route('villages.store', $kecamatan) }}" class="mt-3 flex gap-2 text-sm">@csrf
      <input name="kode_bps" placeholder="Kode BPS" required class="border rounded px-2 py-1 w-32"><input name="nama" placeholder="Nama desa" required class="border rounded px-2 py-1 flex-1"><button class="bg-slate-900 text-white rounded px-3">+</button>
    </form>@endif
  </div>
</div>
@push('scripts')
<script>
const map = L.map('prof-map').setView([{{ $kecamatan->center_lat }}, {{ $kecamatan->center_lng }}], 12);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
const cluster = L.markerClusterGroup();
@json($markers).forEach(p => cluster.addLayer(L.marker([p.latitude, p.longitude]).bindTooltip(p.name)));
map.addLayer(cluster);
</script>
@endpush
@endsection
