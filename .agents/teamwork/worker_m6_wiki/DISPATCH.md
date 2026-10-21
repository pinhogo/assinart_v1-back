## 2026-10-21T03:12:42Z
You are a teamwork_preview_worker agent.
Your Working Directory: c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m6_wiki
Your Identity: worker_m6_wiki

MANDATORY FIRST STEP:
Read the Original Request file at:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\ORIGINAL_REQUEST.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

TASK OBJECTIVE:
Build the complete, self-contained, interactive Neo-Brutalist API Wiki and Documentation in:
`c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\documentacao\index.html`

CONSULT THESE AUTHORITATIVE SOURCES:
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\PROJECT.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\spec_miner_specs\handoff.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\spec_miner_frontend\handoff.md`
- `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\docs\DATA_DICTIONARY.md`

REQUIREMENTS FOR `documentacao/index.html`:
1. Standalone, self-contained single-page application (HTML/CSS/JS embedded, using Google Fonts Space Grotesk and Tailwind CSS via CDN or embedded styles).
2. Neo-Brutalist design language matching Assin.art:
   - High contrast, 3px solid black borders (`border-3 border-black`), sharp shadows (`shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]`), vibrant yellow accent `#FFE600`, paper background `#F7F6F2`, dark mode or high-contrast palette toggle.
   - Interactive sidebar / sticky header navigation with quick links to all sections.
   - Search bar or instant filter for endpoints.
   - Copy-to-clipboard buttons on all code/payload snippets.
3. Content & Sections:
   - **Section 1: Architecture Overview**: Laravel 11 MVC, 2-table schema (`artists` and `buttons`), dual auth (Bearer tokens & session), CORS configuration.
   - **Section 2: Complete Route Catalog (All 28 Endpoints)**:
     - Auth: `GET /api/v1/usernames/check`, `POST /api/v1/auth/register`, `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`, `POST /api/v1/auth/forgot-password`, `POST /api/v1/auth/reset-password`, `POST /api/v1/auth/email/resend`, `POST /api/v1/auth/email/verify`.
     - Onboarding: `PUT /api/v1/onboarding/links`, `PUT /api/v1/onboarding/background`, `PUT /api/v1/onboarding/buttons`.
     - Media: `POST /api/v1/profile/avatar`, `DELETE /api/v1/profile/avatar`, `POST /api/v1/profile/cover`, `DELETE /api/v1/profile/cover`, `POST /api/v1/buttons/{id}/image`.
     - Editor: `GET /api/v1/buttons`, `POST /api/v1/buttons`, `PUT /api/v1/buttons/{id}`, `DELETE /api/v1/buttons/{id}`, `POST /api/v1/buttons/reorder`, `GET /api/v1/profile`, `PUT /api/v1/profile`.
     - Public & Redirect: `GET /{username}` (3 states: found, no_links, not_found), `GET /r/{id}` (atomic 302 redirect & throttle:60,1).
     Each endpoint must display: HTTP Method badge, Endpoint URL, Auth requirement, Headers, Query parameters, Request Body JSON schema + sample, Success response JSON with HTTP status code, Error responses (400, 401, 403, 404, 422, 429).
   - **Section 3: Interactive Data Dictionary**:
     - Full interactive tables for `artists` (all 20 columns) and `buttons` (all 15 columns) with types, defaults, nullability, constraints, and functional descriptions.
     - Auxiliary tables (`password_reset_tokens`, `sessions`, `cache`).
   - **Section 4: Visual Style Inheritance Matrix**:
     - Visual cascade diagram showing how button font, text color, and button color inherit from artist defaults when null.
     - Explanation of the 17 color palette and 17 social network types.
   - **Section 5: Frontend to Backend Mapping**:
     - Table mapping every view in `assinart_v1` (`LandingView`, `SignupView`, `LoginView`, `LinkSelectionView`, `BackgroundCustomizationView`, `ButtonCustomizationView`, `EditorView`, `PublicProfileView`) to its respective API endpoints and hooks.
   - **Section 6: Testing & Quality Assurance**:
     - Guide to running PHPUnit / Pest tests with feature coverage overview.

FILES YOU OWN EXCLUSIVELY:
- `documentacao/index.html`

OUTPUT REQUIREMENTS:
- Update progress.md in your working directory.
- Verify file created and openable in browser.
- Write handoff report to `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m6_wiki\handoff.md`.
- Send message back to orchestrator upon completion.
