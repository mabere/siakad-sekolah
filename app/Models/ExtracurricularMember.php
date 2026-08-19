<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularMember extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'extracurricular_id',
        'student_id',
        'grade',
        'description',
    ];

    /** @return BelongsTo<Extracurricular, $this> */
    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
