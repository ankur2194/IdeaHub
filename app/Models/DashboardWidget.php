<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'category',
        'config',
        'is_system',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Widget types available.
     */
    public const TYPES = [
        'stats_card' => 'Statistics Card',
        'bar' => 'Bar Chart',
        'line' => 'Line Chart',
        'pie' => 'Pie Chart',
        'area' => 'Area Chart',
        'table' => 'Data Table',
        'list' => 'List View',
    ];

    /**
     * Widget categories available.
     */
    public const CATEGORIES = [
        'ideas' => 'Ideas & Submissions',
        'users' => 'Users & Engagement',
        'analytics' => 'Analytics & Metrics',
        'approvals' => 'Approvals & Workflow',
    ];

    /**
     * Scope to get system widgets.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get custom widgets.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the widget data based on configuration.
     * This method will be called to fetch actual data for the widget.
     */
    public function getData(array $filters = []): array
    {
        $config = $this->config ?? [];

        // This is a placeholder - actual implementation would vary by widget type
        return [
            'type' => $this->type,
            'category' => $this->category,
            'data' => $this->fetchDataByType($config, $filters),
        ];
    }

    /**
     * Fetch data based on widget type and configuration.
     */
    protected function fetchDataByType(array $config, array $filters): array
    {
        return match ($this->category) {
            'ideas' => $this->fetchIdeasData($config, $filters),
            'users' => $this->fetchUsersData($config, $filters),
            'analytics' => $this->fetchAnalyticsData($config, $filters),
            'approvals' => $this->fetchApprovalsData($config, $filters),
            default => [],
        };
    }

    /**
     * Fetch ideas-related data.
     */
    protected function fetchIdeasData(array $config, array $filters): array
    {
        $query = Idea::query();

        // Apply filters from config
        if (isset($config['status'])) {
            $query->where('status', $config['status']);
        }

        if (isset($filters['date_range'])) {
            $query->whereBetween('created_at', $filters['date_range']);
        }

        return match ($this->type) {
            'stats_card' => ['count' => $query->count()],
            'bar', 'line', 'area' => $this->aggregateByDate($query),
            'pie' => $this->aggregateByStatus($query),
            'table', 'list' => $query->latest()->limit(10)->get()->toArray(),
            default => [],
        };
    }

    /**
     * Fetch users-related data.
     */
    protected function fetchUsersData(array $config, array $filters): array
    {
        $query = User::query();

        return match ($this->type) {
            'stats_card' => ['count' => $query->count()],
            'bar', 'line' => $this->aggregateUsersByDate($query),
            'table', 'list' => $query->latest()->limit(10)->get()->toArray(),
            default => [],
        };
    }

    /**
     * Fetch analytics-related data.
     */
    protected function fetchAnalyticsData(array $config, array $filters): array
    {
        // Placeholder for analytics aggregations
        return [
            'metrics' => [
                'total_ideas' => Idea::count(),
                'active_users' => User::where('is_active', true)->count(),
                'pending_approvals' => Approval::where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * Fetch approvals-related data.
     */
    protected function fetchApprovalsData(array $config, array $filters): array
    {
        $query = Approval::query();

        if (isset($config['status'])) {
            $query->where('status', $config['status']);
        }

        return match ($this->type) {
            'stats_card' => ['count' => $query->count()],
            'pie' => $this->aggregateApprovalsByStatus($query),
            'table', 'list' => $query->latest()->limit(10)->get()->toArray(),
            default => [],
        };
    }

    /**
     * Helper: Aggregate data by date.
     */
    protected function aggregateByDate($query): array
    {
        return $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    /**
     * Helper: Aggregate data by status.
     */
    protected function aggregateByStatus($query): array
    {
        return $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    /**
     * Helper: Aggregate approvals by status.
     */
    protected function aggregateApprovalsByStatus($query): array
    {
        return $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    /**
     * Helper: Aggregate users by registration date.
     */
    protected function aggregateUsersByDate($query): array
    {
        return $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
    }
}
