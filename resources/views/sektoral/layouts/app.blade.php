<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard Sektoral') — Kab. Bandung</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@stack('head')
</head>
<body class="bg-slate-100 text-slate-800">
<div class="min-h-screen flex">
  <aside class="w-60 bg-slate-900 text-slate-200 hidden md:flex flex-col">
    <div class="p-4 border-b border-slate-700">
      <div class="font-bold text-white">Dash Sektoral</div>
      <div class="text-xs text-slate-400">Kab. Bandung · 31 Kecamatan</div>
      <div class="text-xs mt-1 text-emerald-300">{{ auth()->user()->getRoleNames()->join(', ') }} @if(auth()->user()->kecamatan) · {{ auth()->user()->kecamatan->name }} @endif</div>
    </div>
    <nav class="p-3 space-y-1 text-sm flex-1">
      <a href="{{ route('sektoral.home') }}" class="block px-3 py-2 rounded hover:bg-slate-700">🌐 Beranda Publik</a>
      <a href="{{ route('sektoral.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Dashboard</a>
      <div class="px-3 pt-3 pb-1 text-xs uppercase text-slate-500">Data Sektoral</div>
      @foreach(\App\Models\Facility::MODULES as $kode => $nama)
        <a href="{{ route('facilities.index', ['modul' => $kode]) }}" class="block px-3 py-2 rounded hover:bg-slate-700">{{ $nama }}</a>
      @endforeach
      <a href="{{ route('data-entries.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Kependudukan & Indikator</a>
      @if(auth()->user()->canWrite())
      <a href="{{ route('import.facilities') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Import CSV Fasilitas</a>
      <a href="{{ route('import.entries') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Import CSV Sektoral</a>
      @endif
      <div class="px-3 pt-3 pb-1 text-xs uppercase text-slate-500">Analisis</div>
      <a href="{{ route('komparasi') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Komparasi 31 Kecamatan</a>
      <a href="{{ route('gis.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">🗺️ Dashboard GIS</a>
      <a href="{{ route('ai.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">AI Recommendation</a>
      <div class="px-3 pt-3 pb-1 text-xs uppercase text-slate-500">Administrasi</div>
      @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('kecamatans.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Manajemen Tenant</a>
      @elseif(auth()->user()->kecamatan_id)
        <a href="{{ route('kecamatans.show', auth()->user()->kecamatan_id) }}" class="block px-3 py-2 rounded hover:bg-slate-700">Profil Wilayah</a>
      @endif
      @if(auth()->user()->hasAnyRole(['superadmin','admin','operator']))
        <a href="{{ route('pengguna.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Manajemen User</a>
      @endif
      <a href="{{ route('audit.index') }}" class="block px-3 py-2 rounded hover:bg-slate-700">Audit Log</a>
      <div class="px-3 pt-3 pb-1 text-xs uppercase text-slate-500">Super Apps</div>
      <a href="/" class="block px-3 py-2 rounded hover:bg-slate-700">🏛️ Beranda Super Apps</a>
      <a href="/dashboard" class="block px-3 py-2 rounded hover:bg-slate-700">⚡ SiCepatKeg</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="p-3">@csrf
      <button class="w-full text-left px-3 py-2 rounded bg-slate-800 hover:bg-slate-700 text-sm">Logout ({{ auth()->user()->name }})</button>
    </form>
  </aside>
  <div class="md:hidden bg-slate-900 text-white text-xs flex gap-3 px-4 py-2 overflow-x-auto sticky top-0 z-40">
    <a href="{{ route('sektoral.dashboard') }}" class="whitespace-nowrap">Dashboard</a>
    <a href="{{ route('facilities.index') }}" class="whitespace-nowrap">Fasilitas</a>
    <a href="{{ route('data-entries.index') }}" class="whitespace-nowrap">Sektoral</a>
    <a href="{{ route('komparasi') }}" class="whitespace-nowrap">Komparasi</a>
    <a href="{{ route('gis.index') }}" class="whitespace-nowrap">GIS</a>
    <a href="{{ route('ai.index') }}" class="whitespace-nowrap">AI</a>
    <a href="/" class="whitespace-nowrap">🏛️ Super Apps</a>
  </div>
  <main class="flex-1 p-4 md:p-6 max-w-6xl w-full mx-auto">
    @if(session('success'))<div class="mb-4 p-3 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded"><ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @yield('content')
  </main>
</div>
@stack('scripts')
</body>
</html>
