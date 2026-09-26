@extends('layout')
@section('title','Dashboard')
@section('content')
<h1 class="text-2xl font-bold mb-1">Dashboard Monitoring</h1>
<p class="text-sm text-slate-500 mb-4">Pemantauan real-time percepatan kinerja & kegiatan · Pagu Rp {{ number_format($pagu,0,',','.') }} · Realisasi Rp {{ number_format($realisasi,0,',','.') }} · Sisa Rp {{ number_format($sisa,0,',','.') }}</p>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
  <div class="bg-white rounded shadow p-4"><p class="text-xs text-slate-500">Total Kegiatan</p><p class="text-2xl font-bold">{{ $total }}</p></div>
  <div class="bg-white rounded shadow p-4"><p class="text-xs text-slate-500">Berjalan / Proses</p><p class="text-2xl font-bold text-blue-600">{{ $berjalan }}</p></div>
  <div class="bg-white rounded shadow p-4"><p class="text-xs text-slate-500">Selesai</p><p class="text-2xl font-bold text-green-600">{{ $selesai }}</p></div>
  <div class="bg-white rounded shadow p-4"><p class="text-xs text-slate-500">Ditolak</p><p class="text-2xl font-bold text-red-600">{{ $ditolak }}</p></div>
  <div class="bg-white rounded shadow p-4 border-2 border-red-400"><p class="text-xs text-slate-500">⚠ H-7 (butuh persiapan Kasi)</p><p class="text-2xl font-bold text-red-600">{{ $h7 }}</p><a href="/laporan/h7" class="text-xs text-blue-700 hover:underline">Buka pengingat H-7 →</a></div>
</div>

<h2 class="font-bold mb-2">Kegiatan per Unit Kerja (7 unit)</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  @foreach($perSection as $x)
  <a href="/unit-kerja/{{ $x['section']->id }}" class="bg-white rounded shadow p-4 border-t-4" style="border-color:{{ $x['section']->color }}">
    <p class="font-semibold text-sm">{{ $x['section']->name }}</p>
    <p class="text-xs text-slate-500">{{ $x['section']->code }} · {{ $x['section']->type }}</p>
    <p class="mt-2 text-lg font-bold">{{ $x['count'] }} kegiatan</p>
    <p class="text-xs">Pagu Rp {{ number_format($x['pagu'],0,',','.') }}</p>
    <p class="text-xs text-green-700">Realisasi Rp {{ number_format($x['realisasi'],0,',','.') }}</p>
  </a>
  @endforeach
</div>

<div class="grid md:grid-cols-2 gap-4 mb-6">
  <div class="bg-white rounded shadow p-4"><h3 class="font-semibold mb-2">Grafik Jumlah Kegiatan per Unit</h3><canvas id="chCount"></canvas></div>
  <div class="bg-white rounded shadow p-4"><h3 class="font-semibold mb-2">Grafik Realisasi per Unit (Rp)</h3><canvas id="chReal"></canvas></div>
</div>

<div class="grid md:grid-cols-3 gap-4">
  <div class="bg-white rounded shadow p-4"><h3 class="font-semibold mb-2">Upcoming 30 Hari</h3>
    <ul class="text-sm space-y-2">@forelse($upcoming as $a)<li><a class="text-blue-700 hover:underline" href="/kegiatan/{{ $a->id }}">{{ $a->activity_date->translatedFormat('d F Y') }} — {{ $a->title }}</a> <span class="text-xs text-slate-500">({{ $a->section->short_name }})</span>@if($a->is_h7)<span class="text-xs bg-red-100 text-red-700 px-1 rounded">H-{{ $a->days_to_event }}</span>@endif</li>@empty<li class="text-slate-500">Tidak ada.</li>@endforelse</ul>
  </div>
  <div class="bg-white rounded shadow p-4"><h3 class="font-semibold mb-2">Top 5 Pagu Terbesar</h3>
    <ul class="text-sm space-y-2">@foreach($top5 as $a)<li><a class="text-blue-700 hover:underline" href="/kegiatan/{{ $a->id }}">{{ $a->title }}</a><br><span class="text-xs text-slate-500">Rp {{ number_format($a->budget_pagu,0,',','.') }} · {{ $a->section->short_name }}</span></li>@endforeach</ul>
  </div>
  <div class="bg-white rounded shadow p-4"><h3 class="font-semibold mb-2">Belum Ada Realisasi (Rp 0)</h3>
    <ul class="text-sm space-y-2">@foreach($zeroRealisasi as $a)<li><a class="text-blue-700 hover:underline" href="/kegiatan/{{ $a->id }}">{{ $a->activity_date->translatedFormat('d F') }} — {{ $a->title }}</a></li>@endforeach</ul>
  </div>
</div>
@endsection
@section('scripts')
<script>
new Chart(document.getElementById('chCount'), {type:'bar', data:{labels:@json($chartLabels), datasets:[{data:@json($chartCounts), backgroundColor:'#3B82F6'}]}, options:{plugins:{legend:{display:false}}}});
new Chart(document.getElementById('chReal'), {type:'bar', data:{labels:@json($chartLabels), datasets:[{data:@json($chartRealisasi), backgroundColor:'#10B981'}]}, options:{plugins:{legend:{display:false}}}});
</script>
@endsection
