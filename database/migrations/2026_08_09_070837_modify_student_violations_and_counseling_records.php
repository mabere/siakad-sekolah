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
        Schema::table('student_violations', function (Blueprint $table) {
            $table->foreignId('violation_master_id')->nullable()->after('reporter_teacher_id')->constrained('violation_masters')->nullOnDelete();
            // Drop violation_name if it exists
            if (Schema::hasColumn('student_violations', 'violation_name')) {
                $table->dropColumn('violation_name');
            }
        });

        Schema::table('counseling_records', function (Blueprint $table) {
            $table->time('counseling_time')->nullable()->after('counseling_date');
            $table->date('follow_up_date')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counseling_records', function (Blueprint $table) {
            $table->dropColumn(['counseling_time', 'follow_up_date']);
        });

        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropForeign(['violation_master_id']);
            $table->dropColumn('violation_master_id');
            $table->string('violation_name')->after('reporter_teacher_id')->nullable();
        });
    }
};
