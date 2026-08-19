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
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();

            $table->integer('weight_tugas')->default(30);
            $table->integer('weight_uts')->default(30);
            $table->integer('weight_uas')->default(40);

            $table->timestamps();

            // Ensure only one setting per teacher per subject per class per year
            $table->unique(['academic_year_id', 'classroom_id', 'subject_id', 'teacher_id'], 'unique_grade_weight_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_weights');
    }
};
