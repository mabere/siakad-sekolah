<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CounselingRecord;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\Teacher;
use App\Models\ViolationMaster;
use App\Support\CurrentSchool;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.teacher')]
class Counseling extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'violations'; // 'violations', 'counseling_journals', 'student_points_recap'

    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    // --- Tab 1: Violations State ---
    public string $violationCategoryFilter = '';

    public bool $showViolationModal = false;

    public ?int $editingViolationId = null;

    public string $violationStudentId = '';

    public ?int $violationMasterId = null;

    public string $violationCategory = 'Ringan';

    public int $violationPoints = 5;

    public string $violationEventDate = '';

    public string $violationActionTaken = '';

    public string $violationNotes = '';

    // --- Tab 2: Counseling Journals State ---
    public string $counselingTypeFilter = '';

    public string $counselingStatusFilter = '';

    public bool $showCounselingModal = false;

    public ?int $editingCounselingId = null;

    public string $counselingStudentId = '';

    public string $counselingType = 'Bimbingan Pribadi';

    public string $counselingDate = '';

    public string $counselingTime = '';

    public string $counselingProblem = '';

    public string $counselingSolution = '';

    public string $counselingStatus = 'Proses';

    // --- Tab 3: Recap State ---
    public string $recapSearch = '';

    public string $recapClassroomId = '';

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $activeYear?->id;

        $teacher = Teacher::where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        $this->teacherId = $teacher?->id;

        // abort_if(! $this->teacherId, 403);

        $this->violationEventDate = now()->toDateString();
        $this->counselingDate = now()->toDateString();
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['violations', 'counseling_journals', 'student_points_recap'])) {
            $this->activeTab = $tab;
        }
    }

    // --- Tab 1: Violation Methods ---
    public function openViolationModal(?int $id = null): void
    {
        $this->resetValidation();

        $schoolId = app(CurrentSchool::class)->id();
        $studentsQuery = Student::where('school_id', $schoolId)->where('status', 'Aktif');
        if (session('active_role') === 'Wali Kelas') {
            $classrooms = Classroom::where('school_id', $schoolId)->where('academic_year_id', $this->activeYearId)->where('teacher_id', $this->teacherId)->pluck('id');
            $studentsQuery->whereIn('classroom_id', $classrooms);
        }
        $students = $studentsQuery->orderBy('name')->get();

        if ($id) {
            $vio = $this->accessibleViolationQuery()->whereKey($id)->first();
            if (!$vio) {
                return;
            }

            $this->editingViolationId = $vio->id;
            $this->violationStudentId = (string) $vio->student_id;
            $this->violationMasterId = $vio->violation_master_id;
            $this->violationCategory = $vio->category;
            $this->violationPoints = $vio->points;
            $this->violationEventDate = $vio->event_date ?? now()->toDateString();
            $this->violationActionTaken = $vio->action_taken ?? '';
            $this->violationNotes = $vio->notes ?? '';
        } else {
            $this->editingViolationId = null;
            $this->violationStudentId = !$students->isEmpty() ? (string) $students->first()->id : '';
            $this->violationMasterId = null;
            $this->violationCategory = 'Ringan';
            $this->violationPoints = 5;
            $this->violationEventDate = now()->toDateString();
            $this->violationActionTaken = '';
            $this->violationNotes = '';
        }

        $this->showViolationModal = true;
    }

    public function closeViolationModal(): void
    {
        $this->showViolationModal = false;
        $this->editingViolationId = null;
    }

    public function updatedViolationCategory(string $value): void
    {
        if ($value === 'Ringan') {
            $this->violationPoints = 5;
        } elseif ($value === 'Sedang') {
            $this->violationPoints = 15;
        } elseif ($value === 'Berat') {
            $this->violationPoints = 35;
        }
    }

    public function updatedViolationMasterId(int|string|null $value): void
    {
        if ($value) {
            $master = ViolationMaster::query()
                ->where('school_id', app(CurrentSchool::class)->id())
                ->whereKey($value)
                ->first();
            if ($master) {
                $this->violationCategory = $master->category;
                $this->violationPoints = $master->default_points;
            }
        }
    }

    public function saveViolation(): void
    {
        $validated = Validator::make([
            'student_id' => $this->violationStudentId,
            'violation_master_id' => $this->violationMasterId,
            'category' => $this->violationCategory,
            'points' => $this->violationPoints,
            'event_date' => $this->violationEventDate,
            'action_taken' => $this->violationActionTaken,
            'notes' => $this->violationNotes,
        ], [
            'student_id' => ['required', 'integer'],
            'violation_master_id' => ['required', 'integer'],
            'category' => ['required', 'string', 'in:Ringan,Sedang,Berat'],
            'points' => ['required', 'integer', 'min:1', 'max:500'],
            'event_date' => ['nullable', 'date'],
            'action_taken' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $student = $this->accessibleStudentQuery()->whereKey($validated['student_id'])->first();
        $master = ViolationMaster::query()
            ->where('school_id', $schoolId)
            ->whereKey($validated['violation_master_id'])
            ->first();
        if (!$student || !$master) {
            abort(403);
        }

        $vio = $this->editingViolationId
            ? $this->accessibleViolationQuery()->whereKey($this->editingViolationId)->first()
            : new StudentViolation;
        if ($this->editingViolationId && !$vio) {
            abort(403);
        }
        $vio->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'student_id' => $student->id,
            'reporter_teacher_id' => $this->teacherId,
            'violation_master_id' => $master->id,
            'category' => $validated['category'],
            'points' => $validated['points'],
            'event_date' => $validated['event_date'] ?: null,
            'action_taken' => !empty($validated['action_taken']) ? trim($validated['action_taken']) : null,
            'notes' => !empty($validated['notes']) ? trim($validated['notes']) : null,
        ]);
        $vio->save();

        session()->flash('violation_success', 'Poin kedisiplinan & pelanggaran siswa berhasil dicatat.');
        $this->closeViolationModal();
    }

    public function deleteViolation(int $id): void
    {
        $vio = $this->accessibleViolationQuery()->whereKey($id)->first();
        if ($vio) {
            $vio->delete();
            session()->flash('violation_success', 'Data pelanggaran siswa berhasil dihapus.');
        }
    }

    // --- Tab 2: Counseling Methods ---
    public function openCounselingModal(?int $id = null): void
    {
        $this->resetValidation();

        $schoolId = app(CurrentSchool::class)->id();
        $studentsQuery = Student::where('school_id', $schoolId)->where('status', 'Aktif');
        if (session('active_role') === 'Wali Kelas') {
            $classrooms = Classroom::where('school_id', $schoolId)->where('academic_year_id', $this->activeYearId)->where('teacher_id', $this->teacherId)->pluck('id');
            $studentsQuery->whereIn('classroom_id', $classrooms);
        }
        $students = $studentsQuery->orderBy('name')->get();

        if ($id) {
            $cr = $this->accessibleCounselingQuery()->whereKey($id)->first();
            if (!$cr) {
                return;
            }

            $this->editingCounselingId = $cr->id;
            $this->counselingStudentId = (string) $cr->student_id;
            $this->counselingType = $cr->counseling_type;
            $this->counselingDate = $cr->counseling_date ?? now()->toDateString();
            $this->counselingTime = $cr->counseling_time ? Carbon::parse($cr->counseling_time)->format('H:i') : '';
            $this->counselingProblem = $cr->problem_description;
            $this->counselingSolution = $cr->solution_plan ?? '';
            $this->counselingStatus = $cr->status;
        } else {
            $this->editingCounselingId = null;
            $this->counselingStudentId = !$students->isEmpty() ? (string) $students->first()->id : '';
            $this->counselingType = 'Bimbingan Pribadi';
            $this->counselingDate = now()->toDateString();
            $this->counselingTime = '';
            $this->counselingProblem = '';
            $this->counselingSolution = '';
            $this->counselingStatus = 'Proses';
        }

        $this->showCounselingModal = true;
    }

    public function closeCounselingModal(): void
    {
        $this->showCounselingModal = false;
        $this->editingCounselingId = null;
    }

    public function saveCounseling(): void
    {
        $validated = Validator::make([
            'student_id' => $this->counselingStudentId,
            'counseling_type' => $this->counselingType,
            'counseling_date' => $this->counselingDate,
            'counseling_time' => $this->counselingTime,
            'problem_description' => $this->counselingProblem,
            'solution_plan' => $this->counselingSolution,
            'status' => $this->counselingStatus,
        ], [
            'student_id' => ['required', 'integer'],
            'counseling_type' => ['required', 'string', 'in:Bimbingan Pribadi,Bimbingan Belajar,Bimbingan Sosial,Bimbingan Karir'],
            'counseling_date' => ['nullable', 'date'],
            'counseling_time' => ['nullable'],
            'problem_description' => ['required', 'string', 'max:2000'],
            'solution_plan' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', 'in:Proses,Selesai,Rujukan'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $student = $this->accessibleStudentQuery()->whereKey($validated['student_id'])->first();
        if (!$student) {
            abort(403);
        }

        $cr = $this->editingCounselingId
            ? $this->accessibleCounselingQuery()->whereKey($this->editingCounselingId)->first()
            : new CounselingRecord;
        if ($this->editingCounselingId && !$cr) {
            abort(403);
        }
        $cr->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'student_id' => $student->id,
            'counselor_teacher_id' => $this->teacherId,
            'counseling_type' => $validated['counseling_type'],
            'counseling_date' => $validated['counseling_date'] ?: null,
            'counseling_time' => $validated['counseling_time'] ?: null,
            'problem_description' => trim($validated['problem_description']),
            'solution_plan' => !empty($validated['solution_plan']) ? trim($validated['solution_plan']) : null,
            'status' => $validated['status'],
        ]);
        $cr->save();

        session()->flash('counseling_success', 'Jurnal Bimbingan Konseling (BK) berhasil disimpan.');
        $this->closeCounselingModal();
    }

    public function deleteCounseling(int $id): void
    {
        $cr = $this->accessibleCounselingQuery()->whereKey($id)->first();
        if ($cr) {
            $cr->delete();
            session()->flash('counseling_success', 'Catatan Bimbingan Konseling dihapus.');
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = $this->activeYearId
            ? AcademicYear::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->activeYearId)
                ->first()
            : null;

        $classroomsQuery = Classroom::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId);

        if (session('active_role') === 'Wali Kelas') {
            $classroomsQuery->where('teacher_id', $this->teacherId);
        }

        $classrooms = $classroomsQuery->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $homeroomClassroomIds = [];
        if (session('active_role') === 'Wali Kelas') {
            $homeroomClassroomIds = $classrooms->pluck('id')->toArray();
        }

        // Violations Query for Tab 1
        $violationsQuery = StudentViolation::with(['student', 'reporterTeacher', 'violationMaster'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId);

        if (session('active_role') === 'Wali Kelas') {
            $violationsQuery->whereHas('student', function ($q) use ($homeroomClassroomIds) {
                $q->whereIn('classroom_id', $homeroomClassroomIds);
            });
        }

        if ($this->violationCategoryFilter) {
            $violationsQuery->where('category', $this->violationCategoryFilter);
        }

        $violations = $violationsQuery
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(7);

        // Counseling Query for Tab 2
        $counselingsQuery = CounselingRecord::with(['student', 'counselorTeacher'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId);

        if (session('active_role') === 'Wali Kelas') {
            $counselingsQuery->whereHas('student', function ($q) use ($homeroomClassroomIds) {
                $q->whereIn('classroom_id', $homeroomClassroomIds);
            });
        }

        if ($this->counselingTypeFilter) {
            $counselingsQuery->where('counseling_type', $this->counselingTypeFilter);
        }

        if ($this->counselingStatusFilter) {
            $counselingsQuery->where('status', $this->counselingStatusFilter);
        }

        $counselings = $counselingsQuery
            ->orderBy('counseling_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(7);

        // Recap Query for Tab 3
        $recapQuery = Student::withCount([
            'violations as total_violations' => function ($q): void {
                $q->where('academic_year_id', $this->activeYearId);
            },
            'counselings as total_counselings' => function ($q): void {
                $q->where('academic_year_id', $this->activeYearId);
            },
        ])
            ->withSum([
                'violations as total_points' => function ($q): void {
                    $q->where('academic_year_id', $this->activeYearId);
                },
            ], 'points')
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif');

        if (session('active_role') === 'Wali Kelas') {
            $recapQuery->whereIn('classroom_id', $homeroomClassroomIds);
        }

        if ($this->recapClassroomId) {
            $recapQuery->where('classroom_id', $this->recapClassroomId);
        }

        if ($this->recapSearch) {
            $recapQuery->where(function ($q): void {
                $q->where('name', 'like', '%' . $this->recapSearch . '%')
                    ->orWhere('nisn', 'like', '%' . $this->recapSearch . '%');
            });
        }

        $studentRecap = $recapQuery->orderBy('total_points', 'desc')->paginate(7);

        $violationMasters = ViolationMaster::where('school_id', $schoolId)->orderBy('category')->orderBy('name')->get();

        return view('livewire.teacher.counseling', [
            'activeYear' => $activeYear,
            'classrooms' => $classrooms,
            'violations' => $violations,
            'counselings' => $counselings,
            'studentRecap' => $studentRecap,
            'violationMasters' => $violationMasters,
        ]);
    }

    public function exportCsv(): StreamedResponse
    {
        $schoolId = app(CurrentSchool::class)->id();

        $recapQuery = Student::withCount([
            'violations as total_violations' => function ($q): void {
                $q->where('academic_year_id', $this->activeYearId);
            },
            'counselings as total_counselings' => function ($q): void {
                $q->where('academic_year_id', $this->activeYearId);
            },
        ])
            ->withSum([
                'violations as total_points' => function ($q): void {
                    $q->where('academic_year_id', $this->activeYearId);
                },
            ], 'points')
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif');

        if (session('active_role') === 'Wali Kelas') {
            $classrooms = Classroom::where('school_id', $schoolId)->where('academic_year_id', $this->activeYearId)->where('teacher_id', $this->teacherId)->pluck('id');
            $recapQuery->whereIn('classroom_id', $classrooms);
        }

        if ($this->recapClassroomId) {
            $recapQuery->where('classroom_id', $this->recapClassroomId);
        }

        if ($this->recapSearch) {
            $recapQuery->where(function ($q): void {
                $q->where('name', 'like', '%' . $this->recapSearch . '%')
                    ->orWhere('nisn', 'like', '%' . $this->recapSearch . '%');
            });
        }

        return response()->streamDownload(function () use ($recapQuery): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['NISN', 'Nama Siswa', 'Total Pelanggaran', 'Total Sesi BK', 'Total Poin']);

            foreach ($recapQuery->orderBy('total_points', 'desc')->cursor() as $student) {
                $values = [
                    $student->nisn,
                    $student->name,
                    $student->total_violations,
                    $student->total_counselings,
                    $student->total_points ?? 0,
                ];

                foreach ($values as $index => $value) {
                    if (is_string($value) && preg_match('/^[=+\\-@]/', $value) === 1) {
                        $values[$index] = "'" . $value;
                    }
                }

                fputcsv($handle, $values);
            }

            fclose($handle);
        }, 'Rekap_Kedisiplinan_' . date('Ymd_His') . '.csv');
    }

    /** @return Builder<Student> */
    private function accessibleStudentQuery(): Builder
    {
        $query = Student::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('status', 'Aktif');

        if (session('active_role') === 'Wali Kelas') {
            $query->whereIn('classroom_id', Classroom::query()
                ->where('school_id', app(CurrentSchool::class)->id())
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId)
                ->pluck('id'));
        }

        return $query;
    }

    /** @return Builder<StudentViolation> */
    private function accessibleViolationQuery(): Builder
    {
        return StudentViolation::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('reporter_teacher_id', $this->teacherId);
    }

    /** @return Builder<CounselingRecord> */
    private function accessibleCounselingQuery(): Builder
    {
        return CounselingRecord::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('counselor_teacher_id', $this->teacherId);
    }
}
