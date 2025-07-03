# Laravel RSS Reader

A Laravel application for managing and reading RSS feeds with user authentication and smart RSS item management.

## Features

- User authentication and RSS URL management
- Automated RSS item fetching via external Go service
- Smart RSS item deduplication and retention management
- Hourly automated RSS updates via cron jobs
- Modern UI with Blade templates and Tailwind CSS

## RSS Service Integration

This application integrates with an external Go RSS service that runs on `localhost:8080`. The service accepts POST requests to `/rss` with a JSON payload containing RSS URLs and returns parsed RSS items.

### RSS Service Configuration

The following environment variables control the RSS service integration:

- `RSS_SERVICE_URL`: URL of the RSS service (default: `http://localhost:8080`)
- `RSS_ITEM_RETENTION_DAYS`: Number of days to keep RSS items (default: `30`)

### RSS Service API

The Go RSS service should accept POST requests to `/rss` with the following structure:

**Request:**
```json
{
  "urls": [
    "https://example.com/feed.xml",
    "https://another-site.com/rss"
  ]
}
```

**Response:**
```json
{
  "items": [
    {
      "title": "Article Title",
      "source": "Source Name",
      "source_url": "https://source.com",
      "link": "https://source.com/article",
      "publish_date": "2024-01-01T12:00:00Z",
      "description": "Article description or content"
    }
  ]
}
```

## Commands

### RSS Fetching

- `php artisan rss:fetch` - Fetch RSS items for all users
- `php artisan rss:fetch --user-id=1` - Fetch RSS items for a specific user
- `php artisan rss:fetch --stats` - Show RSS fetching statistics

### Scheduling

The RSS fetch command is automatically scheduled to run every hour. The scheduling is handled by the `ConsoleServiceProvider`.

## Installation

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Copy `.env.example` to `.env` and configure your environment
4. Generate application key: `php artisan key:generate`
5. Run migrations: `php artisan migrate`
6. Build assets: `npm run build`
7. Start the development server: `php artisan serve`

## Docker

The application includes a Dockerfile for containerized deployment. The default environment variables are configured in the Dockerfile.

## Database

The application uses SQLite by default. RSS items are automatically deduplicated using a unique constraint on `(user_id, link)` and old items are cleaned up based on the retention period.