<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbDocument extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'ppdb_application_id',
        'ppdb_requirement_id',
        'uploaded_by',
        'verified_by',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PpdbApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PpdbApplication::class, 'ppdb_application_id');
    }

    /** @return BelongsTo<PpdbRequirement, $this> */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(PpdbRequirement::class, 'ppdb_requirement_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
