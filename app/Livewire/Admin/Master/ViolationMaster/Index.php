<?php

namespace App\Livewire\Admin\Master\ViolationMaster;

use App\Models\ViolationMaster;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $category = 'Ringan';

    public int $default_points = 5;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(string $value): void
    {
        if ($value === 'Ringan') {
            $this->default_points = 5;
        } elseif ($value === 'Sedang') {
            $this->default_points = 15;
        } elseif ($value === 'Berat') {
            $this->default_points = 35;
        }
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();

        if ($id) {
            $record = ViolationMaster::query()
                ->where('school_id', app(CurrentSchool::class)->id())
                ->whereKey($id)
                ->first();
            if (! $record) {
                return;
            }

            $this->editingId = $record->id;
            $this->code = $record->code ?? '';
            $this->name = $record->name;
            $this->category = $record->category;
            $this->default_points = $record->default_points;
        } else {
            $this->editingId = null;
            $this->code = '';
            $this->name = '';
            $this->category = 'Ringan';
            $this->default_points = 5;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function save(): void
    {
        $validated = Validator::make([
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'default_points' => $this->default_points,
        ], [
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:Ringan,Sedang,Berat'],
            'default_points' => ['required', 'integer', 'min:1', 'max:500'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        ViolationMaster::updateOrCreate(
            [
                'school_id' => $schoolId,
                'id' => $this->editingId,
            ],
            [
                'school_id' => $schoolId,
                'code' => ! empty($validated['code']) ? trim($validated['code']) : null,
                'name' => trim($validated['name']),
                'category' => $validated['category'],
                'default_points' => $validated['default_points'],
            ]
        );

        session()->flash('success', 'Data pelanggaran berhasil disimpan.');
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $record = ViolationMaster::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->first();
        if ($record) {
            $record->delete();
            session()->flash('success', 'Data pelanggaran berhasil dihapus.');
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $query = ViolationMaster::where('school_id', $schoolId);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $violations = $query->orderBy('category')->orderBy('name')->paginate(7);

        return view('livewire.admin.master.violation-master.index', [
            'violations' => $violations,
        ]);
    }
}
