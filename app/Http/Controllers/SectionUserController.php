<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionUserController extends Controller
{
    public function sections()
    {
        $sections = Section::withCount('activities')->orderBy('order')->get();
        foreach ($sections as $s) {
            $ss = \App\Models\Activity::budgetSums(\App\Models\Activity::where('section_id', $s->id));
            $s->total_pagu = $ss['pagu'];
            $s->total_realisasi = $ss['realisasi'];
        }
        return view('sections.index', compact('sections'));
    }

    public function sectionShow(Section $section)
    {
        $activities = $section->activities()->with('pptk')->orderBy('activity_date')->paginate(15);
        $ss = \App\Models\Activity::budgetSums($section->activities());
        $stats = [
            'count' => $section->activities()->count(),
            'pagu' => $ss['pagu'],
            'realisasi' => $ss['realisasi'],
        ];
        $stats['sisa'] = $stats['pagu'] - $stats['realisasi'];
        return view('sections.show', compact('section','activities','stats'));
    }

    public function users(Request $request)
    {
        $me = auth()->user();
        abort_unless($me->hasAnyRole(['admin','superadmin']), 403);
        $q = \App\Models\User::with(['section','kecamatan'])->orderBy('name');
        if (!$me->isSuperAdmin() && $me->kecamatan_id) {
            $q->where('kecamatan_id', $me->kecamatan_id);
        }
        if ($me->isSuperAdmin() && $request->filled('kecamatan_id')) {
            $q->where('kecamatan_id', $request->kecamatan_id);
        }
        $users = $q->paginate(15)->withQueryString();
        $kecamatans = $me->isSuperAdmin() ? \App\Models\Kecamatan::active()->orderBy('order')->get() : null;
        return view('users.index', compact('users', 'kecamatans'));
    }

    public function usersStore(Request $request)
    {
        $me = auth()->user();
        abort_unless($me->hasAnyRole(['admin','superadmin']), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8', 'role' => 'required|in:admin,kasi,staf,superadmin',
            'section_id' => 'required|exists:sections,id',
            'kecamatan_id' => 'nullable|exists:kecamatans,id',
        ]);
        abort_if($data['role'] === 'superadmin' && !$me->isSuperAdmin(), 403, 'Hanya superadmin.');
        $u = \App\Models\User::create([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => bcrypt($data['password']), 'section_id' => $data['section_id'],
            'kecamatan_id' => $me->isSuperAdmin() ? ($data['kecamatan_id'] ?? null) : $me->kecamatan_id,
        ]);
        $u->syncRoles([$data['role']]);
        return back()->with('success', 'User dibuat.');
    }

    public function notifications()
    {
        $notifs = auth()->user()->notifications()->paginate(15);
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return view('notifications.index', compact('notifs'));
    }
}
