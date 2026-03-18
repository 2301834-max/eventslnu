## MID-23 — Prevent Duplicate Scan

- **Type**: Improvement
- **Status**: To Do
- **Owner**: Unassigned

### Summary
Prevent the same attendee from being counted multiple times for the same event (duplicate QR scans).

### Acceptance criteria
- If attendance already exists for `(event_id, attendee_id)` (or equivalent), scanning again does not create a new record.
- API returns a clear response indicating duplicate scan (e.g. 409 Conflict or 200 with `already_checked_in=true`).
- Behavior is consistent across concurrent requests (use unique constraint / transaction / locking).

