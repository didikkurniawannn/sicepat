@extends('layout')
@section('title','Notifikasi')
@section('content')
<h1 class="text-xl font-bold mb-3">Notifikasi In-App</h1>
<div class="bg-white rounded shadow p-3 text-sm space-y-2">
@forelse($notifs as $n)<div class="border-b pb-1"><p class="font-medium">{{ $n->title }}</p><p class="text-slate-600">{{ $n->message }}</p><p class="text-xs text-slate-400">{{ $n->created_at }} @if($n->activity_id)· <a class="text-blue-700" href="/kegiatan/{{ $n->activity_id }}">Lihat kegiatan</a>@endif</p></div>@empty<p class="text-slate-500">Tidak ada notifikasi.</p>@endforelse
</div>
<div class="mt-2">{{ $notifs->links() }}</div>
@endsection
