@extends('sektoral.layouts.app')
@section('title', $facility->name)
@section('content')
<h1 class="text-xl font-bold">{{ $facility->name }}</h1>
<p class="text-sm text-slate-500 mb-3">{{ $facility->kecamatan->name ?? '' }} · {{ $facility->village->nama ?? '' }} · {{ $facility->modul }}/{{ $facility->type }} · {{ $facility->status }}</p>
<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-white rounded shadow p-4 text-sm space-y-1">
    <div><b>Alamat:</b> {{ $facility->address ?? '-' }}</div>
    <div><b>Koordinat:</b> <span class="font-mono">{{ $facility->latitude }}, {{ $facility->longitude }}</span> ({{ $facility->location_source }}{{ $facility->accuracy_meters ? ', ±'.$facility->accuracy_meters.'m' : '' }})</div>
    <div class="flex gap-2 pt-2">
      <a href="{{ route('facilities.edit', $facility) }}" class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Edit</a>
      <form method="POST" action="{{ route('facilities.destroy', $facility) }}" onsubmit="return confirm('Hapus data?')">@csrf @method('DELETE')<button class="px-3 py-1.5 border border-red-300 text-red-700 rounded text-sm">Hapus</button></form>
    </div>
  </div>
  <div class="bg-white rounded shadow p-2">
    <div id="mini-map" style="height:320px"></div>
    <p class="text-xs text-slate-500 p-2">Snapshot mini-map (FR-GEO-12).</p>
  </div>
</div>
@push('scripts')
<script>
const m = L.map('mini-map').setView([{{ $facility->latitude }}, {{ $facility->longitude }}], 15);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(m);
L.marker([{{ $facility->latitude }}, {{ $facility->longitude }}]).addTo(m).bindPopup(@json($facility->name)).openPopup();
</script>
@endpush
@endsection
