# ShortLink API Reference

## Overview

The ShortLink API provides RESTful endpoints for URL shortening, analytics, and affiliate management. All API requests use JSON format and require Bearer token authentication for security.

**Base URL**: `https://yourdomain.com/api/v1`

**API Version**: 1.0

## Authentication

### 🔐 Security Notice
For security reasons, **only Bearer token authentication** is supported. Query parameter authentication has been removed.

### Getting an API Token

1. Create an account on the platform
2. Generate an API token via the dashboard or API
3. Include the token in the `Authorization` header

```bash
# ✅ Correct - Bearer token in header
curl -H "Authorization: Bearer YOUR_64_CHARACTER_TOKEN" \
     https://yourdomain.com/api/v1/links

# ❌ Incorrect - Query parameter not supported
curl https://yourdomain.com/api/v1/links?api_key=YOUR_TOKEN
# Returns 401 Unauthorized
```

### Token Format
- Length: 64 characters
- Format: Random alphanumeric string
- Example: `V4ndcU18256YmBnnbZKEhWZRwHPqZmzo82hjxe3kDG2u5iPVSkEu0zlAadazp7s2`

## Rate Limiting

Dynamic rate limits are enforced per endpoint and can be configured via admin panel:

| Endpoint | Default Limit | Description |
|----------|----------------|-------------|
| API Endpoints | 1000 requests/hour | General API usage |
| Token Management | 100 requests/hour | Create/revoke tokens |
| Guest Shortening | 10 requests/hour | Public URL creation |

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Requests allowed per hour
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Unix timestamp when limit resets

## Endpoints

### Health Check

**GET** `/health`

No authentication required. Returns system health status.

```bash
curl https://yourdomain.com/api/v1/health
```

**Response (200 OK)**:
```json
{
  "status": "ok",
  "timestamp": "2026-05-11T14:30:00Z",
  "version": "1.0.0",
  "environment": "production",
  "checks": {
    "database": "ok",
    "cache": "ok",
    "queue": "ok"
  }
}
```

**Response (503 Service Unavailable)**:
```json
{
  "status": "error",
  "timestamp": "2026-05-11T14:30:00Z",
  "checks": {
    "database": "error",
    "cache": "ok",
    "queue": "ok"
  }
}
```

### API Information

**GET** `/`

No authentication required. Returns basic API information.

```bash
curl https://yourdomain.com/api/v1/
```

**Response**:
```json
{
  "name": "ShortLink API",
  "version": "1.0",
  "documentation": "https://yourdomain.com/api-docs"
}
```

### User Profile

**GET** `/user`

Requires authentication. Returns current user information.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/user
```

**Response**:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com"
}
```

## Links Management

### List Links

**GET** `/links`

Requires authentication. Returns paginated list of user's links.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/links?page=1&per_page=20
```

**Query Parameters**:
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 20, max: 100)
- `search` (string): Search in destination URLs
- `domain_id` (integer): Filter by domain

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "short_code": "abc123",
      "short_url": "https://yourdomain.com/abc123",
      "destination_url": "https://example.com/very-long-url",
      "custom_alias": null,
      "clicks_count": 42,
      "is_active": true,
      "expires_at": null,
      "created_at": "2026-05-11T10:00:00Z",
      "updated_at": "2026-05-11T10:00:00Z",
      "domain": {
        "id": 1,
        "domain": "yourdomain.com",
        "is_default": true
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

### Create Link

**POST** `/links`

Requires authentication. Creates a new short link.

```bash
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "destination_url": "https://example.com/very-long-url",
       "custom_alias": "my-link",
       "domain_id": 1,
       "expires_at": "2026-12-31T23:59:59Z",
       "og_title": "My Custom Title",
       "og_description": "My custom description"
     }' \
     https://yourdomain.com/api/v1/links
```

**Request Body**:
```json
{
  "destination_url": "string (required, max:2048)",
  "custom_alias": "string (optional, unique, max:50)",
  "domain_id": "integer (optional)",
  "expires_at": "datetime (optional, ISO 8601)",
  "og_title": "string (optional, max:255)",
  "og_description": "string (optional, max:500)",
  "password": "string (optional, for private links)",
  "visibility": "string (optional: public|private, default: public)"
}
```

**Response (201 Created)**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "short_code": "my-link",
    "short_url": "https://yourdomain.com/my-link",
    "destination_url": "https://example.com/very-long-url",
    "custom_alias": "my-link",
    "clicks_count": 0,
    "is_active": true,
    "expires_at": "2026-12-31T23:59:59Z",
    "created_at": "2026-05-11T14:30:00Z",
    "updated_at": "2026-05-11T14:30:00Z",
    "domain": {
      "id": 1,
      "domain": "yourdomain.com",
      "is_default": true
    }
  }
}
```

