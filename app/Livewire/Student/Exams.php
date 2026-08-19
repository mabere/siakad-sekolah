<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Question;
use App\Models\Student;
use App\Support\CurrentSchool;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.student')]
class Exams extends Component
{
    public string $activeTab = 'available'; // 'available', 'history'

    #[Locked]
    public ?int $activeYearId = null;

    public ?Student $student = null;

    public ?AcademicYear $activeYear = null;

    // CBT Interface State
    public bool $isExamActive = false;

    #[Locked]
    public ?int $activeExamId = null;

    #[Locked]
    public ?int $activeSubmissionId = null;

    public ?Exam $activeExam = null;

    /** @var array<int, array<string, mixed>> */
    public array $questionsList = [];

    /** @var array<int|string, string> */
    public array $answers = []; // question_id => answer_key / text

    /** @var array<int|string, bool> */
    public array $doubtfuls = []; // question_id => true/false

    public int $currentIndex = 0;

    public int $remainingSeconds = 0;

    public bool $isFinished = false;

    public float $finalScore = 0.0;

    public int $totalCorrect = 0;

    public int $totalQuestions = 0;

    public int $totalPgCount = 0;

    public int $totalEssayCount = 0;

    // View Submission Detail State
    public ?ExamSubmission $selectedSubmission = null;

    public bool $showDetailModal = false;

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $this->activeYear?->id;

