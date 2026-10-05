<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Profil wilayah M-01 untuk tabel kecamatans multi-tenant yang sudah ada. */
    public function up(): void
    {
        Schema::table('kecamatans', function (Blueprint $table) {
            $table->string('kode_bps', 10)->unique()->nullable()->after('code');
            $table->string('camat', 100)->nullable()->after('slug');
            $table->text('alamat')->nullable()->after('camat');
            $table->string('telepon', 20)->nullable()->after('alamat');
            $table->string('email', 100)->nullable()->after('telepon');
            $table->decimal('luas_km2', 10, 2)->nullable()->after('email');
            $table->integer('jumlah_penduduk')->nullable()->after('luas_km2');
            $table->decimal('center_lat', 10, 7)->nullable()->after('jumlah_penduduk');
            $table->decimal('center_lng', 11, 7)->nullable()->after('center_lat');
            $table->decimal('min_lat', 10, 7)->nullable()->after('center_lng');
            $table->decimal('max_lat', 10, 7)->nullable()->after('min_lat');
            $table->decimal('min_lng', 11, 7)->nullable()->after('max_lat');
            $table->decimal('max_lng', 11, 7)->nullable()->after('min_lng');
        });
    }

    public function down(): void
    {
        Schema::table('kecamatans', function (Blueprint $table) {
            $table->dropColumn([
                'kode_bps', 'camat', 'alamat', 'telepon', 'email', 'luas_km2',
                'jumlah_penduduk', 'center_lat', 'center_lng',
                'min_lat', 'max_lat', 'min_lng', 'max_lng',
            ]);
        });
    }
};
