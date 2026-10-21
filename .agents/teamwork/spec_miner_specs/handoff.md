# Specification Mining Report — Assin.art v1 Backend API (Laravel 11)

**Working Directory:** `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\spec_miner_specs`  
**Target:** Authoritative Backend Specification & Data Dictionary Mining  
**Sources Examined:**
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\ORIGINAL_REQUEST.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\context\tasks_backend.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\docs\DATA_DICTIONARY.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\context\data_dictionary.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\README.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\database\migrations\`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Models\`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Controllers\`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\routes\`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1\src\models\` & `src\services\` (Frontend counterpart contracts)

---

## 1. Observation

Direct observations from the authoritative specification files in `assinart_v1_backend`:

1. **Architecture & Scope (`README.md:1-20`, `tasks_backend.md:1-24`):**
   - Framework: PHP 8.2+ with Laravel 11.
   - Database: SQLite (`database/database.sqlite`) for local development, agnostic for MySQL 8+ / PostgreSQL in production.
   - Architecture: Standard Laravel MVC with direct Eloquent ORM across **2 integrated tables** (`artists` and `buttons`), avoiding DDD overhead.
   - Phase 0 is completed (initial migrations, base models, seeders, factories, data dictionary). Fases 1 a 6 are defined for complete implementation.

2. **Schema & Relationships (`database/migrations/`, `docs/DATA_DICTIONARY.md:60-140`):**
   - Table `artists`: 20 columns (`id`, `name`, `username`, `email`, `password`, `bio`, `avatar_url`, `cover_url`, `background_color`, `background_id`, `button_shape`, `avatar_shape`, `font_family`, `font_color`, `border_style`, `icon_variant`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`).
   - Table `buttons`: 15 columns (`id`, `artist_id`, `title`, `url`, `platform`, `type`, `button_color`, `text_color`, `font_family`, `image_url`, `order_index`, `clicks_count`, `is_active`, `created_at`, `updated_at`).
   - Auxiliary tables: `password_reset_tokens` (`email` PK, `token`, `created_at`), `sessions` (`id` PK, `user_id` INDEX, `ip_address`, `user_agent`, `payload`, `last_activity`), `cache` & `cache_locks`.
   - Foreign key integrity: `buttons.artist_id` references `artists.id` with `ON DELETE CASCADE`.
   - Performance index: Composite index `INDEX idx_buttons_artist_order (artist_id, order_index ASC)` and single index on `is_active`.

3. **Style Cascade (`README.md:167-186`, `Button.php:62-89`):**
   - Button visual resolution follows an intelligent fallback hierarchy:
     - Font family: `buttons.font_family ?? artists.font_family ?? 'Space Grotesk'`
     - Text color: `buttons.text_color ?? artists.font_color ?? '#000000'`
     - Button background: `buttons.button_color ?? '#FFE600'`
     - Shape & Border: uniform page-wide from `artists.button_shape` and `artists.border_style`.
     - Icon: platform SVG rendered according to `artists.icon_variant`.

4. **Username Rules (`UsernameController.php:16-109`, `tasks_backend.md:28-37`):**
   - Sanitization: trim whitespace, lowercase, remove leading `@`.
   - Format: Regex `^[a-z0-9_.-]{2,30}$`.
   - Blacklist (31 words): `admin`, `api`, `app`, `assinart`, `auth`, `cadastro`, `confirmacao-email`, `contato`, `dashboard`, `editor`, `email`, `entrar`, `esqueci-senha`, `home`, `login`, `logout`, `null`, `perfil`, `playground`, `privacidade`, `r`, `register`, `root`, `sac`, `settings`, `signin`, `signup`, `status`, `termos`, `undefined`.
   - Responses: HTTP 400 when empty; HTTP 200 with `{ "username": "...", "available": false, "reason": "invalid_format"|"reserved"|"taken" }` or `{ "available": true, "reason": null }`.

