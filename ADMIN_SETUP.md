# Admin Dashboard Setup Guide

## Overview

A complete Admin Dashboard system has been created for managing events and student registrations in the LNU Smart Events System.

## Features

### Admin Panel Features:
- **Admin Login**: Dedicated login page for admins
- **Dashboard**: Main dashboard with statistics and recent events
- **Event Management**: 
  - View all events
  - Create new events
  - Edit existing events
  - Delete events
  - View event details with registration levels
- **Student Management**:
  - View all registered students
  - Create/register new students
  - Edit student information
  - Delete students
  - View student profiles with registration history
- **Registration Management**:
  - Approve/reject student event registrations
  - View all registrations
  - Track registration status

### Navigation Structure
The admin sidebar includes quick links to:
- 📊 Dashboard
- 📅 Event List
- ➕ Register Event
- 👥 Students
- 📝 Manage Registrations

## Default Credentials

**Admin Account:**
- Email: `admin@lnusystem.local`
- Password: `password123`

**Test Student Account:**
- Email: `user@lnusystem.local`
- Password: `password123`

## User Roles

The system supports two roles:
1. **Admin** - Full access to dashboard and all management features
2. **Student** - Can view events and register (not yet implemented in Flutter)

## Accessing the Admin Panel

### Login Page
Navigate to: `http://your-app/admin/login`

### Dashboard
After login: `http://your-app/admin/dashboard`

## Available Routes

### Admin Routes (Protected - Requires Admin Role)
- `GET /admin/dashboard` - Admin dashboard home
- `GET /admin/events` - List all events
- `GET /admin/events/create` - Create new event form
- `POST /admin/events` - Store new event
- `GET /admin/events/{event}` - View event details
- `GET /admin/events/{event}/edit` - Edit event form
- `PUT /admin/events/{event}` - Update event
- `DELETE /admin/events/{event}` - Delete event

### Student Management Routes
- `GET /admin/students` - List all students
- `GET /admin/students/create` - Create new student form
- `POST /admin/students` - Store new student
- `GET /admin/students/{student}` - View student profile
- `GET /admin/students/{student}/edit` - Edit student form
- `PUT /admin/students/{student}` - Update student
- `DELETE /admin/students/{student}` - Delete student

### Registration Management Routes
- `GET /admin/registrations` - Manage student registrations
- `PUT /admin/registrations/{registration}/approve` - Approve registration
- `PUT /admin/registrations/{registration}/reject` - Reject registration

## Database Schema

### Users Table
The users table now includes:
- `id` - Primary key
- `name` - User name
- `email` - User email
- `password` - Hashed password
- `role` - User role (admin/student)
- `created_at` - Created timestamp
- `updated_at` - Updated timestamp

### Events Table
- `id` - Primary key
- `title` - Event title
- `description` - Event description
- `start_date` - Event start datetime
- `end_date` - Event end datetime
- `location` - Event location
- `max_participants` - Maximum capacity
- `status` - Event status (upcoming/ongoing/completed)
- `event_image` - Event image path
- `created_by` - Admin who created the event
- `created_at` - Created timestamp
- `updated_at` - Updated timestamp
- `deleted_at` - Soft delete timestamp

## File Structure

### Controllers
- `app/Http/Controllers/AdminEventController.php` - Event management
- `app/Http/Controllers/AdminStudentController.php` - Student management
- `app/Http/Controllers/AuthController.php` - Updated with admin login

### Middleware
- `app/Http/Middleware/AdminMiddleware.php` - Ensures user is admin

### Views
- `resources/views/auth/admin-login.blade.php` - Admin login page
- `resources/views/admin/layout.blade.php` - Admin layout template
- `resources/views/admin/dashboard.blade.php` - Admin dashboard
- `resources/views/admin/events/` - Event management views
- `resources/views/admin/students/` - Student management views
- `resources/views/admin/registrations/` - Registration management views

### Models
- Updated `app/Models/User.php` with `role` field and helper methods
  - `isAdmin()` - Check if user is admin
  - `isStudent()` - Check if user is student

## Creating a New Admin

You can create a new admin user using the command line:

```bash
php artisan tinker
```

Then run:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'John Admin',
    'email' => 'john@example.com',
    'password' => Hash::make('secure_password'),
    'role' => 'admin',
]);
```

## Event Management

### To Create an Event:
1. Log in as admin
2. Click "Register Event" or go to Events > Create
3. Fill in event details:
   - Title
   - Description
   - Start date & time
   - End date & time
   - Location
   - Max participants
   - Status
   - Event image (optional)
4. Click "Create Event"

### To Edit an Event:
1. Go to Event List
2. Find event and click "Edit"
3. Modify details
4. Click "Update Event"

### To View Event Details:
1. Go to Event List
2. Click "View" on any event
3. See registrations and attendance

## Student Management

### To Register a Student:
1. Go to Students section
2. Click "Register Student"
3. Enter student details:
   - Full Name
   - Email
   - Password
   - Confirm Password
4. Click "Register Student"

### To View Student Profile:
1. Go to Students section
2. Click "View" on any student
3. See their registration history and attendance

## Registration Approval

### To Approve/Reject Registrations:
1. Go to "Manage Registrations"
2. View all pending student registrations
3. Click "Approve" or "Reject" buttons
4. Approved registrations will show who approved them and when

## Security Notes

- Admin routes are protected by the `AdminMiddleware`
- Users must be authenticated and have `role = 'admin'`
- All password fields are hashed before storage
- CSRF protection is enabled on all forms
- Soft deletes are used for events (data is not permanently deleted)

## Next Steps

To connect the Flutter frontend:
1. The API endpoints in `routes/api.php` provide JSON responses
2. Use the Sanctum authentication tokens
3. Implement flutter UI to consume these endpoints
4. Follow the API documentation in `API_DOCUMENTATION.md`

## Troubleshooting

### Admin can't login
- Verify user has `role = 'admin'` in database
- Check password is correct
- Ensure migrations have been run

### Events not showing up
- Run migrations: `php artisan migrate --force`
- Verify event status is one of: upcoming, ongoing, completed

### Permission denied error
- User must be logged in as admin
- Check `app/Http/Middleware/AdminMiddleware.php` is registered
- Verify middleware alias in `bootstrap/app.php`
