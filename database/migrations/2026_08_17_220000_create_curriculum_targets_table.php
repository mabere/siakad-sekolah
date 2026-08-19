<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('curriculum_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_name')->index();
            $table->string('phase', 20)->index(); // 'Fase A' s.d. 'Fase F'
            $table->unsignedTinyInteger('grade_level')->index(); // 1 s.d. 12
            $table->string('semester', 10)->default('1'); // '1', '2', '1 & 2'
            $table->unsignedSmallInteger('chapter_number')->default(1);
            $table->string('chapter_title');
            $table->string('element')->nullable(); // misal 'Membaca dan Memirsa, Menulis'
            $table->string('topic');
            $table->text('learning_objectives');
            $table->string('learning_model')->nullable();
            $table->json('p5_dimensions')->nullable();
            $table->text('meaningful_understanding')->nullable();
            $table->json('inquiry_questions')->nullable();
            $table->string('suggested_duration_jp')->nullable();
            $table->string('reference_source')->nullable()->default('Kepka BSKAP No. 032/H/KR/2024');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'phase', 'grade_level']);
            $table->index(['school_id', 'subject_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_targets');
    }
};
