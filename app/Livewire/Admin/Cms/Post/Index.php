<?php

namespace App\Livewire\Admin\Cms\Post;

use App\Models\Post;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int|string $id): void
    {
        Post::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail()->delete();
        session()->flash('message', 'Artikel berhasil dihapus.');
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $query = Post::with(['category', 'author'])
            ->where('school_id', $schoolId);

        if ($this->search) {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.cms.post.index', compact('posts'));
    }
}