5. **Onboarding & Media Rules (`tasks_backend.md:60-116`):**
   - Step 1 (`PUT /api/v1/onboarding/links`): up to 5 initial links from 17 supported platforms, bio up to 160 chars.
   - Step 2 (`PUT /api/v1/onboarding/background`): hex `#RRGGBB` and preset background texture id.
   - Step 3 (`PUT /api/v1/onboarding/buttons`): shape `square|rounded|pill`, completes flow, returns redirect `/editor`.
   - Avatar upload: max 5MB (5120 KB), JPG/PNG/WebP, in `storage/app/public/avatars/`. **Crucial:** Old file must be deleted upon update or DELETE.
   - Cover upload: max 10MB (10240 KB), JPG/PNG/WebP, in `storage/app/public/covers/`. Old file deleted upon update or DELETE.
   - Card image upload: max 5MB in `storage/app/public/cards/` for `card_featured` button type.

6. **Buttons Drag & Drop Reorder (`tasks_backend.md:131-139`, `ButtonController.php:105-126`):**
   - Endpoint: `POST /api/v1/buttons/reorder`.
   - Payload: `{ "ordered_ids": [id1, id2, ...] }`.
   - Transaction: Atomic `DB::transaction`. Enforces ownership verification across all provided IDs.

7. **Public Profile & Click Tracker (`tasks_backend.md:142-166`, `PublicProfileController.php`, `RedirectController.php`):**
   - `GET /{username}`: single-query eager loading `Artist::where('username', $username)->with('activeButtons')->first()`. Differentiates `found` (200), `no_links` (200), `not_found` (404).
   - `GET /r/{id}`: atomic `$button->increment('clicks_count')`, HTTP 302 Found redirect to target URL, throttled 60 req/min per IP.

8. **Wiki Documentation (`ORIGINAL_REQUEST.md:5-8`, `43-48`):**
   - Dedicated folder `documentacao/` with standalone, responsive `index.html`.
   - Fully documents all routes, query parameters, request bodies, success/error payloads, data dictionary, cascade logic, and frontend view mapping.

---

## 2. Logic Chain

1. **From Requirements to Schema Design:**
   - The user request explicitly demands a link-in-bio platform with rapid development, zero DDD bloat, and fast queries.
   - Two tables (`artists` and `buttons`) completely model the application state. All global profile/theme settings reside in `artists`. All link definitions and individual overrides reside in `buttons`.
   - Foreign key `artist_id` with `CASCADE` prevents orphaned buttons when an artist account is removed.
   - Composite index `(artist_id, order_index)` guarantees single-seek sorting for both the editor and the public page.

2. **From Business Rules to Validation Layers:**
   - Usernames serve as public route slugs (`assin.art/{username}`). Any collision with frontend SPA routes (like `/editor`, `/login`, `/r`) would break web routing. Hence, a strict 31-word blacklist combined with regex `^[a-z0-9_.-]{2,30}$` is required and must return structured validation responses.
   - In Drag & Drop reordering (`/api/v1/buttons/reorder`), if an artist submits button IDs belonging to another artist, updating those records without scoping to `$request->user()->id` would allow unauthorized cross-account mutation (ID injection). Therefore, the query must strictly scope updates via `$artist->buttons()->where('id', $id)`.
   - In file uploads, replacing avatars or covers without unlinking the existing stored files creates silent disk exhaustion in `storage/app/public/`. The controller must detect if an existing file path exists on the public disk and delete it before writing the new file.

3. **From Frontend Contracts to Backend Status Codes:**
   - Frontend `PublicProfileView.tsx` defines three states: `found` (renders profile with links), `no_links` (renders profile header with empty links badge), and `not_found` (renders "Claim this @username" card with CTA to `/cadastro?username=...`).
   - If the backend returns 404 for an artist with no links, the frontend cannot render the artist's bio or custom background. Hence, the backend must return HTTP 200 with `'status': 'no_links'` when an artist exists but has no active buttons, and HTTP 404 only when the artist username does not exist.

---

## 3. Caveats

- **Sanctum vs Session Cookies:** Laravel Sanctum supports both stateful SPA session cookies (HttpOnly) and Bearer tokens. Both should be returned upon login/registration to support both browser fetch clients and automated tests.
- **SQLite Dev vs MySQL Prod Enums:** In SQLite, `enum` columns are created as `VARCHAR` check constraints. All enum values must be strictly validated at the Laravel FormRequest layer (`in:square,rounded,pill`, etc.).
- **URL Schemes in Click Tracker:** Redirection to target URLs via `redirect()->away($button->url, 302)` requires valid external URL schemes (`http://` or `https://`). The URL normalizer must enforce prefixing `https://` if omitted.
- **Symlink Requirement:** Uploaded media in `storage/app/public/` requires `php artisan storage:link` to be served by the web server via `/storage/...`.

