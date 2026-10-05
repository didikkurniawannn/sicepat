<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        $kecamatans = \App\Models\Kecamatan::active()->orderBy('order')->get();
        return view('auth.login', compact('kecamatans'));
    }

    /** Daftar akun login per kecamatan (publik, tanpa password) agar Kasi langsung tahu emailnya */
    public function loginAccounts(Request $request)
    {
        $request->validate(['kecamatan' => 'required|string']);
        $kec = \App\Models\Kecamatan::active()->where('slug', $request->kecamatan)->first();
        if (!$kec) return response()->json([]);
        try {
            $users = \App\Models\User::with('section')->where('kecamatan_id', $kec->id)
                ->whereHas('roles', fn($qq) => $qq->whereIn('name', ['admin', 'kasi', 'staf']))
                ->get()->sortBy(fn($u) => ($u->getRoleNames()->first() === 'admin' ? 0 : ($u->getRoleNames()->first() === 'kasi' ? 1 : 2)) * 100 + ($u->section->order ?? 99))
                ->values()->map(fn($u) => [
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->getRoleNames()->first(),
                    'section' => $u->section->name ?? '-',
                ]);
            return response()->json($users);
        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => 'required|email', 'password' => 'required',
        ]);
        if (Auth::attempt($cred, $request->boolean('remember'))) {
            $request->session()->regenerate();
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'login', 'description' => 'Login ke sistem']);
            return redirect()->intended('/dashboard');
        }
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'logout', 'description' => 'Logout dari sistem']);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
