<?php

namespace App\Livewire\Tu;

use App\Models\Student;
use App\Models\StudentLetter;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StudentLettersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    // Create Modal
    public bool $showCreateModal = false;

    public string|int|null $studentId = null;

    public string $letterType = 'surat_keterangan_aktif';

    public ?string $purpose = null;

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function createLetter(): void
    {
        $this->validate([
            'studentId' => ['required', 'integer'],
            'letterType' => ['required', 'in:surat_keterangan_aktif,surat_berkelakuan_baik,surat_pindah_sekolah'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $student = Student::query()
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif')
            ->find($this->studentId);

        if (! $student) {
            $this->addError('studentId', 'Siswa tidak valid untuk sekolah aktif.');

            return;
        }

        DB::transaction(function () use ($schoolId, $student): void {
            $letter = StudentLetter::create([
                'school_id' => $schoolId,
                'student_id' => $student->id,
                'letter_type' => $this->letterType,
                'purpose' => $this->purpose ? trim($this->purpose) : null,
                'status' => 'approved',
                'issued_by' => auth()->id(),
                'issued_at' => now(),
            ]);

            // The primary key is allocated by the database, so concurrent requests
            // cannot produce the same serial number.
            $letter->update([
                'letter_number' => '421.3/TU/'.date('Y').'/'.str_pad((string) $letter->id, 6, '0', STR_PAD_LEFT),
            ]);
        });

        session()->flash('message', 'Surat Keterangan berhasil diterbitkan.');
        $this->showCreateModal = false;
    }

    public function updateStatus(int|string $id, string $status): void
    {
        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return;
        }

        $letter = StudentLetter::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->first();
        if ($letter) {
            DB::transaction(function () use ($letter, $status): void {
                $letter = StudentLetter::query()->whereKey($letter->id)->lockForUpdate()->first();
                if (! $letter) {
                    return;
                }
                $letter->update([
                    'status' => $status,
                    'issued_by' => auth()->id(),
                    'issued_at' => $status === 'approved' ? now() : null,
                ]);
            });
            session()->flash('message', 'Status permohonan surat diperbarui.');
        }
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $letters = StudentLetter::with(['student.classroom', 'issuer'])
            ->where('school_id', $schoolId)
            ->when($this->search, function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nisn', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(8);

        $students = Student::query()
            ->where('school_id', $schoolId)
            ->where('status', 'Aktif')
            ->with('classroom')
            ->orderBy('name')
            ->get();

        return view('livewire.tu.student-letters-index', [
            'letters' => $letters,
            'students' => $students,
        ]);
    }
}
