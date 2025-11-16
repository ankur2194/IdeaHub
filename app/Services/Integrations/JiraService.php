<?php

namespace App\Services\Integrations;

use App\Models\Idea;
use App\Models\Integration;
use App\Models\IntegrationLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class JiraService
{
    /**
     * Create a new JiraService instance.
     */
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Test JIRA API connection.
     *
     * @param array $config
     * @return bool
     */
    public function testConnection(array $config): bool
    {
        try {
            $baseUrl = $config['base_url'] ?? null;
            $email = $config['email'] ?? null;
            $apiToken = $config['api_token'] ?? null;

            if (!$baseUrl || !$email || !$apiToken) {
                return false;
            }

            $response = $this->client->get($baseUrl . '/rest/api/3/myself', [
                'auth' => [$email, $apiToken],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error('JIRA connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a JIRA issue.
     *
     * @param array $config
     * @param array $issueData
     * @return array
     */
    public function createIssue(array $config, array $issueData): array
    {
        try {
            $baseUrl = $config['base_url'] ?? null;
            $email = $config['email'] ?? null;
            $apiToken = $config['api_token'] ?? null;

            if (!$baseUrl || !$email || !$apiToken) {
                throw new \Exception('JIRA credentials not configured');
            }

            $response = $this->client->post($baseUrl . '/rest/api/3/issue', [
                'auth' => [$email, $apiToken],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $issueData,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 201) {
                throw new \Exception('Failed to create JIRA issue');
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Failed to create JIRA issue: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a JIRA issue.
     *
     * @param array $config
     * @param string $issueKey
     * @param array $updateData
     * @return void
     */
    public function updateIssue(array $config, string $issueKey, array $updateData): void
    {
        try {
            $baseUrl = $config['base_url'] ?? null;
            $email = $config['email'] ?? null;
            $apiToken = $config['api_token'] ?? null;

            if (!$baseUrl || !$email || !$apiToken) {
                throw new \Exception('JIRA credentials not configured');
            }

            $response = $this->client->put($baseUrl . '/rest/api/3/issue/' . $issueKey, [
                'auth' => [$email, $apiToken],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $updateData,
            ]);

            if ($response->getStatusCode() !== 204) {
                throw new \Exception('Failed to update JIRA issue');
            }
        } catch (GuzzleException $e) {
            Log::error('Failed to update JIRA issue: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a JIRA issue.
     *
     * @param array $config
     * @param string $issueKey
     * @return array
     */
    public function getIssue(array $config, string $issueKey): array
    {
        try {
            $baseUrl = $config['base_url'] ?? null;
            $email = $config['email'] ?? null;
            $apiToken = $config['api_token'] ?? null;

            if (!$baseUrl || !$email || !$apiToken) {
                throw new \Exception('JIRA credentials not configured');
            }

            $response = $this->client->get($baseUrl . '/rest/api/3/issue/' . $issueKey, [
                'auth' => [$email, $apiToken],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to get JIRA issue');
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Failed to get JIRA issue: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync an Idea to JIRA.
     *
     * @param Integration $integration
     * @param Idea $idea
     * @return string Returns JIRA issue key
     */
    public function syncIdeaToJira(Integration $integration, Idea $idea): string
    {
        try {
            $config = $integration->config;
            $projectKey = $config['project_key'] ?? null;
            $issueType = $config['issue_type'] ?? 'Task';

            if (!$projectKey) {
                throw new \Exception('JIRA project key not configured');
            }

            // Build the issue data
            $issueData = [
                'fields' => [
                    'project' => [
                        'key' => $projectKey,
                    ],
                    'summary' => $idea->title,
                    'description' => [
                        'type' => 'doc',
                        'version' => 1,
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => $idea->description,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'issuetype' => [
                        'name' => $issueType,
                    ],
                ],
            ];

            // Add labels if configured
            if (!empty($config['labels'])) {
                $issueData['fields']['labels'] = $config['labels'];
            }

            // Add custom field for idea ID if configured
            if (!empty($config['idea_id_field'])) {
                $issueData['fields'][$config['idea_id_field']] = (string) $idea->id;
            }

            $result = $this->createIssue($config, $issueData);

            $issueKey = $result['key'] ?? null;

            if (!$issueKey) {
                throw new \Exception('Failed to get JIRA issue key from response');
            }

            // Update the integration's last sync time
            $integration->markAsSynced();

            $this->logSuccess($integration, 'sync_idea_to_jira', [
                'idea_id' => $idea->id,
                'idea_title' => $idea->title,
                'jira_issue_key' => $issueKey,
            ]);

            return $issueKey;
        } catch (\Exception $e) {
            $this->logError($integration, 'sync_idea_to_jira', $e->getMessage(), [
                'idea_id' => $idea->id,
            ]);
            Log::error('Failed to sync idea to JIRA: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync a JIRA issue back to an Idea.
     *
     * @param Integration $integration
     * @param string $issueKey
     * @return void
     */
    public function syncJiraToIdea(Integration $integration, string $issueKey): void
    {
        try {
            $config = $integration->config;
            $issue = $this->getIssue($config, $issueKey);

            // Extract the idea ID from custom field if configured
            $ideaIdField = $config['idea_id_field'] ?? null;
            $ideaId = null;

            if ($ideaIdField && isset($issue['fields'][$ideaIdField])) {
                $ideaId = $issue['fields'][$ideaIdField];
            }

            if (!$ideaId) {
                throw new \Exception('Could not find idea ID in JIRA issue');
            }

            // Find the idea
            $idea = Idea::find($ideaId);

            if (!$idea) {
                throw new \Exception("Idea with ID {$ideaId} not found");
            }

            // Update the idea with JIRA status if configured
            $statusMapping = $config['status_mapping'] ?? [];
            $jiraStatus = $issue['fields']['status']['name'] ?? null;

            if ($jiraStatus && isset($statusMapping[$jiraStatus])) {
                $idea->status = $statusMapping[$jiraStatus];
                $idea->save();
            }

            // Update the integration's last sync time
            $integration->markAsSynced();

            $this->logSuccess($integration, 'sync_jira_to_idea', [
                'idea_id' => $idea->id,
                'jira_issue_key' => $issueKey,
                'jira_status' => $jiraStatus,
            ]);
        } catch (\Exception $e) {
            $this->logError($integration, 'sync_jira_to_idea', $e->getMessage(), [
                'jira_issue_key' => $issueKey,
            ]);
            Log::error('Failed to sync JIRA to idea: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log a successful integration action.
     *
     * @param Integration $integration
     * @param string $action
     * @param array $payload
     * @return void
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
     *
     * @param Integration $integration
     * @param string $action
     * @param string $errorMessage
     * @param array $payload
     * @return void
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
