<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationLog;
use App\Services\Integrations\SlackService;
use App\Services\Integrations\TeamsService;
use App\Services\Integrations\JiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IntegrationController extends Controller
{
    /**
     * Display a listing of integrations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Integration::with(['logs' => function ($query) {
            $query->latest()->limit(5);
        }]);

        // Filter by type if provided
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $integrations = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $integrations,
            'message' => 'Integrations retrieved successfully',
        ]);
    }

    /**
     * Store a newly created integration.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:slack,teams,jira,github,gitlab,trello,asana,zapier,webhook',
            'config' => 'required|array',
            'config.api_key' => 'sometimes|string',
            'config.webhook_url' => 'sometimes|url',
            'config.api_url' => 'sometimes|url',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $integration = Integration::create($validator->validated());

        // Log the integration creation
        IntegrationLog::create([
            'integration_id' => $integration->id,
            'action' => 'integration_created',
            'status' => 'success',
            'payload' => [
                'type' => $integration->type,
                'name' => $integration->name,
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => $integration->load('logs'),
            'message' => 'Integration created successfully',
        ], 201);
    }

    /**
     * Display the specified integration.
     */
    public function show(Integration $integration): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $integration->load(['logs' => function ($query) {
                $query->latest()->limit(20);
            }]),
            'message' => 'Integration retrieved successfully',
        ]);
    }

    /**
     * Update the specified integration.
     */
    public function update(Request $request, Integration $integration): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => [
                'sometimes',
                'string',
                Rule::in(['slack', 'teams', 'jira', 'github', 'gitlab', 'trello', 'asana', 'zapier', 'webhook']),
            ],
            'config' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $integration->update($validator->validated());

        // Log the integration update
        IntegrationLog::create([
            'integration_id' => $integration->id,
            'action' => 'integration_updated',
            'status' => 'success',
            'payload' => $validator->validated(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $integration->fresh()->load('logs'),
            'message' => 'Integration updated successfully',
        ]);
    }

    /**
     * Remove the specified integration.
     */
    public function destroy(Integration $integration): JsonResponse
    {
        $integrationName = $integration->name;

        $integration->delete();

        return response()->json([
            'success' => true,
            'message' => "Integration '{$integrationName}' deleted successfully",
        ]);
    }

    /**
     * Test the integration connection.
     */
    public function test(Integration $integration): JsonResponse
    {
        if (! $integration->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Integration is not properly configured',
                'errors' => ['config' => 'Missing required configuration'],
            ], 400);
        }

        try {
            // Here you would implement actual connection testing based on integration type
            // For now, we'll simulate a test
            $testResult = $this->performConnectionTest($integration);

            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'connection_test',
                'status' => $testResult['success'] ? 'success' : 'failed',
                'payload' => $testResult,
                'error_message' => $testResult['error'] ?? null,
            ]);

            return response()->json([
                'success' => $testResult['success'],
                'message' => $testResult['message'],
                'data' => $testResult,
            ], $testResult['success'] ? 200 : 400);

        } catch (\Exception $e) {
            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'connection_test',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'errors' => ['connection' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Trigger a manual sync for the integration.
     */
    public function sync(Integration $integration): JsonResponse
    {
        if (! $integration->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Integration is not properly configured',
                'errors' => ['config' => 'Missing required configuration'],
            ], 400);
        }

        try {
            // Here you would implement actual sync logic based on integration type
            // For now, we'll simulate a sync
            $syncResult = $this->performSync($integration);

            $integration->markAsSynced();

            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'manual_sync',
                'status' => $syncResult['success'] ? 'success' : 'failed',
                'payload' => $syncResult,
                'error_message' => $syncResult['error'] ?? null,
            ]);

            return response()->json([
                'success' => $syncResult['success'],
                'message' => $syncResult['message'],
                'data' => $syncResult,
            ], $syncResult['success'] ? 200 : 400);

        } catch (\Exception $e) {
            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'manual_sync',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed',
                'errors' => ['sync' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Perform connection test based on integration type.
     */
    private function performConnectionTest(Integration $integration): array
    {
        try {
            $success = false;
            $message = '';

            switch ($integration->type) {
                case 'slack':
                    $slackService = app(SlackService::class);
                    $success = $slackService->testConnection($integration->config);
                    $message = $success ? 'Slack connection successful' : 'Slack connection failed';
                    break;

                case 'teams':
                    $teamsService = app(TeamsService::class);
                    $success = $teamsService->testConnection($integration->config);
                    $message = $success ? 'Microsoft Teams connection successful' : 'Teams connection failed';
                    break;

                case 'jira':
                    $jiraService = app(JiraService::class);
                    $success = $jiraService->testConnection($integration->config);
                    $message = $success ? 'JIRA connection successful' : 'JIRA connection failed';
                    break;

                default:
                    return [
                        'success' => false,
                        'message' => "Integration type '{$integration->type}' not supported for testing",
                        'tested_at' => now()->toISOString(),
                    ];
            }

            return [
                'success' => $success,
                'message' => $message,
                'tested_at' => now()->toISOString(),
                'integration_type' => $integration->type,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: '.$e->getMessage(),
                'tested_at' => now()->toISOString(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Perform sync based on integration type.
     */
    private function performSync(Integration $integration): array
    {
        try {
            $itemsSynced = 0;
            $details = [];

            switch ($integration->type) {
                case 'slack':
                    $slackService = app(SlackService::class);
                    // Sync recent ideas to Slack (last 24 hours)
                    $recentIdeas = \App\Models\Idea::where('created_at', '>=', now()->subDay())
                        ->where('status', 'submitted')
                        ->get();

                    foreach ($recentIdeas as $idea) {
                        try {
                            $slackService->sendNotification(
                                $integration->config,
                                "New Idea: {$idea->title}",
                                ['idea' => $idea->toArray()]
                            );
                            $itemsSynced++;
                        } catch (\Exception $e) {
                            $details['errors'][] = "Failed to sync idea {$idea->id}: {$e->getMessage()}";
                        }
                    }

                    return [
                        'success' => true,
                        'message' => "Synced {$itemsSynced} ideas to Slack",
                        'synced_at' => now()->toISOString(),
                        'items_synced' => $itemsSynced,
                        'details' => $details,
                    ];

                case 'teams':
                    $teamsService = app(TeamsService::class);
                    // Sync recent ideas to Teams (last 24 hours)
                    $recentIdeas = \App\Models\Idea::where('created_at', '>=', now()->subDay())
                        ->where('status', 'submitted')
                        ->get();

                    foreach ($recentIdeas as $idea) {
                        try {
                            $teamsService->sendNotification(
                                $integration->config,
                                "New Idea: {$idea->title}",
                                ['idea' => $idea->toArray()]
                            );
                            $itemsSynced++;
                        } catch (\Exception $e) {
                            $details['errors'][] = "Failed to sync idea {$idea->id}: {$e->getMessage()}";
                        }
                    }

                    return [
                        'success' => true,
                        'message' => "Synced {$itemsSynced} ideas to Microsoft Teams",
                        'synced_at' => now()->toISOString(),
                        'items_synced' => $itemsSynced,
                        'details' => $details,
                    ];

                case 'jira':
                    $jiraService = app(JiraService::class);
                    // Sync approved ideas that don't have a JIRA key yet
                    $ideasToSync = \App\Models\Idea::where('status', 'approved')
                        ->whereNull('jira_issue_key')
                        ->get();

                    foreach ($ideasToSync as $idea) {
                        try {
                            $issueKey = $jiraService->syncIdeaToJira($integration, $idea);
                            $idea->jira_issue_key = $issueKey;
                            $idea->save();
                            $itemsSynced++;
                            $details['synced_issues'][] = $issueKey;
                        } catch (\Exception $e) {
                            $details['errors'][] = "Failed to sync idea {$idea->id}: {$e->getMessage()}";
                        }
                    }

                    return [
                        'success' => true,
                        'message' => "Synced {$itemsSynced} ideas to JIRA",
                        'synced_at' => now()->toISOString(),
                        'items_synced' => $itemsSynced,
                        'details' => $details,
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => "Integration type '{$integration->type}' not supported for sync",
                        'synced_at' => now()->toISOString(),
                        'items_synced' => 0,
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Sync failed: '.$e->getMessage(),
                'synced_at' => now()->toISOString(),
                'items_synced' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle JIRA webhook for bidirectional sync.
     */
    public function jiraWebhook(Request $request): JsonResponse
    {
        try {
            // Validate webhook signature if configured
            $webhookSecret = config('integrations.jira_webhook_secret');
            if ($webhookSecret) {
                $signature = $request->header('X-Hub-Signature');
                if (! $this->validateWebhookSignature($request->getContent(), $signature, $webhookSecret)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid webhook signature',
                    ], 401);
                }
            }

            $payload = $request->all();
            $event = $request->header('X-Event-Key') ?? $payload['webhookEvent'] ?? null;

            if (! $event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing event type',
                ], 400);
            }

            // Find JIRA integration
            $integration = Integration::where('type', 'jira')->where('is_active', true)->first();

            if (! $integration) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active JIRA integration found',
                ], 404);
            }

            // Handle different JIRA events
            switch ($event) {
                case 'jira:issue_updated':
                    $this->handleJiraIssueUpdated($integration, $payload);
                    break;

                case 'jira:issue_deleted':
                    $this->handleJiraIssueDeleted($integration, $payload);
                    break;

                default:
                    // Log unknown event but return success
                    IntegrationLog::create([
                        'integration_id' => $integration->id,
                        'action' => 'webhook_received',
                        'status' => 'success',
                        'payload' => [
                            'event' => $event,
                            'message' => 'Event not handled',
                        ],
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'errors' => ['webhook' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Handle JIRA issue updated event.
     */
    protected function handleJiraIssueUpdated(Integration $integration, array $payload): void
    {
        try {
            $issueKey = $payload['issue']['key'] ?? null;

            if (! $issueKey) {
                return;
            }

            $jiraService = app(\App\Services\Integrations\JiraService::class);
            $jiraService->syncJiraToIdea($integration, $issueKey);

        } catch (\Exception $e) {
            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'webhook_issue_updated',
                'status' => 'failed',
                'payload' => $payload,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle JIRA issue deleted event.
     */
    protected function handleJiraIssueDeleted(Integration $integration, array $payload): void
    {
        try {
            $issueKey = $payload['issue']['key'] ?? null;

            if (! $issueKey) {
                return;
            }

            // Find idea with this JIRA key and clear the reference
            \App\Models\Idea::where('jira_issue_key', $issueKey)->update([
                'jira_issue_key' => null,
            ]);

            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'webhook_issue_deleted',
                'status' => 'success',
                'payload' => [
                    'issue_key' => $issueKey,
                    'message' => 'JIRA issue reference cleared from ideas',
                ],
            ]);

        } catch (\Exception $e) {
            IntegrationLog::create([
                'integration_id' => $integration->id,
                'action' => 'webhook_issue_deleted',
                'status' => 'failed',
                'payload' => $payload,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate webhook signature.
     */
    protected function validateWebhookSignature(string $payload, ?string $signature, string $secret): bool
    {
        if (! $signature) {
            return false;
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
