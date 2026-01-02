# Phase 11: API Development - Implementation Guide

## Overview
This document describes the RESTful API infrastructure implemented in Phase 11 of the MMB platform.

## Important: Database Configuration
All API features follow the database-agnostic approach:
- No hardcoded database names or credentials
- API keys stored in main database
- Project APIs use respective project databases
- All configuration read from files

## Completed Features

### 1. API Infrastructure

#### ApiController Base Class
Abstract base controller for all API endpoints.

**Location**: `/core/API/ApiController.php`

**Features**:
- Request parsing (JSON body, query parameters)
- Authentication via API keys
- Rate limiting integration
- CORS support
- Standardized JSON responses
- Error handling
- Pagination helper
- Field validation

**Usage Example**:
```php
namespace Core\API;

class MyApiController extends ApiController
{
    protected function route(): void
    {
        switch ($this->requestMethod) {
            case 'GET':
                $this->getResource();
                break;
            case 'POST':
                $this->createResource();
                break;
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    private function getResource(): void
    {
        // Check permission
        if (!ApiAuth::hasPermission('resource:read')) {
            $this->respondForbidden();
            return;
        }
        
        // Get data
        $data = ['example' => 'data'];
        
        // Send response
        $this->respondSuccess($data);
    }
}
```

#### ApiAuth Class
Handles API key authentication and management.

**Location**: `/core/API/ApiAuth.php`

**Features**:
- API key generation with random 64-character keys
- Key validation with caching
- Permission management
- Key expiration support
- Usage tracking (request count, last used)
- Key revocation

**Key Methods**:
```php
// Validate API key
ApiAuth::validateKey($apiKey);

// Generate new key
$key = ApiAuth::generateKey($userId, 'My App', ['proshare:read', 'proshare:write'], '2025-12-31');

// Revoke key
ApiAuth::revokeKey($keyId);

// Check permission
ApiAuth::hasPermission('proshare:upload');

// Get current user
$userId = ApiAuth::getUserId();
```

#### RateLimiter Class
Rate limiting per API key with multiple time windows.

**Location**: `/core/API/RateLimiter.php`

**Features**:
- Multi-window rate limiting (minute, hour, day)
- Configurable limits per key
- Cache-based implementation
- Automatic reset after time window
- Rate limit info retrieval

**Default Limits**:
- 60 requests per minute
- 1,000 requests per hour
- 10,000 requests per day

**Usage Example**:
```php
// Check rate limit
if (!RateLimiter::check($apiKey)) {
    // Rate limit exceeded
}

// Get remaining requests
$remaining = RateLimiter::getRemaining($apiKey, 'minute');

// Get rate limit info
$info = RateLimiter::getInfo($apiKey);
// Returns: ['minute' => [...], 'hour' => [...], 'day' => [...]]

// Reset rate limit
RateLimiter::reset($apiKey, 'minute');

// Set custom limits
RateLimiter::setLimits($apiKey, [
    'requests_per_minute' => 120,
    'requests_per_hour' => 5000
]);
```

### 2. ProShare API

#### ProShareApiController
Complete API for ProShare file sharing.

**Location**: `/core/API/ProShareApiController.php`

**Endpoints**:

1. **Upload File** - `POST /api/v1/proshare/upload`
   - Permission: `proshare:upload`
   - Body: `{ "file_content": "base64...", "filename": "doc.pdf", "expires_in": 7 }`
   - Response: `{ "success": true, "data": { "short_code": "abc123", "share_url": "..." } }`

2. **Get File Info** - `GET /api/v1/proshare/files/{shortCode}`
   - Permission: `proshare:read`
   - Response: File metadata including downloads, expiration

3. **List Files** - `GET /api/v1/proshare/files?page=1&per_page=20`
   - Permission: `proshare:read`
   - Response: Paginated list of user's files

4. **Delete File** - `DELETE /api/v1/proshare/files/{shortCode}`
   - Permission: `proshare:delete`
   - Response: Success confirmation

5. **Share File** - `POST /api/v1/proshare/files/{shortCode}/share`
   - Permission: `proshare:write`
   - Response: Public share URL and QR code URL

6. **Get Statistics** - `GET /api/v1/proshare/stats`
   - Permission: `proshare:read`
   - Response: `{ "total_files": 10, "total_downloads": 150, "storage_used": 52428800 }`

**Example Usage**:
```bash
# Upload file
curl -X POST https://example.com/api/v1/proshare/upload \
  -H "X-API-Key: mmb_abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "file_content": "SGVsbG8gV29ybGQ=",
    "filename": "hello.txt",
    "expires_in": 7
  }'

# Get file info
curl -H "X-API-Key: mmb_abc123..." \
  https://example.com/api/v1/proshare/files/abc123

# List files
curl -H "X-API-Key: mmb_abc123..." \
  "https://example.com/api/v1/proshare/files?page=1&per_page=20"

# Delete file
curl -X DELETE \
  -H "X-API-Key: mmb_abc123..." \
  https://example.com/api/v1/proshare/files/abc123
```

### 3. API Permissions

**Permission Format**: `project:action`

**ProShare Permissions**:
- `proshare:read` - View files and statistics
- `proshare:write` - Modify files, share settings
- `proshare:upload` - Upload new files
- `proshare:delete` - Delete files
- `*` - All permissions (wildcard)

**Example Permission Sets**:
```php
// Read-only access
['proshare:read']

// Upload and manage own files
['proshare:read', 'proshare:upload', 'proshare:write', 'proshare:delete']

// Full access to all APIs
['*']
```

