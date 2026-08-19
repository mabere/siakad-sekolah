<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<QuestionBank, $this> */
    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    /** @return HasMany<Exam, $this> */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    /** @return HasMany<Extracurricular, $this> */
    public function extracurriculars(): HasMany
    {
        return $this->hasMany(Extracurricular::class);
    }

    /** @return HasMany<StudentViolation, $this> */
    public function violations(): HasMany
    {
        return $this->hasMany(StudentViolation::class, 'reporter_teacher_id');
    }

    /** @return HasMany<CounselingRecord, $this> */
    public function counselings(): HasMany
    {
        return $this->hasMany(CounselingRecord::class, 'counselor_teacher_id');
    }
}
