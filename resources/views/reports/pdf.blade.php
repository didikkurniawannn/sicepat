<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:sans-serif;font-size:11px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #999;padding:4px}th{background:#eee}</style></head>
<body><h2>Laporan Kegiatan — SiCepatKeg</h2>
@if(!empty($dupColors))
<p style="font-size:11px">Keterangan: baris berwarna = kode rekening yang sama muncul &gt; 1x.
@foreach($dupColors as $code => $color)<span style="background-color:{{ $color }};padding:1px 6px;border:1px solid #999">{{ $code }}</span> @endforeach</p>
@endif
<table><thead><tr><th>No</th><th>Bulan</th><th>Bidang</th><th>Kode Rekening</th><th>Judul</th><th>Kebutuhan</th><th>Satuan</th><th>Pagu</th><th>Realisasi</th><th>Sisa</th></tr></thead>
<tbody>@foreach($activities as $a)<tr @if(!empty($dupColors[$a->account_code])) style="background-color:{{ $dupColors[$a->account_code] }}" @endif><td>{{ $loop->iteration }}</td><td>{{ $a->activity_date->translatedFormat('d F Y') }}</td><td>{{ $a->section->name }}</td><td>{{ $a->account_code }}</td><td>{{ $a->title }}</td><td>{{ number_format($a->requirement_qty,0,',','.') }}</td><td>{{ $a->unit }}</td><td>{{ number_format($a->budget_pagu,0,',','.') }}</td><td>{{ number_format($a->budget_realization,0,',','.') }}</td><td>{{ number_format($a->budget_pagu - $a->budget_realization,0,',','.') }}</td></tr>@endforeach</tbody></table>
</body></html>
