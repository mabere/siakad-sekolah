<?php

namespace App\Livewire\Admin;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Schedule;
use App\Models\Slider;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $activeYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        // Statistics
        $totalStudents = Student::where('school_id', $schoolId)->where('status', 'Aktif')->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $totalClassrooms = Classroom::where('school_id', $schoolId)->count();
        $totalSubjects = Subject::where('school_id', $schoolId)->count();

        // Today's Schedules
        $todayStr = Carbon::today()->isoFormat('dddd'); // e.g., 'Senin'
        // Need to translate standard PHP day to our enum (Senin, Selasa, etc.)
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $todayEnglish = Carbon::today()->format('l');
        $todayEnum = $dayMap[$todayEnglish] ?? 'Senin';

        $todaySchedules = Schedule::with(['subject', 'teacher', 'classroom'])
            ->where('school_id', $schoolId)
            ->when($activeYear, fn ($query) => $query->where('academic_year_id', $activeYear->id))
            ->where('day_of_week', $todayEnum)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $attendanceTotals = Attendance::query()
            ->where('school_id', $schoolId)
            ->when($activeYear, fn ($query) => $query->where('academic_year_id', $activeYear->id))
            ->selectRaw('COUNT(*) as recorded_students')
            ->selectRaw('COALESCE(SUM(sick), 0) as sick_total')
            ->selectRaw('COALESCE(SUM(permission), 0) as permission_total')
            ->selectRaw('COALESCE(SUM(absent), 0) as absent_total')
            ->first();

        $attendanceStats = [
            'recorded_students' => (int) ($attendanceTotals->recorded_students ?? 0),
            'sick' => (int) ($attendanceTotals->sick_total ?? 0),
            'permission' => (int) ($attendanceTotals->permission_total ?? 0),
            'absent' => (int) ($attendanceTotals->absent_total ?? 0),
        ];

        $viewData = [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalClassrooms' => $totalClassrooms,
            'totalSubjects' => $totalSubjects,
            'todaySchedules' => $todaySchedules,
            'attendanceStats' => $attendanceStats,
            'activeYear' => $activeYear,
            'todayName' => $todayEnum,
        ];

        $activeRole = session('active_role');

        // Data khusus Kesiswaan & Kepsek
        if (in_array($activeRole, ['Kepala Sekolah', 'Wakasek Kesiswaan'])) {
            $viewData['totalViolations'] = StudentViolation::where('school_id', $schoolId)
                ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                ->count();

            $viewData['totalAchievements'] = StudentAchievement::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
                ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                ->count();

            $viewData['recentViolations'] = StudentViolation::with(['student', 'violationMaster'])
                ->where('school_id', $schoolId)
                ->orderBy('event_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $viewData['recentAchievements'] = StudentAchievement::with('student')
                ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Data khusus Kurikulum & Kepsek
        if (in_array($activeRole, ['Kepala Sekolah', 'Wakasek Kurikulum'])) {
            $viewData['todayJournals'] = TeachingJournal::with(['teacher', 'subject', 'classroom'])
                ->where('school_id', $schoolId)
                ->whereDate('date', Carbon::today())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Data khusus Humas
        if ($activeRole === 'Wakasek Humas') {
            $viewData['totalPosts'] = Post::where('school_id', $schoolId)->count();
            $viewData['totalCategories'] = PostCategory::where('school_id', $schoolId)->count();
            $viewData['totalSliders'] = Slider::count();
        }

        // View Routing berdasarkan Role
        if ($activeRole === 'Kepala Sekolah') {
            return view('livewire.admin.executive-dashboard', $viewData);
        } elseif ($activeRole === 'Wakasek Kurikulum') {
            return view('livewire.admin.wakasek-kurikulum-dashboard', $viewData);
        } elseif ($activeRole === 'Wakasek Kesiswaan') {
            return view('livewire.admin.wakasek-kesiswaan-dashboard', $viewData);
        } elseif ($activeRole === 'Wakasek Sarana') {
            return view('livewire.admin.wakasek-sarana-dashboard', $viewData);
        } elseif ($activeRole === 'Wakasek Humas') {
            return view('livewire.admin.wakasek-humas-dashboard', $viewData);
        }

        return view('livewire.admin.dashboard', $viewData);
    }
}
