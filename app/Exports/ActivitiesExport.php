<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActivitiesExport implements FromCollection, WithHeadings, WithEvents
{
    /** Palet warna (ARGB) untuk menandai kelompok kode rekening yang sama */
    public const HIGHLIGHT_PALETTE = [
        'FFFFE699', 'FFBDD7EE', 'FFC6EFCE', 'FFFFC7CE',
        'FFD9D2E9', 'FFB4C6E7', 'FFFFB3BA', 'FFBAE1FF',
    ];

    public function __construct(public $filters = []) {}

    public function collection()
    {
        $q = Activity::with('section')->orderBy('activity_date');
        if (!empty($this->filters['section_id'])) $q->where('section_id', $this->filters['section_id']);
        if (!empty($this->filters['status'])) $q->where('status', $this->filters['status']);
        if (!empty($this->filters['month'])) $q->whereMonth('activity_date', $this->filters['month']);
        if (!empty($this->filters['year'])) $q->whereYear('activity_date', $this->filters['year']);
        return $q->get()->map(fn($a) => [
            'Bulan' => $a->activity_date->format('Y-m-d'),
            'Bidang' => $a->section->name,
            'Kode Rekening' => $a->account_code,
            'Kegiatan' => $a->program_name,
            'Judul Kegiatan' => $a->title,
            'Kebutuhan Kegiatan' => $a->requirement_qty,
            'Jumlah' => $a->total_qty,
            'Satuan' => $a->unit,
            'Pagu' => (float) $a->budget_pagu,
            'Realisasi' => (float) $a->budget_realization,
            'Sisa' => (float) $a->budget_pagu - (float) $a->budget_realization,
        ]);
    }

    public function headings(): array
    {
        return ['Bulan','Bidang','Kode Rekening','Kegiatan','Judul Kegiatan','Kebutuhan Kegiatan','Jumlah','Satuan','Pagu','Realisasi','Sisa'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                if ($lastRow < 2) return;

                // Petakan baris per kode rekening (kolom C)
                $groups = [];
                for ($r = 2; $r <= $lastRow; $r++) {
                    $code = trim((string) $sheet->getCell('C'.$r)->getValue());
                    if ($code !== '') $groups[$code][] = $r;
                }

                // Warnai tiap kelompok kode yang muncul > 1x + tulis legenda di kolom M-N
                $i = 0;
                $legendRow = 1;
                $hasLegend = false;
                foreach ($groups as $code => $rows) {
                    if (count($rows) < 2) continue;
                    $color = self::HIGHLIGHT_PALETTE[$i % count(self::HIGHLIGHT_PALETTE)];
                    $i++;
                    foreach ($rows as $r) {
                        $sheet->getStyle('A'.$r.':K'.$r)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB($color);
                    }
                    if (!$hasLegend) {
                        $sheet->setCellValue('M1', 'Keterangan: baris berwarna = kode rekening sama muncul > 1x');
                        $sheet->getStyle('M1')->getFont()->setBold(true);
                        $legendRow = 2;
                        $hasLegend = true;
                    }
                    $sheet->setCellValue('M'.$legendRow, $code);
                    $sheet->getStyle('M'.$legendRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($color);
                    $legendRow++;
                }
                if ($hasLegend) {
                    $sheet->getColumnDimension('M')->setAutoSize(true);
                }
            },
        ];
    }

    /** Warna CSS (#RGB) per kode rekening duplikat — dipakai export PDF agar konsisten */
    public static function cssPalette(): array
    {
        return ['#FFE699','#BDD7EE','#C6EFCE','#FFC7CE','#D9D2E9','#B4C6E7','#FFB3BA','#BAE1FF'];
    }
}
