<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubmission extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'exam_id',
        'student_id',
        'answers',
        'question_order',
        'essay_scores',
        'score',
        'total_correct',
        'total_questions',
        'started_at',
        'submitted_at',
        'status',
    ];

    protected $casts = [
        'answers' => 'array',
        'question_order' => 'array',
        'essay_scores' => 'array',
        'score' => 'float',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
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

    /** @return BelongsTo<Exam, $this> */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
