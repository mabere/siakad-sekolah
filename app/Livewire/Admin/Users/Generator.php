<?php

namespace App\Livewire\Admin\Users;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Generator extends Component
{
    public int $teacherCount = 0;

    public int $studentCount = 0;

    /** @var array<int, array{name: string, email: string, password: string}> */
    public array $generatedCredentials = [];

    private const BATCH_SIZE = 25;

    public function mount(): void
    {
        $this->calculateCounts();
    }

    public function calculateCounts(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->teacherCount = Teacher::where('school_id', $schoolId)
            ->whereNull('user_id')
            ->count();

        $this->studentCount = Student::where('school_id', $schoolId)
            ->whereNull('user_id')
            ->count();
    }

    public function generateTeachers(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $teachers = Teacher::where('school_id', $schoolId)
            ->whereNull('user_id')
            ->orderBy('id')
            ->limit(self::BATCH_SIZE)
            ->get();

        $this->generatedCredentials = [];

        DB::transaction(function () use ($teachers, $schoolId): void {
            foreach ($teachers as $teacher) {
                $email = $this->uniqueEmail($teacher->nip ?: 'guru.'.Str::lower(Str::random(8)));
                $temporaryPassword = Str::password(16);

                $user = User::create([
                    'name' => $teacher->name,
                    'email' => $email,
                    'password' => $temporaryPassword,
                    'school_id' => $schoolId,
                ]);

                $user->assignRole('Guru');
                $teacher->update(['user_id' => $user->id]);

                $this->generatedCredentials[] = [
                    'name' => $teacher->name,
                    'email' => $email,
                    'password' => $temporaryPassword,
                ];
            }
        });

        $this->calculateCounts();
        session()->flash('message_teacher', count($this->generatedCredentials).' akun Guru berhasil dibuat.');
    }

    public function generateStudents(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $students = Student::where('school_id', $schoolId)
            ->whereNull('user_id')
            ->orderBy('id')
            ->limit(self::BATCH_SIZE)
            ->get();

        $this->generatedCredentials = [];

        DB::transaction(function () use ($students, $schoolId): void {
            foreach ($students as $student) {
                $identifier = $student->nisn ?: ($student->nis ?: 'siswa.'.Str::lower(Str::random(8)));
                $email = $this->uniqueEmail($identifier);
                $temporaryPassword = Str::password(16);

                $user = User::create([
                    'name' => $student->name,
                    'email' => $email,
                    'password' => $temporaryPassword,
                    'school_id' => $schoolId,
                ]);

                $user->assignRole('Siswa');
                $student->update(['user_id' => $user->id]);

                $this->generatedCredentials[] = [
                    'name' => $student->name,
                    'email' => $email,
                    'password' => $temporaryPassword,
                ];
            }
        });

        $this->calculateCounts();
        session()->flash('message_student', count($this->generatedCredentials).' akun Siswa berhasil dibuat.');
    }

    public function clearGeneratedCredentials(): void
    {
        $this->generatedCredentials = [];
    }

    private function uniqueEmail(string $identifier): string
    {
        $base = Str::lower(Str::slug($identifier, '.'));
        $candidate = $base.'@siakad.test';
        $counter = 1;

        while (User::query()->where('email', $candidate)->exists()) {
            $candidate = $base.'.'.$counter.'@siakad.test';
            $counter++;
        }

        return $candidate;
    }

    public function render(): View
    {
        return view('livewire.admin.users.generator');
    }
}
