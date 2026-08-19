<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\PpdbApplication;
use App\Models\School;
use App\Services\PpdbFileService;
use App\Services\PpdbPaymentService;
use App\Services\PpdbReceiptDownloadService;
use App\Services\PpdbStudentActivationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.guest')]
class Status extends Component
{
    use WithFileUploads;

    public string $applicationNumber = '';

    public string $accessCode = '';

    public ?PpdbApplication $application = null;

    public ?string $receiptUrl = null;

    public ?string $studentActivationUrl = null;

    public ?string $studentActivationUsername = null;

    public ?string $studentActivationExpiresAt = null;

    public bool $studentAccountActivated = false;

    public ?TemporaryUploadedFile $paymentProof = null;

    public string $paymentNotes = '';

    public function check(): void
    {
        $key = 'ppdb-status|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('applicationNumber', 'Terlalu banyak percobaan. Silakan coba lagi beberapa saat.');

            return;
        }
        RateLimiter::hit($key, 300);

        $this->validate([
            'applicationNumber' => ['required', 'string', 'max:50'],
            'accessCode' => ['required', 'digits:6'],
        ]);

        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $application = PpdbApplication::query()
            ->where('school_id', $schoolId)
            ->where('application_number', strtoupper(trim($this->applicationNumber)))
            ->with(['period', 'pathway', 'candidate', 'payments', 'reRegistration', 'convertedStudent.classroom'])
            ->first();

        if (! $application || ! Hash::check($this->accessCode, $application->access_code_hash)) {
            $this->application = null;
            $this->studentActivationUrl = null;
            $this->studentActivationUsername = null;
            $this->studentActivationExpiresAt = null;
            $this->studentAccountActivated = false;
            $this->addError('applicationNumber', 'Nomor pendaftaran atau kode akses tidak sesuai.');

            return;
        }

        RateLimiter::clear($key);
        $this->application = $application;
        $this->receiptUrl = app(PpdbReceiptDownloadService::class)->createUrl($application, $this->accessCode);
        $activation = app(PpdbStudentActivationService::class)->statusFor($application);
        $this->studentActivationUrl = $activation['activation_url'];
        $this->studentActivationUsername = $activation['username'];
        $this->studentActivationExpiresAt = $activation['expires_at'];
        $this->studentAccountActivated = $activation['activated'];
    }

    public function uploadPaymentProof(): void
    {
        $this->validate([
            'paymentProof' => app(PpdbFileService::class)->rules(),
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($this->application !== null, 403);
        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $application = PpdbApplication::query()
            ->where('school_id', $schoolId)
            ->whereKey($this->application->id)
            ->with('payments')
            ->firstOrFail();
        abort_unless(Hash::check($this->accessCode, $application->access_code_hash), 403, 'Kode akses tidak valid.');

        try {
            app(PpdbPaymentService::class)->submitProof($application, $this->paymentProof, $this->paymentNotes);
        } catch (\DomainException $exception) {
            $this->addError('paymentProof', $exception->getMessage());

            return;
        }
        $this->paymentProof = null;
        $this->paymentNotes = '';
        $this->application = $application->fresh(['period', 'pathway', 'candidate', 'payments', 'reRegistration']);
    }

    public function resetSearch(): void
    {
        $this->reset([
            'applicationNumber', 'accessCode', 'application', 'receiptUrl', 'studentActivationUrl',
            'studentActivationUsername', 'studentActivationExpiresAt', 'studentAccountActivated',
            'paymentProof', 'paymentNotes',
        ]);
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.public.ppdb.status');
    }
}
