<?php

namespace App\Livewire\Admin\Academic\Promotion;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string|int $sourceClassroomId = '';

    public string|int $targetClassroomId = '';

    public string $actionType = 'promote';

    /** @var array<int, int|string> */
    public array $selectedStudents = [];

    public bool $selectAll = false;

    public bool $isPromotionUnlocked = false;

    public function mount(): void
    {
        $this->isPromotionUnlocked = $this->promotionIsUnlocked();
        $this->actionType = $this->isPromotionUnlocked ? 'promote' : 'transfer';
    }

    public function updatedSourceClassroomId(): void
    {
        $this->reset(['selectedStudents', 'selectAll', 'targetClassroomId']);

        if (! $this->sourceClassroomId) {
            $this->actionType = $this->promotionIsUnlocked() ? 'promote' : 'transfer';

            return;
        }

        $sourceClass = $this->schoolClassroomsQuery()
            ->whereKey($this->sourceClassroomId)
            ->firstOrFail();
        $isFinalGrade = in_array((int) $sourceClass->grade_level, [6, 9, 12], true);
        $this->isPromotionUnlocked = $this->promotionIsUnlocked();

        if (! $this->isPromotionUnlocked) {
            $this->actionType = 'transfer';
        } elseif ($isFinalGrade && $this->actionType === 'promote') {
            $this->actionType = 'graduate';
        } elseif (! $isFinalGrade && $this->actionType === 'graduate') {
            $this->actionType = 'promote';
        }
    }

    public function updatedActionType(): void
    {
        $this->targetClassroomId = '';
        $this->selectAll = false;
        $this->selectedStudents = [];
    }

    public function updatedSelectAll(bool $value): void
    {
        if (! $value || ! $this->sourceClassroomId) {
            $this->selectedStudents = [];

            return;
        }

        $source = $this->schoolClassroomsQuery()
            ->whereKey($this->sourceClassroomId)
            ->firstOrFail();
        $this->selectedStudents = Student::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('classroom_id', $source->id)
            ->where('status', 'Aktif')
            ->limit(200)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();
    }

    public function processPromotion(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $unlocked = $this->promotionIsUnlocked();
        $this->isPromotionUnlocked = $unlocked;

        $validated = Validator::make([
            'sourceClassroomId' => $this->sourceClassroomId,
            'targetClassroomId' => $this->targetClassroomId,
            'actionType' => $this->actionType,
            'selectedStudents' => $this->selectedStudents,
        ], [
            'sourceClassroomId' => [
                'required',
                Rule::exists('classrooms', 'id')->where('school_id', $schoolId),
            ],
            'targetClassroomId' => [
                Rule::requiredIf(in_array($this->actionType, ['promote', 'transfer', 'stay'], true)),
                'nullable',
                Rule::exists('classrooms', 'id')->where('school_id', $schoolId),
            ],
            'actionType' => ['required', Rule::in(['promote', 'transfer', 'stay', 'graduate'])],
            'selectedStudents' => ['required', 'array', 'min:1', 'max:200'],
            'selectedStudents.*' => ['required', 'integer', 'distinct'],
        ])->validate();

        if (! $unlocked && $validated['actionType'] !== 'transfer') {
            throw ValidationException::withMessages([
                'actionType' => 'Saat ini hanya perpindahan kelas yang diizinkan.',
            ]);
        }

        try {
            DB::transaction(function () use ($schoolId, $validated): void {
                $source = $this->schoolClassroomsQuery()
                    ->lockForUpdate()
                    ->whereKey($validated['sourceClassroomId'])
                    ->firstOrFail();
                $target = null;

                if ($validated['actionType'] !== 'graduate') {
                    $target = $this->schoolClassroomsQuery()
                        ->lockForUpdate()
                        ->whereKey($validated['targetClassroomId'])
                        ->firstOrFail();
                    $this->validateTransition($source, $target, $validated['actionType']);
                } elseif (! in_array((int) $source->grade_level, [6, 9, 12], true)) {
                    throw ValidationException::withMessages([
                        'actionType' => 'Kelulusan hanya dapat diproses untuk tingkat akhir.',
                    ]);
                }

                $students = Student::query()
                    ->where('school_id', $schoolId)
                    ->where('classroom_id', $source->id)
                    ->where('status', 'Aktif')
                    ->whereIn('id', $validated['selectedStudents'])
                    ->lockForUpdate()
                    ->get();

                if ($students->count() !== count($validated['selectedStudents'])) {
                    throw ValidationException::withMessages([
                        'selectedStudents' => 'Daftar siswa berubah. Muat ulang halaman dan pilih kembali.',
                    ]);
                }

                if ($validated['actionType'] === 'graduate') {
                    Student::query()
                        ->where('school_id', $schoolId)
                        ->whereIn('id', $students->modelKeys())
                        ->update(['status' => 'Lulus', 'classroom_id' => null]);
                    $message = $students->count().' siswa berhasil diluluskan.';
                } else {
                    Student::query()
                        ->where('school_id', $schoolId)
                        ->whereIn('id', $students->modelKeys())
                        ->update(['classroom_id' => $target->id]);
                    $message = $students->count().' siswa berhasil dipindahkan ke kelas '.$target->name.'.';
                }

                session()->flash('message', $message);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Proses belum dapat diselesaikan. Silakan coba kembali.');

            return;
        }

        $this->reset(['selectedStudents', 'selectAll', 'targetClassroomId']);
    }

    private function validateTransition(Classroom $source, Classroom $target, string $action): void
    {
        $sourceGrade = (int) $source->grade_level;
        $targetGrade = (int) $target->grade_level;

        if ($action === 'promote' && $targetGrade !== $sourceGrade + 1) {
            throw ValidationException::withMessages([
                'targetClassroomId' => 'Kelas tujuan kenaikan harus satu tingkat di atas kelas asal.',
            ]);
        }

        if (in_array($action, ['transfer', 'stay'], true) && $targetGrade !== $sourceGrade) {
            throw ValidationException::withMessages([
                'targetClassroomId' => 'Kelas tujuan harus berada pada tingkat yang sama.',
            ]);
        }

        if (in_array($action, ['promote', 'transfer'], true) && $source->is($target)) {
            throw ValidationException::withMessages([
                'targetClassroomId' => 'Kelas tujuan harus berbeda dari kelas asal.',
            ]);
        }
    }

    private function promotionIsUnlocked(): bool
    {
        $value = SystemSetting::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->where('key', 'is_promotion_unlocked')
            ->value('value');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** @return Builder<Classroom> */
    private function schoolClassroomsQuery(): Builder
    {
        return Classroom::query()->where('school_id', app(CurrentSchool::class)->id());
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();
        $classrooms = $this->schoolClassroomsQuery()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
        $students = collect();
        $targetClassrooms = collect();
        $sourceClass = null;
        $isFinalGrade = false;

        if ($this->sourceClassroomId) {
            $sourceClass = $classrooms->firstWhere('id', (int) $this->sourceClassroomId);

            if ($sourceClass) {
                $students = Student::query()
                    ->select(['id', 'name', 'nis', 'nisn'])
                    ->where('school_id', $schoolId)
                    ->where('classroom_id', $sourceClass->id)
                    ->where('status', 'Aktif')
                    ->orderBy('name')
                    ->limit(200)
                    ->get();

                $sourceGrade = (int) $sourceClass->grade_level;
                $isFinalGrade = in_array($sourceGrade, [6, 9, 12], true);
                $targetClassrooms = $classrooms->filter(function (Classroom $classroom) use ($sourceGrade): bool {
                    return match ($this->actionType) {
                        'promote' => (int) $classroom->grade_level === $sourceGrade + 1,
                        'transfer' => (int) $classroom->grade_level === $sourceGrade
                            && (string) $classroom->id !== (string) $this->sourceClassroomId,
                        'stay' => (int) $classroom->grade_level === $sourceGrade,
                        default => false,
                    };
                })->values();
            }
        }

        return view('livewire.admin.academic.promotion.index', compact(
            'classrooms',
            'students',
            'targetClassrooms',
            'sourceClass',
            'isFinalGrade',
        ));
    }
}
