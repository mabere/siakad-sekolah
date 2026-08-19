<?php

namespace App\Livewire\Admin\Finance;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Support\CurrentSchool;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StudentPaymentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string|int $selectedClassroomId = '';

    public string|int $selectedCategoryId = '';

    public string $selectedStatus = '';

    // Generate Bulk Tagihan SPP
    public bool $showGenerateModal = false;

    public string|int $genClassroomId = '';

    public string|int $genCategoryId = '';

    public string|int $genAcademicYearId = '';

    public int $genMonth = 1;

    // Transaksi Kasir Modal
    public bool $showPaymentModal = false;

    public int|string|null $payPaymentId = null;

    public string|int|float $payAmount = 0;

    public string $payMethod = 'cash';

    public string $payNotes = '';

    public function mount(): void
    {
        $schoolId = app(CurrentSchool::class)->id();
        $activeAy = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first();
        if ($activeAy) {
            $this->genAcademicYearId = $activeAy->id;
        }
        $this->genMonth = (int) date('n');
    }

    public function openGenerateModal(): void
    {
        $this->showGenerateModal = true;
    }

    public function generateBulkPayments(): void
    {
        $this->validate([
            'genClassroomId' => 'required',
            'genCategoryId' => 'required',
            'genAcademicYearId' => 'required',
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $category = PaymentCategory::where('school_id', $schoolId)->find($this->genCategoryId);
        $classroom = Classroom::where('school_id', $schoolId)->find($this->genClassroomId);
        $academicYear = AcademicYear::where('school_id', $schoolId)->find($this->genAcademicYearId);

        if (! $category || ! $classroom || ! $academicYear) {
            $this->addError('genCategoryId', 'Kategori, rombel, atau tahun ajaran tidak valid untuk sekolah aktif.');

            return;
        }

        // Ambil seluruh siswa di rombel terpilih
        $students = Student::query()
            ->where('school_id', $schoolId)
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Aktif')
            ->get();

        $count = DB::transaction(function () use ($students, $category, $schoolId): int {
            $count = 0;
            foreach ($students as $student) {
                $month = $category->type === 'monthly_spp' ? $this->genMonth : null;
                $key = StudentPayment::makeDeduplicationKey(
                    $schoolId,
                    $student->id,
                    $category->id,
                    (int) $this->genAcademicYearId,
                    $month,
                );

                $payment = StudentPayment::firstOrCreate(
                    ['deduplication_key' => $key],
                    [
                        'school_id' => $schoolId,
                        'student_id' => $student->id,
                        'payment_category_id' => $category->id,
                        'academic_year_id' => $this->genAcademicYearId,
                        'month' => $month,
                        'amount' => $category->default_amount,
                        'status' => 'unpaid',
                    ],
                );

                if ($payment->wasRecentlyCreated) {
                    $count++;
                }
            }

            return $count;
        });

        session()->flash('message', "Berhasil menerbitkan {$count} tagihan untuk rombel terpilih.");
        $this->showGenerateModal = false;
    }

    public function openPaymentModal(int|string $id): void
    {
        $payment = StudentPayment::query()
            ->where('school_id', app(CurrentSchool::class)->id())
            ->whereKey($id)
            ->first();
        if ($payment) {
            $this->payPaymentId = $id;
            $this->payAmount = max(0, (float) $payment->amount - (float) $payment->discount_amount - (float) $payment->paid_amount);
            $this->payMethod = 'cash';
            $this->payNotes = '';
            $this->showPaymentModal = true;
        }
    }

    public function processPayment(): void
    {
        $this->validate([
            'payPaymentId' => ['required', 'integer'],
            'payAmount' => ['required', 'numeric', 'gt:0'],
            'payMethod' => ['required', 'string', 'in:cash,bank_transfer'],
            'payNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function (): void {
            $payment = StudentPayment::query()
                ->where('school_id', app(CurrentSchool::class)->id())
                ->whereKey($this->payPaymentId)
                ->lockForUpdate()
                ->firstOrFail();
            $targetAmount = max(0, (float) $payment->amount - (float) $payment->discount_amount);
            $newPaidAmount = (float) $payment->paid_amount + (float) $this->payAmount;
            if ($newPaidAmount > $targetAmount) {
                $this->addError('payAmount', 'Nominal pembayaran melebihi sisa tagihan.');

                return;
            }

            $payment->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newPaidAmount >= $targetAmount ? 'paid' : 'partial',
                'payment_method' => $this->payMethod,
                'paid_at' => now(),
                'receiver_id' => auth()->id(),
                'notes' => $this->payNotes,
            ]);
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        session()->flash('message', 'Pembayaran kasir berhasil dicatat.');
        $this->showPaymentModal = false;
    }

    public function confirmPayment(int|string $id): void
    {
        DB::transaction(function () use ($id): void {
            $payment = StudentPayment::query()
                ->where('school_id', app(CurrentSchool::class)->id())
                ->whereKey($id)
                ->where('status', 'pending_confirmation')
                ->lockForUpdate()
                ->first();
            if (! $payment) {
                return;
            }

            $payment->update([
                'status' => 'paid',
                'paid_amount' => max(0, (float) $payment->amount - (float) $payment->discount_amount),
                'paid_at' => now(),
                'receiver_id' => auth()->id(),
            ]);
            session()->flash('message', 'Bukti pembayaran berhasil diverifikasi.');
        });
    }

    public function render(): View
    {
        $schoolId = app(CurrentSchool::class)->id();

        $payments = StudentPayment::with(['student.classroom', 'category', 'academicYear'])
            ->where('school_id', $schoolId)
            ->when($this->search, function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nisn', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedClassroomId, function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('classroom_id', $this->selectedClassroomId);
                });
            })
            ->when($this->selectedCategoryId, function ($q) {
                $q->where('payment_category_id', $this->selectedCategoryId);
            })
            ->when($this->selectedStatus, function ($q) {
                $q->where('status', $this->selectedStatus);
            })
            ->latest()
            ->paginate(7);

        $classrooms = Classroom::where('school_id', $schoolId)->get();
        $categories = PaymentCategory::where('school_id', $schoolId)->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();

        return view('livewire.admin.finance.student-payment-index', [
            'payments' => $payments,
            'classrooms' => $classrooms,
            'categories' => $categories,
            'academicYears' => $academicYears,
        ]);
    }
}
