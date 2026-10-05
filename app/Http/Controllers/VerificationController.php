<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin','superadmin']), 403);
        $queue = Activity::with(['section','pptk'])->whereIn('status', ['diajukan','diverifikasi'])
            ->when(!$user->isSuperAdmin() && $user->kecamatan_id, fn($qq) => $qq->where('activities.kecamatan_id', $user->kecamatan_id))
            ->orderBy('activity_date')->paginate(15);
        $history = Verification::with(['activity.section','user'])
            ->when(!$user->isSuperAdmin() && $user->kecamatan_id, fn($qq) => $qq->whereHas('activity', fn($aa) => $aa->where('kecamatan_id', $user->kecamatan_id)))
            ->latest()->paginate(15);
        return view('verifications.index', compact('queue','history'));
    }

    public function decide(Request $request, Activity $activity)
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['admin','superadmin']), 403);
        abort_unless($user->isSuperAdmin() || ((int) $activity->kecamatan_id === (int) $user->kecamatan_id), 403, 'Data kecamatan lain.');
        $request->validate(['decision' => 'required|in:diverifikasi,disetujui,ditolak,revisi', 'note' => 'nullable|string']);

        // Verifikator -> diverifikasi/ditolak/revisi ; Pimpinan/Admin -> disetujui
        $map = ['diverifikasi' => 'diverifikasi', 'disetujui' => 'disetujui', 'ditolak' => 'ditolak', 'revisi' => 'draft'];
        $activity->update(['status' => $map[$request->decision]]);

        Verification::create([
            'activity_id' => $activity->id, 'user_id' => $user->id,
            'role_at_time' => $user->getRoleNames()->first() ?? '-', 'decision' => $request->decision, 'note' => $request->note,
        ]);

        // Notifikasi ke PPTK & penginput
        $targets = User::whereIn('id', array_filter([$activity->pptk_id, $activity->created_by]))->get();
        foreach ($targets as $t) {
            AppNotification::create(['user_id' => $t->id, 'title' => 'Verifikasi: '.$request->decision, 'message' => $activity->title.' — '.($request->note ?? '-'), 'type' => 'verifikasi', 'activity_id' => $activity->id]);
        }

        ActivityLog::create(['user_id' => $user->id, 'action' => 'verify', 'model_type' => Activity::class, 'model_id' => $activity->id, 'description' => "Keputusan {$request->decision} pada {$activity->title}"]);
        return back()->with('success', 'Keputusan verifikasi disimpan.');
    }
}
