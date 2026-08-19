<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\P5StudentNote;
use App\Models\Student;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.student')]
class Grades extends Component
{
    public string $activeTab = 'academic'; // 'academic', 'p5'

    #[Locked]
    public ?int $activeYearId = null;

    public ?Student $student = null;

    public ?AcademicYear $activeYear = null;

    public float $averageFinalScore = 0.0;

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
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['academic', 'p5'])) {
            $this->activeTab = $tab;
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        // 1. Academic Grades
        $gradesList = collect();
        if ($this->student && $this->activeYearId) {
            $rawGrades = Grade::with('subject')
                ->where('student_id', $this->student->id)
                ->where('academic_year_id', $this->activeYearId)
                ->get();

            $totalScore = 0;
            $count = 0;

            foreach ($rawGrades as $g) {
                $gw = GradeWeight::where('classroom_id', $this->student->classroom_id)
                    ->where('academic_year_id', $this->activeYearId)
                    ->where('subject_id', $g->subject_id)
                    ->first();

                $wT = $gw ? $gw->weight_tugas : 30;
                $wUts = $gw ? $gw->weight_uts : 30;
                $wUas = $gw ? $gw->weight_uas : 40;

                $tugas = (float) ($g->tugas ?? 0);
                $uts = (float) ($g->uts ?? 0);
                $uas = (float) ($g->uas ?? 0);
                $finalScore = (($tugas * $wT) + ($uts * $wUts) + ($uas * $wUas)) / 100;
                $finalScore = round($finalScore, 1);

                $pred = 'D';
                if ($finalScore >= 88) {
                    $pred = 'A';
                } elseif ($finalScore >= 78) {
                    $pred = 'B';
                } elseif ($finalScore >= 68) {
                    $pred = 'C';
                }

                $g->calculated_final = $finalScore;
                $g->calculated_predicate = $pred;

                $totalScore += $finalScore;
                $count++;
            }

            $gradesList = $rawGrades;
            $this->averageFinalScore = $count > 0 ? round($totalScore / $count, 1) : 0.0;
        }

        // 2. P5 Evaluation Projects
        $p5Projects = collect();
        $p5AssessmentsMap = [];
        $p5NotesMap = [];

        if ($this->student && $this->student->classroom_id && $this->activeYearId) {
            $p5Projects = P5Project::with('dimensions')
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('classroom_id', $this->student->classroom_id)
                ->get();

            foreach ($p5Projects as $proj) {
                $ass = P5Assessment::where('p5_project_id', $proj->id)
                    ->where('student_id', $this->student->id)
                    ->get();

                foreach ($ass as $a) {
                    $p5AssessmentsMap[$a->p5_project_dimension_id] = $a->score;
                }

                $sn = P5StudentNote::where('p5_project_id', $proj->id)
                    ->where('student_id', $this->student->id)
                    ->first();

                if ($sn) {
                    $p5NotesMap[$proj->id] = $sn->process_notes;
                }
            }
        }

        return view('livewire.student.grades', [
            'gradesList' => $gradesList,
            'p5Projects' => $p5Projects,
            'p5AssessmentsMap' => $p5AssessmentsMap,
            'p5NotesMap' => $p5NotesMap,
        ]);
    }
}
