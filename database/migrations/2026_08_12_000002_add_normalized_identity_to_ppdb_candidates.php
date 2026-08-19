<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_candidates', function (Blueprint $table): void {
            $table->string('nik_normalized', 30)->nullable()->after('nik');
            $table->string('nisn_normalized', 30)->nullable()->after('nisn');
            $table->index('nik_normalized');
            $table->index('nisn_normalized');
        });

        DB::table('ppdb_candidates')->orderBy('id')->chunkById(250, function ($candidates): void {
            foreach ($candidates as $candidate) {
                DB::table('ppdb_candidates')
                    ->where('id', $candidate->id)
                    ->update([
                        'nik_normalized' => self::normalize($candidate->nik),
                        'nisn_normalized' => self::normalize($candidate->nisn),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_candidates', function (Blueprint $table): void {
            $table->dropIndex('ppdb_candidates_nik_normalized_index');
            $table->dropIndex('ppdb_candidates_nisn_normalized_index');
            $table->dropColumn(['nik_normalized', 'nisn_normalized']);
        });
    }

    private static function normalize(?string $value): ?string
    {
        $normalized = preg_replace('/[^0-9a-z]/i', '', trim((string) $value));

        return $normalized === '' ? null : strtolower((string) $normalized);
    }
};
