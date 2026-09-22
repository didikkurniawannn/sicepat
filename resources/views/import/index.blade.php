@extends('layout')
@section('title','Import Excel')
@section('content')
<h1 class="text-xl font-bold mb-3">Import Data Kegiatan.xlsx</h1>
<div class="bg-white rounded shadow p-4 mb-4 text-sm">
  <p class="mb-2">Format kolom: Bulan | Bidang | Kode Rekening | Kegiatan | Judul Kegiatan | Kebutuhan Kegiatan | Jumlah | Satuan | Pagu | Realisasi | Sisa. <a href="/import/template" class="text-blue-700 underline">Unduh template</a></p>
  <form action="/import/preview" method="POST" enctype="multipart/form-data" class="flex gap-2">@csrf<input type="file" name="file" accept=".xlsx,.xls,.csv" required><button class="bg-slate-900 text-white px-3 py-1 rounded">Upload & Preview</button></form>
</div>
<h2 class="font-semibold mb-2">Log Import</h2>
<div class="bg-white rounded shadow p-3 text-sm space-y-2">
@foreach($logs as $l)<div class="border-b pb-1">{{ $l->created_at }} — {{ $l->file_name }} oleh {{ $l->user->name ?? '-' }}: total {{ $l->total_rows }}, sukses {{ $l->success_rows }}, gagal {{ $l->failed_rows }}@if($l->errors)<ul class="list-disc ml-5 text-red-600 text-xs">@foreach(array_slice($l->errors,0,5) as $e)<li>{{ $e }}</li>@endforeach</ul>@endif</div>@endforeach
</div>
<div class="mt-2">{{ $logs->links() }}</div>
@endsection
