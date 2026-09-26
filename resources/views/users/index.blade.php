@extends('layout')
@section('title','Pengguna')
@section('content')
<h1 class="text-xl font-bold mb-3">Manajemen Pengguna</h1>
<div class="bg-white rounded shadow p-4 mb-4">
<h2 class="font-semibold mb-2 text-sm">Tambah User (wajib 1 unit kerja + role)</h2>
<form method="POST" action="/pengguna" class="grid md:grid-cols-5 gap-2 text-sm">@csrf
<input name="name" required placeholder="Nama" class="border rounded px-2 py-1">
<input name="email" type="email" required placeholder="Email" class="border rounded px-2 py-1">
<input name="password" required placeholder="Password min 8" class="border rounded px-2 py-1">
<select name="role" class="border rounded px-2 py-1"><option value="kasi">kasi (merangkap PPTK)</option><option value="staf">staf</option><option value="admin">admin (verifikasi + pimpinan)</option></select>
<select name="section_id" class="border rounded px-2 py-1">@foreach(\App\Models\Section::active()->orderBy('order')->get() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
<button class="bg-slate-900 text-white px-3 py-1 rounded md:col-span-5">Simpan</button>
</form>
</div>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-left">Nama</th><th class="p-2 text-left">Email</th><th class="p-2">Role</th><th class="p-2">Unit</th></tr></thead>
<tbody>@foreach($users as $u)<tr class="border-t"><td class="p-2">{{ $u->name }}</td><td class="p-2">{{ $u->email }}</td><td class="p-2 text-center text-xs">{{ $u->getRoleNames()->join(', ') }}</td><td class="p-2 text-center text-xs">{{ $u->section->name ?? '-' }}</td></tr>@endforeach</tbody></table></div>
<div class="mt-2">{{ $users->links() }}</div>
@endsection
