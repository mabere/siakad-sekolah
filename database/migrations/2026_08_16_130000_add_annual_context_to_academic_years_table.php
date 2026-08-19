<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table): void {
            $table->string('curriculum_type')->nullable();
            $table->text('local_content')->nullable();
            $table->text('p5_focus')->nullable();
            $table->unsignedTinyInteger('effective_weeks')->nullable();
            $table->text('calendar_notes')->nullable();
        });

        // Preserve the existing school-level curriculum choice for records
        // that already existed before annual curriculum settings were added.
        $settings = DB::table('system_settings')
            ->where('key', 'curriculum_type')
            ->get(['school_id', 'value']);

        foreach ($settings as $setting) {
            $decodedValue = json_decode((string) $setting->value, true);
            $curriculumType = is_string($decodedValue) ? $decodedValue : (string) $setting->value;

            if (! in_array($curriculumType, ['MERDEKA', 'K13'], true)) {
                continue;
            }

            DB::table('academic_years')
                ->where('school_id', $setting->school_id)
                ->update(['curriculum_type' => $curriculumType]);
        }
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table): void {
            $table->dropColumn([
                'curriculum_type',
                'local_content',
                'p5_focus',
                'effective_weeks',
                'calendar_notes',
            ]);
        });
    }
};
