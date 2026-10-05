@extends('sektoral.layouts.app')
@section('title', $item->title)
@section('content')
<h1 class="text-xl font-bold">{{ $item->title }}</h1>
<p class="text-xs text-slate-500 mb-3">Engine: {{ $item->engine }} · {{ $item->created_at }}</p>
<div class="bg-white rounded shadow p-4 text-sm whitespace-pre-line">{{ $item->content }}</div>
@if($item->reasoning)<div class="bg-slate-50 border rounded p-3 mt-3 text-xs text-slate-600"><b>Reasoning:</b> {{ $item->reasoning }}</div>@endif
<div class="mt-3"><button onclick="window.print()" class="px-3 py-1.5 border rounded text-sm">Cetak / PDF</button></div>
@endsection
