<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Student;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.student')]
class Schedules extends Component
{
    public ?AcademicYear $activeYear = null;

    public ?Student $student = null;

    public string $selectedDay = 'Semua'; // 'Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'

    /** @var array<string, array<int, array<string, mixed>>> */
    public array $schedulesByDay = [];

    /** @var array<int, string> */
    public array $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public string $todayDayName = 'Senin';

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->student = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        $daysMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $this->todayDayName = $daysMap[Carbon::now()->format('l')] ?? 'Senin';

        $this->loadSchedules();
    }

    public function selectDay(string $day): void
    {
        $this->selectedDay = $day;
    }

    public function loadSchedules(): void
    {
        $this->schedulesByDay = [];

        if (! $this->activeYear || ! $this->student || ! $this->student->classroom_id) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $rawSchedules = Schedule::with(['teacher', 'subject', 'classroom'])
            ->where('school_id', $schoolId)
            ->where('classroom_id', $this->student->classroom_id)
            ->where('academic_year_id', $this->activeYear->id)
            ->orderBy('start_time')
            ->get();

        // Group by 'day_of_week'
        foreach ($this->daysList as $dayName) {
            $dayScheds = $rawSchedules->filter(function ($item) use ($dayName) {
                return strtolower($item->day_of_week) === strtolower($dayName);
            })->values();

            if (! $dayScheds->isEmpty()) {
                $this->schedulesByDay[$dayName] = $dayScheds->toArray();
            }
        }
    }

    public function render(): View
    {
        return view('livewire.student.schedules');
    }
}
