# Dashboard Builder System Documentation

## Overview

The Dashboard Builder System allows users to create customizable dashboards with various widget types to visualize and monitor their data in IdeaHub. This system provides a flexible, drag-and-drop interface for creating personalized analytics and monitoring views.

## Architecture

### Models

#### 1. UserDashboard (`/home/user/IdeaHub/app/Models/UserDashboard.php`)

Represents a user's custom dashboard configuration.

**Attributes:**
- `id` - Primary key
- `user_id` - Foreign key to users table
- `tenant_id` - Foreign key to tenants table
- `name` - Dashboard name
- `slug` - URL-friendly unique identifier (per user)
- `widgets` - JSON array of widget configurations
- `layout` - JSON object with grid layout settings
- `is_default` - Boolean flag for default dashboard
- `is_shared` - Boolean flag for team sharing

**Widget Configuration Structure:**
```json
{
  "id": "unique-widget-id",
  "widget_id": 1,
  "position": {
    "x": 0,
    "y": 0,
    "w": 6,
    "h": 4
  },
  "filters": {
    "status": "pending",
    "date_range": ["2024-01-01", "2024-12-31"]
  }
}
```

**Layout Configuration Structure:**
```json
{
  "columns": 12,
  "row_height": 100,
  "compact_type": "vertical"
}
```

**Key Methods:**
- `setAsDefault()` - Sets this dashboard as the user's default
- `addWidget(array $config)` - Adds a widget to the dashboard
- `removeWidget(string $widgetId)` - Removes a widget
- `updateWidget(string $widgetId, array $config)` - Updates widget configuration
- `updateLayout(array $layout)` - Updates grid layout

**Scopes:**
- `default($userId)` - Get user's default dashboard
- `shared()` - Get all shared dashboards

#### 2. DashboardWidget (`/home/user/IdeaHub/app/Models/DashboardWidget.php`)

Represents a widget template that can be added to dashboards.

**Attributes:**
- `id` - Primary key
- `tenant_id` - Foreign key to tenants table (nullable for system widgets)
- `name` - Widget display name
- `type` - Widget visualization type
- `category` - Widget data category
- `config` - JSON configuration for data fetching
- `is_system` - Boolean flag for system vs. custom widgets
- `description` - Widget description

**Widget Types:**
- `stats_card` - Single metric display card
- `bar` - Bar chart
- `line` - Line chart
- `pie` - Pie chart
- `area` - Area chart
- `table` - Data table
- `list` - List view

**Widget Categories:**
- `ideas` - Ideas and submissions data
- `users` - User and engagement data
- `analytics` - Analytics and metrics data
- `approvals` - Approval workflow data

**Key Methods:**
- `getData(array $filters)` - Fetches data for the widget
- `fetchDataByType(array $config, array $filters)` - Internal data fetching by type

**Scopes:**
- `system()` - Get system widgets only
- `custom()` - Get custom widgets only
- `byCategory(string $category)` - Filter by category
- `byType(string $type)` - Filter by type

### Controllers

#### 1. DashboardController (`/home/user/IdeaHub/app/Http/Controllers/Api/DashboardController.php`)

Manages user dashboard CRUD operations.

**Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dashboards` | List user's dashboards |
| POST | `/api/dashboards` | Create new dashboard |
| GET | `/api/dashboards/{id}` | Get dashboard with data |
| PUT/PATCH | `/api/dashboards/{id}` | Update dashboard |
| DELETE | `/api/dashboards/{id}` | Delete dashboard |
| POST | `/api/dashboards/{id}/set-default` | Set as default |
| POST | `/api/dashboards/{id}/share` | Share/unshare dashboard |
| GET | `/api/dashboards/{id}/widgets/{widgetId}/data` | Get widget data |
| GET | `/api/dashboards/shared/all` | Get shared dashboards |

**Create Dashboard Request:**
```json
{
  "name": "My Analytics Dashboard",
  "slug": "my-analytics-dashboard",
  "widgets": [
    {
      "id": "widget-1",
      "widget_id": 1,
      "position": { "x": 0, "y": 0, "w": 6, "h": 4 },
      "filters": {}
    }
  ],
  "layout": {
    "columns": 12,
    "row_height": 100,
    "compact_type": "vertical"
  },
  "is_default": false,
  "is_shared": false
}
```

**Update Dashboard Request:**
```json
{
  "name": "Updated Dashboard Name",
  "widgets": [...],
  "layout": {...},
  "is_shared": true
}
```

**Share Dashboard Request:**
```json
{
  "is_shared": true
}
```

#### 2. WidgetController (`/home/user/IdeaHub/app/Http/Controllers/Api/WidgetController.php`)

Manages widget templates (admin functionality).

**Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/widgets` | List available widget templates |
| POST | `/api/widgets` | Create widget template (admin) |
| GET | `/api/widgets/{id}` | Get widget template |
| PUT/PATCH | `/api/widgets/{id}` | Update widget template (admin) |
| DELETE | `/api/widgets/{id}` | Delete widget template (admin) |
| GET | `/api/widgets/{id}/preview` | Preview widget with data |
| GET | `/api/widgets-metadata` | Get widget types and categories |

