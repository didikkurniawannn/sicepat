<?php

namespace App\Imports;

use App\Models\Activity;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ActivitiesImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $success = 0;

    public function collection(Collection $rows)
    {
        $secByName = Section::all()->keyBy('name');
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            try {
                $bidang = trim($row['bidang'] ?? '');
                $section = $secByName[$bidang] ?? Section::where('short_name', $bidang)->first();
                if (!$section) { $this->errors[] = "Baris $line: Bidang '$bidang' tidak dikenal."; continue; }

                $date = $row['bulan'];
                if ($date instanceof \DateTimeInterface) $date = Carbon::instance($date)->format('Y-m-d');
                else $date = Carbon::parse($date)->format('Y-m-d');

                $pagu = (float) ($row['pagu'] ?? 0);
                $real = (float) ($row['realisasi'] ?? 0);
                if ($real > $pagu) { $this->errors[] = "Baris $line: Realisasi > Pagu."; continue; }
                $req = (int) ($row['kebutuhan_kegiatan'] ?? 0);
                $tot = (int) ($row['jumlah'] ?? 0);
                if ($tot < $req) { $this->errors[] = "Baris $line: Jumlah < Kebutuhan."; continue; }

                Activity::updateOrCreate(
                    ['account_code' => $row['kode_rekening'], 'title' => $row['judul_kegiatan'], 'activity_date' => $date],
                    [
                        'section_id' => $section->id,
                        'program_name' => $row['kegiatan'] ?? '-',
                        'requirement_qty' => $req, 'total_qty' => $tot,
                        'unit' => $row['satuan'] ?? 'Orang / Kali',
                        'budget_pagu' => $pagu, 'budget_realization' => $real,
                        'status' => 'disetujui', 'progress' => $real > 0 ? 60 : 10,
                        'created_by' => auth()->id(),
                    ]
                );
                $this->success++;
            } catch (\Throwable $e) {
                $this->errors[] = "Baris $line: ".$e->getMessage();
            }
        }
    }
}
