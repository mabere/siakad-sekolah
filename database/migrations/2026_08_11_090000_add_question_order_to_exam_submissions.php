<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exam_submissions', 'question_order')) {
            Schema::table('exam_submissions', function (Blueprint $table): void {
                $table->json('question_order')->nullable()->after('answers');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exam_submissions', 'question_order')) {
            Schema::table('exam_submissions', function (Blueprint $table): void {
                $table->dropColumn('question_order');
            });
        }
    }
};
