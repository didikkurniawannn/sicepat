@extends('layout')
@section('title','Preview Import')
@section('content')
<h1 class="text-xl font-bold mb-3">Preview 10 Baris Pertama (dry-run)</h1>
<div class="bg-white rounded shadow overflow-x-auto mb-4">
<table class="w-full text-xs"><thead class="bg-slate-100"><tr>@foreach(array_keys($rows->first()->toArray() ?? []) as $h)<th class="p-1 border">{{ $h }}</th>@endforeach</tr></thead>
<tbody>@foreach($rows as $r)<tr>@foreach($r->toArray() as $v)<td class="p-1 border">{{ is_scalar($v) ? \Str::limit((string)$v, 40) : '-' }}</td>@endforeach</tr>@endforeach</tbody></table>
</div>
<form action="/import/proses" method="POST" class="flex gap-2">@csrf<button class="bg-green-600 text-white px-4 py-2 rounded">Konfirmasi & Simpan ke Database</button><a href="/import" class="bg-slate-200 px-4 py-2 rounded">Batal</a></form>
@endsection
