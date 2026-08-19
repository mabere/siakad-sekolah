<?php

namespace App\Livewire\Public\Blog;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.guest')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categorySlug = '';

    /** @var array<string, array<string, string>> */
    protected array $queryString = [
        'search' => ['except' => ''],
        'categorySlug' => ['except' => '', 'as' => 'category'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $slug): void
    {
        $this->categorySlug = $slug;
        $this->resetPage();
    }

    public function render(): View
    {
        $schoolId = School::query()->firstOrFail()->id;

        $query = Post::with(['category', 'author'])
            ->where('school_id', $schoolId)
            ->where('status', 'Published');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->categorySlug) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->categorySlug);
            });
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(6);

        $categories = PostCategory::where('school_id', $schoolId)
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'Published');
            }])
            ->orderBy('name')
            ->get();

        $popularPosts = Post::where('school_id', $schoolId)
            ->where('status', 'Published')
            ->orderBy('published_at', 'desc') // Assume latest are popular for now
            ->take(5)
            ->get();

        return view('livewire.public.blog.index', compact('posts', 'categories', 'popularPosts'));
    }
}
