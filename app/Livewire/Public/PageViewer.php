<?php

namespace App\Livewire\Public;

use App\Models\Page;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class PageViewer extends Component
{
    public Page $page;

    public function mount(string $slug): void
    {
        $school = School::query()->firstOrFail();
        $this->page = Page::where('school_id', $school->id)
            ->where('slug', $slug)
            ->where('status', 'Published')
            ->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.public.page-viewer')
            ->title($this->page->title.' - '.School::query()->firstOrFail()->name);
    }
}
