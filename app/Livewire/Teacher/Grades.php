<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class Grades extends Component
{
    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    /** @var array<int, array<string, mixed>> */
    public array $schedules = [];

    #[Url(as: 'schedule')]
    public string $selectedScheduleId = '';

    /** @var array<int|string, mixed> */
    public array $gradeData = [];

    public bool $isLocked = false;

    // Custom Weights (Persentase Bobot)
    public int $weightTugas = 30;

    public int $weightUts = 30;

    public int $weightUas = 40;

    // Custom Letter Grade Scale Thresholds (Skor Minimum Predikat)
    public int $minScoreA = 90;

    public int $minScoreB = 80;

    public int $minScoreC = 70;

    public int $minScoreD = 60;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $activeYear?->id;

        $teacher = Teacher::where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        $this->teacherId = $teacher?->id;
        abort_unless($teacher !== null, 403, 'Akun belum terhubung ke data guru aktif.');

        if ($this->activeYearId && $this->teacherId) {
            $this->schedules = Schedule::with(['classroom', 'subject'])
                ->where('school_id', $schoolId)
                ->where('teacher_id', $this->teacherId)
                ->where('academic_year_id', $this->activeYearId)
                ->get()
                ->unique(fn ($item) => $item->classroom_id.'-'.$item->subject_id)
                ->values()
                ->toArray();

            // Auto-select schedule if provided or if single schedule exists
            if (! empty($this->selectedScheduleId)) {
                $this->loadGradesForSelectedSchedule();
            } elseif (count($this->schedules) === 1) {
                $this->selectedScheduleId = (string) $this->schedules[0]['id'];
                $this->loadGradesForSelectedSchedule();
            }
        }
    }

    public function updatedSelectedScheduleId(): void
    {
        $this->loadGradesForSelectedSchedule();
    }

    public function loadGradesForSelectedSchedule(): void
    {
        $this->gradeData = [];
        $this->isLocked = false;

        if (! $this->selectedScheduleId || ! $this->activeYearId || ! $this->teacherId) {
            return;
        }

        $schedule = $this->accessibleScheduleQuery()
            ->with(['classroom', 'subject'])
            ->whereKey($this->selectedScheduleId)
            ->first();

        if (! $schedule) {
            return;
        }

        // Load existing custom weights & letter thresholds
        $weight = GradeWeight::where('academic_year_id', $this->activeYearId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('subject_id', $schedule->subject_id)
            ->where('teacher_id', $this->teacherId)
            ->first();

        if ($weight) {
            $this->weightTugas = (int) $weight->weight_tugas;
            $this->weightUts = (int) $weight->weight_uts;
            $this->weightUas = (int) $weight->weight_uas;
            $this->minScoreA = (int) ($weight->min_score_a ?? 90);
            $this->minScoreB = (int) ($weight->min_score_b ?? 80);
            $this->minScoreC = (int) ($weight->min_score_c ?? 70);
            $this->minScoreD = (int) ($weight->min_score_d ?? 60);
        } else {
            $this->weightTugas = 30;
            $this->weightUts = 30;
            $this->weightUas = 40;
            $this->minScoreA = 90;
            $this->minScoreB = 80;
            $this->minScoreC = 70;
            $this->minScoreD = 60;
        }

        // Load students and existing grades
        $students = Student::where('school_id', app(CurrentSchool::class)->id())
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $existingGrades = Grade::where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('subject_id', $schedule->subject_id)
            ->get()
            ->keyBy('student_id');

        $this->isLocked = $existingGrades->where('is_locked', true)->count() > 0;

        foreach ($students as $student) {
            $record = $existingGrades->get($student->id);
            $tugas = $record ? (int) $record->tugas : 0;
            $uts = $record ? (int) $record->uts : 0;
            $uas = $record ? (int) $record->uas : 0;

            $finalScore = $record && $record->final_score !== null
                ? (float) $record->final_score
                : $this->calculateFinalScore($tugas, $uts, $uas);

            $letter = $record && ! empty($record->grade_letter)
                ? $record->grade_letter
                : $this->calculateGradeLetter($finalScore);

            $this->gradeData[$student->id] = [
                'tugas' => $tugas,
                'uts' => $uts,
                'uas' => $uas,
                'final_score' => $finalScore,
                'grade_letter' => $letter,
                'tp_highest' => $record ? ($record->tp_highest ?? '') : '',
                'tp_lowest' => $record ? ($record->tp_lowest ?? '') : '',
                'notes' => $record ? ($record->notes ?? '') : '',
                'is_saved' => $record !== null,
            ];
        }
    }

    public function updatedGradeData(mixed $value, string $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $studentId = $parts[0];
            $field = $parts[1];

            if (in_array($field, ['tugas', 'uts', 'uas'], true)) {
                $tugas = (int) ($this->gradeData[$studentId]['tugas'] ?? 0);
                $uts = (int) ($this->gradeData[$studentId]['uts'] ?? 0);
                $uas = (int) ($this->gradeData[$studentId]['uas'] ?? 0);

                $finalScore = $this->calculateFinalScore($tugas, $uts, $uas);
                $this->gradeData[$studentId]['final_score'] = $finalScore;
                $this->gradeData[$studentId]['grade_letter'] = $this->calculateGradeLetter($finalScore);
            } elseif (in_array($field, ['tp_highest', 'tp_lowest'], true)) {
                $this->generateNote($studentId);
            }
        }
    }

    public function generateNote(int|string $studentId): void
    {
        $tp_h = trim($this->gradeData[$studentId]['tp_highest'] ?? '');
        $tp_l = trim($this->gradeData[$studentId]['tp_lowest'] ?? '');

        $noteParts = [];
        if (filled($tp_h)) {
            $noteParts[] = 'Menunjukkan penguasaan yang sangat baik dalam '.lcfirst($tp_h).'.';
        }
        if (filled($tp_l)) {
            $noteParts[] = 'Perlu bimbingan lebih lanjut dalam '.lcfirst($tp_l).'.';
        }

        if (count($noteParts) > 0) {
            $this->gradeData[$studentId]['notes'] = implode(' ', $noteParts);
        }
    }

    public function applyGradeScalePreset(string $preset): void
    {
        switch ($preset) {
            case 'kktp_75':
                $this->minScoreA = 92;
                $this->minScoreB = 83;
                $this->minScoreC = 75;
                $this->minScoreD = 65;
                break;
            case 'kktp_70':
                $this->minScoreA = 90;
                $this->minScoreB = 80;
                $this->minScoreC = 70;
                $this->minScoreD = 60;
                break;
            case 'kktp_65':
                $this->minScoreA = 88;
                $this->minScoreB = 77;
                $this->minScoreC = 65;
                $this->minScoreD = 55;
                break;
            case 'standard':
            default:
                $this->minScoreA = 90;
                $this->minScoreB = 80;
                $this->minScoreC = 70;
                $this->minScoreD = 60;
                break;
        }
    }

    public function saveWeights(): void
    {
        if ($this->isLocked) {
            session()->flash('weight_error', 'Nilai telah dikunci. Buka kunci terlebih dahulu untuk mengubah bobot.');

            return;
        }

        if (! $this->selectedScheduleId) {
            return;
        }

        Validator::make([
            'tugas' => $this->weightTugas,
            'uts' => $this->weightUts,
            'uas' => $this->weightUas,
            'min_score_a' => $this->minScoreA,
            'min_score_b' => $this->minScoreB,
            'min_score_c' => $this->minScoreC,
            'min_score_d' => $this->minScoreD,
        ], [
            'tugas' => ['integer', 'min:0', 'max:100'],
            'uts' => ['integer', 'min:0', 'max:100'],
            'uas' => ['integer', 'min:0', 'max:100'],
            'min_score_a' => ['integer', 'min:1', 'max:100'],
            'min_score_b' => ['integer', 'min:1', 'max:99'],
            'min_score_c' => ['integer', 'min:1', 'max:98'],
            'min_score_d' => ['integer', 'min:1', 'max:97'],
        ])->validate();

        $total = $this->weightTugas + $this->weightUts + $this->weightUas;
        if ($total !== 100) {
            session()->flash('weight_error', 'Total bobot harus tepat 100%. Saat ini: '.$total.'%');

            return;
        }

        // Validate descending order of letter thresholds
        if (! ($this->minScoreA > $this->minScoreB && $this->minScoreB > $this->minScoreC && $this->minScoreC > $this->minScoreD)) {
            session()->flash('weight_error', 'Urutan skor predikat tidak valid. Pastikan Nilai Min A > Min B > Min C > Min D.');

            return;
        }

        $schedule = $this->accessibleScheduleQuery()->whereKey($this->selectedScheduleId)->first();
        if (! $schedule) {
            return;
        }

        GradeWeight::updateOrCreate(
            [
                'academic_year_id' => $this->activeYearId,
                'classroom_id' => $schedule->classroom_id,
                'subject_id' => $schedule->subject_id,
                'teacher_id' => $this->teacherId,
            ],
            [
                'weight_tugas' => $this->weightTugas,
                'weight_uts' => $this->weightUts,
                'weight_uas' => $this->weightUas,
                'min_score_a' => $this->minScoreA,
                'min_score_b' => $this->minScoreB,
                'min_score_c' => $this->minScoreC,
                'min_score_d' => $this->minScoreD,
            ]
        );

        // Recalculate local final scores & grade letters for all students based on new weights and scale
        foreach ($this->gradeData as $studentId => $row) {
            $tugas = (int) ($row['tugas'] ?? 0);
            $uts = (int) ($row['uts'] ?? 0);
            $uas = (int) ($row['uas'] ?? 0);
            $finalScore = $this->calculateFinalScore($tugas, $uts, $uas);
            $this->gradeData[$studentId]['final_score'] = $finalScore;
            $this->gradeData[$studentId]['grade_letter'] = $this->calculateGradeLetter($finalScore);
        }

        session()->flash('weight_success', "✓ Pengaturan bobot ({$this->weightTugas}/{$this->weightUts}/{$this->weightUas}%) dan rentang predikat (A: ≥{$this->minScoreA}, B: ≥{$this->minScoreB}, C: ≥{$this->minScoreC}, D: ≥{$this->minScoreD}) berhasil disimpan.");
    }

    public function saveAllGrades(): void
    {
        if ($this->isLocked) {
            session()->flash('grade_error', 'Nilai sedang dikunci. Buka kunci terlebih dahulu untuk mengedit atau menyimpan.');

            return;
        }

        if (! $this->selectedScheduleId) {
            return;
        }

        $schedule = $this->accessibleScheduleQuery()->whereKey($this->selectedScheduleId)->first();
        if (! $schedule) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', 'Aktif')
            ->get();

        if ($students->isEmpty()) {
            session()->flash('grade_error', 'Tidak ada siswa aktif di kelas ini.');

            return;
        }

        // Validate all rows
        foreach ($students as $student) {
            $data = $this->gradeData[$student->id] ?? [];
            Validator::make($data, [
                'tugas' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'uts' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'uas' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'tp_highest' => ['nullable', 'string', 'max:1000'],
                'tp_lowest' => ['nullable', 'string', 'max:1000'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ])->validate();
        }

        DB::transaction(function () use ($students, $schedule, $schoolId) {
            foreach ($students as $student) {
                $data = $this->gradeData[$student->id] ?? [];
                $tugas = (int) ($data['tugas'] ?? 0);
                $uts = (int) ($data['uts'] ?? 0);
                $uas = (int) ($data['uas'] ?? 0);

                $finalScore = $this->calculateFinalScore($tugas, $uts, $uas);
                $letter = $this->calculateGradeLetter($finalScore);

                Grade::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'academic_year_id' => $this->activeYearId,
                        'classroom_id' => $schedule->classroom_id,
                        'subject_id' => $schedule->subject_id,
                        'student_id' => $student->id,
                    ],
                    [
                        'tugas' => $tugas,
                        'uts' => $uts,
                        'uas' => $uas,
                        'final_score' => $finalScore,
                        'grade_letter' => $letter,
                        'tp_highest' => ! empty($data['tp_highest']) ? $data['tp_highest'] : null,
                        'tp_lowest' => ! empty($data['tp_lowest']) ? $data['tp_lowest'] : null,
                        'notes' => ! empty($data['notes']) ? $data['notes'] : null,
                        'is_locked' => $this->isLocked,
                    ]
                );

                $this->gradeData[$student->id]['final_score'] = $finalScore;
                $this->gradeData[$student->id]['grade_letter'] = $letter;
                $this->gradeData[$student->id]['is_saved'] = true;
            }
        });

        session()->flash('grade_success', '✓ Berhasil! Semua nilai siswa ('.$students->count().' siswa) telah tersimpan permanen.');
    }

    public function saveGrade(int|string $studentId): void
    {
        if ($this->isLocked) {
            session()->flash('grade_error', 'Nilai sedang dikunci. Buka kunci terlebih dahulu.');

            return;
        }

        if (! $this->selectedScheduleId) {
            return;
        }

        $schedule = $this->accessibleScheduleQuery()->whereKey($this->selectedScheduleId)->first();
        if (! $schedule) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $student = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', 'Aktif')
            ->whereKey($studentId)
            ->first();
        if (! $student) {
            abort(403);
        }

        $data = $this->gradeData[$studentId] ?? [];

        Validator::make($data, [
            'tugas' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uas' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tp_highest' => ['nullable', 'string', 'max:1000'],
            'tp_lowest' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $tugas = (int) ($data['tugas'] ?? 0);
        $uts = (int) ($data['uts'] ?? 0);
        $uas = (int) ($data['uas'] ?? 0);

        $finalScore = $this->calculateFinalScore($tugas, $uts, $uas);
        $letter = $this->calculateGradeLetter($finalScore);

        Grade::updateOrCreate(
            [
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYearId,
                'classroom_id' => $schedule->classroom_id,
                'subject_id' => $schedule->subject_id,
                'student_id' => $studentId,
            ],
            [
                'tugas' => $tugas,
                'uts' => $uts,
                'uas' => $uas,
                'final_score' => $finalScore,
                'grade_letter' => $letter,
                'tp_highest' => ! empty($data['tp_highest']) ? $data['tp_highest'] : null,
                'tp_lowest' => ! empty($data['tp_lowest']) ? $data['tp_lowest'] : null,
                'notes' => ! empty($data['notes']) ? $data['notes'] : null,
                'is_locked' => $this->isLocked,
            ]
        );

        $this->gradeData[$studentId]['final_score'] = $finalScore;
        $this->gradeData[$studentId]['grade_letter'] = $letter;
        $this->gradeData[$studentId]['is_saved'] = true;

        session()->flash('success_'.$studentId, "Tersimpan! Skor: $finalScore ($letter)");
        session()->flash('grade_success', "✓ Nilai untuk {$student->name} berhasil disimpan permanen: Skor $finalScore ($letter).");
    }

    public function toggleLockGrades(): void
    {
        if (! $this->selectedScheduleId) {
            return;
        }

        $schedule = $this->accessibleScheduleQuery()->whereKey($this->selectedScheduleId)->first();
        if (! $schedule) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();
        $this->isLocked = ! $this->isLocked;

        Grade::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('subject_id', $schedule->subject_id)
            ->update(['is_locked' => $this->isLocked]);

        if ($this->isLocked) {
            session()->flash('grade_success', '🔒 Nilai kelas berhasil dikunci. Formulir nilai kini dalam mode aman (read-only).');
        } else {
            session()->flash('grade_success', '🔓 Kunci nilai berhasil dibuka. Anda dapat melakukan pengeditan dan penyimpanan kembali.');
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = $this->activeYearId
            ? AcademicYear::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->activeYearId)
                ->first()
            : null;

        $selectedSchedule = $this->selectedScheduleId
            ? $this->accessibleScheduleQuery()->with(['classroom', 'subject'])->whereKey($this->selectedScheduleId)->first()
            : null;

        $students = collect();
        if ($selectedSchedule) {
            $students = Student::where('school_id', $schoolId)
                ->where('classroom_id', $selectedSchedule->classroom_id)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get();
        }

        // Calculate average class score for summary header
        $averageScore = 0;
        if (! empty($this->gradeData) && count($this->gradeData) > 0) {
            $total = 0;
            foreach ($this->gradeData as $row) {
                $total += (float) ($row['final_score'] ?? 0);
            }
            $averageScore = round($total / count($this->gradeData), 1);
        }

        return view('livewire.teacher.grades', [
            'activeYear' => $activeYear,
            'selectedSchedule' => $selectedSchedule,
            'students' => $students,
            'schedules' => $this->schedules,
            'averageScore' => $averageScore,
        ]);
    }

    private function calculateFinalScore(int $tugas, int $uts, int $uas): float
    {
        return round((($tugas * $this->weightTugas) + ($uts * $this->weightUts) + ($uas * $this->weightUas)) / 100, 2);
    }

    private function calculateGradeLetter(float $finalScore): string
    {
        if ($finalScore >= $this->minScoreA) {
            return 'A';
        }
        if ($finalScore >= $this->minScoreB) {
            return 'B';
        }
        if ($finalScore >= $this->minScoreC) {
            return 'C';
        }
        if ($finalScore >= $this->minScoreD) {
            return 'D';
        }

        return 'E';
    }

    /** @return Builder<Schedule> */
    private function accessibleScheduleQuery(): Builder
    {
        return Schedule::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYearId);
    }
}
