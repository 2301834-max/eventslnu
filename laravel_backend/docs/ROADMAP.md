# LNU Smart Events Roadmap

## Completed

- Laravel MVC backend with Blade admin dashboards.
- Role-based authentication for super admin, admin, and students.
- Admin event management with validation.
- Student management for admin users.
- Event registration API and QR registration flow.
- Attendance tracking and QR code generation.
- Reports and exports.
- Super Admin account management, monitoring, activity logs, and reports.
- Docker setup for local containerized development.
- Automated backend tests and CI configuration.

## Current Presentation Tasks

- Keep test suite green with `composer test:compact`.
- Confirm Docker starts successfully on the presentation machine.
- Prepare JIRA screenshots or export for roadmap/process evidence.
- Prepare a short role-based demo script.
- Assign each member a feature area to explain during evaluation.

## Next Improvements

- Add browser/end-to-end tests for critical login and event flows.
- Add UI polish pass for remaining admin and student dashboard pages.
- Add CI artifacts for test reports.
- Add deployment checklist for production environment variables.
- Add database backup/restore notes for live deployments.

## Known Risks to Monitor

- Local SQLite files can become stale or corrupted; rebuild with `php artisan migrate:fresh --seed` when needed.
- Environment values must match the database driver in use.
- Demo credentials should be seeded before presentation.
