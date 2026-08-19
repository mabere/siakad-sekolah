<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingRecord extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'student_id',
        'counselor_teacher_id',
        'counseling_type',
        'counseling_date',
        'counseling_time',
        'problem_description',
        'solution_plan',
        'status',
        'follow_up_date',
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
    public function counselorTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'counselor_teacher_id');
    }
}
