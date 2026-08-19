<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $total_violations Runtime aggregate from withCount().
 * @property int $total_counselings Runtime aggregate from withCount().
 * @property int|float|null $total_points Runtime aggregate from withSum().
 */
class Student extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'birth_date' => 'date',
    ];

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

    /** @return BelongsTo<Major, $this> */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return HasMany<StudentViolation, $this> */
    public function violations(): HasMany
    {
        return $this->hasMany(StudentViolation::class);
    }

    /** @return HasMany<CounselingRecord, $this> */
    public function counselings(): HasMany
    {
        return $this->hasMany(CounselingRecord::class);
    }

    /** @return HasMany<ExtracurricularMember, $this> */
    public function extracurricularMembers(): HasMany
    {
        return $this->hasMany(ExtracurricularMember::class);
    }

    /** @return HasMany<StudentAchievement, $this> */
    public function achievements(): HasMany
    {
        return $this->hasMany(StudentAchievement::class);
    }

    /** @return HasMany<ExamSubmission, $this> */
    public function examSubmissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class);
    }
}
