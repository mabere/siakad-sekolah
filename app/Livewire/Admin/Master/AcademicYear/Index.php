<?php

namespace App\Livewire\Admin\Master\AcademicYear;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\SystemSetting;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $isFormOpen = false;

    public bool $isEdit = false;

    public int|string|null $editingId = null;

    // Form fields
    public string $name = '';

    public string $semester = 'Ganjil';

    public ?string $start_date = null;

    public ?string $end_date = null;

    public string $curriculum_type = 'MERDEKA';

    public ?string $local_content = null;

    public ?string $p5_focus = null;

    public string|int|null $effective_weeks = null;

    public ?string $calendar_notes = null;

    public bool $is_active = false;

    public function mount(): void
    {
        $this->curriculum_type = $this->defaultCurriculumType();
    }

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $schoolId = app(CurrentSchool::class)->id();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_years', 'name')
                    ->where(fn ($query) => $query
                        ->where('school_id', $schoolId)
                        ->where('semester', $this->semester))
                    ->ignore($this->editingId),
            ],
            'semester' => 'required|in:Ganjil,Genap',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'curriculum_type' => 'required|in:MERDEKA,K13',
            'local_content' => 'nullable|string|max:1000',
            'p5_focus' => 'nullable|string|max:1000',
            'effective_weeks' => 'nullable|integer|min:1|max:52',
            'calendar_notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int|string $id): void
    {
        $this->resetForm();
        $this->isEdit = true;
        $this->editingId = $id;
        $this->isFormOpen = true;

        $record = AcademicYear::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $this->name = $record->name;
        $this->semester = $record->semester;
        $this->start_date = $record->start_date ? $record->start_date->format('Y-m-d') : null;
        $this->end_date = $record->end_date ? $record->end_date->format('Y-m-d') : null;
        $this->curriculum_type = $record->curriculum_type ?: $this->defaultCurriculumType();
        $this->local_content = $record->local_content;
        $this->p5_focus = $record->p5_focus;
        $this->effective_weeks = $record->effective_weeks;
        $this->calendar_notes = $record->calendar_notes;
        $this->is_active = $record->is_active;
    }

    public function save(): void
    {
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();

        DB::transaction(function () use ($schoolId): void {
            if ($this->is_active) {
                AcademicYear::query()
                    ->where('school_id', $schoolId)
                    ->update(['is_active' => false]);
            }

            if ($this->isEdit) {
                $record = AcademicYear::query()
                    ->where('school_id', $schoolId)
                    ->lockForUpdate()
                    ->whereKey($this->editingId)
                    ->firstOrFail();
                $record->update([
                    'name' => $this->name,
                    'semester' => $this->semester,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'curriculum_type' => $this->curriculum_type,
                    'local_content' => $this->nullableTrimmed($this->local_content),
                    'p5_focus' => $this->nullableTrimmed($this->p5_focus),
                    'effective_weeks' => $this->effective_weeks ?: null,
                    'calendar_notes' => $this->nullableTrimmed($this->calendar_notes),
                    'is_active' => $this->is_active,
                ]);
                session()->flash('message', 'Tahun Ajaran berhasil diperbarui.');
            } else {
                AcademicYear::create([
                    'school_id' => $schoolId,
                    'name' => $this->name,
                    'semester' => $this->semester,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'curriculum_type' => $this->curriculum_type,
                    'local_content' => $this->nullableTrimmed($this->local_content),
                    'p5_focus' => $this->nullableTrimmed($this->p5_focus),
                    'effective_weeks' => $this->effective_weeks ?: null,
                    'calendar_notes' => $this->nullableTrimmed($this->calendar_notes),
                    'is_active' => $this->is_active,
                ]);
                session()->flash('message', 'Tahun Ajaran berhasil ditambahkan.');
            }
        });

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $record = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->whereKey($id)
            ->firstOrFail();

        if ($record->is_active || Classroom::query()->where('academic_year_id', $record->id)->exists()) {
            session()->flash('error', 'Tahun ajaran aktif atau yang sudah memiliki kelas tidak dapat dihapus.');

            return;
        }

        $record->delete();
        session()->flash('message', 'Tahun Ajaran berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'name',
            'semester',
            'start_date',
            'end_date',
            'curriculum_type',
            'local_content',
            'p5_focus',
            'effective_weeks',
            'calendar_notes',
            'is_active',
            'isFormOpen',
            'isEdit',
            'editingId',
        ]);
        $this->curriculum_type = $this->defaultCurriculumType();
    }

    public function setAsActive(int|string $id): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        DB::transaction(function () use ($schoolId, $id): void {
            $target = AcademicYear::query()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->whereKey($id)
                ->firstOrFail();

            AcademicYear::query()
                ->where('school_id', $schoolId)
                ->update(['is_active' => false]);

            $target->update(['is_active' => true]);
        });

        session()->flash('message', 'Tahun Ajaran aktif berhasil diubah.');
    }

    private function defaultCurriculumType(): string
    {
        $setting = SystemSetting::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('key', 'curriculum_type')
            ->first();

        return $setting && is_string($setting->value) && in_array($setting->value, ['MERDEKA', 'K13'], true)
            ? $setting->value
            : 'MERDEKA';
    }

    private function nullableTrimmed(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderBy('name', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        return view('livewire.admin.master.academic-year.index', [
            'academicYears' => $academicYears,
        ]);
    }
}
