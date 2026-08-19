<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AI\RemedialEnrichmentGenerator;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.teacher')]
class RemedialEnrichmentIndex extends Component
{
    public ?int $activeYearId = null;
    public ?int $teacherId = null;
    public ?int $schoolId = null;

    /** @var array<int, array{id: int, subject: string, classroom: string, grade_level: string, time: string}> */
    public array $schedules = [];
    public string $selectedScheduleId = '';

    /** @var array<int, array{id: int, title: string, classroom: string, subject: string}> */
    public array $availableExams = [];
    public string $selectedExamId = '';

    // Form inputs
    public string $topic = '';
    public string $targetCompetency = '';
    public string $teacherNotes = '';

    // Student classifications
    /** @var array<int, array{id: int, name: string, nis: string, score: float|int, status: string}> */
    public array $remedialStudents = [];

    /** @var array<int, array{id: int, name: string, nis: string, score: float|int, status: string}> */
    public array $enrichmentStudents = [];

    public ?array $examAnalysis = null;

    // Generated AI Package
    public ?array $package = null;
    public string $activeTab = 'remedial'; // 'remedial' | 'enrichment' | 'analysis'
    public bool $isConfigured = true;

    public function mount(): void
    {
        $school = app(CurrentSchool::class)->get();
        $this->schoolId = $school->id;

        $teacher = Teacher::query()
            ->where('school_id', $this->schoolId)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        abort_unless($teacher !== null, 403, 'Akun belum terhubung ke data guru aktif.');
        $this->teacherId = $teacher->id;

        $activeYear = AcademicYear::query()
            ->where('school_id', $this->schoolId)
            ->where('is_active', true)
            ->first();

        if (! $activeYear) {
            return;
        }

        $this->activeYearId = $activeYear->id;
        $this->isConfigured = (bool) config('services.gemini.enabled', true);

        // Load teacher schedules
        $this->schedules = Schedule::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->with(['classroom', 'subject'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Schedule $s): array => [
                'id' => $s->id,
                'subject' => $s->subject->name ?? '',
                'classroom' => trim(($s->classroom->grade_level ?? '').' '.($s->classroom->name ?? '')),
                'grade_level' => (string) ($s->classroom->grade_level ?? ''),
                'time' => $s->day_of_week.' ('.substr((string) $s->start_time, 0, 5).' - '.substr((string) $s->end_time, 0, 5).')',
            ])
            ->values()
            ->all();

        // Load available exams created by this teacher
        $this->availableExams = Exam::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->with(['classroom', 'subject'])
            ->latest()
            ->get()
            ->map(fn (Exam $e): array => [
                'id' => $e->id,
                'title' => $e->title,
                'classroom' => trim(($e->classroom->grade_level ?? '').' '.($e->classroom->name ?? '')),
                'subject' => $e->subject->name ?? '',
            ])
            ->values()
            ->all();
    }

    public function updatedSelectedScheduleId(): void
    {
        $this->selectedExamId = '';
        $this->package = null;
        $this->resetErrorBag();
        $this->analyzeClassroomData();
    }

    public function updatedSelectedExamId(): void
    {
        $this->package = null;
        $this->resetErrorBag();
        $this->analyzeExamSubmissions();
    }

    public function analyzeClassroomData(): void
    {
        $this->remedialStudents = [];
        $this->enrichmentStudents = [];
        $this->examAnalysis = null;

        if (empty($this->selectedScheduleId)) {
            return;
        }

        $schedule = Schedule::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->whereKey($this->selectedScheduleId)
            ->with(['classroom', 'subject'])
            ->first();

        if (! $schedule) {
            return;
        }

        if (empty($this->topic)) {
            $this->topic = "Materi Pokok: {$schedule->subject->name}";
        }

        // Fetch grades for this classroom & subject
        $grades = Grade::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('subject_id', $schedule->subject_id)
            ->with('student')
            ->get();

        foreach ($grades as $grade) {
            if (! $grade->student) {
                continue;
            }

            $score = (float) ($grade->final_score ?? (($grade->tugas + $grade->uts + $grade->uas) / 3));

            if ($score < 75) {
                $this->remedialStudents[] = [
                    'id' => $grade->student->id,
                    'name' => $grade->student->name,
                    'nis' => $grade->student->nis ?? '-',
                    'score' => round($score, 1),
                    'status' => 'Perlu Remedial (<75)',
                ];
            } elseif ($score >= 85) {
                $this->enrichmentStudents[] = [
                    'id' => $grade->student->id,
                    'name' => $grade->student->name,
                    'nis' => $grade->student->nis ?? '-',
                    'score' => round($score, 1),
                    'status' => 'Siap Pengayaan (≥85)',
                ];
            }
        }
    }

