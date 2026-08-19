<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PpdbPeriod extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_OPEN = 'open';

    public const STATUS_VERIFICATION = 'verification';

    public const STATUS_SELECTION = 'selection';

    public const STATUS_ANNOUNCED = 'announced';

    public const STATUS_REREGISTRATION = 'reregistration';

    public const STATUS_CLOSED = 'closed';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_OPEN,
        self::STATUS_VERIFICATION,
        self::STATUS_SELECTION,
        self::STATUS_ANNOUNCED,
        self::STATUS_REREGISTRATION,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'code',
        'level',
        'registration_starts_at',
        'registration_ends_at',
        'verification_ends_at',
        'announcement_at',
        're_registration_ends_at',
        'selection_finalized_at',
        'selection_finalized_by',
        'status',
        'payment_required',
        'default_registration_fee',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'verification_ends_at' => 'datetime',
            'announcement_at' => 'datetime',
            're_registration_ends_at' => 'datetime',
            'selection_finalized_at' => 'datetime',
            'payment_required' => 'boolean',
            'default_registration_fee' => 'decimal:2',
            'settings' => 'array',
        ];
    }

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

    /** @return HasMany<PpdbPathway, $this> */
    public function pathways(): HasMany
    {
        return $this->hasMany(PpdbPathway::class);
    }

    /** @return HasMany<PpdbApplication, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(PpdbApplication::class);
    }

    /** @return HasMany<PpdbSelectionResult, $this> */
    public function selectionResults(): HasMany
    {
        return $this->hasMany(PpdbSelectionResult::class, 'ppdb_period_id');
    }

    /**
     * @param  Builder<PpdbPeriod>  $query
     * @return Builder<PpdbPeriod>
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /** @return Attribute<bool, bool> */
    protected function isRegistrationOpen(): Attribute
    {
        return Attribute::get(fn (): bool => $this->status === self::STATUS_OPEN
            && now()->between($this->registration_starts_at, $this->registration_ends_at));
    }

    public function isAnnouncementPublished(): bool
    {
        return in_array($this->status, [self::STATUS_ANNOUNCED, self::STATUS_REREGISTRATION, self::STATUS_CLOSED], true)
            && $this->announcement_at !== null
            && $this->dateAttribute('announcement_at')?->isPast();
    }

    public function allowsVerification(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_VERIFICATION], true);
    }

    public function verificationStageLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Pendaftaran aktif - verifikasi berjalan',
            self::STATUS_VERIFICATION => 'Pendaftaran ditutup - penyelesaian verifikasi',
            default => 'Verifikasi ditutup pada tahap ini',
        };
    }

    public function isReregistrationOpen(): bool
    {
        return $this->status === self::STATUS_REREGISTRATION
            && $this->re_registration_ends_at !== null
            && ($endsAt = $this->dateAttribute('re_registration_ends_at')) !== null
            && now()->lessThanOrEqualTo($endsAt);
    }

    public function dateAttribute(string $attribute): ?Carbon
    {
        $value = $this->getAttribute($attribute);

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        return is_string($value) && $value !== '' ? Carbon::parse($value) : null;
    }
}