        $this->student = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['available', 'history'])) {
            $this->activeTab = $tab;
        }
    }

    public function startExam(int $examId): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        if (! $this->student || ! $this->student->classroom_id) {
            session()->flash('error', 'Data siswa atau kelas belum terkonfigurasi.');

            return;
        }

        $exam = $this->accessibleExamQuery()
            ->with(['subject', 'teacher'])
            ->whereKey($examId)
            ->first();

        if (! $exam || ! $exam->question_bank_id) {
            session()->flash('error', 'Ujian CBT tidak ditemukan atau belum aktif.');

            return;
        }

        // Always scope the attempt to the active school, year, exam, and student.
        $existingSub = ExamSubmission::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('exam_id', $exam->id)
            ->where('student_id', $this->student->id)
            ->first();

        if ($existingSub?->status === 'Selesai') {
            $this->viewSubmissionDetail($existingSub->id);

            return;
        }

        if (! $existingSub && ! $this->examIsOpen($exam)) {
            session()->flash('error', 'Ujian CBT belum dibuka atau masa pengerjaannya telah berakhir.');

            return;
        }

        if (! Question::query()->where('question_bank_id', $exam->question_bank_id)->exists()) {
            session()->flash('error', 'Bank soal untuk ujian ini masih kosong.');

            return;
        }

        $submission = DB::transaction(function () use ($exam, $schoolId): ExamSubmission {
            $submission = ExamSubmission::where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('exam_id', $exam->id)
                ->where('student_id', $this->student?->id)
                ->lockForUpdate()
                ->first();

            if ($submission) {
                if (! $submission->started_at) {
                    $submission->started_at = now();
                    $submission->save();
                }

                return $submission;
            }

            return ExamSubmission::create([
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYearId,
                'exam_id' => $exam->id,
                'student_id' => $this->student?->id,
                'answers' => [],
                'essay_scores' => [],
                'question_order' => null,
                'started_at' => now(),
                'status' => 'Proses',
            ]);
        });

        if ($submission->status !== 'Proses') {
            $this->viewSubmissionDetail($submission->id);

            return;
        }

        $questions = $this->loadQuestions($exam, $submission->question_order);
        if ($questions === []) {
            session()->flash('error', 'Bank soal untuk ujian ini masih kosong.');

            return;
        }

        if (! $submission->question_order) {
            $submission->question_order = array_column($questions, 'id');
            $submission->save();
        }

        $this->activeExam = $exam;
        $this->activeExamId = $exam->id;
        $this->activeSubmissionId = $submission->id;
        $this->questionsList = $questions;
        $this->totalQuestions = count($this->questionsList);

        $this->totalPgCount = 0;
        $this->totalEssayCount = 0;
        foreach ($this->questionsList as $q) {
            $t = strtolower($q['type'] ?? 'pg');
            if ($t === 'pg' || $t === 'pilihan_ganda') {
                $this->totalPgCount++;
            } else {
                $this->totalEssayCount++;
            }
        }

        $this->remainingSeconds = $this->remainingSeconds($exam, $submission);
        $this->currentIndex = 0;
        $this->answers = is_array($submission->answers) ? $submission->answers : [];
        $this->doubtfuls = [];
        $this->isExamActive = true;
        $this->isFinished = false;

        if ($this->remainingSeconds <= 0) {
            $this->submitExam();
        }
    }

    public function selectAnswer(int $questionId, string $answerKey): void
    {
        $question = $this->activeQuestion($questionId);
        if (! $question) {
            return;
        }

        if ($this->isChoiceQuestion($question)) {
            $options = is_array($question->options) ? $question->options : [];
            $answerKey = (string) collect(array_keys($options))
                ->first(fn ($key): bool => strcasecmp((string) $key, $answerKey) === 0);

            if ($answerKey === '') {
                return;
            }
        } elseif (mb_strlen($answerKey) > 10000) {
            return;
        }

        $this->answers[$questionId] = $answerKey;
    }

    public function toggleDoubtful(int $questionId): void
    {
        if (! $this->activeQuestion($questionId)) {
            return;
        }

        $this->doubtfuls[$questionId] = ! ($this->doubtfuls[$questionId] ?? false);
    }

    public function goToQuestion(int $index): void
    {
        if ($index >= 0 && $index < count($this->questionsList)) {
            $this->currentIndex = $index;
        }
    }

    public function nextQuestion(): void
    {
        if ($this->currentIndex < count($this->questionsList) - 1) {
            $this->currentIndex++;
        }
    }

    public function prevQuestion(): void
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function submitExam(): void
    {
        if (! $this->isExamActive || ! $this->activeExamId || ! $this->activeSubmissionId || ! $this->student) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $result = DB::transaction(function () use ($schoolId): ?array {
            $exam = $this->accessibleExamQuery()
                ->whereKey($this->activeExamId)
                ->first();
            $submission = ExamSubmission::where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('exam_id', $this->activeExamId)
                ->where('student_id', $this->student?->id)
                ->whereKey($this->activeSubmissionId)
                ->lockForUpdate()
                ->first();

            if (! $exam || ! $submission) {
                return null;
            }

            if ($submission->status === 'Selesai') {
                return [
                    'score' => (float) $submission->score,
                    'total_correct' => (int) $submission->total_correct,
                    'total_questions' => (int) $submission->total_questions,
                ];
            }

            $answers = $this->sanitizeAnswers($exam, $this->answers);
            $score = $this->scoreAnswers($exam, $answers);
            $submittedAt = now();
            $deadline = $this->examDeadline($exam, $submission);
            if ($submittedAt->greaterThan($deadline)) {
                // The attempt is finalized using the last server-known answers.
                $submittedAt = $deadline;
            }

            $submission->fill([
                'answers' => $answers,
                'essay_scores' => [],
                'score' => $score['score'],
                'total_correct' => $score['total_correct'],
                'total_questions' => $score['total_questions'],
                'submitted_at' => $submittedAt,
                'status' => 'Selesai',
            ])->save();

            return $score;
        });

        if ($result === null) {
            session()->flash('error', 'Attempt ujian tidak lagi tersedia.');
            $this->isExamActive = false;

            return;
        }

        $this->totalCorrect = $result['total_correct'];
        $this->totalQuestions = $result['total_questions'];
        $this->finalScore = $result['score'];
        $this->isExamActive = false;
        $this->isFinished = true;
    }

    public function viewSubmissionDetail(int $submissionId): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->selectedSubmission = ExamSubmission::with(['exam.subject', 'exam.teacher', 'exam.questionBank.questions'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->whereKey($submissionId)
            ->where('student_id', $this->student?->id)
            ->where('status', 'Selesai')
            ->whereHas('exam', fn (Builder $query): Builder => $query
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId))
            ->first();

        if ($this->selectedSubmission) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedSubmission = null;
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $availableExams = collect();
        $submissionsList = collect();
        $submittedExamIds = [];
        $inProgressExamIds = [];

        if ($this->student && $this->student->classroom_id && $this->activeYearId) {
            $submissionsList = ExamSubmission::with(['exam.subject', 'exam.teacher'])
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('student_id', $this->student->id)
                ->where('status', 'Selesai')
                ->orderBy('submitted_at', 'desc')
                ->get();

            $submittedExamIds = $submissionsList->pluck('exam_id')->toArray();
            $inProgressExamIds = ExamSubmission::query()
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('student_id', $this->student->id)
                ->where('status', 'Proses')
                ->pluck('exam_id')
                ->toArray();

            $availableExams = $this->accessibleExamQuery()
                ->with(['subject', 'teacher', 'questionBank'])
                ->where(function (Builder $query) use ($schoolId): void {
                    $query
                        ->where(function (Builder $openQuery): void {
                            $openQuery->whereNull('start_time')->orWhere('start_time', '<=', now());
                        })
                        ->where(function (Builder $openQuery): void {
                            $openQuery->whereNull('end_time')->orWhere('end_time', '>', now());
                        })
                        ->orWhereHas('submissions', function (Builder $progressQuery) use ($schoolId): void {
                            $progressQuery
                                ->where('school_id', $schoolId)
                                ->where('academic_year_id', $this->activeYearId)
                                ->where('student_id', $this->student->id)
                                ->where('status', 'Proses');
                        });
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('livewire.student.exams', [
            'availableExams' => $availableExams,
            'submissionsList' => $submissionsList,
            'submittedExamIds' => $submittedExamIds,
            'inProgressExamIds' => $inProgressExamIds,
        ]);
    }

    /** @return Builder<Exam> */
    private function accessibleExamQuery(): Builder
    {
        return Exam::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('classroom_id', $this->student?->classroom_id)
            ->where('status', 'Aktif')
            ->whereHas('questionBank', function (Builder $query): void {
                $query->where('school_id', app(CurrentSchool::class)->id())
                    ->where('academic_year_id', $this->activeYearId);
            });
    }

    private function examIsOpen(Exam $exam): bool
    {
        $now = now();

        return (! $exam->start_time || $now->greaterThanOrEqualTo($exam->start_time))
            && (! $exam->end_time || $now->lessThan($exam->end_time));
    }

    /**
     * @param  array<int, mixed>|null  $questionOrder
     * @return array<int, array<string, mixed>>
     */
    private function loadQuestions(Exam $exam, ?array $questionOrder): array
    {
        $questions = Question::query()
            ->where('question_bank_id', $exam->question_bank_id)
            ->orderBy('id')
            ->get(['id', 'type', 'question_text', 'options', 'score_weight']);

        if ($questions->isEmpty()) {
            return [];
        }

        if ($questionOrder) {
            $byId = $questions->keyBy('id');
            $ordered = collect($questionOrder)
                ->map(fn ($id): ?Question => $byId->get((int) $id))
                ->filter()
                ->values();

            foreach ($questions as $question) {
                if (! $ordered->contains('id', $question->id)) {
                    $ordered->push($question);
                }
            }
            $questions = $ordered;
        } elseif ($exam->randomize_questions) {
            $questions = $questions->shuffle()->values();
        }

        return $questions
            ->map(fn (Question $question): array => $this->publicQuestion($question))
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function publicQuestion(Question $question): array
    {
        return [
            'id' => $question->id,
            'type' => $question->type,
            'question_text' => $question->question_text,
            'options' => is_array($question->options) ? $question->options : [],
            'score_weight' => (int) $question->score_weight,
        ];
    }

    private function activeQuestion(int $questionId): ?Question
    {
        if (! $this->isExamActive || ! $this->activeExamId || ! $this->student) {
            return null;
        }

        $exam = $this->accessibleExamQuery()->whereKey($this->activeExamId)->first();
        if (! $exam) {
            return null;
        }

        return Question::query()
            ->whereKey($questionId)
            ->where('question_bank_id', $exam->question_bank_id)
            ->first();
    }

    /** @param array<int|string, mixed> $answers
     * @return array<int, string>
     */
    private function sanitizeAnswers(Exam $exam, array $answers): array
    {
        $questions = Question::query()
            ->where('question_bank_id', $exam->question_bank_id)
            ->get(['id', 'type', 'options']);
        $sanitized = [];

        foreach ($questions as $question) {
            $rawAnswer = $answers[$question->id] ?? $answers[(string) $question->id] ?? null;
            if (! is_string($rawAnswer) || $rawAnswer === '') {
                continue;
            }

            if ($this->isChoiceQuestion($question)) {
                $options = is_array($question->options) ? $question->options : [];
                $validKey = collect(array_keys($options))
                    ->first(fn ($key): bool => strcasecmp((string) $key, $rawAnswer) === 0);
                if ($validKey === null) {
                    continue;
                }
                $sanitized[$question->id] = (string) $validKey;

                continue;
            }

            $sanitized[$question->id] = mb_substr($rawAnswer, 0, 10000);
        }

        return $sanitized;
    }

    /** @param array<int, string> $answers
     * @return array{score: float, total_correct: int, total_questions: int}
     */
    private function scoreAnswers(Exam $exam, array $answers): array
    {
        $questions = Question::query()
            ->where('question_bank_id', $exam->question_bank_id)
            ->get(['id', 'type', 'correct_answer', 'score_weight']);
        $pgQuestions = $questions->filter(fn (Question $question): bool => $this->isChoiceQuestion($question));
        $essayPortion = $questions
            ->reject(fn (Question $question): bool => $pgQuestions->contains('id', $question->id))
            ->sum(fn (Question $question): float => (float) ($question->score_weight ?: 1));
        $weightPerPg = $pgQuestions->isNotEmpty()
            ? max(0.0, 100.0 - $essayPortion) / $pgQuestions->count()
            : 0.0;
        $correct = 0;

        foreach ($pgQuestions as $question) {
            $answer = $answers[$question->id] ?? null;
            if ($answer !== null && strcasecmp((string) $answer, (string) $question->correct_answer) === 0) {
                $correct++;
            }
        }

        return [
            'score' => round($correct * $weightPerPg, 1),
            'total_correct' => $correct,
            'total_questions' => $questions->count(),
        ];
    }

    private function examDeadline(Exam $exam, ExamSubmission $submission): CarbonInterface
    {
        $startedAt = $submission->started_at?->copy() ?? now();
        $deadline = $startedAt->addMinutes(max(1, (int) ($exam->duration_minutes ?? 60)));

        if ($exam->end_time && $exam->end_time->lessThan($deadline)) {
            return $exam->end_time->copy();
        }

        return $deadline;
    }

    private function remainingSeconds(Exam $exam, ExamSubmission $submission): int
    {
        return max(0, $this->examDeadline($exam, $submission)->getTimestamp() - now()->getTimestamp());
    }

    private function isChoiceQuestion(Question $question): bool
    {
        return in_array(strtolower((string) $question->getRawOriginal('type')), ['pg', 'pilihan_ganda'], true);
    }
}