**Error Responses**:
- `422 Unprocessable Entity`: Validation errors
- `429 Too Many Requests`: Rate limit exceeded

### Get Link

**GET** `/links/{id}`

Requires authentication. Returns details of a specific link.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/links/123
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 123,
    "short_code": "abc123",
    "short_url": "https://yourdomain.com/abc123",
    "destination_url": "https://example.com/very-long-url",
    "custom_alias": null,
    "clicks_count": 42,
    "is_active": true,
    "expires_at": null,
    "created_at": "2026-05-11T10:00:00Z",
    "updated_at": "2026-05-11T10:00:00Z",
    "domain": {
      "id": 1,
      "domain": "yourdomain.com",
      "is_default": true
    }
  }
}
```

### Update Link

**PATCH** `/links/{id}` or **PUT** `/links/{id}`

Requires authentication. Updates a link (partial update with PATCH).

```bash
curl -X PATCH \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "destination_url": "https://new-example.com",
       "is_active": false
     }' \
     https://yourdomain.com/api/v1/links/123
```

**Request Body**: Same fields as create, all optional.

**Response**:
```json
{
  "success": true,
  "data": {
    // Updated link object
  }
}
```

### Delete Link

**DELETE** `/links/{id}`

Requires authentication. Deletes a link permanently.

```bash
curl -X DELETE \
     -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/links/123
```

**Response (200 OK)**:
```json
{
  "success": true,
  "message": "Link deleted successfully"
}
```

## Analytics

### Get Link Analytics

**GET** `/links/{id}/analytics`

Requires authentication. Returns analytics data for a link.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/links/123/analytics
```

**Response**:
```json
{
  "success": true,
  "data": {
    "link": {
      "id": 123,
      "short_code": "abc123",
      "destination_url": "https://example.com"
    },
    "total_clicks": 42,
    "unique_clicks": 38,
    "today_clicks": 5,
    "top_countries": [
      {
        "country_code": "US",
        "country_name": "United States",
        "clicks": 20
      },
      {
        "country_code": "GB",
        "country_name": "United Kingdom", 
        "clicks": 8
      }
    ],
    "top_referrers": [
      {
        "domain": "google.com",
        "clicks": 15
      },
      {
        "domain": "facebook.com",
        "clicks": 8
      }
    ],
    "devices": {
      "desktop": 25,
      "mobile": 15,
      "tablet": 2
    },
    "browsers": {
      "Chrome": 30,
      "Safari": 8,
      "Firefox": 4
    },
    "daily_clicks": [
      {
        "date": "2026-05-10",
        "clicks": 8
      },
      {
        "date": "2026-05-11",
        "clicks": 5
      }
    ]
  }
}
```

## API Token Management

### List Tokens

**GET** `/tokens`

Requires authentication. Lists user's API tokens.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/tokens
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "My API Token",
      "last_used_at": "2026-05-11T14:00:00Z",
      "expires_at": null,
      "created_at": "2026-05-10T10:00:00Z"
    }
  ]
}
```

### Create Token

**POST** `/tokens`

Requires authentication. Creates a new API token.

```bash
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Production API",
       "expires_days": 365
     }' \
     https://yourdomain.com/api/v1/tokens
```

**Request Body**:
```json
{
  "name": "string (optional, max:255, default: 'API Token')",
  "expires_days": "integer (optional, 1-365, default: null)"
}
```

**Response (201 Created)**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Production API",
    "token": "V4ndcU18256YmBnnbZKEhWZRwHPqZmzo82hjxe3kDG2u5iPVSkEu0zlAadazp7s2",
    "last_used_at": null,
    "expires_at": "2027-05-11T14:30:00Z",
    "created_at": "2026-05-11T14:30:00Z"
  }
}
```

### Revoke Token

**DELETE** `/tokens/{id}`

Requires authentication. Revokes an API token.

```bash
curl -X DELETE \
     -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/tokens/2
```

**Response (200 OK)**:
```json
{
  "success": true,
  "message": "Token revoked successfully"
}
```

## Affiliate Program

### Get Affiliate Profile

**GET** `/affiliate`

