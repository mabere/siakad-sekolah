<?php

namespace App\Livewire\Admin\Academic\Rombel;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string|int $targetClassroom = '';

    public string|int $sourceFilter = 'unassigned';

    /** @var array<int, int|string> */
    public array $selectedAvailableStudents = [];

    /** @var array<int, int|string> */
    public array $selectedAssignedStudents = [];

    public string $searchAvailable = '';

    public string $searchAssigned = '';

    public function mount(): void
    {
        $this->targetClassroom = $this->activeClassroomsQuery()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->value('id') ?? '';
    }

    public function updatedTargetClassroom(): void
    {
        $this->validateClassroomSelection($this->targetClassroom, true);
        $this->reset(['selectedAvailableStudents', 'selectedAssignedStudents', 'searchAvailable', 'searchAssigned']);
        $this->resetPage(pageName: 'availablePage');
        $this->resetPage(pageName: 'assignedPage');

        if ((string) $this->sourceFilter === (string) $this->targetClassroom) {
            $this->sourceFilter = 'unassigned';
        }
    }

    public function updatedSourceFilter(): void
    {
        if ($this->sourceFilter !== 'unassigned') {
            $this->validateClassroomSelection($this->sourceFilter);
        }

        $this->reset(['selectedAvailableStudents', 'searchAvailable']);
        $this->resetPage(pageName: 'availablePage');

        if ((string) $this->sourceFilter === (string) $this->targetClassroom) {
            $this->sourceFilter = 'unassigned';
        }
    }

    public function updatedSearchAvailable(): void
    {
        $this->resetPage(pageName: 'availablePage');
    }

    public function updatedSearchAssigned(): void
    {
        $this->resetPage(pageName: 'assignedPage');
    }

    public function assignStudents(): void
    {
        $this->validateClassroomSelection($this->targetClassroom);
        $selectedIds = $this->validatedStudentIds('selectedAvailableStudents');
        $schoolId = app(CurrentSchool::class)->id();

        DB::transaction(function () use ($schoolId, $selectedIds): void {
            $target = $this->activeClassroomsQuery()->lockForUpdate()->findOrFail($this->targetClassroom);
            $students = Student::query()
                ->where('school_id', $schoolId)
                ->where('status', 'Aktif')
                ->when(
                    $this->sourceFilter === 'unassigned',
                    fn ($query) => $query->whereNull('classroom_id'),
                    fn ($query) => $query->where('classroom_id', $this->sourceFilter),
                )
                ->whereIn('id', $selectedIds)
                ->lockForUpdate()
                ->get();

            if ($students->count() !== count($selectedIds)) {
                throw ValidationException::withMessages([
                    'selectedAvailableStudents' => 'Daftar siswa berubah. Muat ulang halaman dan pilih kembali.',
                ]);
            }

            Student::query()
                ->where('school_id', $schoolId)
                ->whereIn('id', $students->modelKeys())
                ->update(['classroom_id' => $target->id]);

            session()->flash('message', $students->count().' siswa berhasil dimasukkan ke kelas '.$target->name.'.');
        });

        $this->reset('selectedAvailableStudents');
    }

    public function removeStudents(): void
    {
        $this->validateClassroomSelection($this->targetClassroom);
        $selectedIds = $this->validatedStudentIds('selectedAssignedStudents');
        $schoolId = app(CurrentSchool::class)->id();

        DB::transaction(function () use ($schoolId, $selectedIds): void {
            $students = Student::query()
                ->where('school_id', $schoolId)
                ->where('classroom_id', $this->targetClassroom)
                ->where('status', 'Aktif')
                ->whereIn('id', $selectedIds)
                ->lockForUpdate()
                ->get();

            if ($students->count() !== count($selectedIds)) {
                throw ValidationException::withMessages([
                    'selectedAssignedStudents' => 'Daftar siswa berubah. Muat ulang halaman dan pilih kembali.',
                ]);
            }

            Student::query()
                ->where('school_id', $schoolId)
                ->whereIn('id', $students->modelKeys())
                ->update(['classroom_id' => null]);

            session()->flash('message', $students->count().' siswa berhasil dikeluarkan dari kelas.');
        });

        $this->reset('selectedAssignedStudents');
    }

    /** @return array<int, int> */
    private function validatedStudentIds(string $field): array
    {
        $validated = Validator::make([$field => $this->{$field}], [
            $field => ['required', 'array', 'min:1', 'max:100'],
            $field.'.*' => ['required', 'integer', 'distinct'],
        ])->validate();

        return array_map('intval', $validated[$field]);
    }

    private function validateClassroomSelection(int|string|null $classroomId, bool $nullable = false): void
    {
        Validator::make(['classroom' => $classroomId], [
            'classroom' => [
                $nullable ? 'nullable' : 'required',
                Rule::exists('classrooms', 'id')
                    ->where('school_id', app(CurrentSchool::class)->id())
                    ->where('academic_year_id', $this->activeAcademicYearId()),
            ],
        ])->validate();
    }

    private function activeAcademicYearId(): int
    {
        return (int) AcademicYear::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('is_active', true)
            ->value('id');
    }

    /** @return Builder<Classroom> */
    private function activeClassroomsQuery(): Builder
    {
        return Classroom::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeAcademicYearId());
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $classrooms = $this->activeClassroomsQuery()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $availableStudentsQuery = Student::query()
            ->select(['id', 'name', 'nis', 'nisn', 'gender'])
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif');

        if ($this->sourceFilter === 'unassigned') {
            $availableStudentsQuery->whereNull('classroom_id');
        } elseif ($classrooms->contains('id', (int) $this->sourceFilter)) {
            $availableStudentsQuery->where('classroom_id', $this->sourceFilter);
        } else {
            $availableStudentsQuery->whereRaw('1 = 0');
        }

        $availableStudentsQuery->when($this->searchAvailable, function ($query): void {
            $search = trim($this->searchAvailable);
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('nisn', 'like', '%'.$search.'%')
                    ->orWhere('nis', 'like', '%'.$search.'%');
            });
        });

        $availableStudents = $availableStudentsQuery
            ->orderBy('name')
            ->paginate(25, pageName: 'availablePage');

        $assignedStudentsQuery = Student::query()
            ->select(['id', 'name', 'nis', 'nisn', 'gender'])
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif');

        if ($classrooms->contains('id', (int) $this->targetClassroom)) {
            $assignedStudentsQuery->where('classroom_id', $this->targetClassroom);
        } else {
            $assignedStudentsQuery->whereRaw('1 = 0');
        }

        $assignedStudentsQuery->when($this->searchAssigned, function ($query): void {
            $search = trim($this->searchAssigned);
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('nisn', 'like', '%'.$search.'%')
                    ->orWhere('nis', 'like', '%'.$search.'%');
            });
        });

        return view('livewire.admin.academic.rombel.index', [
            'classrooms' => $classrooms,
            'availableStudents' => $availableStudents,
            'assignedStudents' => $assignedStudentsQuery
                ->orderBy('name')
                ->paginate(25, pageName: 'assignedPage'),
        ]);
    }
}
