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
            $s->total_pagu = \App\Models\Activity::where('section_id', $s->id)->sum('budget_pagu');
            $s->total_realisasi = \App\Models\Activity::where('section_id', $s->id)->sum('budget_realization');
        }
        return view('sections.index', compact('sections'));
    }

    public function sectionShow(Section $section)
    {
        $activities = $section->activities()->with('pptk')->orderBy('activity_date')->paginate(15);
        $stats = [
            'count' => $section->activities()->count(),
            'pagu' => $section->activities()->sum('budget_pagu'),
            'realisasi' => $section->activities()->sum('budget_realization'),
        ];
        $stats['sisa'] = $stats['pagu'] - $stats['realisasi'];
        return view('sections.show', compact('section','activities','stats'));
    }

    public function users()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $users = \App\Models\User::with('section')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function usersStore(Request $request)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8', 'role' => 'required|in:admin,kasi,staf',
            'section_id' => 'required|exists:sections,id',
        ]);
        $u = \App\Models\User::create([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => bcrypt($data['password']), 'section_id' => $data['section_id'],
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
