<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name') }} — Layanan & Kinerja Kecamatan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

<!-- NAVBAR -->
<nav class="bg-slate-900/95 backdrop-blur text-white sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="/" class="font-bold text-lg">🏛️ {{ config('app.name') }}</a>
    <div class="hidden md:flex items-center gap-6 text-sm">
      <a href="#beranda" class="hover:text-yellow-300">Beranda</a>
      <a href="#modul" class="hover:text-yellow-300">Modul</a>
      <a href="#sorotan" class="hover:text-yellow-300">Sorotan</a>
      <a href="#wilayah" class="hover:text-yellow-300">Wilayah</a>
      <a href="#fitur" class="hover:text-yellow-300">Fitur</a>
      <a href="#wilayah" class="hover:text-yellow-300">Pantauan</a>
      <a href="/login" class="bg-yellow-400 text-slate-900 font-semibold px-4 py-1.5 rounded-lg hover:bg-yellow-300">Masuk Aplikasi</a>
    </div>
    <button id="btnMenu" class="md:hidden text-2xl px-2" aria-label="Menu">☰</button>
  </div>
  <div id="menuMobile" class="hidden md:hidden px-4 pb-4 flex flex-col gap-2 text-sm border-t border-slate-700">
    <a href="#beranda" class="py-1">Beranda</a>
    <a href="#modul" class="py-1">Modul</a>
    <a href="#sorotan" class="py-1">Sorotan</a>
    <a href="#wilayah" class="py-1">Wilayah</a>
    <a href="#fitur" class="py-1">Fitur</a>
    <a href="#wilayah" class="py-1">Pantauan</a>
    <a href="/login" class="bg-yellow-400 text-slate-900 font-semibold px-4 py-2 rounded-lg text-center">Masuk Aplikasi</a>
  </div>
</nav>

<!-- HERO -->
<header id="beranda" class="bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800 text-white overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 py-14 md:py-24 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <span class="inline-block text-xs bg-yellow-400 text-slate-900 font-bold px-3 py-1 rounded-full mb-4">SUPER APPS · 31 KECAMATAN KAB. BANDUNG</span>
      <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">Satu Aplikasi<br>untuk Seluruh <span class="text-yellow-300">Kecamatan</span></h1>
      <p class="mt-4 text-slate-300 text-sm md:text-base">Kinerja kegiatan, data sektoral, peta GIS, dan pantauan H-7 seluruh kecamatan dalam satu genggaman — transparan untuk publik, praktis untuk aparatur.</p>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="/login" class="bg-yellow-400 text-slate-900 font-bold px-6 py-2.5 rounded-xl hover:bg-yellow-300">Masuk Aplikasi →</a>
        <a href="#wilayah" class="border border-white/40 px-6 py-2.5 rounded-xl hover:bg-white/10">📊 Pantauan Publik</a>
      </div>
      <div class="mt-8 grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
        <div class="bg-white/10 rounded-xl p-3"><p class="text-xl md:text-2xl font-extrabold text-yellow-300">{{ $kecamatans->count() }}</p><p class="text-xs text-slate-300">Kecamatan</p></div>
        <div class="bg-white/10 rounded-xl p-3"><p class="text-xl md:text-2xl font-extrabold text-yellow-300">{{ $totalKegiatan }}</p><p class="text-xs text-slate-300">Kegiatan</p></div>
        <div class="bg-white/10 rounded-xl p-3"><p class="text-xl md:text-2xl font-extrabold text-emerald-300">{{ $totalFasilitas }}</p><p class="text-xs text-slate-300">Fasilitas</p></div>
        <div class="bg-white/10 rounded-xl p-3"><p class="text-xl md:text-2xl font-extrabold text-sky-300">{{ $totalDesa }}</p><p class="text-xs text-slate-300">Desa</p></div>
        <div class="bg-white/10 rounded-xl p-3 col-span-2 sm:col-span-1"><p class="text-xl md:text-2xl font-extrabold text-red-300">{{ $h7 }}</p><p class="text-xs text-slate-300">H-7 ke Depan</p></div>
      </div>
    </div>
    <div class="hidden md:block">
      <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10">
        <p class="text-sm font-semibold text-yellow-300 mb-3">🔔 Live — Segera Berlangsung se-Kabupaten</p>
        <div class="space-y-2 text-sm">
          @forelse($sorotan->take(3) as $s)
          <a href="/pantau/{{ $s->kecamatan->slug ?? 'cangkuang' }}" class="block bg-white text-slate-800 rounded-lg p-3 flex items-center gap-3 hover:bg-yellow-50">
            <span class="bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded whitespace-nowrap">H-{{ $s->days_to_event }}</span>
            <span class="truncate">{{ $s->title }} <span class="text-slate-400 text-xs">· {{ $s->kecamatan->name ?? '' }}</span></span>
          </a>
          @empty
          <p class="text-slate-300 text-sm">Tidak ada kegiatan 7 hari ke depan. 🎉</p>
          @endforelse
        </div>
        <p class="text-xs text-slate-400 mt-4">Data live — <a href="#sorotan" class="text-yellow-300 underline">lihat semua sorotan →</a></p>
      </div>
    </div>
  </div>
