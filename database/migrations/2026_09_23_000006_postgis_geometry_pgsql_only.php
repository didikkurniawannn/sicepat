<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aktif HANYA di PostgreSQL: tambah kolom geometry PostGIS + backfill
 * polygon bbox tiap kecamatan. Di SQLite/MySQL dilewati (tetap bbox check).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        if (!Schema::hasColumn('kecamatans', 'geometry')) {
            DB::statement("ALTER TABLE kecamatans ADD COLUMN geometry GEOMETRY(MULTIPOLYGON, 4326)");
        }
        if (!Schema::hasColumn('facilities', 'geometry')) {
            DB::statement("ALTER TABLE facilities ADD COLUMN geometry GEOMETRY(POINT, 4326)");
        }
        // Backfill polygon bbox -> geometry kecamatan
        DB::statement("
            UPDATE kecamatans SET geometry = ST_SetSRID(ST_Multi(ST_MakeEnvelope(min_lng, min_lat, max_lng, max_lat, 4326)), 4326)
            WHERE geometry IS NULL AND min_lat IS NOT NULL
        ");
        // Backfill titik fasilitas
        DB::statement("
            UPDATE facilities SET geometry = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
            WHERE geometry IS NULL AND latitude IS NOT NULL
        ");
        // Trigger auto-sync geometry fasilitas (plan.md §4.5)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION sync_geometry_from_latlng() RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.latitude IS NOT NULL AND NEW.longitude IS NOT NULL THEN
                    NEW.geometry := ST_SetSRID(ST_MakePoint(NEW.longitude, NEW.latitude), 4326);
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
            DROP TRIGGER IF EXISTS trg_facilities_geometry ON facilities;
            CREATE TRIGGER trg_facilities_geometry BEFORE INSERT OR UPDATE ON facilities
            FOR EACH ROW EXECUTE FUNCTION sync_geometry_from_latlng();
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement('DROP TRIGGER IF EXISTS trg_facilities_geometry ON facilities');
    }
};
