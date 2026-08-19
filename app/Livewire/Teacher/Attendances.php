<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class Attendances extends Component
{
    public string $activeTab = 'subject'; // 'subject' or 'homeroom'

    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    #[Locked]
    public ?int $homeroomClassId = null;

    public bool $isHomeroomTeacher = false;

    // --- Tab 1: Subject Attendance State ---
    /** @var array<int, array<string, mixed>> */
    public array $schedules = [];

    public string $selectedScheduleId = '';

    public string $attendanceDate = '';

    public int $meetingNumber = 1;

    /** @var array<int|string, mixed> */
    public array $subjectAttendanceData = [];

    // --- Tab 2: Homeroom Attendance State ---
    /** @var array<int|string, mixed> */
    public array $attendanceData = [];

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->activeYearId = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->value('id');

        $this->teacherId = Teacher::query()
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->value('id');

        abort_unless($this->teacherId !== null, 403, 'Akun belum terhubung ke data guru aktif.');

        // abort_if(! $this->teacherId, 403);

        if (! $this->activeYearId || ! $this->teacherId) {
            return;
        }

        // Check Homeroom Status
        $this->homeroomClassId = Classroom::query()
            ->where('school_id', $schoolId)
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYearId)
            ->value('id');

        $this->isHomeroomTeacher = (bool) $this->homeroomClassId;

        // Load Teaching Schedules for Subject Attendance
        $this->schedules = Schedule::with(['classroom', 'subject'])
            ->where('school_id', $schoolId)
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYearId)
            ->get()
            ->unique(fn ($item) => $item->classroom_id.'-'.$item->subject_id)
            ->values()
            ->toArray();

        $this->attendanceDate = now()->toDateString();

        if (! empty($this->schedules)) {
            $this->selectedScheduleId = (string) $this->schedules[0]['id'];
            $this->loadSubjectAttendance();
        }

        // Load Homeroom Data if applicable
        if ($this->homeroomClassId) {
            $this->loadHomeroomAttendance();
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['subject', 'homeroom'])) {
            $this->activeTab = $tab;
        }
    }

    public function updatedSelectedScheduleId(): void
    {
        $this->loadSubjectAttendance();
    }

    public function updatedAttendanceDate(): void
    {
        $this->loadSubjectAttendance();
    }

    public function loadSubjectAttendance(): void
    {
        $this->subjectAttendanceData = [];

        if (! $this->selectedScheduleId || ! $this->activeYearId || ! $this->teacherId) {
            return;
        }

        $schedule = $this->accessibleScheduleQuery()
            ->with(['classroom', 'subject'])
            ->whereKey($this->selectedScheduleId)
            ->first();

        if (! $schedule) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $students = Student::query()
            ->select(['id', 'name', 'nisn'])
            ->where('school_id', $schoolId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $existingRecords = SubjectAttendance::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->where('schedule_id', $schedule->id)
            ->where('date', $this->attendanceDate)
            ->get()
            ->keyBy('student_id');

        // Derive default meeting number if existing record has it
        $firstRecord = $existingRecords->first();
        if ($firstRecord) {
            $this->meetingNumber = (int) $firstRecord->meeting_number;
        }

        foreach ($students as $student) {
            $record = $existingRecords->get($student->id);
            $this->subjectAttendanceData[$student->id] = [
                'status' => $record !== null ? $record->status : 'Hadir',
                'notes' => $record !== null ? $record->notes : '',
            ];
        }
    }

    public function setAllSubjectStatus(string $status): void
    {
        if (! in_array($status, ['Hadir', 'Sakit', 'Izin', 'Alpa'])) {
            return;
        }

        foreach ($this->subjectAttendanceData as $studentId => $data) {
            $this->subjectAttendanceData[$studentId]['status'] = $status;
        }
    }

    public function saveSubjectAttendance(): void
    {
        if (! $this->selectedScheduleId || ! $this->attendanceDate) {
            session()->flash('subject_attendance_error', 'Pilih jadwal pelajaran dan tanggal presensi.');

            return;
        }

        $schedule = $this->accessibleScheduleQuery()->whereKey($this->selectedScheduleId)->first();
        if (! $schedule) {
            session()->flash('subject_attendance_error', 'Jadwal tidak ditemukan.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $allowedStudentIds = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $schedule->classroom_id)
            ->where('status', 'Aktif')
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($schoolId, $schedule, $allowedStudentIds): void {
            foreach ($this->subjectAttendanceData as $studentId => $data) {
                if (! in_array((int) $studentId, $allowedStudentIds, true)) {
                    continue;
                }

                $status = in_array($data['status'] ?? '', ['Hadir', 'Sakit', 'Izin', 'Alpa'])
                    ? $data['status']
                    : 'Hadir';
                $notes = ! empty($data['notes']) ? trim($data['notes']) : null;

                SubjectAttendance::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'academic_year_id' => $this->activeYearId,
                        'schedule_id' => $schedule->id,
                        'date' => $this->attendanceDate,
                        'student_id' => $studentId,
                    ],
                    [
                        'classroom_id' => $schedule->classroom_id,
                        'subject_id' => $schedule->subject_id,
                        'teacher_id' => $this->teacherId,
                        'meeting_number' => max(1, (int) $this->meetingNumber),
                        'status' => $status,
                        'notes' => $notes,
                    ]
                );
            }
        });

        session()->flash('subject_attendance_success', 'Presensi mata pelajaran berhasil disimpan.');
    }

    public function loadHomeroomAttendance(): void
    {
        if (! $this->homeroomClassId) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $studentIds = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $this->homeroomClassId)
            ->where('status', 'Aktif')
            ->pluck('id');

        $existingRecords = Attendance::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $this->homeroomClassId)
            ->where('academic_year_id', $this->activeYearId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        foreach ($studentIds as $studentId) {
            $record = $existingRecords->get($studentId);
            $this->attendanceData[$studentId] = [
                'sick' => $record !== null ? $record->sick : 0,
                'permission' => $record !== null ? $record->permission : 0,
                'absent' => $record !== null ? $record->absent : 0,
                'notes' => $record !== null ? $record->notes : '',
            ];
        }
    }

    public function saveAttendance(int|string $studentId): void
    {
        $validated = Validator::make([
            'student_id' => $studentId,
            'attendance' => $this->attendanceData[$studentId] ?? null,
        ], [
            'student_id' => ['required', 'integer'],
            'attendance' => ['required', 'array:sick,permission,absent,notes'],
            'attendance.sick' => ['required', 'integer', 'min:0', 'max:366'],
            'attendance.permission' => ['required', 'integer', 'min:0', 'max:366'],
            'attendance.absent' => ['required', 'integer', 'min:0', 'max:366'],
            'attendance.notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereKey($this->activeYearId)
            ->firstOrFail();

        $homeroomClass = Classroom::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $activeYear->id)
            ->where('teacher_id', $this->teacherId)
            ->whereKey($this->homeroomClassId)
            ->firstOrFail();

        $student = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $homeroomClass->id)
            ->where('status', 'Aktif')
            ->whereKey($validated['student_id'])
            ->firstOrFail();

        DB::transaction(function () use ($schoolId, $activeYear, $homeroomClass, $student, $validated): void {
            Attendance::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_year_id' => $activeYear->id,
                    'classroom_id' => $homeroomClass->id,
                    'student_id' => $student->id,
                ],
                [
                    'sick' => $validated['attendance']['sick'],
                    'permission' => $validated['attendance']['permission'],
                    'absent' => $validated['attendance']['absent'],
                    'notes' => $validated['attendance']['notes'] ?: null,
                ],
            );
        });

        session()->flash('success_'.$student->id, 'Presensi disimpan.');
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = $this->activeYearId
            ? AcademicYear::query()->where('school_id', $schoolId)->whereKey($this->activeYearId)->first()
            : null;

        $selectedSchedule = $this->selectedScheduleId
            ? $this->accessibleScheduleQuery()->with(['classroom', 'subject'])->whereKey($this->selectedScheduleId)->first()
            : null;

        $subjectStudents = $selectedSchedule
            ? Student::query()
                ->select(['id', 'name', 'nisn'])
                ->where('school_id', $schoolId)
                ->where('classroom_id', $selectedSchedule->classroom_id)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get()
            : collect();

        $homeroomClass = $this->homeroomClassId
            ? Classroom::query()->where('school_id', $schoolId)->whereKey($this->homeroomClassId)->first()
            : null;

        $homeroomStudents = $homeroomClass
            ? Student::query()
                ->select(['id', 'name', 'nisn'])
                ->where('school_id', $schoolId)
                ->where('classroom_id', $homeroomClass->id)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.teacher.attendances', [
            'activeYear' => $activeYear,
            'selectedSchedule' => $selectedSchedule,
            'subjectStudents' => $subjectStudents,
            'homeroomClass' => $homeroomClass,
            'homeroomStudents' => $homeroomStudents,
            'schedules' => $this->schedules,
        ]);
    }

    /** @return Builder<Schedule> */
    private function accessibleScheduleQuery(): Builder
    {
        return Schedule::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYearId);
    }
}