Requires authentication. Returns affiliate profile and statistics.

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/affiliate
```

**Response**:
```json
{
  "success": true,
  "data": {
    "profile": {
      "referral_code": "AFF001",
      "total_earnings": 1250.50,
      "pending_earnings": 150.00,
      "paid_earnings": 1100.50,
      "total_visits": 50000,
      "is_active": true,
      "created_at": "2026-01-01T00:00:00Z"
    },
    "stats": {
      "today_visits": 25,
      "today_earnings": 2.50,
      "this_month_visits": 1250,
      "this_month_earnings": 125.00
    },
    "tier": {
      "name": "Tier 1",
      "rate": 3.0,
      "description": "High-paying countries"
    }
  }
}
```

### Enroll in Affiliate Program

**POST** `/affiliate/enroll`

Requires authentication. Enrolls user in affiliate program.

```bash
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     https://yourdomain.com/api/v1/affiliate/enroll
```

**Response (201 Created)**:
```json
{
  "success": true,
  "data": {
    "referral_code": "AFF001",
    "is_active": true,
    "created_at": "2026-05-11T14:30:00Z"
  }
}
```

## Public Endpoints

### Active Domains

**GET** `/domains/active`

No authentication required. Returns list of active domains for short URL creation.

```bash
curl https://yourdomain.com/api/v1/domains/active
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "domain": "yourdomain.com",
      "is_default": true
    },
    {
      "id": 2,
      "domain": "short.ly",
      "is_default": false
    }
  ]
}
```

## Error Handling

### Standard Error Format

All error responses follow this format:

```json
{
  "error": "ERROR_TYPE",
  "message": "Human-readable error message",
  "errors": {
    // Optional validation errors
    "field_name": ["Error message"]
  }
}
```

### Common HTTP Status Codes

| Status | Description | Example |
|--------|-------------|---------|
| 200 | Success | Data returned successfully |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid JSON or parameters |
| 401 | Unauthorized | Invalid or missing token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Error Examples

**401 Unauthorized**:
```json
{
  "error": "Unauthorized",
  "message": "API token is required."
}
```

**422 Validation Error**:
```json
{
  "error": "Validation failed",
  "message": "The given data was invalid.",
  "errors": {
    "destination_url": ["The destination url field is required."],
    "custom_alias": ["The custom alias has already been taken."]
  }
}
```

**429 Rate Limit**:
```json
{
  "error": "Too Many Requests",
  "message": "Rate limit exceeded. Try again in 15 minutes."
}
```

## SDK Examples

### JavaScript/Node.js

```javascript
const API_BASE = 'https://yourdomain.com/api/v1';
const TOKEN = 'YOUR_64_CHARACTER_TOKEN';

class ShortLinkAPI {
  constructor(token) {
    this.token = token;
    this.headers = {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    };
  }

  async createLink(destinationUrl, options = {}) {
    const response = await fetch(`${API_BASE}/links`, {
      method: 'POST',
      headers: this.headers,
      body: JSON.stringify({
        destination_url: destinationUrl,
        ...options
      })
    });
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    return response.json();
  }

  async getLinks(page = 1, perPage = 20) {
    const response = await fetch(`${API_BASE}/links?page=${page}&per_page=${perPage}`, {
      headers: this.headers
    });
    
    return response.json();
  }
}

// Usage
const api = new ShortLinkAPI(TOKEN);

// Create a link
const link = await api.createLink('https://example.com', {
  custom_alias: 'my-link'
});
console.log(link.data.short_url);

// List links
const links = await api.getLinks();
console.log(links.data);
```

### Python

```python
import requests
from typing import Dict, List, Optional

class ShortLinkAPI:
    def __init__(self, token: str, base_url: str = "https://yourdomain.com/api/v1"):
        self.token = token
        self.base_url = base_url
        self.headers = {
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json"
        }

    def create_link(self, destination_url: str, **options) -> Dict:
        data = {"destination_url": destination_url, **options}
        response = requests.post(
            f"{self.base_url}/links",
            json=data,
            headers=self.headers
        )
        response.raise_for_status()
        return response.json()

    def get_links(self, page: int = 1, per_page: int = 20) -> Dict:
        params = {"page": page, "per_page": per_page}
        response = requests.get(
            f"{self.base_url}/links",
            params=params,
            headers=self.headers
        )
        response.raise_for_status()
        return response.json()

# Usage
api = ShortLinkAPI("YOUR_64_CHARACTER_TOKEN")

# Create a link
link = api.create_link("https://example.com", custom_alias="my-link")
print(link["data"]["short_url"])

# List links
links = api.get_links()
print(links["data"])
```

## Best Practices

### Security
- Never expose API tokens in client-side code
- Use HTTPS for all API requests
- Rotate tokens regularly
- Monitor API usage for suspicious activity

### Performance
- Implement caching for frequently accessed links
- Use pagination for large datasets
- Handle rate limits gracefully with exponential backoff
- Compress responses for large payloads

### Error Handling
- Always check HTTP status codes
- Implement retry logic for transient errors
- Log errors for debugging
- Provide meaningful error messages to users

## Support

For API support:
- Check the health endpoint: `/api/v1/health`
- Review error messages for specific issues
- Consult the admin panel for rate limit settings
- Contact support for production issues

---

**Version**: 1.0.0  
**Last Updated**: 2026-05-11  
**Base URL**: `https://yourdomain.com/api/v1`
