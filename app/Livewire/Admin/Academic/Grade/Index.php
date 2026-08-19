<?php

namespace App\Livewire\Admin\Academic\Grade;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?AcademicYear $activeYear = null;

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $assignments = [];
        if ($this->activeYear) {
            // Dapatkan kombinasi unik Kelas & Mata Pelajaran dari tabel jadwal
            // di tahun ajaran aktif. Kita ambil data relasinya (classroom, subject, teacher)
            $assignments = Schedule::with(['classroom', 'subject', 'teacher'])
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYear->id)
                ->get()
                // Kelompokkan berdasarkan ID Kelas dan ID Mata Pelajaran agar unik
                ->unique(function ($item) {
                    return $item->classroom_id.'-'.$item->subject_id;
                })
                ->sortBy(function ($item) {
                    return $item->classroom->grade_level.$item->classroom->name.$item->subject->name;
                })
                ->values();
        }

        return view('livewire.admin.academic.grade.index', [
            'assignments' => $assignments,
        ]);
    }
}
