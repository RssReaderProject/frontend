# Laravel RSS Reader

A Laravel application for managing and reading RSS feeds with user authentication and smart RSS item management.

## Features

- User authentication and RSS URL management
- Automated RSS item fetching via external Go service
- Smart RSS item deduplication and retention management
- Hourly automated RSS updates via cron jobs
- Modern UI with Blade templates and Tailwind CSS
- Comprehensive test suite with Dusk browser tests

## Prerequisites

- PHP 8.4 or higher
- Composer 2.0 or higher
- Node.js 22 or higher
- SQLite (default) or MySQL/PostgreSQL
- External Go RSS service (see RSS Service Integration section)

## Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd frontend
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

**Important Environment Variables:**

#### Required Variables
- `APP_KEY` - Generated automatically with `php artisan key:generate`
- `APP_URL` - Your application URL (e.g., `http://localhost:8000`)

#### RSS Service Configuration
- `RSS_SERVICE_URL` - URL of the external Go RSS service (default: `http://localhost:8080`)
- `RSS_ITEM_RETENTION_DAYS` - Number of days to keep RSS items (default: `30`)

#### Database Configuration
- `DB_CONNECTION` - Database driver (`sqlite`, `mysql`, `pgsql`)
- `DB_DATABASE` - Database name or SQLite file path

#### Optional Variables
- `APP_DEBUG` - Enable debug mode for development (`true`/`false`)
- `LOG_LEVEL` - Logging level (`debug`, `info`, `warning`, `error`)
- `CACHE_STORE` - Cache driver (`database`, `file`, `redis`)
- `SESSION_DRIVER` - Session driver (`database`, `file`, `redis`)
- `QUEUE_CONNECTION` - Queue driver (`database`, `sync`, `redis`)

### 4. Database Setup
```bash
# Create SQLite database (default)
touch database/database.sqlite

# Or for MySQL/PostgreSQL, update .env with your database credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel_rss_reader
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Optional: Seed with sample data
php artisan db:seed
```

### 5. Build Assets
```bash
npm run build
```

### 6. Start Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

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
      "description": "Article description or content",
      "rss_url": "https://source.com/feed.xml"
    }
  ]
}
```

## Development Workflow

### Available Commands

#### RSS Fetching
- `php artisan rss:fetch` - Fetch RSS items for all users
- `php artisan rss:fetch --user-id=1` - Fetch RSS items for a specific user
- `php artisan rss:fetch --stats` - Show RSS fetching statistics

#### Development
- `npm run dev` - Start Vite development server for asset compilation
- `npm run build` - Build production assets
- `npm run format` - Format code with Prettier
- `npm run format:check` - Check code formatting

#### Testing
- `php artisan test` - Run PHPUnit tests
- `php artisan dusk` - Run Dusk browser tests
- `php artisan test --parallel` - Run tests in parallel

### Scheduling

The RSS fetch command is automatically scheduled to run every hour. The scheduling is handled by the `ConsoleServiceProvider`.

### Database Management

- `php artisan migrate` - Run database migrations
- `php artisan migrate:rollback` - Rollback last migration
- `php artisan migrate:fresh` - Fresh migration with seed data
- `php artisan db:seed` - Seed database with sample data

## Docker Deployment

The application includes a Dockerfile for containerized deployment.

### Building the Docker Image
```bash
docker build -t laravel-rss-reader .
```

### Running with Docker
```bash
docker run -p 80:80 laravel-rss-reader
```

The default environment variables are configured in the Dockerfile for production deployment.

## Testing

### PHPUnit Tests
```bash
php artisan test
```

### Browser Tests (Dusk)
```bash
# Start Selenium/Chrome service
docker run -d -p 4444:4444 -p 7900:7900 selenium/standalone-chrome:latest

# Run Dusk tests
php artisan dusk
```

### CI/CD

The project includes GitHub Actions workflows for:
- Automated testing on push/PR
- Dusk browser tests
- Code quality checks

## Database Schema

The application uses SQLite by default with the following key tables:

- `users` - User accounts and authentication
- `rss_urls` - RSS feed URLs per user
- `rss_items` - RSS feed items with deduplication
- `sessions` - User sessions
- `cache` - Application cache
- `jobs` - Queue jobs

RSS items are automatically deduplicated using a unique constraint on `(user_id, link)` and old items are cleaned up based on the retention period.

## Troubleshooting

### Common Issues

1. **RSS Service Connection Failed**
   - Ensure the Go RSS service is running on the configured URL
   - Check `RSS_SERVICE_URL` in your `.env` file

2. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Ensure database server is running
   - For SQLite, check file permissions on `database/database.sqlite`

3. **Asset Compilation Errors**
   - Run `npm install` to ensure all dependencies are installed
   - Check Node.js version compatibility

4. **Permission Issues**
   - Ensure `storage/` and `bootstrap/cache/` directories are writable
   - Run `chmod -R 775 storage bootstrap/cache` if needed

### Logs

Check application logs in `storage/logs/laravel.log` for detailed error information.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).