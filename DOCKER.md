# Docker Setup Guide for LNU System

This document explains how to set up and run the LNU System application using Docker.

## Prerequisites

- Docker Desktop (or Docker Engine)
- Docker Compose
- Make (optional, but recommended)

## Quick Start

### Option 1: Using Make (Recommended)

```bash
# Build images and start application
make setup

# View logs
make logs

# Stop application
make down
```

### Option 2: Using Docker Compose Directly

```bash
# Build images
docker-compose build

# Start containers
docker-compose up -d

# Copy environment file
docker-compose exec app cp .env.docker .env

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --force

# Install frontend dependencies
docker-compose exec app npm install

# Build frontend assets
docker-compose exec app npm run build

# View logs
docker-compose logs -f
```

## Services

The application runs with the following services:

- **app** - PHP 8.2-FPM application server
- **nginx** - Web server (Port 80, 443)
- **db** - MySQL 8.0 database (Port 3306)
- **redis** - Redis cache (Port 6379)
- **adminer** - Database management tool (Port 8080)

## Accessing the Application

- **Web Application** - http://localhost:8000 (default, configurable via `APP_HTTP_PORT`)
- **Adminer (Database Manager)** - http://localhost:8080
  - Server: `db`
  - Username: `lnusystem`
  - Password: `lnusystem_password`
  - Database: `lnusystem`

## Common Commands

### Run Artisan Commands

```bash
make artisan migrate
make artisan seed
make artisan tinker
make artisan queue:listen
```

Or using Docker Compose directly:

```bash
docker-compose exec app php artisan <command>
```

### Run Tests

```bash
make test
```

### Clear Caches

```bash
make cache
```

### Execute Shell Commands

```bash
make bash
```

### Manage Dependencies

```bash
# Install composer packages
make composer install

# Update composer packages
make composer update

# Install npm packages
make npm install

# Run npm scripts
make npm run dev
```

## Environment Variables

The application uses `.env.docker` as the environment configuration in Docker. Key variables:

- `DB_HOST=db` - Database service name
- `REDIS_HOST=redis` - Redis service name
- `CACHE_STORE=redis` - Cache backend
- `SESSION_DRIVER=database` - Session storage

## File Volumes

The application uses the following volumes:

- Application code - Mounted from current directory
- Storage - `/var/www/html/storage` (persistent)
- Database - `db_data` volume (persistent)
- Redis - `redis_data` volume (persistent)

## Production Deployment

For production deployment, use the provided `docker-compose.prod.yml`:

```bash
docker-compose -f docker-compose.prod.yml up -d
```

This configuration:
- Sets `APP_ENV=production` and `APP_DEBUG=false`
- Uses read-only volumes for application code
- Optimizes performance with production settings
- Requires `.env` file with production credentials

## Troubleshooting

### Containers won't start

```bash
# Check logs
docker-compose logs

# Rebuild images
docker-compose build --no-cache
```

### Permission denied errors

```bash
# Fix permissions (Linux/Mac)
docker-compose exec app chown -R www-data:www-data /var/www/html
```

### Database connection errors

```bash
# Check MySQL is running
docker-compose ps

# Restart database
docker-compose restart db
```

### Clear everything and start fresh

```bash
make clean
make setup
```

## Database Backups

### Backup

```bash
docker-compose exec db mysqldump -u lnusystem -plnusystem_password lnusystem > backup.sql
```

### Restore

```bash
docker-compose exec -T db mysql -u lnusystem -plnusystem_password lnusystem < backup.sql
```

## Editing Configuration

- Web server: `docker/nginx/conf.d/app.conf`
- PHP-FPM: `Dockerfile`
- Service configuration: `docker-compose.yml`

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Documentation](https://laravel.com/docs)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

## Cleanup

To remove all containers, volumes, and networks:

```bash
make clean
```

Or manually:

```bash
docker-compose down -v
docker system prune -f
```

## Notes

- The Adminer service is included for easy database management but can be removed from `docker-compose.yml` if not needed
- For SSL/HTTPS in production, configure certificates in `docker/nginx/ssl/`
- All services are connected through the `lnusystem` network for inter-service communication
- Logs are written to the container stdout/stderr and can be viewed with `docker-compose logs`