    public function analyzeExamSubmissions(): void
    {
        if (empty($this->selectedExamId)) {
            $this->analyzeClassroomData();

            return;
        }

        $exam = Exam::query()
            ->where('school_id', $this->schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->whereKey($this->selectedExamId)
            ->with(['classroom', 'subject', 'questionBank.questions'])
            ->first();

        if (! $exam) {
            return;
        }

        $this->topic = $exam->title;

        $submissions = ExamSubmission::query()
            ->where('school_id', $this->schoolId)
            ->where('exam_id', $exam->id)
            ->whereIn('status', ['submitted', 'Selesai'])
            ->with('student')
            ->get();

        $this->remedialStudents = [];
        $this->enrichmentStudents = [];

        $totalScore = 0;
        $passedCount = 0;

        foreach ($submissions as $sub) {
            if (! $sub->student) {
                continue;
            }

            $score = (float) $sub->score;
            $totalScore += $score;

            if ($score < 75) {
                $this->remedialStudents[] = [
                    'id' => $sub->student->id,
                    'name' => $sub->student->name,
                    'nis' => $sub->student->nis ?? '-',
                    'score' => round($score, 1),
                    'status' => 'Remedial (<75)',
                ];
            } else {
                $passedCount++;
                if ($score >= 85) {
                    $this->enrichmentStudents[] = [
                        'id' => $sub->student->id,
                        'name' => $sub->student->name,
                        'nis' => $sub->student->nis ?? '-',
                        'score' => round($score, 1),
                        'status' => 'Pengayaan (≥85)',
                    ];
                }
            }
        }

        $submissionCount = count($submissions);
        $avgScore = $submissionCount > 0 ? round($totalScore / $submissionCount, 1) : 0;
        $passRate = $submissionCount > 0 ? round(($passedCount / $submissionCount) * 100, 1) : 0;

        $this->examAnalysis = [
            'exam_title' => $exam->title,
            'total_submissions' => $submissionCount,
            'avg_score' => $avgScore,
            'pass_rate' => $passRate,
            'remedial_count' => count($this->remedialStudents),
            'enrichment_count' => count($this->enrichmentStudents),
        ];
    }

    public function generatePackage(): void
    {
        $this->validate([
            'topic' => ['required', 'string', 'max:500'],
            'targetCompetency' => ['nullable', 'string', 'max:1000'],
            'teacherNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->resetErrorBag('generation');

        $schedule = null;
        if (! empty($this->selectedScheduleId)) {
            $schedule = Schedule::query()
                ->where('school_id', $this->schoolId)
                ->where('teacher_id', $this->teacherId)
                ->whereKey($this->selectedScheduleId)
                ->with(['classroom', 'subject'])
                ->first();
        }

        $context = [
            'topic' => $this->topic,
            'target_competency' => $this->targetCompetency ?: 'Penguasaan konsep esensial dan kemampuan evaluasi tingkat tinggi',
            'teacher_observations' => $this->teacherNotes,
            'classroom_context' => [
                'subject' => $schedule->subject->name ?? 'Mata Pelajaran',
                'grade_level' => $schedule->classroom->grade_level ?? '10',
                'classroom' => $schedule->classroom->name ?? 'Umum',
            ],
            'assessment_metrics' => [
                'remedial_student_count' => count($this->remedialStudents),
                'enrichment_student_count' => count($this->enrichmentStudents),
                'average_score' => $this->examAnalysis['avg_score'] ?? 75,
                'pass_rate' => ($this->examAnalysis['pass_rate'] ?? 70).'%',
            ],
        ];

        try {
            $generator = app(RemedialEnrichmentGenerator::class);
        } catch (Throwable) {
            $generator = new RemedialEnrichmentGenerator();
        }

        try {
            $this->package = $generator->generate($context);
            $this->activeTab = 'remedial';

            // Store in session for print and word export
            session()->put('remedial_enrichment_active_package', [
                'package' => $this->package,
                'topic' => $this->topic,
                'subject' => $schedule->subject->name ?? 'Mata Pelajaran',
                'classroom' => trim(($schedule->classroom->grade_level ?? '').' '.($schedule->classroom->name ?? '')),
                'remedial_students' => $this->remedialStudents,
                'enrichment_students' => $this->enrichmentStudents,
            ]);

            session()->flash('generation_success', 'Paket Lembar Kerja Remedial & Pengayaan AI berhasil disusun!');
        } catch (Throwable $e) {
            Log::warning('AI Remedial Generation Error: '.$e->getMessage(), ['exception' => $e]);
            $this->addError('generation', 'Gagal menyusun paket remedial & pengayaan: '.$e->getMessage());
        }
    }

    public function exportRemedialToCbt(): void
    {
        if (! $this->package || empty($this->package['remedial_package']['practice_items'])) {
            $this->addError('cbt_export', 'Belum ada butir soal remedial yang dapat diekspor.');

            return;
        }

        $schedule = null;
        if (! empty($this->selectedScheduleId)) {
            $schedule = Schedule::query()->whereKey($this->selectedScheduleId)->first();
        }

        DB::transaction(function () use ($schedule) {
            $subjectId = $schedule->subject_id ?? DB::table('subjects')->where('school_id', $this->schoolId)->value('id');

            $questionBank = QuestionBank::create([
                'school_id' => $this->schoolId,
                'academic_year_id' => $this->activeYearId,
                'teacher_id' => $this->teacherId,
                'subject_id' => $subjectId,
                'title' => 'Soal Remedial: '.(data_get($this->package, 'remedial_package.title') ?: $this->topic),
                'code' => 'REM-'.strtoupper(substr(uniqid(), -6)),
                'grade_level' => (string) ($schedule->classroom->grade_level ?? '10'),
                'description' => 'Paket butir soal remedial terarah hasil generate AI',
            ]);

            foreach (data_get($this->package, 'remedial_package.practice_items', []) as $item) {
                $type = data_get($item, 'type', 'pg') === 'essay' ? 'essay' : 'pg';
                $options = data_get($item, 'options');

                Question::create([
                    'question_bank_id' => $questionBank->id,
                    'type' => $type,
                    'question_text' => (string) data_get($item, 'question_text', ''),
                    'options' => is_array($options) ? $options : null,
                    'correct_answer' => (string) data_get($item, 'answer_key', 'A'),
                    'score_weight' => 20,
                ]);
            }
        });

        session()->flash('cbt_export_success', 'Butir soal remedial berhasil diekspor menjadi Bank Soal CBT baru!');
    }

    public function render(): View
    {
        return view('livewire.teacher.remedial-enrichment-index');
    }
}
