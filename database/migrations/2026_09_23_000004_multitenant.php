<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('kecamatan_id')->nullable()->after('section_id')->constrained('kecamatans')->nullOnDelete();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('kecamatan_id')->nullable()->after('section_id')->constrained('kecamatans')->nullOnDelete();
            $table->index('kecamatan_id');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kecamatan_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kecamatan_id');
        });
        Schema::dropIfExists('kecamatans');
    }
};
