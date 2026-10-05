@extends('sektoral.layouts.app')
@section('title', 'Generate Rekomendasi')
@section('content')
<h1 class="text-xl font-bold mb-1">Generate Rekomendasi</h1>
<p class="text-sm text-slate-500 mb-4">Rule-based engine (otomatis). Jika <code>OPENAI_API_KEY</code> diisi di .env, hasil disempurnakan GPT-4o.</p>
<form method="POST" action="{{ route('ai.store') }}" class="bg-white rounded shadow p-4 max-w-xl space-y-3">@csrf
  <div><label class="text-sm">Cakupan</label><select name="kecamatan_id" class="w-full border rounded px-2 py-1.5"><option value="">— Tingkat Kabupaten —</option>@foreach($kecamatans as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
  <div><button class="px-4 py-2 bg-slate-900 text-white rounded">Generate</button></div>
</form>
@endsection
