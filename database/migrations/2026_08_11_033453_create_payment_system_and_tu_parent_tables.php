<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kategori / Pos Tagihan Keuangan
        Schema::create('payment_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // misal: SPP Bulanan, Uang Gedung, Seragam
            $table->enum('type', ['monthly_spp', 'one_time', 'optional'])->default('monthly_spp');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Transaksi / Tagihan Siswa
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_category_id')->constrained('payment_categories')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->unsignedTinyInteger('month')->nullable(); // 1-12 (untuk SPP Bulanan)
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'pending_confirmation', 'paid', 'cancelled'])->default('unpaid');
            $table->string('payment_method')->nullable(); // cash, bank_transfer
            $table->string('proof_file')->nullable(); // path bukti transfer
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete(); // User Staff TU
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Relasi Orang Tua / Wali dengan Siswa
        Schema::create('parent_student_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('relationship_type', ['ayah', 'ibu', 'wali'])->default('ayah');
            $table->timestamps();
        });

        // 4. Pengajuan & Pencetakan Surat Siswa oleh Staff TU
        Schema::create('student_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('letter_number')->unique()->nullable();
            $table->enum('letter_type', ['surat_keterangan_aktif', 'surat_berkelakuan_baik', 'surat_pindah_sekolah'])->default('surat_keterangan_aktif');
            $table->string('purpose')->nullable(); // Keperluan permohonan surat
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete(); // Staff TU
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_letters');
        Schema::dropIfExists('parent_student_relations');
        Schema::dropIfExists('student_payments');
        Schema::dropIfExists('payment_categories');
    }
};
