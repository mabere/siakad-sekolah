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
        // 1. Extracurriculars Table
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('Pilihan'); // Wajib, Pilihan
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete(); // Pembina Ekskul
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id'], 'extracurricular_school_year_idx');
        });

        // 2. Extracurricular Members & Grades Table
        Schema::create('extracurricular_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('grade', ['A', 'B', 'C', 'D'])->default('A');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(
                ['extracurricular_id', 'student_id'],
                'extracurricular_member_unique'
            );
        });

        // 3. Student Achievements Table
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete(); // Pembimbing / Pencatat
            $table->string('event_name');
            $table->enum('category', ['Akademik', 'Non-Akademik'])->default('Akademik');
            $table->enum('level', ['Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'])->default('Kabupaten/Kota');
            $table->string('rank')->default('Juara 1'); // Juara 1, 2, 3, Harapan 1, Peserta
            $table->string('organizer')->nullable();
            $table->date('event_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'student_id'], 'student_achievement_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('extracurricular_members');
        Schema::dropIfExists('extracurriculars');
    }
};
