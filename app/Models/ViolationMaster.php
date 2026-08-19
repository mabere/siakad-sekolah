<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $category
 * @property int $default_points
 */
class ViolationMaster extends Model
{
    protected $fillable = [
        'school_id',
        'code',
        'name',
        'category',
        'default_points',
    ];

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
