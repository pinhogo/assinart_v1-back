# Project: Assin.art v1 Backend API & Documentation

## Architecture
- **Framework:** PHP 8.2+ / Laravel 11 (minimal, zero DDD bloat, Eloquent ORM).
- **Database:** SQLite (`database/database.sqlite`) for local dev & testing, agnostic for MySQL 8+ / PostgreSQL.
- **Core Entities:**
  - `artists`: Consolidated user and profile entity (authentication, theme, palette, shapes, typography).
  - `buttons`: All user links and visual overrides (standard, icon_pill, card_featured).
- **Authentication:** Dual support for Laravel Sanctum Bearer tokens and SPA session guards.
- **Visual Style Inheritance Cascade:**
  - `font_family`: `buttons.font_family ?? artists.font_family ?? 'Space Grotesk'`
  - `text_color`: `buttons.text_color ?? artists.font_color ?? '#000000'`
  - `button_color`: `buttons.button_color ?? '#FFE600'`
  - Shape & Border: uniform page-wide from `artists.button_shape` and `artists.border_style`.

## Code Layout
- `app/Models/`: `Artist.php`, `Button.php`
- `app/Http/Controllers/Api/`: `AuthController.php`, `OnboardingController.php`, `ProfileController.php`, `ButtonController.php`, `UsernameController.php`
- `app/Http/Controllers/`: `PublicProfileController.php`, `RedirectController.php`, `Controller.php`
- `app/Http/Requests/`: FormRequest validation classes for all mutations
- `routes/api.php`: Public and Sanctum-protected REST endpoints (`/api/v1/...`)
- `routes/web.php`: Root status, public profile `GET /{username}`, and click redirect `GET /r/{id}`
- `bootstrap/app.php`: Laravel 11 bootstrap & routing configuration with CORS setup
- `config/`: `cors.php`, `sanctum.php`, `filesystems.php`, `database.php`
- `documentacao/index.html`: Autonomous, interactive Neo-Brutalist API wiki and documentation
- `tests/Feature/`: Comprehensive test suite

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Framework Scaffolding & Bootstrap | `bootstrap/app.php`, `artisan`, `public/index.php`, `phpunit.xml`, `tests/TestCase.php`, Sanctum migration | M0 | Survey |
| 2 | Username Availability Check | Real-time check (`GET /api/v1/usernames/check`), sanitization, 31 reserved words | M1 | Survey / R1 |
| 3 | Artist Registration | `POST /api/v1/auth/register`, password hashing, terms acceptance | M1 | Survey / R1 |
| 4 | Rate-Limited Login | `POST /api/v1/auth/login`, max 5 attempts/min, Sanctum token issue | M1 | Survey / R1 |
| 5 | Session Logout & Me | `POST /api/v1/auth/logout`, `GET /api/v1/auth/me` | M1 | Survey / R1 |
| 6 | Password Recovery & Reset | `/forgot-password`, `/reset-password` | M1 | Survey / R1 |
| 7 | Email Verification & Resend | `/email/resend` (60s cooldown), `/email/verify` | M1 | Survey / R1 |
| 8 | Onboarding Step 1: Links & Bio | `PUT /api/v1/onboarding/links` (max 5 links from 17 networks, bio <= 160) | M2 | Survey / R2 |
| 9 | Onboarding Step 2: Background | `PUT /api/v1/onboarding/background` (17 palette colors, custom hex, patterns) | M2 | Survey / R2 |
| 10 | Onboarding Step 3: Button Shape & Finish | `PUT /api/v1/onboarding/buttons` (square/rounded/pill), complete & publish | M2 | Survey / R2 |
| 11 | Avatar Upload & Replace | `POST /api/v1/profile/avatar` (max 5MB, auto-delete old avatar file) | M3 | Survey / R3 |
| 12 | Avatar Deletion | `DELETE /api/v1/profile/avatar` | M3 | Survey / R3 |
| 13 | Cover Banner Upload | `POST /api/v1/profile/cover` (max 10MB, auto-delete old banner file) | M3 | Survey / R3 |
| 14 | Cover Banner Deletion | `DELETE /api/v1/profile/cover` | M3 | Survey / R3 |
| 15 | Card Thumbnail Upload | `POST /api/v1/buttons/{id}/image` (max 5MB) | M3 | Survey / R3 |
| 16 | Button CRUD | `GET /api/v1/buttons`, `POST`, `PUT`, `DELETE` with ownership checks | M4 | Survey / R4 |
| 17 | Button Reorder (Drag & Drop) | `POST /api/v1/buttons/reorder` atomic transaction | M4 | Survey / R4 |
| 18 | Button Style Fallback Cascade | Model accessors for font, text color, button color | M4 | Survey / R4 |
| 19 | Public Profile Page | `GET /{username}` with 3 states (`found` [200], `no_links` [200], `not_found` [404]) | M5 | Survey / R5 |
| 20 | Atomic Click Tracker | `GET /r/{id}` atomic increment and 302 redirect with rate limiting | M5 | Survey / R5 |
| 21 | CORS & Security Headers | CORS for `http://localhost:5173` and `https://assin.art` | M6 | Survey / R6 |
| 22 | Interactive Documentation Wiki | Standalone `documentacao/index.html` with route catalog, data dictionary, frontend map | M6 | Survey / R7 |
| 23 | E2E Testing Suite | Comprehensive test cases across Tiers 1-4 | E2E | Survey / R6 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M0 | Scaffolding & Framework Bootstrap | `bootstrap/app.php`, `artisan`, `public/index.php`, `phpunit.xml`, `tests/TestCase.php`, Sanctum migration, `Artist.php` HasApiTokens | none | DONE |
| M1 | Auth, Account & Username Check | `AuthController`, `UsernameController`, requests, routes | M0 | DONE (28dbc3d1) |
| M2 | Onboarding & Profile Publishing | `OnboardingController`, 3 steps, validation, routes | M0, M1 | DONE (047ee253) |
| M3 | Media & Storage Management | Avatar/Cover/Card uploads, auto-deletion of old files, delete endpoints | M0 | DONE (a1210264) |
| M4 | Buttons CRUD & Drag & Drop Reorder | FormRequests, atomic reordering transaction, button cascade | M0 | DONE (075fa2c0) |
| M5 | Public Profile & Click Tracker | 3-state public profile (`found`, `no_links`, `not_found`), atomic 302 redirect | M0 | DONE (051aaccd) |
| M6 | CORS, Security & Documentation Wiki | CORS config, standalone Neo-Brutalist `documentacao/index.html` | M0 | DONE (5991f8f6) |
| E2E | E2E Automated Test Suite | Tiers 1-4 feature tests covering all 23 features | M0 | DONE (91e68a0f - 77/77 tests passed) |

## Interface Contracts
### Auth ↔ All Protected Routes
- Header: `Authorization: Bearer <token>`
- User model: `App\Models\Artist` via Sanctum guard
- Success JSON: `{ "status": "success", "data": ... }`
- Error JSON: `{ "status": "error", "message": "...", "errors": { ... } }`

### Public Profile ↔ Frontend View
- Endpoint: `GET /{username}`
- Output: `{ "status": "found"|"no_links", "data": { ...theme, buttons: [ { id, title, redirect_url, target_url, styles } ] } }`
- Missing: HTTP 404 `{ "status": "not_found", "message": "Artista não encontrado." }`

### Reordering ↔ Frontend Editor
- Endpoint: `POST /api/v1/buttons/reorder`
- Payload: `{ "ordered_ids": [id1, id2, ...] }`
- Action: Atomic update of `order_index` within `DB::transaction`