---

## 4. Conclusion

The specification is exhaustive and consistent across `tasks_backend.md`, `DATA_DICTIONARY.md`, `README.md`, and the frontend codebase `assinart_v1`. All endpoints, payload shapes, status codes, cascade rules, and edge cases are documented below.

---

## Features Discovered

| # | Category | Feature | Description | Inputs | Outputs | Error Behavior | Discovered Via |
|---|----------|---------|-------------|--------|---------|----------------|----------------|
| 1 | Auth Core | Real-time Username Check | Validates format, reserved list, and database uniqueness for `@username` | `GET ?username=string` | `200 OK { username, available: bool, reason: null|str, message: str }` | `400 Bad Request` if param empty | `tasks_backend.md:28`, `UsernameController.php` |
| 2 | Auth Core | Artist Registration | Creates new artist account with hashed password and terms acceptance | `POST { name, username, email, password, password_confirmation, agree_terms }` | `201 Created { status: "success", token, artist }` | `422 Unprocessable` on validation error | `tasks_backend.md:38`, `RegisterRequest` |
| 3 | Auth Core | Rate-Limited Login | Authenticates artist via email/password with max 5 attempts/min/IP | `POST { email, password, remember?: bool }` | `200 OK { status: "success", token, artist }` | `401 Unauthorized` on bad credentials; `429 Too Many Requests` on rate limit | `tasks_backend.md:43`, `LoginRequest` |
| 4 | Auth Core | Session Logout | Revokes Sanctum token and invalidates web session | `POST` (auth header) | `200 OK { status: "success", message: "Sessão encerrada com sucesso." }` | `401 Unauthorized` if unauthenticated | `tasks_backend.md:45` |
| 5 | Auth Core | Session Hydration (`/me`) | Returns authenticated artist profile and theme data | `GET` (auth header) | `200 OK { status: "success", data: { id, name, username, email, ... } }` | `401 Unauthorized` if unauthenticated | `tasks_backend.md:46` |
| 6 | Auth Core | Forgot Password | Generates password reset token in `password_reset_tokens` table | `POST { email }` | `200 OK { status: "success", message: "Link de recuperação enviado com sucesso." }` | `422 Unprocessable` on invalid email | `tasks_backend.md:49` |
| 7 | Auth Core | Reset Password | Verifies token and updates artist password | `POST { token, email, password, password_confirmation }` | `200 OK { status: "success", message: "Senha redefinida com sucesso." }` | `422 Unprocessable` on invalid/expired token | `tasks_backend.md:53` |
| 8 | Auth Core | Resend Verification Email | Resends email with strict 60-second cooldown | `POST { email?: string }` (or auth) | `200 OK { status: "success", message: "E-mail reenviado." }` | `429 Too Many Requests` if within 60s cooldown | `tasks_backend.md:54`, `emailConfirmation.model.ts` |
| 9 | Auth Core | Email Verification | Marks `email_verified_at = now()` | `POST { id, hash }` / signed URL | `200 OK { status: "success", message: "E-mail verificado." }` | `403 Forbidden` if signature invalid | `tasks_backend.md:55` |
| 10 | Onboarding | Step 1: Links & Bio | Saves bio (max 160) and synchronizes up to 5 initial social buttons (0..N) | `PUT { bio: str, links: [{ platform, url, title }] }` | `200 OK { status: "success", data: { bio, buttons } }` | `422 Unprocessable` if > 5 links or bio > 160 chars | `tasks_backend.md:62`, `linkSelection.model.ts` |
| 11 | Onboarding | Step 2: Background Customization | Persists background color (`#HEX`) and preset texture/gradient ID | `PUT { background_color: str, background_id: str }` | `200 OK { status: "success", data: { background_color, background_id } }` | `422 Unprocessable` on invalid hex color format | `tasks_backend.md:71`, `backgroundCustomization.model.ts` |
| 12 | Onboarding | Step 3: Button Shape & Publish | Sets global button shape (`square`, `rounded`, `pill`) and marks profile published | `PUT { button_shape: str }` | `200 OK { status: "success", redirect_url: "/editor", data: { button_shape } }` | `422 Unprocessable` if shape not in allowed enum | `tasks_backend.md:79`, `buttonCustomization.model.ts` |
| 13 | Media Storage | Avatar Upload & Replace | Stores avatar in `storage/public/avatars`, deletes old file | `POST multipart/form-data [avatar]` (max 5MB) | `200 OK { status: "success", avatar_url: str }` | `422 Unprocessable` if > 5MB or non-image | `tasks_backend.md:91`, `ProfileController.php` |
| 14 | Media Storage | Avatar Deletion | Removes avatar file from disk, sets `avatar_url = null` | `DELETE` | `200 OK { status: "success", message: "Avatar removido." }` | `401 Unauthorized` if unauthenticated | `tasks_backend.md:98` |
| 15 | Media Storage | Cover Banner Upload | Stores banner in `storage/public/covers`, deletes old file | `POST multipart/form-data [cover]` (max 10MB) | `200 OK { status: "success", cover_url: str }` | `422 Unprocessable` if > 10MB or non-image | `tasks_backend.md:101`, `ProfileController.php` |
| 16 | Media Storage | Cover Banner Deletion | Removes banner from disk, sets `cover_url = null` | `DELETE` | `200 OK { status: "success", message: "Capa removida." }` | `401 Unauthorized` if unauthenticated | `tasks_backend.md:107` |
| 17 | Media Storage | Card Thumbnail Upload | Stores thumbnail for `card_featured` button in `storage/public/cards` | `POST multipart/form-data [image]` to `/buttons/{id}/image` (max 5MB) | `200 OK { status: "success", image_url: str }` | `404 Not Found` if button not owned; `422` on bad file | `tasks_backend.md:110` |
| 18 | Editor Core | Get Profile Settings | Fetches profile theme and metadata for the editor view | `GET /api/v1/profile` | `200 OK { status: "success", data: Artist }` | `401 Unauthorized` if unauthenticated | `ProfileController.php:15` |
| 19 | Editor Core | Update Profile Settings | Updates name, bio, username, background, font, shapes, borders | `PUT /api/v1/profile { name, bio, background_color, ... }` | `200 OK { status: "success", data: Artist }` | `422 Unprocessable` on duplicate username or bad format | `ProfileController.php:26` |
| 20 | Editor Core | List Artist Buttons | Returns all buttons for authenticated artist ordered by `order_index ASC` | `GET /api/v1/buttons` | `200 OK { status: "success", data: [ Button, ... ] }` | `401 Unauthorized` if unauthenticated | `ButtonController.php:16` |
| 21 | Editor Core | Create Button | Appends new button with calculated `order_index = max + 1` | `POST /api/v1/buttons { title, url, platform?, type?, ... }` | `201 Created { status: "success", data: Button }` | `422 Unprocessable` if title/url missing or invalid | `ButtonController.php:30` |
| 22 | Editor Core | Update Button | Modifies button title, url, platform, individual styles, active state | `PUT /api/v1/buttons/{id} { title?, url?, is_active?, ... }` | `200 OK { status: "success", data: Button }` | `404 Not Found` if button not owned; `422` on bad fields | `ButtonController.php:60` |
| 23 | Editor Core | Delete Button | Deletes button record ensuring ownership | `DELETE /api/v1/buttons/{id}` | `200 OK { status: "success", message: "Botão removido." }` | `404 Not Found` if button does not belong to user | `ButtonController.php:89` |
| 24 | Editor Core | Drag & Drop Reorder | Atomically updates `order_index` for an array of IDs in transaction | `POST /api/v1/buttons/reorder { ordered_ids: [int] }` | `200 OK { status: "success", message: "Ordem dos botões atualizada." }` | `403/404` if IDs belong to other artists; `422` if empty | `ButtonController.php:105` |
| 25 | Public Profile | Public Profile Page View | Single-query eager load of artist and active buttons with style cascade | `GET /{username}` | `200 OK { status: "found"|"no_links", data: { ... } }` | `404 Not Found { status: "not_found" }` if user missing | `PublicProfileController.php`, `tasks_backend.md:144` |
| 26 | Public Profile | Atomic Click Tracker & Redirect | Atomically increments `clicks_count` and redirects 302 to destination | `GET /r/{id}` | `HTTP 302 Found` (Location: `$button->url`) | `404 Not Found` if button id nonexistent or inactive | `RedirectController.php`, `tasks_backend.md:156` |
| 27 | Security & Infra | CORS Configuration | Allows requests from Vite dev server and official domain | `OPTIONS /api/v1/*` | `200/204` with CORS headers (`Access-Control-Allow-Origin: http://localhost:5173`) | `403 Forbidden` if unauthorized origin | `tasks_backend.md:179` |
| 28 | Wiki & Docs | Standalone HTML Documentation | Interactive Neo-Brutalist API documentation and Data Dictionary | `GET /documentacao/index.html` (static file) | Standalone HTML rendered page | N/A | `ORIGINAL_REQUEST.md:43`, `tasks_backend.md:186` |