**Create Widget Request:**
```json
{
  "name": "Custom Widget",
  "type": "bar",
  "category": "ideas",
  "description": "Custom bar chart for ideas",
  "config": {
    "aggregation": "count",
    "time_range": "30d",
    "limit": 10
  }
}
```

**Preview Widget Request:**
```json
{
  "filters": {
    "status": "pending",
    "date_range": ["2024-01-01", "2024-12-31"]
  },
  "use_sample_data": false
}
```

### Database Schema

#### user_dashboards Table

```sql
CREATE TABLE user_dashboards (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    widgets JSON NULL,
    layout JSON NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_shared BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY (user_id, slug),
    INDEX (tenant_id, user_id),
    INDEX (is_default),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

#### dashboard_widgets Table

```sql
CREATE TABLE dashboard_widgets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    config JSON NULL,
    is_system BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX (tenant_id, category),
    INDEX (is_system),
    INDEX (type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### Factories

#### UserDashboardFactory
Location: `/home/user/IdeaHub/database/factories/UserDashboardFactory.php`

```php
UserDashboard::factory()->create();
UserDashboard::factory()->default()->create(); // Default dashboard
UserDashboard::factory()->shared()->create(); // Shared dashboard
```

#### DashboardWidgetFactory
Location: `/home/user/IdeaHub/database/factories/DashboardWidgetFactory.php`

```php
DashboardWidget::factory()->create();
DashboardWidget::factory()->system()->create(); // System widget
```

### Seeders

#### DashboardWidgetSeeder
Location: `/home/user/IdeaHub/database/seeders/DashboardWidgetSeeder.php`

Seeds 22 pre-built system widget templates across 4 categories:

**Ideas Category (9 widgets):**
- Total Ideas (stats_card)
- Pending Ideas (stats_card)
- Approved Ideas (stats_card)
- Implemented Ideas (stats_card)
- Ideas by Status (pie)
- Ideas Trend (line)
- Ideas by Category (bar)
- Recent Ideas (list)
- Top Ideas by Likes (table)

**Users Category (5 widgets):**
- Total Users (stats_card)
- Active Users (stats_card)
- User Growth (area)
- Top Contributors (table)
- Leaderboard (list)

**Approvals Category (4 widgets):**
- Pending Approvals (stats_card)
- Approvals by Status (pie)
- Approval Queue (table)
- Recent Approvals (list)

**Analytics Category (4 widgets):**
- Engagement Rate (stats_card)
- Total Comments (stats_card)
- Activity Trend (line)
- Department Activity (bar)

## Usage Examples

### Creating a Dashboard

```php
// Create a new dashboard
$dashboard = UserDashboard::create([
    'user_id' => auth()->id(),
    'name' => 'My Dashboard',
    'slug' => 'my-dashboard',
    'widgets' => [],
    'layout' => [
        'columns' => 12,
        'row_height' => 100,
    ],
]);

// Add a widget
$dashboard->addWidget([
    'id' => 'widget-1',
    'widget_id' => 1,
    'position' => ['x' => 0, 'y' => 0, 'w' => 6, 'h' => 4],
    'filters' => ['status' => 'pending'],
]);

// Set as default
$dashboard->setAsDefault();
```

### Fetching Widget Data

```php
// Get a widget template
$widget = DashboardWidget::find(1);

// Fetch data with filters
$data = $widget->getData([
    'date_range' => [now()->subDays(30), now()],
    'status' => 'pending',
]);
```

### API Usage (Frontend)

```javascript
// Fetch user's dashboards
const dashboards = await fetch('/api/dashboards', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});

// Create a new dashboard
const newDashboard = await fetch('/api/dashboards', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'Analytics Dashboard',
    widgets: [...],
    layout: {...},
  }),
});

// Get dashboard with data
const dashboard = await fetch('/api/dashboards/1', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});

// Update dashboard
const updated = await fetch('/api/dashboards/1', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    widgets: [...],
  }),
});

// Set as default
await fetch('/api/dashboards/1/set-default', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});

// Share dashboard
await fetch('/api/dashboards/1/share', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    is_shared: true,
  }),
});

// Get widget data
const widgetData = await fetch('/api/dashboards/1/widgets/widget-1/data', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});

// List available widgets
const widgets = await fetch('/api/widgets', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});

// Preview widget
const preview = await fetch('/api/widgets/1/preview?use_sample_data=true', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});
```

## Features

### User Features

1. **Multiple Dashboards**
   - Users can create unlimited dashboards
   - Each dashboard has a unique slug per user
   - One dashboard can be set as default

2. **Customizable Layouts**
   - Grid-based drag-and-drop layout
   - Configurable column count
   - Adjustable row heights
   - Responsive positioning

3. **Widget Library**
   - 22 pre-built system widgets
   - Support for custom widgets (admin only)
   - Widgets grouped by category
   - Multiple visualization types

4. **Dashboard Sharing**
   - Share dashboards with team members
   - View shared dashboards from others
   - Private by default

5. **Data Filtering**
   - Per-widget filter configuration
   - Date range filtering
   - Status filtering
   - Custom filters based on widget type

### Admin Features

1. **Widget Template Management**
   - Create custom widget templates
   - Update non-system widgets
   - Delete custom widgets
   - System widgets are protected

2. **Preview Functionality**
   - Preview widgets with sample data
   - Test widget configurations
   - Validate before deployment

## Multi-Tenancy

All dashboards and widgets are tenant-aware:

- User dashboards are scoped to the current tenant
- System widgets (is_system=true) are available to all tenants
- Custom widgets are tenant-specific
- BelongsToTenant trait automatically handles scoping

## Security Considerations

1. **Authorization**
   - Users can only access their own dashboards
   - Shared dashboards require explicit sharing flag
   - Admin-only operations for widget templates
   - System widgets cannot be modified or deleted

2. **Validation**
   - All inputs are validated
   - Slug uniqueness enforced per user
   - Widget positions validated
   - JSON structure validation

3. **Data Access**
   - Tenant scoping prevents cross-tenant data access
   - Widget data respects user permissions
   - Filters are sanitized before queries

## Performance Considerations

1. **Caching Strategy**
   - Widget data can be cached with appropriate TTL
   - Dashboard configurations are lightweight
   - Lazy loading of widget data

2. **Query Optimization**
   - Indexed columns for fast lookups
   - Eager loading of relationships
   - Pagination for large datasets

3. **Scalability**
   - JSON storage for flexible configurations
   - No database schema changes for new widget types
   - Horizontal scaling supported

## Future Enhancements

1. **Real-time Updates**
   - Live data refresh using Laravel Echo
   - WebSocket support for real-time dashboards

2. **Export Functionality**
   - Export dashboards as PDF
   - Share dashboard snapshots
   - Schedule automated reports

3. **Advanced Widgets**
   - Heatmaps
   - Gantt charts
   - Funnel charts
   - Custom D3.js visualizations

4. **Collaboration**
   - Dashboard templates
   - Team dashboards
   - Comments on widgets
   - Activity feed

5. **Mobile Support**
   - Responsive layouts
   - Mobile-optimized widgets
   - Touch gestures for drag-and-drop

## Testing

### Running Migrations

```bash
php artisan migrate
```

### Seeding Widget Templates

```bash
php artisan db:seed --class=DashboardWidgetSeeder
```

### Creating Test Data

```php
// Create a user with dashboards
$user = User::factory()
    ->has(UserDashboard::factory()->count(3))
    ->create();

// Create a dashboard with default flag
$dashboard = UserDashboard::factory()
    ->default()
    ->for($user)
    ->create();

// Create system widgets
DashboardWidget::factory()
    ->system()
    ->count(10)
    ->create();
```

## Troubleshooting

### Common Issues

1. **Slug conflicts**
   - Solution: System auto-generates unique slugs by appending numbers

2. **Cannot delete last dashboard**
   - Solution: API prevents deletion of the last dashboard

3. **Widget data not loading**
   - Check widget configuration
   - Verify filters are valid
   - Ensure data exists for the query

4. **System widgets appearing multiple times**
   - System widgets should only be seeded once
   - Check DashboardWidgetSeeder has run only once

## Support

For issues or questions:
- Check the API documentation
- Review the model methods
- Examine the controller validation rules
- Test with the factory methods
