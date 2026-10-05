@extends('sektoral.layouts.app')
@section('title', 'AI Recommendation')
@section('content')
<div class="flex justify-between items-center mb-4"><h1 class="text-xl font-bold">AI Recommendation</h1><a href="{{ route('ai.create') }}" class="px-3 py-2 bg-slate-900 text-white rounded text-sm">+ Generate</a></div>
<div class="space-y-2">@forelse($items as $it)<a href="{{ route('ai.show', $it) }}" class="block bg-white rounded shadow p-3 hover:bg-slate-50"><div class="font-semibold text-sm">{{ $it->title }}</div><div class="text-xs text-slate-500">{{ $it->kecamatan->name ?? 'Kabupaten' }} · {{ $it->engine }} · {{ $it->created_at->diffForHumans() }}</div></a>@empty<p class="text-sm text-slate-400">Belum ada rekomendasi. Klik Generate.</p>@endforelse</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
