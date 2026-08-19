<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbRequirement extends Model
{
    protected $fillable = [
        'ppdb_pathway_id',
        'code',
        'name',
        'is_required',
        'accepted_mimes',
        'max_file_size_kb',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'max_file_size_kb' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<PpdbPathway, $this> */
    public function pathway(): BelongsTo
    {
        return $this->belongsTo(PpdbPathway::class, 'ppdb_pathway_id');
    }

    /** @return HasMany<PpdbDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(PpdbDocument::class);
    }
}
