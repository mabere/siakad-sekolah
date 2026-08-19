<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $configuredSeedPassword = config('app.seed_password');
        $seedPassword = is_string($configuredSeedPassword) && $configuredSeedPassword !== ''
            ? $configuredSeedPassword
            : (app()->isProduction() ? null : 'password');

        if (! $seedPassword) {
            throw new RuntimeException('SIAKAD_SEED_PASSWORD wajib diisi sebelum menjalankan seeder di production.');
        }

        $school = School::first();
        if (! $school) {
            $school = School::create([
                'name' => 'Sekolah Siakad',
                'level' => 'SMA', // Defaulting to SMA for testing penjurusan
                'status' => 'NEGERI',
            ]);
        }

        // Academic Years
        $ay1 = AcademicYear::create(['school_id' => $school->id, 'name' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false]);
        $ay2 = AcademicYear::create(['school_id' => $school->id, 'name' => '2025/2026', 'semester' => 'Genap', 'is_active' => false]);
        $ay3 = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);

        // Majors
        $ipa = Major::create(['school_id' => $school->id, 'name' => 'Matematika dan Ilmu Pengetahuan Alam', 'code' => 'MIPA']);
        $ips = Major::create(['school_id' => $school->id, 'name' => 'Ilmu Pengetahuan Sosial', 'code' => 'IPS']);

        // Subjects
        Subject::create(['school_id' => $school->id, 'name' => 'Matematika Wajib', 'code' => 'MAT-W', 'type' => 'Wajib']);
        Subject::create(['school_id' => $school->id, 'name' => 'Bahasa Indonesia', 'code' => 'BIN', 'type' => 'Wajib']);
        Subject::create(['school_id' => $school->id, 'name' => 'Fisika', 'code' => 'FIS', 'type' => 'Peminatan']);
        Subject::create(['school_id' => $school->id, 'name' => 'Ekonomi', 'code' => 'EKO', 'type' => 'Peminatan']);

        // Teachers
        $teachers = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Guru $i",
                'email' => "guru$i@siakad.test",
                'password' => Hash::make($seedPassword),
                'school_id' => $school->id,
            ]);
            $user->assignRole('Guru');

            $teachers[] = Teacher::create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'nip' => '19800101200501100'.$i,
                'name' => "Guru $i, S.Pd",
                'gender' => $i % 2 == 0 ? 'P' : 'L',
                'phone' => '08123456789'.$i,
                'is_active' => true,
            ]);
        }

        // Classrooms
        $classXIPA = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay3->id,
            'major_id' => $ipa->id,
            'teacher_id' => $teachers[0]->id,
            'name' => 'X MIPA 1',
            'grade_level' => '10',
        ]);

        $classXIPS = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay3->id,
            'major_id' => $ips->id,
            'teacher_id' => $teachers[1]->id,
            'name' => 'X IPS 1',
            'grade_level' => '10',
        ]);

        // Students
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'name' => "Siswa $i",
                'email' => "siswa$i@siakad.test",
                'password' => Hash::make($seedPassword),
                'school_id' => $school->id,
            ]);
            $user->assignRole('Siswa');

            $majorId = $i <= 10 ? $ipa->id : $ips->id;

            Student::create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'major_id' => $majorId,
                'nisn' => '00512345'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'nis' => '232410'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'name' => "Siswa $i",
                'gender' => $i % 2 == 0 ? 'P' : 'L',
                'status' => 'Aktif',
            ]);
        }
    }
}
