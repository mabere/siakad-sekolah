<?php

namespace App\Livewire\Admin\Academic\Attendance;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?AcademicYear $activeYear = null;

    // Filters
    public string|int $filterClassroom = '';

    // State for attendance
    /** @var array<int|string, int> */
    public array $sickCounts = [];

    /** @var array<int|string, int> */
    public array $permissionCounts = [];

    /** @var array<int|string, int> */
    public array $absentCounts = [];

    /** @var array<int|string, string|null> */
    public array $notes = [];

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $firstClassroom = Classroom::where('school_id', $schoolId)->first();
        if ($firstClassroom) {
            $this->filterClassroom = $firstClassroom->id;
            $this->loadStudents();
        }
    }

    public function updatedFilterClassroom(): void
    {
        Validator::make(
            ['filterClassroom' => $this->filterClassroom],
            ['filterClassroom' => [
                'nullable',
                Rule::exists('classrooms', 'id')->where('school_id', app(CurrentSchool::class)->id()),
            ]],
        )->validate();

        $this->loadStudents();
    }

    public function loadStudents(): void
    {
        if (! $this->filterClassroom) {
            return;
        }

        if (! $this->activeYear) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();
        $students = Student::where('school_id', $schoolId)
            ->where('classroom_id', $this->filterClassroom)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $existingAttendances = Attendance::where('classroom_id', $this->filterClassroom)
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYear->id ?? 0)
            ->get()
            ->keyBy('student_id');

        $this->sickCounts = [];
        $this->permissionCounts = [];
        $this->absentCounts = [];
        $this->notes = [];

        foreach ($students as $student) {
            if ($existingAttendances->has($student->id)) {
                $this->sickCounts[$student->id] = $existingAttendances[$student->id]->sick;
                $this->permissionCounts[$student->id] = $existingAttendances[$student->id]->permission;
                $this->absentCounts[$student->id] = $existingAttendances[$student->id]->absent;
                $this->notes[$student->id] = $existingAttendances[$student->id]->notes;
            } else {
                $this->sickCounts[$student->id] = 0;
                $this->permissionCounts[$student->id] = 0;
                $this->absentCounts[$student->id] = 0;
                $this->notes[$student->id] = '';
            }
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $students = [];
        if ($this->filterClassroom) {
            $students = Student::where('school_id', $schoolId)
                ->where('classroom_id', $this->filterClassroom)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.admin.academic.attendance.index', [
            'classrooms' => $classrooms,
            'students' => $students,
        ]);
    }
}
