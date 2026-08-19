<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_student_activations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('requested_ip', 45)->nullable();
            $table->string('activated_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['ppdb_application_id', 'activated_at', 'revoked_at', 'expires_at']);
            $table->index(['school_id', 'user_id', 'activated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_student_activations');
    }
};
