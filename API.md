# Locket API Documentation

The Locket API provides programmatic access to link sharing and social feed functionality. All API endpoints require authentication using a personal access token.

## Authentication

All API requests must include an Authorization header with a Bearer token and Accept header for JSON responses:

```http
Authorization: Bearer YOUR_API_TOKEN
Accept: application/json
```

### Getting an API Token

1. Log in to your Locket account
2. Navigate to Settings → Profile (`/settings/profile`)
3. Create a new personal access token in the API Tokens section
4. Copy the token immediately (it won't be shown again)

## Base URL

```
Local: http://locket.test/api
Production: https://locket.laravel.cloud/api
```

## Endpoints

### User

#### Get Current User

```http
GET /api/user
```

Returns the authenticated user's information.

**Response:**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00Z"
}
```

### Links

#### Get Recent Links

```http
GET /api/links/recent
```

Retrieves the most recently added links across all users.

**Query Parameters:**

- `limit` (optional, integer): Number of links to return (1-25, default: 10)

**Response:**

```json
{
    "data": [
        {
            "id": 123,
            "url": "https://example.com/article",
            "title": "Interesting Article",
            "domain": "example.com",
            "created_at": "2024-01-01T12:00:00Z",
            "user": {
                "id": 1,
                "name": "John Doe"
            }
        }
    ],
    "meta": {
        "count": 10,
        "limit": 10
    }
}
```

#### Get Trending Links

```http
GET /api/links/trending
```

Retrieves links that are popular today based on bookmark activity.

**Query Parameters:**

- `limit` (optional, integer): Number of links to return (1-25, default: 10)

**Response:**

```json
{
    "data": [
        {
            "id": 456,
            "url": "https://trending.com/post",
            "title": "Popular Post",
            "domain": "trending.com",
            "bookmark_count": 42,
            "created_at": "2024-01-01T08:00:00Z"
        }
    ],
    "meta": {
        "count": 10,
        "limit": 10
    }
}
```

#### Add a Link

```http
POST /api/links
```

Adds a new link to your reading list and creates a status update.

**Request Body:**

```json
{
    "url": "https://example.com/article",
    "thoughts": "Great article about Laravel development",
    "category_hint": "read"
}
```

**Parameters:**

- `url` (required, string): The URL to add (max 2048 characters)
- `thoughts` (optional, string): Your notes about the link (max 2000 characters, saved privately)
- `category_hint` (optional, string): Category suggestion - one of: `read`, `reference`, `watch`, `tools`
    - `read`: Articles, blog posts
    - `reference`: Documentation, specifications
    - `watch`: Videos
    - `tools`: Libraries, services

**Response (201 Created):**

```json
{
    "data": {
        "link": {
            "id": 789,
            "url": "https://example.com/article",
            "title": "Article Title",
            "domain": "example.com"
        },
        "user_link": {
            "id": 321,
            "bookmarked_at": "2024-01-01T14:30:00Z"
        },
        "status": {
            "id": 654,
            "message": "Added to reading list: Article Title"
        },
        "note": {
            "id": 987,
            "content": "Great article about Laravel development"
        }
    },
    "meta": {
        "already_bookmarked": false
    }
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
    "error": "Failed to add link",
    "message": "The URL field is required"
}
```

### Statuses

#### Get Recent Statuses

```http
GET /api/statuses/recent
```

Retrieves recent status messages from all Locket users.

**Query Parameters:**

- `limit` (optional, integer): Number of statuses to return (1-50, default: 10)

**Response:**

```json
{
    "data": [
        {
            "id": 111,
            "message": "Just bookmarked: Interesting Article",
            "created_at": "2024-01-01T15:00:00Z",
            "user": {
                "id": 2,
                "name": "Jane Smith"
            },
            "link": {
                "id": 123,
                "url": "https://example.com/article",
                "title": "Interesting Article"
            }
        }
    ],
    "meta": {
        "count": 10,
        "limit": 10
    }
}
```

## Rate Limiting

API requests are subject to rate limiting. Check the response headers for current limits:

- `X-RateLimit-Limit`: Maximum requests per minute
- `X-RateLimit-Remaining`: Requests remaining in current window
- `X-RateLimit-Reset`: Unix timestamp when the rate limit resets

## Error Responses

The API uses standard HTTP status codes:

- `200 OK`: Request succeeded
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

Error responses include a JSON body with details:

```json
{
    "error": "Error type",
    "message": "Human-readable error message"
}
```

## Example Usage

### cURL

```bash
# Get recent links
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
  https://locket.test/api/links/recent?limit=5

# Add a new link
curl -X POST \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"url":"https://laravel.com/docs","thoughts":"Laravel documentation","category_hint":"reference"}' \
  https://locket.test/api/links
```
