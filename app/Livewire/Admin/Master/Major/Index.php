<?php

namespace App\Livewire\Admin\Master\Major;

use App\Models\Major;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
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
    public ?string $name = null;

    public ?string $code = null;

    public ?string $description = null;

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
                Rule::unique('majors', 'code')
                    ->where('school_id', $schoolId)
                    ->ignore($this->editingId),
            ],
            'description' => 'nullable|string',
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

        $record = Major::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $this->name = $record->name;
        $this->code = $record->code;
        $this->description = $record->description;
    }

    public function save(): void
    {
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();

        if ($this->isEdit) {
            $record = Major::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->editingId)
                ->firstOrFail();
            $record->update([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Jurusan berhasil diperbarui.');
        } else {
            Major::create([
                'school_id' => $schoolId,
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Jurusan berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        $record = Major::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $record->delete();
        session()->flash('message', 'Jurusan berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'code', 'description', 'isFormOpen', 'isEdit', 'editingId']);
    }

    public function render(): View
    {
        $school = app(CurrentSchool::class)->get();
        $isSmp = $school->level === 'SMP';

        $majors = Major::where('school_id', $school->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.admin.master.major.index', [
            'majors' => $majors,
            'isSmp' => $isSmp,
        ]);
    }
}
