## 2026-10-21T03:22:04Z
You are a teamwork_preview_worker agent.
Your Working Directory: c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m1_auth
Your Identity: worker_m1_auth

MANDATORY FIRST STEP:
Read the Original Request file at:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\ORIGINAL_REQUEST.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

TASK OBJECTIVE:
Implement Milestone 1 (Auth, Account & Username Check Core) for Assin.art v1 backend:

1. `app/Http/Controllers/Api/AuthController.php`:
   - `register(RegisterRequest $request)`: Creates Artist account. Accepts `name`, `username`, `email`, `password`, `agree_terms` (also accepts camelCase `agreeTerms`). Generates Sanctum API token via `$artist->createToken('auth_token')->plainTextToken`. Returns 201 `{ status: "success", message: "Conta criada com sucesso.", token, artist }`.
   - `login(LoginRequest $request)`: Authenticates via email & password. Protected by RateLimiter max 5 attempts/minute per IP (returns 429 Too Many Requests if exceeded). Returns 200 `{ status: "success", message: "Login realizado com sucesso.", token, artist }`. If invalid, returns 401 `{ status: "error", message: "Credenciais inválidas. Verifique seu e-mail e senha." }`.
   - `logout(Request $request)`: Requires `auth:sanctum`. Revokes current token (`$request->user()->currentAccessToken()->delete()`). Returns 200 `{ status: "success", message: "Sessão encerrada com sucesso." }`.
   - `me(Request $request)`: Requires `auth:sanctum`. Returns 200 `{ status: "success", data: $request->user() }`.
   - `forgotPassword(Request $request)`: Validates email, creates token in `password_reset_tokens`. Returns 200 `{ status: "success", message: "Link de recuperação enviado com sucesso." }`.
   - `resetPassword(Request $request)`: Validates token, email, password, password_confirmation. Updates password. Returns 200 `{ status: "success", message: "Senha redefinida com sucesso." }`.
   - `resendVerification(Request $request)`: Strict 60-second cooldown via RateLimiter/Cache. Returns 200 `{ status: "success", message: "E-mail de confirmação reenviado." }` or 429.
   - `verifyEmail(Request $request)`: Marks `email_verified_at = now()`. Returns 200.
2. `app/Http/Requests/RegisterRequest.php` & `app/Http/Requests/LoginRequest.php`: FormRequests with validation rules and clean Portuguese error messages.
3. `routes/api_auth.php`: Define all routes for Auth (register, login, logout, me, forgot-password, reset-password, email/resend, email/verify).
   Ensure `GET /api/v1/usernames/check` is also properly routed to `UsernameController::check`.

FILES YOU OWN EXCLUSIVELY:
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Requests/RegisterRequest.php`
- `app/Http/Requests/LoginRequest.php`
- `routes/api_auth.php`

PHP CLI PATH:
`C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe`
Run lint check: `& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l <file>`

OUTPUT REQUIREMENTS:
- Update progress.md in your working directory.
- Verify files with PHP lint.
- Write handoff report to `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m1_auth\handoff.md`.
- Send message back to orchestrator upon completion.
