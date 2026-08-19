<?php

namespace App\Livewire\Admin\Master\Student;

use App\Models\Classroom;
use App\Models\Student;
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
    public ?string $nisn = null;

    public ?string $nis = null;

    public ?string $name = null;

    public ?string $gender = null;

    public ?string $birth_place = null;

    public ?string $birth_date = null;

    public ?string $religion = null;

    public ?string $address = null;

    public ?string $parent_phone = null;

    public string|int|null $classroom_id = null;

    public string $status = 'Aktif';

    // Search & Filter
    public string $search = '';

    public string $filter_classroom = '';

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $schoolId = app(CurrentSchool::class)->id();

        $rules = [
            'name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'parent_phone' => 'nullable|string|max:20',
            'classroom_id' => [
                'nullable',
                Rule::exists('classrooms', 'id')->where('school_id', $schoolId),
            ],
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
        ];

        $rules['nisn'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('students', 'nisn')
                ->where('school_id', $schoolId)
                ->ignore($this->editingId),
        ];
        $rules['nis'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('students', 'nis')
                ->where('school_id', $schoolId)
                ->ignore($this->editingId),
        ];

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClassroom(): void
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

        $record = Student::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $this->nisn = $record->nisn;
        $this->nis = $record->nis;
        $this->name = $record->name;
        $this->gender = $record->gender;
        $this->birth_place = $record->birth_place;
        $this->birth_date = $record->birth_date ? $record->birth_date->format('Y-m-d') : null;
        $this->religion = $record->religion;
        $this->address = $record->address;
        $this->parent_phone = $record->parent_phone;
        $this->classroom_id = $record->classroom_id;
        $this->status = $record->status;
    }

    public function save(): void
    {
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();

        if ($this->isEdit) {
            $record = Student::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->editingId)
                ->firstOrFail();
            $record->update([
                'nisn' => $this->nisn,
                'nis' => $this->nis,
                'name' => $this->name,
                'gender' => $this->gender,
                'birth_place' => $this->birth_place,
                'birth_date' => $this->birth_date,
                'religion' => $this->religion,
                'address' => $this->address,
                'parent_phone' => $this->parent_phone,
                'classroom_id' => $this->classroom_id,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Data Siswa berhasil diperbarui.');
        } else {
            Student::create([
                'school_id' => $schoolId,
                'nisn' => $this->nisn,
                'nis' => $this->nis,
                'name' => $this->name,
                'gender' => $this->gender,
                'birth_place' => $this->birth_place,
                'birth_date' => $this->birth_date,
                'religion' => $this->religion,
                'address' => $this->address,
                'parent_phone' => $this->parent_phone,
                'classroom_id' => $this->classroom_id,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Data Siswa berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        $record = Student::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $record->delete();
        session()->flash('message', 'Data Siswa berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'nisn',
            'nis',
            'name',
            'gender',
            'birth_place',
            'birth_date',
            'religion',
            'address',
            'parent_phone',
            'classroom_id',
            'isFormOpen',
            'isEdit',
            'editingId',
        ]);
        $this->status = 'Aktif';
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $students = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nisn', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filter_classroom, function ($query) {
                if ($this->filter_classroom === 'unassigned') {
                    $query->whereNull('classroom_id');
                } else {
                    $query->where('classroom_id', $this->filter_classroom);
                }
            })
            ->orderBy('name', 'asc')
            ->paginate(7);

        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('grade_level', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.admin.master.student.index', [
            'students' => $students,
            'classrooms' => $classrooms,
        ]);
    }
}
