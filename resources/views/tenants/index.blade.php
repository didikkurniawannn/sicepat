@extends('layout')
@section('title','Kecamatan')
@section('content')
<h1 class="text-xl font-bold mb-1">Data Kecamatan ({{ $kecamatans->count() }})</h1>
<p class="text-sm text-slate-500 mb-4">Multi-tenant: tiap kecamatan mengakses datanya masing-masing. Akun admin per kecamatan: <code class="bg-slate-200 px-1 rounded">admin-&lt;slug&gt;@sicepatkeg.local</code> / <code class="bg-slate-200 px-1 rounded">password123</code>. Pantauan publik: <code class="bg-slate-200 px-1 rounded">/pantau/&lt;slug&gt;</code>.</p>
<div class="bg-white rounded shadow p-4 mb-4">
<h2 class="font-semibold mb-2 text-sm">Tambah Kecamatan</h2>
<form method="POST" action="/kecamatan" class="grid md:grid-cols-4 gap-2 text-sm">@csrf
<input name="code" required placeholder="Kode (mis. CKU)" class="border rounded px-2 py-1">
<input name="name" required placeholder="Nama (mis. Kecamatan Cibiru)" class="border rounded px-2 py-1">
<input name="slug" required placeholder="Slug (mis. cibiru)" class="border rounded px-2 py-1">
<button class="bg-slate-900 text-white px-3 py-1 rounded">Simpan</button>
</form>
</div>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-center">No</th><th class="p-2 text-left">Kode</th><th class="p-2 text-left">Nama</th><th class="p-2 text-left">Slug / Link Pantau</th><th class="p-2 text-center">Kegiatan</th><th class="p-2 text-center">User</th><th class="p-2">Status</th><th class="p-2">Aksi</th></tr></thead>
<tbody>@foreach($kecamatans as $k)<tr class="border-t">
<td class="p-2 text-center">{{ $loop->iteration }}</td>
<td class="p-2 font-mono">{{ $k->code }}</td>
<td class="p-2 font-medium">{{ $k->name }}</td>
<td class="p-2"><a href="/pantau/{{ $k->slug }}" target="_blank" class="text-blue-700 hover:underline font-mono text-xs">/pantau/{{ $k->slug }}</a></td>
<td class="p-2 text-center">{{ $k->jml_kegiatan }}</td>
<td class="p-2 text-center">{{ $k->jml_user }}</td>
<td class="p-2 text-center"><span class="text-xs px-2 py-0.5 rounded {{ $k->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-200' }}">{{ $k->is_active ? 'aktif' : 'nonaktif' }}</span></td>
<td class="p-2 text-center"><form action="/kecamatan/{{ $k->id }}/toggle" method="POST" class="inline">@csrf<button class="text-xs bg-slate-200 px-2 py-0.5 rounded">{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td>
</tr>@endforeach</tbody></table></div>
@endsection
