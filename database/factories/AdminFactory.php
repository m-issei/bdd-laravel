<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'organization_id' => null,
            'name'            => fake()->name(),
            'email'           => fake()->unique()->safeEmail(),
            'password'        => Hash::make('password'),
            'is_super'        => false,
            'is_active'       => true,
        ];
    }

    public function super(): static
    {
        return $this->state([
            'organization_id' => null,
            'is_super'        => true,
        ]);
    }
}
