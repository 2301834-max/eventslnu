# LNU Event Management API - Quick Start Guide

## Prerequisites
- Docker & Docker Compose installed
- cURL or Postman for API testing
- PHP 8.2+ (for local development)

## Installation & Setup

### 1. Environment Setup
The `.env` file is already configured with:
- Database: MySQL (lnusystem database)
- Cache: Redis
- Session: File-based

### 2. Database Migrations
All migrations have been run automatically. Tables created:
- `events` - Event management
- `registrations` - User registrations
- `attendance_records` - Check-in/out logs
- `qr_codes` - QR code storage
- Default: `users`, `migrations`, `cache`, `jobs`

### 3. Running the Application

```bash
# Start all services
docker-compose up -d

# Verify containers are running
docker ps

# Run migrations (if needed)
docker exec lnusystem_app php artisan migrate --force
```

## API Access

### Base URL
```
http://localhost/api
```

### Available Services
- **Web Application**: http://localhost
- **API**: http://localhost/api
- **Database Manager (Adminer)**: http://localhost:8080
- **MySQL**: localhost:3307 (from host machine)
- **Redis**: localhost:6379

### Database Credentials
- **Host**: db
- **Port**: 3306
- **Database**: lnusystem
- **Username**: lnusystem
- **Password**: lnusystem_password

### Adminer Access
1. Go to http://localhost:8080
2. Select: MySQL
3. Server: db
4. Username: lnusystem
5. Password: lnusystem_password
6. Database: lnusystem

## Authentication

All API endpoints require authentication. You need a valid user account and Bearer token.

### Getting Started with Laravel Sanctum

```php
// Create a user account (via direct DB or custom endpoint)
$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password123'),
]);

// Generate API token
$token = $user->createToken('admin-token')->plainTextToken;

// Use token in API requests
Authorization: Bearer {token}
```

## Testing the API

### 1. Create an Event

```bash
curl -X POST http://localhost/api/events \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Tech Conference 2026",
    "description": "Annual technology conference",
    "start_date": "2026-06-15T09:00:00Z",
    "end_date": "2026-06-15T17:00:00Z",
    "location": "Convention Center, Hall A",
    "max_participants": 500
  }'
```

### 2. Publish an Event

```bash
curl -X POST http://localhost/api/events/1/publish \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Get Event Details

```bash
curl http://localhost/api/events/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. List Registrations

```bash
curl http://localhost/api/events/1/registrations \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. Approve Registration

```bash
curl -X POST http://localhost/api/events/1/registrations/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "remarks": "Approved for VIP access"
  }'
```

### 6. Check-In Attendee (Scan QR Code)

```bash
curl -X POST http://localhost/api/events/1/attendance/check-in \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "qr_code": "QR-1-1-a1b2c3d4e5f6g7h8",
    "location": "Main Hall"
  }'
```

### 7. Get Event Statistics

```bash
curl http://localhost/api/events/1/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 8. Export Attendance Report (CSV)

```bash
curl http://localhost/api/events/1/reports/attendance/csv \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o attendance_report.csv
```

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── EventController.php           # Event CRUD operations
│           ├── RegistrationController.php    # Registration management
│           ├── AttendanceController.php      # QR scanning & attendance
│           ├── StatisticsController.php      # Analytics & reports
│           └── ReportController.php          # CSV/JSON exports
│
├── Models/
│   ├── Event.php                 # Event model with relationships
│   ├── Registration.php          # Registration model
│   ├── AttendanceRecord.php       # Attendance tracking
│   └── QRCode.php                # QR code model
│
database/
└── migrations/
    ├── 2026_03_02_000001_create_events_table.php
    ├── 2026_03_02_000002_create_registrations_table.php
    ├── 2026_03_02_000003_create_attendance_records_table.php
    └── 2026_03_02_000004_create_qr_codes_table.php

routes/
└── api.php                       # All API route definitions
```

## Key Features Implemented

### Event Management ✅
- Create, read, update, delete events
- Publish, start, end, cancel events
- Capacity management
- Event status workflow

### Registration Management ✅
- User registration to events
- Admin approval/rejection
- Bulk approval operations
- Registration number generation
- Auto QR code generation on approval

### Attendance Tracking ✅
- QR code scanning
- Check-in/check-out functionality
- Location tracking during check-in
- Real-time attendance metrics
- Bulk check-in capability

### Analytics & Statistics ✅
- Total registrations & approvals
- Attendance rate calculation
- Hourly/daily attendance breakdown
- Location-based statistics
- User attendance patterns
- No-show analysis
- Real-time event metrics

### Reporting & Exports ✅
- Attendance report (CSV)
- Registrations report (CSV)
- No-show report (CSV)
- Location breakdown (CSV)
- Time analysis (CSV)
- Event summary (JSON)

## Common Issues & Solutions

### Issue: 404 Not Found on API endpoints
**Solution**: Make sure you:
1. Have a valid Bearer token in Authorization header
2. Use correct event ID in the URL
3. API endpoints are under `/api` prefix

### Issue: Database connection errors
**Solution**: Verify:
1. MySQL container is running: `docker ps`
2. Database credentials in `.env` file
3. Database migrations have been run

### Issue: QR Code not generating
**Current Implementation**: QR codes are stored as text codes (JSON format) without image generation to avoid GD dependency. You can:
- Generate QR images on the frontend using libraries like `qrcode.js`
- Use the `code` field to generate QR images when needed
- Integrate with external QR generation APIs

## Performance Tips

1. **Use Pagination**: Always paginate results for large datasets
   ```
   ?page=1&per_page=15
   ```

2. **Filter Results**: Use query parameters to reduce data
   ```
   ?status=approved&sort_by=created_at&sort_order=desc
   ```

3. **Bulk Operations**: Use bulk endpoints for multiple items
   ```
   POST /api/events/{eventId}/registrations/bulk-approve
   ```

4. **Cache Statistics**: Statistics are calculated on-demand. Cache results if queried frequently.

## Security Considerations

1. **Authentication**: Always use valid Bearer tokens
2. **Rate Limiting**: Implement rate limiting for production
3. **CORS**: Configure CORS properly for frontend integration
4. **Input Validation**: All inputs are validated server-side
5. **SQL Injection**: Uses Laravel ORM (safe from SQL injection)
6. **CSRF**: Enable CSRF protection for web routes

## API Testing with Postman

1. Import the API documentation into Postman
2. Create a collection with the base URL: `http://localhost/api`
3. Add authorization header with Bearer token
4. Test endpoints in sequence following the provided workflows

## Logs & Debugging

```bash
# View application logs
docker exec lnusystem_app tail -f storage/logs/laravel.log

# View database logs
docker logs lnusystem_db

# View nginx access logs
docker logs lnusystem_nginx
```

## Maintenance

### Backup Database
```bash
docker exec lnusystem_db mysqldump -u lnusystem -plnusystem_password lnusystem > backup.sql
```

### Clear Cache
```bash
docker exec lnusystem_app php artisan cache:clear
docker exec lnusystem_app php artisan view:clear
```

## Support

For detailed API documentation, see: [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

For troubleshooting:
1. Check container logs
2. Verify database connectivity
3. Ensure all migrations are run
4. Check Bearer token validity
