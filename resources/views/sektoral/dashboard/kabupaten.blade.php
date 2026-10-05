@extends('sektoral.layouts.app')
@section('title', 'Dashboard Kabupaten')
@section('content')
<h1 class="text-xl font-bold">Dashboard Kabupaten Bandung</h1>
<p class="text-sm text-slate-500 mb-4">Agregat 31 kecamatan · Tahun {{ $year }}</p>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Kecamatan aktif</div><div class="text-2xl font-bold">{{ $kecamatans->count() }}</div></div>
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Total fasilitas</div><div class="text-2xl font-bold">{{ $totalFacilities }}</div></div>
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Total penduduk (agregat)</div><div class="text-2xl font-bold">{{ number_format($totalPenduduk) }}</div></div>
  <div class="bg-white rounded shadow p-3"><div class="text-xs text-slate-500">Tahun data</div>
    <form method="GET"><select name="year" onchange="this.form.submit()" class="border rounded px-2 py-1">@foreach([2024,2025,2026] as $y)<option @selected($year==$y)>{{ $y }}</option>@endforeach</select></form></div>
</div>
<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-white rounded shadow p-3">
    <h2 class="font-semibold text-sm mb-2">Fasilitas per modul</h2>
    <canvas id="ch-modul" height="200"></canvas>
  </div>
  <div class="bg-white rounded shadow p-3">
    <h2 class="font-semibold text-sm mb-2">Peta sebaran fasilitas ({{ $markers->count() }} titik)</h2>
    <div id="dash-map" style="height:300px"></div>
  </div>
</div>
<div class="bg-white rounded shadow p-3 mt-4">
  <div class="flex justify-between items-center mb-2"><h2 class="font-semibold text-sm">Ranking penduduk per kecamatan</h2><a href="{{ route('komparasi') }}" class="text-xs text-blue-600 underline">Komparasi lengkap →</a></div>
  <table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-2 text-left">#</th><th class="p-2 text-left">Kecamatan</th><th class="p-2 text-right">Penduduk</th></tr></thead>
  <tbody>@foreach($ranking->take(10) as $i => $r)<tr class="border-t"><td class="p-2">{{ $i+1 }}</td><td class="p-2">{{ $r->kecamatan->name ?? '-' }}</td><td class="p-2 text-right">{{ number_format($r->value) }}</td></tr>@endforeach</tbody></table>
</div>
@push('scripts')
<script>
new Chart(document.getElementById('ch-modul'), {type: 'bar', data: {labels: @json(array_values(\App\Models\Facility::MODULES)), datasets: [{data: @json(array_map(fn($k) => $perModul[$k] ?? 0, array_keys(\App\Models\Facility::MODULES)))}]}});
const map = L.map('dash-map').setView([-7.03, 107.6], 10);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
const cluster = L.markerClusterGroup();
@json($markers).forEach(p => cluster.addLayer(L.circleMarker([p.latitude, p.longitude], {radius: 3}).bindTooltip(p.name)));
map.addLayer(cluster);
</script>
@endpush
@endsection
