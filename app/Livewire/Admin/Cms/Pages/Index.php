<?php

namespace App\Livewire\Admin\Cms\Pages;

use App\Models\Page;
use App\Support\CurrentSchool;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $isFormOpen = false;

    public bool $isEdit = false;

    public int|string|null $editingId = null;

    // Form fields
    public string $title = '';

    public string $slug = '';

    public string $content = '';

    public string $status = 'Draft';

    public function updatedTitle(mixed $value): void
    {
        if (! $this->isEdit) {
            $this->slug = Str::slug($value);
        }
    }

    public function openForm(int|string|null $id = null): void
    {
        $this->resetValidation();

        if ($id) {
            $page = Page::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->first();
            if ($page) {
                $this->isEdit = true;
                $this->editingId = $id;
                $this->title = $page->title;
                $this->slug = $page->slug;
                $this->content = $page->content;
                $this->status = $page->status;
                $this->isFormOpen = true;
            }
        } else {
            $this->isEdit = false;
            $this->editingId = null;
            $this->title = '';
            $this->slug = '';
            $this->content = '';
            $this->status = 'Draft';
            $this->isFormOpen = true;
        }
    }

    public function closeForm(): void
    {
        $this->isFormOpen = false;
    }

    public function save(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,'.$this->editingId.',id,school_id,'.$schoolId,
            'status' => 'required|in:Draft,Published',
            'content' => 'nullable|string',
        ]);

        if ($this->isEdit) {
            $page = Page::where('school_id', $schoolId)->whereKey($this->editingId)->firstOrFail();
            $page->update([
                'title' => $this->title,
                'slug' => $this->slug,
                'content' => SafeHtml::clean($this->content),
                'status' => $this->status,
            ]);
            session()->flash('message', 'Halaman berhasil diperbarui.');
        } else {
            Page::create([
                'school_id' => $schoolId,
                'author_id' => auth()->id(),
                'title' => $this->title,
                'slug' => $this->slug,
                'content' => SafeHtml::clean($this->content),
                'status' => $this->status,
            ]);
            session()->flash('message', 'Halaman berhasil ditambahkan.');
        }

        $this->closeForm();
    }

    public function delete(int|string $id): void
    {
        Page::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail()->delete();
        session()->flash('message', 'Halaman berhasil dihapus.');
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $pages = Page::where('school_id', $schoolId)
            ->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.cms.pages.index', [
            'pages' => $pages,
        ]);
    }
}
