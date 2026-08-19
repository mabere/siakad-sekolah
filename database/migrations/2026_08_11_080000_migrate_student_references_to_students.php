<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $studentReferenceTables = [
        'student_payments',
        'parent_student_relations',
        'student_letters',
    ];

    public function up(): void
    {
        foreach ($this->studentReferenceTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('canonical_student_id')->nullable();
            });
        }

        DB::transaction(function (): void {
            foreach ($this->studentReferenceTables as $tableName) {
                $this->copyStudentReferences($tableName);
                $this->assertNoUnmappedStudents($tableName);
                $this->replaceStudentForeignKey($tableName, 'students');
            }

            $duplicateRelation = DB::table('parent_student_relations')
                ->select(['parent_user_id', 'student_id'])
                ->groupBy(['parent_user_id', 'student_id'])
                ->havingRaw('COUNT(*) > 1')
                ->first();

            if ($duplicateRelation) {
                throw new RuntimeException(
                    'Migrasi dibatalkan: terdapat relasi orang tua-siswa duplikat untuk parent_user_id '
                    .$duplicateRelation->parent_user_id.' dan student_id '.$duplicateRelation->student_id.'.',
                );
            }
        });

        Schema::table('parent_student_relations', function (Blueprint $table): void {
            $table->unique(
                ['parent_user_id', 'student_id'],
                'parent_student_relations_parent_student_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('parent_student_relations', function (Blueprint $table): void {
            $table->dropUnique('parent_student_relations_parent_student_unique');
        });

        foreach ($this->studentReferenceTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('legacy_user_student_id')->nullable();
            });
        }

        DB::transaction(function (): void {
            foreach ($this->studentReferenceTables as $tableName) {
                DB::statement(
                    "UPDATE {$tableName} AS target
                     SET legacy_user_student_id = (
                         SELECT students.user_id
                         FROM students
                         WHERE students.id = target.student_id
                     )",
                );

                $unmapped = DB::table($tableName)
                    ->whereNotNull('student_id')
                    ->whereNull('legacy_user_student_id')
                    ->count();

                if ($unmapped > 0) {
                    throw new RuntimeException(
                        "Rollback {$tableName} dibatalkan: {$unmapped} referensi siswa tidak memiliki user_id.",
                    );
                }

                $this->replaceStudentForeignKey($tableName, 'users', 'legacy_user_student_id');
            }
        });
    }

    private function copyStudentReferences(string $tableName): void
    {
        if ($tableName === 'parent_student_relations') {
            DB::statement(
                "UPDATE {$tableName} AS target
                 SET canonical_student_id = (
                     SELECT students.id
                     FROM students
                     INNER JOIN users AS student_users ON student_users.id = students.user_id
                     INNER JOIN users AS parent_users ON parent_users.id = target.parent_user_id
                     WHERE students.user_id = target.student_id
                       AND students.school_id = parent_users.school_id
                 )",
            );

            return;
        }

        DB::statement(
            "UPDATE {$tableName} AS target
             SET canonical_student_id = (
                 SELECT students.id
                 FROM students
                 WHERE students.user_id = target.student_id
                   AND students.school_id = target.school_id
             )",
        );
    }

    private function assertNoUnmappedStudents(string $tableName): void
    {
        $unmapped = DB::table($tableName)
            ->whereNotNull('student_id')
            ->whereNull('canonical_student_id')
            ->count();

        if ($unmapped > 0) {
            throw new RuntimeException(
                "Migrasi {$tableName} dibatalkan: {$unmapped} referensi users.id tidak dapat dipetakan ke students.id.",
            );
        }
    }

    private function replaceStudentForeignKey(
        string $tableName,
        string $referencedTable,
        string $replacementColumn = 'canonical_student_id',
    ): void {
        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });

        Schema::table($tableName, function (Blueprint $table) use ($replacementColumn): void {
            $table->renameColumn($replacementColumn, 'student_id');
        });

        Schema::table($tableName, function (Blueprint $table) use ($referencedTable): void {
            $table->foreign('student_id')
                ->references('id')
                ->on($referencedTable)
                ->cascadeOnDelete();
        });
    }
};
