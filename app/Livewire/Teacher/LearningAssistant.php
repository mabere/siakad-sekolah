<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\LearningDraft;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Services\AI\LearningDraftGenerator;
use App\Services\Curriculum\CurriculumBank;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.teacher')]
class LearningAssistant extends Component
{
    public string $schoolName = '';

    public string $schoolLevel = '';

    public string $curriculumType = 'MERDEKA';

    public string $academicYearLabel = '';

    public ?int $activeYearId = null;

    public ?int $teacherId = null;

    public string $selectedScheduleId = '';

    public string $documentType = 'modul_ajar';

    public string $selectedP5Theme = 'Gaya Hidup Berkelanjutan';

    public int $effectiveWeeksOdd = 18;

    public int $effectiveWeeksEven = 16;

    public string $topic = '';

    public string $learningObjectives = '';

    public string $schoolVisionMission = '';

    public string $localContent = '';

    public string $studentNeeds = '';

    public string $availableFacilities = '';

    public string $additionalContext = '';

    public string $selectedLearningModel = '';

    /** @var array<int, string> */
    public array $selectedP5Dimensions = [];

    public string $detectedFase = '';

    // --- CP & ATP Kemendikdasmen Bank State ---
    public string $selectedBankTopicId = '';

    /** @var array<string, array<string, mixed>> */
    public array $availableBankTopics = [];

    // --- Parallel Class Duplication State ---
    public bool $showDuplicateModal = false;

    /** @var array<int, array<string, mixed>> */
    public array $parallelClassSchedules = [];

    /** @var array<int, int> */
    public array $selectedTargetScheduleIds = [];

    public bool $isConfigured = false;

    /** @var array<int, array<string, mixed>> */
    public array $schedules = [];

    /** @var array<string, mixed>|null */
    public ?array $draft = null;

    public ?int $savedDraftId = null;

    public ?string $savedDraftStatus = null;

    /** @var array<int, array<string, mixed>> */
    public array $recentDrafts = [];

    // --- Interactive Editing State ---
    public bool $isEditing = false;

    public string $editTitle = '';

    public string $editSummary = '';

    /** @var array<int, string> */
    public array $editP5Dimensions = [];

    public string $editLearningModel = '';

    public string $editMeaningfulUnderstanding = '';

    /** @var array<int, string> */
    public array $editInquiryQuestions = [];

    /** @var array<int, string> */
    public array $editObjectives = [];

    /** @var array<int, array<string, mixed>> */
    public array $editActivities = [];

    public string $editWorksheetTitle = '';

    public string $editWorksheetInstructions = '';

    /** @var array<int, string> */
    public array $editWorksheetTasks = [];

    public string $editDiagnostic = '';

    public string $editFormative = '';

    public string $editSummative = '';

    /** @var array<int, array<string, string>> */
    public array $editAssessmentRubric = [];

    public string $editDifferentiation = '';

    /** @var array<int, string> */
    public array $editResources = [];

    /** @var array<int, string> */
    public array $editReferences = [];

