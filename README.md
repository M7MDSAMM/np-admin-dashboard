# Admin Dashboard (Port 8000)

Server-rendered Laravel 12 web app that provides the operational console for the Notification Platform. It handles admin authentication, session management, and orchestration of downstream services (User Service, Notification Service, Messaging Service, Template Service).

## What it does
- Admin login/logout, session lifecycle (JWT from User Service stored server-side in session).
- Admin management UI (list/create/update/toggle).
- Recipient user management UI (list, edit, preferences, devices) via User Service APIs.
- Dashboard entrypoint for notifications, templates, and messaging insights (consumes other services’ REST APIs).
- Correlation-aware logging with structured JSON and request/outbound timing.

## Architecture at a glance
- **Tech**: Laravel 12, PHP 8.2, Blade, Tailwind, Guzzle.
- **Auth**: Admin JWT issued by User Service (`/api/v1/admin/auth/login`), stored in session; `/api/v1/admin/me` used to hydrate profile.
- **Middleware**:
  - `CorrelationIdMiddleware` – ensures `X-Correlation-Id` header exists and is echoed.
  - `RequestTimingMiddleware` – logs request duration and context.
  - `HandleUnauthorizedRemoteMiddleware` – logs out on 401/403 from User Service.
  - `RequireAdminSessionMiddleware`, `RequireSuperAdminMiddleware` – gate access and role checks.
- **Logging**: Structured JSON to `storage/logs/app.log` with fields: `service`, `correlation_id`, `method`, `route`, `status_code`, `latency_ms`, `actor`. Outbound calls log `http.outbound.user_service`.
- **Health**: `GET /health` returns `{service,status,time,version}` plus `X-Correlation-Id`.

## Local setup
```bash
cp .env.example .env
php artisan key:generate
composer install
npm install && npm run build
php artisan serve --port=8000
```
Requires User Service running and accessible at `SERVICES_USER_SERVICE_BASE_URL` (see `.env`).

## Running tests
```bash
php artisan test
```

## Key environment variables
- `APP_URL` (e.g., http://localhost:8000)
- `SERVICES_USER_SERVICE_BASE_URL` (e.g., http://localhost:8001/api/v1/)
- `LOG_CHANNEL=stack`, `LOG_STACK=structured` (JSON logging)
- `APP_VERSION` (optional; falls back to git SHA for health)

## Endpoints (core)
- Web: `/login`, `/logout`, `/`, `/admins`, `/users`, `/health`
- Outbound to User Service:
  - `POST /api/v1/admin/auth/login`
  - `GET /api/v1/admin/me`
  - `GET /api/v1/users` and related CRUD/prefs/devices routes

## Observability
- Correlation ID is propagated from browser → dashboard → user-service and echoed on responses.
- Every inbound web request and outbound HTTP call is timed and logged in JSON for tracing.

## Data ownership
- Stores only admin accounts and dashboard UI/session state.
- All recipient user data, preferences, and devices are owned by User Service; this app never touches other databases directly.
