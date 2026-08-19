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
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('meeting_number')->default(1);
            $table->string('learning_method')->default('Tatap Muka (Luring)');
            $table->text('topic_summary');
            $table->text('activities')->nullable();
            $table->text('student_notes')->nullable();
            $table->text('obstacles_and_solutions')->nullable();
            $table->string('status')->default('Disetujui');
            $table->timestamps();

            $table->unique(
                ['school_id', 'academic_year_id', 'schedule_id', 'date', 'meeting_number'],
                'teaching_journal_unique'
            );
            $table->index(['school_id', 'teacher_id', 'date'], 'teaching_journal_teacher_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
