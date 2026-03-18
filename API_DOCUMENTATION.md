# LNU Smart Event Management & QR-Based Attendance Monitoring System
## Admin API Documentation

---

## System Overview

This is a comprehensive Admin API for managing events, registrations, attendance tracking, and generating reports for the LNU Smart Event Management System. The system utilizes QR code scanning for efficient attendance monitoring.

### Key Features
- ✅ Create and manage events
- ✅ Approve/reject user registrations
- ✅ Scan QR codes for attendance check-in/out
- ✅ Real-time attendance tracking
- ✅ Comprehensive event statistics and analytics
- ✅ Export reports (CSV, JSON)
- ✅ Attendance patterns analysis
- ✅ No-show tracking and analysis

---

## API Base URL
```
http://localhost/api
```

## Authentication
All endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

---

## Core Modules

### 1. EVENT MANAGEMENT

#### List All Events
```http
GET /api/events
```

**Query Parameters:**
- `page` (int) - Page number (default: 1)
- `per_page` (int) - Items per page (default: 15)
- `status` (string) - Filter by status: `draft`, `published`, `ongoing`, `completed`, `cancelled`
- `filter` (string) - Quick filter: `upcoming`, `ongoing`, `completed`
- `search` (string) - Search by title or location
- `sort_by` (string) - Sort field (default: `created_at`)
- `sort_order` (string) - Sort order: `asc`, `desc`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Tech Conference 2026",
      "description": "Annual tech conference",
      "start_date": "2026-03-15T09:00:00Z",
      "end_date": "2026-03-15T17:00:00Z",
      "location": "Convention Center, Hall A",
      "max_participants": 500,
      "status": "published",
      "created_by": 1,
      "current_attendees": 123,
      "timestamps": {}
    }
  ],
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

#### Create Event
```http
POST /api/events
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Summer Tech Meetup",
  "description": "Join us for an exciting tech discussion",
  "start_date": "2026-06-15T18:00:00Z",
  "end_date": "2026-06-15T21:00:00Z",
  "location": "Tech Hub Building, Room 101",
  "max_participants": 100
}
```

**Response:**
```json
{
  "success": true,
  "message": "Event created successfully",
  "data": {
    "id": 5,
    "title": "Summer Tech Meetup",
    "status": "draft",
    "created_by": 1,
    "created_at": "2026-03-02T10:00:00Z"
  }
}
```

---

#### Get Event Details
```http
GET /api/events/{eventId}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Tech Conference 2026",
    "description": "Annual tech conference",
    "start_date": "2026-03-15T09:00:00Z",
    "end_date": "2026-03-15T17:00:00Z",
    "location": "Convention Center",
    "status": "published",
    "registrations": [
      {
        "id": 1,
        "user_id": 5,
        "status": "approved",
        "registration_number": "REG-20260302120000-1234"
      }
    ],
    "attendanceRecords": [
      {
        "id": 1,
        "user_id": 5,
        "checked_in_at": "2026-03-15T09:15:00Z",
        "checked_out_at": "2026-03-15T17:30:00Z"
      }
    ]
  },
  "statistics": {
    "total_registrations": 150,
    "approved_registrations": 145,
    "pending_registrations": 5,
    "total_attended": 142,
    "attendance_rate": "97.93%",
    "is_registration_full": false
  }
}
```

---

#### Update Event
```http
PUT /api/events/{eventId}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Updated Event Title",
  "description": "Updated description",
  "location": "New Location",
  "status": "published"
}
```

---

#### Delete Event
```http
DELETE /api/events/{eventId}
```

---

#### Publish Event (Draft → Published)
```http
POST /api/events/{eventId}/publish
```

---

#### Start Event (Published → Ongoing)
```http
POST /api/events/{eventId}/start
```

---

#### End Event (Ongoing → Completed)
```http
POST /api/events/{eventId}/end
```

---

#### Cancel Event
```http
POST /api/events/{eventId}/cancel
Content-Type: application/json
```

**Request Body:**
```json
{
  "reason": "Venue unavailable due to weather"
}
```

---

### 2. REGISTRATION MANAGEMENT

#### List Registrations for an Event
```http
GET /api/events/{eventId}/registrations
```

