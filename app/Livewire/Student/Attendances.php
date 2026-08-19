<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\SubjectAttendance;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.student')]
class Attendances extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'daily'; // 'daily', 'subject'

    #[Locked]
    public ?int $activeYearId = null;

    public ?Student $student = null;

    public ?AcademicYear $activeYear = null;

    // Daily Attendance Stats
    public int $dailyHadir = 0;

    public int $dailySakit = 0;

    public int $dailyIzin = 0;

    public int $dailyAlpa = 0;

    public float $dailyPercentage = 100.0;

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $this->activeYear?->id;

        $this->student = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $this->student || ! $this->activeYearId) {
            return;
        }

        // Daily Attendance Summary from Attendance model (sick, permission, absent)
        $dailyRecords = Attendance::where('student_id', $this->student->id)
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->get();

        $this->dailySakit = (int) $dailyRecords->sum('sick');
        $this->dailyIzin = (int) $dailyRecords->sum('permission');
        $this->dailyAlpa = (int) $dailyRecords->sum('absent');

        // Estimate total effective school days or subject meeting attendance
        $subjectHadirCount = SubjectAttendance::where('student_id', $this->student->id)
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('status', 'Hadir')
            ->count();

        $totalSubjectCount = SubjectAttendance::where('student_id', $this->student->id)
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->count();

        if ($totalSubjectCount > 0) {
            $this->dailyHadir = $subjectHadirCount;
            $this->dailyPercentage = round(($subjectHadirCount / $totalSubjectCount) * 100, 1);
        } else {
            // Default 100 effective days benchmark
            $totalBenchmark = 100;
            $this->dailyHadir = max(0, $totalBenchmark - ($this->dailySakit + $this->dailyIzin + $this->dailyAlpa));
            $this->dailyPercentage = round(($this->dailyHadir / $totalBenchmark) * 100, 1);
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['daily', 'subject'])) {
            $this->activeTab = $tab;
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        // Daily Attendance Summary Record
        $dailyLogs = ($this->student && $this->activeYearId)
            ? Attendance::with(['classroom', 'academicYear'])
                ->where('student_id', $this->student->id)
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->paginate(15, ['*'], 'dailyPage')
            : collect();

        // Subject Attendance Logs
        $subjectLogs = ($this->student && $this->activeYearId)
            ? SubjectAttendance::with(['subject', 'teacher'])
                ->where('student_id', $this->student->id)
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->orderBy('date', 'desc')
                ->orderBy('meeting_number', 'desc')
                ->paginate(15, ['*'], 'subjectPage')
            : collect();

        return view('livewire.student.attendances', [
            'dailyLogs' => $dailyLogs,
            'subjectLogs' => $subjectLogs,
        ]);
    }
}
