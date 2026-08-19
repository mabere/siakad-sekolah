<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningDraft;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintLearningDraftController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int|string $draftId): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $school = app(CurrentSchool::class)->get();

        $draft = LearningDraft::with([
            'school',
            'academicYear',
            'teacher.user',
            'schedule.subject',
            'schedule.classroom',
        ])
            ->where('school_id', $schoolId)
            ->whereKey($draftId)
            ->firstOrFail();

        // Otorisasi: Pastikan guru hanya mencetak draf miliknya kecuali jika pengguna berstatus Admin/Kepsek
        $user = auth()->user();
        $isManager = $user && $user->hasAnyRole(['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum']);
        if (! $isManager && $draft->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak draf ini.');
        }

        // Cari data Kepala Sekolah untuk tanda tangan
        $headmasterUser = User::role('Kepala Sekolah')
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $headmasterTeacher = null;
        if ($headmasterUser) {
            $headmasterTeacher = Teacher::where('user_id', $headmasterUser->id)->first();
        }

        $teacher = $draft->teacher;
        $schedule = $draft->schedule;
        $output = is_array($draft->output) ? $draft->output : (json_decode((string) $draft->getRawOriginal('output'), true) ?? []);

        return view('teacher.print-learning-draft', [
            'draft' => $draft,
            'output' => $output,
            'school' => $school,
            'teacher' => $teacher,
            'schedule' => $schedule,
            'academicYear' => $draft->academicYear,
            'headmasterUser' => $headmasterUser,
            'headmasterTeacher' => $headmasterTeacher,
            'printDate' => Carbon::now()->translatedFormat('d F Y'),
        ]);
    }
}