**Query Parameters:**
- `page` (int) - Page number
- `per_page` (int) - Items per page (default: 15)
- `status` (string) - Filter: `pending`, `approved`, `rejected`, `cancelled`
- `search` (string) - Search by user name or email
- `sort_by` (string) - Sort field (default: `created_at`)
- `sort_order` (string) - Sort order

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "user_id": 5,
      "status": "approved",
      "registration_number": "REG-20260302120000-1234",
      "approved_at": "2026-03-02T11:30:00Z",
      "approved_by": 1,
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "pagination": {}
}
```

---

#### Get Single Registration
```http
GET /api/events/{eventId}/registrations/{registrationId}
```

---

#### Approve Registration
```http
POST /api/events/{eventId}/registrations/{registrationId}/approve
Content-Type: application/json
```

**Request Body (Optional):**
```json
{
  "remarks": "Approved for VIP access"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration approved successfully",
  "data": {
    "id": 1,
    "status": "approved",
    "approved_by": 1
  },
  "qr_code": {
    "id": 1,
    "code": "QR-1-1-a1b2c3d4e5f6g7h8",
    "qr_image_data": "{\"type\": \"text\", \"value\": \"QR-1-1-a1b2c3d4e5f6g7h8\"}"
  }
}
```

---

#### Reject Registration
```http
POST /api/events/{eventId}/registrations/{registrationId}/reject
Content-Type: application/json
```

**Request Body:**
```json
{
  "remarks": "Duplicate registration found"
}
```

---

#### Cancel Registration
```http
POST /api/events/{eventId}/registrations/{registrationId}/cancel
```

---

#### Bulk Approve Registrations
```http
POST /api/events/{eventId}/registrations/bulk-approve
Content-Type: application/json
```

**Request Body:**
```json
{
  "registration_ids": [1, 2, 3, 4, 5],
  "remarks": "Batch approval"
}
```

**Response:**
```json
{
  "success": true,
  "message": "5 registrations approved successfully",
  "approved_count": 5
}
```

---

### 3. ATTENDANCE TRACKING & QR CODE SCANNING

#### Scan QR Code (Check-In)
```http
POST /api/events/{eventId}/attendance/check-in
Content-Type: application/json
```

**Request Body:**
```json
{
  "qr_code": "QR-1-1-a1b2c3d4e5f6g7h8",
  "location": "Main Hall"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Check-in successful",
  "data": {
    "id": 1,
    "user_id": 5,
    "event_id": 1,
    "checked_in_at": "2026-03-15T09:15:00Z",
    "qr_code_reference": "QR-1-1-a1b2c3d4e5f6g7h8",
    "check_in_location": "Main Hall",
    "user": {
      "id": 5,
      "name": "John Doe"
    }
  }
}
```

---

#### Verify QR Code (Without Check-In)
```http
POST /api/events/{eventId}/attendance/verify-qr
Content-Type: application/json
```

**Request Body:**
```json
{
  "qr_code": "QR-1-1-a1b2c3d4e5f6g7h8"
}
```

**Response:**
```json
{
  "success": true,
  "message": "QR code is valid",
  "valid": true,
  "data": {
    "qr_code_id": 1,
    "status": "active",
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "registration_number": "REG-20260302120000-1234",
    "is_active": true,
    "expires_at": "2026-04-01T00:00:00Z"
  }
}
```

---

#### Check-Out Attendee
```http
POST /api/events/{eventId}/attendance/{attendanceId}/check-out
```

**Response:**
```json
{
  "success": true,
  "message": "Check-out successful",
  "data": {
    "id": 1,
    "checked_in_at": "2026-03-15T09:15:00Z",
    "checked_out_at": "2026-03-15T17:30:00Z"
  }
}
```

---

#### Get Event Attendance Records
```http
GET /api/events/{eventId}/attendance
```

**Query Parameters:**
- `status` (string) - Filter: `checked_in`, `checked_out`
- `from_date` (string) - Start date
- `to_date` (string) - End date
- `search` (string) - Search by user name/email
- `page`, `per_page`, `sort_by`, `sort_order`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "checked_in_at": "2026-03-15T09:15:00Z",
      "checked_out_at": "2026-03-15T17:30:00Z",
      "check_in_location": "Main Hall"
    }
  ],
  "summary": {
    "total_attended": 142,
    "currently_present": 8,
    "checked_out": 134
  }
}
```

---

#### Bulk Check-In
```http
POST /api/events/{eventId}/attendance/bulk-check-in
Content-Type: application/json
```

**Request Body:**
```json
{
  "qr_codes": [
    "QR-1-1-a1b2c3d4e5f6g7h8",
    "QR-1-2-b2c3d4e5f6g7h8i9",
    "QR-1-3-c3d4e5f6g7h8i9j0"
  ],
  "location": "Main Hall"
}
```

