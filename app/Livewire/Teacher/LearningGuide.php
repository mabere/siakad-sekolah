<?php

namespace App\Livewire\Teacher;

use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class LearningGuide extends Component
{
    public string $activeRoleTab = 'guru'; // 'guru' | 'wakasek' | 'kepsek' | 'admin'
    public string $searchQuery = '';
    public string $activeSection = 'all';

    public function mount(): void
    {
        $allowed = array_keys($this->getAllowedTabs());
        $activeRole = (string) session('active_role', auth()->user()?->roles->first()?->name ?? 'Guru');

        $preferred = match ($activeRole) {
            'Kepala Sekolah' => 'kepsek',
            'Wakasek Kurikulum', 'Wakasek Kesiswaan', 'Wakasek Sarana', 'Wakasek Humas' => 'wakasek',
            'Super Admin', 'Admin Sekolah' => 'admin',
            default => 'guru',
        };

        $this->activeRoleTab = in_array($preferred, $allowed) ? $preferred : ($allowed[0] ?? 'guru');
    }

    /**
     * @return array<string, string>
     */
    public function getAllowedTabs(): array
    {
        $activeRole = (string) session('active_role', auth()->user()?->roles->first()?->name ?? 'Guru');

        if (in_array($activeRole, ['Super Admin', 'Admin Sekolah'])) {
            return [
                'guru' => 'Guru & Wali Kelas',
                'wakasek' => 'Wakasek Kurikulum',
                'kepsek' => 'Kepala Sekolah',
                'admin' => 'Administrator Sekolah',
            ];
        }

        if ($activeRole === 'Kepala Sekolah') {
            return [
                'kepsek' => 'Kepala Sekolah',
            ];
        }

        if (str_starts_with($activeRole, 'Wakasek')) {
            return [
                'wakasek' => 'Wakasek Kurikulum',
            ];
        }

        return [
            'guru' => 'Guru & Wali Kelas',
        ];
    }

    public function setRoleTab(string $tab): void
    {
        if (array_key_exists($tab, $this->getAllowedTabs())) {
            $this->activeRoleTab = $tab;
        }
    }

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function render(): View
    {
        $school = app(CurrentSchool::class)->get();
        $allowedTabs = $this->getAllowedTabs();

        return view('livewire.teacher.learning-guide', [
            'schoolName' => $school->name ?? 'SIAKAD Sekolah',
            'schoolLevel' => $school->level ?? 'SMA',
            'allowedTabs' => $allowedTabs,
        ]);
    }
}
