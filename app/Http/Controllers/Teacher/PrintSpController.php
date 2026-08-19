<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintSpController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $studentId): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $school = app(CurrentSchool::class)->get();
        $activeYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->firstOrFail();

        $student = Student::with([
            'classroom.teacher',
            'violations' => function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id)
                    ->orderBy('event_date', 'asc');
            },
            'violations.violationMaster',
        ])
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);

        $totalPoints = $student->violations->sum('points');

        // Logic SP Level (bisa disesuaikan)
        $spLevel = 1;
        if ($totalPoints >= 100) {
            $spLevel = 3;
        } elseif ($totalPoints >= 75) {
            $spLevel = 2;
        }

        return view('teacher.print-sp', [
            'student' => $student,
            'school' => $school,
            'totalPoints' => $totalPoints,
            'spLevel' => $spLevel,
            'date' => Carbon::now()->translatedFormat('d F Y'),
        ]);
    }
}
