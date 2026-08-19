<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use App\Services\AI\ClassroomDifferentiationAdvisor;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.teacher')]
class ClassroomDifferentiationIndex extends Component
{
    public ?int $activeYearId = null;
    public ?int $teacherId = null;
    public ?int $schoolId = null;

    /** @var array<int, array{id: int, subject: string, classroom: string, grade_level: string, time: string}> */
    public array $schedules = [];
    public string $selectedScheduleId = '';

    // Empirical Classroom Statistics
    public ?array $classStats = null;

    // AI Generated Recommendation
    public ?array $recommendation = null;
    public string $additionalContext = '';
    public bool $isConfigured = true;

    public function mount(CurrentSchool $currentSchool): void
    {
        $this->schoolId = $currentSchool->id();
        $this->activeYearId = AcademicYear::query()
            ->where('school_id', $this->schoolId)
            ->where('is_active', true)
            ->value('id');

        $teacher = Teacher::query()
            ->where('school_id', $this->schoolId)
            ->where('user_id', auth()->id())
            ->first();

        $this->teacherId = $teacher?->id;

        $geminiConfig = config('services.gemini', []);
        $this->isConfigured = ($geminiConfig['enabled'] ?? false) === true
            && filled($geminiConfig['api_key'] ?? null);

        $this->loadSchedules();

        if (count($this->schedules) > 0) {
            $this->selectedScheduleId = (string) $this->schedules[0]['id'];
            $this->loadClassroomStats();
        }
    }

    public function updatedSelectedScheduleId(): void
    {
        $this->recommendation = null;
        $this->loadClassroomStats();
    }

    public function loadSchedules(): void
    {
        if (! $this->schoolId || ! $this->activeYearId || ! $this->teacherId) {
            $this->schedules = [];

            return;
        }

        $this->schedules = Schedule::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->with(['subject', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Schedule $s): array => [
                'id' => $s->id,
                'subject' => (string) ($s->subject?->name ?? 'Mata Pelajaran'),
                'classroom' => (string) ($s->classroom?->name ?? 'Kelas'),
                'grade_level' => (string) ($s->classroom?->grade_level ?? '10'),
                'time' => $s->day_of_week.', '.substr($s->start_time, 0, 5).' - '.substr($s->end_time, 0, 5),
            ])
            ->values()
            ->all();
    }

    public function loadClassroomStats(): void
    {
        $schedule = $this->resolveAccessibleSchedule();
        if (! $schedule || ! $schedule->classroom) {
            $this->classStats = null;

            return;
        }

        $classroom = $schedule->classroom;
        $subject = $schedule->subject;

        // 1. Total Students in Classroom
        $totalStudents = $classroom->students()->count();

        // 2. Grades Analytics for this Classroom and Subject
        $grades = Grade::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('classroom_id', $classroom->id)
            ->where('subject_id', $subject->id)
            ->get();

        $gradesCount = $grades->count();
        $avgScore = $gradesCount > 0 ? round((float) $grades->avg('final_score'), 1) : null;
        $avgTugas = $gradesCount > 0 ? round((float) $grades->avg('tugas'), 1) : null;
        $avgUts = $gradesCount > 0 ? round((float) $grades->avg('uts'), 1) : null;
        $avgUas = $gradesCount > 0 ? round((float) $grades->avg('uas'), 1) : null;

        $needSupportCount = $grades->filter(fn ($g) => ($g->final_score ?? 0) < 75)->count();
        $highAchieverCount = $grades->filter(fn ($g) => ($g->final_score ?? 0) >= 85)->count();
        $regularCount = max(0, $gradesCount - $needSupportCount - $highAchieverCount);

        // Highest and Lowest TP samples
        $tpHighestList = $grades->pluck('tp_highest')->filter()->unique()->take(3)->values()->all();
        $tpLowestList = $grades->pluck('tp_lowest')->filter()->unique()->take(3)->values()->all();

        // 3. Attendance Analytics
        $attendances = Attendance::query()
            ->where('school_id', $this->schoolId)
            ->where('classroom_id', $classroom->id)
            ->get();

        $totalAttRecords = $attendances->count();
        $hadirCount = $attendances->where('status', 'Hadir')->count();
        $sakitCount = $attendances->where('status', 'Sakit')->count();
        $izinCount = $attendances->where('status', 'Izin')->count();
        $alphaCount = $attendances->where('status', 'Alpha')->count();

        $hadirRate = $totalAttRecords > 0 ? round(($hadirCount / $totalAttRecords) * 100, 1) : 100.0;

        $this->classStats = [
            'total_students' => $totalStudents,
            'grades_recorded_count' => $gradesCount,
            'avg_final_score' => $avgScore ?? 75.0,
            'avg_tugas' => $avgTugas ?? 75.0,
            'avg_uts' => $avgUts ?? 75.0,
            'avg_uas' => $avgUas ?? 75.0,
            'need_support_count' => $needSupportCount,
            'regular_count' => $regularCount,
            'high_achiever_count' => $highAchieverCount,
            'tp_highest_samples' => $tpHighestList,
            'tp_lowest_samples' => $tpLowestList,
            'attendance_rate' => $hadirRate,
            'hadir_count' => $hadirCount,
            'sakit_count' => $sakitCount,
            'izin_count' => $izinCount,
            'alpha_count' => $alphaCount,
            'student_needs' => (string) ($classroom->student_needs ?: 'Variasi gaya belajar visual dan kinestetik.'),
            'available_facilities' => (string) ($classroom->available_facilities ?: 'Proyektor LCD, papan tulis, buku paket.'),
            'learning_environment' => (string) ($classroom->learning_environment ?: 'Ruang kelas representatif ber-AC.'),
        ];
    }

