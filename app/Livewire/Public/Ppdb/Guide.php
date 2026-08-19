<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\School;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Guide extends Component
{
    public function render(): View
    {
        $school = School::query()->where('is_active', true)->orderBy('id')->firstOrFail();

        return view('livewire.public.ppdb.guide', [
            'school' => $school,
        ])->title('Panduan PPDB - '.$school->name);
    }
}
