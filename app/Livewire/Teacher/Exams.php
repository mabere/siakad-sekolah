<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.teacher')]
class Exams extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'question_banks'; // 'question_banks', 'questions_editor', 'exams_list', 'submissions'

    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    // --- Tab 1: Bank Soal State ---
    public bool $showBankModal = false;

    public ?int $editingBankId = null;

    public string $bankSubjectId = '';

    public string $bankTitle = '';

    public string $bankCode = '';

    public string $bankGradeLevel = '10';

    public string $bankDescription = '';

    // --- Tab 2: Questions Editor State ---
    public ?int $selectedBankId = null;

    public bool $showQuestionModal = false;

    public ?int $editingQuestionId = null;

    public string $questionType = 'pg'; // pg, essay

    public string $questionText = '';

    public string $optionA = '';

    public string $optionB = '';

    public string $optionC = '';

    public string $optionD = '';

    public string $optionE = '';

    public string $correctAnswer = 'a';

    public int $scoreWeight = 1;

    // --- Tab 3: Asesmen Ujian CBT State ---
    public bool $showExamModal = false;

    public ?int $editingExamId = null;

    public string $examTitle = '';

    public string $examSubjectId = '';

    public string $examClassroomId = '';

    public string $examBankId = '';

    public int $examDurationMinutes = 60;

    public string $examStartTime = '';

    public string $examEndTime = '';

    public bool $examRandomize = true;

    public string $examStatus = 'Draft';

    // --- Tab 4: Koreksi & Penilaian CBT State ---
    public bool $showCorrectionModal = false;

    public ?int $editingSubmissionId = null;

    public ?ExamSubmission $editingSubmission = null;

    /** @var array<int, float> */
    public array $essayScores = []; // question_id => float_score_earned

    public float $calculatedPgEarned = 0.0;

    public float $calculatedEssayEarned = 0.0;

    public float $calculatedPgWeightPerQ = 0.0;

    public float $calculatedPgPortionTotal = 0.0;

    public float $calculatedEssayPortionTotal = 0.0;

    public float $calculatedRawTotal = 0.0;

    public float $calculatedPercentageScore = 0.0;

    public float $correctedScore = 0.0;

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $activeYear?->id;

        $teacher = Teacher::where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        $this->teacherId = $teacher?->id;

        // abort_if(! $this->teacherId, 403);

        $this->examStartTime = now()->addHour()->format('Y-m-d\TH:i');
        $this->examEndTime = now()->addDays(2)->format('Y-m-d\TH:i');
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['question_banks', 'questions_editor', 'exams_list', 'submissions'])) {
            $this->activeTab = $tab;
        }
    }

    // --- Tab 1: Bank Soal Methods ---
    public function openBankModal(?int $bankId = null): void
    {
        $this->resetValidation();

        $subjects = $this->accessibleSubjectQuery()->orderBy('name')->get();

        if ($bankId) {
            $bank = $this->accessibleQuestionBanks()->find($bankId);
            if (! $bank) {
                return;
            }

            $this->editingBankId = $bank->id;
            $this->bankSubjectId = (string) $bank->subject_id;
            $this->bankTitle = $bank->title;
            $this->bankCode = $bank->code ?? '';
            $this->bankGradeLevel = $bank->grade_level ?? '10';
            $this->bankDescription = $bank->description ?? '';
        } else {
            $this->editingBankId = null;
            $this->bankSubjectId = ! $subjects->isEmpty() ? (string) $subjects->first()->id : '';
            $this->bankTitle = '';
            $this->bankCode = 'BS-'.strtoupper(substr(md5(microtime()), 0, 5));
            $this->bankGradeLevel = '10';
            $this->bankDescription = '';
        }

        $this->showBankModal = true;
    }

    public function closeBankModal(): void
    {
        $this->showBankModal = false;
        $this->editingBankId = null;
    }

    public function saveBank(): void
    {
        $validated = Validator::make([
            'subject_id' => $this->bankSubjectId,
            'title' => $this->bankTitle,
            'code' => $this->bankCode,
            'grade_level' => $this->bankGradeLevel,
            'description' => $this->bankDescription,
        ], [
            'subject_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'grade_level' => ['required', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $subject = $this->accessibleSubjectQuery()->whereKey($validated['subject_id'])->first();
        if (! $subject) {
            $this->addError('bankSubjectId', 'Mata pelajaran tidak valid untuk sekolah aktif.');

            return;
        }

        $bank = $this->editingBankId
            ? $this->accessibleQuestionBanks()->find($this->editingBankId)
            : new QuestionBank;

        if (! $bank) {
            abort(403, 'Bank soal tidak dapat diakses oleh guru ini.');
        }

        $bank->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'teacher_id' => $this->teacherId,
            'subject_id' => $subject->id,
            'title' => trim($validated['title']),
            'code' => ! empty($validated['code']) ? trim($validated['code']) : null,
            'grade_level' => $validated['grade_level'],
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
        ]);
        $bank->save();

        session()->flash('bank_success', 'Paket Bank Soal berhasil disimpan.');
        $this->closeBankModal();

        $this->selectedBankId = $bank->id;
    }

    public function deleteBank(int $bankId): void
    {
        $bank = $this->accessibleQuestionBanks()->find($bankId);
        if ($bank) {
            $bank->delete();
            session()->flash('bank_success', 'Paket Bank Soal dihapus.');

            if ($this->selectedBankId === $bankId) {
                $this->selectedBankId = null;
            }
        }
    }

    public function selectBankForQuestions(int $bankId): void
    {
        if (! $this->accessibleQuestionBanks()->whereKey($bankId)->exists()) {
            abort(403, 'Bank soal tidak dapat diakses oleh guru ini.');
        }

        $this->selectedBankId = $bankId;
        $this->activeTab = 'questions_editor';
    }

    // --- Tab 2: Question Item Methods ---
    public function openQuestionModal(?int $questionId = null): void
    {
        $this->resetValidation();

        if ($questionId) {
            $q = $this->accessibleQuestions()->find($questionId);
            if (! $q) {
                return;
            }

            $this->editingQuestionId = $q->id;
            $this->questionType = $q->type;
            $this->questionText = $q->question_text;
            $opts = $q->options ?? [];
            $this->optionA = $opts['a'] ?? '';
            $this->optionB = $opts['b'] ?? '';
            $this->optionC = $opts['c'] ?? '';
            $this->optionD = $opts['d'] ?? '';
            $this->optionE = $opts['e'] ?? '';
            $this->correctAnswer = $q->correct_answer ?? 'a';
            $this->scoreWeight = $q->score_weight ?? 1;
        } else {
            $this->editingQuestionId = null;
            $this->questionType = 'pg';
            $this->questionText = '';
            $this->optionA = '';
            $this->optionB = '';
            $this->optionC = '';
            $this->optionD = '';
            $this->optionE = '';
            $this->correctAnswer = 'a';
            $this->scoreWeight = 1;
        }

        $this->showQuestionModal = true;
    }

    public function closeQuestionModal(): void
    {
        $this->showQuestionModal = false;
        $this->editingQuestionId = null;
    }

    public function saveQuestion(): void
    {
        if (! $this->selectedBankId) {
            session()->flash('question_error', 'Pilih Paket Bank Soal terlebih dahulu.');

            return;
        }

        if (! $this->accessibleQuestionBanks()->whereKey($this->selectedBankId)->exists()) {
            abort(403, 'Bank soal tidak dapat diakses oleh guru ini.');
        }

        $rules = [
            'question_text' => ['required', 'string'],
            'type' => ['required', 'string', 'in:pg,essay'],
            'score_weight' => ['required', 'integer', 'min:1'],
        ];

        if ($this->questionType === 'pg') {
            $rules['optionA'] = ['required', 'string'];
            $rules['optionB'] = ['required', 'string'];
            $rules['optionC'] = ['required', 'string'];
            $rules['optionD'] = ['required', 'string'];
            $rules['correctAnswer'] = ['required', 'string', 'in:a,b,c,d,e'];
        }

        Validator::make([
            'question_text' => $this->questionText,
            'type' => $this->questionType,
            'score_weight' => $this->scoreWeight,
            'optionA' => $this->optionA,
            'optionB' => $this->optionB,
            'optionC' => $this->optionC,
            'optionD' => $this->optionD,
            'correctAnswer' => $this->correctAnswer,
        ], $rules)->validate();

        $options = null;
        if ($this->questionType === 'pg') {
            $options = [
                'a' => trim($this->optionA),
                'b' => trim($this->optionB),
                'c' => trim($this->optionC),
                'd' => trim($this->optionD),
            ];
            if (! empty($this->optionE)) {
                $options['e'] = trim($this->optionE);
            }
        }

        $question = $this->editingQuestionId
            ? $this->accessibleQuestions()->find($this->editingQuestionId)
            : new Question;

        if (! $question) {
            abort(403, 'Butir soal tidak dapat diakses oleh guru ini.');
        }

        $question->fill([
            'question_bank_id' => $this->selectedBankId,
            'type' => $this->questionType,
            'question_text' => trim($this->questionText),
            'options' => $options,
            'correct_answer' => $this->questionType === 'pg' ? $this->correctAnswer : trim($this->correctAnswer),
            'score_weight' => $this->scoreWeight,
        ]);
        $question->save();

        session()->flash('question_success', 'Butir Soal berhasil disimpan.');
        $this->closeQuestionModal();
    }

    public function deleteQuestion(int $questionId): void
    {
        $q = $this->accessibleQuestions()->find($questionId);
        if ($q) {
            $q->delete();
            session()->flash('question_success', 'Butir soal berhasil dihapus.');
        }
    }

    // --- Tab 3: Asesmen Ujian CBT Methods ---
    public function openExamModal(?int $examId = null): void
    {
        $this->resetValidation();

        $schoolId = app(CurrentSchool::class)->id();
        $subjects = $this->accessibleSubjectQuery()->orderBy('name')->get();
        $classrooms = $this->accessibleClassroomQuery()->orderBy('grade_level')->orderBy('name')->get();
        $banks = $this->accessibleQuestionBanks()->with('subject')->orderBy('title')->get();

        if ($examId) {
            $ex = $this->accessibleExams()->find($examId);
            if (! $ex) {
                return;
            }

            $this->editingExamId = $ex->id;
            $this->examTitle = $ex->title;
            $this->examSubjectId = (string) $ex->subject_id;
            $this->examClassroomId = (string) $ex->classroom_id;
            $this->examBankId = (string) $ex->question_bank_id;
            $this->examDurationMinutes = $ex->duration_minutes;
            $this->examStartTime = $ex->start_time ? $ex->start_time->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            $this->examEndTime = $ex->end_time ? $ex->end_time->format('Y-m-d\TH:i') : now()->addDays(1)->format('Y-m-d\TH:i');
            $this->examRandomize = $ex->randomize_questions;
            $this->examStatus = $ex->status;
        } else {
            $this->editingExamId = null;
            $this->examTitle = '';
            $this->examSubjectId = ! $subjects->isEmpty() ? (string) $subjects->first()->id : '';
            $this->examClassroomId = ! $classrooms->isEmpty() ? (string) $classrooms->first()->id : '';
            $this->examBankId = ! $banks->isEmpty() ? (string) $banks->first()->id : '';
            $this->examDurationMinutes = 60;
            $this->examStartTime = now()->addHour()->format('Y-m-d\TH:i');
            $this->examEndTime = now()->addDays(2)->format('Y-m-d\TH:i');
            $this->examRandomize = true;
            $this->examStatus = 'Draft';
        }

        $this->showExamModal = true;
    }

    public function closeExamModal(): void
    {
        $this->showExamModal = false;
        $this->editingExamId = null;
    }

    public function saveExam(): void
    {
        $validated = Validator::make([
            'title' => $this->examTitle,
            'subject_id' => $this->examSubjectId,
            'classroom_id' => $this->examClassroomId,
            'question_bank_id' => $this->examBankId,
            'duration_minutes' => $this->examDurationMinutes,
            'start_time' => $this->examStartTime,
            'end_time' => $this->examEndTime,
            'randomize_questions' => $this->examRandomize,
            'status' => $this->examStatus,
        ], [
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer'],
            'classroom_id' => ['required', 'integer'],
            'question_bank_id' => ['required', 'integer'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'randomize_questions' => ['boolean'],
            'status' => ['required', 'string', 'in:Draft,Aktif,Selesai'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $subject = $this->accessibleSubjectQuery()->whereKey($validated['subject_id'])->first();
        $classroom = $this->accessibleClassroomQuery()->whereKey($validated['classroom_id'])->first();
        $schedule = $this->accessibleScheduleQuery()
            ->where('subject_id', $validated['subject_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->first();
        $bank = $this->accessibleQuestionBanks()
            ->where('subject_id', $validated['subject_id'])
            ->whereKey($validated['question_bank_id'])
            ->first();

        if (! $subject || ! $classroom || ! $schedule || ! $bank) {
            $this->addError('examClassroomId', 'Ujian hanya dapat dibuat untuk kombinasi mata pelajaran dan rombel yang ada pada jadwal mengajar Anda.');

            return;
        }

        $exam = $this->editingExamId
            ? $this->accessibleExams()->find($this->editingExamId)
            : new Exam;

        if (! $exam) {
            abort(403, 'Ujian tidak dapat diakses oleh guru ini.');
        }

        $exam->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'teacher_id' => $this->teacherId,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'question_bank_id' => $bank->id,
            'title' => trim($validated['title']),
            'duration_minutes' => $validated['duration_minutes'],
            'start_time' => $validated['start_time'] ?: null,
            'end_time' => $validated['end_time'] ?: null,
            'randomize_questions' => $validated['randomize_questions'],
            'status' => $validated['status'],
        ]);
        $exam->save();

        session()->flash('exam_success', 'Jadwal & Pengaturan Ujian CBT berhasil disimpan.');
        $this->closeExamModal();
    }

    public function deleteExam(int $examId): void
    {
        $ex = $this->accessibleExams()->find($examId);
        if ($ex) {
            $ex->delete();
            session()->flash('exam_success', 'Jadwal Ujian berhasil dihapus.');
        }
    }

    // --- Tab 4: Koreksi & Penilaian CBT Methods ---
    public function openCorrectionModal(int $submissionId): void
    {
        $this->editingSubmission = $this->accessibleSubmissions()
            ->with(['student.classroom', 'exam.subject', 'exam.questionBank.questions'])
            ->find($submissionId);

        if (! $this->editingSubmission || ! $this->editingSubmission->exam || ! $this->editingSubmission->exam->questionBank) {
            return;
        }

        $this->editingSubmissionId = $submissionId;
        $this->essayScores = [];

        $existingEssayScores = $this->editingSubmission->essay_scores ?? [];
        $questions = $this->editingSubmission->exam->questionBank->questions;

        foreach ($questions as $q) {
            $t = strtolower($q->type ?? 'pg');
            if (! in_array((string) $t, ['pg', 'pilihan_ganda'], true)) {
                // Essay question
                $this->essayScores[$q->id] = isset($existingEssayScores[$q->id])
                    ? (float) $existingEssayScores[$q->id]
                    : 0.0;
            }
        }

        $this->recalculateCombinedScore(true);
        $this->showCorrectionModal = true;
    }

    public function updatedEssayScores(): void
    {
        $this->recalculateCombinedScore(true);
    }

    public function recalculateCombinedScore(bool $useRawDefault = false): void
    {
        if (! $this->editingSubmission || ! $this->editingSubmission->exam || ! $this->editingSubmission->exam->questionBank) {
            return;
        }

        $questions = $this->editingSubmission->exam->questionBank->questions;
        $studentAnswers = $this->editingSubmission->answers ?? [];

        $pgQuestions = [];
        $totalEssayPortion = 0.0;
        $essayEarned = 0.0;

        foreach ($questions as $q) {
            $t = strtolower($q->type ?? 'pg');
            if (in_array((string) $t, ['pg', 'pilihan_ganda'], true)) {
                $pgQuestions[] = $q;
            } else {
                $weight = (float) ($q->score_weight ?? 1);
                $totalEssayPortion += $weight;

                $scoreGiven = (float) ($this->essayScores[$q->id] ?? 0);
                $scoreGiven = min($weight, max(0, $scoreGiven));
                $essayEarned += $scoreGiven;
            }
        }

        $totalPgCount = count($pgQuestions);
        $pgPortion = max(0.0, 100.0 - $totalEssayPortion);
        $weightPerPg = $totalPgCount > 0 ? ($pgPortion / $totalPgCount) : 0.0;

        $correctPgCount = 0;
        foreach ($pgQuestions as $q) {
            $userAns = $studentAnswers[$q->id] ?? null;
            $correctAns = $q->correct_answer ?? null;
            if ($userAns !== null && strtolower(trim((string) $userAns)) === strtolower(trim((string) $correctAns))) {
                $correctPgCount++;
            }
        }

        $pgEarned = $correctPgCount * $weightPerPg;

        $this->calculatedPgEarned = round($pgEarned, 1);
        $this->calculatedEssayEarned = round($essayEarned, 1);
        $this->calculatedPgWeightPerQ = round($weightPerPg, 2);
        $this->calculatedPgPortionTotal = round($pgPortion, 1);
        $this->calculatedEssayPortionTotal = round($totalEssayPortion, 1);

        $combinedScore = round($pgEarned + $essayEarned, 1);
        $this->calculatedRawTotal = $combinedScore;
        $this->calculatedPercentageScore = $combinedScore;

        if ($useRawDefault) {
            $this->correctedScore = $combinedScore;
        }
    }

    public function useRawTotal(): void
    {
        $this->recalculateCombinedScore(false);
        $this->correctedScore = $this->calculatedRawTotal;
    }

    public function usePercentageScore(): void
    {
        $this->recalculateCombinedScore(false);
        $this->correctedScore = $this->calculatedPercentageScore;
    }

    public function closeCorrectionModal(): void
    {
        $this->showCorrectionModal = false;
        $this->editingSubmissionId = null;
        $this->editingSubmission = null;
        $this->essayScores = [];
    }

    public function saveCorrection(): void
    {
        if ($this->editingSubmissionId) {
            $submission = $this->accessibleSubmissions()->find($this->editingSubmissionId);

            if (! $submission) {
                abort(403, 'Submission ujian tidak dapat diakses oleh guru ini.');
            }

            $submission->update([
                'essay_scores' => $this->essayScores,
                'score' => $this->correctedScore,
                'status' => 'Selesai',
            ]);

            session()->flash('correction_success', 'Koreksi esai & Nilai Akhir Ujian berhasil diperbarui.');
            $this->closeCorrectionModal();
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = $this->activeYearId
            ? AcademicYear::where('school_id', $schoolId)->find($this->activeYearId)
            : null;

        $subjects = $this->accessibleSubjectQuery()->orderBy('name')->get();
        $classrooms = $this->accessibleClassroomQuery()->orderBy('grade_level')->orderBy('name')->get();

        // Question Banks
        $questionBanks = QuestionBank::with(['subject', 'teacher'])
            ->withCount('questions')
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->orderBy('id', 'desc')
            ->get();

        // Selected Bank & Questions for Tab 2
        $selectedBank = $this->selectedBankId
            ? $this->accessibleQuestionBanks()->with('subject')->find($this->selectedBankId)
            : null;
        $questions = $selectedBank ? Question::where('question_bank_id', $selectedBank->id)->orderBy('id', 'asc')->get() : collect();

        // Exams List for Tab 3
        $exams = Exam::with(['subject', 'classroom', 'questionBank'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'examsPage');

        // Submissions List for Tab 4
        $submissions = ExamSubmission::with(['student.classroom', 'exam.subject'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->whereHas('exam', function ($q) {
                $q->where('teacher_id', $this->teacherId);
            })
            ->orderBy('submitted_at', 'desc')
            ->paginate(15, ['*'], 'submissionsPage');

        return view('livewire.teacher.exams', [
            'activeYear' => $activeYear,
            'subjects' => $subjects,
            'classrooms' => $classrooms,
            'questionBanks' => $questionBanks,
            'selectedBank' => $selectedBank,
            'questions' => $questions,
            'exams' => $exams,
            'submissions' => $submissions,
        ]);
    }

    /**
     * @return Builder<QuestionBank>
     */
    private function accessibleQuestionBanks(): Builder
    {
        return QuestionBank::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId);
    }

    /**
     * @return Builder<Schedule>
     */
    private function accessibleScheduleQuery(): Builder
    {
        return Schedule::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId);
    }

    /**
     * @return Builder<Subject>
     */
    private function accessibleSubjectQuery(): Builder
    {
        return Subject::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereIn('id', $this->accessibleScheduleQuery()->select('subject_id'));
    }

    /**
     * @return Builder<Classroom>
     */
    private function accessibleClassroomQuery(): Builder
    {
        return Classroom::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->whereIn('id', $this->accessibleScheduleQuery()->select('classroom_id'));
    }

    /**
     * @return Builder<Question>
     */
    private function accessibleQuestions(): Builder
    {
        return Question::query()->whereHas('questionBank', function ($query): void {
            $query->where('school_id', app(CurrentSchool::class)->id())
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId);
        });
    }

    /**
     * @return Builder<Exam>
     */
    private function accessibleExams(): Builder
    {
        return Exam::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId);
    }

    /**
     * @return Builder<ExamSubmission>
     */
    private function accessibleSubmissions(): Builder
    {
        return ExamSubmission::query()->whereHas('exam', function ($query): void {
            $query->where('school_id', app(CurrentSchool::class)->id())
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId);
        });
    }
}
