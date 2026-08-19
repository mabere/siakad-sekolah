<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P5StudentNote extends Model
{
    protected $table = 'p5_student_notes';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'p5_project_id',
        'student_id',
        'process_notes',
    ];

    /** @return BelongsTo<P5Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
