# Dashboard Builder API Reference

Quick reference for all dashboard and widget API endpoints.

## Authentication

All endpoints require authentication using Laravel Sanctum:

```
Authorization: Bearer {token}
```

## Dashboard Endpoints

### List User Dashboards
```
GET /api/dashboards
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "tenant_id": 1,
      "name": "My Dashboard",
      "slug": "my-dashboard",
      "widgets": [...],
      "layout": {...},
      "is_default": true,
      "is_shared": false,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Create Dashboard
```
POST /api/dashboards
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Analytics Dashboard",
  "slug": "analytics-dashboard",
  "widgets": [
    {
      "id": "widget-uuid-1",
      "widget_id": 1,
      "position": {
        "x": 0,
        "y": 0,
        "w": 6,
        "h": 4
      },
      "filters": {
        "status": "pending"
      }
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

**Response:** (201 Created)
```json
{
  "success": true,
  "data": {...},
  "message": "Dashboard created successfully"
}
```

### Get Dashboard with Data
```
GET /api/dashboards/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "dashboard": {
      "id": 1,
      "name": "My Dashboard",
      ...
    },
    "widgets": [
      {
        "id": "widget-uuid-1",
        "widget_id": 1,
        "position": {...},
        "filters": {...},
        "widget_info": {
          "name": "Total Ideas",
          "type": "stats_card",
          "category": "ideas",
          "description": "Total number of ideas submitted"
        },
        "data": {
          "type": "stats_card",
          "category": "ideas",
          "data": {
            "count": 150
          }
        }
      }
    ]
  }
}
```

### Update Dashboard
```
PUT /api/dashboards/{id}
PATCH /api/dashboards/{id}
Content-Type: application/json
```

**Request Body:** (all fields optional)
```json
{
  "name": "Updated Dashboard Name",
  "widgets": [...],
  "layout": {...},
  "is_shared": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {...},
  "message": "Dashboard updated successfully"
}
```

### Delete Dashboard
```
DELETE /api/dashboards/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Dashboard deleted successfully"
}
```

**Error Response:** (if trying to delete last dashboard)
```json
{
  "success": false,
  "message": "Cannot delete your last dashboard"
}
```

### Set Dashboard as Default
```
POST /api/dashboards/{id}/set-default
```

**Response:**
```json
{
  "success": true,
  "data": {...},
  "message": "Dashboard set as default"
}
```

### Share/Unshare Dashboard
```
POST /api/dashboards/{id}/share
Content-Type: application/json
```

**Request Body:**
```json
{
  "is_shared": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {...},
  "message": "Dashboard is now shared with your team"
}
```

### Get Widget Data
```
GET /api/dashboards/{id}/widgets/{widgetId}/data
GET /api/dashboards/{id}/widgets/{widgetId}/data?filters[status]=pending
```

**Response:**
```json
{
  "success": true,
  "data": {
    "widget_id": "widget-uuid-1",
    "widget_info": {
      "name": "Total Ideas",
      "type": "stats_card",
      "category": "ideas",
      "description": "Total number of ideas submitted"
    },
    "data": {
      "type": "stats_card",
      "category": "ideas",
      "data": {
        "count": 150
      }
    }
  }
}
```

### Get Shared Dashboards
```
GET /api/dashboards/shared/all
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "user_id": 2,
      "name": "Team Analytics",
      "is_shared": true,
      "user": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "..."
      },
      ...
    }
  ]
}
```

## Widget Template Endpoints

### List Widget Templates
```
GET /api/widgets
GET /api/widgets?category=ideas
GET /api/widgets?type=bar
GET /api/widgets?category=ideas&type=stats_card
```

**Response:**
```json
{
  "success": true,
  "data": {
    "widgets": [
      {
        "id": 1,
        "tenant_id": null,
        "name": "Total Ideas",
        "type": "stats_card",
        "category": "ideas",
        "config": {...},
        "is_system": true,
        "description": "Total number of ideas submitted",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "grouped": {
      "ideas": [...],
      "users": [...],
      "analytics": [...],
      "approvals": [...]
    },
    "types": {
      "stats_card": "Statistics Card",
      "bar": "Bar Chart",
      "line": "Line Chart",
      "pie": "Pie Chart",
      "area": "Area Chart",
      "table": "Data Table",
      "list": "List View"
    },
    "categories": {
      "ideas": "Ideas & Submissions",
      "users": "Users & Engagement",
      "analytics": "Analytics & Metrics",
      "approvals": "Approvals & Workflow"
    }
  }
}
```

### Create Widget Template (Admin Only)
```
POST /api/widgets
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Custom Widget",
  "type": "bar",
  "category": "ideas",
  "description": "Custom bar chart for ideas",
  "config": {
    "aggregation": "count",
    "time_range": "30d",
    "limit": 10,
    "status": "pending"
  }
}
```

**Response:** (201 Created)
```json
{
  "success": true,
  "data": {...},
  "message": "Widget template created successfully"
}
```

**Error Response:** (if not admin)
```json
{
  "success": false,
  "message": "Unauthorized. Only administrators can create widget templates."
}
```

### Get Widget Template
```
GET /api/widgets/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Total Ideas",
    "type": "stats_card",
    "category": "ideas",
    ...
  }
}
```

### Update Widget Template (Admin Only)
```
PUT /api/widgets/{id}
PATCH /api/widgets/{id}
Content-Type: application/json
```

**Request Body:** (all fields optional)
```json
{
  "name": "Updated Widget Name",
  "description": "Updated description",
  "config": {...}
}
```

**Response:**
```json
{
  "success": true,
  "data": {...},
  "message": "Widget template updated successfully"
}
```

**Error Response:** (if system widget)
```json
{
  "success": false,
  "message": "System widgets cannot be modified."
}
```

### Delete Widget Template (Admin Only)
```
DELETE /api/widgets/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Widget template deleted successfully"
}
```

**Error Response:** (if system widget)
```json
{
  "success": false,
  "message": "System widgets cannot be deleted."
}
```

### Preview Widget
```
GET /api/widgets/{id}/preview
GET /api/widgets/{id}/preview?use_sample_data=true
POST /api/widgets/{id}/preview
Content-Type: application/json
```

**Request Body:** (optional)
```json
{
  "filters": {
    "status": "pending",
    "date_range": ["2024-01-01", "2024-12-31"]
  },
  "use_sample_data": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "widget": {
      "id": 1,
      "name": "Total Ideas",
      "type": "stats_card",
      ...
    },
    "preview_data": {
      "type": "stats_card",
      "category": "ideas",
      "data": {
        "count": 150
      }
    }
  }
}
```

### Get Widget Metadata
```
GET /api/widgets-metadata
```

**Response:**
```json
{
  "success": true,
  "data": {
    "types": {
      "stats_card": "Statistics Card",
      "bar": "Bar Chart",
      "line": "Line Chart",
      "pie": "Pie Chart",
      "area": "Area Chart",
      "table": "Data Table",
      "list": "List View"
    },
    "categories": {
      "ideas": "Ideas & Submissions",
      "users": "Users & Engagement",
      "analytics": "Analytics & Metrics",
      "approvals": "Approvals & Workflow"
    }
  }
}
```

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "widgets.0.widget_id": [
      "The selected widget_id is invalid."
    ]
  }
}
```

### Unauthorized (403)
```json
{
  "success": false,
  "message": "Unauthorized access to this dashboard"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Widget not found in this dashboard"
}
```

### Server Error (500)
```json
{
  "message": "Server Error"
}
```

## Widget Data Response Formats

### Stats Card
```json
{
  "count": 150,
  "label": "Total Ideas",
  "change": "+15%"
}
```

### Bar/Line/Area Chart
```json
{
  "data": [
    { "date": "2024-01-01", "count": 10 },
    { "date": "2024-01-02", "count": 15 },
    { "date": "2024-01-03", "count": 12 }
  ]
}
```

### Pie Chart
```json
{
  "data": [
    { "status": "pending", "count": 50 },
    { "status": "approved", "count": 80 },
    { "status": "rejected", "count": 20 },
    { "status": "implemented", "count": 40 }
  ]
}
```

### Table/List
```json
{
  "data": [
    {
      "id": 1,
      "title": "Idea Title",
      "status": "pending",
      "created_at": "2024-01-01T00:00:00.000000Z",
      ...
    }
  ]
}
```

## Rate Limiting

All API endpoints are subject to Laravel's default rate limiting (60 requests per minute for authenticated users).

## CORS

Configure CORS settings in `config/cors.php` for frontend integration.

## Pagination

List endpoints support pagination using Laravel's standard query parameters:
- `?page=1` - Page number
- `?per_page=15` - Items per page (default: 15, max: 100)

## Filtering

Widget data can be filtered using query parameters:
```
GET /api/dashboards/1/widgets/widget-1/data?filters[status]=pending&filters[category_id]=2
```

## Sorting

Some endpoints support sorting:
```
GET /api/widgets?sort=name&order=asc
```
