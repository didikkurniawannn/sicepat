<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\AiRecommendation;
use App\Models\DataEntry;
use App\Models\Facility;
use App\Models\Indicator;
use App\Models\Kecamatan;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiRecommendationController extends Controller
{
    public function index()
    {
        $q = AiRecommendation::with('kecamatan')->latest();
        if (!auth()->user()->isSuperAdmin() && auth()->user()->kecamatan_id) {
            $q->where(fn ($w) => $w->where('kecamatan_id', auth()->user()->kecamatan_id)->orWhereNull('kecamatan_id'));
        }

        return view('sektoral.ai.index', ['items' => $q->paginate(10)]);
    }

    public function create()
    {
        $u = auth()->user();

        return view('sektoral.ai.form', [
            'kecamatans' => $u->isSuperAdmin() ? Kecamatan::orderBy('name')->get() : Kecamatan::where('id', $u->kecamatan_id)->get(),
        ]);
    }

    /** FR-AI-01/02: rule-based engine + opsional OpenAI/Gemini via HTTP */
    public function store(Request $request)
    {
        $data = $request->validate(['kecamatan_id' => 'nullable|exists:kecamatans,id']);
        $kecId = $data['kecamatan_id'] ?? auth()->user()->kecamatan_id;
        if (!auth()->user()->isSuperAdmin()) {
            $kecId = auth()->user()->kecamatan_id;
        }

        $stats = $this->buildStats($kecId);
        $content = $this->ruleBased($stats, $kecId);
        $reasoning = 'Dihasilkan rule-based dari rasio fasilitas per 10rb penduduk & kelengkapan data tahun berjalan.';

        // Opsional: perkaya dengan LLM jika API key tersedia
        $engine = 'rule_based';
        if ($key = config('services.openai.key')) {
            try {
                $res = Http::withToken($key)->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda analis pembangunan daerah Kabupaten Bandung. Jawab ringkas Bahasa Indonesia.'],
                        ['role' => 'user', 'content' => 'Data: '.json_encode($stats)."\nDraf:\n".$content."\nSempurnakan menjadi 5 rekomendasi prioritas bernomor."],
                    ],
                ]);
                if ($res->successful() && ($text = $res->json('choices.0.message.content'))) {
                    $content = $text;
                    $reasoning .= ' Disempurnakan dengan OpenAI GPT-4o.';
                    $engine = 'openai';
                }
            } catch (\Throwable) {
            }
        }

        $rec = AiRecommendation::create([
            'kecamatan_id' => $kecId,
            'title' => 'Rekomendasi '.($kecId ? Kecamatan::find($kecId)->name : 'Kabupaten').' — '.date('d M Y'),
            'content' => $content, 'reasoning' => $reasoning, 'engine' => $engine,
            'metadata' => $stats, 'created_by' => auth()->id(),
        ]);
        Audit::log('create', $rec, null, ['title' => $rec->title]);

        return redirect()->route('ai.show', $rec)->with('success', 'Rekomendasi dibuat.');
    }

    public function show(AiRecommendation $ai)
    {
        return view('sektoral.ai.show', ['item' => $ai->load('kecamatan')]);
    }

    public function destroy(AiRecommendation $ai)
    {
        $ai->delete();

        return redirect()->route('ai.index')->with('success', 'Rekomendasi dihapus.');
    }

    private function buildStats(?int $kecId): array
    {
        $pend = Indicator::where('kode', 'PEND_TOTAL')->first();
        $penduduk = 0;
        if ($pend) {
            $q = DataEntry::query();
            if ($kecId) {
                $q->where('kecamatan_id', $kecId);
            }
            $penduduk = (int) $q->where('indicator_id', $pend->id)->where('year', date('Y'))->sum('value');
        }
        $counts = [];
        foreach (['M-03', 'M-04', 'M-05', 'M-06', 'M-07', 'M-08'] as $m) {
            $q = Facility::query();
            if ($kecId) {
                $q->where('kecamatan_id', $kecId);
            }
            // Tanpa global scope tenant (agar super admin bisa generate kab),
            // filter manual per kecamatan bila ada.
            $counts[$m] = $q->withoutGlobalScope('tenant')->when($kecId, fn ($w) => $w->where('kecamatan_id', $kecId))->where('modul', $m)->count();
        }

        return ['kecamatan_id' => $kecId, 'penduduk' => (int) $penduduk, 'fasilitas_per_modul' => $counts];
    }

    private function ruleBased(array $stats, ?int $kecId): string
    {
        $nama = $kecId ? (Kecamatan::find($kecId)->name ?? 'wilayah') : 'Kabupaten Bandung';
        $lines = ["Rekomendasi prioritas untuk {$nama}:"];
        $n = 1;
        $pop = max(1, $stats['penduduk'] / 10000);
        $labels = ['M-03' => 'pendidikan', 'M-04' => 'kesehatan', 'M-05' => 'pertanian', 'M-06' => 'ekonomi lokal', 'M-07' => 'sarana sosial', 'M-08' => 'KDMP/SPPG'];
        foreach ($stats['fasilitas_per_modul'] as $modul => $count) {
            $ratio = round($count / $pop, 2);
            $status = $ratio < 1 ? 'KURANG — tambah unit baru di desa yang belum terlayani' : 'CUKUP — fokus ke pemerataan & kualitas';
            $lines[] = "{$n}. Fasilitas {$labels[$modul]}: {$count} unit ({$ratio}/10rb penduduk) → {$status}.";
            $n++;
        }
        if (!$stats['penduduk']) {
            $lines[] = "{$n}. Data kependudukan tahun berjalan belum diinput — lengkapi agar rasio akurat.";
        }

        return implode("\n", $lines);
    }
}
