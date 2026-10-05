<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropUnique('act_unique');
            $table->unique(['account_code', 'title', 'activity_date', 'kecamatan_id'], 'act_unique_tenant');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropUnique('act_unique_tenant');
            $table->unique(['account_code', 'title', 'activity_date'], 'act_unique');
        });
    }
};
