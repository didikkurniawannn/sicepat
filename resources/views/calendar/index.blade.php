@extends('layout')
@section('title','Kalender')
@section('content')
<h1 class="text-xl font-bold mb-2">Kalender Kegiatan</h1>
<div class="flex flex-wrap gap-2 text-xs mb-3">
  @foreach($sections as $s)<span class="px-2 py-0.5 rounded text-white" style="background:{{ $s->color }}">{{ $s->short_name }}</span>@endforeach
  <span class="px-2 py-0.5 rounded bg-red-600 text-white">⚠ H-7 butuh persiapan</span>
</div>
<div class="bg-white rounded shadow p-3">
  <div class="flex gap-2 text-sm mb-3">
    <select id="fSection" class="border rounded px-2 py-1"><option value="">Semua Unit</option>@foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
    <select id="fStatus" class="border rounded px-2 py-1"><option value="">Semua Status</option><option>draft</option><option>diajukan</option><option>diverifikasi</option><option>disetujui</option><option>berjalan</option><option>selesai</option><option>ditolak</option></select>
  </div>
  <div id="cal"></div>
</div>
@endsection
@section('scripts')
<script>
const calEl = document.getElementById('cal');
const calendar = new FullCalendar.Calendar(calEl, {
  initialView: 'dayGridMonth', locale: 'id', height: 'auto',
  headerToolbar: {left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listMonth'},
  events: function(info, success, failure){
    const p = new URLSearchParams({section_id: document.getElementById('fSection').value, status: document.getElementById('fStatus').value});
    fetch('/api/kalender?'+p).then(r=>r.json()).then(success).catch(failure);
  }
});
calendar.render();
document.getElementById('fSection').addEventListener('change', ()=>calendar.refetchEvents());
document.getElementById('fStatus').addEventListener('change', ()=>calendar.refetchEvents());
</script>
@endsection
