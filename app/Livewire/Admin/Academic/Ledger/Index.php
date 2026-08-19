<?php

namespace App\Livewire\Admin\Academic\Ledger;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?AcademicYear $activeYear = null;

    public string|int $filterClassroom = '';

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $firstClassroom = Classroom::where('school_id', $schoolId)->first();
        if ($firstClassroom) {
            $this->filterClassroom = $firstClassroom->id;
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $students = collect();
        $subjects = collect();
        $grades = collect();

        if ($this->filterClassroom && $this->activeYear) {
            $students = Student::where('school_id', $schoolId)
                ->where('classroom_id', $this->filterClassroom)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get();

            $subjects = Subject::where('school_id', $schoolId)
                ->orderBy('name')
                ->get();

            $grades = Grade::where('academic_year_id', $this->activeYear->id)
                ->where('school_id', $schoolId)
                ->where('classroom_id', $this->filterClassroom)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');
        }

        return view('livewire.admin.academic.ledger.index', [
            'classrooms' => $classrooms,
            'students' => $students,
            'subjects' => $subjects,
            'grades' => $grades,
        ]);
    }
}
