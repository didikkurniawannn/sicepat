<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang role modul Data Sektoral (diadaptasi ke Spatie Permission).
 * Nama role sektoral: super_admin, admin_operator, pimpinan, viewer.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Akses ditolak untuk role Anda.');
        }
        $ok = false;
        foreach ($roles as $role) {
            if ($role === 'super_admin' && $user->isSuperAdmin()) {
                $ok = true;
                break;
            }
            if ($role === 'admin_operator' && $user->canWrite()) {
                $ok = true;
                break;
            }
            if (in_array($role, ['pimpinan', 'viewer'], true) && $user->hasAnyRole(['superadmin', 'admin', 'operator', 'pimpinan', 'viewer', 'kasi', 'staf'])) {
                $ok = true;
                break;
            }
        }
        abort_unless($ok, 403, 'Akses ditolak untuk role Anda.');

        return $next($request);
    }
}
