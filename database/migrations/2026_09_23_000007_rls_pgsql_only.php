<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Row-Level Security PostgreSQL sebagai backup defense (plan.md NFR-SEC-04).
 * Aplikasi tetap mengandalkan global scope; RLS menahan kebocoran bila ada
 * query yang mem-bypass scope. Aktif HANYA di pgsql. Dilewati di SQLite/MySQL.
 *
 * Konteks tenant dibaca dari setting `app.current_tenant` yang di-set
 * per-request oleh ResolveTenant. Super admin => string kosong = bypass.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        foreach (['facilities', 'data_entries', 'audit_logs'] as $table) {
            DB::unprepared("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::unprepared("DROP POLICY IF EXISTS tenant_isolation ON {$table}");
            DB::unprepared("
                CREATE POLICY tenant_isolation ON {$table}
                USING (
                    current_setting('app.current_tenant', true) IN ('', 'bypass')
                    OR kecamatan_id::text = NULLIF(current_setting('app.current_tenant', true), '')
                )
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        foreach (['facilities', 'data_entries', 'audit_logs'] as $table) {
            DB::unprepared("DROP POLICY IF EXISTS tenant_isolation ON {$table}");
            DB::unprepared("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
