<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\PpdbApplication;
use App\Models\PpdbPeriod;
use App\Models\PpdbSelectionResult;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.guest')]
class Announcement extends Component
{
    use WithPagination;

    public int $periodId;

    public string $statusFilter = '';

    public string $pathwayFilter = '';

    public function mount(int|string $period): void
    {
        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $periodRecord = PpdbPeriod::query()
            ->where('school_id', $schoolId)
            ->whereKey($period)
            ->whereIn('status', [PpdbPeriod::STATUS_ANNOUNCED, PpdbPeriod::STATUS_REREGISTRATION, PpdbPeriod::STATUS_CLOSED])
            ->whereNotNull('announcement_at')
            ->whereNotNull('selection_finalized_at')
            ->where('announcement_at', '<=', now())
            ->firstOrFail();

        $this->periodId = $periodRecord->id;
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPathwayFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $period = PpdbPeriod::query()
            ->with(['academicYear', 'pathways' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->findOrFail($this->periodId);
        $allowedStatuses = [
            PpdbApplication::SELECTION_ACCEPTED,
            PpdbApplication::SELECTION_WAITLISTED,
            PpdbApplication::SELECTION_REJECTED,
        ];
        $applications = PpdbSelectionResult::query()
            ->where('school_id', $period->school_id)
            ->where('ppdb_period_id', $period->id)
            ->whereNull('invalidated_at')
            ->whereIn('selection_status', $allowedStatuses)
            ->with(['application:id,application_number', 'application.candidate:id,ppdb_application_id,name', 'pathway:id,name'])
            ->when(in_array($this->statusFilter, $allowedStatuses, true), fn ($query) => $query->where('selection_status', $this->statusFilter))
            ->when($this->pathwayFilter !== '', fn ($query) => $query->where('ppdb_pathway_id', $this->pathwayFilter))
            ->orderByRaw("CASE selection_status WHEN 'accepted' THEN 1 WHEN 'waitlisted' THEN 2 WHEN 'rejected' THEN 3 ELSE 4 END")
            ->orderBy('rank')
            ->paginate(25);

        $applications->through(fn (PpdbSelectionResult $result): object => (object) [
            'application_number' => self::maskApplicationNumber($result->application->application_number),
            'candidate' => (object) ['name' => self::maskCandidateName($result->application?->candidate?->name)],
            'pathway' => (object) ['name' => $result->pathway?->name],
            'selection_status' => $result->selection_status,
        ]);

        return view('livewire.public.ppdb.announcement', [
            'period' => $period,
            'applications' => $applications,
        ]);
    }

    private static function maskApplicationNumber(string $applicationNumber): string
    {
        return '****-'.substr($applicationNumber, -4);
    }

    private static function maskCandidateName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return '';
        }

        return collect($parts)
            ->map(fn (string $part): string => mb_substr($part, 0, 1).'***')
            ->implode(' ');
    }
}
