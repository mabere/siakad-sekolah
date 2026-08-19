<?php

namespace App\Livewire\Tu;

use App\Models\StudentLetter;
use App\Models\StudentPayment;
use App\Models\User;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        // Summary Stats
        $totalStudents = User::whereHas('roles', function ($q) {
            $q->where('name', 'siswa');
        })->where('school_id', $schoolId)->count();

        $todayIncome = StudentPayment::where('school_id', $schoolId)
            ->whereDate('paid_at', today())
            ->sum('paid_amount');

        $pendingVerifications = StudentPayment::where('school_id', $schoolId)
            ->where('status', 'pending_confirmation')
            ->count();

        $pendingLetters = StudentLetter::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->count();

        $recentPayments = StudentPayment::with(['student', 'category'])
            ->where('school_id', $schoolId)
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->take(5)
            ->get();

        $recentLetters = StudentLetter::with('student')
            ->where('school_id', $schoolId)
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.tu.dashboard', [
            'totalStudents' => $totalStudents,
            'todayIncome' => $todayIncome,
            'pendingVerifications' => $pendingVerifications,
            'pendingLetters' => $pendingLetters,
            'recentPayments' => $recentPayments,
            'recentLetters' => $recentLetters,
        ]);
    }
}
