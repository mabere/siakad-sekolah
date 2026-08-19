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
        // 1. Question Banks Table
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('code')->nullable();
            $table->string('grade_level')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'teacher_id'], 'qbank_school_idx');
        });

        // 2. Questions Table
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pg', 'essay'])->default('pg');
            $table->text('question_text');
            $table->json('options')->nullable(); // ['a' => '...', 'b' => '...', 'c' => '...', 'd' => '...', 'e' => '...']
            $table->string('correct_answer')->default('a'); // a, b, c, d, e or sample answer for essay
            $table->integer('score_weight')->default(1);
            $table->timestamps();
        });

        // 3. Exams / Quiz CBT Table
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('duration_minutes')->default(60);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('randomize_questions')->default(true);
            $table->enum('status', ['Draft', 'Aktif', 'Selesai'])->default('Draft');
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'teacher_id'], 'exam_school_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
