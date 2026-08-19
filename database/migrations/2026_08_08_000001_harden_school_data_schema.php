<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $firstSchoolId = DB::table('schools')->orderBy('id')->value('id');

        if ($firstSchoolId) {
            DB::table('users')->whereNull('school_id')->update(['school_id' => $firstSchoolId]);
        }

        Schema::table('students', function (Blueprint $table): void {
            $table->string('religion', 50)->nullable()->after('birth_date');
            $table->text('address')->nullable()->after('religion');
            $table->string('parent_phone', 20)->nullable()->after('address');
            $table->string('status', 20)->default('Aktif')->change();

            $table->unique(['school_id', 'nisn'], 'students_school_nisn_unique');
            $table->unique(['school_id', 'nis'], 'students_school_nis_unique');
            $table->index(['school_id', 'classroom_id', 'status'], 'students_school_class_status_index');
        });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->unique(['school_id', 'nip'], 'teachers_school_nip_unique');
            $table->index(['school_id', 'is_active'], 'teachers_school_active_index');
        });

        Schema::table('subjects', function (Blueprint $table): void {
            $table->unique(['school_id', 'code'], 'subjects_school_code_unique');
        });

        Schema::table('majors', function (Blueprint $table): void {
            $table->unique(['school_id', 'code'], 'majors_school_code_unique');
        });

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->unique(['school_id', 'academic_year_id', 'name'], 'classrooms_school_year_name_unique');
            $table->index(['school_id', 'academic_year_id', 'grade_level'], 'classrooms_school_year_grade_index');
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->index(['school_id', 'academic_year_id', 'classroom_id'], 'schedules_school_year_class_index');
            $table->index(['teacher_id', 'academic_year_id', 'day_of_week'], 'schedules_teacher_year_day_index');
        });

        Schema::table('grades', function (Blueprint $table): void {
            $table->index(['school_id', 'academic_year_id', 'classroom_id'], 'grades_school_year_class_index');
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->index(['school_id', 'academic_year_id', 'classroom_id'], 'attendances_school_year_class_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropIndex('attendances_school_year_class_index');
        });

        Schema::table('grades', function (Blueprint $table): void {
            $table->dropIndex('grades_school_year_class_index');
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropIndex('schedules_school_year_class_index');
            $table->dropIndex('schedules_teacher_year_day_index');
        });

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->dropUnique('classrooms_school_year_name_unique');
            $table->dropIndex('classrooms_school_year_grade_index');
        });

        Schema::table('majors', function (Blueprint $table): void {
            $table->dropUnique('majors_school_code_unique');
        });

        Schema::table('subjects', function (Blueprint $table): void {
            $table->dropUnique('subjects_school_code_unique');
        });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropUnique('teachers_school_nip_unique');
            $table->dropIndex('teachers_school_active_index');
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropUnique('students_school_nisn_unique');
            $table->dropUnique('students_school_nis_unique');
            $table->dropIndex('students_school_class_status_index');
            $table->dropColumn(['religion', 'address', 'parent_phone']);
        });
    }
};
