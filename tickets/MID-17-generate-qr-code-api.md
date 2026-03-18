## MID-17 — Generate QR Code API

- **Type**: New feature
- **Status**: To Do
- **Owner**: Unassigned

### Summary
Create an authenticated API endpoint that generates a QR code payload for event attendance check-in.

### Acceptance criteria
- Endpoint requires authentication/authorization.
- Generates a QR payload that can be validated server-side (signed token or encrypted data).
- Payload includes at minimum: `event_id`, `issued_at`, `expires_at` (or TTL), and a nonce/unique id.
- Returns a response that Flutter can render (either raw payload string + metadata, or an image/PNG, or both).
- Uses consistent error responses (401/403/422/500).

### Notes
- Pairs with `MID-20` (expiration) and `MID-23` (duplicate scan prevention).

