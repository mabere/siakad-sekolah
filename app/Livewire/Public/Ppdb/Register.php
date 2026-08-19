<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\PpdbApplication;
use App\Models\PpdbDocument;
use App\Models\PpdbPathway;
use App\Models\PpdbPeriod;
use App\Models\PpdbRequirement;
use App\Models\School;
use App\Services\PpdbApplicationService;
use App\Services\PpdbFileService;
use App\Services\PpdbPaymentService;
use App\Services\PpdbReceiptService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\Response;

#[Layout('components.layouts.guest')]
class Register extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    public int $periodId;

    public string $pathwayId = '';

    public string $candidateName = '';

    public string $candidateNik = '';

    public string $candidateNisn = '';

    public string $candidateGender = '';

    public string $birthPlace = '';

    public string $birthDate = '';

    public string $previousSchool = '';

    public string $address = '';

    public string $village = '';

    public string $district = '';

    public string $regency = '';

    public string $province = '';

    public string $postalCode = '';

    public string $guardianRelationship = 'ayah';

    public string $guardianName = '';

    public string $guardianNik = '';

    public string $guardianPhone = '';

    public string $guardianEmail = '';

    public string $guardianOccupation = '';

    public string $guardianAddress = '';

    public string $contactEmail = '';

    public string $contactPhone = '';

    /** @var array<int|string, mixed> */
    public array $documents = [];

    public ?string $applicationNumber = null;

    public ?string $accessCode = null;

    public ?TemporaryUploadedFile $paymentProof = null;

    public string $paymentNotes = '';

    public ?string $paymentStatus = null;

    public function mount(int|string $period): void
    {
        $school = School::query()->where('is_active', true)->orderBy('id')->firstOrFail();
        $periodRecord = PpdbPeriod::query()
            ->where('school_id', $school->id)
            ->whereKey($period)
            ->where('status', PpdbPeriod::STATUS_OPEN)
            ->where('registration_starts_at', '<=', now())
            ->where('registration_ends_at', '>=', now())
            ->firstOrFail();

        $this->periodId = $periodRecord->id;
        $this->pathwayId = (string) $periodRecord->pathways()->where('is_active', true)->orderBy('sort_order')->value('id');
    }

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        $requirements = $this->requirements();
        $rules = [
            'pathwayId' => ['required', Rule::exists('ppdb_pathways', 'id')->where(fn ($query) => $query->where('ppdb_period_id', $this->periodId)->where('is_active', true))],
            'candidateName' => ['required', 'string', 'max:255'],
            'candidateNik' => ['nullable', 'string', 'max:30'],
            'candidateNisn' => ['nullable', 'string', 'max:30'],
            'candidateGender' => ['required', Rule::in(['L', 'P'])],
            'birthPlace' => ['required', 'string', 'max:100'],
            'birthDate' => ['required', 'date'],
            'previousSchool' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'village' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'regency' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postalCode' => ['nullable', 'string', 'max:10'],
            'guardianRelationship' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
            'guardianName' => ['required', 'string', 'max:255'],
            'guardianNik' => ['nullable', 'string', 'max:30'],
            'guardianPhone' => ['required', 'string', 'max:30'],
            'guardianEmail' => ['nullable', 'email', 'max:255'],
            'guardianOccupation' => ['nullable', 'string', 'max:255'],
            'guardianAddress' => ['nullable', 'string', 'max:2000'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['required', 'string', 'max:30'],
        ];

        foreach ($requirements as $requirement) {
            $rules['documents.'.$requirement->id] = app(PpdbFileService::class)->rules($requirement, $requirement->is_required);
        }

        return $rules;
    }

    public function nextStep(): void
    {
        if ($this->currentStep >= 4) {
            return;
        }

        $this->validate($this->stepRules($this->currentStep));
        $this->resetValidation();
        $this->currentStep++;
    }

    public function previousStep(): void
    {
        if ($this->currentStep <= 1 || $this->currentStep >= 5) {
            return;
        }

        $this->resetValidation();
        $this->currentStep--;
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 4 || $step > $this->currentStep || $this->currentStep >= 5) {
            return;
        }

        $this->resetValidation();
        $this->currentStep = $step;
    }

    public function submit(): void
    {
        $key = 'ppdb-register|'.request()->ip().'|'.$this->periodId;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('candidateName', 'Terlalu banyak percobaan. Silakan coba lagi beberapa saat.');

            return;
        }
        RateLimiter::hit($key, 300);

        $this->validate();
        $pathway = $this->pathwayQuery()->whereKey($this->pathwayId)->firstOrFail();
        $period = $pathway->period;

        try {
            [$application, $accessCode] = app(PpdbApplicationService::class)->submitOnline(
                $period,
                $pathway,
                [
                    'name' => trim($this->candidateName),
                    'nik' => $this->candidateNik ?: null,
                    'nisn' => $this->candidateNisn ?: null,
                    'gender' => $this->candidateGender,
                    'birth_place' => trim($this->birthPlace),
                    'birth_date' => $this->birthDate,
                    'previous_school' => trim($this->previousSchool),
                    'address' => trim($this->address),
                    'village' => $this->village ?: null,
                    'district' => $this->district ?: null,
                    'regency' => $this->regency ?: null,
                    'province' => $this->province ?: null,
                    'postal_code' => $this->postalCode ?: null,
                ],
                [
                    'relationship' => $this->guardianRelationship,
                    'name' => trim($this->guardianName),
                    'nik' => $this->guardianNik ?: null,
                    'phone' => trim($this->guardianPhone),
                    'email' => $this->guardianEmail ?: null,
                    'occupation' => $this->guardianOccupation ?: null,
                    'address' => $this->guardianAddress ?: null,
                ],
                $this->contactEmail,
                $this->contactPhone,
            );
        } catch (\DomainException $exception) {
            $this->addError('candidateName', $exception->getMessage());
            $this->currentStep = 1;

            return;
        }

        foreach ($this->documents as $requirementId => $file) {
            if (! $file) {
                continue;
            }

            $requirement = $pathway->requirements()->whereKey($requirementId)->firstOrFail();
            $stored = app(PpdbFileService::class)->store($file, 'ppdb/'.$period->school_id.'/'.$application->id, $requirement);
            PpdbDocument::create([
                'ppdb_application_id' => $application->id,
                'ppdb_requirement_id' => $requirement->id,
                'file_path' => $stored['path'],
                'original_name' => $stored['original_name'],
                'mime_type' => $stored['mime_type'],
                'file_size' => $stored['file_size'],
                'status' => PpdbDocument::STATUS_PENDING,
            ]);
        }

        RateLimiter::clear($key);
        $this->applicationNumber = $application->application_number;
        $this->accessCode = $accessCode;
        $this->paymentStatus = $application->payment_status;
        $this->currentStep = 5;
        $this->resetForm();
    }

    public function uploadPaymentProof(): void
    {
        $this->validate([
            'paymentProof' => app(PpdbFileService::class)->rules(),
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($this->applicationNumber !== null && $this->accessCode !== null, 403);
        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $application = PpdbApplication::query()
            ->where('school_id', $schoolId)
            ->where('application_number', $this->applicationNumber)
            ->with('payments')
            ->firstOrFail();
        abort_unless(Hash::check($this->accessCode, $application->access_code_hash), 403, 'Kode akses tidak valid.');

        try {
            $payment = app(PpdbPaymentService::class)->submitProof($application, $this->paymentProof, $this->paymentNotes);
        } catch (\DomainException $exception) {
            $this->addError('paymentProof', $exception->getMessage());

            return;
        }
        $this->paymentStatus = $payment->status;
        $this->paymentProof = null;
        $this->paymentNotes = '';
    }

    public function downloadReceipt(): Response
    {
        abort_unless($this->applicationNumber !== null && $this->accessCode !== null, 404);

        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $application = PpdbApplication::query()
            ->where('school_id', $schoolId)
            ->where('application_number', $this->applicationNumber)
            ->firstOrFail();

        abort_unless(Hash::check($this->accessCode, $application->access_code_hash), 403, 'Kode akses tidak valid.');

        return app(PpdbReceiptService::class)->download($application, $this->accessCode);
    }

    public function resetForm(): void
    {
        $this->reset([
            'candidateName', 'candidateNik', 'candidateNisn', 'candidateGender', 'birthPlace', 'birthDate',
            'previousSchool', 'address', 'village', 'district', 'regency', 'province', 'postalCode',
            'guardianRelationship', 'guardianName', 'guardianNik', 'guardianPhone', 'guardianEmail',
            'guardianOccupation', 'guardianAddress', 'contactEmail', 'contactPhone', 'documents',
        ]);
        $this->guardianRelationship = 'ayah';
    }

    public function updatedPathwayId(): void
    {
        $this->documents = [];
        $this->resetValidation('documents');
    }

    public function render(): View
    {
        $period = PpdbPeriod::query()->with('academicYear')->findOrFail($this->periodId);
        $pathways = $period->pathways()->where('is_active', true)->with('requirements')->orderBy('sort_order')->get();

        return view('livewire.public.ppdb.register', [
            'period' => $period,
            'pathways' => $pathways,
            'selectedPathway' => $pathways->firstWhere('id', (int) $this->pathwayId),
            'requirements' => $this->requirements(),
        ]);
    }

    /** @return Collection<int, PpdbRequirement> */
    private function requirements(): Collection
    {
        $pathway = $this->pathwayQuery()->whereKey($this->pathwayId)->first();

        return $pathway?->requirements()->orderBy('sort_order')->get() ?? new Collection;
    }

    /** @return Builder<PpdbPathway> */
    private function pathwayQuery(): Builder
    {
        return PpdbPathway::query()->whereHas('period', function ($query): void {
            $query->whereKey($this->periodId)->where('status', PpdbPeriod::STATUS_OPEN);
        })->where('is_active', true);
    }

    /** @return array<string, array<int, mixed>|string> */
    private function stepRules(int $step): array
    {
        $rules = $this->rules();
        $fields = match ($step) {
            1 => [
                'pathwayId', 'candidateName', 'candidateNik', 'candidateNisn', 'candidateGender',
                'birthPlace', 'birthDate', 'previousSchool', 'address', 'village', 'district',
                'regency', 'province', 'postalCode',
            ],
            2 => [
                'guardianRelationship', 'guardianName', 'guardianNik', 'guardianPhone', 'guardianEmail',
                'guardianOccupation', 'guardianAddress', 'contactEmail', 'contactPhone',
            ],
            3 => array_keys(array_filter($rules, fn ($value, $key): bool => str_starts_with((string) $key, 'documents.'), ARRAY_FILTER_USE_BOTH)),
            default => array_keys($rules),
        };

        return array_intersect_key($rules, array_flip($fields));
    }
}
