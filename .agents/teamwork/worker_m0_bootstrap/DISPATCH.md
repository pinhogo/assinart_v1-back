# Subagent Workspace: worker_m0_bootstrap
Target: Milestone 0 - Framework Scaffolding & Bootstrap (bootstrap/app.php, artisan, public/index.php, phpunit.xml, tests/TestCase.php, personal_access_tokens migration, Artist HasApiTokens, modular routes structure).

## 2026-10-21T03:12:06Z
Implement Milestone 0 (Framework Scaffolding & Bootstrap) for the Laravel 11 backend at c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend:

1. Create `bootstrap/app.php`:
   Standard Laravel 11 bootstrap registering web, api, commands, and health: '/up'. Configure CORS middleware or withMiddleware to handle stateful api requests and CORS.
2. Create `artisan`:
   Standard Laravel 11 artisan CLI runner script.
3. Create `public/index.php`:
   Standard Laravel 11 public HTTP entrypoint.
4. Create `phpunit.xml`:
   PHPUnit 11 configuration with testsuites (Unit, Feature), and environment variables:
   APP_ENV=testing, DB_CONNECTION=sqlite, DB_DATABASE=:memory:, CACHE_STORE=array, SESSION_DRIVER=array, QUEUE_CONNECTION=sync, MAIL_MAILER=array.
5. Create `tests/TestCase.php`:
   Base test case class `Tests\TestCase` extending `Illuminate\Foundation\Testing\TestCase`.
6. Create `database/migrations/0001_01_01_000003_create_personal_access_tokens_table.php`:
   Standard Laravel Sanctum migration for `personal_access_tokens` table.
7. Update `app/Models/Artist.php`:
   Add `Laravel\Sanctum\HasApiTokens` trait to the `Artist` class so `createToken()` works for Sanctum authentication.
8. Create `config/cors.php`:
   Support `allowed_origins` => `['http://localhost:5173', 'https://assin.art', 'http://127.0.0.1:5173']`, `supports_credentials` => true.
9. Create `config/sanctum.php` & `config/filesystems.php`.
10. Modular Route Loading:
    - In `routes/api.php`: include modular files `api_auth.php`, `api_onboarding.php`, `api_profile.php`, `api_buttons.php`.
    - In `routes/web.php`: include `web_public.php`.
    - Create clean initial route stubs for these modular files.

FILES YOU OWN EXCLUSIVELY:
- `bootstrap/app.php`
- `artisan`
- `public/index.php`
- `phpunit.xml`
- `tests/TestCase.php`
- `database/migrations/0001_01_01_000003_create_personal_access_tokens_table.php`
- `app/Models/Artist.php`
- `config/cors.php`, `config/sanctum.php`, `config/filesystems.php`
- `routes/api.php`, `routes/web.php`
- `routes/api_auth.php`, `routes/api_onboarding.php`, `routes/api_profile.php`, `routes/api_buttons.php`, `routes/web_public.php`
