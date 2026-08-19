<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class SetupWizard extends Component
{
    public int $step = 1;

    // School Profile
    public string $schoolName = '';

    public string $npsn = '';

    public string $level = 'SMP';

    public string $address = '';

    // Academic Year
    public string $academicYearName = '2026/2027';

    public string $semester = 'Ganjil';

    public function mount(): void
    {
        $user = $this->authorizeSetupUser();
        $school = $this->setupSchoolQuery($user)->firstOrFail();

        if ($school->is_setup_completed) {
            $this->redirectRoute('admin.dashboard');

            return;
        }

        $this->schoolName = $school->name;
        $this->npsn = $school->npsn ?? '';
        $this->level = $school->level ?? 'SMP';
        $this->address = $school->address ?? '';
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'schoolName' => 'required|string|max:255',
                'npsn' => 'nullable|string|max:50',
                'level' => 'required|in:SD,SMP,SMA,SMK,TERPADU',
                'address' => 'nullable|string',
            ]);
        } elseif ($this->step === 2) {
            $this->validate([
                'academicYearName' => 'required|string|max:50',
                'semester' => 'required|in:Ganjil,Genap',
            ]);
        }

        $this->step = min(3, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function completeSetup(): void
    {
        $user = $this->authorizeSetupUser();

        if ($this->step < 3) {
            $this->addError('step', 'Selesaikan seluruh langkah setup sebelum menyimpan konfigurasi.');

            return;
        }

        $validated = $this->validate([
            'schoolName' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'level' => 'required|in:SD,SMP,SMA,SMK,TERPADU',
            'address' => 'nullable|string|max:5000',
            'academicYearName' => 'required|string|max:50',
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $school = $this->setupSchoolQuery($user)
                ->lockForUpdate()
                ->firstOrFail();

            // A second request arriving after the first successful setup is
            // harmless and cannot rewrite the completed configuration.
            if ($school->is_setup_completed) {
                return;
            }

            $school->fill([
                'name' => $validated['schoolName'],
                'npsn' => $validated['npsn'],
                'level' => $validated['level'],
                'address' => $validated['address'],
                'is_setup_completed' => true,
            ]);
            $school->save();

            $activeYear = AcademicYear::query()
                ->where('school_id', $school->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($activeYear) {
                return;
            }

            $academicYear = AcademicYear::query()
                ->where('school_id', $school->id)
                ->where('name', $validated['academicYearName'])
                ->where('semester', $validated['semester'])
                ->lockForUpdate()
                ->first();

            if ($academicYear) {
                $academicYear->update(['is_active' => true]);

                return;
            }

            AcademicYear::create([
                'school_id' => $school->id,
                'name' => $validated['academicYearName'],
                'semester' => $validated['semester'],
                'is_active' => true,
            ]);
        });

        $this->redirectRoute('admin.dashboard');
    }

    private function authorizeSetupUser(): User
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof User && $user->is_active && $user->hasRole('Super Admin'),
            403,
            'Hanya Super Admin aktif yang dapat menjalankan setup sekolah.',
        );

        return $user;
    }

    /**
     * @return Builder<School>
     */
    private function setupSchoolQuery(User $user): Builder
    {
        if (! $user->school_id) {
            abort(403, 'Super Admin belum terhubung ke sekolah.');
        }

        return School::query()
            ->whereKey($user->school_id)
            ->where('is_active', true);
    }

    public function render(): View
    {
        return view('livewire.setup-wizard');
    }
}
