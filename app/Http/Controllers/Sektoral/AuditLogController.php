<?php

namespace App\Http\Controllers\Sektoral;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $u = auth()->user();
        $q = AuditLog::with('user')->latest();
        if (!$u->isSuperAdmin()) {
            $q->where('kecamatan_id', $u->kecamatan_id); // FR-LOG-03
        }

        return view('sektoral.audit.index', ['logs' => $q->paginate(20)]);
    }
}
