<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumTarget extends Model
{
    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'p5_dimensions' => 'array',
        'inquiry_questions' => 'array',
        'is_active' => 'boolean',
        'grade_level' => 'integer',
        'chapter_number' => 'integer',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForPhase(Builder $query, string $phase): Builder
    {
        return $query->where('phase', $phase);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForSubject(Builder $query, string $subjectName): Builder
    {
        return $query->where(function (Builder $q) use ($subjectName): void {
            $q->where('subject_name', 'like', "%{$subjectName}%")
                ->orWhereHas('subject', function (Builder $sq) use ($subjectName): void {
                    $sq->where('name', 'like', "%{$subjectName}%");
                });
        });
    }
}
