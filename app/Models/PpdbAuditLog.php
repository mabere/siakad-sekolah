<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbAuditLog extends Model
{
    protected $fillable = [
        'school_id',
        'ppdb_application_id',
        'actor_id',
        'action',
        'from_status',
        'to_status',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
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

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
