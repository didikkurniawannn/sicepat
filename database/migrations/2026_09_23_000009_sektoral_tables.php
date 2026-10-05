<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tabel modul Data Sektoral: desa, indikator, tipe fasilitas, fasilitas, entri, AI, audit. */
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->string('kode_bps', 15)->unique();
            $table->string('nama', 100);
            $table->decimal('luas_ha', 10, 2)->nullable();
            $table->timestamps();
            $table->index('kecamatan_id');
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('modul', 10);
            $table->string('kode', 50)->unique();
            $table->string('nama', 150);
            $table->string('satuan', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('facility_types', function (Blueprint $table) {
            $table->id();
            $table->string('modul', 10);
            $table->string('slug', 50)->unique();
            $table->string('nama', 100);
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans');
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('name', 150);
            $table->string('type', 50);
            $table->string('modul', 10);
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->string('location_source', 20)->default('map_click');
            $table->timestamp('coordinate_verified_at')->nullable();
            $table->foreignId('coordinate_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['kecamatan_id', 'type', 'status']);
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('data_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans');
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators');
            $table->integer('year');
            $table->decimal('value', 15, 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['kecamatan_id', 'indicator_id', 'year']);
        });

        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->string('title', 200);
            $table->text('content');
            $table->text('reasoning')->nullable();
            $table->string('engine', 30)->default('rule_based');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->string('action', 20);
            $table->string('model_type', 100)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['kecamatan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('data_entries');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('facility_types');
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('villages');
    }
};
