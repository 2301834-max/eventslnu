## MID-22 — Attendance API

- **Type**: New feature
- **Status**: To Do
- **Owner**: Unassigned

### Summary
Implement API endpoints to record attendance when a QR code is scanned.

### Acceptance criteria
- Endpoint requires authentication/authorization (scanner user/admin as appropriate).
- Accepts QR payload (string) + scanner metadata (optional).
- Validates QR authenticity and expiration (see `MID-20`).
- Creates an attendance record linking user/student, event, scan time, and scanner identity if applicable.
- Returns a success response with attendance record details.

### Notes
- Should integrate duplicate-scan prevention (`MID-23`).

