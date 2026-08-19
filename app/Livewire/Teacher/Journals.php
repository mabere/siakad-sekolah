<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.teacher')]
class Journals extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    /** @var array<int, array<string, mixed>> */
    public array $schedules = [];

    #[Url(as: 'schedule')]
    public string $selectedScheduleId = '';

    public string $dateFilter = '';

    // --- Modal Form State ---
    public bool $showFormModal = false;

    public ?int $editingJournalId = null;

    public string $formScheduleId = '';

    public string $date = '';

    public int $meetingNumber = 1;

    public string $learningMethod = 'Tatap Muka (Luring)';

    public string $topicSummary = '';

    public string $activities = '';

    public string $studentNotes = '';

    public string $obstaclesAndSolutions = '';

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
        abort_unless($teacher !== null, 403, 'Akun belum terhubung ke data guru aktif.');

        // abort_if(! $this->teacherId, 403);

        if ($this->activeYearId && $this->teacherId) {
            $this->schedules = Schedule::with(['classroom', 'subject'])
                ->where('school_id', $schoolId)
                ->where('teacher_id', $this->teacherId)
                ->where('academic_year_id', $this->activeYearId)
                ->get()
                ->unique(fn ($item) => $item->classroom_id.'-'.$item->subject_id)
                ->values()
                ->toArray();

            if (empty($this->selectedScheduleId) && ! empty($this->schedules)) {
                $this->selectedScheduleId = (string) $this->schedules[0]['id'];
            }
        }

        $this->date = now()->toDateString();
    }

    public function openFormModal(?int $journalId = null): void
    {
        $this->resetValidation();

        if ($journalId) {
            $journal = $this->accessibleJournalQuery()->whereKey($journalId)->first();
            if (! $journal) {
                return;
            }

            $this->editingJournalId = $journal->id;
            $this->formScheduleId = (string) $journal->schedule_id;
            $this->date = $journal->date;
            $this->meetingNumber = (int) $journal->meeting_number;
            $this->learningMethod = $journal->learning_method;
            $this->topicSummary = $journal->topic_summary;
            $this->activities = $journal->activities ?? '';
            $this->studentNotes = $journal->student_notes ?? '';
            $this->obstaclesAndSolutions = $journal->obstacles_and_solutions ?? '';
        } else {
            $this->editingJournalId = null;
            $this->formScheduleId = $this->selectedScheduleId;
            $this->date = now()->toDateString();
            $this->learningMethod = 'Tatap Muka (Luring)';
            $this->topicSummary = '';
            $this->activities = '';
            $this->studentNotes = '';
            $this->obstaclesAndSolutions = '';

            // Auto-calculate next meeting number
            if ($this->formScheduleId) {
                $latestMeeting = $this->accessibleJournalQuery()
                    ->where('schedule_id', $this->formScheduleId)
                    ->max('meeting_number');
                $this->meetingNumber = $latestMeeting ? ((int) $latestMeeting + 1) : 1;
            } else {
                $this->meetingNumber = 1;
            }
        }

        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->editingJournalId = null;
    }

    public function updatedFormScheduleId(): void
    {
        if ($this->formScheduleId && ! $this->editingJournalId) {
            $latestMeeting = $this->accessibleJournalQuery()
                ->where('schedule_id', $this->formScheduleId)
                ->max('meeting_number');
            $this->meetingNumber = $latestMeeting ? ((int) $latestMeeting + 1) : 1;
        }
    }

    public function saveJournal(): void
    {
        $validated = Validator::make([
            'schedule_id' => $this->formScheduleId,
            'date' => $this->date,
            'meeting_number' => $this->meetingNumber,
            'learning_method' => $this->learningMethod,
            'topic_summary' => $this->topicSummary,
            'activities' => $this->activities,
            'student_notes' => $this->studentNotes,
            'obstacles_and_solutions' => $this->obstaclesAndSolutions,
        ], [
            'schedule_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'meeting_number' => ['required', 'integer', 'min:1'],
            'learning_method' => ['required', 'string', 'max:100'],
            'topic_summary' => ['required', 'string', 'max:5000'],
            'activities' => ['nullable', 'string', 'max:5000'],
            'student_notes' => ['nullable', 'string', 'max:5000'],
            'obstacles_and_solutions' => ['nullable', 'string', 'max:5000'],
        ])->validate();

        $schedule = $this->accessibleScheduleQuery()->whereKey($validated['schedule_id'])->first();
        if (! $schedule) {
            session()->flash('journal_error', 'Jadwal tidak ditemukan.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $journal = $this->editingJournalId
            ? $this->accessibleJournalQuery()->whereKey($this->editingJournalId)->first()
            : new TeachingJournal;
        if ($this->editingJournalId && ! $journal) {
            abort(403);
        }

        $journal->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'schedule_id' => $schedule->id,
            'classroom_id' => $schedule->classroom_id,
            'subject_id' => $schedule->subject_id,
            'teacher_id' => $this->teacherId,
            'date' => $validated['date'],
            'meeting_number' => $validated['meeting_number'],
            'learning_method' => $validated['learning_method'],
            'topic_summary' => trim($validated['topic_summary']),
            'activities' => ! empty($validated['activities']) ? trim($validated['activities']) : null,
            'student_notes' => ! empty($validated['student_notes']) ? trim($validated['student_notes']) : null,
            'obstacles_and_solutions' => ! empty($validated['obstacles_and_solutions']) ? trim($validated['obstacles_and_solutions']) : null,
            'status' => 'Disetujui',
        ]);
        $journal->save();

        session()->flash('journal_success', 'Jurnal Mengajar KBM berhasil disimpan.');
        $this->closeFormModal();
    }

    public function deleteJournal(int $journalId): void
    {
        $journal = $this->accessibleJournalQuery()->whereKey($journalId)->first();
        if ($journal) {
            $journal->delete();
            session()->flash('journal_success', 'Entri Jurnal Mengajar berhasil dihapus.');
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

        $selectedSchedule = $this->selectedScheduleId
            ? $this->accessibleScheduleQuery()->with(['classroom', 'subject'])->whereKey($this->selectedScheduleId)->first()
            : null;

        $journalsQuery = TeachingJournal::with(['classroom', 'subject'])
            ->where('school_id', $schoolId)
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYearId);

        if ($this->selectedScheduleId) {
            $journalsQuery->where('schedule_id', $this->selectedScheduleId);
        }

        if ($this->dateFilter) {
            $journalsQuery->where('date', $this->dateFilter);
        }

        $journals = $journalsQuery
            ->orderBy('date', 'desc')
            ->orderBy('meeting_number', 'desc')
            ->paginate(7);

        // Load all attendance summaries for the current page in one grouped query.
        $attendanceRows = SubjectAttendance::query()
            ->where('school_id', $schoolId)
            ->whereIn('schedule_id', $journals->pluck('schedule_id')->unique()->values())
            ->whereIn('date', $journals->pluck('date')->unique()->values())
            ->selectRaw('schedule_id, date, status, count(*) as count')
            ->groupBy('schedule_id', 'date', 'status')
            ->get();

        $attendanceSummaries = [];
        foreach ($journals as $journal) {
            $attCounts = $attendanceRows
                ->where('schedule_id', $journal->schedule_id)
                ->where('date', $journal->date)
                ->pluck('count', 'status')
                ->toArray();

            $attendanceSummaries[$journal->id] = [
                'Hadir' => $attCounts['Hadir'] ?? 0,
                'Sakit' => $attCounts['Sakit'] ?? 0,
                'Izin' => $attCounts['Izin'] ?? 0,
                'Alpa' => $attCounts['Alpa'] ?? 0,
            ];
        }

        return view('livewire.teacher.journals', [
            'activeYear' => $activeYear,
            'selectedSchedule' => $selectedSchedule,
            'journals' => $journals,
            'attendanceSummaries' => $attendanceSummaries,
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

    /** @return Builder<TeachingJournal> */
    private function accessibleJournalQuery(): Builder
    {
        return TeachingJournal::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYearId);
    }
}
