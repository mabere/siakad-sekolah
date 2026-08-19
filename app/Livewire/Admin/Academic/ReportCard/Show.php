<?php

namespace App\Livewire\Admin\Academic\ReportCard;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Student $student;

    public ?AcademicYear $activeYear = null;

    public School $school;

    /** @var Collection<int, Grade> */
    public Collection $grades;

    /** @var array<string, int> */
    public array $attendanceSummary = [
        'Hadir' => 0,
        'Sakit' => 0,
        'Izin' => 0,
        'Alpa' => 0,
    ];

    public float|int $averageScore = 0;

    public function mount(Student $student): void
    {
        $this->school = app(CurrentSchool::class)->get();
        $this->student = Student::query()
            ->where('school_id', $this->school->id)
            ->findOrFail($student->id);

        $this->activeYear = AcademicYear::where('school_id', $this->school->id ?? 0)
            ->where('is_active', true)
            ->first();

        if ($this->activeYear) {
            $this->loadData();
        }
    }

    private function loadData(): void
    {
        // Get Grades
        $this->grades = Grade::with('subject')
            ->where('school_id', $this->school->id)
            ->where('student_id', $this->student->id)
            ->where('academic_year_id', $this->activeYear->id)
            ->get();

        if ($this->grades->count() > 0) {
            $this->averageScore = round($this->grades->avg('final_score'), 2);
        }

        // Get Attendance Summary
        $attendance = Attendance::where('student_id', $this->student->id)
            ->where('school_id', $this->school->id)
            ->where('academic_year_id', $this->activeYear->id)
            ->first();

        if ($attendance) {
            $this->attendanceSummary['Sakit'] = $attendance->sick;
            $this->attendanceSummary['Izin'] = $attendance->permission;
            $this->attendanceSummary['Alpa'] = $attendance->absent;
        } else {
            $this->attendanceSummary['Sakit'] = 0;
            $this->attendanceSummary['Izin'] = 0;
            $this->attendanceSummary['Alpa'] = 0;
        }
    }

    public function render(): View
    {
        $settings = SystemSetting::query()
            ->where('school_id', $this->school->id)
            ->whereIn('key', ['headmaster_name', 'headmaster_nip', 'city'])
            ->get()
            ->keyBy('key');

        $headmasterNameSetting = $settings->get('headmaster_name');
        $headmasterName = $headmasterNameSetting && ! empty($headmasterNameSetting->value) ? $headmasterNameSetting->value : '_________________________';

        $headmasterNipSetting = $settings->get('headmaster_nip');
        $headmasterNip = $headmasterNipSetting && ! empty($headmasterNipSetting->value) ? $headmasterNipSetting->value : '.........................';

        $citySetting = $settings->get('city');
        $city = $citySetting ? $citySetting->value : '';

        return view('livewire.admin.academic.report-card.show', [
            'headmasterName' => $headmasterName,
            'headmasterNip' => $headmasterNip,
            'city' => $city,
        ]);
    }
}
