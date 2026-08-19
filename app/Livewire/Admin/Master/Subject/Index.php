<?php

namespace App\Livewire\Admin\Master\Subject;

use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Subject;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $isFormOpen = false;

    public bool $isEdit = false;

    public int|string|null $editingId = null;

    // Search and Filters
    public string $search = '';

    public string $selectedType = '';

    public int $perPage = 8;

    // Form fields
    public ?string $name = null;

    public ?string $code = null;

    public string $type = 'Wajib';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedType(): void
    {
        $this->resetPage();
    }

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $schoolId = app(CurrentSchool::class)->id();

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('school_id', $schoolId)
                    ->ignore($this->editingId),
            ],
            'type' => 'required|in:Wajib,Peminatan,Muatan Lokal',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isFormOpen = true;
    }

    public function edit(int|string $id): void
    {
        $this->resetForm();
        $this->isEdit = true;
        $this->editingId = $id;
        $this->showModal = true;
        $this->isFormOpen = true;

        $record = Subject::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $this->name = $record->name;
        $this->code = $record->code;
        $this->type = $record->type;
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();

        if ($this->isEdit) {
            $record = Subject::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->editingId)
                ->firstOrFail();
            $record->update([
                'name' => $this->name,
                'code' => $this->code,
                'type' => $this->type,
            ]);
            session()->flash('message', 'Mata Pelajaran berhasil diperbarui.');
        } else {
            Subject::create([
                'school_id' => $schoolId,
                'name' => $this->name,
                'code' => $this->code,
                'type' => $this->type,
            ]);
            session()->flash('message', 'Mata Pelajaran berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        $record = Subject::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();

        if (
            Schedule::query()->where('subject_id', $record->id)->exists()
            || Grade::query()->where('subject_id', $record->id)->exists()
        ) {
            session()->flash('error', 'Mata pelajaran yang sudah dipakai pada jadwal atau nilai tidak dapat dihapus.');

            return;
        }

        $record->delete();
        session()->flash('message', 'Mata Pelajaran berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'code', 'type', 'showModal', 'isFormOpen', 'isEdit', 'editingId']);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $subjects = Subject::where('school_id', $schoolId)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedType !== '', function ($query) {
                $query->where('type', $this->selectedType);
            })
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.subject.index', [
            'subjects' => $subjects,
        ]);
    }
}
