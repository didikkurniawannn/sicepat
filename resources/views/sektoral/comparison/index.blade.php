@extends('sektoral.layouts.app')
@section('title', 'Komparasi Regional')
@section('content')
<h1 class="text-xl font-bold">Komparasi 31 Kecamatan</h1>
<p class="text-sm text-slate-500 mb-3">Ranking & perbandingan indikator · rata-rata: <b>{{ number_format($avg ?? 0, 2) }}</b></p>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex gap-2 text-sm">
  <select name="indicator_id" class="border rounded px-2 py-1">@foreach($indicators as $i)<option value="{{ $i->id }}" @selected($indicator?->id==$i->id)>{{ $i->nama }} ({{ $i->satuan }})</option>@endforeach</select>
  <select name="year" class="border rounded px-2 py-1">@foreach([2024,2025,2026] as $y)<option @selected($year==$y)>{{ $y }}</option>@endforeach</select>
  <button class="bg-slate-200 rounded px-3">Tampilkan</button>
  <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-1 border rounded">Export CSV</a>
</form>
<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-white rounded shadow p-3"><canvas id="ch-rank" height="400"></canvas></div>
  <div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-slate-50"><tr><th class="p-2">Rank</th><th class="p-2 text-left">Kecamatan</th><th class="p-2 text-right">Nilai</th><th class="p-2">Visual</th></tr></thead>
    <tbody>@foreach($rows as $i => $r)<tr class="border-t"><td class="p-2 text-center">{{ $i+1 }}</td><td class="p-2">{{ $r->kecamatan->name ?? '-' }}</td><td class="p-2 text-right">{{ number_format($r->value, 2) }}</td><td class="p-2 w-32"><div class="bg-slate-200 rounded h-2"><div class="bg-emerald-500 h-2 rounded" style="width:{{ round($r->value / $max * 100) }}%"></div></div></td></tr>@endforeach</tbody></table></div>
</div>
@push('scripts')
<script>
new Chart(document.getElementById('ch-rank'), {type: 'bar', data: {labels: @json($rows->take(15)->map(fn($r) => $r->kecamatan->name ?? '-')->values()), datasets: [{label: @json(($indicator?->nama ?? '').' ('.($indicator?->satuan ?? '').')'), data: @json($rows->take(15)->map(fn($r) => (float)$r->value)->values())}]}, options: {indexAxis: 'y'}});
</script>
@endpush
@endsection
