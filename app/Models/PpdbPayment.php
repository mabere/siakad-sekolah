<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbPayment extends Model
{
    public const TYPE_REGISTRATION = 'registration';

    public const TYPE_REREGISTRATION = 'reregistration';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'ppdb_application_id',
        'invoice_number',
        'verified_by',
        'type',
        'amount',
        'discount_amount',
        'paid_amount',
        'status',
        'payment_method',
        'proof_file',
        'proof_original_name',
        'proof_mime_type',
        'proof_file_size',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'proof_file_size' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PpdbApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PpdbApplication::class, 'ppdb_application_id');
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** @return HasMany<PpdbPaymentHistory, $this> */
    public function histories(): HasMany
    {
        return $this->hasMany(PpdbPaymentHistory::class, 'ppdb_payment_id')->latest();
    }
}
