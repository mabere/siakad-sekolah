<?php

namespace App\Livewire\Public\Blog;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Show extends Component
{
    public Post $post;

    public function mount(string $slug): void
    {
        $schoolId = School::query()->firstOrFail()->id;

        $this->post = Post::with(['category', 'author'])
            ->where('school_id', $schoolId)
            ->where('slug', $slug)
            ->where('status', 'Published')
            ->firstOrFail();
    }

    public function render(): View
    {
        $schoolId = School::query()->firstOrFail()->id;

        $popularPosts = Post::where('school_id', $schoolId)
            ->where('status', 'Published')
            ->where('id', '!=', $this->post->id)
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();

        $categories = PostCategory::where('school_id', $schoolId)
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'Published');
            }])
            ->orderBy('name')
            ->get();

        return view('livewire.public.blog.show', compact('popularPosts', 'categories'));
    }
}
