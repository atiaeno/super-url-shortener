# ShortLink API - Quick Reference

## 🔐 Authentication
```bash
# Bearer token required for all endpoints except /health and /
Authorization: Bearer YOUR_64_CHARACTER_TOKEN
```

## 📊 Rate Limits
- **API**: 1000 req/hour
- **Tokens**: 100 req/hour  
- **Guest**: 10 req/hour

## 🚀 Base URL
```
https://yourdomain.com/api/v1
```

## ⚡ Core Endpoints

### Health Check
```bash
GET /health
# No auth required
```

### Create Link
```bash
POST /links
{
  "destination_url": "https://example.com",
  "custom_alias": "my-link",
  "expires_at": "2026-12-31T23:59:59Z"
}
```

### List Links
```bash
GET /links?page=1&per_page=20&search=example
```

### Get Link Analytics
```bash
GET /links/{id}/analytics
```

### Create API Token
```bash
POST /tokens
{
  "name": "Production API",
  "expires_days": 365
}
```

### List Tokens
```bash
GET /tokens
```

### Revoke Token
```bash
DELETE /tokens/{id}
```

## 📝 Response Format

### Success
```json
{
  "success": true,
  "data": { ... }
}
```

### Error
```json
{
  "error": "ERROR_TYPE",
  "message": "Human readable message",
  "errors": { "field": ["Error"] }
}
```

## 🔧 Common Status Codes
- `200` - Success
- `201` - Created
- `401` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `429` - Rate Limited

## 📱 SDK Examples

### JavaScript
```javascript
const response = await fetch('/api/v1/links', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    destination_url: 'https://example.com'
  })
});
const result = await response.json();
```

### Python
```python
import requests

response = requests.post(
    'https://yourdomain.com/api/v1/links',
    json={'destination_url': 'https://example.com'},
    headers={'Authorization': f'Bearer {token}'}
)
result = response.json()
```

### cURL
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"destination_url": "https://example.com"}' \
  https://yourdomain.com/api/v1/links
```

---

**Full Documentation**: [api-reference.md](api-reference.md)  
**OpenAPI Spec**: [openapi.yaml](openapi.yaml)
