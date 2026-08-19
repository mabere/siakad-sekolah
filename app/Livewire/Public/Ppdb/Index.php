<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\PpdbPeriod;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Index extends Component
{
    public function render(): View
    {
        $school = School::query()->where('is_active', true)->orderBy('id')->firstOrFail();
        $periods = PpdbPeriod::query()
            ->where('school_id', $school->id)
            ->whereIn('status', [PpdbPeriod::STATUS_PUBLISHED, PpdbPeriod::STATUS_OPEN])
            ->with(['academicYear', 'pathways' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderByDesc('registration_starts_at')
            ->get();
        $announcementPeriods = PpdbPeriod::query()
            ->where('school_id', $school->id)
            ->whereIn('status', [PpdbPeriod::STATUS_ANNOUNCED, PpdbPeriod::STATUS_REREGISTRATION, PpdbPeriod::STATUS_CLOSED])
            ->whereNotNull('announcement_at')
            ->where('announcement_at', '<=', now())
            ->with('academicYear')
            ->orderByDesc('announcement_at')
            ->get();

        return view('livewire.public.ppdb.index', [
            'school' => $school,
            'periods' => $periods,
            'announcementPeriods' => $announcementPeriods,
        ]);
    }
}