---

## Edge Cases

| # | Feature | Input | Observed Behavior |
|---|---------|-------|-------------------|
| 1 | Username Check | `?username=@Pinho_GO ` | Sanitized to `pinho_go` (strips leading `@`, trims whitespace, converts to lowercase). |
| 2 | Username Check | `?username=admin` or `?username=editor` | Returns HTTP 200 with `{ "available": false, "reason": "reserved" }`. |
| 3 | Username Check | `?username=a` (length < 2) | Returns HTTP 200 with `{ "available": false, "reason": "invalid_format" }`. |
| 4 | Username Check | `?username=` (empty query string) | Returns HTTP 400 Bad Request with `{ "available": false, "reason": "empty" }`. |
| 5 | Username Check | `?username=artista.legal-123_ok` | Valid format, matches `^[a-z0-9_.-]{2,30}$`. |
| 6 | Auth Login | 6 failed attempts within 1 minute from same IP | HTTP 429 Too Many Requests triggered by Laravel `RateLimiter`. |
| 7 | Email Resend | Second request within 45 seconds | HTTP 429 Too Many Requests (cooldown of 60 seconds enforced). |
| 8 | Onboarding Links | Payload with 6 social platforms | Rejected with HTTP 422 (`max: 5` platforms constraint). |
| 9 | Onboarding Links | WhatsApp input with raw phone `(11) 98765-4321` | Sanitized to standard digits/DDI `5511987654321` or `https://wa.me/5511987654321`. |
| 10 | Onboarding Bio | String with 161 characters | Rejected with HTTP 422 (`max: 160` characters limit). |
| 11 | Avatar Upload | File of 5.2 MB (5324800 bytes) | Rejected with HTTP 422 (`max: 5120` KB). |
| 12 | Avatar Upload | Valid PNG uploaded when artist already has existing avatar | Old avatar file deleted from `storage/app/public/avatars/` to prevent orphans. |
| 13 | Cover Upload | File of 11 MB | Rejected with HTTP 422 (`max: 10240` KB). |
| 14 | Button Reorder | `ordered_ids` containing ID belonging to Artist B | Rejected with HTTP 403 or 404; database transaction aborts with zero modification. |
| 15 | Button Reorder | Empty array `{ "ordered_ids": [] }` | Rejected with HTTP 422. |
| 16 | Button Reorder | Duplicate IDs in array `{ "ordered_ids": [5, 5, 5] }` | Validated and rejected with HTTP 422 `distinct`. |
| 17 | Public Profile | Valid username, but all buttons have `is_active = false` | Returns HTTP 200 with `{ "status": "no_links", "data": { ... "buttons": [] } }`. |
| 18 | Public Profile | Nonexistent username `gabriel_xyz_9999` | Returns HTTP 404 with `{ "status": "not_found", "message": "Artista não encontrado." }`. |
| 19 | Public Profile | Button has `font_family = null`, artist has `font_family = 'Syne'` | Resolved cascade outputs `'font_family': 'Syne'` in button styles payload. |
| 20 | Public Profile | Button has `button_color = null`, artist has default theme | Resolved cascade outputs `'button_color': '#FFE600'`. |
| 21 | Click Redirect | Button with `is_active = false` requested at `/r/{id}` | Throws HTTP 404 (only active buttons redirect and count clicks). |
| 22 | Click Redirect | Nonexistent button ID `999999` requested at `/r/999999` | Throws HTTP 404 Not Found. |
| 23 | Click Redirect | Bot hammering `/r/{id}` 100 times in 10 seconds | Throttled by `throttle:60,1` middleware. |

