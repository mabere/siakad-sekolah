<?php

namespace App\Livewire\Public\Ppdb;

use App\Models\PpdbApplication;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ReRegistration extends Component
{
    public string $applicationNumber = '';

    public string $accessCode = '';

    public ?PpdbApplication $application = null;

    public bool $confirmed = false;

    public function check(): void
    {
        $key = 'ppdb-reregistration|'.request()->ip();
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
            ->with(['period', 'pathway', 'candidate', 'reRegistration'])
            ->first();

        if (! $application || ! Hash::check($this->accessCode, $application->access_code_hash) || $application->selection_status !== PpdbApplication::SELECTION_ACCEPTED || ! in_array($application->reregistration_status, ['pending', 'confirmed'], true)) {
            $this->application = null;
            $this->addError('applicationNumber', 'Data pendaftaran tidak ditemukan atau belum dapat melakukan daftar ulang.');

            return;
        }

        if (! $application->period->isReregistrationOpen()) {
            $this->application = null;
            $this->addError('applicationNumber', 'Masa daftar ulang belum dibuka atau sudah berakhir.');

            return;
        }

        RateLimiter::clear($key);
        $this->application = $application;
    }

    public function confirm(): void
    {
        abort_unless($this->application !== null, 403);
        $schoolId = School::query()->where('is_active', true)->orderBy('id')->value('id');
        $application = PpdbApplication::query()->where('school_id', $schoolId)->with('period')->whereKey($this->application->id)->firstOrFail();

        abort_unless($application->selection_status === PpdbApplication::SELECTION_ACCEPTED && $application->reregistration_status === 'pending' && $application->period->isReregistrationOpen(), 403);
        $application->reRegistration()->updateOrCreate([], [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        $application->update(['reregistration_status' => 'confirmed']);
        $this->confirmed = true;
        $this->application->refresh();
    }

    public function resetSearch(): void
    {
        $this->reset(['applicationNumber', 'accessCode', 'application', 'confirmed']);
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.public.ppdb.re-registration');
    }
}
