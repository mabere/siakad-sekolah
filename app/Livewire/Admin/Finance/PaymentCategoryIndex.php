<?php

namespace App\Livewire\Admin\Finance;

use App\Models\PaymentCategory;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PaymentCategoryIndex extends Component
{
    public string $name = '';

    public string $type = 'monthly_spp';

    public string|int|float $default_amount = 0;

    public bool $is_active = true;

    public int|string|null $editingId = null;

    public bool $isFormOpen = false;

    public function openForm(int|string|null $id = null): void
    {
        $this->resetValidation();
        if ($id) {
            $cat = PaymentCategory::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->firstOrFail();
            $this->editingId = $id;
            $this->name = $cat->name;
            $this->type = $cat->type;
            $this->default_amount = $cat->default_amount;
            $this->is_active = $cat->is_active;
            $this->isFormOpen = true;
        } else {
            $this->editingId = null;
            $this->name = '';
            $this->type = 'monthly_spp';
            $this->default_amount = 0;
            $this->is_active = true;
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:monthly_spp,one_time,optional',
            'default_amount' => 'required|numeric|min:0',
        ]);

        $category = $this->editingId
            ? PaymentCategory::where('school_id', $schoolId)->whereKey($this->editingId)->firstOrFail()
            : new PaymentCategory;
        $category->fill([
            'school_id' => $schoolId,
            'name' => trim($this->name),
            'type' => $this->type,
            'default_amount' => $this->default_amount,
            'is_active' => $this->is_active,
        ]);
        $category->save();

        session()->flash('message', 'Kategori Pembayaran berhasil disimpan.');
        $this->closeForm();
    }

    public function toggleStatus(int|string $id): void
    {
        $cat = PaymentCategory::where('school_id', app(CurrentSchool::class)->id())->whereKey($id)->first();
        if ($cat) {
            $cat->update(['is_active' => ! $cat->is_active]);
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $categories = PaymentCategory::where('school_id', $schoolId)->get();

        return view('livewire.admin.finance.payment-category-index', [
            'categories' => $categories,
        ]);
    }
}
