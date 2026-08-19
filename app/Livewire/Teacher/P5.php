<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\P5ProjectDimension;
use App\Models\P5StudentNote;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class P5 extends Component
{
    public string $activeTab = 'assessment'; // 'assessment' or 'manage_projects'

    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    // --- Tab 1: Assessment Matrix State ---
    public string $selectedProjectId = '';

    /** @var array<int|string, mixed> */
    public array $assessmentData = [];

    /** @var array<int|string, mixed> */
    public array $processNotesData = [];

    // --- Tab 2: Manage Projects State ---
    public bool $showProjectModal = false;

    public ?int $editingProjectId = null;

    public string $projectTitle = '';

    public string $projectTheme = 'Gaya Hidup Berkelanjutan';

    public string $projectPhase = 'Fase D';

    public string $projectClassroomId = '';

    public string $projectDescription = '';

    /** @var array<int|string, mixed> */
    public array $projectDimensionsInput = [];

    /** @var array<int, string> */
    public static array $themes = [
        'Gaya Hidup Berkelanjutan',
        'Kearifan Lokal',
        'Bhinneka Tunggal Ika',
        'Bangunlah Jiwa dan Raganya',
        'Suara Demokrasi',
        'Rekayasa dan Teknologi',
        'Kewirausahaan',
        'Kebekerjaan',
    ];

    /** @var array<int, string> */
    public static array $dimensions = [
        'Beriman, Bertakwa kepada Tuhan YME, & Berakhlak Mulia',
        'Berkebinekaan Global',
        'Gotong Royong',
        'Mandiri',
        'Bernalar Kritis',
        'Kreatif',
    ];

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

        if ($this->activeYearId && $this->teacherId) {
            $firstProject = P5Project::where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId)
                ->first();

            if ($firstProject) {
                $this->selectedProjectId = (string) $firstProject->id;
                $this->loadProjectAssessment();
            }
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['assessment', 'manage_projects'])) {
            $this->activeTab = $tab;
        }
    }

    public function updatedSelectedProjectId(): void
    {
        $this->loadProjectAssessment();
    }

    public function loadProjectAssessment(): void
    {
        $this->assessmentData = [];
        $this->processNotesData = [];

        if (! $this->selectedProjectId || ! $this->activeYearId) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $project = $this->accessibleProjectQuery()
            ->with(['dimensions', 'classroom'])
            ->whereKey($this->selectedProjectId)
            ->first();

        if (! $project) {
            return;
        }

        $students = Student::where('school_id', $schoolId)
            ->where('classroom_id', $project->classroom_id)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $existingAssessments = P5Assessment::where('p5_project_id', $project->id)
            ->get()
            ->groupBy('student_id');

        $existingNotes = P5StudentNote::where('p5_project_id', $project->id)
            ->get()
            ->keyBy('student_id');

        foreach ($students as $student) {
            $studentAssessments = $existingAssessments->get($student->id, collect())->keyBy('p5_project_dimension_id');

            foreach ($project->dimensions as $dim) {
                $record = $studentAssessments->get($dim->id);
                $this->assessmentData[$student->id][$dim->id] = $record !== null ? $record->score : 'BSH';
            }

            $noteRecord = $existingNotes->get($student->id);
            $this->processNotesData[$student->id] = $noteRecord !== null ? $noteRecord->process_notes : '';
        }
    }

    public function setAllDimensionScore(int $dimensionId, string $score): void
    {
        if (! in_array($score, ['BB', 'MB', 'BSH', 'SB'])) {
            return;
        }

        foreach ($this->assessmentData as $studentId => $dims) {
            $this->assessmentData[$studentId][$dimensionId] = $score;
        }
    }

    public function saveAssessments(): void
    {
        if (! $this->selectedProjectId) {
            session()->flash('p5_error', 'Pilih projek P5 terlebih dahulu.');

            return;
        }

        $project = $this->accessibleProjectQuery()->with('dimensions')->whereKey($this->selectedProjectId)->first();
        if (! $project) {
            session()->flash('p5_error', 'Projek P5 tidak ditemukan.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $studentIds = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $project->classroom_id)
            ->where('status', 'Aktif')
            ->pluck('id')
            ->all();
        $dimensionIds = $project->dimensions->pluck('id')->all();

        DB::transaction(function () use ($schoolId, $project, $studentIds, $dimensionIds): void {
            // 1. Save Qualitative Scores
            foreach ($this->assessmentData as $studentId => $dimensions) {
                if (! in_array((int) $studentId, $studentIds, true)) {
                    continue;
                }

                foreach ($dimensions as $dimensionId => $score) {
                    if (! in_array((int) $dimensionId, $dimensionIds, true)) {
                        continue;
                    }

                    $validScore = in_array($score, ['BB', 'MB', 'BSH', 'SB']) ? $score : 'BSH';

                    P5Assessment::updateOrCreate(
                        [
                            'school_id' => $schoolId,
                            'academic_year_id' => $this->activeYearId,
                            'p5_project_id' => $project->id,
                            'p5_project_dimension_id' => $dimensionId,
                            'student_id' => $studentId,
                        ],
                        [
                            'score' => $validScore,
                        ]
                    );
                }
            }

            // 2. Save Process Notes
            foreach ($this->processNotesData as $studentId => $notes) {
                if (! in_array((int) $studentId, $studentIds, true) || ! is_string($notes)) {
                    continue;
                }

                if (! empty(trim($notes))) {
                    P5StudentNote::updateOrCreate(
                        [
                            'school_id' => $schoolId,
                            'academic_year_id' => $this->activeYearId,
                            'p5_project_id' => $project->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'process_notes' => trim($notes),
                        ]
                    );
                }
            }
        });

        session()->flash('p5_success', 'Evaluasi kualitatif karakter P5 berhasil disimpan.');
    }

    // --- Tab 2: Manage Projects Methods ---
    public function openProjectModal(?int $projectId = null): void
    {
        $this->resetValidation();

        $schoolId = app(CurrentSchool::class)->id();
        $classrooms = Classroom::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->get();

        if ($projectId) {
            $project = $this->accessibleProjectQuery()->with('dimensions')->whereKey($projectId)->first();
            if (! $project) {
                return;
            }

            $this->editingProjectId = $project->id;
            $this->projectTitle = $project->title;
            $this->projectTheme = $project->theme;
            $this->projectPhase = $project->phase;
            $this->projectClassroomId = (string) $project->classroom_id;
            $this->projectDescription = $project->description ?? '';

            $this->projectDimensionsInput = [];
            foreach ($project->dimensions as $dim) {
                $this->projectDimensionsInput[] = [
                    'id' => $dim->id,
                    'dimension_name' => $dim->dimension_name,
                    'element_name' => $dim->element_name,
                    'sub_element' => $dim->sub_element,
                    'target_description' => $dim->target_description ?? '',
                ];
            }
        } else {
            $this->editingProjectId = null;
            $this->projectTitle = '';
            $this->projectTheme = 'Gaya Hidup Berkelanjutan';
            $this->projectPhase = 'Fase D';
            $this->projectClassroomId = $classrooms->first()?->id ? (string) $classrooms->first()->id : '';
            $this->projectDescription = '';
            $this->projectDimensionsInput = [
                [
                    'id' => null,
                    'dimension_name' => 'Gotong Royong',
                    'element_name' => 'Kolaborasi',
                    'sub_element' => 'Kerjasama kelompok dalam merencanakan & mengeksekusi projek',
                    'target_description' => '',
                ],
            ];
        }

        $this->showProjectModal = true;
    }

    public function addDimensionRow(): void
    {
        $this->projectDimensionsInput[] = [
            'id' => null,
            'dimension_name' => 'Bernalar Kritis',
            'element_name' => 'Memperoleh & memproses informasi',
            'sub_element' => 'Mengidentifikasi, mengklarifikasi, dan mengolah informasi',
            'target_description' => '',
        ];
    }

    public function removeDimensionRow(int $index): void
    {
        unset($this->projectDimensionsInput[$index]);
        $this->projectDimensionsInput = array_values($this->projectDimensionsInput);
    }

    public function closeProjectModal(): void
    {
        $this->showProjectModal = false;
        $this->editingProjectId = null;
    }

    public function saveProject(): void
    {
        $validated = Validator::make([
            'title' => $this->projectTitle,
            'theme' => $this->projectTheme,
            'phase' => $this->projectPhase,
            'classroom_id' => $this->projectClassroomId,
            'description' => $this->projectDescription,
            'dimensions' => $this->projectDimensionsInput,
        ], [
            'title' => ['required', 'string', 'max:255'],
            'theme' => ['required', 'string', 'max:100'],
            'phase' => ['required', 'string', 'max:50'],
            'classroom_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:2000'],
            'dimensions' => ['required', 'array', 'min:1'],
            'dimensions.*.dimension_name' => ['required', 'string', 'max:150'],
            'dimensions.*.element_name' => ['required', 'string', 'max:150'],
            'dimensions.*.sub_element' => ['required', 'string', 'max:255'],
            'dimensions.*.target_description' => ['nullable', 'string', 'max:500'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $classroom = Classroom::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->whereKey($validated['classroom_id'])
            ->first();
        if (! $classroom) {
            abort(403);
        }

        DB::transaction(function () use ($schoolId, $validated): void {
            $project = $this->editingProjectId
                ? $this->accessibleProjectQuery()->whereKey($this->editingProjectId)->first()
                : new P5Project;
            if ($this->editingProjectId && ! $project) {
                abort(403);
            }
            $project->fill([
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYearId,
                'classroom_id' => $validated['classroom_id'],
                'teacher_id' => $this->teacherId,
                'title' => trim($validated['title']),
                'theme' => $validated['theme'],
                'phase' => $validated['phase'],
                'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            ]);
            $project->save();

            // Sync dimensions
            $keptDimensionIds = [];
            foreach ($validated['dimensions'] as $dimInput) {
                $dimModel = P5ProjectDimension::updateOrCreate(
                    [
                        'id' => $dimInput['id'] ?? null,
                        'p5_project_id' => $project->id,
                    ],
                    [
                        'dimension_name' => trim($dimInput['dimension_name']),
                        'element_name' => trim($dimInput['element_name']),
                        'sub_element' => trim($dimInput['sub_element']),
                        'target_description' => ! empty($dimInput['target_description']) ? trim($dimInput['target_description']) : null,
                    ]
                );

                $keptDimensionIds[] = $dimModel->id;
            }

            // Delete removed dimensions
            P5ProjectDimension::where('p5_project_id', $project->id)
                ->whereNotIn('id', $keptDimensionIds)
                ->delete();

            $this->selectedProjectId = (string) $project->id;
        });

        session()->flash('p5_success', 'Projek P5 berhasil disimpan.');
        $this->closeProjectModal();
        $this->loadProjectAssessment();
    }

    public function deleteProject(int $projectId): void
    {
        $project = $this->accessibleProjectQuery()->whereKey($projectId)->first();
        if ($project) {
            $project->delete();
            session()->flash('p5_success', 'Projek P5 berhasil dihapus.');

            if ($this->selectedProjectId == (string) $projectId) {
                $this->selectedProjectId = '';
                $this->assessmentData = [];
                $this->processNotesData = [];
            }
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

        $classrooms = Classroom::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $projects = P5Project::with(['classroom', 'dimensions'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId)
            ->orderBy('title')
            ->get();

        $selectedProject = $this->selectedProjectId
            ? $this->accessibleProjectQuery()->with(['classroom', 'dimensions'])->whereKey($this->selectedProjectId)->first()
            : null;

        $students = $selectedProject
            ? Student::where('school_id', $schoolId)
                ->where('classroom_id', $selectedProject->classroom_id)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.teacher.p5', [
            'activeYear' => $activeYear,
            'classrooms' => $classrooms,
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'students' => $students,
        ]);
    }

    /** @return Builder<P5Project> */
    private function accessibleProjectQuery(): Builder
    {
        return P5Project::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId);
    }
}
