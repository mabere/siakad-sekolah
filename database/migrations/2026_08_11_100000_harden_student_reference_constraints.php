<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'student_payments',
        'parent_student_relations',
        'student_letters',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (DB::table($tableName)->whereNull('student_id')->exists()) {
                throw new RuntimeException('Migrasi dibatalkan: terdapat referensi student_id kosong pada '.$tableName.'.');
            }
        }

        Schema::table('student_payments', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
            $table->index(
                ['school_id', 'student_id', 'academic_year_id', 'status'],
                'student_payments_scope_idx',
            );
        });
        Schema::table('parent_student_relations', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
        });
        Schema::table('student_letters', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
            $table->index(['school_id', 'student_id', 'created_at'], 'student_letters_scope_idx');
        });
        Schema::table('exam_submissions', function (Blueprint $table): void {
            $table->index(
                ['school_id', 'academic_year_id', 'student_id', 'status'],
                'exam_submissions_student_scope_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table): void {
            $table->dropIndex('exam_submissions_student_scope_idx');
        });
        Schema::table('student_letters', function (Blueprint $table): void {
            $table->dropIndex('student_letters_scope_idx');
            $table->unsignedBigInteger('student_id')->nullable()->change();
        });
        Schema::table('parent_student_relations', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->nullable()->change();
        });
        Schema::table('student_payments', function (Blueprint $table): void {
            $table->dropIndex('student_payments_scope_idx');
            $table->unsignedBigInteger('student_id')->nullable()->change();
        });
    }
};
