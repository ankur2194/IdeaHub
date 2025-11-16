<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Badge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $types = ['milestone', 'achievement', 'participation', 'special'];
        $categories = ['ideas', 'comments', 'approvals', 'engagement', 'quality'];
        $rarities = ['common', 'uncommon', 'rare', 'epic', 'legendary'];

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['star', 'trophy', 'medal', 'crown', 'flame']),
            'type' => fake()->randomElement($types),
            'category' => fake()->randomElement($categories),
            'criteria' => [
                'type' => 'count',
                'target' => fake()->numberBetween(1, 100),
            ],
            'points_reward' => fake()->numberBetween(10, 500),
            'rarity' => fake()->randomElement($rarities),
            'order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the badge is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
