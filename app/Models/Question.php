<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['survey_id', 'survey_section_id', 'answer_type_id', 'text', 'order'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SurveySection::class, 'survey_section_id');
    }

    public function answerType(): BelongsTo
    {
        return $this->belongsTo(AnswerType::class);
    }

    public function responseAnswers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }
}
