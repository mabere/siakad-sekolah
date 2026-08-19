<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CounselingRecord;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class Dashboard extends Component
{
    public int $todaySchedulesCount = 0;

    public int $totalClassesCount = 0;

    public bool $isEwsEnabled = false;

    /** @var array<int, array<string, mixed>> */
    public array $redZoneStudents = [];

    /** @var array<int, array<string, mixed>> */
    public array $yellowZoneStudents = [];

    /** @var array<int, array<string, mixed>> */
    public array $upcomingCounselings = [];

    /** @var array<string, mixed> */
    public array $topViolationsData = [];

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $activeYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
        $teacher = Teacher::query()
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        if ($activeYear && $teacher) {
            $dayMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $today = $dayMap[Carbon::today()->format('l')];

            $this->todaySchedulesCount = Schedule::query()
                ->where('school_id', $schoolId)
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('day_of_week', $today)
                ->count();

            $this->totalClassesCount = Schedule::query()
                ->where('school_id', $schoolId)
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->distinct()
                ->count('classroom_id');

            // EWS Logic
            $activeRole = session('active_role');
            if (in_array($activeRole, ['Guru BK', 'Wakasek Kesiswaan', 'Kepala Sekolah', 'Wali Kelas'])) {
                $this->isEwsEnabled = true;

                $studentsQuery = Student::where('school_id', $schoolId)
                    ->where('status', 'Aktif')
                    ->withSum([
                        'violations as total_points' => function ($q) use ($activeYear) {
                            $q->where('academic_year_id', $activeYear->id);
                        },
                    ], 'points');

                if ($activeRole === 'Wali Kelas') {
                    $homeroomClassroomIds = Classroom::where('school_id', $schoolId)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('teacher_id', $teacher->id)
                        ->pluck('id');
                    $studentsQuery->whereIn('classroom_id', $homeroomClassroomIds);
                }

                $students = $studentsQuery->get();

                $this->redZoneStudents = $students->where('total_points', '>=', 50)->sortByDesc('total_points')->take(5)->values()->toArray();
                $this->yellowZoneStudents = $students->where('total_points', '>=', 20)->where('total_points', '<', 50)->sortByDesc('total_points')->take(5)->values()->toArray();

                $counselingQuery = CounselingRecord::with('student')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('status', 'Proses')
                    ->where('counseling_date', '>=', Carbon::today())
                    ->orderBy('counseling_date')
                    ->orderBy('counseling_time');

                if ($activeRole === 'Wali Kelas') {
                    $counselingQuery->whereHas('student', function ($q) use ($homeroomClassroomIds) {
                        $q->whereIn('classroom_id', $homeroomClassroomIds);
                    });
                }

                $this->upcomingCounselings = $counselingQuery->take(5)->get()->toArray();

                // Grafik Top 5 Pelanggaran (Statistik Bulanan)
                $currentMonth = Carbon::now()->month;
                $topViolationsQuery = StudentViolation::where('school_id', $schoolId)
                    ->where('academic_year_id', $activeYear->id)
                    ->whereMonth('event_date', $currentMonth)
                    ->select('violation_master_id', DB::raw('count(*) as total'))
                    ->groupBy('violation_master_id')
                    ->orderBy('total', 'desc')
                    ->take(5);

                if ($activeRole === 'Wali Kelas') {
                    $topViolationsQuery->whereHas('student', function ($q) use ($homeroomClassroomIds) {
                        $q->whereIn('classroom_id', $homeroomClassroomIds);
                    });
                }

                $topVios = $topViolationsQuery->with('violationMaster')->get();
                $this->topViolationsData = [
                    'labels' => $topVios->map(fn ($v) => $v->violationMaster ? $v->violationMaster->name : 'Lainnya')->toArray(),
                    'data' => $topVios->pluck('total')->toArray(),
                ];
            }
        }
    }

    public function render(): View
    {
        return view('livewire.teacher.dashboard');
    }
}
