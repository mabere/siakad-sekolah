<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\CounselingRecord;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.student')]
class Counseling extends Component
{
    public string $activeTab = 'discipline'; // 'discipline', 'counseling'

    #[Locked]
    public ?int $activeYearId = null;

    public ?Student $student = null;

    public ?AcademicYear $activeYear = null;

    public int $totalDemeritPoints = 0;

    public int $totalViolationsCount = 0;

    // Request Counseling Modal State
    public bool $showRequestModal = false;

    public string $counseling_type = 'Bimbingan Pribadi';

    public string $problem_description = '';

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $this->activeYear?->id;

        $this->student = Student::with('classroom')
            ->where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        if ($this->student && $this->activeYearId) {
            $this->totalViolationsCount = StudentViolation::where('student_id', $this->student->id)
                ->where('academic_year_id', $this->activeYearId)
                ->count();

            $this->totalDemeritPoints = (int) StudentViolation::where('student_id', $this->student->id)
                ->where('academic_year_id', $this->activeYearId)
                ->sum('points');
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['discipline', 'counseling'])) {
            $this->activeTab = $tab;
        }
    }

    public function openRequestModal(): void
    {
        $this->reset(['counseling_type', 'problem_description']);
        $this->counseling_type = 'Bimbingan Pribadi';
        $this->showRequestModal = true;
    }

    public function submitRequest(): void
    {
        $this->validate([
            'counseling_type' => 'required|string',
            'problem_description' => 'required|string|min:10',
        ]);

        if (! $this->student || ! $this->activeYearId) {
            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        CounselingRecord::create([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'student_id' => $this->student->id,
            'counselor_teacher_id' => null,
            'counseling_type' => $this->counseling_type,
            'counseling_date' => now()->toDateString(),
            'problem_description' => $this->problem_description,
            'solution_plan' => 'Permohonan bimbingan baru diajukan oleh siswa.',
            'status' => 'Proses',
        ]);

        $this->showRequestModal = false;
        session()->flash('success', 'Permohonan konseling BK berhasil dikirimkan. Guru BK akan segera menghubungi Anda.');
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $violationsList = ($this->student && $this->activeYearId)
            ? StudentViolation::with('reporterTeacher')
                ->where('student_id', $this->student->id)
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->orderBy('event_date', 'desc')
                ->get()
            : collect();

        $counselingsList = ($this->student && $this->activeYearId)
            ? CounselingRecord::with('counselorTeacher')
                ->where('student_id', $this->student->id)
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->orderBy('counseling_date', 'desc')
                ->get()
            : collect();

        return view('livewire.student.counseling', [
            'violationsList' => $violationsList,
            'counselingsList' => $counselingsList,
        ]);
    }
}
