<?php

namespace Database\Factories;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'title'           => fake()->sentence(4),
            'body'            => fake()->paragraphs(2, true),
            'status'          => AnnouncementStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => AnnouncementStatus::Published]);
    }
}