---

## 5. Complete Data Dictionary and Database Schema

### Table: `artists`
- **Model:** `App\Models\Artist`
- **Primary Key:** `id` (BIGINT UNSIGNED, Auto Increment)
- **Table Constraints:** `username` UNIQUE, `email` UNIQUE

| Column | Type | Nullable | Default | Restrições / Índices | Descrição Funcional |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Não | *Auto Increment* | `PRIMARY KEY` | Identificador único do artista |
| `name` | `VARCHAR(100)` | Não | — | — | Nome real ou artístico exibido no perfil |
| `username` | `VARCHAR(50)` | Não | — | `UNIQUE`, `INDEX` | URL pública única (`assin.art/{username}`) |
| `email` | `VARCHAR(255)` | Não | — | `UNIQUE` | E-mail para login e recuperação |
| `password` | `VARCHAR(255)` | Não | — | — | Hash Bcrypt/Argon2id da senha |
| `bio` | `VARCHAR(500)` | Sim | `NULL` | — | Biografia do artista (máx 160 chars no onboarding) |
| `avatar_url` | `VARCHAR(2048)` | Sim | `NULL` | — | URL pública da foto de perfil |
| `cover_url` | `VARCHAR(2048)` | Sim | `NULL` | — | URL da imagem de capa/banner no topo |
| `background_color` | `VARCHAR(20)` | Não | `'#FFFFFF'` | — | Cor hexadecimal do plano de fundo |
| `background_id` | `VARCHAR(50)` | Sim | `NULL` | — | ID de padrão/textura neo-brutalista |
| `button_shape` | `ENUM` | Não | `'square'` | `'square', 'rounded', 'pill'` | Formato global dos botões da página |
| `avatar_shape` | `ENUM` | Não | `'circle'` | `'circle', 'square', 'rounded'` | Formato do recorte da moldura do avatar |
| `font_family` | `VARCHAR(50)` | Não | `'Space Grotesk'` | — | Tipografia global de títulos e textos |
| `font_color` | `VARCHAR(20)` | Não | `'#000000'` | — | Cor padrão das fontes textuais |
| `border_style` | `VARCHAR(30)` | Não | `'solid-3px'` | — | Estilo e espessura da borda neo-brutalista |
| `icon_variant` | `ENUM` | Não | `'default'` | `'default', 'black_outline', 'white_outline', 'none'` | Estilo dos ícones sociais dos botões |
| `email_verified_at`| `TIMESTAMP` | Sim | `NULL` | — | Data/hora de confirmação do e-mail |
| `remember_token` | `VARCHAR(100)` | Sim | `NULL` | — | Token para persistência de sessão |
| `created_at` | `TIMESTAMP` | Sim | `CURRENT_TIMESTAMP` | — | Data/hora de criação do cadastro |
| `updated_at` | `TIMESTAMP` | Sim | `CURRENT_TIMESTAMP` | — | Data/hora da última alteração cadastral/visual |

