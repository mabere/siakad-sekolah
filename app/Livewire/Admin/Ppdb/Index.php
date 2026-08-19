<?php

namespace App\Livewire\Admin\Ppdb;

use App\Models\AcademicYear;
use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use App\Services\PpdbPeriodService;
use App\Services\PpdbPeriodWorkflowService;
use App\Services\PpdbSelectionFinalizationService;
use App\Support\CurrentSchool;
use App\Support\PpdbPermissions;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $isFormOpen = false;

    public bool $isEdit = false;

    public int|string|null $editingId = null;

    public string $name = '';

    public string $code = '';

    public string $academic_year_id = '';

    public string $registration_starts_at = '';

    public string $registration_ends_at = '';

    public string $verification_ends_at = '';

    public string $announcement_at = '';

    public string $re_registration_ends_at = '';

    public string $status = PpdbPeriod::STATUS_DRAFT;

    public bool $payment_required = true;

    public string|int|float $default_registration_fee = 0;

    public string $payment_bank = '';

    public string $payment_account_name = '';

    public string $payment_account_number = '';

    public string $payment_instructions = '';

    public ?int $reopenVerificationPeriodId = null;

    public string $reopenVerificationPeriodName = '';

    public string $reopenVerificationReason = '';

    public ?int $cancelFinalizationPeriodId = null;

    public string $cancelFinalizationPeriodName = '';

    public string $cancelFinalizationReason = '';

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $schoolId = app(CurrentSchool::class)->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('ppdb_periods', 'code')->where(fn ($query) => $query->where('school_id', $schoolId))->ignore($this->editingId),
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'registration_starts_at' => ['required', 'date'],
            'registration_ends_at' => ['required', 'date', 'after:registration_starts_at'],
            'verification_ends_at' => ['nullable', 'date', 'after_or_equal:registration_ends_at'],
            'announcement_at' => ['nullable', 'date', 'after_or_equal:verification_ends_at'],
            're_registration_ends_at' => ['nullable', 'date', 'after_or_equal:announcement_at'],
            'status' => ['required', Rule::in(PpdbPeriod::STATUSES)],
            'payment_required' => ['boolean'],
            'default_registration_fee' => ['required', 'numeric', 'min:0'],
            'payment_bank' => ['nullable', 'string', 'max:100'],
            'payment_account_name' => ['nullable', 'string', 'max:150'],
            'payment_account_number' => ['nullable', 'string', 'max:50'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function create(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
        $this->resetForm();
        $this->isFormOpen = true;
        $this->registration_starts_at = now()->format('Y-m-d\TH:i');
        $this->registration_ends_at = now()->addDays(30)->format('Y-m-d\TH:i');
        $this->name = 'PPDB '.now()->year;
        $this->code = 'PPDB-'.now()->year;
    }

    public function edit(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
        $this->resetForm();
        $period = $this->periodQuery()->whereKey($id)->firstOrFail();
        $this->isEdit = true;
        $this->editingId = $period->id;
        $this->isFormOpen = true;
        $this->name = $period->name;
        $this->code = $period->code;
        $this->academic_year_id = (string) $period->academic_year_id;
        $this->registration_starts_at = CarbonImmutable::parse($period->registration_starts_at)->format('Y-m-d\TH:i');
        $this->registration_ends_at = CarbonImmutable::parse($period->registration_ends_at)->format('Y-m-d\TH:i');
        $this->verification_ends_at = filled($period->verification_ends_at) ? CarbonImmutable::parse($period->verification_ends_at)->format('Y-m-d\TH:i') : '';
        $this->announcement_at = filled($period->announcement_at) ? CarbonImmutable::parse($period->announcement_at)->format('Y-m-d\TH:i') : '';
        $this->re_registration_ends_at = filled($period->re_registration_ends_at) ? CarbonImmutable::parse($period->re_registration_ends_at)->format('Y-m-d\TH:i') : '';
        $this->status = $period->status;
        $this->payment_required = $period->payment_required;
        $this->default_registration_fee = $period->default_registration_fee;
        $this->payment_bank = (string) data_get($period->settings, 'payment_bank', '');
        $this->payment_account_name = (string) data_get($period->settings, 'payment_account_name', '');
        $this->payment_account_number = (string) data_get($period->settings, 'payment_account_number', '');
        $this->payment_instructions = (string) data_get($period->settings, 'payment_instructions', '');
    }

    public function save(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();
        $attributes = [
            'academic_year_id' => (int) $this->academic_year_id,
            'name' => trim($this->name),
            'code' => strtoupper(trim($this->code)),
            'level' => app(CurrentSchool::class)->get()->level,
            'registration_starts_at' => $this->registration_starts_at,
            'registration_ends_at' => $this->registration_ends_at,
            'verification_ends_at' => $this->verification_ends_at ?: null,
            'announcement_at' => $this->announcement_at ?: null,
            're_registration_ends_at' => $this->re_registration_ends_at ?: null,
            'status' => $this->status,
            'payment_required' => $this->payment_required,
            'default_registration_fee' => $this->default_registration_fee,
            'settings' => [
                'payment_bank' => trim($this->payment_bank) ?: null,
                'payment_account_name' => trim($this->payment_account_name) ?: null,
                'payment_account_number' => trim($this->payment_account_number) ?: null,
                'payment_instructions' => trim($this->payment_instructions) ?: null,
            ],
        ];

        if ($this->isEdit) {
            $period = $this->periodQuery()->whereKey($this->editingId)->firstOrFail();
            $currentSettings = $period->getAttribute('settings');
            if (! is_array($currentSettings)) {
                $currentSettings = [];
            }
            $attributes['settings'] = [
                ...$currentSettings,
                ...$attributes['settings'],
            ];
            $previousStatus = $period->status;

            try {
                DB::transaction(function () use ($period, $attributes, $previousStatus): void {
                    $period->update([...$attributes, 'status' => $previousStatus]);
                    app(PpdbPeriodWorkflowService::class)->transition($period->refresh(), $this->status);
                });
            } catch (\DomainException $exception) {
                $this->addError('status', $exception->getMessage());

                return;
            }

            session()->flash('message', 'Periode PPDB berhasil diperbarui.');
        } else {
            if ($this->status !== PpdbPeriod::STATUS_DRAFT) {
                $this->addError('status', 'Periode baru harus dibuat sebagai draft, lalu dipublikasikan setelah jadwal lengkap.');

                return;
            }

            app(PpdbPeriodService::class)->create($schoolId, $attributes);
            session()->flash('message', 'Periode PPDB dan jalur bawaan berhasil dibuat.');
        }

        $this->resetForm();
    }

    public function togglePathway(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
        $pathway = PpdbPathway::query()
            ->whereHas('period', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();

        $pathway->update(['is_active' => ! $pathway->is_active]);
    }

    public function addOptionalPathway(int|string $id, string $code): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
        if (! in_array($code, [PpdbPathway::ZONASI, PpdbPathway::AFIRMASI], true)) {
            abort(422);
        }

        $period = $this->periodQuery()->whereKey($id)->firstOrFail();
        app(PpdbPeriodService::class)->addOptionalPathway($period, $code);
        session()->flash('message', 'Jalur '.ucfirst($code).' berhasil diaktifkan.');
    }

    public function updatePathwaySettings(int|string $id, int|string $quota, int|float|string $registrationFee): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_PERIODS);
        $this->validate([
            'default_registration_fee' => ['nullable', 'numeric', 'min:0'],
        ]);
        $pathway = PpdbPathway::query()
            ->whereHas('period', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();
        $pathway->update([
            'quota' => max(0, (int) $quota),
            'registration_fee' => max(0, (float) $registrationFee),
        ]);
    }

    public function finalizeSelection(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::FINALIZE_SELECTION);
        $period = $this->periodQuery()->whereKey($id)->firstOrFail();

        try {
            app(PpdbSelectionFinalizationService::class)->finalize($period);
        } catch (\DomainException $exception) {
            $this->addError('status', $exception->getMessage());

            return;
        }

        session()->flash('message', 'Hasil seleksi berhasil difinalisasi dan dikunci sebagai snapshot.');
    }

    public function openReopenVerification(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::REOPEN_VERIFICATION);
        $period = $this->periodQuery()->whereKey($id)->firstOrFail();

        if ($period->status !== PpdbPeriod::STATUS_CLOSED) {
            session()->flash('error', 'Buka kembali verifikasi hanya tersedia untuk periode berstatus closed.');

            return;
        }

        $this->reopenVerificationPeriodId = $period->id;
        $this->reopenVerificationPeriodName = $period->name;
        $this->reopenVerificationReason = '';
        $this->resetValidation('reopenVerificationReason');
    }

    public function cancelReopenVerification(): void
    {
        $this->resetReopenVerification();
    }

    public function reopenVerification(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::REOPEN_VERIFICATION);
        $this->validate(['reopenVerificationReason' => ['required', 'string', 'max:1000']]);
        $period = $this->periodQuery()->whereKey($this->reopenVerificationPeriodId)->firstOrFail();

        try {
            app(PpdbPeriodWorkflowService::class)->reopenVerification($period, $this->reopenVerificationReason);
        } catch (\DomainException|\InvalidArgumentException $exception) {
            $this->addError('reopenVerificationReason', $exception->getMessage());

            return;
        }

        $this->resetReopenVerification();
        session()->flash('message', 'Periode berhasil dibuka kembali untuk penyelesaian verifikasi.');
    }

    public function openCancelFinalization(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::CANCEL_FINALIZATION);
        $period = $this->periodQuery()->whereKey($id)->firstOrFail();

        if (! in_array($period->status, [PpdbPeriod::STATUS_SELECTION, PpdbPeriod::STATUS_CLOSED], true) || $period->selection_finalized_at === null) {
            session()->flash('error', 'Pembatalan finalisasi hanya tersedia untuk hasil seleksi yang sudah difinalisasi pada tahap seleksi atau closed.');

            return;
        }

        $this->cancelFinalizationPeriodId = $period->id;
        $this->cancelFinalizationPeriodName = $period->name;
        $this->cancelFinalizationReason = '';
        $this->resetValidation('cancelFinalizationReason');
    }

    public function closeCancelFinalization(): void
    {
        $this->resetCancelFinalization();
    }

    public function cancelFinalization(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::CANCEL_FINALIZATION);
        $this->validate(['cancelFinalizationReason' => ['required', 'string', 'max:1000']]);
        $period = $this->periodQuery()->whereKey($this->cancelFinalizationPeriodId)->firstOrFail();

        try {
            app(PpdbSelectionFinalizationService::class)->cancelFinalization($period, $this->cancelFinalizationReason);
        } catch (\DomainException|\InvalidArgumentException $exception) {
            $this->addError('cancelFinalizationReason', $exception->getMessage());

            return;
        }

        $this->resetCancelFinalization();
        session()->flash('message', 'Finalisasi dibatalkan. Periode kembali ke tahap verifikasi dan snapshot lama disimpan sebagai riwayat.');
    }

    public function closeForm(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'isFormOpen',
            'isEdit',
            'editingId',
            'name',
            'code',
            'academic_year_id',
            'registration_starts_at',
            'registration_ends_at',
            'verification_ends_at',
            'announcement_at',
            're_registration_ends_at',
            'status',
            'payment_required',
            'default_registration_fee',
            'payment_bank',
            'payment_account_name',
            'payment_account_number',
            'payment_instructions',
        ]);
        $this->resetReopenVerification();
        $this->resetCancelFinalization();
        $this->status = PpdbPeriod::STATUS_DRAFT;
        $this->payment_required = true;
        $this->default_registration_fee = 0;
        $this->resetValidation();
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $periods = PpdbPeriod::query()
            ->forSchool($schoolId)
            ->with(['academicYear', 'pathways' => fn ($query) => $query->orderBy('sort_order')])
            ->withCount('applications')
            ->latest('id')
            ->get();

        $academicYears = AcademicYear::query()
            ->where('school_id', $schoolId)
            ->orderByDesc('name')
            ->orderByDesc('semester')
            ->get(['id', 'name', 'semester']);

        return view('livewire.admin.ppdb.index', [
            'periods' => $periods,
            'academicYears' => $academicYears,
            'schoolLevel' => app(CurrentSchool::class)->get()->level,
            'canManagePeriods' => PpdbPermissions::allows(PpdbPermissions::MANAGE_PERIODS),
            'canReopenVerification' => PpdbPermissions::allows(PpdbPermissions::REOPEN_VERIFICATION),
            'canCancelFinalization' => PpdbPermissions::allows(PpdbPermissions::CANCEL_FINALIZATION),
        ]);
    }

    /** @return Builder<PpdbPeriod> */
    private function periodQuery(): Builder
    {
        return PpdbPeriod::query()->forSchool(app(CurrentSchool::class)->id());
    }

    private function resetReopenVerification(): void
    {
        $this->reopenVerificationPeriodId = null;
        $this->reopenVerificationPeriodName = '';
        $this->reopenVerificationReason = '';
        $this->resetValidation('reopenVerificationReason');
    }

    private function resetCancelFinalization(): void
    {
        $this->cancelFinalizationPeriodId = null;
        $this->cancelFinalizationPeriodName = '';
        $this->cancelFinalizationReason = '';
        $this->resetValidation('cancelFinalizationReason');
    }
}
