@extends('layout')
@section('title', $section->name)
@section('content')
<h1 class="text-xl font-bold">{{ $section->name }}</h1>
<p class="text-sm text-slate-500 mb-3">{{ $section->activities()->count() }} kegiatan · Pagu Rp {{ number_format($stats['pagu'],0,',','.') }} · Realisasi Rp {{ number_format($stats['realisasi'],0,',','.') }} · Sisa Rp {{ number_format($stats['sisa'],0,',','.') }}</p>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Judul</th><th class="p-2 text-right">Pagu</th><th class="p-2">Status</th></tr></thead>
<tbody>@foreach($activities as $a)<tr class="border-t"><td class="p-2">{{ $a->activity_date->format('Y-m-d') }}</td><td class="p-2"><a class="text-blue-700" href="/kegiatan/{{ $a->id }}">{{ $a->title }}</a></td><td class="p-2 text-right">{{ number_format($a->budget_pagu,0,',','.') }}</td><td class="p-2 text-center text-xs">{{ $a->status }}</td></tr>@endforeach</tbody></table></div>
<div class="mt-2">{{ $activities->links() }}</div>
@endsection
