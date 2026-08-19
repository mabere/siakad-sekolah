<?php

namespace App\Livewire\Admin\Academic\Grade;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Input extends Component
{
    public ?AcademicYear $activeYear = null;

    #[Locked]
    public Classroom $classroom;

    #[Locked]
    public Subject $subject;

    // State for grades
    /** @var array<int|string, array<string, mixed>> */
    public array $grades = [];

    /** @var array<int|string, string|null> */
    public array $notes = [];

    /** @var array<int|string, string|null> */
    public array $tpHighest = [];

    /** @var array<int|string, string|null> */
    public array $tpLowest = [];

    // Calculated for UI display
    /** @var array<int|string, float|int> */
    public array $calculatedFinals = [];

    /** @var array<int|string, string> */
    public array $calculatedLetters = [];

    public function updatedTpHighest(mixed $value, int|string $studentId): void
    {
        $this->generateNote($studentId);
    }

    public function updatedTpLowest(mixed $value, int|string $studentId): void
    {
        $this->generateNote($studentId);
    }

    public function generateNote(int|string $studentId): void
    {
        $tp_h = trim($this->tpHighest[$studentId] ?? '');
        $tp_l = trim($this->tpLowest[$studentId] ?? '');

        $noteParts = [];
        if (filled($tp_h)) {
            $noteParts[] = 'Menunjukkan penguasaan yang sangat baik dalam '.lcfirst($tp_h).'.';
        }
        if (filled($tp_l)) {
            $noteParts[] = 'Perlu bimbingan lebih lanjut dalam '.lcfirst($tp_l).'.';
        }

        if (count($noteParts) > 0) {
            $this->notes[$studentId] = implode(' ', $noteParts);
        }
    }

    public function mount(Classroom $classroom, Subject $subject): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->classroom = Classroom::query()
            ->where('school_id', $schoolId)
            ->when($this->activeYear, fn ($query) => $query->where('academic_year_id', $this->activeYear->id))
            ->whereKey($classroom->id)
            ->firstOrFail();
        $this->subject = Subject::query()
            ->where('school_id', $schoolId)
            ->whereKey($subject->id)
            ->firstOrFail();

        $this->loadStudents();
    }

    public function calculatePreview(int|string $studentId): void
    {
        $tugas = (float) ($this->grades[$studentId]['tugas'] ?? 0);
        $uts = (float) ($this->grades[$studentId]['uts'] ?? 0);
        $uas = (float) ($this->grades[$studentId]['uas'] ?? 0);

        $final = ($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4);

        $this->calculatedFinals[$studentId] = round($final, 2);
        $this->calculatedLetters[$studentId] = $this->getGradeLetter($final);
    }

    private function getGradeLetter(float $score): string
    {
        if ($score >= 90) {
            return 'A';
        }
        if ($score >= 80) {
            return 'B';
        }
        if ($score >= 70) {
            return 'C';
        }
        if ($score >= 60) {
            return 'D';
        }

        return 'E';
    }

    public function loadStudents(): void
    {
        if (! $this->activeYear) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();
        $students = Student::where('school_id', $schoolId)
            ->where('classroom_id', $this->classroom->id)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $existingGrades = Grade::where('academic_year_id', $this->activeYear->id)
            ->where('school_id', $schoolId)
            ->where('classroom_id', $this->classroom->id)
            ->where('subject_id', $this->subject->id)
            ->get()
            ->keyBy('student_id');

        $this->grades = [];
        $this->notes = [];
        $this->tpHighest = [];
        $this->tpLowest = [];
        $this->calculatedFinals = [];
        $this->calculatedLetters = [];

        foreach ($students as $student) {
            if ($existingGrades->has($student->id)) {
                $g = $existingGrades[$student->id];
                $this->grades[$student->id] = [
                    'tugas' => $g->tugas,
                    'uts' => $g->uts,
                    'uas' => $g->uas,
                ];
                $this->notes[$student->id] = $g->notes;
                $this->tpHighest[$student->id] = $g->tp_highest;
                $this->tpLowest[$student->id] = $g->tp_lowest;
                $this->calculatedFinals[$student->id] = $g->final_score;
                $this->calculatedLetters[$student->id] = $g->grade_letter;
            } else {
                $this->grades[$student->id] = [
                    'tugas' => 0,
                    'uts' => 0,
                    'uas' => 0,
                ];
                $this->notes[$student->id] = '';
                $this->tpHighest[$student->id] = '';
                $this->tpLowest[$student->id] = '';
                $this->calculatePreview($student->id);
            }
        }
    }

    public function saveGrades(): void
    {
        if (! $this->activeYear) {
            session()->flash('error', 'Tidak ada Tahun Ajaran yang aktif.');

            return;
        }

        Validator::make([
            'grades' => $this->grades,
            'notes' => $this->notes,
            'tpHighest' => $this->tpHighest,
            'tpLowest' => $this->tpLowest,
        ], [
            'grades' => ['required', 'array'],
            'grades.*.tugas' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.uas' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['array'],
            'notes.*' => ['nullable', 'string', 'max:1000'],
            'tpHighest' => ['array'],
            'tpHighest.*' => ['nullable', 'string', 'max:255'],
            'tpLowest' => ['array'],
            'tpLowest.*' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();
        $classroom = Classroom::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYear->id)
            ->whereKey($this->classroom->id)
            ->firstOrFail();
        $subject = Subject::query()
            ->where('school_id', $schoolId)
            ->whereKey($this->subject->id)
            ->firstOrFail();
        $allowedStudentIds = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Aktif')
            ->pluck('id')
            ->map(fn ($id) => (string) $id);
        $submittedStudentIds = collect(array_keys($this->grades))->map(fn ($id) => (string) $id);

        if ($submittedStudentIds->diff($allowedStudentIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'grades' => 'Terdapat siswa yang tidak terdaftar pada kelas ini.',
            ]);
        }

        $now = now();
        $rows = $submittedStudentIds->map(function ($studentId) use ($schoolId, $classroom, $subject, $now): array {
            $components = $this->grades[$studentId];
            $tugas = (float) $components['tugas'];
            $uts = (float) $components['uts'];
            $uas = (float) $components['uas'];
            $final = ($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4);

            return [
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYear->id,
                'classroom_id' => $classroom->id,
                'subject_id' => $subject->id,
                'student_id' => (int) $studentId,
                'tugas' => $tugas,
                'uts' => $uts,
                'uas' => $uas,
                'final_score' => round($final, 2),
                'grade_letter' => $this->getGradeLetter($final),
                'tp_highest' => filled($this->tpHighest[$studentId] ?? null) ? $this->tpHighest[$studentId] : null,
                'tp_lowest' => filled($this->tpLowest[$studentId] ?? null) ? $this->tpLowest[$studentId] : null,
                'notes' => filled($this->notes[$studentId] ?? null) ? $this->notes[$studentId] : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        DB::transaction(fn () => Grade::query()->upsert(
            $rows,
            ['academic_year_id', 'subject_id', 'student_id'],
            ['classroom_id', 'tugas', 'uts', 'uas', 'final_score', 'grade_letter', 'tp_highest', 'tp_lowest', 'notes', 'updated_at'],
        ));

        $this->loadStudents(); // Refresh UI to match DB
        session()->flash('message', 'Data Nilai berhasil disimpan.');
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $students = Student::where('school_id', $schoolId)
            ->where('classroom_id', $this->classroom->id)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.academic.grade.input', [
            'students' => $students,
        ]);
    }
}
