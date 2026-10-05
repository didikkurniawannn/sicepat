<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityChecklist;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Section;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $q = Activity::with(['section','pptk'])->orderBy('activity_date');

        if ($user->hasAnyRole(['kasi','staf']) && $user->section_id) {
            $q->where('section_id', $user->section_id);
        }
        // Multi-tenant: selain superadmin hanya data kecamatannya; superadmin boleh filter kecamatan
        if (!$user->isSuperAdmin() && $user->kecamatan_id) {
            $q->where('activities.kecamatan_id', $user->kecamatan_id);
        }
        if ($user->isSuperAdmin() && $request->filled('kecamatan_id')) {
            $q->where('activities.kecamatan_id', $request->kecamatan_id);
        }
        if ($request->filled('section_id')) $q->where('section_id', $request->section_id);
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('month')) $q->whereMonth('activity_date', $request->month);
        if ($request->filled('year')) $q->whereYear('activity_date', $request->year);
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(fn($qq) => $qq->where('title','like',"%$s%")->orWhere('program_name','like',"%$s%")->orWhere('account_code','like',"%$s%"));
        }
        if ($request->filled('pptk_id')) $q->where('pptk_id', $request->pptk_id);

        // Catatan: rekap dihitung SEBELUM paginate karena paginate() menempelkan limit/offset ke query builder
        $totals = [
            'kebutuhan' => (clone $q)->sum('requirement_qty'),
            'jumlah' => (clone $q)->sum('total_qty'),
        ];
        // Rincian pagu dikelompokkan per kode rekening yang sama (mengikuti filter aktif).
        // TANPA penjumlahan: nilai diambil dari baris wakil (kegiatan paling awal).
        $perRekening = (clone $q)->orderBy('activity_date')->orderBy('id')->get(['account_code', 'budget_pagu', 'budget_realization'])
            ->groupBy('account_code')
            ->map(function ($g, $code) {
                $wakil = $g->first();
                $pagu = (float) $wakil->budget_pagu;
                $real = (float) $wakil->budget_realization;
                return [
                    'code' => $code,
                    'count' => $g->count(),
                    'pagu' => $pagu,
                    'realisasi' => $real,
                    'sisa' => $pagu - $real,
                ];
            })->sortKeys()->values();

        $activities = $q->paginate(15)->withQueryString();
        $sections = Section::active()->orderBy('order')->get();
        $statuses = ['draft','diajukan','diverifikasi','disetujui','berjalan','selesai','ditolak'];
        $kecamatans = $user->isSuperAdmin() ? \App\Models\Kecamatan::active()->orderBy('order')->get() : null;

        return view('activities.index', compact('activities','sections','statuses','totals','perRekening','kecamatans'));
    }

    public function create()
    {
        $this->authorizeInput();
        $user = auth()->user();
        $sections = Section::active()->orderBy('order')->get();
        $pptks = User::role('kasi')->with('section')
            ->when(!$user->isSuperAdmin() && $user->kecamatan_id, fn($qq) => $qq->where('kecamatan_id', $user->kecamatan_id))
            ->get();
        $kecamatans = $user->isSuperAdmin() ? \App\Models\Kecamatan::active()->orderBy('order')->get() : null;
        return view('activities.form', ['activity' => new Activity(), 'sections' => $sections, 'pptks' => $pptks, 'kecamatans' => $kecamatans]);
    }

    public function store(Request $request)
    {
        $this->authorizeInput();
        $user = auth()->user();
        $data = $this->validated($request);
        $data['created_by'] = $user->id;
        $data['kecamatan_id'] = $user->isSuperAdmin()
            ? $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id'])['kecamatan_id']
            : $user->kecamatan_id;
        $act = Activity::create($data);
        $this->syncChecklist($act, $request);
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'create_activity', 'model_type' => Activity::class, 'model_id' => $act->id, 'description' => "Input kegiatan: {$act->title}"]);
        return redirect('/kegiatan/'.$act->id)->with('success', 'Kegiatan berhasil disimpan.');
    }

    public function show(Activity $activity)
    {
        $this->authorizeTenant($activity);
        $activity->load(['section','pptk','documents','checklists','verifications.user']);
        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity)
    {
        $this->authorizeInput($activity);
        $user = auth()->user();
        $sections = Section::active()->orderBy('order')->get();
        $pptks = User::role('kasi')->with('section')
            ->when(!$user->isSuperAdmin() && $user->kecamatan_id, fn($qq) => $qq->where('kecamatan_id', $user->kecamatan_id))
            ->get();
        $kecamatans = $user->isSuperAdmin() ? \App\Models\Kecamatan::active()->orderBy('order')->get() : null;
        return view('activities.form', ['activity' => $activity, 'sections' => $sections, 'pptks' => $pptks, 'kecamatans' => $kecamatans]);
    }

    public function update(Request $request, Activity $activity)
    {
        $this->authorizeInput($activity);
        $data = $this->validated($request, $activity->id);
        if (auth()->user()->isSuperAdmin() && $request->filled('kecamatan_id')) {
            $data['kecamatan_id'] = $request->validate(['kecamatan_id' => 'exists:kecamatans,id'])['kecamatan_id'];
        }
        $activity->update($data);
        $this->syncChecklist($act = $activity, $request);
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'update_activity', 'model_type' => Activity::class, 'model_id' => $activity->id, 'description' => "Update kegiatan: {$activity->title}"]);
        return redirect('/kegiatan/'.$activity->id)->with('success', 'Kegiatan berhasil diperbarui. Sisa otomatis = Pagu - Realisasi.');
    }

    public function destroy(Activity $activity)
    {
        $this->authorizeInput($activity);
        $activity->delete();
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'delete_activity', 'description' => "Hapus kegiatan: {$activity->title}"]);
        return redirect('/kegiatan')->with('success', 'Kegiatan dihapus.');
    }

    public function ajukan(Activity $activity)
    {
        $this->authorizeTenant($activity);
        $activity->update(['status' => 'diajukan']);
        Verification::create(['activity_id' => $activity->id, 'user_id' => auth()->id(), 'role_at_time' => auth()->user()->getRoleNames()->first() ?? '-', 'decision' => 'diajukan', 'note' => 'Diajukan untuk verifikasi']);
        $this->notifyRole('admin', 'Pengajuan baru perlu diverifikasi', $activity->title, $activity->id, $activity->kecamatan_id);
        return back()->with('success', 'Kegiatan diajukan untuk verifikasi.');
    }

    public function updateProgress(Request $request, Activity $activity)
    {
        $this->authorizeTenant($activity);
        // Realisasi dibandingkan ke pagu milik kegiatan (form tidak mengirim budget_pagu)
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'budget_realization' => 'nullable|numeric|min:0|max:'.$activity->budget_pagu,
        ]);
        if ($request->filled('budget_realization')) {
            $activity->budget_realization = $request->budget_realization;
        }
        $activity->progress = $request->progress;
        // Status SELESAI tidak otomatis dari angka — hanya lewat aksi eksplisit "sudah dilaksanakan".
        // Di sini progress hanya bisa menggerakkan status ke 'berjalan'.
        if ($activity->progress > 0 && in_array($activity->status, ['disetujui','diverifikasi','draft'])) $activity->status = 'berjalan';
        $activity->save();
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'update_progress', 'model_type' => Activity::class, 'model_id' => $activity->id, 'description' => "Progress {$activity->progress}% & realisasi Rp ".number_format($activity->budget_realization,0,',','.')]);
        return back()->with('success', 'Progress & realisasi diperbarui.');
    }

    /**
     * Menandai kegiatan SUDAH DILAKSANAKAN (status = selesai).
     * Murni berdasarkan pelaksanaan di lapangan, bukan dari % realisasi/progress.
     */
    public function selesaikan(Request $request, Activity $activity)
    {
        $this->authorizeInput($activity);
        $activity->update(['status' => 'selesai', 'progress' => 100]);
        Verification::create([
            'activity_id' => $activity->id, 'user_id' => auth()->id(),
            'role_at_time' => auth()->user()->getRoleNames()->first() ?? '-',
            'decision' => 'disetujui', 'note' => 'Kegiatan ditandai sudah dilaksanakan di lapangan.',
        ]);
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'complete_activity', 'model_type' => Activity::class, 'model_id' => $activity->id, 'description' => "Kegiatan ditandai selesai dilaksanakan: {$activity->title}"]);
        return back()->with('success', 'Kegiatan ditandai sudah dilaksanakan (selesai).');
    }

    public function uploadDoc(Request $request, Activity $activity)
    {
        $this->authorizeTenant($activity);
        $request->validate(['name' => 'required|string|max:255', 'type' => 'required|string|max:30', 'file' => 'required|file|max:10240']);
        $path = $request->file('file')->store('docs', 'public');
        $activity->documents()->create(['name' => $request->name, 'type' => $request->type, 'file_path' => $path, 'uploaded_by' => auth()->id()]);
        return back()->with('success', 'Dokumen diunggah.');
    }

    private function validated(Request $request, $ignore = null)
    {
        $data = $request->validate([
            'activity_date' => 'required|date',
            'section_id' => 'required|exists:sections,id',
            'account_code' => 'required|string|max:30',
            'program_name' => 'required|string',
            'title' => 'required|string|max:255',
            'requirement_qty' => 'required|integer|min:0',
            'total_qty' => 'required|integer|min:0|gte:requirement_qty',
            'unit' => 'required|string|max:30',
            'budget_pagu' => 'required|numeric|min:0',
            'budget_realization' => 'required|numeric|min:0|lte:budget_pagu',
            'location' => 'nullable|string|max:255',
            'pptk_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,diajukan,diverifikasi,disetujui,berjalan,selesai,ditolak',
            'progress' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
        ]);
        return $data;
    }

    private function syncChecklist(Activity $act, Request $request)
    {
        $defaults = ['Dokumen TOR/KAK tersedia','RAB/anggaran tersedia','SDM/PPTK siap','Jadwal & lokasi fix'];
        if ($request->has('checklist')) {
            $act->checklists()->delete();
            foreach ($request->input('checklist', []) as $i => $val) {
                $act->checklists()->create(['item' => $defaults[$i] ?? $val, 'is_checked' => (bool) $val]);
            }
        } elseif ($act->checklists()->count() === 0) {
            foreach ($defaults as $d) $act->checklists()->create(['item' => $d, 'is_checked' => false]);
        }
    }

    private function authorizeTenant($activity): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) return;
        abort_unless($activity->kecamatan_id && (int) $activity->kecamatan_id === (int) $user->kecamatan_id, 403, 'Data kecamatan lain.');
    }

    private function authorizeInput($activity = null)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin','kasi']), 403, 'Hanya Admin/Kasi yang dapat input kegiatan.');
        if ($activity) {
            $this->authorizeTenant($activity);
            if ($user->hasRole('kasi') && $activity->section_id !== $user->section_id) {
                abort(403, 'Kasi hanya dapat mengelola unit sendiri.');
            }
        }
        if (!$activity && $user->hasRole('kasi') && request('section_id') && (int) request('section_id') !== (int) $user->section_id) {
            abort(403, 'Kasi hanya dapat input untuk unit sendiri.');
        }
    }

    private function notifyRole(string $role, string $title, string $msg, $activityId, $kecamatanId = null)
    {
        $users = User::role($role)->when($kecamatanId, fn($qq) => $qq->where('kecamatan_id', $kecamatanId))->get();
        $superadmins = $kecamatanId ? User::role('superadmin')->get() : collect();
        foreach ($users->merge($superadmins)->unique('id') as $u) {
            AppNotification::create(['user_id' => $u->id, 'title' => $title, 'message' => $msg, 'type' => 'verifikasi', 'activity_id' => $activityId]);
        }
    }
}