---

### Table: `buttons`
- **Model:** `App\Models\Button`
- **Primary Key:** `id` (BIGINT UNSIGNED, Auto Increment)
- **Foreign Key:** `artist_id` references `artists(id)` with `ON DELETE CASCADE`

| Column | Type | Nullable | Default | Restrições / Índices | Descrição Funcional |
|---|---|:---:|---|---|---|
| `id` | `BIGINT UNSIGNED` | Não | *Auto Increment* | `PRIMARY KEY` | Identificador único do botão |
| `artist_id` | `BIGINT UNSIGNED` | Não | — | `FK -> artists.id`, `INDEX` | Artista dono do botão (Cascade on delete) |
| `title` | `VARCHAR(100)` | Não | — | — | Texto/rótulo do botão |
| `url` | `VARCHAR(2048)` | Não | — | — | Link de destino final |
| `platform` | `VARCHAR(50)` | Sim | `'custom'` | — | Rede social ou `'custom'` |
| `type` | `ENUM` | Não | `'standard'` | `'standard', 'icon_pill', 'card_featured'` | Tipo estrutural do botão |
| `button_color` | `VARCHAR(20)` | Sim | `NULL` | — | Cor individual (se NULL, herda do tema) |
| `text_color` | `VARCHAR(20)` | Sim | `NULL` | — | Cor do texto (se NULL, herda `artists.font_color`) |
| `font_family` | `VARCHAR(50)` | Sim | `NULL` | — | Fonte individual (se NULL, herda `artists.font_family`) |
| `image_url` | `VARCHAR(2048)` | Sim | `NULL` | — | Imagem de thumbnail para `card_featured` |
| `order_index` | `INT UNSIGNED` | Não | `0` | `INDEX (artist_id, order_index)` | Posição ordinal para Drag & Drop |
| `clicks_count` | `INT UNSIGNED` | Não | `0` | — | Contador de cliques via `/r/{id}` |
| `is_active` | `BOOLEAN` | Não | `TRUE` | `INDEX` | Visibilidade ligada/desligada |
| `created_at` | `TIMESTAMP` | Sim | `CURRENT_TIMESTAMP` | — | Data/hora de inclusão |
| `updated_at` | `TIMESTAMP` | Sim | `CURRENT_TIMESTAMP` | — | Data/hora da última alteração |

