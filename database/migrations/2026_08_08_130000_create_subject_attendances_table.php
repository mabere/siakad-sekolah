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
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('meeting_number')->default(1);
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpa'])->default('Hadir');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['school_id', 'academic_year_id', 'schedule_id', 'date', 'student_id'],
                'subject_attendance_unique'
            );
            $table->index(['school_id', 'schedule_id', 'date'], 'subject_att_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_attendances');
    }
};
