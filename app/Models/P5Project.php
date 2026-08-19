<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5Project extends Model
{
    protected $table = 'p5_projects';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'classroom_id',
        'teacher_id',
        'title',
        'theme',
        'description',
        'phase',
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

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<Teacher, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /** @return HasMany<P5ProjectDimension, $this> */
    public function dimensions(): HasMany
    {
        return $this->hasMany(P5ProjectDimension::class, 'p5_project_id');
    }

    /** @return HasMany<P5Assessment, $this> */
    public function assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'p5_project_id');
    }

    /** @return HasMany<P5StudentNote, $this> */
    public function studentNotes(): HasMany
    {
        return $this->hasMany(P5StudentNote::class, 'p5_project_id');
    }
}
