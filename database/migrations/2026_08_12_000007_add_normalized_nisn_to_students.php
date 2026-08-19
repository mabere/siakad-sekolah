<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->string('nisn_normalized', 30)->nullable()->after('nisn');
            $table->index(['school_id', 'nisn_normalized']);
        });

        DB::table('students')->orderBy('id')->chunkById(250, function ($students): void {
            foreach ($students as $student) {
                $normalized = preg_replace('/[^0-9a-z]/i', '', trim((string) $student->nisn));
                DB::table('students')->where('id', $student->id)->update([
                    'nisn_normalized' => $normalized === '' ? null : strtolower((string) $normalized),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropIndex('students_school_id_nisn_normalized_index');
            $table->dropColumn('nisn_normalized');
        });
    }
};
