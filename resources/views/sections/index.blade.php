@extends('layout')
@section('title','Unit Kerja')
@section('content')
<h1 class="text-xl font-bold mb-3">Unit Kerja (7 unit)</h1>
<div class="grid md:grid-cols-2 gap-3">
@foreach($sections as $s)
<a href="/unit-kerja/{{ $s->id }}" class="bg-white rounded shadow p-4 border-t-4" style="border-color:{{ $s->color }}">
  <p class="font-bold">{{ $s->name }}</p>
  <p class="text-xs text-slate-500">{{ $s->code }} · {{ $s->type }} · Kepala: {{ $s->head_name ?? '-' }}</p>
  <p class="mt-2 text-sm">{{ $s->activities_count }} kegiatan · Pagu Rp {{ number_format($s->total_pagu,0,',','.') }} · Realisasi Rp {{ number_format($s->total_realisasi,0,',','.') }}</p>
</a>
@endforeach
</div>
@endsection
