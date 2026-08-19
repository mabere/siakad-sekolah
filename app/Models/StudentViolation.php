<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentViolation extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'student_id',
        'reporter_teacher_id',
        'violation_master_id',
        'category',
        'points',
        'event_date',
        'action_taken',
        'notes',
    ];

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

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Teacher, $this> */
    public function reporterTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'reporter_teacher_id');
    }

    /** @return BelongsTo<ViolationMaster, $this> */
    public function violationMaster(): BelongsTo
    {
        return $this->belongsTo(ViolationMaster::class, 'violation_master_id');
    }
}
