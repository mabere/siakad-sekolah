<?php

namespace App\Livewire\Admin\Master\Teacher;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Teacher;
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

    public bool $isFormOpen = false;

    public bool $isEdit = false;

    public int|string|null $editingId = null;

    // Form fields
    public ?string $nip = null;

    public ?string $name = null;

    public ?string $gender = null;

    public ?string $phone = null;

    public bool $is_active = true;

    // Search
    public string $search = '';

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $schoolId = app(CurrentSchool::class)->id();
        $rules = [
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:L,P',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ];

        $rules['nip'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('teachers', 'nip')
                ->where('school_id', $schoolId)
                ->ignore($this->editingId),
        ];

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
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

        $record = Teacher::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $this->nip = $record->nip;
        $this->name = $record->name;
        $this->gender = $record->gender;
        $this->phone = $record->phone;
        $this->is_active = $record->is_active;
    }

    public function save(): void
    {
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();

        if ($this->isEdit) {
            $record = Teacher::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->editingId)
                ->firstOrFail();
            $record->update([
                'nip' => $this->nip,
                'name' => $this->name,
                'gender' => $this->gender,
                'phone' => $this->phone,
                'is_active' => $this->is_active,
            ]);

            if ($record->user) {
                $record->user->update(['is_active' => $this->is_active]);
            }

            session()->flash('message', 'Data Guru berhasil diperbarui.');
        } else {
            Teacher::create([
                'school_id' => $schoolId,
                'nip' => $this->nip,
                'name' => $this->name,
                'gender' => $this->gender,
                'phone' => $this->phone,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Data Guru berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        $record = Teacher::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();

        if ($record->user_id
            || Classroom::query()->where('teacher_id', $record->id)->exists()
            || Schedule::query()->where('teacher_id', $record->id)->exists()) {
            session()->flash('error', 'Guru yang sudah memiliki akun, kelas, atau jadwal tidak dapat dihapus. Nonaktifkan data guru sebagai gantinya.');

            return;
        }

        $record->delete();
        session()->flash('message', 'Data Guru berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['nip', 'name', 'gender', 'phone', 'is_active', 'isFormOpen', 'isEdit', 'editingId']);
        $this->is_active = true;
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $teachers = Teacher::where('school_id', $schoolId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nip', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(7);

        return view('livewire.admin.master.teacher.index', [
            'teachers' => $teachers,
        ]);
    }
}
