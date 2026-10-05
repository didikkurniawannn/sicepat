@extends('sektoral.layouts.app')
@section('title', 'Manajemen Tenant')
@section('content')
<div class="flex justify-between items-center mb-4"><h1 class="text-xl font-bold">Manajemen Tenant (31 Kecamatan)</h1><a href="{{ route('kecamatans.create') }}" class="px-3 py-2 bg-slate-900 text-white rounded text-sm">+ Tenant</a></div>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm">
<thead class="bg-slate-50"><tr><th class="p-2 text-left">Kode BPS</th><th class="p-2 text-left">Nama</th><th class="p-2">Desa</th><th class="p-2">Fasilitas</th><th class="p-2">Penduduk</th><th class="p-2">Status</th><th class="p-2"></th></tr></thead>
<tbody>@foreach($kecamatans as $k)<tr class="border-t"><td class="p-2 font-mono">{{ $k->kode_bps }}</td><td class="p-2"><a href="{{ route('kecamatans.show', $k) }}" class="text-blue-700 underline">{{ $k->name }}</a></td><td class="p-2 text-center">{{ $k->villages_count }}</td><td class="p-2 text-center">{{ $k->facilities_count }}</td><td class="p-2 text-right">{{ number_format($k->jumlah_penduduk ?? 0) }}</td><td class="p-2 text-center">{{ $k->is_active ? '✅' : '⛔' }}</td><td class="p-2 whitespace-nowrap"><a href="{{ route('kecamatans.edit', $k) }}" class="text-blue-600">Edit</a><form method="POST" action="{{ route('kecamatans.toggle', $k) }}" class="inline">@csrf<button class="ml-2 text-amber-600">Toggle</button></form></td></tr>@endforeach</tbody></table></div>
<div class="mt-3">{{ $kecamatans->links() }}</div>
@endsection
