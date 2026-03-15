# Admin Dashboard (Port 8000)

Server-rendered Laravel 12 web application that provides the **operational console** for the Notification Platform. It handles admin authentication via session-stored JWTs and orchestrates all downstream services through their REST APIs.

## Responsibilities

- Admin login/logout with session lifecycle (JWT from User Service stored server-side).
- Admin management UI: list, create, update, toggle active status.
- Recipient user management UI: list, view, edit preferences, manage devices.
- Notification management: create notifications, view details, retry failed deliveries.
- Template management: list, create, edit, delete, preview rendered output.
- Correlation-aware logging with structured JSON and request/outbound timing.

## Database

**Database:** `np_admin_dashboard`

This service stores only session and cache data. All business data (admins, users, templates, notifications) is owned by the respective backend services and accessed via REST APIs.

## Web Routes

| Route | Auth | Description |
|-------|------|-------------|
| `GET /login` | Public | Login page |
| `POST /login` | Public | Authenticate via User Service |
| `POST /logout` | Admin | End session |
| `GET /` | Admin | Dashboard home |
| `GET /admins` | Super Admin | Admin management |
| `GET /users` | Admin | User management |
| `GET /users/{uuid}` | Admin | User details with preferences and devices |
| `GET /notifications` | Admin | Notification list |
| `GET /notifications/create` | Admin | Create notification form |
| `POST /notifications` | Admin | Submit notification |
| `GET /notifications/{uuid}` | Admin | Notification details |
| `POST /notifications/{uuid}/retry` | Admin | Retry notification |
| `GET /templates` | Super Admin | Template management |
| `GET /health` | Public | Service health check |

## Architecture

- **Tech**: Laravel 12, PHP 8.2, Blade, Tailwind CSS.
- **Auth**: Admin JWT issued by User Service (`POST /api/v1/admin/auth/login`), stored in session; profile hydrated via `GET /api/v1/admin/me`.
- **Service Clients**: Typed client classes for each backend service:
  - `UserServiceClient` — admin auth, user CRUD, preferences, devices.
  - `NotificationServiceClient` — notification CRUD, retry.
  - `MessagingServiceClient` — delivery tracking.
  - `TemplateManagementService` — template CRUD, render preview.
- **Middleware**:
  - `CorrelationIdMiddleware` — ensures `X-Correlation-Id` on every request.
  - `RequestTimingMiddleware` — logs request duration and context.
  - `HandleUnauthorizedRemoteMiddleware` — handles 401 from User Service (session invalidation).
  - `RequireAdminSessionMiddleware` — gates authenticated routes.
  - `RequireSuperAdminMiddleware` — gates super-admin-only views.
- **Logging**: Structured JSON with fields: `service`, `correlation_id`, `method`, `route`, `status_code`, `latency_ms`, `actor`.

## Inter-Service Dependencies

| Service | Purpose |
|---------|---------|
| User Service (8001) | Admin authentication, user/preference/device management |
| Notification Service (8002) | Notification CRUD, retry |
| Messaging Service (8003) | Delivery tracking |
| Template Service (8004) | Template CRUD, render preview |

## Local Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
npm install && npm run build
php artisan serve --port=8000
```

Configure service URLs in `.env`:

```env
USER_SERVICE_URL=http://localhost:8001/api/v1
NOTIFICATION_SERVICE_URL=http://localhost:8002/api/v1
MESSAGING_SERVICE_URL=http://localhost:8003/api/v1
TEMPLATE_SERVICE_URL=http://localhost:8004/api/v1
```

## Testing

```bash
php artisan test
```

Tests use SQLite in-memory for speed. External service calls are stubbed using Fake implementations in `tests/Support/`.

**Test coverage:** 42 tests, 135 assertions — covers auth flows, admin/user management, notification CRUD, template access, session handling, and authorization.

## Notes

- The dashboard never accesses other service databases directly — all data flows through REST APIs.
- Correlation IDs propagate from browser through dashboard to all downstream services for distributed tracing.
- The `HandleUnauthorizedRemoteMiddleware` distinguishes between User Service 401 (session expired, triggers logout) and other service 403 (permission denied, preserves session).
