<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbCandidate extends Model
{
    protected $fillable = [
        'ppdb_application_id',
        'name',
        'nik',
        'nik_normalized',
        'nisn',
        'nisn_normalized',
        'gender',
        'birth_place',
        'birth_date',
        'previous_school',
        'previous_school_npsn',
        'address',
        'village',
        'district',
        'regency',
        'province',
        'postal_code',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    /** @return BelongsTo<PpdbApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PpdbApplication::class, 'ppdb_application_id');
    }
}
