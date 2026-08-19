<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $tugas
 * @property int|null $uts
 * @property int|null $uas
 * @property float $calculated_final Runtime value prepared for the student grade view.
 * @property string $calculated_predicate Runtime value prepared for the student grade view.
 */
class Grade extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'final_score' => 'float',
            'tugas' => 'integer',
            'uts' => 'integer',
            'uas' => 'integer',
        ];
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
