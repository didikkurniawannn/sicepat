<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivitiesExport implements FromCollection, WithHeadings
{
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
}
