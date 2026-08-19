<?php

namespace App\Livewire\Admin\Cms\Category;

use App\Models\PostCategory;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    /** @var Collection<int, PostCategory> */
    public Collection $categories;

    public string $name = '';

    public int|string|null $categoryId = null;

    public bool $isModalOpen = false;

    public function mount(): void
    {
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $this->categories = PostCategory::where('school_id', $schoolId)->orderBy('name')->get();
    }

    public function openModal(int|string|null $id = null): void
    {
        $this->resetValidation();
        $this->categoryId = $id;

        if ($id) {
            $category = PostCategory::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
            $this->name = $category->name;
        } else {
            $this->name = '';
        }

        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->name = '';
        $this->categoryId = null;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $slug = Str::slug($this->name);

        $existing = PostCategory::where('school_id', $schoolId)
            ->where('slug', $slug)
            ->when($this->categoryId, function ($q) {
                $q->where('id', '!=', $this->categoryId);
            })->first();

        if ($existing) {
            $slug = $slug.'-'.time();
        }

        $category = $this->categoryId
            ? PostCategory::where('school_id', $schoolId)->whereKey($this->categoryId)->firstOrFail()
            : new PostCategory;
        $category->fill([
            'school_id' => $schoolId,
            'name' => trim($this->name),
            'slug' => $slug,
        ]);
        $category->save();

        session()->flash('message', 'Kategori berhasil disimpan.');
        $this->closeModal();
        $this->loadCategories();
    }

    public function delete(int|string $id): void
    {
        PostCategory::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail()->delete();
        session()->flash('message', 'Kategori berhasil dihapus.');
        $this->loadCategories();
    }

    public function render(): View
    {
        return view('livewire.admin.cms.category.index');
    }
}
