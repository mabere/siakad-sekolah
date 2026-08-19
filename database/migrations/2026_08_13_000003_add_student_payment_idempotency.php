<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('student_payments')
            ->select(['school_id', 'student_id', 'payment_category_id', 'academic_year_id', 'month'])
            ->groupBy(['school_id', 'student_id', 'payment_category_id', 'academic_year_id', 'month'])
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate !== null) {
            throw new RuntimeException('Migrasi idempotensi tagihan dihentikan karena ditemukan tagihan duplikat yang harus direkonsiliasi terlebih dahulu.');
        }

        Schema::table('student_payments', function (Blueprint $table): void {
            $table->string('deduplication_key', 64)->nullable();
        });

        DB::table('student_payments')
            ->orderBy('id')
            ->chunkById(100, function ($payments): void {
                foreach ($payments as $payment) {
                    $key = hash('sha256', implode('|', [
                        $payment->school_id,
                        $payment->student_id,
                        $payment->payment_category_id,
                        $payment->academic_year_id ?? 'none',
                        $payment->month ?? 'none',
                    ]));

                    DB::table('student_payments')
                        ->where('id', $payment->id)
                        ->update(['deduplication_key' => $key]);
                }
            });

        Schema::table('student_payments', function (Blueprint $table): void {
            $table->string('deduplication_key', 64)->nullable(false)->change();
            $table->unique('deduplication_key', 'student_payments_deduplication_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_payments', function (Blueprint $table): void {
            $table->dropUnique('student_payments_deduplication_key_unique');
            $table->dropColumn('deduplication_key');
        });
    }
};
