# IdeaHub API Documentation

Complete REST API reference for the IdeaHub Innovation Management Platform.

## Table of Contents

- [Base URL](#base-url)
- [Authentication](#authentication)
- [Common Response Format](#common-response-format)
- [Error Codes](#error-codes)
- [Rate Limiting](#rate-limiting)

## Base URL

\`\`\`
http://localhost:8000/api
\`\`\`

For production, replace with your actual domain.

## Authentication

IdeaHub uses **Laravel Sanctum** for token-based authentication.

### Authentication Flow

1. Register or login to receive a token
2. Include token in all subsequent requests:
   \`\`\`http
   Authorization: Bearer {your-token-here}
   \`\`\`

### Token Management

- Tokens don't expire by default (configurable)
- Logout invalidates the current token
- Multiple tokens per user supported

## Common Response Format

### Success Response

\`\`\`json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
\`\`\`

### Error Response

\`\`\`json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
\`\`\`

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## API Endpoints

See README.md for endpoint summary. Key endpoints include:

- **Authentication:** /api/register, /api/login, /api/logout
- **Ideas:** /api/ideas (CRUD + like, submit, attachments)
- **Comments:** /api/comments (CRUD + like, threading)
- **Categories & Tags:** /api/categories, /api/tags
- **Approvals:** /api/approvals (approve, reject, workflow)
- **Analytics:** /api/analytics/* (8 endpoints)
- **Gamification:** /api/badges, /api/leaderboard
- **Dashboard:** /api/dashboards (custom builder)
- **Export:** /api/export/* (PDF, CSV)
- **SSO:** /api/sso/* (SAML, OAuth, LDAP)
- **Integrations:** /api/integrations/* (Slack, Teams, JIRA)

## GraphQL API

GraphQL endpoint: \`POST /graphql\`

See \`graphql/schema.graphql\` for complete schema (1,115 lines).

## Rate Limiting

- **Default:** 60 requests per minute
- **Authenticated:** 120 requests per minute

---

**For detailed endpoint documentation, refer to the codebase controllers in \`app/Http/Controllers/Api/\`**

**Last Updated:** 2025-01-17
