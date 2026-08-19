<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintRemedialEnrichmentController extends Controller
{
    public function __invoke(Request $request): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $school = app(CurrentSchool::class)->get();
        $type = $request->query('type', 'remedial'); // 'remedial' | 'enrichment' | 'both'

        $data = session('remedial_enrichment_active_package');
        abort_unless(is_array($data) && ! empty($data['package']), 404, 'Data paket remedial & pengayaan belum tersedia atau sesi telah berakhir.');

        $teacher = Teacher::query()
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        // Cari data Kepala Sekolah untuk tanda tangan
        $headmasterUser = User::role('Kepala Sekolah')
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $headmasterTeacher = null;
        if ($headmasterUser) {
            $headmasterTeacher = Teacher::where('user_id', $headmasterUser->id)->first();
        }

        return view('teacher.print-remedial-enrichment', [
            'type' => $type,
            'package' => $data['package'],
            'topic' => $data['topic'] ?? '',
            'subject' => $data['subject'] ?? 'Mata Pelajaran',
            'classroom' => $data['classroom'] ?? 'Kelas',
            'remedialStudents' => $data['remedial_students'] ?? [],
            'enrichmentStudents' => $data['enrichment_students'] ?? [],
            'school' => $school,
            'teacher' => $teacher,
            'headmasterTeacher' => $headmasterTeacher,
            'headmasterUser' => $headmasterUser,
            'currentDate' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
        ]);
    }
}