</header>

<!-- MODUL -->
<section id="modul" class="max-w-7xl mx-auto px-4 py-14">
  <h2 class="text-2xl md:text-3xl font-extrabold text-center">Modul Aplikasi</h2>
  <p class="text-center text-slate-500 text-sm mt-1 mb-8">Dimulai dari percepatan kinerja — bertambah mengikuti kebutuhan kecamatan.</p>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-indigo-600 flex flex-col">
      <p class="text-3xl">⚡</p>
      <h3 class="font-bold mt-2">SiCepatKeg</h3>
      <p class="text-xs text-slate-500 mt-1 flex-1">Percepatan kinerja & kegiatan: kalender H-7, verifikasi berjenjang, laporan realisasi anggaran.</p>
      <div class="mt-4 flex gap-2 text-sm">
        <a href="#wilayah" class="flex-1 text-center bg-indigo-600 text-white px-3 py-1.5 rounded-lg">Pantau</a>
        <a href="/login" class="flex-1 text-center bg-slate-900 text-white px-3 py-1.5 rounded-lg">Masuk</a>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow p-6 border-t-4 border-slate-300 opacity-80 flex flex-col">
      <p class="text-3xl">📝</p>
      <h3 class="font-bold mt-2">Layanan Administrasi</h3>
      <p class="text-xs text-slate-500 mt-1 flex-1">Pengajuan surat & layanan kecamatan secara daring.</p>
      <span class="mt-4 text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1.5 rounded-lg text-center">SEGERA HADIR</span>
    </div>
    <div class="bg-white rounded-2xl shadow p-6 border-t-4 border-slate-300 opacity-80 flex flex-col">
      <p class="text-3xl">📣</p>
      <h3 class="font-bold mt-2">Pengaduan Masyarakat</h3>
      <p class="text-xs text-slate-500 mt-1 flex-1">Saluran aspirasi & pengaduan warga terpadu.</p>
      <span class="mt-4 text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1.5 rounded-lg text-center">SEGERA HADIR</span>
    </div>
    <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-emerald-600 flex flex-col">
      <p class="text-3xl">🗺️</p>
      <h3 class="font-bold mt-2">Data Sektoral</h3>
      <p class="text-xs text-slate-500 mt-1 flex-1">Peta GIS, fasilitas per modul, kependudukan & indikator, komparasi 31 kecamatan, dan rekomendasi AI.</p>
      <div class="mt-4 flex gap-2 text-sm">
        <a href="/sektoral" class="flex-1 text-center bg-emerald-600 text-white px-3 py-1.5 rounded-lg">Peta GIS</a>
        <a href="/sektoral/dashboard" class="flex-1 text-center bg-slate-900 text-white px-3 py-1.5 rounded-lg">Dashboard</a>
      </div>
    </div>
  </div>
</section>

<!-- SOROTAN GLOBAL -->
<section id="sorotan" class="bg-white border-y">
  <div class="max-w-7xl mx-auto px-4 py-14">
    <h2 class="text-2xl md:text-3xl font-extrabold text-center">Sorotan se-Kabupaten</h2>
    <p class="text-center text-slate-500 text-sm mt-1 mb-8">Kegiatan 7 hari ke depan dari seluruh kecamatan — klik untuk pantauan wilayahnya.</p>
    <div class="flex gap-3 overflow-x-auto pb-2">
      @forelse($sorotan as $s)
      <a href="/pantau/{{ $s->kecamatan->slug ?? 'cangkuang' }}" class="min-w-[240px] max-w-[240px] bg-slate-50 border rounded-2xl p-4 hover:shadow-lg hover:-translate-y-0.5 transition">
        <span class="text-xs font-bold text-white bg-red-600 px-2 py-0.5 rounded">H-{{ $s->days_to_event }} · {{ $s->activity_date->translatedFormat('d F Y') }}</span>
        <p class="font-bold text-sm mt-2 line-clamp-2">{{ $s->title }}</p>
        <p class="text-xs text-slate-500 mt-1">🏘️ {{ $s->kecamatan->name ?? '-' }} · {{ $s->section->short_name ?? '' }}</p>
      </a>
      @empty
      <p class="text-slate-500 text-sm">Tidak ada kegiatan dalam 7 hari ke depan. 🎉</p>
      @endforelse
    </div>
  </div>
