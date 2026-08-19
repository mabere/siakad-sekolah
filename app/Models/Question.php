<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'question_bank_id',
        'type',
        'question_text',
        'options',
        'correct_answer',
        'score_weight',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /** @return BelongsTo<QuestionBank, $this> */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }
}
