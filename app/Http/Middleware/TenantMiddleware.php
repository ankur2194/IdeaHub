<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->identifyTenant($request);

        if ($tenant) {
            // Store tenant in app container and session
            app()->instance('current_tenant_id', $tenant->id);
            app()->instance('current_tenant', $tenant);
            session(['tenant_id' => $tenant->id]);

            // Check if tenant is active
            if (!$tenant->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is currently inactive. Please contact support.',
                ], 403);
            }

            // Check if tenant has expired
            if ($tenant->hasExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your trial has expired. Please subscribe to continue using IdeaHub.',
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Identify tenant from the request
     */
    protected function identifyTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();

        // Try to identify by custom domain first
        $tenant = Tenant::where('domain', $host)->where('is_active', true)->first();

        if ($tenant) {
            return $tenant;
        }

        // Try to identify by subdomain
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain && $subdomain !== 'www') {
            $tenant = Tenant::where('subdomain', $subdomain)->where('is_active', true)->first();

            if ($tenant) {
                return $tenant;
            }
        }

        // For API requests, tenant should be identified by user's tenant assignment
        // Check if user is authenticated and use their tenant
        if ($request->user()) {
            return $request->user()->tenant;
        }

        return null;
    }

    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        // If we have at least 3 parts (e.g., subdomain.example.com)
        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }
}
