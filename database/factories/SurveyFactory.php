<?php

namespace Database\Factories;

use App\Enums\SurveyStatus;
use App\Models\Organization;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'title'           => fake()->sentence(3),
            'status'          => SurveyStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => SurveyStatus::Published]);
    }
}
