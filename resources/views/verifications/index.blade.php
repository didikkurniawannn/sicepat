@extends('layout')
@section('title','Verifikasi')
@section('content')
<h1 class="text-xl font-bold mb-3">Verifikasi & Validasi</h1>
<h2 class="font-semibold mb-2">Antrian (Diajukan / Diverifikasi)</h2>
<div class="bg-white rounded shadow overflow-x-auto mb-6">
<table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Kegiatan</th><th class="p-2">Status</th><th class="p-2">Aksi</th></tr></thead>
<tbody>@foreach($queue as $a)<tr class="border-t">
<td class="p-2">{{ $a->activity_date->translatedFormat('d F Y') }}</td>
<td class="p-2"><a href="/kegiatan/{{ $a->id }}" class="text-blue-700 hover:underline">{{ $a->title }}</a><br><span class="text-xs text-slate-500">{{ $a->section->name }}</span></td>
<td class="p-2 text-center text-xs">{{ $a->status }}</td>
<td class="p-2"><form action="/verifikasi/{{ $a->id }}" method="POST" class="flex gap-1">@csrf
<select name="decision" class="border rounded text-xs px-1 py-0.5"><option value="diverifikasi">Verifikasi</option><option value="disetujui">Setujui</option><option value="revisi">Revisi</option><option value="ditolak">Tolak</option></select>
<input name="note" placeholder="Catatan" class="border rounded text-xs px-1 py-0.5 w-32"><button class="bg-slate-900 text-white text-xs px-2 rounded">OK</button></form></td>
</tr>@endforeach</tbody></table>
</div>
<div>{{ $queue->links() }}</div>
<h2 class="font-semibold mb-2 mt-6">Riwayat Keputusan</h2>
<div class="bg-white rounded shadow p-3 text-sm space-y-2">
@forelse($history as $v)<div class="border-b pb-1"><span class="font-medium">{{ $v->decision }}</span> — <a href="/kegiatan/{{ $v->activity_id }}" class="text-blue-700">{{ $v->activity->title ?? '-' }}</a> oleh {{ $v->user->name }} <span class="text-slate-500">({{ $v->created_at }})</span><br><span class="text-slate-600">{{ $v->note }}</span></div>@empty<p class="text-slate-500">Belum ada.</p>@endforelse
</div>
<div class="mt-2">{{ $history->links() }}</div>
@endsection
