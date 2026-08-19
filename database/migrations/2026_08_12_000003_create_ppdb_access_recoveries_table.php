<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_access_recoveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20)->default('email');
            $table->string('destination_hash');
            $table->text('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('requested_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['ppdb_application_id', 'consumed_at', 'expires_at']);
            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_access_recoveries');
    }
};