---

### 4. EVENT STATISTICS & ANALYTICS

#### Get Complete Event Statistics
```http
GET /api/events/{eventId}/statistics
```

**Response:**
```json
{
  "success": true,
  "event": {
    "id": 1,
    "title": "Tech Conference 2026",
    "location": "Convention Center",
    "start_date": "2026-03-15T09:00:00Z",
    "end_date": "2026-03-15T17:00:00Z",
    "status": "completed"
  },
  "registration_stats": {
    "total": 150,
    "approved": 145,
    "pending": 3,
    "rejected": 2,
    "cancelled": 0,
    "rejection_rate": "1.33%"
  },
  "attendance_stats": {
    "total_attended": 142,
    "approved_count": 145,
    "attendance_rate": "97.93%",
    "average_time_spent_minutes": 480,
    "currently_present": 0,
    "peak_check_in_hour": "09:00"
  },
  "event_info": {
    "capacity": 500,
    "is_full": false,
    "available_slots": 355
  }
}
```

---

#### Get Hourly Attendance Breakdown
```http
GET /api/events/{eventId}/statistics/hourly-attendance?from_date=2026-03-15&to_date=2026-03-15
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "hour": "2026-03-15 09:00",
      "count": 45
    },
    {
      "hour": "2026-03-15 10:00",
      "count": 38
    }
  ]
}
```

---

#### Get Daily Attendance Breakdown
```http
GET /api/events/{eventId}/statistics/daily-attendance
```

---

#### Get Location-Based Statistics
```http
GET /api/events/{eventId}/statistics/location-stats
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "check_in_location": "Main Hall",
      "count": 95
    },
    {
      "check_in_location": "VIP Room",
      "count": 45
    }
  ]
}
```

---

#### Get User Attendance Patterns
```http
GET /api/events/{eventId}/statistics/user-patterns
```

---

#### Get Registration vs Attendance Comparison
```http
GET /api/events/{eventId}/statistics/comparison
```

**Response:**
```json
{
  "success": true,
  "summary": {
    "total_approved": 145,
    "attended": 142,
    "not_attended": 3,
    "attendance_rate": "97.93%"
  },
  "details": [
    {
      "user_id": 5,
      "user_name": "John Doe",
      "registration_number": "REG-20260302120000-1234",
      "attended": true,
      "check_in_time": "2026-03-15T09:15:00Z",
      "check_out_time": "2026-03-15T17:30:00Z"
    }
  ]
}
```

---

#### Get No-Show Analysis
```http
GET /api/events/{eventId}/statistics/no-shows
```

**Response:**
```json
{
  "success": true,
  "summary": {
    "total_no_shows": 3,
    "no_show_rate": "2.07%"
  },
  "no_show_list": [
    {
      "user_id": 10,
      "user_name": "Jane Smith",
      "user_email": "jane@example.com",
      "registration_number": "REG-20260302120000-5678",
      "approved_at": "2026-03-01T10:00:00Z"
    }
  ]
}
```

---

#### Get Real-Time Event Metrics
```http
GET /api/events/{eventId}/statistics/realtime
```

**Response:**
```json
{
  "success": true,
  "event_id": 1,
  "event_title": "Tech Conference 2026",
  "is_ongoing": true,
  "metrics": {
    "currently_present": 8,
    "total_attended_so_far": 142,
    "pending_approvals": 3,
    "check_ins_last_hour": 15,
    "check_ins_last_minute": 2
  },
  "timestamp": "2026-03-15T14:30:00Z"
}
```

---

### 5. REPORTS & EXPORTS

#### Export Attendance Report (CSV)
```http
GET /api/events/{eventId}/reports/attendance/csv?from_date=2026-03-15&to_date=2026-03-15
```

**Returns:** CSV file download
```
Event,Registration Number,User Name,User Email,Check-In Time,Check-Out Time,Duration (minutes),Location,Attendance Status
Tech Conference 2026,REG-20260302120000-1234,John Doe,john@example.com,2026-03-15 09:15:00,2026-03-15 17:30:00,495,Main Hall,Checked Out
```

---

#### Export Registrations Report (CSV)
```http
GET /api/events/{eventId}/reports/registrations/csv?status=approved
```

---

#### Export No-Show Report (CSV)
```http
GET /api/events/{eventId}/reports/no-shows/csv
```

---

