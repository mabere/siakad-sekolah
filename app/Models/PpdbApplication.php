<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PpdbApplication extends Model
{
    public const SOURCE_ONLINE = 'online';

    public const SOURCE_OFFLINE = 'offline';

    public const VERIFICATION_DRAFT = 'draft';

    public const VERIFICATION_SUBMITTED = 'submitted';

    public const VERIFICATION_REVISION = 'revision';

    public const VERIFICATION_VERIFIED = 'verified';

    public const VERIFICATION_REJECTED = 'rejected';

    public const SELECTION_PENDING = 'pending';

    public const SELECTION_ELIGIBLE = 'eligible';

    public const SELECTION_ACCEPTED = 'accepted';

    public const SELECTION_WAITLISTED = 'waitlisted';

    public const SELECTION_REJECTED = 'rejected';

    public const CONVERSION_NOT_READY = 'not_ready';

    public const CONVERSION_CONVERTED = 'converted';

    protected $fillable = [
        'school_id',
        'ppdb_period_id',
        'ppdb_pathway_id',
        'created_by',
        'application_number',
        'source',
        'contact_email',
        'contact_phone',
        'access_code_hash',
        'verification_status',
        'payment_status',
        'selection_status',
        'reregistration_status',
        'conversion_status',
        'revision_note',
        'rejection_note',
        'submitted_at',
        'verified_at',
        'selected_at',
        'converted_at',
        'converted_student_id',
    ];

    protected $hidden = ['access_code_hash'];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'selected_at' => 'datetime',
            'converted_at' => 'datetime',
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

    /** @return BelongsTo<PpdbPathway, $this> */
    public function pathway(): BelongsTo
    {
        return $this->belongsTo(PpdbPathway::class, 'ppdb_pathway_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasOne<PpdbCandidate, $this> */
    public function candidate(): HasOne
    {
        return $this->hasOne(PpdbCandidate::class);
    }

    /** @return HasMany<PpdbGuardian, $this> */
    public function guardians(): HasMany
    {
        return $this->hasMany(PpdbGuardian::class);
    }

    /** @return HasMany<PpdbDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(PpdbDocument::class);
    }

    /** @return HasMany<PpdbPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(PpdbPayment::class);
    }

    /** @return HasMany<PpdbSelectionScore, $this> */
    public function selectionScores(): HasMany
    {
        return $this->hasMany(PpdbSelectionScore::class);
    }

    /** @return HasOne<PpdbReRegistration, $this> */
    public function reRegistration(): HasOne
    {
        return $this->hasOne(PpdbReRegistration::class);
    }

    /** @return HasMany<PpdbAuditLog, $this> */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(PpdbAuditLog::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }
}