---

### Auxiliary Tables (Auth & Cache)

1. **`password_reset_tokens`**:
   - `email`: `VARCHAR(255)`, `PRIMARY KEY`.
   - `token`: `VARCHAR(255)`, `NOT NULL`.
   - `created_at`: `TIMESTAMP`, `NULLABLE`.
2. **`sessions`**:
   - `id`: `VARCHAR(255)`, `PRIMARY KEY`.
   - `user_id`: `BIGINT UNSIGNED`, `NULLABLE`, `INDEX`.
   - `ip_address`: `VARCHAR(45)`, `NULLABLE`.
   - `user_agent`: `TEXT`, `NULLABLE`.
   - `payload`: `LONGTEXT`, `NOT NULL`.
   - `last_activity`: `INTEGER`, `NOT NULL`, `INDEX`.
3. **`cache` & `cache_locks`**:
   - `cache`: `key` (`VARCHAR(255)` PK), `value` (`MEDIUMTEXT`), `expiration` (`INTEGER`).
   - `cache_locks`: `key` (`VARCHAR(255)` PK), `owner` (`VARCHAR(255)`), `expiration` (`INTEGER`).

---

## 6. Frontend View to Backend Endpoint Correlation

| Frontend View / Component | Frontend Route / Hook | Backend Endpoint | Method | Purpose |
|---|---|---|---|---|
| `LandingView.tsx` | `/` | `GET /api/v1/usernames/check?username=...` | `GET` | Instant validation of `@username` hero input |
| `SignupView.tsx` | `/cadastro` | `POST /api/v1/auth/register` | `POST` | Account creation |
| `LoginView.tsx` | `/entrar` | `POST /api/v1/auth/login` | `POST` | Account authentication |
| `ForgotPasswordView.tsx` | `/esqueci-senha` | `POST /api/v1/auth/forgot-password` | `POST` | Password reset request |
| `EmailConfirmationView.tsx`| `/confirmacao-email`| `POST /api/v1/auth/email/resend` | `POST` | Resend confirmation with 60s cooldown |
| `LinkSelectionView.tsx` | `/cadastro/personalizacao/links` | `PUT /api/v1/onboarding/links` | `PUT` | Onboarding Step 1: Up to 5 links + bio |
| `BackgroundCustomizationView.tsx` | `/cadastro/personalizacao/fundo` | `PUT /api/v1/onboarding/background` | `PUT` | Onboarding Step 2: Background color & pattern |
| `ButtonCustomizationView.tsx` | `/cadastro/personalizacao/botoes`| `PUT /api/v1/onboarding/buttons` | `PUT` | Onboarding Step 3: Button shape & finish |
| `EditorView.tsx` (Links Tab)| `/editor` | `GET /api/v1/buttons` | `GET` | Load buttons list |
| `EditorView.tsx` (New Link) | `/editor` | `POST /api/v1/buttons` | `POST` | Add standard/pill/card button |
| `EditorView.tsx` (Edit Link)| `/editor` | `PUT /api/v1/buttons/{id}` | `PUT` | Update title, URL, active state, style |
| `EditorView.tsx` (Delete) | `/editor` | `DELETE /api/v1/buttons/{id}` | `DELETE` | Remove button |
| `EditorView.tsx` (Drag & Drop)| `/editor` | `POST /api/v1/buttons/reorder` | `POST` | Persist reordered array of button IDs |
| `EditorView.tsx` (Appearance)| `/editor` | `PUT /api/v1/profile` | `PUT` | Update theme colors, font, bio |
| `EditorView.tsx` (Avatar) | `/editor` | `POST /api/v1/profile/avatar` | `POST` | Upload profile photo (5MB) |
| `EditorView.tsx` (Cover) | `/editor` | `POST /api/v1/profile/cover` | `POST` | Upload header banner (10MB) |
| `PublicProfileView.tsx` | `/:username` | `GET /{username}` | `GET` | Render public profile & active buttons |
| Public Profile Links | Button click | `GET /r/{id}` | `GET` | Track click & 302 redirect to URL |

