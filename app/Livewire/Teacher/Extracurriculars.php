<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\Teacher;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.teacher')]
class Extracurriculars extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'ekskul_grading'; // 'ekskul_grading', 'achievements', 'master_ekskul'

    #[Locked]
    public ?int $activeYearId = null;

    #[Locked]
    public ?int $teacherId = null;

    // --- Tab 1: Ekskul Grading State ---
    public string $selectedEkskulId = '';

    /** @var array<int|string, mixed> */
    public array $memberData = [];

    public bool $showAddMemberModal = false;

    public string $addMemberClassroomId = '';

    public string $addMemberStudentId = '';

    // --- Tab 2: Achievements State ---
    public string $achievementCategoryFilter = '';

    public string $achievementLevelFilter = '';

    public bool $showAchievementModal = false;

    public ?int $editingAchievementId = null;

    public string $achievementStudentId = '';

    public string $achievementEventName = '';

    public string $achievementCategory = 'Akademik';

    public string $achievementLevel = 'Kabupaten/Kota';

    public string $achievementRank = 'Juara 1';

    public string $achievementOrganizer = '';

    public string $achievementEventDate = '';

    public string $achievementNotes = '';

    // --- Tab 3: Master Ekskul State ---
    public bool $showEkskulModal = false;

    public ?int $editingEkskulId = null;

    public string $ekskulName = '';

    public string $ekskulCategory = 'Pilihan';

    public string $ekskulTeacherId = '';

    public string $ekskulDescription = '';

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        $this->activeYearId = $activeYear?->id;

        $teacher = Teacher::where('school_id', $schoolId)
            ->where('user_id', auth()->id())
            ->first();

        $this->teacherId = $teacher?->id;

        // abort_if(! $this->teacherId, 403);

        if ($this->activeYearId) {
            $firstEkskul = Extracurricular::where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->first();

            if ($firstEkskul) {
                $this->selectedEkskulId = (string) $firstEkskul->id;
                $this->loadEkskulMembers();
            }
        }

        $this->achievementEventDate = now()->toDateString();
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['ekskul_grading', 'achievements', 'master_ekskul'])) {
            $this->activeTab = $tab;
        }
    }

    // --- Tab 1: Ekskul Grading Methods ---
    public function updatedSelectedEkskulId(): void
    {
        $this->loadEkskulMembers();
    }

    public function loadEkskulMembers(): void
    {
        $this->memberData = [];

        if (! $this->selectedEkskulId || ! $this->activeYearId) {
            return;
        }

        $selectedEkskul = $this->accessibleEkskulQuery()->whereKey($this->selectedEkskulId)->first();
        if (! $selectedEkskul) {
            return;
        }

        $members = ExtracurricularMember::with('student')
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('extracurricular_id', $selectedEkskul->id)
            ->get();

        foreach ($members as $mem) {
            $this->memberData[$mem->id] = [
                'student_id' => $mem->student_id,
                'grade' => $mem->grade,
                'description' => $mem->description ?? '',
            ];
        }
    }

    public function openAddMemberModal(): void
    {
        $this->resetValidation();
        $this->addMemberStudentId = '';

        $schoolId = app(CurrentSchool::class)->id();
        $classrooms = Classroom::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        if (! $this->addMemberClassroomId && ! $classrooms->isEmpty()) {
            $this->addMemberClassroomId = (string) $classrooms->first()->id;
        }

        $this->showAddMemberModal = true;
    }

    public function closeAddMemberModal(): void
    {
        $this->showAddMemberModal = false;
    }

    public function addMemberToEkskul(): void
    {
        if (! $this->selectedEkskulId || ! $this->addMemberStudentId) {
            session()->flash('ekskul_error', 'Pilih siswa yang akan ditambahkan.');

            return;
        }

        $schoolId = app(CurrentSchool::class)->id();

        $ekskul = $this->accessibleEkskulQuery()->whereKey($this->selectedEkskulId)->first();
        $student = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $this->addMemberClassroomId)
            ->where('status', 'Aktif')
            ->whereKey($this->addMemberStudentId)
            ->first();
        if (! $ekskul || ! $student) {
            abort(403);
        }

        ExtracurricularMember::updateOrCreate(
            [
                'extracurricular_id' => $ekskul->id,
                'student_id' => $student->id,
            ],
            [
                'school_id' => $schoolId,
                'academic_year_id' => $this->activeYearId,
                'grade' => 'A',
                'description' => 'Aktif mengikuti kegiatan ekstrakurikuler.',
            ]
        );

        session()->flash('ekskul_success', 'Siswa berhasil ditambahkan ke anggota ekstrakurikuler.');
        $this->closeAddMemberModal();
        $this->loadEkskulMembers();
    }

    public function removeMemberFromEkskul(int $memberId): void
    {
        $member = ExtracurricularMember::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->whereKey($memberId)
            ->whereHas('extracurricular', fn ($query) => $query->where('school_id', app(CurrentSchool::class)->id())->where('academic_year_id', $this->activeYearId))
            ->first();
        if ($member) {
            $member->delete();
            session()->flash('ekskul_success', 'Anggota ekstrakurikuler dihapus.');
            $this->loadEkskulMembers();
        }
    }

    public function saveEkskulGrades(): void
    {
        if (! $this->selectedEkskulId) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->memberData as $memberId => $data) {
                $grade = in_array($data['grade'] ?? '', ['A', 'B', 'C', 'D']) ? $data['grade'] : 'A';
                $desc = ! empty($data['description']) ? trim($data['description']) : null;

                ExtracurricularMember::query()
                    ->where('school_id', app(CurrentSchool::class)->id())
                    ->where('academic_year_id', $this->activeYearId)
                    ->whereKey($memberId)
                    ->where('extracurricular_id', $this->selectedEkskulId)
                    ->update([
                        'grade' => $grade,
                        'description' => $desc,
                    ]);
            }
        });

        session()->flash('ekskul_success', 'Nilai & deskripsi ekstrakurikuler berhasil disimpan.');
    }

    // --- Tab 2: Achievement Methods ---
    public function openAchievementModal(?int $achievementId = null): void
    {
        $this->resetValidation();

        $schoolId = app(CurrentSchool::class)->id();
        $students = Student::where('school_id', $schoolId)->where('status', 'Aktif')->orderBy('name')->get();

        if ($achievementId) {
            $ach = StudentAchievement::query()
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('teacher_id', $this->teacherId)
                ->whereKey($achievementId)
                ->first();
            if (! $ach) {
                return;
            }

            $this->editingAchievementId = $ach->id;
            $this->achievementStudentId = (string) $ach->student_id;
            $this->achievementEventName = $ach->event_name;
            $this->achievementCategory = $ach->category;
            $this->achievementLevel = $ach->level;
            $this->achievementRank = $ach->rank;
            $this->achievementOrganizer = $ach->organizer ?? '';
            $this->achievementEventDate = $ach->event_date ?? now()->toDateString();
            $this->achievementNotes = $ach->notes ?? '';
        } else {
            $this->editingAchievementId = null;
            $this->achievementStudentId = ! $students->isEmpty() ? (string) $students->first()->id : '';
            $this->achievementEventName = '';
            $this->achievementCategory = 'Akademik';
            $this->achievementLevel = 'Kabupaten/Kota';
            $this->achievementRank = 'Juara 1';
            $this->achievementOrganizer = '';
            $this->achievementEventDate = now()->toDateString();
            $this->achievementNotes = '';
        }

        $this->showAchievementModal = true;
    }

    public function closeAchievementModal(): void
    {
        $this->showAchievementModal = false;
        $this->editingAchievementId = null;
    }

    public function saveAchievement(): void
    {
        $validated = Validator::make([
            'student_id' => $this->achievementStudentId,
            'event_name' => $this->achievementEventName,
            'category' => $this->achievementCategory,
            'level' => $this->achievementLevel,
            'rank' => $this->achievementRank,
            'organizer' => $this->achievementOrganizer,
            'event_date' => $this->achievementEventDate,
            'notes' => $this->achievementNotes,
        ], [
            'student_id' => ['required', 'integer'],
            'event_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:Akademik,Non-Akademik'],
            'level' => ['required', 'string', 'in:Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional'],
            'rank' => ['required', 'string', 'max:100'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        $student = Student::query()
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif')
            ->whereKey($validated['student_id'])
            ->first();
        if (! $student) {
            abort(403);
        }

        $ach = $this->editingAchievementId
            ? $this->accessibleAchievementQuery()->whereKey($this->editingAchievementId)->first()
            : new StudentAchievement;
        if ($this->editingAchievementId && ! $ach) {
            abort(403);
        }
        $ach->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'student_id' => $student->id,
            'teacher_id' => $this->teacherId,
            'event_name' => trim($validated['event_name']),
            'category' => $validated['category'],
            'level' => $validated['level'],
            'rank' => trim($validated['rank']),
            'organizer' => ! empty($validated['organizer']) ? trim($validated['organizer']) : null,
            'event_date' => $validated['event_date'] ?: null,
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
        ]);
        $ach->save();

        session()->flash('achievement_success', 'Pencatatan Prestasi Siswa berhasil disimpan.');
        $this->closeAchievementModal();
    }

    public function deleteAchievement(int $achievementId): void
    {
        $ach = $this->accessibleAchievementQuery()->whereKey($achievementId)->first();
        if ($ach) {
            $ach->delete();
            session()->flash('achievement_success', 'Data prestasi siswa berhasil dihapus.');
        }
    }

    // --- Tab 3: Master Ekskul Methods ---
    public function openEkskulModal(?int $ekskulId = null): void
    {
        $this->resetValidation();

        $schoolId = app(CurrentSchool::class)->id();
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('name')->get();

        if ($ekskulId) {
            $eks = $this->accessibleEkskulQuery()->whereKey($ekskulId)->first();
            if (! $eks) {
                return;
            }

            $this->editingEkskulId = $eks->id;
            $this->ekskulName = $eks->name;
            $this->ekskulCategory = $eks->category;
            $this->ekskulTeacherId = (string) ($eks->teacher_id ?? '');
            $this->ekskulDescription = $eks->description ?? '';
        } else {
            $this->editingEkskulId = null;
            $this->ekskulName = '';
            $this->ekskulCategory = 'Pilihan';
            $this->ekskulTeacherId = (string) $this->teacherId;
            $this->ekskulDescription = '';
        }

        $this->showEkskulModal = true;
    }

    public function closeEkskulModal(): void
    {
        $this->showEkskulModal = false;
        $this->editingEkskulId = null;
    }

    public function saveEkskul(): void
    {
        $validated = Validator::make([
            'name' => $this->ekskulName,
            'category' => $this->ekskulCategory,
            'teacher_id' => $this->ekskulTeacherId,
            'description' => $this->ekskulDescription,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:Wajib,Pilihan'],
            'teacher_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $schoolId = app(CurrentSchool::class)->id();

        if ($validated['teacher_id']) {
            $teacherExists = Teacher::query()
                ->where('school_id', $schoolId)
                ->whereKey($validated['teacher_id'])
                ->exists();
            if (! $teacherExists) {
                abort(403);
            }
        }

        $ekskul = $this->editingEkskulId
            ? $this->accessibleEkskulQuery()->whereKey($this->editingEkskulId)->first()
            : new Extracurricular;
        if ($this->editingEkskulId && ! $ekskul) {
            abort(403);
        }
        $ekskul->fill([
            'school_id' => $schoolId,
            'academic_year_id' => $this->activeYearId,
            'name' => trim($validated['name']),
            'category' => $validated['category'],
            'teacher_id' => $validated['teacher_id'] ?: null,
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
        ]);
        $ekskul->save();

        session()->flash('ekskul_success', 'Master kegiatan ekstrakurikuler berhasil disimpan.');
        $this->closeEkskulModal();

        $this->selectedEkskulId = (string) $ekskul->id;
        $this->loadEkskulMembers();
    }

    public function deleteEkskul(int $ekskulId): void
    {
        $eks = $this->accessibleEkskulQuery()->whereKey($ekskulId)->first();
        if ($eks) {
            $eks->delete();
            session()->flash('ekskul_success', 'Ekstrakurikuler berhasil dihapus.');

            if ($this->selectedEkskulId == (string) $ekskulId) {
                $this->selectedEkskulId = '';
                $this->memberData = [];
            }
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $activeYear = $this->activeYearId
            ? AcademicYear::query()
                ->where('school_id', $schoolId)
                ->whereKey($this->activeYearId)
                ->first()
            : null;

        $extracurriculars = Extracurricular::with('teacher')
            ->withCount('members')
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        $classrooms = Classroom::where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        // Selected Ekskul Members for Tab 1
        $selectedEkskul = $this->selectedEkskulId
            ? $this->accessibleEkskulQuery()->with('teacher')->whereKey($this->selectedEkskulId)->first()
            : null;

        $members = $selectedEkskul
            ? ExtracurricularMember::with('student')
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $this->activeYearId)
                ->where('extracurricular_id', $selectedEkskul->id)
                ->get()
            : collect();

        // Filtered Students for Modal Add Member
        $availableStudents = $this->addMemberClassroomId
            ? Student::where('school_id', $schoolId)
                ->where('classroom_id', $this->addMemberClassroomId)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get()
            : collect();

        // Achievements for Tab 2
        $achievementsQuery = StudentAchievement::with(['student', 'teacher'])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $this->activeYearId);

        if ($this->achievementCategoryFilter) {
            $achievementsQuery->where('category', $this->achievementCategoryFilter);
        }

        if ($this->achievementLevelFilter) {
            $achievementsQuery->where('level', $this->achievementLevelFilter);
        }

        $achievements = $achievementsQuery
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(7);

        return view('livewire.teacher.extracurriculars', [
            'activeYear' => $activeYear,
            'extracurriculars' => $extracurriculars,
            'selectedEkskul' => $selectedEkskul,
            'members' => $members,
            'teachers' => $teachers,
            'classrooms' => $classrooms,
            'availableStudents' => $availableStudents,
            'achievements' => $achievements,
        ]);
    }

    /** @return Builder<Extracurricular> */
    private function accessibleEkskulQuery(): Builder
    {
        return Extracurricular::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId);
    }

    /** @return Builder<StudentAchievement> */
    private function accessibleAchievementQuery(): Builder
    {
        return StudentAchievement::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('academic_year_id', $this->activeYearId)
            ->where('teacher_id', $this->teacherId);
    }
}
