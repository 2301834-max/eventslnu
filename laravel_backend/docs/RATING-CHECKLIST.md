# Finals Rating Checklist

Project: LNU Smart Events

## Code Quality and Laravel Structure

- Clean and readable code: Controllers, models, middleware, migrations, seeders, routes, and Blade views are grouped by responsibility.
- Laravel MVC structure: Models live in `app/Models`, controllers in `app/Http/Controllers`, views in `resources/views`, routes in `routes`, migrations/seeders in `database`.
- Modular design: Admin, Super Admin, student dashboard, public API, QR registration, reports, and attendance are separated into focused controllers and views.
- Laravel features used: Eloquent relationships, factories, seeders, middleware, validation, Sanctum tokens, migrations, Blade templates, route names, and model casts.

## Infrastructure

- Docker is configured with PHP-FPM, Nginx, MySQL, Redis, and Adminer in `docker-compose.yml`.
- Production Docker compose is available in `docker-compose.prod.yml`.
- Nginx config is stored in `docker/nginx/conf.d/default.conf`.
- Environment values are documented through `.env`, `.env.testing`, Docker compose variables, and CI-generated test environments.

## Database and Roles

- Database schema is migration-based and normalized around users, events, registrations, attendance records, QR codes, activity logs, and API tokens.
- Roles are defined as `super_admin`, `admin`, and `student`.
- Role access is enforced through `AdminMiddleware`, `SuperAdminMiddleware`, route groups, and controller checks.
- Seeders provide predictable local accounts for admin and super admin testing.

## Testing and CI

- Backend test suite: `143 tests`, `463 assertions`.
- Latest local verification command: `composer test:compact`.
- Feature coverage includes authentication, admin access, event management, student management, API registrations, QR registration, attendance, profile, reports, statistics, and seeders.
- Unit coverage includes model behavior for users, events, registrations, attendance records, and QR codes.
- GitHub Actions workflow exists at `.github/workflows/backend-tests.yml`.
- Bitbucket pipeline exists at `bitbucket-pipelines.yml`.

## Scope and Feature Completeness

- Admin can manage events, registrations, students, attendance QR flows, reports, and dashboards.
- Students can register, view dashboard data, and use QR registration flows.
- Super Admin can manage admin accounts, monitor events, review activity logs, and view reports.
- Validation and error handling are present for major forms and API endpoints.

## Demonstration Checklist

- Run `composer test:compact` before presentation.
- Run `php artisan route:list` to show organized route structure.
- Show Docker setup with `docker compose config`.
- Demonstrate login flows:
  - Admin: `/admin/login`
  - Super Admin: `/super-admin/login`
  - Student/API login: `/api/login`
- Show role enforcement by trying to access admin or super admin routes with the wrong role.
- Show activity logs after creating/updating admin accounts or signing in.

## External Evidence Needed From Team

- JIRA board screenshots or export showing backlog, in-progress, done, and roadmap tasks.
- Team contribution evidence from Git commits, pull requests, or task assignments.
- Notes/screenshots showing feedback received and iterations made.
- Each member should be ready to explain their assigned feature, related code files, and one issue they solved.
