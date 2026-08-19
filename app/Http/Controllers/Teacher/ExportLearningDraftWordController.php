<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningDraft;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ExportLearningDraftWordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int|string $draftId): Response
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

        $user = auth()->user();
        $isManager = $user && $user->hasAnyRole(['Super Admin', 'Admin Sekolah', 'Kepala Sekolah', 'Wakasek Kurikulum']);
        if (! $isManager && $draft->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh draf ini.');
        }

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

        $htmlContent = view('teacher.print-learning-draft', [
            'draft' => $draft,
            'output' => $output,
            'school' => $school,
            'teacher' => $teacher,
            'schedule' => $schedule,
            'academicYear' => $draft->academicYear,
            'headmasterUser' => $headmasterUser,
            'headmasterTeacher' => $headmasterTeacher,
            'printDate' => Carbon::now()->translatedFormat('d F Y'),
            'isWordExport' => true,
        ])->render();

        $subjectName = $schedule?->subject?->name ?? 'Perangkat';
        $className = $schedule?->classroom?->name ?? 'Kelas';
        $filename = Str::slug('Modul-Ajar-'.$subjectName.'-'.$className.'-v'.$draft->version).'.doc';

        return response($htmlContent, 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
