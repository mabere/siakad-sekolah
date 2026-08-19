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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            // Komponen Nilai
            $table->integer('tugas')->nullable()->default(0);
            $table->integer('uts')->nullable()->default(0);
            $table->integer('uas')->nullable()->default(0);

            // Kalkulasi
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('grade_letter', 2)->nullable(); // A, B, C, dll.
            $table->text('notes')->nullable();

            $table->timestamps();

            // Seorang siswa hanya punya 1 record nilai per mata pelajaran per semester (academic year)
            $table->unique(['academic_year_id', 'subject_id', 'student_id'], 'unique_grade_per_student_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
