<?php

namespace App\Services\Integrations;

use App\Models\Idea;
use App\Models\Integration;
use App\Models\IntegrationLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SlackService
{
    /**
     * Create a new SlackService instance.
     */
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Test Slack webhook/API connection.
     */
    public function testConnection(array $config): bool
    {
        try {
            $webhookUrl = $config['webhook_url'] ?? null;

            if (! $webhookUrl) {
                return false;
            }

            $response = $this->client->post($webhookUrl, [
                'json' => [
                    'text' => 'IdeaHub connection test - this is a test message.',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error('Slack connection test failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send a notification to Slack.
     */
    public function sendNotification(array $config, string $message, array $data = []): void
    {
        try {
            $webhookUrl = $config['webhook_url'] ?? null;

            if (! $webhookUrl) {
                throw new \Exception('Slack webhook URL not configured');
            }

            $payload = [
                'text' => $message,
                'blocks' => $this->formatMessageBlocks($message, $data),
            ];

            $this->client->post($webhookUrl, [
                'json' => $payload,
            ]);
        } catch (GuzzleException $e) {
            Log::error('Failed to send Slack notification: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a Slack channel.
     */
    public function createChannel(array $config, string $name): array
    {
        try {
            $token = $config['bot_token'] ?? null;

            if (! $token) {
                throw new \Exception('Slack bot token not configured');
            }

            $response = $this->client->post('https://slack.com/api/conversations.create', [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $name,
                    'is_private' => $config['private_channels'] ?? false,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (! $result['ok']) {
                throw new \Exception($result['error'] ?? 'Unknown error creating channel');
            }

            return $result['channel'];
        } catch (GuzzleException $e) {
            Log::error('Failed to create Slack channel: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Post a message to a Slack channel.
     */
    public function postMessage(array $config, string $channel, string $text, array $attachments = []): void
    {
        try {
            $token = $config['bot_token'] ?? null;

            if (! $token) {
                throw new \Exception('Slack bot token not configured');
            }

            $payload = [
                'channel' => $channel,
                'text' => $text,
            ];

            if (! empty($attachments)) {
                $payload['attachments'] = $attachments;
            }

            $response = $this->client->post('https://slack.com/api/chat.postMessage', [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (! $result['ok']) {
                throw new \Exception($result['error'] ?? 'Unknown error posting message');
            }
        } catch (GuzzleException $e) {
            Log::error('Failed to post message to Slack: '.$e->getMessage());
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
                throw new \Exception('Slack webhook URL not configured');
            }

            $message = '🎯 New Idea Submitted';
            $blocks = [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🎯 New Idea Submitted',
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Title:*\n{$idea->title}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Status:*\n{$idea->status}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Category:*\n{$idea->category->name}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Submitted By:*\n".($idea->is_anonymous ? 'Anonymous' : $idea->user->name),
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Description:*\n{$idea->description}",
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "Submitted at: {$idea->submitted_at->format('Y-m-d H:i:s')}",
                        ],
                    ],
                ],
            ];

            $this->client->post($webhookUrl, [
                'json' => [
                    'text' => $message,
                    'blocks' => $blocks,
                ],
            ]);

            $this->logSuccess($integration, 'idea_created', [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
            ]);
        } catch (\Exception $e) {
            $this->logError($integration, 'idea_created', $e->getMessage(), [
                'idea_id' => $idea->id,
            ]);
            Log::error('Failed to handle Slack idea created event: '.$e->getMessage());
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
                throw new \Exception('Slack webhook URL not configured');
            }

            $message = '✅ Idea Approved';
            $blocks = [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '✅ Idea Approved',
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Title:*\n{$idea->title}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Status:*\n{$idea->status}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Category:*\n{$idea->category->name}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Approved At:*\n{$idea->approved_at->format('Y-m-d H:i:s')}",
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Description:*\n{$idea->description}",
                    ],
                ],
            ];

            $this->client->post($webhookUrl, [
                'json' => [
                    'text' => $message,
                    'blocks' => $blocks,
                ],
            ]);

            $this->logSuccess($integration, 'idea_approved', [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
            ]);
        } catch (\Exception $e) {
            $this->logError($integration, 'idea_approved', $e->getMessage(), [
                'idea_id' => $idea->id,
            ]);
            Log::error('Failed to handle Slack idea approved event: '.$e->getMessage());
        }
    }

    /**
     * Format message blocks for Slack.
     */
    protected function formatMessageBlocks(string $message, array $data): array
    {
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => $message,
                ],
            ],
        ];

        if (! empty($data)) {
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = [
                    'type' => 'mrkdwn',
                    'text' => '*'.ucfirst(str_replace('_', ' ', $key)).":*\n{$value}",
                ];
            }

            if (! empty($fields)) {
                $blocks[] = [
                    'type' => 'section',
                    'fields' => $fields,
                ];
            }
        }

        return $blocks;
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
