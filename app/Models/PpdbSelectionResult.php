<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbSelectionResult extends Model
{
    protected $fillable = [
        'school_id',
        'ppdb_period_id',
        'ppdb_application_id',
        'ppdb_pathway_id',
        'rank',
        'selection_status',
        'total_score',
        'average_score',
        'snapshot_at',
        'finalized_by',
        'invalidated_at',
        'invalidated_by',
        'invalidation_reason',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'total_score' => 'decimal:2',
            'average_score' => 'decimal:2',
            'snapshot_at' => 'datetime',
            'invalidated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<PpdbPeriod, $this> */
    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'ppdb_period_id');
    }

    /** @return BelongsTo<PpdbApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PpdbApplication::class, 'ppdb_application_id');
    }

    /** @return BelongsTo<PpdbPathway, $this> */
    public function pathway(): BelongsTo
    {
        return $this->belongsTo(PpdbPathway::class, 'ppdb_pathway_id');
    }

    /** @return BelongsTo<User, $this> */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
