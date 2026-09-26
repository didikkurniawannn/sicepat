<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'activity_date','section_id','account_code','program_name','title',
        'requirement_qty','total_qty','unit','budget_pagu','budget_realization',
        'location','pptk_id','status','progress','description','created_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'budget_pagu' => 'decimal:2',
        'budget_realization' => 'decimal:2',
    ];

    protected $appends = ['budget_remaining', 'is_h7', 'days_to_event'];

    public function getBudgetRemainingAttribute(): float
    {
        return (float) $this->budget_pagu - (float) $this->budget_realization;
    }

    public function getRealizationPercentAttribute(): float
    {
        if ((float) $this->budget_pagu <= 0) return 0;
        return round(((float) $this->budget_realization / (float) $this->budget_pagu) * 100, 1);
    }

    public function getDaysToEventAttribute(): ?int
    {
        if (!$this->activity_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->activity_date, false);
    }

    public function getIsH7Attribute(): bool
    {
        $d = $this->days_to_event;
        return $d !== null && $d >= 0 && $d <= 7;
    }

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function pptk(): BelongsTo { return $this->belongsTo(User::class, 'pptk_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function documents(): HasMany { return $this->hasMany(ActivityDocument::class); }
    public function checklists(): HasMany { return $this->hasMany(ActivityChecklist::class); }
    public function verifications(): HasMany { return $this->hasMany(Verification::class)->latest(); }

    public function scopeUpcoming($q, $days = 30)
    {
        return $q->whereBetween('activity_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function scopeH7($q)
    {
        return $q->whereBetween('activity_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
    }

    /**
     * Baris wakil per kode rekening: kegiatan paling awal (tanggal, lalu id).
     * Satu kode rekening = satu nilai (tidak ada penjumlahan sama sekali).
     */
    public static function representativePerRekening($query)
    {
        return (clone $query)->orderBy('activity_date')->orderBy('id')
            ->get(['id', 'account_code', 'budget_pagu', 'budget_realization'])
            ->groupBy('account_code')
            ->map(fn($g) => $g->first());
    }

    /**
     * Total pagu & realisasi = jumlah nilai wakil tiap kode rekening.
     * $query adalah Eloquent builder yang sudah difilter (dicloning di dalam).
     */
    public static function budgetSums($query): array
    {
        $reps = self::representativePerRekening($query);
        $pagu = $reps->sum(fn($a) => (float) $a->budget_pagu);
        $real = $reps->sum(fn($a) => (float) $a->budget_realization);
        return ['pagu' => $pagu, 'realisasi' => $real, 'sisa' => $pagu - $real];
    }
}
