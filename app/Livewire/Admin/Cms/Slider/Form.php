<?php

namespace App\Livewire\Admin\Cms\Slider;

use App\Models\Slider;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public int|string|null $sliderId = null;

    public string $title = '';

    public string $description = '';

    public mixed $image_path = null;

    public ?string $existing_image_path = null;

    public string $button_text = '';

    public string $button_url = '';

    public bool $is_active = true;

    public int $order = 0;

    public function mount(int|string|null $id = null): void
    {
        if ($id) {
            $slider = Slider::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
            $this->sliderId = $slider->id;
            $this->title = $slider->title;
            $this->description = $slider->description;
            $this->existing_image_path = $slider->image_path;
            $this->button_text = $slider->button_text;
            $this->button_url = $slider->button_url;
            $this->is_active = $slider->is_active;
            $this->order = $slider->order;
        }
    }

    public function save(): RedirectResponse|Redirector
    {
        $this->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'image_path' => $this->sliderId ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'button_text' => 'nullable|max:255',
            'button_url' => 'nullable|url|max:255',
        ]);

        $data = [
            'school_id' => app(CurrentSchool::class)->id(),
            'title' => $this->title,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ];

        if ($this->image_path) {
            $data['image_path'] = $this->image_path->store('sliders', 'public');
        }

        $slider = $this->sliderId
            ? Slider::where('school_id', $data['school_id'])->whereKey($this->sliderId)->firstOrFail()
            : new Slider;
        $slider->fill($data);
        $slider->save();

        session()->flash('message', 'Slider berhasil disimpan.');

        return redirect()->route('admin.cms.sliders');
    }

    public function render(): View
    {
        return view('livewire.admin.cms.slider.form');
    }
}
