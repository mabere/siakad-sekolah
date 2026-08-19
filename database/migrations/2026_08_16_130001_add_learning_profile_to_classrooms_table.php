<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table): void {
            $table->text('student_needs')->nullable();
            $table->text('available_facilities')->nullable();
            $table->string('learning_environment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table): void {
            $table->dropColumn([
                'student_needs',
                'available_facilities',
                'learning_environment',
            ]);
        });
    }
};
