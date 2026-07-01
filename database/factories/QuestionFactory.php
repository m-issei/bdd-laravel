<?php

namespace Database\Factories;

use App\Models\AnswerType;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'survey_id'         => Survey::factory(),
            'survey_section_id' => null,
            'answer_type_id'    => AnswerType::firstOrCreate(['name' => 'radio_1_5'], ['label' => '1〜5段階'])->id,
            'text'              => fake()->sentence() . '？',
            'order'             => 0,
        ];
    }
}
