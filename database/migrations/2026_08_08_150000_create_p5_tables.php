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
        // 1. Projects Table
        Schema::create('p5_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete(); // Coordinator/Facilitator
            $table->string('title');
            $table->string('theme');
            $table->text('description')->nullable();
            $table->string('phase')->default('Fase D'); // Fase A, B, C, D, E, F
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'classroom_id'], 'p5_proj_school_year_class_idx');
        });

        // 2. Dimensions & Sub-elements Table
        Schema::create('p5_project_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_id')->constrained()->cascadeOnDelete();
            $table->string('dimension_name'); // Beriman & Bertakwa, Berkebinekaan Global, Gotong Royong, Mandiri, Bernalar Kritis, Kreatif
            $table->string('element_name');
            $table->string('sub_element');
            $table->text('target_description')->nullable();
            $table->timestamps();
        });

        // 3. Qualitative Assessments Table (BB, MB, BSH, SB)
        Schema::create('p5_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('p5_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('p5_project_dimension_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('score', ['BB', 'MB', 'BSH', 'SB'])->default('BSH');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['p5_project_id', 'p5_project_dimension_id', 'student_id'],
                'p5_assessment_unique'
            );
        });

        // 4. Student Process Notes Table
        Schema::create('p5_student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('p5_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('process_notes');
            $table->timestamps();

            $table->unique(
                ['p5_project_id', 'student_id'],
                'p5_student_note_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p5_student_notes');
        Schema::dropIfExists('p5_assessments');
        Schema::dropIfExists('p5_project_dimensions');
        Schema::dropIfExists('p5_projects');
    }
};
