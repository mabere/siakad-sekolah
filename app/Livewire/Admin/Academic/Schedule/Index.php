<?php

namespace App\Livewire\Admin\Academic\Schedule;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?AcademicYear $activeYear = null;

    public string|int $filterClassroom = '';

    public string|int $filterAcademicYear = '';

    public bool $isFormOpen = false;

    public bool $isEdit = false;

    public int|string|null $editingId = null;

    public int|string|null $subject_id = null;

    public int|string|null $teacher_id = null;

    public ?string $day_of_week = null;

    public ?string $start_time = null;

    public ?string $end_time = null;

    /** @var array<int, string> */
    public array $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        $schoolId = app(CurrentSchool::class)->id();

        return [
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('school_id', $schoolId),
            ],
            'teacher_id' => [
                'required',
                Rule::exists('teachers', 'id')->where('school_id', $schoolId),
            ],
            'day_of_week' => ['required', Rule::in($this->days)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->activeYear = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if ($this->activeYear) {
            $this->filterAcademicYear = $this->activeYear->id;
        }

        $this->filterClassroom = Classroom::query()
            ->where('school_id', $schoolId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->value('id') ?? '';
    }

    private function isReadOnly(): bool
    {
        return ! $this->activeYear
            || (string) $this->filterAcademicYear !== (string) $this->activeYear->id;
    }

    private function validateFilters(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        Validator::make([
            'filterClassroom' => $this->filterClassroom,
            'filterAcademicYear' => $this->filterAcademicYear,
        ], [
            'filterClassroom' => [
                'required',
                Rule::exists('classrooms', 'id')
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $this->activeYear->id),
            ],
            'filterAcademicYear' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
        ])->validate();
    }

    public function create(): void
    {
        if ($this->isReadOnly()) {
            session()->flash('error', 'Tidak dapat memodifikasi riwayat jadwal masa lalu.');

            return;
        }

        $this->validateFilters();
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int|string $id): void
    {
        if ($this->isReadOnly()) {
            session()->flash('error', 'Tidak dapat memodifikasi riwayat jadwal masa lalu.');

            return;
        }

        $this->validateFilters();
        $schoolId = app(CurrentSchool::class)->id();
        $record = Schedule::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYear->id)
            ->where('classroom_id', $this->filterClassroom)
            ->whereKey($id)
            ->firstOrFail();

        $this->resetForm();
        $this->isEdit = true;
        $this->editingId = $record->id;
        $this->isFormOpen = true;
        $this->subject_id = $record->subject_id;
        $this->teacher_id = $record->teacher_id;
        $this->day_of_week = $record->day_of_week;
        $this->start_time = substr((string) $record->start_time, 0, 5);
        $this->end_time = substr((string) $record->end_time, 0, 5);
    }

    public function save(): void
    {
        if ($this->isReadOnly()) {
            session()->flash('error', 'Tidak dapat memodifikasi riwayat jadwal masa lalu.');

            return;
        }

        $this->validateFilters();
        $this->validate();
        $schoolId = app(CurrentSchool::class)->id();

        DB::transaction(function () use ($schoolId): void {
            $conflict = Schedule::query()
                ->with('classroom:id,name,grade_level')
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYear->id)
                ->where('day_of_week', $this->day_of_week)
                ->where('start_time', '<', $this->end_time)
                ->where('end_time', '>', $this->start_time)
                ->where(function (Builder $query): void {
                    $query->where('teacher_id', $this->teacher_id)
                        ->orWhere('classroom_id', $this->filterClassroom);
                })
                ->when($this->isEdit, fn (Builder $query) => $query->whereKeyNot($this->editingId))
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                $reason = (string) $conflict->teacher_id === (string) $this->teacher_id
                    ? 'Guru sudah memiliki jadwal pada waktu tersebut'
                    : 'Kelas sudah memiliki jadwal pada waktu tersebut';
                $className = trim(($conflict->classroom->grade_level ?? '').' '.($conflict->classroom->name ?? ''));

                throw ValidationException::withMessages([
                    'start_time' => $reason.($className ? ' di kelas '.$className : '').'.',
                ]);
            }

            $payload = [
                'subject_id' => $this->subject_id,
                'teacher_id' => $this->teacher_id,
                'day_of_week' => $this->day_of_week,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
            ];

            if ($this->isEdit) {
                Schedule::query()
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $this->activeYear->id)
                    ->where('classroom_id', $this->filterClassroom)
                    ->findOrFail($this->editingId)
                    ->update($payload);

                session()->flash('message', 'Jadwal berhasil diperbarui.');

                return;
            }

            Schedule::create($payload + [
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYear->id,
                'classroom_id' => $this->filterClassroom,
            ]);

            session()->flash('message', 'Jadwal berhasil ditambahkan.');
        });

        $this->resetForm();
    }

    public function delete(int|string $id): void
    {
        if ($this->isReadOnly()) {
            session()->flash('error', 'Tidak dapat menghapus riwayat jadwal masa lalu.');

            return;
        }

        $this->validateFilters();
        Schedule::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYear->id)
            ->where('classroom_id', $this->filterClassroom)
            ->whereKey($id)
            ->firstOrFail()
            ->delete();

        session()->flash('message', 'Jadwal berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'subject_id',
            'teacher_id',
            'day_of_week',
            'start_time',
            'end_time',
            'isFormOpen',
            'isEdit',
            'editingId',
        ]);
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $academicYears = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->orderByDesc('id')
            ->get();
        $classrooms = Classroom::query()
            ->where('school_id', $schoolId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
        $subjects = Subject::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get();
        $teachers = Teacher::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        $schedules = collect($this->days)->mapWithKeys(fn (string $day): array => [$day => collect()]);

        if ($this->filterClassroom && $this->filterAcademicYear) {
            $rawSchedules = Schedule::query()
                ->with(['subject:id,name,code', 'teacher:id,name'])
                ->where('school_id', $schoolId)
                ->where('classroom_id', $this->filterClassroom)
                ->where('academic_year_id', $this->filterAcademicYear)
                ->orderBy('start_time')
                ->get();

            $schedules = collect($this->days)->mapWithKeys(
                fn (string $day): array => [$day => $rawSchedules->where('day_of_week', $day)->values()]
            );
        }

        return view('livewire.admin.academic.schedule.index', [
            'academicYears' => $academicYears,
            'classrooms' => $classrooms,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'schedules' => $schedules,
            'isReadOnly' => $this->isReadOnly(),
        ]);
    }
}
