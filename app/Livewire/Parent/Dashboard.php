<?php

namespace App\Livewire\Parent;

use App\Models\Attendance;
use App\Models\ParentStudentRelation;
use App\Models\StudentPayment;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public int|string|null $selectedStudentId = null;

    public function mount(): void
    {
        $relations = $this->accessibleRelations();
        if ($relations->isNotEmpty()) {
            $this->selectedStudentId = $relations->first()->student_id;
        }
    }

    public function selectStudent(int $id): void
    {
        abort_unless(
            $this->accessibleRelations()->contains(fn ($relation): bool => (int) $relation->student_id === $id),
            403,
            'Siswa tidak terhubung ke akun orang tua ini.',
        );

        $this->selectedStudentId = $id;
    }

    public function render(): View
    {
        $relations = $this->accessibleRelations();

        $selectedRelation = $relations->firstWhere('student_id', $this->selectedStudentId);
        $student = $selectedRelation?->student;

        $attendances = collect();
        $unpaidPayments = collect();

        if ($student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->where('school_id', $student->school_id)
                ->latest()
                ->take(5)
                ->get();

            $unpaidPayments = StudentPayment::with('category')
                ->where('student_id', $student->id)
                ->where('school_id', $student->school_id)
                ->whereIn('status', ['unpaid', 'partial', 'pending_confirmation'])
                ->get();
        }

        return view('livewire.parent.dashboard', [
            'relations' => $relations,
            'student' => $student,
            'attendances' => $attendances,
            'unpaidPayments' => $unpaidPayments,
        ]);
    }

    /** @return Collection<int, ParentStudentRelation> */
    private function accessibleRelations(): Collection
    {
        return ParentStudentRelation::with(['student.classroom'])
            ->where('parent_user_id', auth()->id())
            ->whereHas('student', function ($query): void {
                $query->where('school_id', app(CurrentSchool::class)->id());
            })
            ->get();
    }
}
