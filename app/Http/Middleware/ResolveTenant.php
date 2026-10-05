<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inject tenant context ke request (plan.md §3.2).
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $request->attributes->set('tenant_kecamatan_id', $user->isSuperAdmin() ? null : $user->kecamatan_id);
            $request->attributes->set('tenant_role', $user->getRoleNames()->first());
            // RLS PostgreSQL: set konteks tenant per koneksi (diabaikan bila bukan pgsql)
            try {
                if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
                    $tenant = $user->isSuperAdmin() ? 'bypass' : (string) ($user->kecamatan_id ?? '');
                    \Illuminate\Support\Facades\DB::statement('SELECT set_config(?, ?, false)', ['app.current_tenant', $tenant]);
                }
            } catch (\Throwable) {
            }
        }

        return $next($request);
    }
}
