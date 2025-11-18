<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDashboard>
 */
class UserDashboardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'user_id' => \App\Models\User::factory(),
            'tenant_id' => \App\Models\Tenant::factory(),
            'name' => ucfirst($name).' Dashboard',
            'slug' => \Illuminate\Support\Str::slug($name),
            'widgets' => [],
            'layout' => [
                'columns' => 12,
                'row_height' => 100,
                'compact_type' => 'vertical',
            ],
            'is_default' => false,
            'is_shared' => fake()->boolean(20), // 20% chance of being shared
        ];
    }

    /**
     * Indicate that the dashboard is default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the dashboard is shared.
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => true,
        ]);
    }
}
