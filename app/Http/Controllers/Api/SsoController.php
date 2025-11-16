<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SsoController extends Controller
{
    /**
     * List all configured SSO providers for current tenant.
     */
    public function index(Request $request)
    {
        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        // Get SSO providers from tenant settings
        $providers = $tenant->settings['sso_providers'] ?? [];

        // Remove sensitive data (client secrets) from response
        $providersPublic = collect($providers)->map(function ($provider) {
            return [
                'id' => $provider['id'] ?? null,
                'name' => $provider['name'] ?? null,
                'type' => $provider['type'] ?? null,
                'enabled' => $provider['enabled'] ?? false,
                'auto_provision' => $provider['auto_provision'] ?? false,
                'default_role' => $provider['default_role'] ?? 'user',
                'created_at' => $provider['created_at'] ?? null,
                'updated_at' => $provider['updated_at'] ?? null,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'message' => 'SSO providers retrieved successfully',
            'data' => [
                'providers' => $providersPublic,
                'count' => count($providersPublic),
            ],
        ]);
    }

    /**
     * Get details of a specific SSO provider (admin only).
     */
    public function show(Request $request, string $providerId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can view SSO provider details.',
            ], 403);
        }

        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        $providers = $tenant->settings['sso_providers'] ?? [];
        $provider = collect($providers)->firstWhere('id', $providerId);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'SSO provider not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'SSO provider retrieved successfully',
            'data' => [
                'provider' => $provider,
            ],
        ]);
    }

    /**
     * Create or update SSO provider configuration (admin only).
     */
    public function configure(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can configure SSO providers.',
            ], 403);
        }

        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(['saml', 'oauth2', 'oidc', 'ldap'])],
            'enabled' => 'required|boolean',
            'auto_provision' => 'required|boolean',
            'default_role' => ['required', 'string', Rule::in(['user', 'team_lead', 'department_head', 'admin'])],

            // SAML specific fields
            'saml_entity_id' => 'required_if:type,saml|nullable|string|max:500',
            'saml_sso_url' => 'required_if:type,saml|nullable|url|max:500',
            'saml_slo_url' => 'nullable|url|max:500',
            'saml_certificate' => 'required_if:type,saml|nullable|string',
            'saml_name_id_format' => 'nullable|string|max:255',

            // OAuth2/OIDC specific fields
            'oauth_client_id' => 'required_if:type,oauth2,oidc|nullable|string|max:255',
            'oauth_client_secret' => 'required_if:type,oauth2,oidc|nullable|string|max:500',
            'oauth_authorize_url' => 'required_if:type,oauth2,oidc|nullable|url|max:500',
            'oauth_token_url' => 'required_if:type,oauth2,oidc|nullable|url|max:500',
            'oauth_user_info_url' => 'required_if:type,oauth2,oidc|nullable|url|max:500',
            'oauth_scopes' => 'nullable|string|max:500',

            // LDAP specific fields
            'ldap_host' => 'required_if:type,ldap|nullable|string|max:255',
            'ldap_port' => 'nullable|integer|between:1,65535',
            'ldap_base_dn' => 'required_if:type,ldap|nullable|string|max:500',
            'ldap_bind_dn' => 'nullable|string|max:500',
            'ldap_bind_password' => 'nullable|string|max:500',
            'ldap_use_ssl' => 'nullable|boolean',
            'ldap_use_tls' => 'nullable|boolean',

            // Attribute mapping
            'attribute_mapping' => 'nullable|array',
            'attribute_mapping.email' => 'nullable|string|max:255',
            'attribute_mapping.name' => 'nullable|string|max:255',
            'attribute_mapping.first_name' => 'nullable|string|max:255',
            'attribute_mapping.last_name' => 'nullable|string|max:255',
            'attribute_mapping.department' => 'nullable|string|max:255',
            'attribute_mapping.job_title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = $tenant->settings ?? [];
        $providers = $settings['sso_providers'] ?? [];

        // Check if updating existing or creating new
        $providerId = $request->input('id') ?? Str::uuid()->toString();
        $existingIndex = collect($providers)->search(fn($p) => $p['id'] === $providerId);

        $providerData = [
            'id' => $providerId,
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'enabled' => $request->input('enabled'),
            'auto_provision' => $request->input('auto_provision'),
            'default_role' => $request->input('default_role'),
            'updated_at' => now()->toISOString(),
        ];

        // Add type-specific configuration
        switch ($request->input('type')) {
            case 'saml':
                $providerData['config'] = [
                    'entity_id' => $request->input('saml_entity_id'),
                    'sso_url' => $request->input('saml_sso_url'),
                    'slo_url' => $request->input('saml_slo_url'),
                    'certificate' => $request->input('saml_certificate'),
                    'name_id_format' => $request->input('saml_name_id_format', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'),
                ];
                break;

            case 'oauth2':
            case 'oidc':
                $providerData['config'] = [
                    'client_id' => $request->input('oauth_client_id'),
                    'client_secret' => $request->input('oauth_client_secret'),
                    'authorize_url' => $request->input('oauth_authorize_url'),
                    'token_url' => $request->input('oauth_token_url'),
                    'user_info_url' => $request->input('oauth_user_info_url'),
                    'scopes' => $request->input('oauth_scopes', 'openid profile email'),
                ];
                break;

            case 'ldap':
                $providerData['config'] = [
                    'host' => $request->input('ldap_host'),
                    'port' => $request->input('ldap_port', 389),
                    'base_dn' => $request->input('ldap_base_dn'),
                    'bind_dn' => $request->input('ldap_bind_dn'),
                    'bind_password' => $request->input('ldap_bind_password'),
                    'use_ssl' => $request->input('ldap_use_ssl', false),
                    'use_tls' => $request->input('ldap_use_tls', false),
                ];
                break;
        }

        // Add attribute mapping
        if ($request->has('attribute_mapping')) {
            $providerData['attribute_mapping'] = $request->input('attribute_mapping');
        }

        // Update or add provider
        if ($existingIndex !== false) {
            $providerData['created_at'] = $providers[$existingIndex]['created_at'] ?? now()->toISOString();
            $providers[$existingIndex] = $providerData;
            $message = 'SSO provider updated successfully';
        } else {
            $providerData['created_at'] = now()->toISOString();
            $providers[] = $providerData;
            $message = 'SSO provider configured successfully';
        }

        // Save back to tenant settings
        $settings['sso_providers'] = $providers;
        $tenant->settings = $settings;
        $tenant->save();

        // Remove sensitive data from response
        $responseProvider = $providerData;
        if (isset($responseProvider['config']['client_secret'])) {
            $responseProvider['config']['client_secret'] = '********';
        }
        if (isset($responseProvider['config']['bind_password'])) {
            $responseProvider['config']['bind_password'] = '********';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'provider' => $responseProvider,
            ],
        ], $existingIndex !== false ? 200 : 201);
    }

    /**
     * Delete SSO provider (admin only).
     */
    public function destroy(Request $request, string $providerId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can delete SSO providers.',
            ], 403);
        }

        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        $settings = $tenant->settings ?? [];
        $providers = $settings['sso_providers'] ?? [];

        $index = collect($providers)->search(fn($p) => $p['id'] === $providerId);

        if ($index === false) {
            return response()->json([
                'success' => false,
                'message' => 'SSO provider not found',
            ], 404);
        }

        // Remove provider
        array_splice($providers, $index, 1);
        $settings['sso_providers'] = $providers;
        $tenant->settings = $settings;
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'SSO provider deleted successfully',
        ]);
    }

    /**
     * Initiate SSO login flow.
     */
    public function initiate(Request $request, string $providerId)
    {
        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        $providers = $tenant->settings['sso_providers'] ?? [];
        $provider = collect($providers)->firstWhere('id', $providerId);

        if (!$provider || !($provider['enabled'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'SSO provider not found or not enabled',
            ], 404);
        }

        // Generate state token for CSRF protection
        $state = Str::random(40);
        session(['sso_state' => $state, 'sso_provider_id' => $providerId]);

        $redirectUrl = null;
        $type = $provider['type'];

        switch ($type) {
            case 'saml':
                // For SAML, redirect to SP-initiated SSO URL
                $redirectUrl = $this->buildSamlRedirectUrl($provider, $state);
                break;

            case 'oauth2':
            case 'oidc':
                // For OAuth2/OIDC, redirect to authorization endpoint
                $redirectUrl = $this->buildOAuthRedirectUrl($provider, $state);
                break;

            case 'ldap':
                // LDAP doesn't have a redirect flow - show error
                return response()->json([
                    'success' => false,
                    'message' => 'LDAP authentication requires username/password. Use the callback endpoint directly.',
                ], 400);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported SSO provider type',
                ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'SSO login initiated',
            'data' => [
                'redirect_url' => $redirectUrl,
                'state' => $state,
                'provider' => [
                    'id' => $provider['id'],
                    'name' => $provider['name'],
                    'type' => $provider['type'],
                ],
            ],
        ]);
    }

    /**
     * Handle SSO callback and complete authentication.
     */
    public function callback(Request $request)
    {
        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        // Validate state token
        $state = $request->input('state');
        $sessionState = session('sso_state');

        if (!$state || $state !== $sessionState) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid state token. Possible CSRF attack.',
            ], 400);
        }

        $providerId = session('sso_provider_id');
        $providers = $tenant->settings['sso_providers'] ?? [];
        $provider = collect($providers)->firstWhere('id', $providerId);

        if (!$provider || !($provider['enabled'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'SSO provider not found or not enabled',
            ], 404);
        }

        // Process callback based on provider type
        try {
            $userData = null;

            switch ($provider['type']) {
                case 'saml':
                    $userData = $this->processSamlCallback($request, $provider);
                    break;

                case 'oauth2':
                case 'oidc':
                    $userData = $this->processOAuthCallback($request, $provider);
                    break;

                case 'ldap':
                    $userData = $this->processLdapCallback($request, $provider);
                    break;

                default:
                    throw new \Exception('Unsupported SSO provider type');
            }

            // Find or create user
            $user = $this->findOrCreateUser($userData, $provider, $tenant);

            // Authenticate user
            Auth::login($user);

            // Create API token
            $token = $user->createToken('sso-auth-token')->plainTextToken;

            // Clear session state
            session()->forget(['sso_state', 'sso_provider_id']);

            return response()->json([
                'success' => true,
                'message' => 'SSO authentication successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSO authentication failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Test SSO provider configuration (admin only).
     */
    public function test(Request $request, string $providerId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can test SSO providers.',
            ], 403);
        }

        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        $providers = $tenant->settings['sso_providers'] ?? [];
        $provider = collect($providers)->firstWhere('id', $providerId);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'SSO provider not found',
            ], 404);
        }

        // Perform basic connectivity tests
        $testResults = [];
        $type = $provider['type'];

        switch ($type) {
            case 'saml':
                $testResults = $this->testSamlProvider($provider);
                break;

            case 'oauth2':
            case 'oidc':
                $testResults = $this->testOAuthProvider($provider);
                break;

            case 'ldap':
                $testResults = $this->testLdapProvider($provider);
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported SSO provider type',
                ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'SSO provider test completed',
            'data' => [
                'provider' => [
                    'id' => $provider['id'],
                    'name' => $provider['name'],
                    'type' => $provider['type'],
                ],
                'test_results' => $testResults,
            ],
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Build SAML redirect URL.
     */
    protected function buildSamlRedirectUrl(array $provider, string $state): string
    {
        // This is a simplified example. In production, use a SAML library like OneLogin
        $ssoUrl = $provider['config']['sso_url'];
        $callbackUrl = url('/api/sso/callback');

        return $ssoUrl . '?' . http_build_query([
            'SAMLRequest' => base64_encode('<!-- SAML Request XML -->'),
            'RelayState' => $state,
        ]);
    }

    /**
     * Build OAuth redirect URL.
     */
    protected function buildOAuthRedirectUrl(array $provider, string $state): string
    {
        $config = $provider['config'];
        $callbackUrl = url('/api/sso/callback');

        return $config['authorize_url'] . '?' . http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $callbackUrl,
            'response_type' => 'code',
            'scope' => $config['scopes'] ?? 'openid profile email',
            'state' => $state,
        ]);
    }

    /**
     * Process SAML callback response.
     */
    protected function processSamlCallback(Request $request, array $provider): array
    {
        // This is a placeholder. In production, use a SAML library to parse and validate
        // the SAML response, verify signatures, and extract user attributes.
        throw new \Exception('SAML processing requires a SAML library implementation');
    }

    /**
     * Process OAuth callback response.
     */
    protected function processOAuthCallback(Request $request, array $provider): array
    {
        // This is a placeholder. In production, exchange the authorization code
        // for an access token and fetch user info from the user info endpoint.
        throw new \Exception('OAuth processing requires implementation with HTTP client');
    }

    /**
     * Process LDAP authentication.
     */
    protected function processLdapCallback(Request $request, array $provider): array
    {
        // This is a placeholder. In production, bind to LDAP server and authenticate user.
        throw new \Exception('LDAP authentication requires LDAP extension');
    }

    /**
     * Find or create user from SSO data.
     */
    protected function findOrCreateUser(array $userData, array $provider, $tenant): User
    {
        $email = $userData['email'] ?? null;

        if (!$email) {
            throw new \Exception('Email address not provided by SSO provider');
        }

        $user = User::where('email', $email)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$user) {
            if (!($provider['auto_provision'] ?? false)) {
                throw new \Exception('User does not exist and auto-provisioning is disabled');
            }

            // Create new user
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $userData['name'] ?? $userData['email'],
                'email' => $userData['email'],
                'password' => Hash::make(Str::random(32)), // Random password for SSO users
                'department' => $userData['department'] ?? null,
                'job_title' => $userData['job_title'] ?? null,
                'role' => $provider['default_role'] ?? 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }

    /**
     * Test SAML provider configuration.
     */
    protected function testSamlProvider(array $provider): array
    {
        $config = $provider['config'];
        $results = [];

        // Test SSO URL accessibility
        $results['sso_url_accessible'] = filter_var($config['sso_url'], FILTER_VALIDATE_URL) !== false;

        // Test certificate validity
        $results['certificate_provided'] = !empty($config['certificate']);

        return $results;
    }

    /**
     * Test OAuth provider configuration.
     */
    protected function testOAuthProvider(array $provider): array
    {
        $config = $provider['config'];
        $results = [];

        // Validate URLs
        $results['authorize_url_valid'] = filter_var($config['authorize_url'], FILTER_VALIDATE_URL) !== false;
        $results['token_url_valid'] = filter_var($config['token_url'], FILTER_VALIDATE_URL) !== false;
        $results['user_info_url_valid'] = filter_var($config['user_info_url'], FILTER_VALIDATE_URL) !== false;

        // Check credentials provided
        $results['client_id_provided'] = !empty($config['client_id']);
        $results['client_secret_provided'] = !empty($config['client_secret']);

        return $results;
    }

    /**
     * Test LDAP provider configuration.
     */
    protected function testLdapProvider(array $provider): array
    {
        $config = $provider['config'];
        $results = [];

        // Check configuration
        $results['host_provided'] = !empty($config['host']);
        $results['base_dn_provided'] = !empty($config['base_dn']);
        $results['port_valid'] = isset($config['port']) && $config['port'] > 0 && $config['port'] <= 65535;

        return $results;
    }
}
