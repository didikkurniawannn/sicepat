<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pantauan Kegiatan — SiCepatKeg</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<meta http-equiv="refresh" content="300">
<style>
  #tooltip { position: fixed; z-index: 100; max-width: 320px; pointer-events: none; display: none; }
  .fc-event { cursor: pointer; }
</style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
<header class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50">
  <div>
    <span class="font-bold text-lg">⚡ SiCepatKeg</span>
    <span class="text-xs text-slate-300 ml-2">Pantauan Kegiatan — tanpa login · data per {{ now()->format('d M Y H:i') }} WIB · refresh otomatis 5 menit</span>
  </div>
  <a href="/login" class="bg-yellow-400 text-slate-900 text-sm font-semibold px-3 py-1 rounded">Login Petugas</a>
</header>

<main class="max-w-7xl mx-auto p-4 space-y-6">
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <div class="bg-white rounded shadow p-4"><p class="text-xs text-slate-500">Total Kegiatan</p><p class="text-2xl font-bold">{{ $total }}</p></div>
    <div class="bg-white rounded shadow p-4 border-2 border-red-400"><p class="text-xs text-slate-500">⚠ 7 Hari Ke Depan</p><p class="text-2xl font-bold text-red-600">{{ $totalH7 }}</p></div>
    <div class="bg-white rounded shadow p-4 md:col-span-2"><p class="text-xs text-slate-500">Legenda Unit Kerja</p>
      <div class="flex flex-wrap gap-1 mt-1">@foreach($sections as $s)<span class="text-xs px-2 py-0.5 rounded text-white" style="background:{{ $s->color }}">{{ $s->short_name }} ({{ $s->activity_count }})</span>@endforeach</div>
    </div>
  </div>

  <section class="grid md:grid-cols-3 gap-4">
    <div class="bg-white rounded shadow p-4 md:col-span-1">
      <h2 class="font-bold mb-2">📅 Kegiatan 7 Hari Ke Depan ({{ $upcoming7->count() }})</h2>
      <ul class="text-sm space-y-2 max-h-[520px] overflow-y-auto">
        @forelse($upcoming7 as $a)
        <li class="border-l-4 pl-2" style="border-color:{{ $a->section->color }}">
          <span class="font-semibold">{{ $a->activity_date->format('d M Y') }}</span>
          @if($a->is_h7)<span class="text-xs bg-red-600 text-white px-1 rounded">H-{{ $a->days_to_event }}</span>@endif
          <br>{{ $a->title }}
          <br><span class="text-xs text-slate-500">{{ $a->section->name }} · {{ $a->status }} · Rp {{ number_format($a->budget_pagu,0,',','.') }}</span>
        </li>
        @empty
        <li class="text-slate-500">Tidak ada kegiatan dalam 7 hari ke depan.</li>
        @endforelse
      </ul>
    </div>
    <div class="bg-white rounded shadow p-4 md:col-span-2">
      <div class="flex flex-wrap items-center gap-2 mb-3">
        <h2 class="font-bold flex-1">🗓️ Kalender Kegiatan <span class="text-xs font-normal text-slate-500">(arahkan kursor / sorot untuk detail, klik untuk rincian penuh)</span></h2>
        <select id="fSection" class="border rounded px-2 py-1 text-sm">
          <option value="">Semua Unit</option>
          @foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
      </div>
      <div id="cal"></div>
    </div>
  </section>

  <section>
    <h2 class="font-bold text-lg mb-2">Jumlah Kegiatan per Kasi / Kasubbag</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      @foreach($sections as $s)
      <div class="bg-white rounded shadow p-4 border-t-4" style="border-color:{{ $s->color }}">
        <p class="font-semibold text-sm">{{ $s->name }}</p>
        <p class="text-xs text-slate-500">{{ $s->code }} · {{ $s->type }} · Ka. Unit: {{ $s->head_name ?? '-' }}</p>
        <p class="mt-2 text-2xl font-bold">{{ $s->activity_count }} <span class="text-xs font-normal text-slate-500">kegiatan</span></p>
        <p class="text-xs">7 hari ke depan: <span class="font-bold {{ $s->upcoming7 ? 'text-red-600' : '' }}">{{ $s->upcoming7 }}</span></p>
        <p class="text-xs">Pagu Rp {{ number_format($s->total_pagu,0,',','.') }}</p>
        <p class="text-xs text-green-700">Realisasi Rp {{ number_format($s->total_realisasi,0,',','.') }}</p>
      </div>
      @endforeach
    </div>
  </section>