    public function mount(): void
    {
        $school = app(CurrentSchool::class)->get();
        $this->schoolName = (string) $school->name;
        $this->schoolLevel = (string) $school->level;
        $this->schoolVisionMission = $this->formatSchoolVisionMission(
            (string) ($school->vision ?? ''),
            (string) ($school->mission ?? ''),
        );
        $this->isConfigured = $this->geminiIsConfigured();

        $curriculumSetting = SystemSetting::query()
            ->where('school_id', $school->id)
            ->where('key', 'curriculum_type')
            ->first();
        if ($curriculumSetting && is_string($curriculumSetting->value)) {
            $this->curriculumType = $curriculumSetting->value;
        }

        $teacher = Teacher::query()
            ->where('school_id', $school->id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        abort_unless($teacher !== null, 403, 'Akun belum terhubung ke data guru aktif.');
        $this->teacherId = $teacher->id;

        $activeYear = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->first();

        if (! $activeYear) {
            $this->loadRecentDrafts();

            return;
        }

        $this->activeYearId = $activeYear->id;
        if (is_string($activeYear->curriculum_type) && in_array($activeYear->curriculum_type, ['MERDEKA', 'K13'], true)) {
            $this->curriculumType = $activeYear->curriculum_type;
        }
        if (trim((string) $activeYear->local_content) !== '') {
            $this->localContent = (string) $activeYear->local_content;
        }
        $this->academicYearLabel = $activeYear->name.' • '.$activeYear->semester;

        $this->schedules = $this->accessibleScheduleQuery($school->id, $activeYear->id, $teacher->id)
            ->with(['classroom', 'subject'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Schedule $schedule): array => [
                'id' => $schedule->id,
                'subject' => $schedule->subject->name,
                'classroom' => trim($schedule->classroom->grade_level.' '.$schedule->classroom->name),
                'time' => $this->formatTimeRange($schedule),
            ])
            ->values()
            ->all();

        $this->loadRecentDrafts();

        if (session()->has('differentiation_prefill')) {
            $prefill = session()->pull('differentiation_prefill');
            if (is_array($prefill)) {
                $this->selectedScheduleId = (string) ($prefill['schedule_id'] ?? '');
                $this->prefillClassContext();
                if (! empty($prefill['student_needs'])) {
                    $this->studentNeeds = (string) $prefill['student_needs'];
                }
                if (! empty($prefill['differentiation'])) {
                    $this->additionalContext = trim($this->additionalContext."\n\n[Rekomendasi Diferensiasi AI]:\n".$prefill['differentiation']);
                }
                if (! empty($prefill['learning_model'])) {
                    $this->selectedLearningModel = (string) $prefill['learning_model'];
                }
            }
        }
    }

    public function updatedSelectedScheduleId(): void
    {
        $this->draft = null;
        $this->savedDraftId = null;
        $this->savedDraftStatus = null;
        $this->resetErrorBag('generation');
        $this->prefillClassContext();
    }

    public function generate(): void
    {
        $this->draft = null;
        $this->savedDraftId = null;
        $this->savedDraftStatus = null;

        $this->validate([
            'selectedScheduleId' => ['required', 'integer'],
            'documentType' => ['required', 'in:modul_ajar,atp,prota_prosem,bahan_ajar,lkpd_bertingkat,modul_p5,asesmen_kktp,lkpd,materi_ajar,asesmen'],
            'selectedP5Theme' => ['nullable', 'string', 'max:255'],
            'effectiveWeeksOdd' => ['nullable', 'integer', 'min:1', 'max:30'],
            'effectiveWeeksEven' => ['nullable', 'integer', 'min:1', 'max:30'],
            'topic' => ['required', 'string', 'max:1500'],
            'learningObjectives' => ['required', 'string', 'max:2000'],
            'selectedLearningModel' => ['nullable', 'string', 'max:255'],
            'selectedP5Dimensions' => ['nullable', 'array'],
            'schoolVisionMission' => ['nullable', 'string', 'max:2000'],
            'localContent' => ['nullable', 'string', 'max:1000'],
            'studentNeeds' => ['nullable', 'string', 'max:2000'],
            'availableFacilities' => ['nullable', 'string', 'max:1500'],
            'additionalContext' => ['nullable', 'string', 'max:2500'],
        ]);

        $this->isConfigured = $this->geminiIsConfigured();

        if (! $this->isConfigured) {
            $this->addError('generation', 'Provider Gemini belum diaktifkan oleh administrator.');

            return;
        }

        if (! $this->activeYearId || ! $this->teacherId) {
            $this->addError('generation', 'Belum ada tahun ajaran aktif untuk konteks pembelajaran.');

            return;
        }

        $schedule = $this->resolveAccessibleSchedule();

        if (! $schedule) {
            $this->addError('selectedScheduleId', 'Jadwal tidak ditemukan dalam ruang lingkup akun guru ini.');

            return;
        }

        try {
            $this->draft = app(LearningDraftGenerator::class)->generate($this->buildContext($schedule));

            session()->flash('generation_success', 'Draf Kurikulum Merdeka ('.$this->documentLabel().') berhasil dibuat. Tinjau, sesuaikan, atau cetak resmi.');
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->addError('generation', $exception->getMessage());
        }
    }

    public function saveDraft(): void
    {
        if (! $this->draft) {
            $this->addError('generation', 'Buat atau buka draf sebelum menyimpannya.');

            return;
        }

        $schedule = $this->resolveAccessibleSchedule();
        if (! $schedule || ! $this->activeYearId || ! $this->teacherId) {
            $this->addError('generation', 'Draf tidak dapat disimpan karena konteks jadwal tidak lagi tersedia.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();
        $draftOutput = $this->draft;
        $draft = DB::transaction(function () use ($schoolId, $schedule, $draftOutput): LearningDraft {
            $latestVersion = LearningDraft::query()
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId)
                ->where('schedule_id', $schedule->id)
                ->where('document_type', $this->documentType)
                ->lockForUpdate()
                ->max('version');

            return LearningDraft::create([
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYearId,
                'teacher_id' => $this->teacherId,
                'schedule_id' => $schedule->id,
                'user_id' => auth()->id(),
                'document_type' => $this->documentType,
                'status' => LearningDraft::STATUS_DRAFT,
                'source' => 'user',
                'version' => ((int) $latestVersion) + 1,
                'provider' => 'gemini',
                'model' => (string) config('services.gemini.model', 'gemini-2.5-flash'),
                'input_context' => $this->buildContext($schedule),
                'output' => $draftOutput,
            ]);
        });

        $this->savedDraftId = $draft->id;
        $this->savedDraftStatus = $draft->status;
        $this->loadRecentDrafts();
        session()->flash('generation_success', 'Draf tersimpan sebagai versi '.$draft->version.'.');
    }

    public function approveDraft(): void
    {
        if (! $this->savedDraftId) {
            $this->addError('generation', 'Simpan draf terlebih dahulu sebelum menyetujuinya.');

            return;
        }

        $draft = $this->accessibleDraftQuery()
            ->whereKey($this->savedDraftId)
            ->where('status', LearningDraft::STATUS_DRAFT)
            ->first();

        if (! $draft) {
            $this->addError('generation', 'Draf tidak ditemukan atau sudah tidak dapat disetujui.');

            return;
        }

        $draft->forceFill([
            'status' => LearningDraft::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ])->save();

        $this->savedDraftStatus = LearningDraft::STATUS_APPROVED;
        $this->loadRecentDrafts();
        session()->flash('generation_success', 'Draf versi '.$draft->version.' telah disetujui.');
    }

    public function syncToTeachingJournal(): void
    {
        if (! $this->draft) {
            $this->addError('generation', 'Tidak ada draf yang dapat disinkronkan ke jurnal.');

            return;
        }

        $schedule = $this->resolveAccessibleSchedule();
        if (! $schedule) {
            $this->addError('generation', 'Jadwal mengajar tidak ditemukan untuk sinkronisasi jurnal.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();
        $existingCount = TeachingJournal::where('school_id', $schoolId)
            ->where('schedule_id', $schedule->id)
            ->count();

        $meetingNumber = $existingCount + 1;
        $topic = (string) data_get($this->draft, 'title', $this->topic);
        $learningModel = (string) data_get($this->draft, 'learning_model', 'Kurikulum Merdeka');

        $docType = (string) data_get($this->draft, 'document_type', $this->documentType);
        $activitiesText = '';
        $notes = '';

        if ($docType === 'modul_p5') {
            $topic = 'Projek P5: '.(string) data_get($this->draft, 'project_topic', $topic);
            $stages = (array) data_get($this->draft, 'project_stages', []);
            $activitiesText = collect($stages)->map(function ($stg) {
                $name = data_get($stg, 'stage_name', 'Tahap');
                $dur = data_get($stg, 'duration_jp', '-');
                $act = data_get($stg, 'activities', '');
                $out = data_get($stg, 'output_artifact', '');

                return "• [{$name} - {$dur}]: {$act} (Output: {$out})";
            })->implode("\n");

            $notes = 'Tema P5: '.(string) data_get($this->draft, 'p5_theme', '-')."\nLatar Belakang: ".(string) data_get($this->draft, 'project_background', '-');
        } elseif ($docType === 'atp') {
            $flows = (array) data_get($this->draft, 'atp_flow', []);
            $activitiesText = collect($flows)->map(function ($flw) {
                $seq = data_get($flw, 'sequence_number', '-');
                $ch = data_get($flw, 'chapter', '');
                $tp = data_get($flw, 'learning_objectives', '');

                return "• [Alur {$seq} - {$ch}]: {$tp}";
            })->implode("\n");

            $notes = 'Capaian Pembelajaran: '.(string) data_get($this->draft, 'cp_general', '-');
        } elseif ($docType === 'bahan_ajar') {
            $sections = (array) data_get($this->draft, 'key_sections', []);
            $activitiesText = collect($sections)->map(function ($sec) {
                $sub = data_get($sec, 'subtitle', 'Materi');
                $takeaway = data_get($sec, 'key_takeaway', '');

                return "• [{$sub}]: {$takeaway}";
            })->implode("\n");

            $notes = 'Konsep Inti: '.(string) data_get($this->draft, 'concept_summary', '-');
        } elseif ($docType === 'lkpd_bertingkat') {
            $l1 = (string) data_get($this->draft, 'level_1_scaffolding.guidance_steps', '-');
            $l2 = (string) data_get($this->draft, 'level_2_regular.instructions', '-');
            $l3 = (string) data_get($this->draft, 'level_3_advanced.challenge_case', '-');
            $activitiesText = "• Level 1 (Scaffolding): {$l1}\n• Level 2 (Reguler): {$l2}\n• Level 3 (Pengayaan): {$l3}";
            $notes = 'Instruksi Umum: '.(string) data_get($this->draft, 'general_instructions', '-');
        } else {
            // modul_ajar default
            $activitiesList = (array) data_get($this->draft, 'activities', []);
            $activitiesText = collect($activitiesList)->map(function ($act) {
                $stage = data_get($act, 'stage', 'Kegiatan');
                $dur = data_get($act, 'duration_minutes', 0);
                $desc = data_get($act, 'activity', '');
                $roles = '';
                if (data_get($act, 'teacher_role') || data_get($act, 'student_role')) {
                    $roles = ' (Guru: '.data_get($act, 'teacher_role', '-').', Murid: '.data_get($act, 'student_role', '-').')';
                }

                return "• [{$stage} - {$dur}m]: {$desc}{$roles}";
            })->implode("\n");

            $objectives = (array) data_get($this->draft, 'learning_objectives', []);
            $diff = (string) data_get($this->draft, 'differentiation', '');
            $diag = (string) data_get($this->draft, 'assessment.diagnostic', '');
            $form = (string) data_get($this->draft, 'assessment.formative', '');
            $notes = 'Tujuan: '.implode('; ', $objectives)."\n\nDiferensiasi: {$diff}\nAsesmen: {$diag} | {$form}";
        }

        TeachingJournal::create([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'schedule_id' => $schedule->id,
            'classroom_id' => $schedule->classroom_id,
            'subject_id' => $schedule->subject_id,
            'teacher_id' => $this->teacherId,
            'date' => now()->toDateString(),
            'meeting_number' => $meetingNumber,
            'learning_method' => $learningModel,
            'topic_summary' => $topic,
            'activities' => $activitiesText ?: 'Aktivitas pembelajaran sesuai draf perangkat pembelajaran.',
            'student_notes' => $notes ?: 'Catatan perangkat ajar terintegrasi.',
            'obstacles_and_solutions' => 'Diadaptasi dari Perangkat Pembelajaran AI Kurikulum Merdeka.',
            'status' => 'draft',
        ]);

        session()->flash('sync_journal_success', 'Jurnal mengajar pertemuan ke-'.$meetingNumber.' berhasil dibuat dari draf '.$this->documentLabel().'!');
    }

    public function syncToQuestionBank(): void
    {
        if (! $this->draft) {
            $this->addError('generation', 'Tidak ada draf yang dapat diekspor ke bank soal.');

            return;
        }

        $schedule = $this->resolveAccessibleSchedule();
        if (! $schedule) {
            $this->addError('generation', 'Jadwal mengajar tidak ditemukan.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();
        $subject = $schedule->subject;
        $classroom = $schedule->classroom;

        $moduleTitle = (string) data_get($this->draft, 'title', 'Perangkat Ajar');
        $bankCode = 'BS-'.strtoupper(substr($subject->name ?? 'MAPEL', 0, 3)).'-'.date('YmdHis');

        $questionBank = QuestionBank::create([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'teacher_id' => $this->teacherId,
            'subject_id' => $schedule->subject_id,
            'title' => 'Bank Soal: '.$moduleTitle,
            'code' => $bankCode,
            'grade_level' => (string) ($classroom->grade_level ?? '10'),
            'description' => 'Disinkronkan otomatis dari Asisten Perangkat Pembelajaran AI ('.$this->documentLabel().').',
        ]);

        $questionsCount = 0;
        $docType = (string) data_get($this->draft, 'document_type', $this->documentType);

        if ($docType === 'asesmen_kktp') {
            // Ekspor butir soal sumatif terstruktur
            $summativeQuestions = (array) data_get($this->draft, 'summative_assessment.questions', []);
            foreach ($summativeQuestions as $q) {
                $qText = (string) data_get($q, 'question_text', '');
                $stimulus = (string) data_get($q, 'stimulus_text', '');
                if ($stimulus !== '') {
                    $qText = "[Wacana Stimulus]\n{$stimulus}\n\n[Pertanyaan]\n{$qText}";
                }

                $qType = str_contains(strtolower((string) data_get($q, 'question_type', '')), 'pilihan ganda') ? 'pg' : 'essay';
                $options = data_get($q, 'options');
                $score = (int) data_get($q, 'scoring_points', 10);
                $correct = (string) data_get($q, 'correct_answer', 'Kunci Jawaban');

                Question::create([
                    'question_bank_id' => $questionBank->id,
                    'type' => $qType,
                    'question_text' => $qText,
                    'options' => is_array($options) ? $options : null,
                    'correct_answer' => $correct,
                    'score_weight' => $score > 0 ? $score : 10,
                ]);
                $questionsCount++;
            }

            // Ekspor butir diagnostik kognitif
            $diagQuestions = (array) data_get($this->draft, 'diagnostic_assessment.cognitive_questions', []);
            foreach ($diagQuestions as $dq) {
                $dqText = (string) data_get($dq, 'question', '');
                if (trim($dqText) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => '[Diagnostik Awal Kognitif] '.$dqText,
                        'options' => null,
                        'correct_answer' => (string) data_get($dq, 'correct_answer', 'Kunci Diagnostik'),
                        'score_weight' => 5,
                    ]);
                    $questionsCount++;
                }
            }
        } elseif ($docType === 'lkpd_bertingkat') {
            $l1Tasks = (array) data_get($this->draft, 'level_1_scaffolding.tasks', []);
            $l2Tasks = (array) data_get($this->draft, 'level_2_regular.core_tasks', []);
            $l3Tasks = (array) data_get($this->draft, 'level_3_advanced.hots_tasks', []);

            foreach ($l1Tasks as $t) {
                if (trim((string) $t) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => '[LKPD Level 1 - Fondasi] '.trim((string) $t),
                        'options' => null,
                        'correct_answer' => 'Rubrik LKPD Level 1',
                        'score_weight' => 5,
                    ]);
                    $questionsCount++;
                }
            }
            foreach ($l2Tasks as $t) {
                if (trim((string) $t) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => '[LKPD Level 2 - Reguler] '.trim((string) $t),
                        'options' => null,
                        'correct_answer' => 'Rubrik LKPD Level 2',
                        'score_weight' => 10,
                    ]);
                    $questionsCount++;
                }
            }
            foreach ($l3Tasks as $t) {
                if (trim((string) $t) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => '[LKPD Level 3 - HOTS/Pengayaan] '.trim((string) $t),
                        'options' => null,
                        'correct_answer' => 'Rubrik LKPD Level 3',
                        'score_weight' => 15,
                    ]);
                    $questionsCount++;
                }
            }
        } else {
            // 1. Export LKPD tasks
            $worksheetTasks = (array) data_get($this->draft, 'student_worksheet.tasks', []);
            foreach ($worksheetTasks as $task) {
                if (trim((string) $task) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => trim((string) $task),
                        'options' => null,
                        'correct_answer' => 'Rubrik/Kunci Esai LKPD',
                        'score_weight' => 10,
                    ]);
                    $questionsCount++;
                }
            }

            // 2. Export Inquiry Questions
            $inquiryQuestions = (array) data_get($this->draft, 'inquiry_questions', []);
            foreach ($inquiryQuestions as $inq) {
                if (trim((string) $inq) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => trim((string) $inq),
                        'options' => null,
                        'correct_answer' => 'Rubrik/Kunci Pertanyaan Pemantik',
                        'score_weight' => 5,
                    ]);
                    $questionsCount++;
                }
            }
        }

        // Fallback if no specific questions created
        if ($questionsCount === 0) {
            $objectives = (array) data_get($this->draft, 'learning_objectives', []);
            foreach ($objectives as $obj) {
                if (trim((string) $obj) !== '') {
                    Question::create([
                        'question_bank_id' => $questionBank->id,
                        'type' => 'essay',
                        'question_text' => 'Jelaskan bagaimana pemahaman Anda mengenai capaian: '.trim((string) $obj),
                        'options' => null,
                        'correct_answer' => 'Rubrik/Kunci Capaian Pembelajaran',
                        'score_weight' => 10,
                    ]);
                    $questionsCount++;
                }
            }
        }

        session()->flash('sync_cbt_success', 'Bank Soal "'.$questionBank->title.'" berhasil dibuat dengan '.$questionsCount.' butir soal CBT!');
    }

    public function selectBankTopic(string $topicId): void
    {
        $this->selectedBankTopicId = $topicId;
        if ($topicId === '' || ! isset($this->availableBankTopics[$topicId])) {
            return;
        }

        $topicData = $this->availableBankTopics[$topicId];
        $this->topic = (string) ($topicData['topic'] ?? '');
        $this->learningObjectives = (string) ($topicData['learning_objectives'] ?? '');
        $this->selectedLearningModel = (string) ($topicData['learning_model'] ?? '');
        $this->selectedP5Dimensions = (array) ($topicData['p5_dimensions'] ?? []);

        session()->flash('bank_selected_info', 'Topik resmi "'.($topicData['chapter_title'] ?? $this->topic).'" berhasil dimuat ke formulir.');
    }

    public function openDuplicateModal(): void
    {
        if (! $this->savedDraftId || ! $this->draft) {
            $this->addError('generation', 'Simpan draf terlebih dahulu sebelum menduplikasikannya ke kelas paralel.');

            return;
        }

        $currentSchedule = $this->resolveAccessibleSchedule();
        if (! $currentSchedule) {
            $this->addError('generation', 'Jadwal saat ini tidak ditemukan.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        // Find other active schedules taught by this teacher for the same subject
        $this->parallelClassSchedules = Schedule::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->where('subject_id', $currentSchedule->subject_id)
            ->where('id', '!=', $currentSchedule->id)
            ->with(['classroom', 'subject'])
            ->get()
            ->map(fn (Schedule $s) => [
                'id' => $s->id,
                'classroom_name' => trim(($s->classroom->grade_level ?? '').' '.($s->classroom->name ?? '')),
                'day_time' => $s->day_of_week.' ('.$this->formatTimeRange($s).')',
                'subject_name' => $s->subject->name ?? '',
            ])
            ->values()
            ->toArray();

        $this->selectedTargetScheduleIds = [];
        $this->showDuplicateModal = true;
    }

    public function closeDuplicateModal(): void
    {
        $this->showDuplicateModal = false;
        $this->selectedTargetScheduleIds = [];
    }

    public function duplicateToParallelClasses(): void
    {
        if (empty($this->selectedTargetScheduleIds)) {
            $this->addError('duplicate', 'Pilih minimal satu kelas paralel target.');

            return;
        }

        $sourceDraft = $this->accessibleDraftQuery()->whereKey($this->savedDraftId)->first();
        if (! $sourceDraft) {
            $this->addError('duplicate', 'Draf sumber tidak ditemukan.');

            return;
        }

        $duplicatedCount = 0;
        $schoolId = app(CurrentSchool::class)->id();

        foreach ($this->selectedTargetScheduleIds as $targetScheduleId) {
            $targetSchedule = Schedule::query()
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId)
                ->with('classroom')
                ->whereKey((int) $targetScheduleId)
                ->first();

            if (! $targetSchedule) {
                continue;
            }

            $outputPayload = $this->draft;
            $className = trim(($targetSchedule->classroom->grade_level ?? '').' '.($targetSchedule->classroom->name ?? ''));

            $nextVersion = ((int) LearningDraft::query()
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId)
                ->where('schedule_id', $targetSchedule->id)
                ->where('document_type', $sourceDraft->document_type)
                ->max('version')) + 1;

            LearningDraft::create([
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYearId,
                'teacher_id' => $this->teacherId,
                'user_id' => auth()->id(),
                'schedule_id' => $targetSchedule->id,
                'document_type' => $sourceDraft->document_type,
                'status' => LearningDraft::STATUS_DRAFT,
                'source' => 'duplication',
                'version' => $nextVersion,
                'provider' => $sourceDraft->provider,
                'model' => $sourceDraft->model,
                'input_context' => array_merge((array) $sourceDraft->input_context, [
                    'duplicated_from_draft_id' => $sourceDraft->id,
                    'target_classroom' => $className,
                ]),
                'output' => $outputPayload,
            ]);

            $duplicatedCount++;
        }

        $this->showDuplicateModal = false;
        $this->selectedTargetScheduleIds = [];
        $this->loadRecentDrafts();

        session()->flash('duplicate_success', "Berhasil menduplikasi modul ajar ke {$duplicatedCount} kelas paralel!");
    }

    public function loadDraft(int $draftId): void
    {
        $draft = $this->accessibleDraftQuery()
            ->whereKey($draftId)
            ->first();

        if (! $draft) {
            $this->addError('generation', 'Draf tidak ditemukan dalam ruang lingkup akun Anda.');

            return;
        }

        $this->selectedScheduleId = $draft->schedule_id ? (string) $draft->schedule_id : '';
        $this->documentType = $draft->document_type;
        $rawOutput = $draft->getRawOriginal('output');
        if (! is_string($rawOutput)) {
            $this->addError('generation', 'Isi draf tersimpan tidak memiliki struktur yang dapat ditampilkan.');

            return;
        }

        $decodedOutput = json_decode($rawOutput, true);
        if (! is_array($decodedOutput)) {
            $this->addError('generation', 'Isi draf tersimpan tidak memiliki struktur yang dapat ditampilkan.');

            return;
        }

        $this->draft = $decodedOutput;
        $this->savedDraftId = $draft->id;
        $this->savedDraftStatus = $draft->status;
        $this->isEditing = false;
        $this->resetErrorBag('generation');
    }

    public function loadSavedDraft(int $draftId): void
    {
        $this->loadDraft($draftId);
    }

    public function startEditing(): void
    {
        if (! $this->draft) {
            return;
        }

        $this->editTitle = (string) data_get($this->draft, 'title', '');
        $this->editSummary = (string) data_get($this->draft, 'summary', '');
        $this->editP5Dimensions = (array) data_get($this->draft, 'p5_dimensions', []);
        $this->editLearningModel = (string) data_get($this->draft, 'learning_model', '');
        $this->editMeaningfulUnderstanding = (string) data_get($this->draft, 'meaningful_understanding', '');
        $this->editInquiryQuestions = (array) data_get($this->draft, 'inquiry_questions', []);
        $this->editObjectives = (array) data_get($this->draft, 'learning_objectives', []);
        $this->editActivities = (array) data_get($this->draft, 'activities', []);
        $this->editWorksheetTitle = (string) data_get($this->draft, 'student_worksheet.title', '');
        $this->editWorksheetInstructions = (string) data_get($this->draft, 'student_worksheet.instructions', '');
        $this->editWorksheetTasks = (array) data_get($this->draft, 'student_worksheet.tasks', []);
        $this->editDiagnostic = (string) data_get($this->draft, 'assessment.diagnostic', '');
        $this->editFormative = (string) data_get($this->draft, 'assessment.formative', '');
        $this->editSummative = (string) data_get($this->draft, 'assessment.summative', '');
        $this->editAssessmentRubric = (array) data_get($this->draft, 'assessment_rubric', []);
        $this->editDifferentiation = (string) data_get($this->draft, 'differentiation', '');
        $this->editResources = (array) data_get($this->draft, 'resources', []);
        $this->editReferences = (array) data_get($this->draft, 'references', []);

        $this->isEditing = true;
    }

    public function cancelEditing(): void
    {
        $this->isEditing = false;
    }

    public function saveEditedDraft(): void
    {
        $this->validate([
            'editTitle' => ['required', 'string', 'max:500'],
            'editSummary' => ['nullable', 'string', 'max:2500'],
            'editP5Dimensions' => ['nullable', 'array'],
            'editLearningModel' => ['nullable', 'string', 'max:255'],
            'editMeaningfulUnderstanding' => ['nullable', 'string', 'max:2000'],
            'editInquiryQuestions' => ['nullable', 'array'],
            'editInquiryQuestions.*' => ['nullable', 'string', 'max:1000'],
            'editObjectives' => ['array'],
            'editObjectives.*' => ['nullable', 'string', 'max:1000'],
            'editActivities' => ['array'],
            'editActivities.*.stage' => ['required', 'string', 'max:255'],
            'editActivities.*.duration_minutes' => ['required', 'integer', 'min:1'],
            'editActivities.*.activity' => ['required', 'string', 'max:3000'],
            'editActivities.*.teacher_role' => ['nullable', 'string', 'max:1000'],
            'editActivities.*.student_role' => ['nullable', 'string', 'max:1000'],
            'editWorksheetTitle' => ['nullable', 'string', 'max:255'],
            'editWorksheetInstructions' => ['nullable', 'string', 'max:2500'],
            'editWorksheetTasks' => ['nullable', 'array'],
            'editWorksheetTasks.*' => ['nullable', 'string', 'max:2000'],
            'editDiagnostic' => ['nullable', 'string', 'max:2000'],
            'editFormative' => ['nullable', 'string', 'max:2000'],
            'editSummative' => ['nullable', 'string', 'max:2000'],
            'editAssessmentRubric' => ['nullable', 'array'],
            'editAssessmentRubric.*.criteria' => ['nullable', 'string', 'max:255'],
            'editAssessmentRubric.*.indicator' => ['nullable', 'string', 'max:1000'],
            'editAssessmentRubric.*.scoring_guide' => ['nullable', 'string', 'max:1000'],
            'editDifferentiation' => ['nullable', 'string', 'max:2500'],
            'editResources' => ['array'],
            'editResources.*' => ['nullable', 'string', 'max:500'],
            'editReferences' => ['array'],
            'editReferences.*' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedDraft = [
            'title' => trim($this->editTitle),
            'summary' => trim($this->editSummary),
            'p5_dimensions' => array_values(array_filter(array_map('trim', $this->editP5Dimensions))),
            'learning_model' => trim($this->editLearningModel),
            'meaningful_understanding' => trim($this->editMeaningfulUnderstanding),
            'inquiry_questions' => array_values(array_filter(array_map('trim', $this->editInquiryQuestions))),
            'learning_objectives' => array_values(array_filter(array_map('trim', $this->editObjectives))),
            'activities' => array_values(array_map(function ($act) {
                return [
                    'stage' => trim($act['stage'] ?? 'Kegiatan'),
                    'duration_minutes' => (int) ($act['duration_minutes'] ?? 10),
                    'activity' => trim($act['activity'] ?? ''),
                    'teacher_role' => trim($act['teacher_role'] ?? ''),
                    'student_role' => trim($act['student_role'] ?? ''),
                ];
            }, $this->editActivities)),
            'student_worksheet' => [
                'title' => trim($this->editWorksheetTitle) ?: 'Lembar Kerja Siswa',
                'instructions' => trim($this->editWorksheetInstructions),
                'tasks' => array_values(array_filter(array_map('trim', $this->editWorksheetTasks))),
            ],
            'assessment' => [
                'diagnostic' => trim($this->editDiagnostic),
                'formative' => trim($this->editFormative),
                'summative' => trim($this->editSummative),
            ],
            'assessment_rubric' => array_values(array_filter(array_map(function ($rub) {
                return [
                    'criteria' => trim($rub['criteria'] ?? ''),
                    'indicator' => trim($rub['indicator'] ?? ''),
                    'scoring_guide' => trim($rub['scoring_guide'] ?? ''),
                ];
            }, $this->editAssessmentRubric))),
            'differentiation' => trim($this->editDifferentiation),
            'resources' => array_values(array_filter(array_map('trim', $this->editResources))),
            'warnings' => (array) data_get($this->draft, 'warnings', []),
            'references' => array_values(array_filter(array_map('trim', $this->editReferences))),
        ];

        $this->draft = $updatedDraft;
        $this->isEditing = false;

        if ($this->savedDraftId) {
            $draft = $this->accessibleDraftQuery()->whereKey($this->savedDraftId)->first();
            if ($draft) {
                $draft->update([
                    'output' => $updatedDraft,
                ]);
                $this->loadRecentDrafts();
            }
        }

        session()->flash('generation_success', 'Perubahan draf berhasil disimpan.');
    }

    public function addInquiryQuestionRow(): void
    {
        $this->editInquiryQuestions[] = '';
    }

    public function removeInquiryQuestionRow(int $index): void
    {
        unset($this->editInquiryQuestions[$index]);
        $this->editInquiryQuestions = array_values($this->editInquiryQuestions);
    }

    public function addWorksheetTaskRow(): void
    {
        $this->editWorksheetTasks[] = '';
    }

    public function removeWorksheetTaskRow(int $index): void
    {
        unset($this->editWorksheetTasks[$index]);
        $this->editWorksheetTasks = array_values($this->editWorksheetTasks);
    }

    public function addRubricRow(): void
    {
        $this->editAssessmentRubric[] = [
            'criteria' => '',
            'indicator' => '',
            'scoring_guide' => '',
        ];
    }

    public function removeRubricRow(int $index): void
    {
        unset($this->editAssessmentRubric[$index]);
        $this->editAssessmentRubric = array_values($this->editAssessmentRubric);
    }

    public function addActivityRow(): void
    {
        $this->editActivities[] = [
            'stage' => 'Kegiatan Inti',
            'duration_minutes' => 15,
            'activity' => '',
            'teacher_role' => '',
            'student_role' => '',
        ];
    }

    public function removeActivityRow(int $index): void
    {
        unset($this->editActivities[$index]);
        $this->editActivities = array_values($this->editActivities);
    }

    public function addObjectiveRow(): void
    {
        $this->editObjectives[] = '';
    }

    public function removeObjectiveRow(int $index): void
    {
        unset($this->editObjectives[$index]);
        $this->editObjectives = array_values($this->editObjectives);
    }

    public function addResourceRow(): void
    {
        $this->editResources[] = '';
    }

    public function removeResourceRow(int $index): void
    {
        unset($this->editResources[$index]);
        $this->editResources = array_values($this->editResources);
    }

    public function addReferenceRow(): void
    {
        $this->editReferences[] = '';
    }

    public function removeReferenceRow(int $index): void
    {
        unset($this->editReferences[$index]);
        $this->editReferences = array_values($this->editReferences);
    }

    /**
     * @return array<string, string>
     */
    public function documentTypes(): array
    {
        return [
            'modul_ajar' => 'Modul Ajar (RPP+ Berdiferensiasi)',
            'atp' => 'Alur Tujuan Pembelajaran (ATP / Silabus)',
            'prota_prosem' => 'Program Tahunan & Semester (Prota & Prosem)',
            'bahan_ajar' => 'Bahan Ajar & Ringkasan Konseptual',
            'lkpd_bertingkat' => 'LKPD Berdiferensiasi (3 Tingkat Kesiapan)',
            'modul_p5' => 'Modul Projek Penguatan Profil Pelajar Pancasila (P5)',
            'asesmen_kktp' => 'Instrumen Asesmen & Rubrik KKTP Lengkap',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availableLearningModels(): array
    {
        return [
            'Problem-Based Learning (PBL)',
            'Project-Based Learning (PjBL)',
            'Discovery Learning',
            'Inquiry Learning (Inkuiri)',
            'Pedagogi Genre (Genre-Based)',
            'Diferensiasi Berbasis Stasiun',
            'Direct Instruction & Latihan Terbimbing',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availableP5Dimensions(): array
    {
        return [
            'Bernalar Kritis',
            'Kreatif',
            'Gotong Royong',
            'Mandiri',
            'Berkebinekaan Global',
            'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availableP5Themes(): array
    {
        return [
            'Gaya Hidup Berkelanjutan',
            'Kearifan Lokal',
            'Bhinneka Tunggal Ika',
            'Bangunlah Jiwa dan Raganya',
            'Suara Demokrasi',
            'Rekayasa dan Teknologi',
            'Kewirausahaan',
            'Kebekerjaan',
        ];
    }

    public function render(): View
    {
        return view('livewire.teacher.learning-assistant', [
            'documentTypes' => $this->documentTypes(),
            'availableLearningModels' => $this->availableLearningModels(),
            'availableP5Dimensions' => $this->availableP5Dimensions(),
            'availableP5Themes' => $this->availableP5Themes(),
        ]);
    }

    /**
     * @return Builder<Schedule>
     */
    private function accessibleScheduleQuery(int $schoolId, int $academicYearId, int $teacherId): Builder
    {
        return Schedule::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('teacher_id', $teacherId)
            ->whereHas('subject', fn (Builder $query): Builder => $query->where('school_id', $schoolId))
            ->whereHas('classroom', fn (Builder $query): Builder => $query
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $academicYearId));
    }

    private function resolveAccessibleSchedule(): ?Schedule
    {
        if (! $this->activeYearId || ! $this->teacherId || $this->selectedScheduleId === '') {
            return null;
        }

        $schoolId = app(CurrentSchool::class)->id();

        return $this->accessibleScheduleQuery($schoolId, $this->activeYearId, $this->teacherId)
            ->with(['classroom', 'subject', 'academicYear'])
            ->whereKey((int) $this->selectedScheduleId)
            ->first();
    }

    /**
     * @return Builder<LearningDraft>
     */
    private function accessibleDraftQuery(): Builder
    {
        if (! $this->activeYearId || ! $this->teacherId) {
            return LearningDraft::query()->whereKey(0);
        }

        return LearningDraft::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->where('user_id', auth()->id());
    }

    private function loadRecentDrafts(): void
    {
        if (! $this->activeYearId || ! $this->teacherId) {
            $this->recentDrafts = [];

            return;
        }

        $this->recentDrafts = $this->accessibleDraftQuery()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (LearningDraft $draft): array => [
                'id' => $draft->id,
                'title' => (string) data_get($draft->output, 'title', 'Draf perangkat pembelajaran'),
                'document_type' => $draft->document_type,
                'status' => $draft->status,
                'source' => $draft->source,
                'version' => $draft->version,
                'created_at' => $draft->created_at?->format('d/m/Y H:i') ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(Schedule $schedule): array
    {
        $studentNeeds = $this->preferOrFallback($this->studentNeeds, $schedule->classroom->student_needs);
        $availableFacilities = $this->preferOrFallback($this->availableFacilities, $schedule->classroom->available_facilities);
        $learningEnvironment = trim((string) $schedule->classroom->learning_environment);

        if ($learningEnvironment !== '') {
            $availableFacilities = trim($availableFacilities."\nLingkungan belajar: {$learningEnvironment}");
        }

        $p5Focus = !empty($this->selectedP5Dimensions) ? implode(', ', $this->selectedP5Dimensions) : 'Bernalar Kritis, Kreatif, Gotong Royong';
        $learningModel = $this->selectedLearningModel !== '' ? $this->selectedLearningModel : 'Pilih model yang paling sesuai (misal: Problem-Based Learning / Project-Based Learning / Pedagogi Genre)';

        return [
            'document_type' => $this->documentType,
            'jenis_dokumen' => $this->documentLabel(),
            'sekolah' => [
                'nama' => $this->schoolName,
                'jenjang' => $this->schoolLevel,
                'kurikulum' => $this->curriculumType,
                'fase' => $this->detectedFase ?: 'Fase E (Kelas 10)',
                'visi_misi' => $this->missingIfBlank($this->schoolVisionMission),
                'muatan_lokal' => $this->missingIfBlank($this->localContent),
            ],
            'tahun_ajaran' => $this->academicYearLabel,
            'jadwal' => [
                'mata_pelajaran' => $schedule->subject->name,
                'kelas' => trim($schedule->classroom->grade_level.' '.$schedule->classroom->name),
                'alokasi_waktu' => $this->formatTimeRange($schedule),
                'lingkungan_belajar' => $this->missingIfBlank($learningEnvironment),
            ],
            'konteks_guru' => [
                'topik' => trim($this->topic),
                'tujuan_pembelajaran' => trim($this->learningObjectives),
                'model_pembelajaran' => $learningModel,
                'dimensi_profil_pelajar_pancasila' => $p5Focus,
                'p5_tema' => $this->selectedP5Theme,
                'pekan_efektif_ganjil' => $this->effectiveWeeksOdd,
                'pekan_efektif_genap' => $this->effectiveWeeksEven,
                'kebutuhan_belajar_kelas' => $this->missingIfBlank($studentNeeds),
                'fasilitas_tersedia' => $this->missingIfBlank($availableFacilities),
                'catatan_tambahan' => $this->missingIfBlank($this->additionalContext),
            ],
        ];
    }

    private function documentLabel(): string
    {
        return $this->documentTypes()[$this->documentType] ?? 'Perangkat Pembelajaran';
    }

    private function missingIfBlank(string $value): string
    {
        return trim($value) === '' ? '[PERLU DIISI/DIKONFIRMASI]' : trim($value);
    }

    private function formatSchoolVisionMission(string $vision, string $mission): string
    {
        $sections = [];

        if (trim($vision) !== '') {
            $sections[] = "Visi:\n".trim($vision);
        }

        if (trim($mission) !== '') {
            $sections[] = "Misi:\n".trim($mission);
        }

        return implode("\n\n", $sections);
    }

    private function prefillClassContext(): void
    {
        $schedule = $this->resolveAccessibleSchedule();

        if (! $schedule) {
            return;
        }

        $this->studentNeeds = (string) ($schedule->classroom->student_needs ?? '');
        $this->availableFacilities = (string) ($schedule->classroom->available_facilities ?? '');

        // Auto-detect Kurikulum Merdeka Fase based on grade level
        $grade = (int) ($schedule->classroom->grade_level ?? 10);
        $this->detectedFase = match (true) {
            $grade >= 11 => 'Fase F (Kelas 11–12 SMA/SMK)',
            $grade === 10 => 'Fase E (Kelas 10 SMA/SMK)',
            $grade >= 7 => 'Fase D (Kelas 7–9 SMP/MTs)',
            $grade >= 5 => 'Fase C (Kelas 5–6 SD/MI)',
            $grade >= 3 => 'Fase B (Kelas 3–4 SD/MI)',
            default => 'Fase A (Kelas 1–2 SD/MI)',
        };

        $subjectName = (string) ($schedule->subject->name ?? '');
        $schoolId = app(CurrentSchool::class)->id();
        $this->availableBankTopics = CurriculumBank::getTopicsForSubjectAndFase($subjectName, $this->detectedFase, $schoolId);
        $this->selectedBankTopicId = '';
    }

    private function preferOrFallback(string $preferred, ?string $fallback): string
    {
        return trim($preferred) !== '' ? trim($preferred) : trim((string) $fallback);
    }

    private function geminiIsConfigured(): bool
    {
        return filter_var(config('services.gemini.enabled', false), FILTER_VALIDATE_BOOL) === true
            && filled(config('services.gemini.api_key'));
    }

    private function formatTimeRange(Schedule $schedule): string
    {
        $start = substr((string) $schedule->start_time, 0, 5);
        $end = substr((string) $schedule->end_time, 0, 5);

        return trim($start.'–'.$end);
    }
}
