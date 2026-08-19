<?php

namespace App\Livewire\Admin\Ppdb;

use App\Models\PpdbApplication;
use App\Models\PpdbDocument;
use App\Models\PpdbPathway;
use App\Models\PpdbPayment;
use App\Models\PpdbPeriod;
use App\Services\PpdbApplicationService;
use App\Services\PpdbConversionService;
use App\Services\PpdbFileService;
use App\Services\PpdbPaymentService;
use App\Support\CurrentSchool;
use App\Support\PpdbPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class Applications extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $verificationFilter = '';

    public string $selectionFilter = '';

    public string $periodFilter = '';

    public bool $isOfflineFormOpen = false;

    public string $offlinePeriodId = '';

    public string $offlinePathwayId = '';

    public string $offlineCandidateName = '';

    public string $offlineCandidateNik = '';

    public string $offlineCandidateNisn = '';

    public string $offlineGender = '';

    public string $offlineBirthPlace = '';

    public string $offlineBirthDate = '';

    public string $offlinePreviousSchool = '';

    public string $offlineAddress = '';

    public string $offlineGuardianRelationship = 'ayah';

    public string $offlineGuardianName = '';

    public string $offlineGuardianPhone = '';

    public string $offlineContactPhone = '';

    public string $offlineContactEmail = '';

    public ?int $selectedApplicationId = null;

    public ?string $rotatedAccessCode = null;

    /** @var list<array{role: string, username: string, password: string, delivery: string}> */
    public array $conversionCredentials = [];

    public string $decisionNote = '';

    public string $scoreCriterion = '';

    public string $scoreValue = '';

    public string $scoreNotes = '';

    public string $offlineRequirementId = '';

    public ?TemporaryUploadedFile $offlineDocument = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedVerificationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodFilter(): void
    {
        $this->resetPage();
    }

    public function openOfflineForm(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::REGISTER_OFFLINE);
        $this->resetOfflineForm();
        $this->isOfflineFormOpen = true;
        $this->offlinePeriodId = (string) PpdbPeriod::query()
            ->forSchool(app(CurrentSchool::class)->id())
            ->where('status', PpdbPeriod::STATUS_OPEN)
            ->where('registration_starts_at', '<=', now())
            ->where('registration_ends_at', '>=', now())
            ->latest('id')
            ->value('id');
        $this->updatedOfflinePeriodId();
    }

    public function updatedOfflinePeriodId(): void
    {
        $this->offlinePathwayId = (string) PpdbPathway::query()
            ->whereHas('period', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id())->whereKey($this->offlinePeriodId))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('id');
    }

    public function saveOffline(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::REGISTER_OFFLINE);
        $this->validate([
            'offlinePeriodId' => ['required', Rule::exists('ppdb_periods', 'id')->where(fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id())->where('status', PpdbPeriod::STATUS_OPEN)->where('registration_starts_at', '<=', now())->where('registration_ends_at', '>=', now()))],
            'offlinePathwayId' => ['required', Rule::exists('ppdb_pathways', 'id')->where(fn ($query) => $query->where('is_active', true)->where('ppdb_period_id', $this->offlinePeriodId))],
            'offlineCandidateName' => ['required', 'string', 'max:255'],
            'offlineCandidateNik' => ['nullable', 'string', 'max:30'],
            'offlineCandidateNisn' => ['nullable', 'string', 'max:30'],
            'offlineGender' => ['required', Rule::in(['L', 'P'])],
            'offlineBirthPlace' => ['required', 'string', 'max:100'],
            'offlineBirthDate' => ['required', 'date'],
            'offlinePreviousSchool' => ['required', 'string', 'max:255'],
            'offlineAddress' => ['required', 'string', 'max:2000'],
            'offlineGuardianRelationship' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
            'offlineGuardianName' => ['required', 'string', 'max:255'],
            'offlineGuardianPhone' => ['required', 'string', 'max:30'],
            'offlineContactPhone' => ['required', 'string', 'max:30'],
            'offlineContactEmail' => ['nullable', 'email', 'max:255'],
        ]);

        $pathway = $this->pathwayQuery()->whereKey($this->offlinePathwayId)->firstOrFail();
        $period = $pathway->period;
        try {
            [$application, $accessCode] = app(PpdbApplicationService::class)->submitOffline(
                $period,
                $pathway,
                [
                    'name' => trim($this->offlineCandidateName),
                    'nik' => $this->offlineCandidateNik ?: null,
                    'nisn' => $this->offlineCandidateNisn ?: null,
                    'gender' => $this->offlineGender,
                    'birth_place' => trim($this->offlineBirthPlace),
                    'birth_date' => $this->offlineBirthDate,
                    'previous_school' => trim($this->offlinePreviousSchool),
                    'address' => trim($this->offlineAddress),
                ],
                [
                    'relationship' => $this->offlineGuardianRelationship,
                    'name' => trim($this->offlineGuardianName),
                    'phone' => trim($this->offlineGuardianPhone),
                ],
                $this->offlineContactEmail,
                $this->offlineContactPhone,
                (int) auth()->id(),
            );
        } catch (\DomainException $exception) {
            $this->addError('offlineCandidateName', $exception->getMessage());

            return;
        }

        $this->isOfflineFormOpen = false;
        $this->resetOfflineForm();
        session()->flash('message', 'Pendaftar offline berhasil dibuat: '.$application->application_number.' (kode akses: '.$accessCode.').');
    }

    public function showApplication(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::VIEW_APPLICATIONS);
        $this->selectedApplicationId = $this->applicationQuery()->whereKey($id)->value('id');
        $this->rotatedAccessCode = null;
        $this->conversionCredentials = [];
        $this->decisionNote = '';
        $this->scoreCriterion = '';
        $this->scoreValue = '';
        $this->scoreNotes = '';
        $this->offlineRequirementId = '';
        $this->offlineDocument = null;
    }

    public function closeApplication(): void
    {
        $this->selectedApplicationId = null;
        $this->rotatedAccessCode = null;
        $this->conversionCredentials = [];
        $this->decisionNote = '';
        $this->scoreCriterion = '';
        $this->scoreValue = '';
        $this->scoreNotes = '';
        $this->offlineRequirementId = '';
        $this->offlineDocument = null;
    }

    public function resetAccessCode(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::RESET_ACCESS_CODE);
        $application = $this->applicationQuery()->whereKey($id)->firstOrFail();
        $accessCode = (string) random_int(100000, 999999);
        $application->update(['access_code_hash' => Hash::make($accessCode)]);
        app(PpdbApplicationService::class)->audit($application, 'access_code_reset', null, null);

        $this->rotatedAccessCode = $accessCode;
        session()->flash('message', 'PIN berhasil di-reset. Sampaikan PIN baru kepada calon setelah verifikasi identitas.');
    }

    public function uploadDocument(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::VERIFY_DOCUMENTS);
        $application = $this->applicationQuery()->with('pathway')->whereKey($this->selectedApplicationId)->firstOrFail();
        if ($application->source !== PpdbApplication::SOURCE_OFFLINE) {
            session()->flash('error', 'Pengunggahan berkas dari panel admin hanya tersedia untuk pendaftar offline.');

            return;
        }
        if (! $this->ensureVerificationWindow($application->period)) {
            return;
        }

        $this->validate([
            'offlineRequirementId' => ['required', 'integer'],
        ]);

        $requirement = $application->pathway->requirements()->whereKey($this->offlineRequirementId)->firstOrFail();
        $this->validate(['offlineDocument' => app(PpdbFileService::class)->rules($requirement)]);
        $fileService = app(PpdbFileService::class);
        $stored = $fileService->store($this->offlineDocument, 'ppdb/'.$application->school_id.'/'.$application->id, $requirement);
        $existing = $application->documents()->where('ppdb_requirement_id', $requirement->id)->first();
        $oldPath = $existing?->file_path;
        $document = $application->documents()->updateOrCreate(
            ['ppdb_requirement_id' => $requirement->id],
            [
                'uploaded_by' => auth()->id(),
                'file_path' => $stored['path'],
                'original_name' => $stored['original_name'],
                'mime_type' => $stored['mime_type'],
                'file_size' => $stored['file_size'],
                'status' => PpdbDocument::STATUS_PENDING,
                'rejection_reason' => null,
                'verified_at' => null,
            ],
        );
        $fileService->delete($oldPath);
        $document->touch();
        $this->offlineRequirementId = '';
        $this->offlineDocument = null;
        session()->flash('message', 'Dokumen berhasil diunggah dan menunggu verifikasi.');
    }

    public function verifyPayment(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::VERIFY_PAYMENTS);
        $payment = PpdbPayment::query()
            ->with('application.period')
            ->whereHas('application', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();
        if (! $this->ensureVerificationWindow($payment->application?->period)) {
            return;
        }
        try {
            app(PpdbPaymentService::class)->verify($payment);
        } catch (\DomainException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }
        session()->flash('message', 'Pembayaran berhasil diverifikasi.');
    }

    public function rejectPayment(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::VERIFY_PAYMENTS);
        $this->validate(['decisionNote' => ['required', 'string', 'max:1000']]);

        $payment = PpdbPayment::query()
            ->with('application.period')
            ->whereHas('application', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();
        if (! $this->ensureVerificationWindow($payment->application?->period)) {
            return;
        }
        $note = trim($this->decisionNote);
        try {
            app(PpdbPaymentService::class)->reject($payment, $note);
        } catch (\DomainException|\InvalidArgumentException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }
        $this->decisionNote = '';
        session()->flash('message', 'Pembayaran ditolak dan alasan berhasil disimpan.');
    }

    public function setDocumentStatus(int|string $id, string $status): void
    {
        PpdbPermissions::authorize(PpdbPermissions::VERIFY_DOCUMENTS);
        if (! in_array($status, [PpdbDocument::STATUS_VERIFIED, PpdbDocument::STATUS_REJECTED], true)) {
            abort(422);
        }

        $document = PpdbDocument::query()
            ->with('application.period')
            ->whereHas('application', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()))
            ->whereKey($id)
            ->firstOrFail();
        if (! $this->ensureVerificationWindow($document->application?->period)) {
            return;
        }
        $document->update([
            'status' => $status,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => $status === PpdbDocument::STATUS_REJECTED ? trim($this->decisionNote) : null,
        ]);
        $this->decisionNote = '';
        session()->flash('message', 'Status dokumen berhasil diperbarui.');
    }

    public function setVerificationStatus(int|string $id, string $status): void
    {
        PpdbPermissions::authorize(PpdbPermissions::VERIFY_DOCUMENTS);
        if (! in_array($status, [PpdbApplication::VERIFICATION_REVISION, PpdbApplication::VERIFICATION_REJECTED, PpdbApplication::VERIFICATION_VERIFIED], true)) {
            abort(422);
        }

        $application = $this->applicationQuery()->whereKey($id)->firstOrFail();
        if (! $this->ensureVerificationWindow($application->period)) {
            return;
        }
        if ($status === PpdbApplication::VERIFICATION_VERIFIED) {
            $requiredIds = $application->pathway->requirements()->where('is_required', true)->pluck('id');
            $verifiedIds = $application->documents()->where('status', 'verified')->pluck('ppdb_requirement_id');
            if ($requiredIds->diff($verifiedIds)->isNotEmpty()) {
                session()->flash('error', 'Semua dokumen wajib harus diverifikasi terlebih dahulu.');

                return;
            }
            if ($application->payment_status !== 'not_required' && $application->payment_status !== PpdbPayment::STATUS_VERIFIED) {
                session()->flash('error', 'Pembayaran pendaftaran belum diverifikasi.');

                return;
            }
        }

        $fromStatus = $application->verification_status;
        $application->update([
            'verification_status' => $status,
            'revision_note' => $status === PpdbApplication::VERIFICATION_REVISION ? trim($this->decisionNote) : null,
            'rejection_note' => $status === PpdbApplication::VERIFICATION_REJECTED ? trim($this->decisionNote) : null,
            'verified_at' => $status === PpdbApplication::VERIFICATION_VERIFIED ? now() : null,
        ]);
        app(PpdbApplicationService::class)->audit($application, 'verification_status_changed', $fromStatus, $status, ['note' => $this->decisionNote]);
        $this->decisionNote = '';
        session()->flash('message', 'Status verifikasi berhasil diperbarui.');
    }

    public function setSelectionStatus(int|string $id, string $status): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_SELECTION);
        if (! in_array($status, [PpdbApplication::SELECTION_ELIGIBLE, PpdbApplication::SELECTION_ACCEPTED, PpdbApplication::SELECTION_WAITLISTED, PpdbApplication::SELECTION_REJECTED], true)) {
            abort(422);
        }

        $application = $this->applicationQuery()->whereKey($id)->firstOrFail();
        if ($application->period->selection_finalized_at !== null) {
            session()->flash('error', 'Hasil seleksi sudah difinalisasi dan tidak dapat diubah.');

            return;
        }
        if ($application->verification_status !== PpdbApplication::VERIFICATION_VERIFIED) {
            session()->flash('error', 'Pendaftar harus terverifikasi sebelum masuk seleksi.');

            return;
        }
        $quota = (int) $application->pathway->quota;
        if ($status === PpdbApplication::SELECTION_ACCEPTED && $quota > 0) {
            $acceptedCount = $this->applicationQuery()
                ->where('ppdb_period_id', $application->ppdb_period_id)
                ->where('ppdb_pathway_id', $application->ppdb_pathway_id)
                ->where('selection_status', PpdbApplication::SELECTION_ACCEPTED)
                ->where('id', '<>', $application->id)
                ->count();
            if ($acceptedCount >= $quota) {
                session()->flash('error', 'Kuota jalur ini sudah terpenuhi.');

                return;
            }
        }
        $fromStatus = $application->selection_status;
        $application->update([
            'selection_status' => $status,
            'selected_at' => now(),
        ]);
        app(PpdbApplicationService::class)->audit($application, 'selection_status_changed', $fromStatus, $status, ['note' => $this->decisionNote]);
        session()->flash('message', 'Hasil seleksi berhasil diperbarui.');
    }

    public function saveSelectionScore(): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_SELECTION);
        $this->validate([
            'selectedApplicationId' => ['required', 'integer'],
            'scoreCriterion' => ['required', 'string', 'max:100'],
            'scoreValue' => ['required', 'numeric', 'min:0', 'max:100'],
            'scoreNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application = $this->applicationQuery()->whereKey($this->selectedApplicationId)->firstOrFail();
        if ($application->period->selection_finalized_at !== null) {
            session()->flash('error', 'Hasil seleksi sudah difinalisasi dan tidak dapat diubah.');

            return;
        }
        if ($application->verification_status !== PpdbApplication::VERIFICATION_VERIFIED) {
            session()->flash('error', 'Pendaftar harus terverifikasi sebelum diberi skor.');

            return;
        }

        $criterion = trim($this->scoreCriterion);
        $score = $application->selectionScores()->updateOrCreate(
            ['criterion' => $criterion],
            [
                'assessed_by' => auth()->id(),
                'score' => (float) $this->scoreValue,
                'notes' => trim($this->scoreNotes) ?: null,
                'assessed_at' => now(),
            ],
        );
        app(PpdbApplicationService::class)->audit($application, 'selection_score_saved', null, null, [
            'criterion' => $score->criterion,
            'score' => (float) $score->score,
        ]);

        $this->scoreCriterion = '';
        $this->scoreValue = '';
        $this->scoreNotes = '';
        session()->flash('message', 'Skor seleksi berhasil disimpan.');
    }

    public function removeSelectionScore(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_SELECTION);
        $application = $this->applicationQuery()->whereKey($this->selectedApplicationId)->firstOrFail();
        if ($application->period->selection_finalized_at !== null) {
            session()->flash('error', 'Hasil seleksi sudah difinalisasi dan tidak dapat diubah.');

            return;
        }
        $score = $application->selectionScores()->whereKey($id)->firstOrFail();
        $criterion = $score->criterion;
        $score->delete();
        app(PpdbApplicationService::class)->audit($application, 'selection_score_deleted', null, null, [
            'criterion' => $criterion,
        ]);
        session()->flash('message', 'Skor seleksi berhasil dihapus.');
    }

    public function exportApplications(): StreamedResponse
    {
        PpdbPermissions::authorize(PpdbPermissions::EXPORT_APPLICATIONS);
        $query = $this->applicationQuery()
            ->with(['candidate', 'pathway', 'selectionScores'])
            ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                $search = trim($this->search);
                $query->where('application_number', 'like', '%'.$search.'%')
                    ->orWhereHas('candidate', fn ($candidate) => $candidate->where('name', 'like', '%'.$search.'%'));
            }))
            ->when($this->verificationFilter !== '', fn ($query) => $query->where('verification_status', $this->verificationFilter))
            ->when($this->selectionFilter !== '', fn ($query) => $query->where('selection_status', $this->selectionFilter))
            ->when($this->periodFilter !== '', fn ($query) => $query->where('ppdb_period_id', $this->periodFilter))
            ->orderBy('id');

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Nomor Pendaftaran', 'Nama Calon Siswa', 'Jalur', 'Sumber',
                'Verifikasi', 'Pembayaran', 'Seleksi', 'Total Skor',
                'Rata-rata Skor', 'Tanggal Daftar',
            ], ';');

            foreach ($query->lazyById(100) as $application) {
                $scores = $application->selectionScores;
                $totalScore = (float) $scores->sum(fn ($score): float => (float) $score->score);
                $averageScore = $scores->isNotEmpty() ? $totalScore / $scores->count() : 0.0;
                $submittedAt = $application->submitted_at !== null
                    ? Carbon::parse($application->submitted_at)->format('Y-m-d H:i:s')
                    : null;

                fputcsv($handle, [
                    self::csvValue($application->application_number),
                    self::csvValue($application->candidate?->name),
                    self::csvValue($application->pathway?->name),
                    self::csvValue(ucfirst((string) $application->source)),
                    self::csvValue(match ($application->verification_status) {
                        PpdbApplication::VERIFICATION_VERIFIED => 'Terverifikasi',
                        PpdbApplication::VERIFICATION_REVISION => 'Perlu perbaikan',
                        PpdbApplication::VERIFICATION_REJECTED => 'Ditolak',
                        default => 'Menunggu verifikasi',
                    }),
                    self::csvValue(match ($application->payment_status) {
                        PpdbPayment::STATUS_VERIFIED => 'Terverifikasi',
                        PpdbPayment::STATUS_REJECTED => 'Ditolak',
                        PpdbPayment::STATUS_SUBMITTED => 'Menunggu verifikasi',
                        'not_required' => 'Tidak diperlukan',
                        default => 'Belum dibayar',
                    }),
                    self::csvValue(match ($application->selection_status) {
                        PpdbApplication::SELECTION_ACCEPTED => 'Diterima',
                        PpdbApplication::SELECTION_WAITLISTED => 'Cadangan',
                        PpdbApplication::SELECTION_REJECTED => 'Tidak diterima',
                        default => 'Belum dinilai',
                    }),
                    number_format($totalScore, 2, '.', ''),
                    number_format($averageScore, 2, '.', ''),
                    $submittedAt,
                ], ';');
            }

            fclose($handle);
        }, 'ppdb-pendaftar-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function openReregistration(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_REREGISTRATION);
        $application = $this->applicationQuery()->whereKey($id)->firstOrFail();
        if ($application->selection_status !== PpdbApplication::SELECTION_ACCEPTED) {
            session()->flash('error', 'Daftar ulang hanya dapat dibuka untuk pendaftar yang diterima.');

            return;
        }
        $application->reRegistration()->firstOrCreate([], ['status' => 'pending']);
        $application->update(['reregistration_status' => 'pending']);
        app(PpdbApplicationService::class)->audit($application, 'reregistration_opened', null, 'pending');
        session()->flash('message', 'Daftar ulang berhasil dibuka.');
    }

    public function verifyReregistration(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::MANAGE_REREGISTRATION);
        $application = $this->applicationQuery()->whereKey($id)->firstOrFail();
        $registration = $application->reRegistration;
        if (! $registration || $registration->status !== 'confirmed') {
            session()->flash('error', 'Calon siswa belum mengonfirmasi daftar ulang.');

            return;
        }
        $registration->update(['status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now()]);
        $application->update(['reregistration_status' => 'verified']);
        session()->flash('message', 'Daftar ulang berhasil diverifikasi.');
    }

    public function convertToStudent(int|string $id): void
    {
        PpdbPermissions::authorize(PpdbPermissions::CONVERT_STUDENT);
        $application = $this->applicationQuery()->whereKey($id)->firstOrFail();

        try {
            $result = app(PpdbConversionService::class)->convertWithCredentials($application);
        } catch (\DomainException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }

        $this->conversionCredentials = $result['credentials'];
        session()->flash('message', 'Pendaftar berhasil dikonversi menjadi siswa dengan NIS '.$result['student']->nis.'.');
    }

    public function render(): View
    {
        PpdbPermissions::authorize(PpdbPermissions::VIEW_APPLICATIONS);
        $schoolId = app(CurrentSchool::class)->id();
        $applications = $this->applicationQuery()
            ->with(['candidate', 'pathway', 'period'])
            ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('application_number', 'like', '%'.trim($this->search).'%')
                    ->orWhereHas('candidate', fn ($candidate) => $candidate->where('name', 'like', '%'.trim($this->search).'%'));
            }))
            ->when($this->verificationFilter !== '', fn ($query) => $query->where('verification_status', $this->verificationFilter))
            ->when($this->selectionFilter !== '', fn ($query) => $query->where('selection_status', $this->selectionFilter))
            ->when($this->periodFilter !== '', fn ($query) => $query->where('ppdb_period_id', $this->periodFilter))
            ->latest('id')
            ->paginate(20);

        $periods = PpdbPeriod::query()->forSchool($schoolId)->latest('id')->get(['id', 'name']);
        $offlinePeriods = PpdbPeriod::query()->forSchool($schoolId)->where('status', PpdbPeriod::STATUS_OPEN)->where('registration_starts_at', '<=', now())->where('registration_ends_at', '>=', now())->with('pathways')->latest('id')->get();
        $selectedApplication = $this->selectedApplicationId
            ? $this->applicationQuery()->with(['candidate', 'period', 'guardians', 'documents.requirement', 'payments.histories.actor', 'selectionScores', 'reRegistration', 'pathway.requirements'])->whereKey($this->selectedApplicationId)->first()
            : null;

        return view('livewire.admin.ppdb.applications', [
            'applications' => $applications,
            'periods' => $periods,
            'offlinePeriods' => $offlinePeriods,
            'selectedApplication' => $selectedApplication,
            'offlinePathways' => $this->offlinePeriodId !== '' ? PpdbPathway::query()->where('ppdb_period_id', $this->offlinePeriodId)->where('is_active', true)->orderBy('sort_order')->get() : collect(),
        ]);
    }

    /** @return Builder<PpdbApplication> */
    private function applicationQuery(): Builder
    {
        return PpdbApplication::query()->where('school_id', app(CurrentSchool::class)->id());
    }

    /** @return Builder<PpdbPathway> */
    private function pathwayQuery(): Builder
    {
        return PpdbPathway::query()->whereHas('period', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id()));
    }

    private function resetOfflineForm(): void
    {
        $this->reset([
            'offlinePeriodId', 'offlinePathwayId', 'offlineCandidateName', 'offlineCandidateNik', 'offlineCandidateNisn', 'offlineGender', 'offlineBirthPlace',
            'offlineBirthDate', 'offlinePreviousSchool', 'offlineAddress', 'offlineGuardianRelationship',
            'offlineGuardianName', 'offlineGuardianPhone', 'offlineContactPhone', 'offlineContactEmail',
        ]);
        $this->offlineGuardianRelationship = 'ayah';
        $this->resetValidation();
    }

    private function ensureVerificationWindow(?PpdbPeriod $period): bool
    {
        if ($period?->allowsVerification()) {
            return true;
        }

        session()->flash('error', 'Verifikasi hanya dapat dilakukan saat periode berstatus open atau verification.');

        return false;
    }

    public function ppdbRoutePrefix(): string
    {
        return match (session('active_role')) {
            'Super Admin', 'Admin Sekolah' => 'admin',
            'Kepala Sekolah' => 'kepsek',
            'Staf Tata Usaha', 'Panitia PPDB' => 'tu',
            default => 'admin',
        };
    }

    private static function csvValue(?string $value): string
    {
        $value = (string) $value;

        return in_array($value[0] ?? '', ['=', '+', '-', '@'], true) ? "'".$value : $value;
    }
}