    public function generateRecommendation(ClassroomDifferentiationAdvisor $advisor): void
    {
        $this->validate([
            'selectedScheduleId' => ['required', 'string'],
        ], [
            'selectedScheduleId.required' => 'Pilih jadwal mengajar terlebih dahulu.',
        ]);

        $schedule = $this->resolveAccessibleSchedule();
        if (! $schedule) {
            $this->addError('generation', 'Jadwal mengajar tidak ditemukan atau tidak dapat diakses.');

            return;
        }

        $school = School::find($this->schoolId);
        $academicYear = AcademicYear::find($this->activeYearId);

        $gradeLevel = (string) ($schedule->classroom?->grade_level ?? '10');
        $faseStr = match(true) {
            (int) $gradeLevel >= 11 => 'Fase F (Kelas 11–12)',
            (int) $gradeLevel === 10 => 'Fase E (Kelas 10)',
            (int) $gradeLevel >= 7 => 'Fase D (Kelas 7–9)',
            default => 'Fase A-C (SD)',
        };

        $context = [
            'sekolah' => [
                'nama' => $school?->name ?? 'Sekolah',
                'jenjang' => $school?->level ?? 'SMA',
                'visi_misi' => trim(($school?->vision ?? '')."\n".($school?->mission ?? '')),
            ],
            'tahun_ajaran' => ($academicYear?->name ?? '2026/2027').' - Semester '.($academicYear?->semester ?? 'Ganjil'),
            'mata_pelajaran' => $schedule->subject?->name ?? 'Mata Pelajaran',
            'kelas' => trim($schedule->classroom?->grade_level.' '.($schedule->classroom?->name ?? '')),
            'fase' => $faseStr,
            'statistik_kelas' => $this->classStats ?? [],
            'catatan_tambahan_guru' => trim($this->additionalContext),
        ];

        try {
            $this->recommendation = $advisor->advise($context);
            session()->flash('generation_success', 'Rekomendasi diferensiasi pembelajaran kelas berhasil digenerate oleh Gemini AI!');
        } catch (Throwable $e) {
            Log::error('Gagal generate rekomendasi diferensiasi kelas', [
                'message' => $e->getMessage(),
                'schedule_id' => $schedule->id,
            ]);
            $this->addError('generation', 'Gagal memproses rekomendasi AI: '.$e->getMessage());
        }
    }

    public function applyToLearningAssistant(): void
    {
        if (! $this->recommendation) {
            return;
        }

        $contentDiff = (string) data_get($this->recommendation, 'differentiation_content.strategy', '');
        $processDiff = (string) data_get($this->recommendation, 'differentiation_process.strategy', '');
        $combinedDiff = "Diferensiasi Konten: {$contentDiff}\nDiferensiasi Proses: {$processDiff}";

        $scaffoldingNeeds = (string) data_get($this->recommendation, 'student_grouping.scaffolding_group.teacher_intervention', '');
        $combinedNeeds = "Kebutuhan Belajar Kelas: {$scaffoldingNeeds}";

        session()->put('differentiation_prefill', [
            'schedule_id' => $this->selectedScheduleId,
            'differentiation' => $combinedDiff,
            'student_needs' => $combinedNeeds,
            'learning_model' => data_get($this->recommendation, 'recommended_learning_models.0', ''),
        ]);

        $this->redirect(route('guru.learning-assistant', ['from_differentiation' => 1]));
    }

    public function render(): View
    {
        return view('livewire.teacher.classroom-differentiation-index', [
            'schedules' => $this->schedules,
            'classStats' => $this->classStats,
            'recommendation' => $this->recommendation,
        ]);
    }

    private function resolveAccessibleSchedule(): ?Schedule
    {
        if (! $this->activeYearId || ! $this->teacherId || $this->selectedScheduleId === '') {
            return null;
        }

        return Schedule::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->whereKey((int) $this->selectedScheduleId)
            ->with(['classroom.students', 'subject', 'academicYear'])
            ->first();
    }
}
