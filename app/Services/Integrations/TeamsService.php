<?php

namespace App\Services\Integrations;

use App\Models\Idea;
use App\Models\Integration;
use App\Models\IntegrationLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TeamsService
{
    /**
     * Create a new TeamsService instance.
     */
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Test Microsoft Teams webhook connection.
     */
    public function testConnection(array $config): bool
    {
        try {
            $webhookUrl = $config['webhook_url'] ?? null;

            if (! $webhookUrl) {
                return false;
            }

            $card = [
                'type' => 'message',
                'attachments' => [
                    [
                        'contentType' => 'application/vnd.microsoft.card.adaptive',
                        'content' => [
                            'type' => 'AdaptiveCard',
                            'body' => [
                                [
                                    'type' => 'TextBlock',
                                    'text' => 'IdeaHub Connection Test',
                                    'weight' => 'Bolder',
                                    'size' => 'Medium',
                                ],
                                [
                                    'type' => 'TextBlock',
                                    'text' => 'This is a test message to verify the Teams integration is working properly.',
                                    'wrap' => true,
                                ],
                            ],
                            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                            'version' => '1.2',
                        ],
                    ],
                ],
            ];

            $response = $this->client->post($webhookUrl, [
                'json' => $card,
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error('Teams connection test failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send a notification to Microsoft Teams.
     */
    public function sendNotification(array $config, string $message, array $data = []): void
    {
        try {
            $webhookUrl = $config['webhook_url'] ?? null;

            if (! $webhookUrl) {
                throw new \Exception('Teams webhook URL not configured');
            }

            $card = $this->buildSimpleCard($message, $data);

            $this->client->post($webhookUrl, [
                'json' => $card,
            ]);
        } catch (GuzzleException $e) {
            Log::error('Failed to send Teams notification: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Post an Adaptive Card to Microsoft Teams.
     */
    public function postAdaptiveCard(array $config, string $webhookUrl, array $card): void
    {
        try {
            if (! $webhookUrl) {
                throw new \Exception('Teams webhook URL not provided');
            }

            $response = $this->client->post($webhookUrl, [
                'json' => $card,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to post Adaptive Card to Teams');
            }
        } catch (GuzzleException $e) {
            Log::error('Failed to post Adaptive Card to Teams: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle idea created event.
     */
    public function handleIdeaCreated(Integration $integration, Idea $idea): void
    {
        try {
            $config = $integration->config;
            $webhookUrl = $config['webhook_url'] ?? null;

            if (! $webhookUrl) {
                throw new \Exception('Teams webhook URL not configured');
            }

            $card = [
                'type' => 'message',
                'attachments' => [
                    [
                        'contentType' => 'application/vnd.microsoft.card.adaptive',
                        'content' => [
                            'type' => 'AdaptiveCard',
                            'body' => [
                                [
                                    'type' => 'TextBlock',
                                    'text' => '🎯 New Idea Submitted',
                                    'weight' => 'Bolder',
                                    'size' => 'Large',
                                    'color' => 'Accent',
                                ],
                                [
                                    'type' => 'FactSet',
                                    'facts' => [
                                        [
                                            'title' => 'Title:',
                                            'value' => $idea->title,
                                        ],
                                        [
                                            'title' => 'Status:',
                                            'value' => ucfirst($idea->status),
                                        ],
                                        [
                                            'title' => 'Category:',
                                            'value' => $idea->category->name,
                                        ],
                                        [
                                            'title' => 'Submitted By:',
                                            'value' => $idea->is_anonymous ? 'Anonymous' : $idea->user->name,
                                        ],
                                        [
                                            'title' => 'Submitted At:',
                                            'value' => $idea->submitted_at->format('Y-m-d H:i:s'),
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'TextBlock',
                                    'text' => 'Description',
                                    'weight' => 'Bolder',
                                    'spacing' => 'Medium',
                                ],
                                [
                                    'type' => 'TextBlock',
                                    'text' => $idea->description,
                                    'wrap' => true,
                                    'spacing' => 'Small',
                                ],
                            ],
                            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                            'version' => '1.2',
                        ],
                    ],
                ],
            ];

            $this->client->post($webhookUrl, [
                'json' => $card,
            ]);

            $this->logSuccess($integration, 'idea_created', [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
            ]);
        } catch (\Exception $e) {
            $this->logError($integration, 'idea_created', $e->getMessage(), [
                'idea_id' => $idea->id,
            ]);
            Log::error('Failed to handle Teams idea created event: '.$e->getMessage());
        }
    }

    /**
     * Handle idea approved event.
     */
    public function handleIdeaApproved(Integration $integration, Idea $idea): void
    {
        try {
            $config = $integration->config;
            $webhookUrl = $config['webhook_url'] ?? null;

            if (! $webhookUrl) {
                throw new \Exception('Teams webhook URL not configured');
            }

            $card = [
                'type' => 'message',
                'attachments' => [
                    [
                        'contentType' => 'application/vnd.microsoft.card.adaptive',
                        'content' => [
                            'type' => 'AdaptiveCard',
                            'body' => [
                                [
                                    'type' => 'TextBlock',
                                    'text' => '✅ Idea Approved',
                                    'weight' => 'Bolder',
                                    'size' => 'Large',
                                    'color' => 'Good',
                                ],
                                [
                                    'type' => 'FactSet',
                                    'facts' => [
                                        [
                                            'title' => 'Title:',
                                            'value' => $idea->title,
                                        ],
                                        [
                                            'title' => 'Status:',
                                            'value' => ucfirst($idea->status),
                                        ],
                                        [
                                            'title' => 'Category:',
                                            'value' => $idea->category->name,
                                        ],
                                        [
                                            'title' => 'Approved At:',
                                            'value' => $idea->approved_at->format('Y-m-d H:i:s'),
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'TextBlock',
                                    'text' => 'Description',
                                    'weight' => 'Bolder',
                                    'spacing' => 'Medium',
                                ],
                                [
                                    'type' => 'TextBlock',
                                    'text' => $idea->description,
                                    'wrap' => true,
                                    'spacing' => 'Small',
                                ],
                            ],
                            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                            'version' => '1.2',
                        ],
                    ],
                ],
            ];

            $this->client->post($webhookUrl, [
                'json' => $card,
            ]);

            $this->logSuccess($integration, 'idea_approved', [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
            ]);
        } catch (\Exception $e) {
            $this->logError($integration, 'idea_approved', $e->getMessage(), [
                'idea_id' => $idea->id,
            ]);
            Log::error('Failed to handle Teams idea approved event: '.$e->getMessage());
        }
    }

    /**
     * Build a simple Adaptive Card.
     */
    protected function buildSimpleCard(string $message, array $data = []): array
    {
        $body = [
            [
                'type' => 'TextBlock',
                'text' => $message,
                'weight' => 'Bolder',
                'size' => 'Medium',
                'wrap' => true,
            ],
        ];

        if (! empty($data)) {
            $facts = [];
            foreach ($data as $key => $value) {
                $facts[] = [
                    'title' => ucfirst(str_replace('_', ' ', $key)).':',
                    'value' => (string) $value,
                ];
            }

            $body[] = [
                'type' => 'FactSet',
                'facts' => $facts,
            ];
        }

        return [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'content' => [
                        'type' => 'AdaptiveCard',
                        'body' => $body,
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'version' => '1.2',
                    ],
                ],
            ],
        ];
    }

    /**
     * Log a successful integration action.
     */
    protected function logSuccess(Integration $integration, string $action, array $payload = []): void
    {
        IntegrationLog::create([
            'integration_id' => $integration->id,
            'tenant_id' => $integration->tenant_id,
            'action' => $action,
            'status' => 'success',
            'payload' => $payload,
            'error_message' => null,
        ]);
    }

    /**
     * Log a failed integration action.
     */
    protected function logError(Integration $integration, string $action, string $errorMessage, array $payload = []): void
    {
        IntegrationLog::create([
            'integration_id' => $integration->id,
            'tenant_id' => $integration->tenant_id,
            'action' => $action,
            'status' => 'failed',
            'payload' => $payload,
            'error_message' => $errorMessage,
        ]);
    }
}
