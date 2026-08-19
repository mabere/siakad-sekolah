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
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'date']);
            $table->dropColumn(['date', 'status']);

            $table->integer('sick')->default(0)->after('student_id');
            $table->integer('permission')->default(0)->after('sick');
            $table->integer('absent')->default(0)->after('permission');

            // Add unique constraint to prevent duplicate attendance records for a student per semester per classroom
            $table->unique(['school_id', 'academic_year_id', 'classroom_id', 'student_id'], 'attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_unique');
            $table->dropColumn(['sick', 'permission', 'absent']);
            $table->date('date')->nullable();
            $table->string('status')->nullable();

            $table->unique(['student_id', 'date']);
        });
    }
};
