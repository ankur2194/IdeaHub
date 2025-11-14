# IdeaHub API Documentation

**Version:** 1.0.0
**Base URL:** `http://localhost:8000/api` (development)
**Production URL:** `https://yourdomain.com/api`

## Table of Contents

- [Authentication](#authentication)
- [Error Handling](#error-handling)
- [Pagination](#pagination)
- [Endpoints](#endpoints)
  - [Authentication](#authentication-endpoints)
  - [Ideas](#ideas-endpoints)
  - [Comments](#comments-endpoints)
  - [Categories](#categories-endpoints)
  - [Tags](#tags-endpoints)
  - [Approvals](#approvals-endpoints)

---

## Authentication

IdeaHub uses **Laravel Sanctum** for API authentication with token-based authentication.

### Authentication Flow

1. **Register or Login** to receive an authentication token
2. **Include token** in the `Authorization` header for all protected requests
3. **Token format:** `Bearer {your-token-here}`

### Request Headers

```http
Content-Type: application/json
Authorization: Bearer {token}
```

### Example Request

```bash
curl -X GET http://localhost:8000/api/ideas \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abc123def456..."
```

---

## Error Handling

### Standard Error Response

```json
{
  "success": false,
  "message": "Error message description",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 204 | No Content - Request successful, no content returned |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required or failed |
| 403 | Forbidden - Authenticated but not authorized |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error - Server error |

---

## Pagination

List endpoints support pagination with the following parameters:

### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | integer | 1 | Page number |
| per_page | integer | 15 | Items per page (max: 100) |

### Pagination Response

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://localhost:8000/api/ideas?page=1",
    "last": "http://localhost:8000/api/ideas?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/ideas?page=2"
  }
}
```

---

## Endpoints

---

## Authentication Endpoints

### Register User

Create a new user account.

**Endpoint:** `POST /api/register`
**Authentication:** Not required

#### Request Body

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123",
  "department": "Engineering",
  "job_title": "Software Developer"
}
```

#### Validation Rules

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| name | string | Yes | Max 255 characters |
| email | string | Yes | Valid email, unique |
| password | string | Yes | Min 8 characters, confirmed |
| department | string | No | Max 255 characters |
| job_title | string | No | Max 255 characters |

#### Response (201 Created)

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "department": "Engineering",
      "job_title": "Software Developer",
      "is_active": true,
      "created_at": "2025-11-14T10:30:00.000000Z"
    },
    "token": "1|abc123def456..."
  }
}
```

---

### Login

Authenticate and receive an access token.

**Endpoint:** `POST /api/login`
**Authentication:** Not required

#### Request Body

```json
{
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|abc123def456..."
  }
}
```

#### Error Response (401 Unauthorized)

```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### Logout

Revoke the current access token.

**Endpoint:** `POST /api/logout`
**Authentication:** Required

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Get Authenticated User

Retrieve the currently authenticated user's information.

**Endpoint:** `GET /api/user`
**Authentication:** Required

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "department": "Engineering",
    "job_title": "Software Developer",
    "is_active": true,
    "created_at": "2025-11-14T10:30:00.000000Z"
  }
}
```

---

## Ideas Endpoints

### List Ideas

Retrieve a paginated list of ideas with optional filtering and sorting.

**Endpoint:** `GET /api/ideas`
**Authentication:** Required

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number (default: 1) |
| per_page | integer | Items per page (default: 15, max: 100) |
| status | string | Filter by status (draft, submitted, under_review, approved, rejected, implemented, archived) |
| category_id | integer | Filter by category ID |
| search | string | Search in title and description |
| sort | string | Sort field (created_at, likes_count, comments_count, views_count) |
| order | string | Sort order (asc, desc) - default: desc |

#### Example Request

```bash
GET /api/ideas?status=submitted&category_id=2&sort=likes_count&order=desc&page=1
```

#### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Implement Dark Mode",
      "description": "Add dark mode support to improve user experience...",
      "status": "submitted",
      "is_anonymous": false,
      "likes_count": 15,
      "comments_count": 8,
      "views_count": 142,
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "category": {
        "id": 2,
        "name": "Product Innovation",
        "color": "#3B82F6",
        "icon": "lightbulb"
      },
      "tags": [
        {
          "id": 1,
          "name": "UI/UX"
        },
        {
          "id": 3,
          "name": "Frontend"
        }
      ],
      "created_at": "2025-11-14T10:30:00.000000Z",
      "updated_at": "2025-11-14T12:15:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

---

### Get Idea Details

Retrieve detailed information about a specific idea.

**Endpoint:** `GET /api/ideas/{id}`
**Authentication:** Required

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Implement Dark Mode",
    "description": "Detailed description of the dark mode feature...",
    "status": "submitted",
    "is_anonymous": false,
    "likes_count": 15,
    "comments_count": 8,
    "views_count": 143,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "department": "Engineering"
    },
    "category": {
      "id": 2,
      "name": "Product Innovation",
      "color": "#3B82F6",
      "icon": "lightbulb"
    },
    "tags": [
      {
        "id": 1,
        "name": "UI/UX"
      }
    ],
    "created_at": "2025-11-14T10:30:00.000000Z",
    "updated_at": "2025-11-14T12:15:00.000000Z"
  }
}
```

---

### Create Idea

Create a new idea (saved as draft).

**Endpoint:** `POST /api/ideas`
**Authentication:** Required

#### Request Body

```json
{
  "title": "Implement Dark Mode",
  "description": "Add dark mode support to improve user experience during night hours...",
  "category_id": 2,
  "tag_ids": [1, 3],
  "is_anonymous": false
}
```

#### Validation Rules

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| title | string | Yes | Max 255 characters |
| description | text | Yes | - |
| category_id | integer | Yes | Must exist in categories |
| tag_ids | array | No | Array of existing tag IDs |
| is_anonymous | boolean | No | Default: false |

#### Response (201 Created)

```json
{
  "success": true,
  "message": "Idea created successfully",
  "data": {
    "id": 1,
    "title": "Implement Dark Mode",
    "status": "draft",
    "created_at": "2025-11-14T10:30:00.000000Z"
  }
}
```

---

### Update Idea

Update an existing idea (only if status allows).

**Endpoint:** `PUT /api/ideas/{id}`
**Authentication:** Required
**Authorization:** Own ideas only, status must be draft or submitted

#### Request Body

```json
{
  "title": "Implement Dark Mode (Updated)",
  "description": "Updated description...",
  "category_id": 2,
  "tag_ids": [1, 3, 5]
}
```

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Idea updated successfully",
  "data": {
    "id": 1,
    "title": "Implement Dark Mode (Updated)",
    "updated_at": "2025-11-14T12:30:00.000000Z"
  }
}
```

---

### Delete Idea

Delete an idea (only if status is draft).

**Endpoint:** `DELETE /api/ideas/{id}`
**Authentication:** Required
**Authorization:** Own ideas only, status must be draft

#### Response (204 No Content)

```json
{
  "success": true,
  "message": "Idea deleted successfully"
}
```

---

### Submit Idea for Review

Change idea status from draft to submitted.

**Endpoint:** `POST /api/ideas/{id}/submit`
**Authentication:** Required
**Authorization:** Own ideas only, status must be draft

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Idea submitted for review",
  "data": {
    "id": 1,
    "status": "submitted",
    "updated_at": "2025-11-14T12:30:00.000000Z"
  }
}
```

---

### Like/Unlike Idea

Toggle like status on an idea.

**Endpoint:** `POST /api/ideas/{id}/like`
**Authentication:** Required

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Idea liked successfully",
  "data": {
    "liked": true,
    "likes_count": 16
  }
}
```

---

## Comments Endpoints

### List Comments for Idea

Retrieve all comments for a specific idea.

**Endpoint:** `GET /api/ideas/{idea_id}/comments`
**Authentication:** Required

#### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content": "Great idea! This would really improve the user experience.",
      "is_anonymous": false,
      "likes_count": 5,
      "user": {
        "id": 2,
        "name": "Jane Smith"
      },
      "created_at": "2025-11-14T11:00:00.000000Z",
      "updated_at": "2025-11-14T11:00:00.000000Z"
    }
  ]
}
```

---

### Create Comment

Add a comment to an idea.

**Endpoint:** `POST /api/comments`
**Authentication:** Required

#### Request Body

```json
{
  "idea_id": 1,
  "content": "Great idea! This would really improve the user experience.",
  "is_anonymous": false
}
```

#### Validation Rules

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| idea_id | integer | Yes | Must exist in ideas |
| content | text | Yes | - |
| is_anonymous | boolean | No | Default: false |

#### Response (201 Created)

```json
{
  "success": true,
  "message": "Comment created successfully",
  "data": {
    "id": 1,
    "content": "Great idea!",
    "created_at": "2025-11-14T11:00:00.000000Z"
  }
}
```

---

### Update Comment

Update an existing comment.

**Endpoint:** `PUT /api/comments/{id}`
**Authentication:** Required
**Authorization:** Own comments only

#### Request Body

```json
{
  "content": "Updated comment content..."
}
```

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Comment updated successfully",
  "data": {
    "id": 1,
    "content": "Updated comment content...",
    "updated_at": "2025-11-14T12:00:00.000000Z"
  }
}
```

---

### Delete Comment

Delete a comment.

**Endpoint:** `DELETE /api/comments/{id}`
**Authentication:** Required
**Authorization:** Own comments only

#### Response (204 No Content)

```json
{
  "success": true,
  "message": "Comment deleted successfully"
}
```

---

### Like/Unlike Comment

Toggle like status on a comment.

**Endpoint:** `POST /api/comments/{id}/like`
**Authentication:** Required

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Comment liked successfully",
  "data": {
    "liked": true,
    "likes_count": 6
  }
}
```

---

## Categories Endpoints

### List Categories

Retrieve all categories.

**Endpoint:** `GET /api/categories`
**Authentication:** Not required (public)

#### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Product Innovation",
      "slug": "product-innovation",
      "description": "Ideas for new products or product improvements",
      "color": "#3B82F6",
      "icon": "lightbulb",
      "is_active": true,
      "ideas_count": 24,
      "created_at": "2025-11-14T10:00:00.000000Z"
    }
  ]
}
```

---

### Get Category Details

Retrieve a specific category with its ideas.

**Endpoint:** `GET /api/categories/{id}`
**Authentication:** Not required (public)

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Innovation",
    "description": "Ideas for new products or product improvements",
    "color": "#3B82F6",
    "icon": "lightbulb",
    "ideas_count": 24,
    "recent_ideas": [...]
  }
}
```

---

### Create Category

Create a new category.

**Endpoint:** `POST /api/categories`
**Authentication:** Required
**Authorization:** Admin only

#### Request Body

```json
{
  "name": "Customer Experience",
  "description": "Ideas to improve customer satisfaction",
  "color": "#10B981",
  "icon": "users"
}
```

#### Response (201 Created)

```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": 9,
    "name": "Customer Experience",
    "slug": "customer-experience"
  }
}
```

---

### Update Category

Update an existing category.

**Endpoint:** `PUT /api/categories/{id}`
**Authentication:** Required
**Authorization:** Admin only

---

### Delete Category

Delete a category.

**Endpoint:** `DELETE /api/categories/{id}`
**Authentication:** Required
**Authorization:** Admin only

---

## Tags Endpoints

### List Tags

Retrieve all tags.

**Endpoint:** `GET /api/tags`
**Authentication:** Not required (public)

#### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "UI/UX",
      "slug": "ui-ux",
      "ideas_count": 15,
      "created_at": "2025-11-14T10:00:00.000000Z"
    }
  ]
}
```

