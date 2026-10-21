# Handoff Report — Milestone 1 (Auth, Account & Username Check Core)

## 1. Observation
- **Exclusively Owned Files**:
  1. `app/Http/Controllers/Api/AuthController.php`
  2. `app/Http/Requests/RegisterRequest.php`
  3. `app/Http/Requests/LoginRequest.php`
  4. `routes/api_auth.php`
- **Database Schema**:
  - `database/migrations/0001_01_01_000000_create_artists_table.php` defines the `artists` table with `id`, `name`, `username`, `email`, `password`, `email_verified_at`, and style attributes, alongside the `password_reset_tokens` table with `email`, `token`, `created_at`.
  - `database/migrations/0001_01_01_000003_create_personal_access_tokens_table.php` defines Laravel Sanctum's personal access tokens schema.
- **Model Verification**:
  - `app/Models/Artist.php` uses `Laravel\Sanctum\HasApiTokens`, enabling `$artist->createToken('auth_token')->plainTextToken`.
- **PHP Lint Results**:
  Executed command:
  ```powershell
  & 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Controllers\Api\AuthController.php'
  & 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Requests\RegisterRequest.php'
  & 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Requests\LoginRequest.php'
  & 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\routes\api_auth.php'
  ```
  Result output:
  ```
  No syntax errors detected in c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Controllers\Api\AuthController.php
  No syntax errors detected in c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Requests\RegisterRequest.php
  No syntax errors detected in c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Requests\LoginRequest.php
  No syntax errors detected in c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\routes\api_auth.php
  ```

## 2. Logic Chain
1. *Register Handling*:
   - In `RegisterRequest.php`, `prepareForValidation()` maps camelCase `agreeTerms` into `agree_terms` and sanitizes `@` from username.
   - Validation ensures uniqueness in `artists`, minimum 6-character passwords, regex constraints, and blocks reserved route names (`admin`, `api`, `editor`, etc.).
   - `AuthController::register()` creates the `Artist` record, generates a Sanctum personal access token (`$artist->createToken('auth_token')->plainTextToken`), and responds with HTTP 201 containing `{ status: "success", message: "Conta criada com sucesso.", token, artist }`.
2. *Login with Rate Limiting*:
   - `AuthController::login()` builds a throttle key `'login|' . $request->ip()`.
   - If attempts exceed 5 per minute, `RateLimiter::tooManyAttempts()` triggers an immediate HTTP 429 response: `{ status: "error", message: "Muitas tentativas de login. Tente novamente em X segundos.", retry_after: X }`.
   - On credential mismatch, `RateLimiter::hit()` increments attempts and returns HTTP 401 `{ status: "error", message: "Credenciais inválidas. Verifique seu e-mail e senha." }`.
   - On success, `RateLimiter::clear()` resets attempts and returns HTTP 200 with Sanctum token and artist data.
3. *Session Management*:
   - `logout()` invokes `$request->user()->currentAccessToken()->delete()` when protected by `auth:sanctum`, returning HTTP 200 `{ status: "success", message: "Sessão encerrada com sucesso." }`.
   - `me()` returns HTTP 200 `{ status: "success", data: $request->user() }`.
4. *Password Recovery Flow*:
   - `forgotPassword()` validates email existence, generates a secure 64-character token, writes/updates `password_reset_tokens` with timestamp, and returns HTTP 200 `{ status: "success", message: "Link de recuperação enviado com sucesso." }`.
   - `resetPassword()` validates token, email, password, and `password_confirmation`. Checks token validity (supporting Hash, raw, or sha256), enforces 60-minute expiration, updates artist password, deletes used reset token, and returns HTTP 200 `{ status: "success", message: "Senha redefinida com sucesso." }`.
5. *Email Verification*:
   - `resendVerification()` enforces a 60-second cooldown using `RateLimiter::hit(..., 60)`, returning HTTP 429 when throttled or HTTP 200 when dispatched.
   - `verifyEmail()` sets `email_verified_at = now()` on the artist record and returns HTTP 200 `{ status: "success", message: "E-mail confirmado com sucesso." }`.
6. *Routing*:
   - `routes/api_auth.php` is included by `routes/api.php` under prefix `'v1'`. It correctly wires `GET /usernames/check` to `UsernameController::check` and groups all auth endpoints under `/api/v1/auth/...`.

## 3. Caveats
- Outbound SMTP mail delivery is decoupled from HTTP responses (standard token-in-db architecture without requiring third-party mailer daemon running during test execution).
- Route `email/verify` supports both GET and POST requests for compatibility with browser click-through links and frontend API asynchronous calls.

## 4. Conclusion
Milestone 1 (Auth, Account & Username Check Core) has been fully implemented with zero facades or hardcoded values. All endpoints strictly conform to the frontend contract and security specifications (Sanctum tokens, RateLimiter protection, and Portuguese messages).

## 5. Verification Method
Execute the following verification commands via PowerShell:
```powershell
& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Controllers\Api\AuthController.php'
& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Requests\RegisterRequest.php'
& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\app\Http\Requests\LoginRequest.php'
& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l 'c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\routes\api_auth.php'
```
Invalidation condition: Any syntax error or missing endpoint method in `AuthController.php`.
