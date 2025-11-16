<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationLog;
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
        if (!$integration->isConfigured()) {
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
        if (!$integration->isConfigured()) {
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
     * This is a placeholder - implement actual testing logic for each integration type.
     */
    private function performConnectionTest(Integration $integration): array
    {
        // Placeholder implementation
        // In a real application, you would test the actual connection based on type

        switch ($integration->type) {
            case 'slack':
                // Test Slack webhook or API
                return [
                    'success' => true,
                    'message' => 'Slack connection successful',
                    'tested_at' => now()->toISOString(),
                ];

            case 'jira':
                // Test Jira API
                return [
                    'success' => true,
                    'message' => 'Jira connection successful',
                    'tested_at' => now()->toISOString(),
                ];

            default:
                return [
                    'success' => true,
                    'message' => 'Connection test successful',
                    'tested_at' => now()->toISOString(),
                ];
        }
    }

    /**
     * Perform sync based on integration type.
     * This is a placeholder - implement actual sync logic for each integration type.
     */
    private function performSync(Integration $integration): array
    {
        // Placeholder implementation
        // In a real application, you would perform actual data sync based on type

        return [
            'success' => true,
            'message' => 'Sync completed successfully',
            'synced_at' => now()->toISOString(),
            'items_synced' => 0,
        ];
    }
}
