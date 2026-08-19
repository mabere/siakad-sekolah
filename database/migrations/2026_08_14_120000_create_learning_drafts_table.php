<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 50);
            $table->string('status', 30)->default('draft');
            $table->string('source', 30)->default('user');
            $table->unsignedInteger('version')->default(1);
            $table->string('provider', 50)->default('gemini');
            $table->string('model')->nullable();
            $table->json('input_context');
            $table->json('output');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'teacher_id', 'status']);
            $table->index(['school_id', 'teacher_id', 'document_type', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_drafts');
    }
};
