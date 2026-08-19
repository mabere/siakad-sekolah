<?php

namespace App\Livewire\Parent;

use App\Models\ParentStudentRelation;
use App\Models\StudentPayment;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PaymentsIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public int|string|null $selectedStudentId = null;

    // Upload Bukti Modal
    public bool $showUploadModal = false;

    public int|string|null $paymentId = null;

    public ?TemporaryUploadedFile $proofFile = null;

    public ?string $paymentNotes = null;

    public function mount(): void
    {
        $relations = $this->accessibleRelations();
        if ($relations->isNotEmpty()) {
            $this->selectedStudentId = $relations->first()->student_id;
        }
    }

    public function selectStudent(int $id): void
    {
        abort_unless(
            $this->accessibleRelations()->contains(fn ($relation): bool => (int) $relation->student_id === $id),
            403,
            'Siswa tidak terhubung ke akun orang tua ini.',
        );

        $this->selectedStudentId = $id;
        $this->resetPage('paymentsPage');
    }

    public function openUploadModal(int $id): void
    {
        abort_unless(
            $this->accessiblePayments()->whereKey($id)->exists(),
            403,
            'Pembayaran tidak dapat diakses oleh akun orang tua ini.',
        );

        $this->paymentId = $id;
        $this->proofFile = null;
        $this->paymentNotes = '';
        $this->showUploadModal = true;
    }

    public function uploadProof(): void
    {
        $this->validate([
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000',
            'paymentNotes' => 'nullable|string|max:1000',
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $previousPath = null;

        DB::transaction(function () use ($schoolId, &$previousPath): void {
            $payment = $this->accessiblePayments()
                ->whereKey($this->paymentId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->lockForUpdate()
                ->first();

            abort_unless($payment !== null, 403, 'Pembayaran tidak dapat diproses.');

            $previousPath = $payment->proof_file;
            $path = $this->proofFile->store('payment_proofs/'.$schoolId.'/'.$payment->id, 'local');
            abort_if($path === false, 500, 'Bukti pembayaran gagal disimpan.');
            $payment->update([
                'status' => 'pending_confirmation',
                'payment_method' => 'bank_transfer',
                'proof_file' => $path,
                'notes' => $this->paymentNotes,
            ]);
        });

        if (is_string($previousPath) && str_starts_with($previousPath, 'payment_proofs/')) {
            Storage::disk('local')->delete($previousPath);
        }

        session()->flash('message', 'Bukti transfer berhasil diupload. Menunggu verifikasi Staf TU.');
        $this->showUploadModal = false;
    }

    public function render(): View
    {
        $relations = $this->accessibleRelations();

        $payments = StudentPayment::query()
            ->whereKey(0)
            ->paginate(15, ['*'], 'paymentsPage');
        if ($this->selectedStudentId) {
            $student = $relations->firstWhere('student_id', $this->selectedStudentId)?->student;

            if (! $student) {
                $this->selectedStudentId = null;
            } else {
                $payments = $this->accessiblePayments()
                    ->with(['category', 'academicYear'])
                    ->where('student_id', $this->selectedStudentId)
                    ->latest()
                    ->paginate(15, ['*'], 'paymentsPage');
            }
        }

        return view('livewire.parent.payments-index', [
            'relations' => $relations,
            'payments' => $payments,
        ]);
    }

    /** @return Collection<int, ParentStudentRelation> */
    private function accessibleRelations(): Collection
    {
        return ParentStudentRelation::with(['student.classroom'])
            ->where('parent_user_id', auth()->id())
            ->whereHas('student', function ($query): void {
                $query->where('school_id', app(CurrentSchool::class)->id());
            })
            ->get();
    }

    /** @return Builder<StudentPayment> */
    private function accessiblePayments(): Builder
    {
        return StudentPayment::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereIn('student_id', $this->accessibleRelations()->pluck('student_id'));
    }
}
