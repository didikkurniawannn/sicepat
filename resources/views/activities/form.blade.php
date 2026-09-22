@extends('layout')
@section('title', ($activity->exists ? 'Edit' : 'Tambah').' Kegiatan')
@section('content')
<h1 class="text-xl font-bold mb-3">{{ $activity->exists ? 'Edit' : 'Tambah' }} Kegiatan</h1>
<form method="POST" action="{{ $activity->exists ? '/kegiatan/'.$activity->id : '/kegiatan' }}" class="bg-white rounded shadow p-4 grid md:grid-cols-2 gap-3 text-sm">
@csrf @if($activity->exists)@method('PUT')@endif
  <div><label>Tanggal Pelaksanaan *</label><input type="date" name="activity_date" value="{{ old('activity_date', $activity->activity_date?->format('Y-m-d')) }}" required class="w-full border rounded px-2 py-1"></div>
  <div><label>Bidang / Unit Kerja *</label><select name="section_id" required class="w-full border rounded px-2 py-1">@foreach($sections as $s)<option value="{{ $s->id }}" @selected(old('section_id',$activity->section_id)==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
  <div><label>Kode Rekening *</label><input name="account_code" value="{{ old('account_code',$activity->account_code) }}" required class="w-full border rounded px-2 py-1" placeholder="7.01.02.2.04.0003"></div>
  <div><label>PPTK</label><select name="pptk_id" class="w-full border rounded px-2 py-1"><option value="">—</option>@foreach($pptks as $p)<option value="{{ $p->id }}" @selected(old('pptk_id',$activity->pptk_id)==$p->id)>{{ $p->name }} ({{ $p->section->short_name ?? '-' }})</option>@endforeach</select></div>
  <div class="md:col-span-2"><label>Nama Program/Kegiatan *</label><textarea name="program_name" required class="w-full border rounded px-2 py-1">{{ old('program_name',$activity->program_name) }}</textarea></div>
  <div class="md:col-span-2"><label>Judul Kegiatan *</label><input name="title" value="{{ old('title',$activity->title) }}" required class="w-full border rounded px-2 py-1"></div>
  <div><label>Kebutuhan Kegiatan *</label><input type="number" name="requirement_qty" value="{{ old('requirement_qty',$activity->requirement_qty ?? 0) }}" required class="w-full border rounded px-2 py-1"></div>
  <div><label>Jumlah Total *</label><input type="number" name="total_qty" value="{{ old('total_qty',$activity->total_qty ?? 0) }}" required class="w-full border rounded px-2 py-1"></div>
  <div><label>Satuan *</label><select name="unit" class="w-full border rounded px-2 py-1"><option @selected(old('unit',$activity->unit)=='Orang / Kali')>Orang / Kali</option><option @selected(old('unit',$activity->unit)=='Orang / Hari')>Orang / Hari</option></select></div>
  <div><label>Lokasi</label><input name="location" value="{{ old('location',$activity->location) }}" class="w-full border rounded px-2 py-1"></div>
  <div><label>Pagu (Rp) *</label><input type="number" id="pagu" name="budget_pagu" value="{{ old('budget_pagu',$activity->budget_pagu ?? 0) }}" required class="w-full border rounded px-2 py-1"></div>
  <div><label>Realisasi (Rp) *</label><input type="number" id="real" name="budget_realization" value="{{ old('budget_realization',$activity->budget_realization ?? 0) }}" required class="w-full border rounded px-2 py-1"><p class="text-xs text-slate-500">Sisa otomatis: <span id="sisa" class="font-bold">0</span></p></div>
  <div><label>Status</label><select name="status" class="w-full border rounded px-2 py-1">@foreach(['draft','diajukan','diverifikasi','disetujui','berjalan','selesai','ditolak'] as $st)<option @selected(old('status',$activity->status ?? 'draft')==$st)>{{ $st }}</option>@endforeach</select></div>
  <div><label>Progress (0-100)</label><input type="number" name="progress" min="0" max="100" value="{{ old('progress',$activity->progress ?? 0) }}" class="w-full border rounded px-2 py-1"></div>
  <div class="md:col-span-2"><label>Deskripsi</label><textarea name="description" class="w-full border rounded px-2 py-1">{{ old('description',$activity->description) }}</textarea></div>
  <div class="md:col-span-2"><button class="bg-slate-900 text-white px-4 py-2 rounded">Simpan</button> <a href="/kegiatan" class="text-sm text-slate-500">Batal</a></div>
</form>
@endsection
@section('scripts')
<script>
const pagu=document.getElementById('pagu'), real=document.getElementById('real'), sisa=document.getElementById('sisa');
function hitung(){ sisa.textContent = Number(pagu.value||0) - Number(real.value||0); }
pagu.addEventListener('input',hitung); real.addEventListener('input',hitung); hitung();
</script>
@endsection
