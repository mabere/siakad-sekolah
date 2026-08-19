<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPayment extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'payment_category_id',
        'academic_year_id',
        'month',
        'deduplication_key',
        'amount',
        'discount_amount',
        'paid_amount',
        'status',
        'payment_method',
        'proof_file',
        'paid_at',
        'receiver_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            if (! $payment->deduplication_key) {
                $payment->deduplication_key = self::makeDeduplicationKey(
                    (int) $payment->school_id,
                    (int) $payment->student_id,
                    (int) $payment->payment_category_id,
                    $payment->academic_year_id !== null ? (int) $payment->academic_year_id : null,
                    $payment->month !== null ? (int) $payment->month : null,
                );
            }
        });
    }

    public static function makeDeduplicationKey(
        int $schoolId,
        int $studentId,
        int $paymentCategoryId,
        ?int $academicYearId,
        ?int $month,
    ): string {
        return hash('sha256', implode('|', [
            $schoolId,
            $studentId,
            $paymentCategoryId,
            $academicYearId ?? 'none',
            $month ?? 'none',
        ]));
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /** @return BelongsTo<PaymentCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getMonthNameAttribute(): string
    {
        if (! $this->month) {
            return '-';
        }
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $months[$this->month] ?? '-';
    }
}
