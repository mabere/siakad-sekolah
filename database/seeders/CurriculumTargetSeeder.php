<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Services\Curriculum\CurriculumBank;
use Illuminate\Database\Seeder;

class CurriculumTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::all();
        $adminUser = User::first();

        foreach ($schools as $school) {
            CurriculumBank::seedPresetsToSchool(
                schoolId: $school->id,
                subjectName: null, // seed all 5 subjects
                fase: 'Fase E',
                userId: $adminUser?->id
            );
        }
    }
}
