@extends('sektoral.layouts.app')
@section('title', 'Dashboard '.$kecamatan->name)
@section('content')
<h1 class="text-xl font-bold">Dashboard Kecamatan {{ $kecamatan->name }}</h1>
<p class="text-sm text-slate-500 mb-4">{{ $kecamatan->kode_bps }} · {{ number_format($kecamatan->jumlah_penduduk ?? 0) }} jiwa · {{ $kecamatan->luas_km2 }} km²</p>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
  @foreach(\App\Models\Facility::MODULES as $kode => $nama)
  <a href="{{ route('facilities.index', ['modul' => $kode]) }}" class="bg-white rounded shadow p-3 hover:bg-slate-50"><div class="text-xs text-slate-500">{{ $nama }}</div><div class="text-2xl font-bold">{{ $perModul[$kode] ?? 0 }}</div></a>
  @endforeach
</div>
<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-white rounded shadow p-3"><h2 class="font-semibold text-sm mb-2">Komposisi fasilitas</h2><canvas id="ch-type" height="220"></canvas></div>
  <div class="bg-white rounded shadow p-3"><h2 class="font-semibold text-sm mb-2">Peta fasilitas ({{ $markers->count() }} titik)</h2><div id="dash-map" style="height:300px"></div></div>
</div>
<div class="bg-white rounded shadow p-3 mt-4">
  <div class="flex justify-between items-center mb-2"><h2 class="font-semibold text-sm">Indikator tahun {{ $year }}</h2><a href="{{ route('data-entries.create') }}" class="text-xs px-2 py-1 bg-slate-900 text-white rounded">+ Input data</a></div>
  <table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-2 text-left">Indikator</th><th class="p-2 text-right">Nilai</th></tr></thead>
  <tbody>@forelse($entries as $e)<tr class="border-t"><td class="p-2">{{ $e->indicator->nama }} ({{ $e->indicator->satuan }})</td><td class="p-2 text-right">{{ number_format($e->value, 2) }}</td></tr>@empty<tr><td colspan="2" class="p-3 text-center text-slate-400">Belum ada data tahun {{ $year }}.</td></tr>@endforelse</tbody></table>
</div>
@push('scripts')
<script>
new Chart(document.getElementById('ch-type'), {type: 'doughnut', data: {labels: @json(array_keys($perType->toArray())), datasets: [{data: @json(array_values($perType->toArray()))}]}});
const map = L.map('dash-map').setView([{{ $kecamatan->center_lat }}, {{ $kecamatan->center_lng }}], 12);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
const cluster = L.markerClusterGroup();
@json($markers).forEach(p => cluster.addLayer(L.marker([p.latitude, p.longitude]).bindTooltip(p.name)));
map.addLayer(cluster);
</script>
@endpush
@endsection
