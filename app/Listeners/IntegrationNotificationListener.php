<?php

namespace App\Listeners;

use App\Events\IdeaApproved;
use App\Events\IdeaCreated;
use App\Models\Integration;
use App\Services\Integrations\SlackService;
use App\Services\Integrations\TeamsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class IntegrationNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle idea created event.
     */
    public function handleIdeaCreated(IdeaCreated $event): void
    {
        $this->sendNotifications($event->idea, 'idea_created');
    }

    /**
     * Handle idea approved event.
     */
    public function handleIdeaApproved(IdeaApproved $event): void
    {
        $this->sendNotifications($event->idea, 'idea_approved');
    }

    /**
     * Send notifications to active integrations.
     */
    protected function sendNotifications($idea, string $eventType): void
    {
        // Get all active integrations
        $integrations = Integration::where('is_active', true)->get();

        foreach ($integrations as $integration) {
            try {
                switch ($integration->type) {
                    case 'slack':
                        $this->sendSlackNotification($integration, $idea, $eventType);
                        break;

                    case 'teams':
                        $this->sendTeamsNotification($integration, $idea, $eventType);
                        break;

                    default:
                        // Other integration types can be added here
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send {$eventType} notification via {$integration->type}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Send Slack notification.
     */
    protected function sendSlackNotification(Integration $integration, $idea, string $eventType): void
    {
        $slackService = app(SlackService::class);

        $message = match ($eventType) {
            'idea_created' => "New idea submitted: {$idea->title}",
            'idea_approved' => "Idea approved: {$idea->title}",
            default => "Idea update: {$idea->title}",
        };

        $slackService->sendNotification(
            $integration->config,
            $message,
            [
                'idea' => [
                    'id' => $idea->id,
                    'title' => $idea->title,
                    'description' => $idea->description,
                    'status' => $idea->status,
                    'author' => $idea->user->name ?? 'Anonymous',
                    'category' => $idea->category->name ?? null,
                ],
            ]
        );
    }

    /**
     * Send Teams notification.
     */
    protected function sendTeamsNotification(Integration $integration, $idea, string $eventType): void
    {
        $teamsService = app(TeamsService::class);

        $message = match ($eventType) {
            'idea_created' => "New idea submitted: {$idea->title}",
            'idea_approved' => "Idea approved: {$idea->title}",
            default => "Idea update: {$idea->title}",
        };

        $teamsService->sendNotification(
            $integration->config,
            $message,
            [
                'idea' => [
                    'id' => $idea->id,
                    'title' => $idea->title,
                    'description' => $idea->description,
                    'status' => $idea->status,
                    'author' => $idea->user->name ?? 'Anonymous',
                    'category' => $idea->category->name ?? null,
                ],
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            IdeaCreated::class => 'handleIdeaCreated',
            IdeaApproved::class => 'handleIdeaApproved',
        ];
    }
}
