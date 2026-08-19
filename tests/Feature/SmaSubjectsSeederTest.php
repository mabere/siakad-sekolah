<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Subject;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SmaSubjectsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmaSubjectsSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sma_subjects_seeder_seeds_all_standard_subjects_without_duplicates(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 1 Prestasi',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        $this->seed(SmaSubjectsSeeder::class);

        $subjects = Subject::where('school_id', $school->id)->get();

        // 37 standard subjects
        $this->assertCount(37, $subjects);

        // Verify key subjects exist
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'BIND', 'name' => 'Bahasa Indonesia', 'type' => 'Wajib']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'MTK', 'name' => 'Matematika', 'type' => 'Wajib']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'BING', 'name' => 'Bahasa Inggris', 'type' => 'Wajib']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'BIO', 'name' => 'Biologi', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'FIS', 'name' => 'Fisika', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'KIM', 'name' => 'Kimia', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'EKO', 'name' => 'Ekonomi', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'SOS', 'name' => 'Sosiologi', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'GEO', 'name' => 'Geografi', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'MTKL', 'name' => 'Matematika Tingkat Lanjut', 'type' => 'Peminatan']);
        $this->assertDatabaseHas('subjects', ['school_id' => $school->id, 'code' => 'BDER', 'name' => 'Bahasa Daerah (Muatan Lokal)', 'type' => 'Muatan Lokal']);
    }

    public function test_sma_subjects_seeder_is_idempotent_and_preserves_existing_records(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri 2 Prestasi',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        // Pre-create some subjects with custom codes / existing IDs
        $existingBIndo = Subject::create([
            'school_id' => $school->id,
            'code' => 'BIND',
            'name' => 'Bahasa Indonesia',
            'type' => 'Wajib',
        ]);

        $existingMtk = Subject::create([
            'school_id' => $school->id,
            'code' => 'MTK',
            'name' => 'Matematika',
            'type' => 'Wajib',
        ]);

        // Run seeder 1st time
        $this->seed(SmaSubjectsSeeder::class);
        $this->assertCount(37, Subject::where('school_id', $school->id)->get());

        // Assert original IDs are preserved
        $this->assertEquals($existingBIndo->id, Subject::where('school_id', $school->id)->where('code', 'BIND')->first()->id);
        $this->assertEquals($existingMtk->id, Subject::where('school_id', $school->id)->where('code', 'MTK')->first()->id);

        // Run seeder 2nd and 3rd times
        $this->seed(SmaSubjectsSeeder::class);
        $this->seed(SmaSubjectsSeeder::class);

        $this->assertCount(37, Subject::where('school_id', $school->id)->get());
    }
}
