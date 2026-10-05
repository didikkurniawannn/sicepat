@extends('sektoral.layouts.app')
@section('title', 'Audit Log')
@section('content')
<h1 class="text-xl font-bold mb-4">Audit Log (immutable)</h1>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-xs">
<thead class="bg-slate-50"><tr><th class="p-2 text-left">Waktu</th><th class="p-2 text-left">User</th><th class="p-2">Aksi</th><th class="p-2 text-left">Model</th><th class="p-2">IP</th></tr></thead>
<tbody>@forelse($logs as $l)<tr class="border-t"><td class="p-2">{{ $l->created_at }}</td><td class="p-2">{{ $l->user->email ?? '?' }}</td><td class="p-2 text-center font-mono">{{ $l->action }}</td><td class="p-2">{{ class_basename($l->model_type ?? '') }} #{{ $l->model_id }}</td><td class="p-2">{{ $l->ip }}</td></tr>@empty<tr><td colspan="5" class="p-4 text-center text-slate-400">Belum ada log.</td></tr>@endforelse</tbody></table></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
