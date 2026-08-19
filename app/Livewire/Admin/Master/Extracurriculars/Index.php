<?php

namespace App\Livewire\Admin\Master\Extracurriculars;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $search = '';

    // Form Properties
    public bool $showModal = false;

    public bool $isEdit = false;

    public int|string|null $extracurricularId = null;

    public string $name = '';

    public string $category = 'Wajib'; // Wajib, Pilihan

    public string|int|null $teacher_id = null;

    public ?string $description = null;

    /** @return array<string, array<int, mixed>|string> */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|in:Wajib,Pilihan',
            'teacher_id' => 'required|exists:teachers,id',
            'description' => 'nullable|string',
        ];
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->isEdit = false;
        $this->extracurricularId = null;
        $this->name = '';
        $this->category = 'Wajib';
        $this->teacher_id = '';
        $this->description = '';
        $this->showModal = true;
    }

    public function editMode(int|string $id): void
    {
        $this->resetValidation();
        $extracurricular = Extracurricular::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();

        $this->extracurricularId = $extracurricular->id;
        $this->name = $extracurricular->name;
        $this->category = $extracurricular->category ?? 'Wajib';
        $this->teacher_id = $extracurricular->teacher_id;
        $this->description = $extracurricular->description;

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate();

        $schoolId = app(CurrentSchool::class)->id();
        $activeYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first();

        if (! $activeYear) {
            $this->dispatch('notify', title: 'Gagal', message: 'Tidak ada Tahun Ajaran aktif.', type: 'error');

            return;
        }

        if ($this->isEdit) {
            $extracurricular = Extracurricular::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->extracurricularId)
                ->firstOrFail();
            $extracurricular->update([
                'name' => $this->name,
                'category' => $this->category,
                'teacher_id' => $this->teacher_id,
                'description' => $this->description,
            ]);

            $this->dispatch('notify', title: 'Berhasil', message: 'Data ekstrakurikuler berhasil diperbarui.', type: 'success');
        } else {
            Extracurricular::create([
                'school_id' => $schoolId,
                'academic_year_id' => $activeYear->id,
                'name' => $this->name,
                'category' => $this->category,
                'teacher_id' => $this->teacher_id,
                'description' => $this->description,
            ]);

            $this->dispatch('notify', title: 'Berhasil', message: 'Data ekstrakurikuler berhasil ditambahkan.', type: 'success');
        }

        $this->closeModal();
    }

    public function delete(int|string $id): void
    {
        $extracurricular = Extracurricular::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->firstOrFail();

        if ($extracurricular->members()->count() > 0) {
            $this->dispatch('notify', title: 'Gagal', message: 'Tidak dapat menghapus ekskul yang memiliki anggota terdaftar.', type: 'error');

            return;
        }

        $extracurricular->delete();
        $this->dispatch('notify', title: 'Berhasil', message: 'Ekskul dihapus.', type: 'success');
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $activeYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first();

        $extracurriculars = collect();
        if ($activeYear) {
            $extracurriculars = Extracurricular::with(['teacher'])
                ->withCount('members')
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $activeYear->id)
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->latest()
                ->get();
        }

        $teachers = Teacher::where('school_id', $schoolId)->orderBy('name')->get();

        return view('livewire.admin.master.extracurriculars.index', [
            'extracurriculars' => $extracurriculars,
            'teachers' => $teachers,
            'activeYear' => $activeYear,
        ]);
    }
}
