<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('short_name', 30);
            $table->enum('type', ['seksi', 'sub_bagian', 'tim']);
            $table->string('color', 7)->default('#3B82F6');
            $table->string('head_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('email')->constrained('sections')->nullOnDelete();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->date('activity_date');
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('account_code', 30);
            $table->text('program_name');
            $table->string('title');
            $table->integer('requirement_qty')->default(0);
            $table->integer('total_qty')->default(0);
            $table->string('unit', 30)->default('Orang / Kali');
            $table->decimal('budget_pagu', 15, 2)->default(0);
            $table->decimal('budget_realization', 15, 2)->default(0);
            $table->string('location')->nullable();
            $table->foreignId('pptk_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft','diajukan','diverifikasi','disetujui','berjalan','selesai','ditolak'])->default('draft');
            $table->integer('progress')->default(0);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['activity_date', 'section_id']);
            $table->index('status');
            $table->unique(['account_code', 'title', 'activity_date'], 'act_unique');
        });

        Schema::create('activity_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 30)->default('lainnya');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('item');
            $table->boolean('is_checked')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_at_time', 30);
            $table->enum('decision', ['diajukan','diverifikasi','disetujui','ditolak','revisi'])->default('diajukan');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type', 30)->default('info');
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->string('model_type', 100)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('verifications');
        Schema::dropIfExists('activity_checklists');
        Schema::dropIfExists('activity_documents');
        Schema::dropIfExists('activities');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
            $table->dropColumn(['phone', 'is_active']);
        });
        Schema::dropIfExists('sections');
    }
};
