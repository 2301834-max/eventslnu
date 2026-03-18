## MID-24 — Attendance Report API

- **Type**: New feature
- **Status**: To Do
- **Owner**: Unassigned

### Summary
Create reporting endpoints for attendance (by event, date range, student, etc.).

### Acceptance criteria
- Endpoint requires appropriate authorization (admin/staff).
- Supports at least:
  - Get attendance list for an event
  - Summary counts (total checked-in, unique attendees)
  - Optional filters (date range, student id)
- Returns data in a stable JSON schema suitable for Flutter UI/export.

