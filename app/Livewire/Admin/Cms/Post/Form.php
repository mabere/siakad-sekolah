<?php

namespace App\Livewire\Admin\Cms\Post;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\CurrentSchool;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public int|string|null $postId = null;

    public string $title = '';

    public string $content = '';

    public string|int $post_category_id = '';

    public string $status = 'Draft';

    public mixed $featured_image = null;

    public ?string $existing_featured_image = null;

    public function mount(int|string|null $id = null): void
    {
        if ($id) {
            $post = Post::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
            $this->postId = $post->id;
            $this->title = $post->title;
            $this->content = $post->content;
            $this->post_category_id = $post->post_category_id;
            $this->status = $post->status;
            $this->existing_featured_image = $post->featured_image;
        }
    }

    public function save(): RedirectResponse|Redirector
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'content' => 'required',
            'post_category_id' => 'required',
            'status' => 'required|in:Draft,Published',
            'featured_image' => 'nullable|image|max:2048', // max 2MB
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $slug = Str::slug($this->title);

        $existing = Post::where('school_id', $schoolId)
            ->where('slug', $slug)
            ->when($this->postId, function ($q) {
                $q->where('id', '!=', $this->postId);
            })->first();

        if ($existing) {
            $slug = $slug.'-'.time();
        }

        $data = [
            'school_id' => $schoolId,
            'title' => $this->title,
            'slug' => $slug,
            'content' => SafeHtml::clean($this->content),
            'post_category_id' => $this->post_category_id,
            'status' => $this->status,
        ];

        if ($this->featured_image) {
            $data['featured_image'] = $this->featured_image->store('posts', 'public');
        }

        if (! $this->postId) {
            $data['author_id'] = auth()->id();
        }

        if ($this->status === 'Published' && ! $this->postId) {
            $data['published_at'] = now();
        } elseif ($this->status === 'Published' && $this->postId) {
            $post = Post::where('school_id', $schoolId)->whereKey($this->postId)->firstOrFail();
            if (! $post->published_at) {
                $data['published_at'] = now();
            }
        }

        $post = $this->postId
            ? Post::where('school_id', $schoolId)->whereKey($this->postId)->firstOrFail()
            : new Post;
        $post->fill($data);
        $post->save();

        session()->flash('message', 'Artikel berhasil disimpan.');

        return redirect()->route('admin.cms.posts');
    }

    public function render(): View
    {
        $categories = PostCategory::where('school_id', app(CurrentSchool::class)->id())
            ->orderBy('name')
            ->get();

        return view('livewire.admin.cms.post.form', compact('categories'));
    }
}
