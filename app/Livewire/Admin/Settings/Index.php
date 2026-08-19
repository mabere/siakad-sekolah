<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Classroom;
use App\Models\School;
use App\Models\SystemSetting;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public School $school;

    public string $name = '';

    public string $npsn = '';

    public string $level = 'SMP'; // SMP, SMA, TERPADU

    public string $status = 'NEGERI'; // NEGERI, SWASTA

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public string $vision = '';

    public string $mission = '';

    public ?TemporaryUploadedFile $logo = null; // For new upload

    public ?string $existingLogo = null; // To display current logo

    // Dynamic settings based on level
    public bool $useMajors = false; // Penjurusan (IPA/IPS) untuk SMA

    public string $curriculumType = 'MERDEKA';

    // Headmaster Information
    public string $headmasterName = '';

    public string $headmasterNip = '';

    // Additional Contact/Address info
    public string $city = '';

    // Lock status
    public bool $isLevelLocked = false;

    // Academic Calendar / Rules
    public bool $isPromotionUnlocked = false;

    public function mount(): void
    {
        $this->school = app(CurrentSchool::class)->get();

        // Lock the level if there are already classrooms or students to prevent data corruption
        $this->isLevelLocked = Classroom::query()
            ->where('school_id', $this->school->id)
            ->exists();

        $this->name = (string) $this->school->name;
        $this->npsn = (string) ($this->school->npsn ?? '');
        $this->level = (string) $this->school->level;
        $this->status = (string) $this->school->status;
        $this->address = (string) ($this->school->address ?? '');
        $this->phone = (string) ($this->school->phone ?? '');
        $this->email = (string) ($this->school->email ?? '');
        $this->website = (string) ($this->school->website ?? '');
        $this->vision = (string) ($this->school->vision ?? '');
        $this->mission = (string) ($this->school->mission ?? '');
        $this->existingLogo = $this->school->logo;

        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $settings = SystemSetting::query()
            ->where('school_id', $this->school->id)
            ->whereIn('key', [
                'use_majors',
                'curriculum_type',
                'headmaster_name',
                'headmaster_nip',
                'city',
                'is_promotion_unlocked',
            ])
            ->get()
            ->keyBy('key');

        $useMajorsSetting = $settings->get('use_majors');
        $this->useMajors = $useMajorsSetting
            ? (filter_var($useMajorsSetting->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false)
            : false;

        $curriculumSetting = $settings->get('curriculum_type');
        $this->curriculumType = $curriculumSetting && is_string($curriculumSetting->value)
            ? $curriculumSetting->value
            : 'MERDEKA';

        $headmasterNameSetting = $settings->get('headmaster_name');
        $this->headmasterName = $headmasterNameSetting && is_string($headmasterNameSetting->value)
            ? $headmasterNameSetting->value
            : '';

        $headmasterNipSetting = $settings->get('headmaster_nip');
        $this->headmasterNip = $headmasterNipSetting && is_string($headmasterNipSetting->value)
            ? $headmasterNipSetting->value
            : '';

        $citySetting = $settings->get('city');
        $this->city = $citySetting && is_string($citySetting->value)
            ? $citySetting->value
            : '';

        $promoLockSetting = $settings->get('is_promotion_unlocked');
        $this->isPromotionUnlocked = $promoLockSetting
            ? (filter_var($promoLockSetting->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false)
            : false;
    }

    public function updatedLevel(string $value): void
    {
        if ($value === 'SMP') {
            $this->useMajors = false;
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'status' => 'required|in:NEGERI,SWASTA',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'vision' => 'nullable|string|max:5000',
            'mission' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|max:2048', // max 2MB
            'useMajors' => 'boolean',
            'curriculumType' => 'required|in:MERDEKA,K13',
            'headmasterName' => 'nullable|string|max:255',
            'headmasterNip' => 'nullable|string|max:50',
            'isPromotionUnlocked' => 'boolean',
        ];

        if (! $this->isLevelLocked) {
            $rules['level'] = 'required|in:SMP,SMA,TERPADU';
        }

        $this->validate($rules);

        $logoPath = $this->school->logo;

        if ($this->logo) {
            // Upload to storage/app/public/logos
            $storedLogoPath = $this->logo->store('logos', 'public');
            if ($storedLogoPath === false) {
                $this->addError('logo', 'Logo gagal disimpan.');

                return;
            }

            $logoPath = $storedLogoPath;
            $this->existingLogo = $logoPath;
        }

        $updateData = [
            'name' => $this->name,
            'npsn' => $this->npsn,
            'status' => $this->status,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'vision' => trim($this->vision) !== '' ? trim($this->vision) : null,
            'mission' => trim($this->mission) !== '' ? trim($this->mission) : null,
            'logo' => $logoPath,
        ];

        if (! $this->isLevelLocked) {
            $updateData['level'] = $this->level;
        }

        $oldLogo = $this->school->logo;

        DB::transaction(function () use ($updateData): void {
            $this->school->update($updateData);

            $settings = [
                'use_majors' => [$this->useMajors ? 'true' : 'false', 'boolean'],
                'curriculum_type' => [$this->curriculumType, 'string'],
                'headmaster_name' => [$this->headmasterName, 'string'],
                'headmaster_nip' => [$this->headmasterNip, 'string'],
                'city' => [$this->city, 'string'],
                'is_promotion_unlocked' => [$this->isPromotionUnlocked ? 'true' : 'false', 'boolean'],
            ];

            foreach ($settings as $key => [$value, $type]) {
                SystemSetting::updateOrCreate(
                    ['school_id' => $this->school->id, 'key' => $key],
                    ['value' => $value, 'type' => $type],
                );
            }
        });

        if ($this->logo && $oldLogo && $oldLogo !== $logoPath) {
            Storage::disk('public')->delete($oldLogo);
        }

        // Reset file input after successful upload
        $this->logo = null;

        session()->flash('message', 'Pengaturan berhasil disimpan.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.index');
    }
}
