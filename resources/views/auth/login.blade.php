<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — {{ config('app.name') }}</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
  <h1 class="text-2xl font-bold">🏛️ {{ config('app.name') }}</h1>
  <p class="text-sm text-slate-500 mb-6">Modul SiCepatKeg · Percepatan Kinerja & Kegiatan Instansi</p>
  @if($errors->any())<div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">{{ $errors->first() }}</div>@endif
  <form method="POST" action="/login" class="space-y-4">@csrf
    <div><label class="text-sm font-medium">Email</label><input name="email" type="email" required value="{{ old('email') }}" class="w-full border rounded px-3 py-2"></div>
    <div><label class="text-sm font-medium">Password</label><input name="password" type="password" required class="w-full border rounded px-3 py-2"></div>
    <button class="w-full bg-slate-900 text-white py-2 rounded font-semibold">Masuk</button>
  </form>
  <a href="/pantau" class="block text-center text-sm text-blue-700 hover:underline mt-3">📊 Lihat Pantauan Kegiatan (tanpa login)</a>
  <div class="mt-6 text-xs bg-slate-50 border rounded p-3">
    <p class="font-semibold mb-1">💡 Cara masuk tanpa panduan:</p>
    <ol class="list-decimal ml-4 space-y-0.5 text-slate-600 mb-2">
      <li>Pilih kecamatan Anda.</li>
      <li>Ketik email sesuai nama & unit kerja + password <code class="bg-slate-200 px-1 rounded">password123</code>.</li>
    </ol>
    <select id="pilihKecamatan" class="w-full border rounded px-2 py-1.5 mb-2 text-slate-800">
      <option value="">— Pilih kecamatan —</option>
      @foreach($kecamatans as $k)<option value="{{ $k->slug }}">{{ $k->name }}</option>@endforeach
    </select>
    <ul id="daftarAkun" class="space-y-1 text-slate-600"><li class="text-slate-400">Pilih kecamatan untuk melihat daftar akun.</li></ul>
  </div>
</div>
<script>
document.getElementById('pilihKecamatan').addEventListener('change', function(){
  const box = document.getElementById('daftarAkun');
  if (!this.value) { box.innerHTML = '<li class="text-slate-400">Pilih kecamatan untuk melihat daftar akun.</li>'; return; }
  box.innerHTML = '<li class="text-slate-400">Memuat...</li>';
  fetch('/api/login-accounts?kecamatan=' + this.value).then(r => r.json()).then(data => {
    if (!data.length) { box.innerHTML = '<li class="text-slate-400">Belum ada akun di kecamatan ini.</li>'; return; }
    const ikon = {admin: '👑', kasi: '📋', staf: '🧑‍💼'};
    box.innerHTML = data.map(u =>
      `<li><span class="font-semibold text-slate-800">${ikon[u.role] || '👤'} ${u.name} <span class="font-normal text-slate-500">(${u.section})</span></span><br><span class="font-mono">${u.email}</span></li>`
    ).join('');
  });
});
</script>
</body>
</html>
