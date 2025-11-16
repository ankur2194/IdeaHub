<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Idea>
 */
class IdeaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Idea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'pending', 'approved', 'rejected', 'implemented'];
        $status = fake()->randomElement($statuses);

        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'status' => $status,
            'is_anonymous' => fake()->boolean(20),
            'likes_count' => fake()->numberBetween(0, 100),
            'comments_count' => fake()->numberBetween(0, 50),
            'views_count' => fake()->numberBetween(0, 500),
            'attachments' => [],
            'submitted_at' => now(),
            'approved_at' => $status === 'approved' ? now() : null,
            'rejected_at' => $status === 'rejected' ? now() : null,
            'implemented_at' => $status === 'implemented' ? now() : null,
        ];
    }

    /**
     * Indicate that the idea is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the idea is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the idea is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'submitted_at' => now(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the idea is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'submitted_at' => now(),
            'rejected_at' => now(),
        ]);
    }

    /**
     * Indicate that the idea is implemented.
     */
    public function implemented(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'implemented',
            'submitted_at' => now(),
            'approved_at' => now(),
            'implemented_at' => now(),
        ]);
    }

    /**
     * Indicate that the idea is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
        ]);
    }
}
