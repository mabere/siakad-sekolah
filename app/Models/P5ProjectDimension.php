<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5ProjectDimension extends Model
{
    protected $table = 'p5_project_dimensions';

    protected $fillable = [
        'p5_project_id',
        'dimension_name',
        'element_name',
        'sub_element',
        'target_description',
    ];

    /** @return BelongsTo<P5Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /** @return HasMany<P5Assessment, $this> */
    public function assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'p5_project_dimension_id');
    }
}