</section>

<!-- WILAYAH -->
<section id="wilayah" class="max-w-7xl mx-auto px-4 py-14">
  <h2 class="text-2xl md:text-3xl font-extrabold text-center">Pantauan per Kecamatan</h2>
  <p class="text-center text-slate-500 text-sm mt-1 mb-4">Pilih kecamatan untuk melihat kalender & pengingat kegiatan — tanpa login, tanpa data nominal.</p>
  <div class="max-w-md mx-auto mb-8"><input id="cariWilayah" placeholder="🔍 Cari kecamatan..." class="w-full border rounded-xl px-4 py-2 text-sm shadow-sm"></div>
  <div id="gridWilayah" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
    @foreach($kecamatans as $k)
    <a href="/pantau/{{ $k->slug }}" data-nama="{{ strtolower($k->name) }}" class="kartu-wilayah bg-white rounded-xl shadow p-4 hover:shadow-lg hover:-translate-y-0.5 transition text-center">
      <p class="font-bold text-sm">🏘️ {{ $k->name }}</p>
      <p class="text-xs text-blue-700 mt-1">Buka pantauan →</p>
    </a>
    @endforeach
  </div>
  <p id="wilayahKosong" class="hidden text-center text-slate-500 text-sm mt-4">Tidak ada kecamatan yang cocok.</p>
</section>

<!-- FITUR -->
<section id="fitur" class="bg-slate-900 text-white">
  <div class="max-w-7xl mx-auto px-4 py-14">
    <h2 class="text-2xl md:text-3xl font-extrabold text-center">Kenapa {{ config('app.name') }}?</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8 text-sm">
      <div class="bg-white/5 rounded-2xl p-5"><p class="text-2xl">🗓️</p><h3 class="font-bold mt-2">Kalender H-7</h3><p class="text-slate-400 text-xs mt-1">Pengingat otomatis 7 hari sebelum kegiatan agar PPTK siap.</p></div>
      <div class="bg-white/5 rounded-2xl p-5"><p class="text-2xl">✅</p><h3 class="font-bold mt-2">Verifikasi Berjenjang</h3><p class="text-slate-400 text-xs mt-1">Alur Kasi → Admin dengan catatan revisi terdokumentasi.</p></div>
      <div class="bg-white/5 rounded-2xl p-5"><p class="text-2xl">💰</p><h3 class="font-bold mt-2">Realisasi Anggaran</h3><p class="text-slate-400 text-xs mt-1">Pantau pagu vs realisasi per rekening secara transparan.</p></div>
      <div class="bg-white/5 rounded-2xl p-5"><p class="text-2xl">📱</p><h3 class="font-bold mt-2">Responsif & Publik</h3><p class="text-slate-400 text-xs mt-1">Bisa dibuka di HP tanpa login untuk halaman pantauan.</p></div>
    </div>
    <div class="text-center mt-10">
      <a href="/login" class="bg-yellow-400 text-slate-900 font-bold px-8 py-3 rounded-xl hover:bg-yellow-300">Mulai Gunakan Aplikasi →</a>
    </div>
  </div>
</section>

<footer class="text-center text-xs text-slate-500 py-6">© 2026 {{ config('app.name') }} — Kecamatan · Transparan · Akuntabel</footer>

<script>
document.getElementById('btnMenu').addEventListener('click', () => {
  document.getElementById('menuMobile').classList.toggle('hidden');
});
document.getElementById('cariWilayah').addEventListener('input', function(){
  const q = this.value.toLowerCase();
  let n = 0;
  document.querySelectorAll('.kartu-wilayah').forEach(el => {
    const cocok = el.dataset.nama.includes(q);
    el.classList.toggle('hidden', !cocok);
    if (cocok) n++;
  });
  document.getElementById('wilayahKosong').classList.toggle('hidden', n > 0);
});
</script>
</body>
</html>
