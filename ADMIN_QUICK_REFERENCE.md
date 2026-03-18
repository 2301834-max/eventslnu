# Admin Dashboard Quick Reference

## Accessing the Admin System

### Main URLs
| Page | URL |
|------|-----|
| Admin Login | `/admin/login` |
| Admin Dashboard | `/admin/dashboard` |
| Event List | `/admin/events` |
| Create Event | `/admin/events/create` |
| Student List | `/admin/students` |
| Register Student | `/admin/students/create` |
| Registration Management | `/admin/registrations` |

## Test Credentials

```
Email: admin@lnusystem.local
Password: password123
```

## Admin Dashboard Features

### Dashboard Statistics
- Total Events
- Upcoming Events  
- Total Registrations
- Pending Approvals
- Recent Events Table

### Quick Actions
- Create New Event
- Register Student
- Review Registrations

### Navigation
All pages include a sidebar with links to:
- Dashboard
- Event List
- Register Event
- Students
- Manage Registrations

## Event Management

### Tasks
- ✅ View all events with status and registration count
- ✅ Create new events with title, description, dates, location, capacity
- ✅ Edit event details
- ✅ Delete events
- ✅ View event details with registrations and attendance

### Event Status Options
- `Upcoming` - Event hasn't started
- `Ongoing` - Event is currently happening
- `Completed` - Event has finished

## Student Management

### Tasks
- ✅ View all students with registration counts
- ✅ Create new student accounts
- ✅ Edit student information
- ✅ Delete student accounts
- ✅ View student profile with registration and attendance history

## Registration Management

### Tasks
- ✅ View all student registrations
- ✅ Approve pending registrations
- ✅ Reject registrations
- ✅ Track who approved/denied registrations

### Registration Status
- `Pending` - Awaiting admin approval (can approve/reject)
- `Approved` - Student can attend event (shows approver name)
- `Rejected` - Student cannot attend (shows rejecter name)

## Form Fields

### Create Event Form
```
* Title (required)
* Description (required)
* Start Date & Time (required)
* End Date & Time (required, must be after start date)
* Location (required)
* Max Participants (required, must be > 0)
* Status (required - upcoming/ongoing/completed)
- Event Image (optional, accepts jpg/png/gif)
```

### Register Student Form
```
* Full Name (required)
* Email Address (required, must be unique)
* Password (required, min 8 chars)
* Confirm Password (required, must match)
```

### Edit Student Form
```
* Full Name (required)
* Email Address (required, unique)
- Password (optional, leave blank to keep current)
- Confirm Password (optional)
```

## Color Coding

### Status Badges
| Status | Color |
|--------|-------|
| Upcoming | Blue |
| Ongoing | Green |
| Completed | Gray |
| Pending (Registration) | Yellow |
| Approved | Green |
| Rejected | Red |

## Actions

### On Event List Page
- **View** - See event details, registrations, attendance
- **Edit** - Modify event information
- **Delete** - Remove event (with confirmation)

### On Student List Page
- **View** - See student profile and history
- **Edit** - Update student information
- **Delete** - Remove student account (with confirmation)

### On Registration Page
- **Approve** - Accept student registration
- **Reject** - Deny student registration
- (Locked after action, shows who made decision)

## Tips

1. **Creating Events**: Always set appropriate max participants before opening registrations
2. **Managing Students**: Use unique emails for each student to avoid conflicts
3. **Approvals**: Review pending registrations regularly to allow students to confirm attendance
4. **Event Status**: Update event status when it starts/ends for accurate reporting
5. **Images**: Upload event images for better presentation (optional but recommended)

## Notes

- ❌ Flutter integration not yet connected
- ✅ Web dashboard fully functional
- ✅ All CRUD operations working
- ✅ Role-based access control active
- ✅ Soft deletes protect event data
