<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P5Assessment extends Model
{
    protected $table = 'p5_assessments';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'p5_project_id',
        'p5_project_dimension_id',
        'student_id',
        'score',
        'notes',
    ];

    /** @return BelongsTo<P5Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /** @return BelongsTo<P5ProjectDimension, $this> */
    public function dimension(): BelongsTo
    {
        return $this->belongsTo(P5ProjectDimension::class, 'p5_project_dimension_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
