<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->string('level', 20);
            $table->dateTime('registration_starts_at');
            $table->dateTime('registration_ends_at');
            $table->dateTime('verification_ends_at')->nullable();
            $table->dateTime('announcement_at')->nullable();
            $table->dateTime('re_registration_ends_at')->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('payment_required')->default(true);
            $table->decimal('default_registration_fee', 12, 2)->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'academic_year_id', 'status']);
        });

        Schema::create('ppdb_pathways', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_period_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('quota')->default(0);
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['ppdb_period_id', 'code']);
            $table->index(['ppdb_period_id', 'is_active', 'sort_order']);
        });

        Schema::create('ppdb_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_pathway_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->boolean('is_required')->default(true);
            $table->string('accepted_mimes')->nullable();
            $table->unsignedInteger('max_file_size_kb')->default(5120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['ppdb_pathway_id', 'code']);
        });

        Schema::create('ppdb_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_pathway_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('application_number', 50);
            $table->string('source', 20)->default('online');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->text('access_code_hash');
            $table->string('verification_status', 30)->default('draft');
            $table->string('payment_status', 30)->default('not_required');
            $table->string('selection_status', 30)->default('pending');
            $table->string('reregistration_status', 30)->default('not_open');
            $table->string('conversion_status', 30)->default('not_ready');
            $table->text('revision_note')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('selected_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();

            $table->unique(['ppdb_period_id', 'application_number']);
            $table->index(['school_id', 'ppdb_period_id', 'verification_status']);
            $table->index(['school_id', 'ppdb_period_id', 'selection_status']);
        });

        Schema::create('ppdb_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('nik', 30)->nullable();
            $table->string('nisn', 30)->nullable();
            $table->string('gender', 1)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('previous_school_npsn', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('regency')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->timestamps();

            $table->index(['nik', 'nisn']);
        });

        Schema::create('ppdb_guardians', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->string('relationship', 20)->default('ayah');
            $table->string('name');
            $table->string('nik', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->index(['ppdb_application_id', 'is_primary']);
        });

        Schema::create('ppdb_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_requirement_id')->constrained()->restrictOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['ppdb_application_id', 'ppdb_requirement_id']);
            $table->index(['ppdb_application_id', 'status']);
        });

        Schema::create('ppdb_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30)->default('registration');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 30)->nullable();
            $table->string('proof_file')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['ppdb_application_id', 'type', 'status']);
        });

        Schema::create('ppdb_selection_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('criterion', 100);
            $table->decimal('score', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();

            $table->unique(['ppdb_application_id', 'criterion']);
        });

        Schema::create('ppdb_re_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ppdb_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_application_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'ppdb_application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_audit_logs');
        Schema::dropIfExists('ppdb_re_registrations');
        Schema::dropIfExists('ppdb_selection_scores');
        Schema::dropIfExists('ppdb_payments');
        Schema::dropIfExists('ppdb_documents');
        Schema::dropIfExists('ppdb_guardians');
        Schema::dropIfExists('ppdb_candidates');
        Schema::dropIfExists('ppdb_applications');
        Schema::dropIfExists('ppdb_requirements');
        Schema::dropIfExists('ppdb_pathways');
        Schema::dropIfExists('ppdb_periods');
    }
};
