<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbAccessRecovery extends Model
{
    protected $fillable = [
        'school_id',
        'ppdb_application_id',
        'channel',
        'destination_hash',
        'code_hash',
        'attempts',
        'expires_at',
        'consumed_at',
        'requested_ip',
    ];

    protected $hidden = ['destination_hash', 'code_hash'];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<PpdbApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PpdbApplication::class, 'ppdb_application_id');
    }
}
