<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentViolation;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.student')]
class Dashboard extends Component
{
    public ?Student $student = null;

    public ?AcademicYear $activeYear = null;

    public string $activeYearName = '-';

    public string $classroomName = '-';

    public string $homeroomTeacherName = 'Belum Ditentukan';

    public float $attendancePercentage = 100.0;

    public int $totalViolationsCount = 0;

    public int $totalDemeritPoints = 0;

    public int $totalAchievementsCount = 0;

    public int $activeExamsCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $todaySchedules = [];

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if ($this->activeYear) {
            $this->activeYearName = $this->activeYear->name;
        }

        $this->student = Student::with(['classroom.homeroomTeacher', 'major'])
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $this->student) {
            return;
        }

        if ($this->student->classroom) {
            $this->classroomName = 'Kelas '.$this->student->classroom->grade_level.' '.$this->student->classroom->name;
            $this->homeroomTeacherName = $this->student->classroom->homeroomTeacher->name ?? 'Belum Ditentukan';
        }

        if ($this->activeYear) {
            // 1. Demerit Points & Violations
            $this->totalViolationsCount = StudentViolation::where('student_id', $this->student->id)
                ->where('academic_year_id', $this->activeYear->id)
                ->count();

            $this->totalDemeritPoints = (int) StudentViolation::where('student_id', $this->student->id)
                ->where('academic_year_id', $this->activeYear->id)
                ->sum('points');

            // 2. Achievements Count
            $this->totalAchievementsCount = StudentAchievement::where('student_id', $this->student->id)
                ->where('academic_year_id', $this->activeYear->id)
                ->count();

            // 3. Active Exams Count
            if ($this->student->classroom_id) {
                $this->activeExamsCount = Exam::where('classroom_id', $this->student->classroom_id)
                    ->where('academic_year_id', $this->activeYear->id)
                    ->where('status', 'Aktif')
                    ->count();
            }

            // 4. Cumulative Attendance Percentage
            $totalAttendances = Attendance::where('student_id', $this->student->id)->count();
            if ($totalAttendances > 0) {
                $hadirCount = Attendance::where('student_id', $this->student->id)->where('status', 'Hadir')->count();
                $this->attendancePercentage = round(($hadirCount / $totalAttendances) * 100, 1);
            }

            // 5. Today Schedules
            $daysMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];

            $todayDayName = $daysMap[Carbon::now()->format('l')] ?? 'Senin';

            if ($this->student->classroom_id) {
                $this->todaySchedules = Schedule::with(['subject', 'teacher'])
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $this->activeYear->id)
                    ->where('classroom_id', $this->student->classroom_id)
                    ->where('day_of_week', $todayDayName)
                    ->orderBy('start_time')
                    ->get()
                    ->toArray();
            }
        }
    }

    public function render(): View
    {
        return view('livewire.student.dashboard');
    }
}
