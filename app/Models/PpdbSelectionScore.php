<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbSelectionScore extends Model
{
    protected $fillable = [
        'ppdb_application_id',
        'assessed_by',
        'criterion',
        'score',
        'notes',
        'assessed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'assessed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PpdbApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PpdbApplication::class, 'ppdb_application_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
