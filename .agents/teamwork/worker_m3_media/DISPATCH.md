## 2026-10-21T03:22:05Z
You are a teamwork_preview_worker agent.
Your Working Directory: c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m3_media
Your Identity: worker_m3_media

MANDATORY FIRST STEP:
Read the Original Request file at:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\ORIGINAL_REQUEST.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

TASK OBJECTIVE:
Implement Milestone 3 (Media & Storage Management) for Assin.art v1 backend:

1. `app/Http/Controllers/Api/ProfileController.php` (all endpoints require `auth:sanctum`):
   - `show(Request $request)`: Returns authenticated artist (`GET /api/v1/profile`).
   - `update(Request $request)`: Updates artist fields (`name`, `bio`, `background_color`, `background_id`, `button_shape`, `avatar_shape`, `font_family`, `font_color`, `border_style`, `icon_variant`).
   - `uploadAvatar(Request $request)`:
     Endpoint: `POST /api/v1/profile/avatar`.
     Accepts: `avatar` file (max 5MB, mimes: `jpeg,png,jpg,webp`).
     **CRITICAL STORAGE ORPHAN RULE:** If `$artist->avatar_url` is already set, parse the relative path and physically delete the old file from the `public` storage disk (`Storage::disk('public')->delete(...)`) before saving the new file!
     Stores new file in `avatars` on `public` disk.
     Updates `$artist->avatar_url = Storage::url($path)` (or `/storage/` prefix).
     Returns 200 `{ status: "success", message: "Avatar atualizado com sucesso.", avatar_url: "..." }`.
   - `deleteAvatar(Request $request)`:
     Endpoint: `DELETE /api/v1/profile/avatar`.
     Physically deletes old avatar file from disk if exists.
     Sets `$artist->avatar_url = null`.
     Returns 200 `{ status: "success", message: "Avatar removido com sucesso." }`.
   - `uploadCover(Request $request)`:
     Endpoint: `POST /api/v1/profile/cover`.
     Accepts: `cover` file (max 10MB, mimes: `jpeg,png,jpg,webp`).
     **CRITICAL STORAGE ORPHAN RULE:** If `$artist->cover_url` is already set, physically delete the old cover file from disk before saving the new file!
     Stores new file in `covers` on `public` disk.
     Updates `$artist->cover_url = Storage::url($path)`.
     Returns 200 `{ status: "success", message: "Capa atualizada com sucesso.", cover_url: "..." }`.
   - `deleteCover(Request $request)`:
     Endpoint: `DELETE /api/v1/profile/cover`.
     Physically deletes old cover file from disk if exists.
     Sets `$artist->cover_url = null`.
     Returns 200 `{ status: "success", message: "Capa removida com sucesso." }`.
2. `routes/api_profile.php`:
   Define all profile and media routes with `auth:sanctum`:
   `GET /api/v1/profile`, `PUT /api/v1/profile`, `POST /api/v1/profile/avatar`, `DELETE /api/v1/profile/avatar`, `POST /api/v1/profile/cover`, `DELETE /api/v1/profile/cover`.

FILES YOU OWN EXCLUSIVELY:
- `app/Http/Controllers/Api/ProfileController.php`
- `routes/api_profile.php`

PHP CLI PATH:
`C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe`
Run lint check: `& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l <file>`

OUTPUT REQUIREMENTS:
- Update progress.md in your working directory.
- Verify files with PHP lint.
- Write handoff report to `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m3_media\handoff.md`.
- Send message back to orchestrator upon completion.