</main>

<div id="tooltip" class="bg-slate-900 text-white text-xs rounded-lg shadow-xl p-3"></div>

<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[90] p-4">
  <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-5 relative">
    <button onclick="closeModal()" class="absolute top-2 right-3 text-xl text-slate-400 hover:text-slate-700">×</button>
    <div id="modalBody" class="text-sm"></div>
  </div>
</div>

<footer class="text-center text-xs text-slate-500 py-6">© 2026 SiCepatKeg — Halaman pantauan publik, dapat diakses tanpa login</footer>

<script>
const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const tip = document.getElementById('tooltip');

function tipHtml(p) {
  return `<p class="font-bold text-sm mb-1">${p.judul}</p>
    <p>📅 ${p.tanggal} ${p.is_h7 ? '· ⚠ H-' + p.days : ''}</p>
    <p>🏢 ${p.bidang} · <span class="bg-slate-700 px-1 rounded">${p.status}</span></p>
    <p class="mt-1 text-slate-300">${p.kode_rekening}</p>
    <p>Pagu ${fmt(p.pagu)} · Realisasi ${fmt(p.realisasi)} · Sisa ${fmt(p.sisa)}</p>
    <p class="text-slate-400 mt-1">Sorot = ringkas · Klik = rincian penuh</p>`;
}

function modalHtml(p) {
  return `<h3 class="font-bold text-lg mb-1">${p.judul}</h3>
    <p class="text-xs mb-3"><span class="px-2 py-0.5 rounded text-white" style="background:${p.section_color}">${p.bidang}</span>
    ${p.is_h7 ? '<span class="bg-red-600 text-white px-2 py-0.5 rounded ml-1">⚠ H-' + p.days + ' perlu persiapan</span>' : ''}</p>
    <table class="w-full">
      <tr class="border-t"><td class="py-1 text-slate-500 w-32">Tanggal</td><td class="font-medium">${p.tanggal}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Kode Rekening</td><td class="font-mono">${p.kode_rekening}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Program</td><td>${p.program}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Kebutuhan</td><td>${p.kebutuhan}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Pagu</td><td>${fmt(p.pagu)}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Realisasi</td><td>${fmt(p.realisasi)}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Sisa</td><td class="font-bold">${fmt(p.sisa)}</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Status / Progress</td><td>${p.status} · ${p.progress}%</td></tr>
      <tr class="border-t"><td class="py-1 text-slate-500">Lokasi / Penanggung Jawab</td><td>${p.lokasi} / ${p.pptk}</td></tr>
    </table>`;
}

function closeModal(){ document.getElementById('modal').classList.add('hidden'); document.getElementById('modal').classList.remove('flex'); }
document.getElementById('modal').addEventListener('click', e => { if (e.target.id === 'modal') closeModal(); });

const calendar = new FullCalendar.Calendar(document.getElementById('cal'), {
  initialView: 'dayGridMonth', locale: 'id', height: 'auto',
  headerToolbar: {left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listMonth'},
  events: function(info, success, failure){
    fetch('/api/pantau/events?section_id=' + document.getElementById('fSection').value)
      .then(r => r.json()).then(success).catch(failure);
  },
  eventMouseEnter: function(info){
    tip.innerHTML = tipHtml(info.event.extendedProps);
    tip.style.display = 'block';
  },
  eventMouseLeave: function(){ tip.style.display = 'none'; },
  eventClick: function(info){
    info.jsEvent.preventDefault();
    document.getElementById('modalBody').innerHTML = modalHtml(info.event.extendedProps);
    const m = document.getElementById('modal');
    m.classList.remove('hidden'); m.classList.add('flex');
    tip.style.display = 'none';
  }
});
calendar.render();
document.addEventListener('mousemove', e => {
  if (tip.style.display !== 'block') return;
  const w = 330, h = tip.offsetHeight || 200;
  tip.style.left = Math.min(e.clientX + 14, window.innerWidth - w) + 'px';
  tip.style.top = Math.min(e.clientY + 14, window.innerHeight - h - 10) + 'px';
});
document.getElementById('fSection').addEventListener('change', () => calendar.refetchEvents());
</script>
</body>
</html>
