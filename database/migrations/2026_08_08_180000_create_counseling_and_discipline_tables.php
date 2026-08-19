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
        // 1. Student Violations & Discipline Points Table
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('violation_name');
            $table->enum('category', ['Ringan', 'Sedang', 'Berat'])->default('Ringan');
            $table->integer('points')->default(5);
            $table->date('event_date')->nullable();
            $table->string('action_taken')->nullable(); // e.g. Teguran Lisan, Surat Peringatan 1, Panggilan Orang Tua
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'student_id'], 'student_violation_idx');
        });

        // 2. Counseling Records (BK) Table
        Schema::create('counseling_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->enum('counseling_type', ['Bimbingan Pribadi', 'Bimbingan Belajar', 'Bimbingan Sosial', 'Bimbingan Karir'])->default('Bimbingan Pribadi');
            $table->date('counseling_date')->nullable();
            $table->text('problem_description');
            $table->text('solution_plan')->nullable();
            $table->enum('status', ['Proses', 'Selesai', 'Rujukan'])->default('Proses');
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'student_id'], 'counseling_record_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_records');
        Schema::dropIfExists('student_violations');
    }
};
