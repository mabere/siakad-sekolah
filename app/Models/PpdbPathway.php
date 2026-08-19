<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbPathway extends Model
{
    public const UMUM = 'umum';

    public const PRESTASI = 'prestasi';

    public const PINDAHAN = 'pindahan';

    public const ZONASI = 'zonasi';

    public const AFIRMASI = 'afirmasi';

    /** @var list<string> */
    public const DEFAULT_CODES = [self::UMUM, self::PRESTASI, self::PINDAHAN];

    protected $fillable = [
        'ppdb_period_id',
        'code',
        'name',
        'description',
        'quota',
        'registration_fee',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'quota' => 'integer',
            'registration_fee' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    /** @return BelongsTo<PpdbPeriod, $this> */
    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'ppdb_period_id');
    }

    /** @return HasMany<PpdbRequirement, $this> */
    public function requirements(): HasMany
    {
        return $this->hasMany(PpdbRequirement::class);
    }

    /** @return HasMany<PpdbApplication, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(PpdbApplication::class);
    }
}
