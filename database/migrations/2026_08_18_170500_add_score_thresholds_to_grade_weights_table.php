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
        Schema::table('grade_weights', function (Blueprint $table) {
            $table->integer('min_score_a')->default(90)->after('weight_uas');
            $table->integer('min_score_b')->default(80)->after('min_score_a');
            $table->integer('min_score_c')->default(70)->after('min_score_b');
            $table->integer('min_score_d')->default(60)->after('min_score_c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_weights', function (Blueprint $table) {
            $table->dropColumn(['min_score_a', 'min_score_b', 'min_score_c', 'min_score_d']);
        });
    }
};
