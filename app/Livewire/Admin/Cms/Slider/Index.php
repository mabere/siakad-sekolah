<?php

namespace App\Livewire\Admin\Cms\Slider;

use App\Models\Slider;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function toggleStatus(int|string $id): void
    {
        $slider = Slider::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
        $slider->is_active = ! $slider->is_active;
        $slider->save();

        session()->flash('message', 'Status slider berhasil diubah.');
    }

    public function delete(int|string $id): void
    {
        $slider = Slider::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
        if ($slider->image_path) {
            Storage::disk('public')->delete($slider->image_path);
        }
        $slider->delete();

        session()->flash('message', 'Slider berhasil dihapus.');
    }

    public function moveUp(int|string $id): void
    {
        $slider = Slider::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
        $slider->order = $slider->order - 1;
        $slider->save();
    }

    public function moveDown(int|string $id): void
    {
        $slider = Slider::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
        $slider->order = $slider->order + 1;
        $slider->save();
    }

    public function render(): View
    {
        $sliders = Slider::where('school_id', app(CurrentSchool::class)->id())
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.cms.slider.index', compact('sliders'));
    }
}
