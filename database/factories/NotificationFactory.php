<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'idea_created',
            'idea_approved',
            'idea_rejected',
            'comment_created',
            'badge_earned',
            'level_up',
            'approval_required',
        ];

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement($types),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'data' => [
                'action_url' => '/ideas/' . fake()->numberBetween(1, 100),
                'related_id' => fake()->numberBetween(1, 100),
            ],
            'is_read' => false,
            'email_sent' => false,
            'read_at' => null,
        ];
    }

    /**
     * Indicate that the notification has been read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification email was sent.
     */
    public function emailSent(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_sent' => true,
        ]);
    }
}
