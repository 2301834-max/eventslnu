## MID-20 — QR Expiration Logic

- **Type**: Improvement
- **Status**: To Do
- **Owner**: Unassigned

### Summary
Add server-side validation that rejects expired QR codes and enforces TTL consistently.

### Acceptance criteria
- QR validation fails if current time > `expires_at` (or TTL exceeded).
- Expiration duration is configurable (env/config).
- Expiration is enforced in the scan/attendance endpoint (not only client-side).
- Returns a clear error (e.g. 410 Gone or 422 with `QR_EXPIRED`).

### Notes
- Depends on the payload generated in `MID-17`.

