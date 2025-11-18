<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantBranding;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BrandingController extends Controller
{
    /**
     * Get current tenant branding configuration.
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

        $branding = $tenant->branding;

        if (!$branding) {
            // Return default branding if none exists
            return response()->json([
                'success' => true,
                'message' => 'No custom branding configured',
                'data' => [
                    'branding' => null,
                    'css_variables' => [],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Branding retrieved successfully',
            'data' => [
                'branding' => $branding,
                'css_variables' => $branding->getCssVariables(),
                'inline_css' => $branding->getInlineCssStyle(),
            ],
        ]);
    }

    /**
     * Update tenant branding configuration (admin only).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can update branding.',
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
            'logo_url' => 'nullable|string|max:500',
            'logo_dark_url' => 'nullable|string|max:500',
            'favicon_url' => 'nullable|string|max:500',
            'login_background_url' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'secondary_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'accent_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'success_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'warning_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'error_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'text_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'background_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'surface_color' => 'nullable|string|max:20|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'font_family' => 'nullable|string|max:255',
            'heading_font_family' => 'nullable|string|max:255',
            'app_name' => 'nullable|string|max:255',
            'app_tagline' => 'nullable|string|max:500',
            'support_email' => 'nullable|email|max:255',
            'support_url' => 'nullable|url|max:500',
            'custom_css' => 'nullable|string|max:10000',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required_with:social_links|string|in:facebook,twitter,linkedin,instagram,youtube,github',
            'social_links.*.url' => 'required_with:social_links|url',
            'footer_text' => 'nullable|string|max:500',
            'show_powered_by' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get or create branding
        $branding = $tenant->branding;

        if (!$branding) {
            $branding = new TenantBranding(['tenant_id' => $tenant->id]);
        }

        // Update branding fields
        $branding->fill($request->only([
            'logo_url',
            'logo_dark_url',
            'favicon_url',
            'login_background_url',
            'primary_color',
            'secondary_color',
            'accent_color',
            'success_color',
            'warning_color',
            'error_color',
            'text_color',
            'background_color',
            'surface_color',
            'font_family',
            'heading_font_family',
            'app_name',
            'app_tagline',
            'support_email',
            'support_url',
            'custom_css',
            'social_links',
            'footer_text',
            'show_powered_by',
        ]));

        $branding->save();

        return response()->json([
            'success' => true,
            'message' => 'Branding updated successfully',
            'data' => [
                'branding' => $branding->fresh(),
                'css_variables' => $branding->getCssVariables(),
            ],
        ]);
    }

    /**
     * Upload logo file (admin only).
     */
    public function uploadLogo(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can upload logos.',
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
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => ['required', 'string', Rule::in(['logo', 'logo_dark', 'favicon', 'login_background'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->input('type');
        $file = $request->file('logo');

        // Create directory path for tenant branding
        $directory = "branding/tenant_{$tenant->id}";

        // Delete old file if exists
        $branding = $tenant->branding;
        if ($branding) {
            $fieldName = $type === 'logo' ? 'logo_url' :
                        ($type === 'logo_dark' ? 'logo_dark_url' :
                        ($type === 'favicon' ? 'favicon_url' : 'login_background_url'));

            if ($branding->$fieldName) {
                $oldPath = str_replace('/storage/', '', $branding->$fieldName);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        // Store the file
        $filename = $type . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        // Generate URL
        $url = Storage::url($path);

        // Get or create branding
        if (!$branding) {
            $branding = new TenantBranding(['tenant_id' => $tenant->id]);
        }

        // Update the appropriate field
        switch ($type) {
            case 'logo':
                $branding->logo_url = $url;
                break;
            case 'logo_dark':
                $branding->logo_dark_url = $url;
                break;
            case 'favicon':
                $branding->favicon_url = $url;
                break;
            case 'login_background':
                $branding->login_background_url = $url;
                break;
        }

        $branding->save();

        return response()->json([
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $type)) . ' uploaded successfully',
            'data' => [
                'url' => $url,
                'type' => $type,
                'branding' => $branding->fresh(),
            ],
        ]);
    }

    /**
     * Delete logo file (admin only).
     */
    public function deleteLogo(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can delete logos.',
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
            'type' => ['required', 'string', Rule::in(['logo', 'logo_dark', 'favicon', 'login_background'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $branding = $tenant->branding;

        if (!$branding) {
            return response()->json([
                'success' => false,
                'message' => 'No branding configuration found',
            ], 404);
        }

        $type = $request->input('type');
        $fieldName = $type === 'logo' ? 'logo_url' :
                    ($type === 'logo_dark' ? 'logo_dark_url' :
                    ($type === 'favicon' ? 'favicon_url' : 'login_background_url'));

        if ($branding->$fieldName) {
            $oldPath = str_replace('/storage/', '', $branding->$fieldName);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $branding->$fieldName = null;
            $branding->save();
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $type)) . ' deleted successfully',
            'data' => [
                'branding' => $branding->fresh(),
            ],
        ]);
    }

    /**
     * Reset branding to defaults (admin only).
     */
    public function reset(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can reset branding.',
            ], 403);
        }

        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found',
            ], 404);
        }

        $branding = $tenant->branding;

        if (!$branding) {
            return response()->json([
                'success' => false,
                'message' => 'No branding configuration to reset',
            ], 404);
        }

        // Delete all uploaded files
        $directory = "branding/tenant_{$tenant->id}";
        if (Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->deleteDirectory($directory);
        }

        // Delete the branding record
        $branding->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branding reset to defaults successfully',
        ]);
    }
}
