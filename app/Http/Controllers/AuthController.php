<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Daftar akun login per peran agar tiap user langsung tahu emailnya (dinamis dari database)
        try {
            $adminUsers = \App\Models\User::role('admin')->get();
            $kasiUsers = \App\Models\User::role('kasi')->with('section')->get()
                ->sortBy(fn($u) => $u->section->order ?? 99)->values();
            $stafUsers = \App\Models\User::role('staf')->with('section')->get()
                ->sortBy(fn($u) => $u->section->order ?? 99)->values();
        } catch (\Throwable $e) {
            $adminUsers = $kasiUsers = $stafUsers = collect();
        }
        return view('auth.login', compact('adminUsers', 'kasiUsers', 'stafUsers'));
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