---

## 7. Interactive Documentation / Wiki Requirements (`documentacao/index.html`)

The user explicitly requested:
> *"preciso de um agente pra documentaçao e wiki, documentar as rotas bem certinho de acordo com o front, dicionario, todos parametros, pode ser uma pasta chamada documentacao com um index em html"*

### Required Specs for `documentacao/index.html`:
1. **Self-Contained & Responsive:**
   - Single autonomous HTML file with embedded styling or Google Fonts (`Space Grotesk`) and Tailwind CDN.
   - Neo-brutalist aesthetic (solid 3px black borders, vibrant accent `#FFE600`, dark mode toggle or high-contrast palette).
   - Sidebar navigation linking directly to anchor sections.
2. **Key Sections Required in Wiki:**
   - **Section 1: Architecture & Overview:** Explaining the 2-table schema, Laravel 11 MVC setup, and environment config.
   - **Section 2: Complete API Route Catalog:**
     - Organized by domain: Auth Core, Onboarding, Media & Storage, Editor & Buttons CRUD, Public Profile, Click Tracker.
     - Each route must document: Method, Path, Authentication Type (Public or Bearer Sanctum), Request Headers, Query Parameters, Request Body (JSON schema + copyable example), Success Response (HTTP 200/201/302 with sample JSON), and Error Responses (HTTP 400, 401, 403, 404, 422, 429).
   - **Section 3: Interactive Data Dictionary:**
     - Full visual tables for `artists` and `buttons` with all columns, SQL types, nullability, defaults, keys, and functional descriptions.
   - **Section 4: Visual Style Inheritance Matrix:**
     - Visual diagram illustrating how `button_color`, `text_color`, and `font_family` inherit from `artists` defaults.
   - **Section 5: Frontend-to-Backend Integration Map:**
     - Comprehensive mapping table linking every frontend view and action to its corresponding backend API call.
   - **Section 6: Testing & Quality Assurance:**
     - Guide to running PHPUnit feature tests and verifying 100% pass rate.

---

## 8. Verification Method

To independently verify all findings and validate backend implementation:

1. **Verify Migrations & Schema Integrity:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   Check that tables `artists`, `buttons`, `password_reset_tokens`, `sessions`, `cache` are created without errors and demo profile `pinhogo` is seeded.

2. **Run Existing Feature Tests:**
   ```bash
   php artisan test tests/Feature/UsernameCheckTest.php
   ```
   Confirm all 6 test cases for username availability pass (valid, sanitized, taken, reserved, too short, empty).

3. **Verify Documentation Wiki:**
   - Inspect `documentacao/index.html` in a web browser.
   - Verify all 28 features, parameters, data dictionary tables, and cascade rules are visible and interactive.

4. **Invalidation Conditions:**
   - If any column in `database/migrations/` diverges from `docs/DATA_DICTIONARY.md`, the migration must be aligned.
   - If `GET /{username}` fails to return status `'no_links'` when active buttons are empty, the frontend empty state will break.
   - If `POST /api/v1/buttons/reorder` allows updating another user's button ID, an immediate security vulnerability exists.
