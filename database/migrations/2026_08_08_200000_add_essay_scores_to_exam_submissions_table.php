<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('exam_submissions', 'essay_scores')) {
                $table->json('essay_scores')->nullable()->after('answers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_submissions', 'essay_scores')) {
                $table->dropColumn('essay_scores');
            }
        });
    }
};
