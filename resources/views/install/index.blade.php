<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalasi — SiCepatKeg</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-900 min-h-screen flex items-start justify-center p-4 py-10">
<div class="bg-white rounded-xl shadow-xl p-6 md:p-8 w-full max-w-2xl">
  <h1 class="text-2xl font-bold">⚡ Instalasi SiCepatKeg</h1>
  <p class="text-sm text-slate-500 mb-4">Link instalasi data & aplikasi (migrasi database + seed 58 kegiatan dari Data Kegiatan.xlsx + akun demo).</p>

  @if(!empty($installed['done']))
  <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4 text-sm">
    ✅ Aplikasi sudah terinstal: {{ $installed['sections'] }} unit kerja · {{ $installed['activities'] }} kegiatan · {{ $installed['users'] }} user.
    <br><a href="/pantau" class="underline font-semibold">Buka Pantauan</a> · <a href="/login" class="underline font-semibold">Login (admin@sicepatkeg.local / password123)</a>
  </div>
  @endif

  @if(!empty($runSuccess))
  <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4 text-sm">
    🎉 Instalasi berhasil: {{ $installed['sections'] }} unit kerja · {{ $installed['activities'] }} kegiatan · {{ $installed['users'] }} user.
    <br><a href="/pantau" class="underline font-semibold">Buka Pantauan</a> · <a href="/login" class="underline font-semibold">Login</a>
  </div>
  @endif

  @if(!empty($runError))
  <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4 text-sm">❌ {{ $runError }}</div>
  @endif

  <h2 class="font-bold mb-2 text-sm">1. Cek persyaratan server</h2>
  <ul class="text-sm space-y-1 mb-4">
    @foreach($checks as $c)
    <li>{{ $c['ok'] ? '✅' : (!empty($c['optional']) ? '⚠️ (opsional, boleh dilewati)' : '❌') }} {{ $c['item'] }}</li>
    @endforeach
  </ul>

  <h2 class="font-bold mb-2 text-sm">2. Jalankan instalasi (tanpa terminal — cukup klik tombol)</h2>
  @if($errors->any())<div class="bg-red-100 text-red-700 p-2 rounded mb-3 text-sm">Centang konfirmasi terlebih dahulu.</div>@endif
  <form method="POST" action="/install?token={{ $token }}" class="text-sm space-y-3">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label class="flex items-start gap-2 bg-yellow-50 border border-yellow-300 rounded p-3">
      <input type="checkbox" name="confirm" value="1" class="mt-1" required>
      <span>Saya paham instalasi akan <strong>menghapus & membuat ulang</strong> seluruh tabel lalu mengisi data awal (7 unit kerja, 58 kegiatan, 17 akun demo).</span>
    </label>
    <button @disabled(!$allOk) class="w-full bg-slate-900 text-white py-2 rounded font-semibold disabled:opacity-40">🚀 Jalankan Migrasi + Seed Data</button>
    @unless($allOk)<p class="text-red-600 text-xs">Lengkapi persyaratan bertanda ❌ di atas sebelum instalasi.</p>@endunless
  </form>

  @if(!empty($runLog))
  <h2 class="font-bold mt-4 mb-2 text-sm">Log instalasi</h2>
  <pre class="bg-slate-900 text-green-300 text-xs rounded p-3 overflow-x-auto whitespace-pre-wrap">{{ $runLog }}</pre>
  @endif

  <p class="text-xs text-slate-400 mt-4">Demi keamanan, installer otomatis nonaktif setelah instalasi berhasil. Untuk mengaktifkan lagi, set <code>INSTALLER_ENABLED=true</code> di file .env.</p>

  <div class="mt-4 bg-slate-50 border rounded p-3 text-xs text-slate-600">
    <p class="font-bold text-sm text-slate-800 mb-1">Info path server (untuk setting file index.php di public_html tanpa terminal)</p>
    <p>Folder aplikasi: <code class="bg-slate-200 px-1 rounded">{{ $paths['base'] }}</code></p>
    <p class="mt-1">Di file <code>index.php</code> dalam <code>public_html</code>, ganti 2 baris menjadi:</p>
    <pre class="bg-slate-900 text-green-300 rounded p-2 mt-1 overflow-x-auto">require '{{ $paths['vendor'] }}';
$app = require_once '{{ $paths['bootstrap'] }}';</pre>
  </div>
</div>
</body>
</html>