## Database Schema

Add these tables to the main database:

```sql
-- API keys table
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `api_key` VARCHAR(100) NOT NULL UNIQUE,
    `permissions` JSON NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `expires_at` TIMESTAMP NULL,
    `last_used_at` TIMESTAMP NULL,
    `revoked_at` TIMESTAMP NULL,
    `request_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_api_key` (`api_key`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API request logs (optional, for analytics)
CREATE TABLE IF NOT EXISTS `api_request_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `api_key_id` INT UNSIGNED NOT NULL,
    `endpoint` VARCHAR(255) NOT NULL,
    `method` VARCHAR(10) NOT NULL,
    `status_code` INT NOT NULL,
    `response_time` INT NOT NULL COMMENT 'milliseconds',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`api_key_id`) REFERENCES `api_keys`(`id`) ON DELETE CASCADE,
    INDEX `idx_api_key_id` (`api_key_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    "key": "value"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["error details"]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "items": [...],
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1,
      "total_pages": 5,
      "has_more": true
    }
  }
}
```

## Authentication

### API Key Header
```
X-API-Key: mmb_abc123...
```

Or using Authorization header:
```
Authorization: Bearer mmb_abc123...
```

### Generating API Keys

From admin panel or programmatically:
```php
use Core\API\ApiAuth;

$key = ApiAuth::generateKey(
    $userId,
    'My Application',
    ['proshare:read', 'proshare:upload'],
    '2025-12-31' // Optional expiration
);

echo "Your API key: " . $key['api_key'];
```

## Rate Limiting

API responses include rate limit headers:
```
X-RateLimit-Limit-Minute: 60
X-RateLimit-Remaining-Minute: 45
X-RateLimit-Reset-Minute: 1638360000
```

When rate limit is exceeded:
```json
{
  "success": false,
  "message": "Rate limit exceeded",
  "retry_after": 30
}
```

## Security Best Practices

### API Key Security
1. Store keys securely (never in public repositories)
2. Use HTTPS for all API requests
3. Rotate keys periodically
4. Set appropriate expiration dates
5. Use minimal required permissions
6. Revoke unused keys

### Request Security
1. Validate all input data
2. Use rate limiting
3. Log suspicious activity
4. Implement IP whitelisting for sensitive operations
5. Use CORS restrictions in production

## Error Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (invalid/missing API key)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `405` - Method Not Allowed
- `429` - Too Many Requests (rate limit exceeded)
- `500` - Internal Server Error

## Testing

### Test API Key Generation
```php
use Core\API\ApiAuth;

$key = ApiAuth::generateKey(1, 'Test Key', ['*']);
echo "Test API Key: " . $key['api_key'] . "\n";
```

### Test API Endpoint
```bash
# Test with curl
curl -H "X-API-Key: your_test_key" \
  https://example.com/api/v1/proshare/stats
```

### Test Rate Limiting
```bash
# Send multiple requests quickly
for i in {1..100}; do
  curl -H "X-API-Key: your_test_key" \
    https://example.com/api/v1/proshare/stats
done
```

## Integration Examples

### JavaScript/Node.js
```javascript
const axios = require('axios');

const api = axios.create({
  baseURL: 'https://example.com/api/v1',
  headers: {
    'X-API-Key': 'mmb_abc123...',
    'Content-Type': 'application/json'
  }
});

// Upload file
const uploadFile = async (fileContent, filename) => {
  const response = await api.post('/proshare/upload', {
    file_content: Buffer.from(fileContent).toString('base64'),
    filename: filename,
    expires_in: 7
  });
  return response.data;
};

// Get statistics
const getStats = async () => {
  const response = await api.get('/proshare/stats');
  return response.data.data;
};
```

### Python
```python
import requests
import base64

API_KEY = 'mmb_abc123...'
BASE_URL = 'https://example.com/api/v1'

headers = {
    'X-API-Key': API_KEY,
    'Content-Type': 'application/json'
}

# Upload file
def upload_file(file_path, filename):
    with open(file_path, 'rb') as f:
        content = base64.b64encode(f.read()).decode()
    
    response = requests.post(
        f'{BASE_URL}/proshare/upload',
        headers=headers,
        json={
            'file_content': content,
            'filename': filename,
            'expires_in': 7
        }
    )
    return response.json()

# Get statistics
def get_stats():
    response = requests.get(
        f'{BASE_URL}/proshare/stats',
        headers=headers
    )
    return response.json()['data']
```

### PHP
```php
$apiKey = 'mmb_abc123...';
$baseUrl = 'https://example.com/api/v1';

// Upload file
function uploadFile($filePath, $filename) {
    global $apiKey, $baseUrl;
    
    $content = base64_encode(file_get_contents($filePath));
    
    $ch = curl_init("{$baseUrl}/proshare/upload");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'file_content' => $content,
        'filename' => $filename,
        'expires_in' => 7
    ]));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

## Next Steps

After Phase 11:
- [ ] Add CodeXPro API endpoints
- [ ] Add ImgTxt API endpoints
- [ ] Create API documentation portal (Swagger/OpenAPI)
- [ ] Add webhooks for events
- [ ] Create developer SDKs
- [ ] Add API analytics dashboard
- [ ] Implement API versioning (v2)
- [ ] Add OAuth2 support

## Support

For issues or questions:
1. Check API response error messages
2. Review rate limit headers
3. Verify API key permissions
4. Check error logs in `/storage/logs/`
5. Test with curl before using SDKs
6. Verify database tables are created
