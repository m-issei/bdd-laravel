<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Response;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Response>
 */
class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition(): array
    {
        return [
            'participant_id' => Participant::factory(),
            'survey_id'      => Survey::factory(),
            'submitted_at'   => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(['submitted_at' => now()]);
    }
}
