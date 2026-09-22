<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — SiCepatKeg</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
  <h1 class="text-2xl font-bold">⚡ SiCepatKeg</h1>
  <p class="text-sm text-slate-500 mb-6">Sistem Informasi Percepatan Kinerja & Kegiatan Instansi</p>
  @if($errors->any())<div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">{{ $errors->first() }}</div>@endif
  <form method="POST" action="/login" class="space-y-4">@csrf
    <div><label class="text-sm font-medium">Email</label><input name="email" type="email" required value="{{ old('email') }}" class="w-full border rounded px-3 py-2"></div>
    <div><label class="text-sm font-medium">Password</label><input name="password" type="password" required class="w-full border rounded px-3 py-2"></div>
    <button class="w-full bg-slate-900 text-white py-2 rounded font-semibold">Masuk</button>
  </form>
  <a href="/pantau" class="block text-center text-sm text-blue-700 hover:underline mt-3">📊 Lihat Pantauan Kegiatan (tanpa login)</a>
  <div class="mt-6 text-xs bg-slate-50 border rounded p-3">
    <p class="font-semibold mb-1">Akun demo (password: password123)</p>
    <ul class="space-y-0.5 text-slate-600">
      <li>Admin: admin@sicepatkeg.local</li>
      <li>Pimpinan: pimpinan@sicepatkeg.local</li>
      <li>Verifikator: verifikator@sicepatkeg.local</li>
      <li>Kasi: spr.kasi@sicepatkeg.local (spr/spb/ssb/spm/skt/tmv/sbu)</li>
      <li>PPTK: spr.pptk@sicepatkeg.local (idem)</li>
    </ul>
  </div>
</div>
</body>
</html>
