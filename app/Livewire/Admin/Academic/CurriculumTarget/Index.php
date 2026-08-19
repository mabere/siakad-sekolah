<?php

namespace App\Livewire\Admin\Academic\CurriculumTarget;

use App\Models\CurriculumTarget;
use App\Models\Subject;
use App\Services\Curriculum\CurriculumBank;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedPhase = '';

    public string $selectedSubjectName = '';

    public string $selectedGrade = '';

    public string $selectedSemester = '';

    // --- Modal Form State ---
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $targetId = null;

    public ?int $subject_id = null;

    public string $subject_name = '';

    public string $phase = 'Fase E';

    public int $grade_level = 10;

    public string $semester = '1';

    public int $chapter_number = 1;

    public string $chapter_title = '';

    public string $element = '';

    public string $topic = '';

    public string $learning_objectives = '';

    public string $learning_model = 'Problem-Based Learning (PBL) & Pedagogi Genre';

    /** @var array<int, string> */
    public array $p5_dimensions = [];

    public string $meaningful_understanding = '';

    public string $inquiry_questions_text = '';

    public string $suggested_duration_jp = '6 JP (3 Pertemuan)';

    public string $reference_source = 'Kepka BSKAP No. 032/H/KR/2024';

    public bool $is_active = true;

    // --- Confirmation Modal State ---
    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedPhase(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSubjectName(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedGrade(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSemester(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editTarget(int $id): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $target = CurriculumTarget::where('school_id', $schoolId)->findOrFail($id);

        $this->targetId = $target->id;
        $this->subject_id = $target->subject_id;
        $this->subject_name = (string) $target->subject_name;
        $this->phase = (string) $target->phase;
        $this->grade_level = (int) $target->grade_level;
        $this->semester = (string) $target->semester;
        $this->chapter_number = (int) $target->chapter_number;
        $this->chapter_title = (string) $target->chapter_title;
        $this->element = (string) ($target->element ?? '');
        $this->topic = (string) $target->topic;
        $this->learning_objectives = (string) $target->learning_objectives;
        $this->learning_model = (string) ($target->learning_model ?? '');
        $this->p5_dimensions = (array) ($target->p5_dimensions ?? []);
        $this->meaningful_understanding = (string) ($target->meaningful_understanding ?? '');
        $this->inquiry_questions_text = implode("\n", (array) ($target->inquiry_questions ?? []));
        $this->suggested_duration_jp = (string) ($target->suggested_duration_jp ?? '');
        $this->reference_source = (string) ($target->reference_source ?? 'Kepka BSKAP No. 032/H/KR/2024');
        $this->is_active = (bool) $target->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'subject_name' => ['required', 'string', 'max:150'],
            'phase' => ['required', 'string', 'max:20'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'chapter_number' => ['required', 'integer', 'min:1'],
            'chapter_title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'learning_objectives' => ['required', 'string', 'max:3000'],
            'learning_model' => ['nullable', 'string', 'max:255'],
            'p5_dimensions' => ['nullable', 'array'],
            'meaningful_understanding' => ['nullable', 'string', 'max:2000'],
            'inquiry_questions_text' => ['nullable', 'string', 'max:2000'],
            'suggested_duration_jp' => ['nullable', 'string', 'max:100'],
            'reference_source' => ['nullable', 'string', 'max:255'],
        ]);

        $schoolId = app(CurrentSchool::class)->id();

        // Match subject_id if subject exists in school
        $subject = Subject::where('school_id', $schoolId)
            ->where('name', 'like', "%{$this->subject_name}%")
            ->first();

        // Convert inquiry questions text to array
        $inquiries = array_values(array_filter(
            array_map('trim', explode("\n", $this->inquiry_questions_text)),
            fn ($line) => $line !== ''
        ));

        $payload = [
            'school_id' => $schoolId,
            'subject_id' => $subject?->id,
            'subject_name' => trim($this->subject_name),
            'phase' => trim($this->phase),
            'grade_level' => $this->grade_level,
            'semester' => $this->semester,
            'chapter_number' => $this->chapter_number,
            'chapter_title' => trim($this->chapter_title),
            'element' => trim($this->element),
            'topic' => trim($this->topic),
            'learning_objectives' => trim($this->learning_objectives),
            'learning_model' => trim($this->learning_model),
            'p5_dimensions' => $this->p5_dimensions,
            'meaningful_understanding' => trim($this->meaningful_understanding),
            'inquiry_questions' => $inquiries,
            'suggested_duration_jp' => trim($this->suggested_duration_jp),
            'reference_source' => trim($this->reference_source) ?: 'Kepka BSKAP No. 032/H/KR/2024',
            'is_active' => $this->is_active,
            'created_by' => auth()->id(),
        ];

        if ($this->isEditing && $this->targetId) {
            $target = CurriculumTarget::where('school_id', $schoolId)->findOrFail($this->targetId);
            $target->update($payload);
            session()->flash('message', 'Target Capaian Pembelajaran & TP berhasil diperbarui.');
        } else {
            CurriculumTarget::create($payload);
            session()->flash('message', 'Target Capaian Pembelajaran & TP baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteTargetId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteTarget(): void
    {
        if ($this->deleteTargetId) {
            $schoolId = app(CurrentSchool::class)->id();
            CurriculumTarget::where('school_id', $schoolId)
                ->whereKey($this->deleteTargetId)
                ->delete();

            session()->flash('message', 'Target kurikulum berhasil dihapus.');
            $this->showDeleteModal = false;
            $this->deleteTargetId = null;
        }
    }

    public function toggleStatus(int $id): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $target = CurriculumTarget::where('school_id', $schoolId)->findOrFail($id);
        $target->update(['is_active' => ! $target->is_active]);

        session()->flash('message', 'Status aktif target kurikulum diperbarui.');
    }

    public function loadNationalPresets(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $subject = $this->selectedSubjectName !== '' ? $this->selectedSubjectName : null;
        $phase = $this->selectedPhase !== '' ? $this->selectedPhase : 'Fase E';

        $count = CurriculumBank::seedPresetsToSchool(
            schoolId: $schoolId,
            subjectName: $subject,
            fase: $phase,
            userId: auth()->id()
        );

        session()->flash('message', "Berhasil memuat {$count} target CP & TP standar nasional Kemendikdasmen (BSKAP 032/2024).");
    }

    public function resetForm(): void
    {
        $this->targetId = null;
        $this->subject_id = null;
        $this->subject_name = $this->selectedSubjectName ?: 'Bahasa Indonesia';
        $this->phase = $this->selectedPhase ?: 'Fase E';
        $this->grade_level = 10;
        $this->semester = '1';
        $this->chapter_number = 1;
        $this->chapter_title = '';
        $this->element = '';
        $this->topic = '';
        $this->learning_objectives = '';
        $this->learning_model = 'Problem-Based Learning (PBL) & Pedagogi Genre';
        $this->p5_dimensions = ['Bernalar Kritis', 'Gotong Royong', 'Mandiri'];
        $this->meaningful_understanding = '';
        $this->inquiry_questions_text = '';
        $this->suggested_duration_jp = '6 JP (3 Pertemuan)';
        $this->reference_source = 'Kepka BSKAP No. 032/H/KR/2024';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    /**
     * @return array<int, string>
     */
    public function availablePhases(): array
    {
        return [
            'Fase A' => 'Fase A (Kelas 1–2 SD/MI)',
            'Fase B' => 'Fase B (Kelas 3–4 SD/MI)',
            'Fase C' => 'Fase C (Kelas 5–6 SD/MI)',
            'Fase D' => 'Fase D (Kelas 7–9 SMP/MTs)',
            'Fase E' => 'Fase E (Kelas 10 SMA/SMK)',
            'Fase F' => 'Fase F (Kelas 11–12 SMA/SMK)',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availableLearningModels(): array
    {
        return [
            'Problem-Based Learning (PBL) & Pedagogi Genre',
            'Problem-Based Learning (PBL)',
            'Project-Based Learning (PjBL)',
            'Discovery Learning',
            'Inquiry Learning (Inkuiri)',
            'Genre-Based Approach (Pedagogi Genre)',
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

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();

        $targets = CurriculumTarget::query()
            ->where('school_id', $schoolId)
            ->when($this->search !== '', function (Builder $q): void {
                $q->where(function (Builder $sq): void {
                    $sq->where('chapter_title', 'like', "%{$this->search}%")
                        ->orWhere('topic', 'like', "%{$this->search}%")
                        ->orWhere('subject_name', 'like', "%{$this->search}%")
                        ->orWhere('element', 'like', "%{$this->search}%");
                });
            })
            ->when($this->selectedPhase !== '', fn (Builder $q) => $q->where('phase', $this->selectedPhase))
            ->when($this->selectedSubjectName !== '', fn (Builder $q) => $q->where('subject_name', 'like', "%{$this->selectedSubjectName}%"))
            ->when($this->selectedGrade !== '', fn (Builder $q) => $q->where('grade_level', (int) $this->selectedGrade))
            ->when($this->selectedSemester !== '', fn (Builder $q) => $q->where('semester', $this->selectedSemester))
            ->orderBy('phase')
            ->orderBy('subject_name')
            ->orderBy('grade_level')
            ->orderBy('chapter_number')
            ->paginate(7);

        return view('livewire.admin.academic.curriculum-target.index', [
            'targets' => $targets,
            'subjects' => $subjects,
            'phases' => $this->availablePhases(),
            'learningModels' => $this->availableLearningModels(),
            'p5DimensionsList' => $this->availableP5Dimensions(),
        ]);
    }
}