---

### Create Tag

Create a new tag.

**Endpoint:** `POST /api/tags`
**Authentication:** Required

#### Request Body

```json
{
  "name": "Machine Learning"
}
```

---

## Approvals Endpoints

### List Approvals

Retrieve approvals (filtered by role).

**Endpoint:** `GET /api/approvals`
**Authentication:** Required
**Authorization:** Department heads and above

#### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "idea_id": 5,
      "approver_id": 2,
      "status": "pending",
      "level": 1,
      "idea": {
        "id": 5,
        "title": "New Feature Request"
      },
      "created_at": "2025-11-14T10:00:00.000000Z"
    }
  ]
}
```

---

### Approve Idea

Approve an idea at a specific approval level.

**Endpoint:** `POST /api/approvals/{id}/approve`
**Authentication:** Required
**Authorization:** Assigned approver only

#### Request Body

```json
{
  "comment": "Looks good, approved for implementation"
}
```

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Idea approved successfully",
  "data": {
    "id": 1,
    "status": "approved"
  }
}
```

---

### Reject Idea

Reject an idea at a specific approval level.

**Endpoint:** `POST /api/approvals/{id}/reject`
**Authentication:** Required
**Authorization:** Assigned approver only

#### Request Body

```json
{
  "comment": "Needs more detail before we can proceed"
}
```

---

### Get Pending Approvals Count

Get count of pending approvals for the authenticated user.

**Endpoint:** `GET /api/approvals/pending/count`
**Authentication:** Required
**Authorization:** Department heads and above

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "pending_count": 5
  }
}
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests:** 60 requests per minute
- **Guest requests:** 30 requests per minute

Rate limit headers are included in responses:

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1699876543
```

---

## Versioning

The API currently uses **v1** (implicit). Future versions will be prefixed:

- Current: `/api/ideas`
- Future: `/api/v2/ideas`

---

## Support

For API support:

- **Documentation:** [GitHub Repository](https://github.com/ankur2194/IdeaHub)
- **Issues:** [Report bugs](https://github.com/ankur2194/IdeaHub/issues)
- **Email:** support@ideahub.example

---

**Last Updated:** 2025-11-14
**API Version:** 1.0.0
