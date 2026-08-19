<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_periods', function (Blueprint $table): void {
            $table->timestamp('selection_finalized_at')->nullable()->after('re_registration_ends_at');
            $table->foreignId('selection_finalized_by')->nullable()->after('selection_finalized_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('ppdb_selection_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_pathway_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('rank')->nullable();
            $table->string('selection_status', 30);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('average_score', 8, 2)->default(0);
            $table->timestamp('snapshot_at');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('ppdb_application_id');
            $table->index(['school_id', 'ppdb_period_id', 'selection_status']);
            $table->index(['ppdb_period_id', 'ppdb_pathway_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_selection_results');
        Schema::table('ppdb_periods', function (Blueprint $table): void {
            $table->dropForeign(['selection_finalized_by']);
            $table->dropColumn(['selection_finalized_at', 'selection_finalized_by']);
        });
    }
};
