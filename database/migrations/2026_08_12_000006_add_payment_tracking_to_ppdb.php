<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ppdb_payments', 'invoice_number')) {
            Schema::table('ppdb_payments', function (Blueprint $table): void {
                $table->string('invoice_number', 80)->nullable()->after('ppdb_application_id');
                $table->string('proof_original_name')->nullable()->after('proof_file');
                $table->string('proof_mime_type', 100)->nullable()->after('proof_original_name');
                $table->unsignedBigInteger('proof_file_size')->nullable()->after('proof_mime_type');
                $table->unique('invoice_number');
            });
        }

        DB::table('ppdb_payments')
            ->join('ppdb_applications', 'ppdb_applications.id', '=', 'ppdb_payments.ppdb_application_id')
            ->whereNull('ppdb_payments.invoice_number')
            ->select(['ppdb_payments.id as id', 'ppdb_applications.application_number'])
            ->orderBy('ppdb_payments.id')
            ->chunkById(250, function ($payments): void {
                foreach ($payments as $payment) {
                    DB::table('ppdb_payments')->where('id', $payment->id)->update([
                        'invoice_number' => 'INV-'.$payment->application_number,
                    ]);
                }
            }, 'ppdb_payments.id', 'id');

        if (Schema::hasTable('ppdb_payment_histories')) {
            return;
        }

        Schema::create('ppdb_payment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'ppdb_application_id', 'created_at']);
            $table->index(['ppdb_payment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_payment_histories');
        Schema::table('ppdb_payments', function (Blueprint $table): void {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['invoice_number', 'proof_original_name', 'proof_mime_type', 'proof_file_size']);
        });
    }
};
