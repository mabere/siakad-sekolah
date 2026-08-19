<?php

namespace App\Livewire\Public;

use App\Models\Classroom;
use App\Models\Post;
use App\Models\School;
use App\Models\Slider;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Home extends Component
{
    public function render(): View
    {
        // For public pages, we assume the first school or fetch by domain.
        // For simplicity, we just take the first one since it's a single-tenant instance right now.
        $school = School::query()->first();
        $schoolId = $school === null ? 0 : (int) $school->id;

        $stats = [
            'teachers' => Teacher::where('school_id', $schoolId)->count(),
            'students' => Student::where('school_id', $schoolId)->where('status', 'Aktif')->count(),
            'classrooms' => Classroom::where('school_id', $schoolId)->count(),
            'achievements' => StudentAchievement::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count(),
        ];

        $latestPosts = Post::with(['category', 'author'])
            ->where('school_id', $schoolId)
            ->where('status', 'Published')
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        $sliders = Slider::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.public.home', compact('school', 'stats', 'latestPosts', 'sliders'));
    }
}
