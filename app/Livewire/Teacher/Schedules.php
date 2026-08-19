<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class Schedules extends Component
{
    public ?AcademicYear $activeYear = null;

    /** @var array<string, array<int, array<string, mixed>>> */
    public array $schedules = [];

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->activeYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if ($this->activeYear) {
            $teacher = auth()->user()->teacher;

            if ($teacher) {
                // Fetch schedules for the active year and the logged-in teacher
                $rawSchedules = Schedule::with(['classroom', 'subject'])
                    ->where('school_id', $schoolId)
                    ->where('teacher_id', $teacher->id)
                    ->where('academic_year_id', $this->activeYear->id)
                    ->orderBy('start_time')
                    ->get();

                // Group by day
                $this->schedules = $rawSchedules->groupBy('day_of_week')->toArray();
            }
        }
    }

    public function render(): View
    {
        return view('livewire.teacher.schedules');
    }
}