#### Export Location Breakdown (CSV)
```http
GET /api/events/{eventId}/reports/location/csv
```

---

#### Export Time Analysis (CSV)
```http
GET /api/events/{eventId}/reports/time-analysis/csv
```

---

#### Export Event Summary (JSON)
```http
GET /api/events/{eventId}/reports/summary
```

**Response:**
```json
{
  "success": true,
  "data": {
    "report_type": "Event Summary Report",
    "generated_at": "2026-03-02T14:30:00Z",
    "event": {
      "id": 1,
      "title": "Tech Conference 2026",
      "location": "Convention Center"
    },
    "registration_summary": {
      "total_registrations": 150,
      "approved": 145,
      "pending": 3,
      "rejected": 2
    },
    "attendance_summary": {
      "total_attended": 142,
      "attendance_rate": "97.93%",
      "no_show_count": 3
    }
  }
}
```

---

## Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (missing/invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Unprocessable Entity
- `500` - Server Error

---

## Database Schema

### Events Table
- `id` (PK)
- `title` (string, unique)
- `description` (text)
- `start_date` (datetime)
- `end_date` (datetime)
- `location` (string)
- `max_participants` (int)
- `status` (enum: draft, published, ongoing, completed, cancelled)
- `event_image` (string, nullable)
- `created_by` (FK → users)
- `current_attendees` (int)
- `timestamps`
- `soft deletes`

### Registrations Table
- `id` (PK)
- `event_id` (FK → events)
- `user_id` (FK → users)
- `status` (enum: pending, approved, rejected, cancelled)
- `registration_number` (string, unique)
- `remarks` (text, nullable)
- `approved_at` (datetime, nullable)
- `approved_by` (FK → users, nullable)
- `unique(event_id, user_id)`
- `timestamps`
- `soft deletes`

### Attendance Records Table
- `id` (PK)
- `registration_id` (FK → registrations)
- `event_id` (FK → events)
- `user_id` (FK → users)
- `checked_in_at` (datetime)
- `checked_out_at` (datetime, nullable)
- `qr_code_reference` (string)
- `check_in_location` (string, nullable)
- `timestamps`

### QR Codes Table
- `id` (PK)
- `registration_id` (FK → registrations)
- `event_id` (FK → events)
- `code` (string, unique)
- `status` (enum: active, scanned, expired, revoked)
- `generated_at` (datetime)
- `expires_at` (datetime, nullable)
- `scanned_at` (datetime, nullable)
- `qr_image_data` (text, nullable)
- `timestamps`

---

## Example Workflows

### Workflow 1: Create Event and Manage Registrations

1. **Create Event**
   ```http
   POST /api/events
   ```

2. **Publish Event**
   ```http
   POST /api/events/{eventId}/publish
   ```

3. **Get Registrations**
   ```http
   GET /api/events/{eventId}/registrations
   ```

4. **Approve Registration**
   ```http
   POST /api/events/{eventId}/registrations/{registrationId}/approve
   ```

---

### Workflow 2: Conduct Event Check-In

1. **Get Event Details**
   ```http
   GET /api/events/{eventId}
   ```

2. **Start Event**
   ```http
   POST /api/events/{eventId}/start
   ```

3. **Scan QR Code for Check-In**
   ```http
   POST /api/events/{eventId}/attendance/check-in
   ```

4. **Check-Out Attendee**
   ```http
   POST /api/events/{eventId}/attendance/{attendanceId}/check-out
   ```

5. **Get Real-Time Metrics**
   ```http
   GET /api/events/{eventId}/statistics/realtime
   ```

---

### Workflow 3: Generate Reports

1. **Get Event Statistics**
   ```http
   GET /api/events/{eventId}/statistics
   ```

2. **Export Attendance Report**
   ```http
   GET /api/events/{eventId}/reports/attendance/csv
   ```

3. **Export Summary**
   ```http
   GET /api/events/{eventId}/reports/summary
   ```

---

## Implementation Notes

- All timestamps are in UTC (ISO 8601 format)
- QR codes expire after 30 days by default
- Registrations must be approved before attendees can check-in
- Bulk operations are available for approval and check-in
- CSV exports can be filtered by date range
- No-show analysis compares approved registrations with actual attendance
- Location tracking is optional during check-in

---

## Future Enhancements

- PDF report generation
- Email notifications for registrations
- SMS notifications for check-ins
- Mobile app integration
- Advanced analytics dashboards
- Attendee feedback surveys
- Integration with calendar systems
- QR code customization options
