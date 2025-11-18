<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBranding extends Model
{
    /** @use HasFactory<\Database\Factories\TenantBrandingFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
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
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'show_powered_by' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the branding
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the CSS variables for the branding
     */
    public function getCssVariables(): array
    {
        return [
            '--color-primary' => $this->primary_color,
            '--color-secondary' => $this->secondary_color,
            '--color-accent' => $this->accent_color,
            '--color-success' => $this->success_color,
            '--color-warning' => $this->warning_color,
            '--color-error' => $this->error_color,
            '--color-text' => $this->text_color,
            '--color-background' => $this->background_color,
            '--color-surface' => $this->surface_color,
            '--font-family' => $this->font_family,
            '--font-family-heading' => $this->heading_font_family ?? $this->font_family,
        ];
    }

    /**
     * Get the inline CSS style string
     */
    public function getInlineCssStyle(): string
    {
        $variables = $this->getCssVariables();
        $cssString = ':root {';

        foreach ($variables as $key => $value) {
            $cssString .= "{$key}: {$value}; ";
        }

        $cssString .= '}';

        if ($this->custom_css) {
            $cssString .= "\n".$this->custom_css;
        }

        return $cssString;
    }
}
