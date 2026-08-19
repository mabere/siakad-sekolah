<?php

namespace App\Livewire\Admin\Master\Classroom;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
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
    public string $name = '';

    public string $grade_level = '';

    public string|int $academic_year_id = '';

    public string|int|null $major_id = null;

    public string|int|null $teacher_id = null;

    public ?string $student_needs = null;

    public ?string $available_facilities = null;

    public ?string $learning_environment = null;

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $school = app(CurrentSchool::class)->get();
        $schoolLevel = $school->level ?? 'SMP';

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classrooms', 'name')
                    ->where(fn ($query) => $query
                        ->where('school_id', $school->id)
                        ->where('academic_year_id', $this->academic_year_id))
                    ->ignore($this->editingId),
            ],
            'grade_level' => 'required|string|max:20',
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $school->id),
            ],
            // Jurusan wajib hanya untuk kelas 11 & 12 SMA/SMK.
            // Kelas 10 belum berjurusan, SD/SMP tidak pakai jurusan.
            'major_id' => (in_array($schoolLevel, ['SMA', 'SMK', 'TERPADU']) && in_array((string) $this->grade_level, ['11', '12']))
                ? ['required', Rule::exists('majors', 'id')->where('school_id', $school->id)]
                : ['nullable'],
            'teacher_id' => [
                'nullable',
                Rule::exists('teachers', 'id')
                    ->where('school_id', $school->id)
                    ->where('is_active', true),
            ],
            'student_needs' => 'nullable|string|max:2000',
            'available_facilities' => 'nullable|string|max:1500',
            'learning_environment' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        // Set default academic year to the active one
        $activeYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first();
        if ($activeYear) {
            $this->academic_year_id = $activeYear->id;
        }
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

        $record = Classroom::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();
        $this->name = $record->name;
        $this->grade_level = $record->grade_level;
        $this->academic_year_id = $record->academic_year_id;
        $this->major_id = $record->major_id;
        $this->teacher_id = $record->teacher_id;
        $this->student_needs = $record->student_needs;
        $this->available_facilities = $record->available_facilities;
        $this->learning_environment = $record->learning_environment;
    }

    public function save(): void
    {
        $this->validate();

        $school = app(CurrentSchool::class)->get();

        // Nullify major_id if SMP
        if ($school->level === 'SMP') {
            $this->major_id = null;
        }

        if ($this->isEdit) {
            $record = Classroom::query()
                ->where('school_id', $school->id)
                ->whereKey($this->editingId)
                ->firstOrFail();
            $record->update([
                'name' => $this->name,
                'grade_level' => $this->grade_level,
                'academic_year_id' => $this->academic_year_id,
                // Kosongkan jurusan jika kelas 10, SD, atau SMP
                'major_id' => $this->shouldRequireMajor() ? $this->major_id : null,
                'teacher_id' => $this->teacher_id,
                'student_needs' => $this->nullableTrimmed($this->student_needs),
                'available_facilities' => $this->nullableTrimmed($this->available_facilities),
                'learning_environment' => $this->nullableTrimmed($this->learning_environment),
            ]);
            session()->flash('message', 'Kelas berhasil diperbarui.');
        } else {
            Classroom::create([
                'school_id' => $school->id,
                'name' => $this->name,
                'grade_level' => $this->grade_level,
                'academic_year_id' => $this->academic_year_id,
                'major_id' => $this->shouldRequireMajor() ? $this->major_id : null,
                'teacher_id' => $this->teacher_id,
                'student_needs' => $this->nullableTrimmed($this->student_needs),
                'available_facilities' => $this->nullableTrimmed($this->available_facilities),
                'learning_environment' => $this->nullableTrimmed($this->learning_environment),
            ]);
            session()->flash('message', 'Kelas berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        $record = Classroom::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();

        if (Student::query()->where('classroom_id', $record->id)->exists()
            || Schedule::query()->where('classroom_id', $record->id)->exists()
            || Attendance::query()->where('classroom_id', $record->id)->exists()
            || Grade::query()->where('classroom_id', $record->id)->exists()) {
            session()->flash('error', 'Kelas yang sudah memiliki siswa atau data akademik tidak dapat dihapus.');

            return;
        }

        $record->delete();
        session()->flash('message', 'Kelas berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'name',
            'grade_level',
            'major_id',
            'teacher_id',
            'student_needs',
            'available_facilities',
            'learning_environment',
            'isFormOpen',
            'isEdit',
            'editingId',
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $activeYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first();
        if ($activeYear) {
            $this->academic_year_id = $activeYear->id;
        }
    }

    /**
     * Jurusan wajib hanya untuk kelas 11 & 12 di SMA/SMK/TERPADU.
     * Kelas 10 belum berjurusan (penjurusan dilakukan di awal kelas 11).
     */
    public function shouldRequireMajor(): bool
    {
        $school = app(CurrentSchool::class)->get();
        $schoolLevel = $school->level ?? 'SMP';

        return in_array($schoolLevel, ['SMA', 'SMK', 'TERPADU'])
            && in_array((string) $this->grade_level, ['11', '12']);
    }

    private function nullableTrimmed(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function render(): View
    {
        $school = app(CurrentSchool::class)->get();

        $classrooms = Classroom::with(['academicYear', 'major', 'teacher'])
            ->where('school_id', $school->id ?? 0)
            ->orderBy('grade_level', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $academicYears = AcademicYear::where('school_id', $school->id ?? 0)->orderBy('name', 'desc')->get();
        $majors = Major::where('school_id', $school->id ?? 0)->orderBy('name', 'asc')->get();
        $teachers = Teacher::where('school_id', $school->id ?? 0)->where('is_active', true)->orderBy('name', 'asc')->get();

        $schoolLevel = $school->level ?? 'SMP';

        return view('livewire.admin.master.classroom.index', [
            'classrooms' => $classrooms,
            'academicYears' => $academicYears,
            'majors' => $majors,
            'teachers' => $teachers,
            'schoolLevel' => $schoolLevel,
            'isSd' => $schoolLevel === 'SD',
            'isSmp' => $schoolLevel === 'SMP',
            'isSmaSmk' => in_array($schoolLevel, ['SMA', 'SMK']),
            'isTerpadu' => $schoolLevel === 'TERPADU',
        ]);
    }
}
