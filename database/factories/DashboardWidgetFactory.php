<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DashboardWidget>
 */
class DashboardWidgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(\App\Models\DashboardWidget::TYPES));
        $category = fake()->randomElement(array_keys(\App\Models\DashboardWidget::CATEGORIES));

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'name' => ucfirst(fake()->words(2, true)) . ' Widget',
            'type' => $type,
            'category' => $category,
            'config' => [
                'time_range' => fake()->randomElement(['7d', '30d', '90d', '1y']),
                'aggregation' => fake()->randomElement(['count', 'sum', 'avg']),
                'limit' => fake()->numberBetween(5, 20),
            ],
            'is_system' => false,
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the widget is a system widget.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
            'tenant_id' => null,
        ]);
    }
}
