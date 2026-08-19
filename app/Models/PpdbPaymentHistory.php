<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbPaymentHistory extends Model
{
    protected $fillable = [
        'school_id',
        'ppdb_application_id',
        'ppdb_payment_id',
        'actor_id',
        'from_status',
        'to_status',
        'amount',
        'proof_file',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<PpdbPayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(PpdbPayment::class, 'ppdb_payment_id');
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
