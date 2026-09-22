<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','Dashboard') — SiCepatKeg</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
<nav class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-50">
  <a href="/dashboard" class="font-bold text-lg">⚡ SiCepatKeg <span class="text-xs font-normal text-slate-300">Percepatan Kinerja & Kegiatan</span></a>
  <div class="hidden md:flex gap-4 text-sm items-center">
    <a href="/dashboard" class="hover:text-yellow-300">Dashboard</a>
    <a href="/pantau" target="_blank" title="Halaman pantauan publik (tanpa login)" class="font-bold bg-yellow-400 text-slate-900 px-2 py-0.5 rounded hover:bg-yellow-300">📊 Pantau</a>
    <a href="/kegiatan" class="hover:text-yellow-300">Kegiatan</a>
    <a href="/kalender" class="hover:text-yellow-300">Kalender</a>
    <a href="/verifikasi" class="hover:text-yellow-300">Verifikasi</a>
    <a href="/laporan" class="hover:text-yellow-300">Laporan</a>
    <a href="/unit-kerja" class="hover:text-yellow-300">Unit Kerja</a>
    @role('admin')
    <a href="/import" class="hover:text-yellow-300">Import</a>
    <a href="/pengguna" class="hover:text-yellow-300">Pengguna</a>
    @endrole
    <a href="/notifikasi" class="hover:text-yellow-300">Notifikasi ({{ auth()->user()->unreadNotifications()->count() }})</a>
  </div>
  <div class="flex items-center gap-2 text-sm">
    <span class="hidden sm:inline">{{ auth()->user()->name }} <span class="text-slate-300">({{ auth()->user()->getRoleNames()->first() }} · {{ auth()->user()->section->short_name ?? '-' }})</span></span>
    <form action="/logout" method="POST">@csrf<button class="bg-red-600 px-3 py-1 rounded">Keluar</button></form>
  </div>
</nav>
<div class="md:hidden bg-slate-800 text-white text-xs flex gap-3 px-4 py-2 overflow-x-auto">
  <a href="/dashboard">Dashboard</a><a href="/pantau" target="_blank" class="font-bold bg-yellow-400 text-slate-900 px-2 py-0.5 rounded">📊 Pantau</a><a href="/kegiatan">Kegiatan</a><a href="/kalender">Kalender</a>
  <a href="/verifikasi">Verifikasi</a><a href="/laporan">Laporan</a><a href="/unit-kerja">Unit</a>
  @role('admin')<a href="/import">Import</a><a href="/pengguna">User</a>@endrole
  <a href="/notifikasi">Notifikasi</a>
</div>
<main class="max-w-7xl mx-auto p-4">
  @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4"><ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  @yield('content')
</main>
<footer class="text-center text-xs text-slate-500 py-6">© 2026 SiCepatKeg — Sistem Informasi Percepatan Kinerja & Kegiatan Instansi</footer>
@yield('scripts')
</body>
</html>
