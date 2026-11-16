# Auditoria de Segurança de Rotas e Endpoints — Assin.art v1 Backend

> **Escopo:** Análise estática de segurança atualizada após merge de `origin/develop` em `main` (commit `e2fc47d`), cobrindo `routes/*`, controllers, concerns, `FormRequest`s, `Rules`, modelos, `bootstrap/app.php` e `config/*`.
> **Status:** Somente documentação (nenhum arquivo de código da aplicação foi modificado).

---

## 1. Sumário Executivo (Pós-Merge `develop` -> `main`)

O merge de `origin/develop` trouxe melhorias importantes — com destaque para a **correção da validação unificada de `username` em `PUT/PATCH /api/v1/profile`** via `App\Rules\ArtistUsername` e `UpdateProfileRequest`, o controle de visibilidade de perfis não publicados (`is_published` / `status: 'draft'` em `PublicProfileController`), e a confirmação de senha em `deleteAccount`.

Contudo, **as falhas críticas anteriores permanecem abertas** e **novas superfícies vulneráveis foram introduzidas** com os novos endpoints de perfil/mídia, SVG/data URIs e telemetria (`POST /api/v1/track`):
1. **Bypass completo de verificação de e-mail** (`GET|POST /api/v1/auth/email/verify` público sem token e auto-verificação no onboarding).
2. **Exclusão arbitrária de arquivos entre artistas (IDOR no disco `public`) — agora ampliada** para também atingir `avatar_url`, `cover_url` e `wallpaper_url` via `PATCH /api/v1/profile` + `DELETE /api/v1/profile/*` + `DELETE /api/v1/auth/account`, além de `/api/v1/buttons*`.
3. **Injeção de URI insegura & bypass de filtro SVG base64** (`UpdateProfileRequest` permite `data:image/svg+xml;base64,...` sem decodificar base64 e deixa `wallpaper_url` sem validação de protocolo; `OnboardingController::formatPlatformUrl` mantém fallback bruto em `whatsapp`; `StoreButtonRequest`/`UpdateButtonRequest`/`RedirectController` não restringem protocolo exclusivamente a `http`/`https`).
4. **Endpoint público `POST /api/v1/track` sem autenticação, sem `throttle` e sem validação**, permitindo envenenamento de métricas, inflação arbitrária de `clicks_count`, bypass de estado `draft`/`is_active` e exaustão de escrita em disco/SQLite.
5. **Ausência de `trustProxies` em `bootstrap/app.php`** e **falta de `throttle` em `register`, `forgot-password`, `reset-password`, `usernames/check`, `GET /{username}` e uploads**.

---

## 2. Matriz de Risco (Atualizada)

| ID | Severidade | Rota / Componente | Status pós-merge | Vulnerabilidade |
|---|---|---|---|---|
| **SEC-01** | Crítica | `GET\|POST /api/v1/auth/email/verify`, `PUT /api/v1/onboarding/buttons` | **Mantida** | Verificação de e-mail sem autenticação/token e auto-verificação no onboarding |
| **SEC-02** | Crítica | `/api/v1/buttons*`, `/api/v1/profile` (`PUT/PATCH/DELETE`), `/api/v1/auth/account` | **Ampliada** | Exclusão arbitrária de arquivos de mídia de outros artistas (IDOR no disco `public`) |
| **SEC-03** | Alta | `PUT /api/v1/onboarding/links`, `POST\|PUT\|PATCH /api/v1/buttons*`, `PUT\|PATCH /api/v1/profile`, `GET /r/{id}` | **Ampliada** | `javascript:` em `whatsapp`, Open Redirect sem whitelist `http(s)`, `wallpaper_url` sem filtro e bypass base64 em SVG |
| **SEC-04** | Alta | `POST /api/v1/track` (`MetricsController::track`) | **Nova** | Telemetria pública sem validação nem `throttle` (inflação de cliques/views, escrita ilimitada e bypass de `draft`/`is_active`) |
| **SEC-05** | Alta | `POST /api/v1/auth/forgot-password`, `reset-password`, `register` | **Mantida** | Sem rate limiting, fallback plaintext no reset e tokens Sanctum não revogados pós-reset |
| **SEC-06** | Alta | `bootstrap/app.php`, `login`, `email/resend`, `GET /r/{id}` | **Mantida** | Sem `trustProxies` (colapso de IP no rate limit) e cooldown travável de terceiros em `email/resend` |
| **SEC-07** | Média | `POST /api/v1/auth/forgot-password`, `GET /api/v1/usernames/check`, `GET /{username}`, `GET /r/{id}` | **Atualizada** | Enumeração de contas/perfis sem `throttle`, vazamento de `artists.id` e `/r/{id}` acessível para perfil `draft` |
| **SEC-08** | Média | `config/sanctum.php`, `RegisterRequest`, `AuthController::logout` | **Mantida** | Tokens Sanctum sem expiração (`null`), senha `min:6` sem `confirmed` e logout parcial |
| **SEC-09** | Média | `config/cors.php`, `bootstrap/app.php` | **Mantida** | CORS amplo (`paths` com `'*'` e `supports_credentials: true`) e ausência de Security Headers HTTP |
| **SEC-10** | Média | `bootstrap/app.php` (`finfo` stub) e `/storage` | **Mantida** | Stub silencioso de `finfo` retornando `false` e necessidade de bloquear PHP em `/storage` |
| **SEC-11** | Baixa | `UpdateProfileRequest`, `OnboardingButtonsRequest`, `Store/UpdateButtonRequest`, `RedirectController` | **Atualizada** | Cores/estilos sem regex estrito, `X-Visitor-Id` sem truncamento (64 chars) e falta de `throttle` em uploads |
| **SEC-12** | Baixa | `Button.php`, `OnboardingController::saveLinks`, `ManagesPasswordAndEmail` | **Parcialmente corrigida** | `$fillable` com `artist_id`/`clicks_count`, órfãos em `saveLinks` e e-mail simulado. A divergência de slugs reservados foi corrigida por `App\Support\ReservedUsernames` |
| **SEC-13** | Média | `database/database.sqlite`, `bootstrap/cache/*.php`, `composer.phar`, `storage/logs/laravel.log`, `.env.example` | **Nova** | Artefatos sensíveis e gerados versionados no Git. `APP_DEBUG=true` no exemplo |
| **SEC-14** | Alta (governança) | Backlog (`#287`, `#288`, `#289`) | **Nova** | SEC-01 a SEC-03 constam como `DONE` no backlog, mas o código do backend ainda contém as falhas |
| **SEC-OLD-06** | — | `PUT\|PATCH /api/v1/profile` (validação de `username`) | **Corrigida** | Resolvida por `UpdateProfileRequest` + `App\Rules\ArtistUsername` |

---

## 3. Inventário de Segurança das Rotas (Pós-Merge)

| Método & Rota | Arquivo | Auth | Rate Limit Atual | Principais Riscos |
|---|---|---|---|---|
| `GET /` | `routes/web.php` | Pública | Nenhum | Expõe versão |
| `GET /up` | `bootstrap/app.php` | Pública | Nenhum | Baixo (healthcheck) |
| `GET /r/{id}` | `routes/web_public.php` | Pública | `throttle:60,1` (IP) | **SEC-03**, **SEC-06**, **SEC-07**, **SEC-11** |
| `GET /{username}` | `routes/web_public.php` | Pública (checa `is_published`) | Nenhum | **SEC-07**, **SEC-12** |
| `GET /api/v1/usernames/check` | `routes/api_auth.php` | Pública | Nenhum | **SEC-07** |
| `POST /api/v1/track` | `routes/api_metrics.php` | Pública | Nenhum | **SEC-04** |
| `GET /api/v1/metrics/summary` | `routes/api_metrics.php` | `auth:sanctum` | Nenhum | OK (escopado a `$request->user()`) |
| `POST /api/v1/auth/register` | `routes/api_auth.php` | Pública | Nenhum | **SEC-05**, **SEC-08** |
| `POST /api/v1/auth/login` | `routes/api_auth.php` | Pública | 5/min (`RateLimiter` IP) | **SEC-06** |
| `POST /api/v1/auth/forgot-password` | `routes/api_auth.php` | Pública | Nenhum | **SEC-05**, **SEC-07** |
| `POST /api/v1/auth/reset-password` | `routes/api_auth.php` | Pública | Nenhum | **SEC-05** |
| `POST /api/v1/auth/email/resend` | `routes/api_auth.php` | Pública | 1/60s (`id`/`email`/`ip`) | **SEC-06** |
| `GET\|POST /api/v1/auth/email/verify` | `routes/api_auth.php` | Pública | Nenhum | **SEC-01** |
| `POST /api/v1/auth/logout` | `routes/api_auth.php` | `auth:sanctum` | Nenhum | **SEC-08** |
| `GET /api/v1/auth/me` | `routes/api_auth.php` | `auth:sanctum` | Nenhum | OK |
| `POST /api/v1/auth/account/delete` & `DELETE /api/v1/auth/account` | `routes/api_auth.php` | `auth:sanctum` | Nenhum | **SEC-02** (ao limpar storage) |
| `PUT /api/v1/onboarding/links` | `routes/api_onboarding.php` | `auth:sanctum` | Nenhum | **SEC-03**, **SEC-12** |
| `PUT /api/v1/onboarding/background` | `routes/api_onboarding.php` | `auth:sanctum` | Nenhum | Validação branda de `background_id` |
| `PUT /api/v1/onboarding/buttons` | `routes/api_onboarding.php` | `auth:sanctum` | Nenhum | **SEC-01**, **SEC-11** |
| `GET /api/v1/profile` | `routes/api_profile.php` | `auth:sanctum` | Nenhum | OK |
| `PUT\|PATCH /api/v1/profile` | `routes/api_profile.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-03**, **SEC-11** |
| `POST\|DELETE /api/v1/profile/avatar` | `routes/api_profile.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-10**, **SEC-11** |
| `POST\|DELETE /api/v1/profile/cover` | `routes/api_profile.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-10**, **SEC-11** |
| `POST\|DELETE /api/v1/profile/background` & `/wallpaper` | `routes/api_profile.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-10**, **SEC-11** |
| `GET /api/v1/buttons` | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | OK |
| `POST /api/v1/buttons` | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-03**, **SEC-11** |
| `DELETE /api/v1/buttons` (`destroyAll`) | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | **SEC-02** |
| `POST /api/v1/buttons/reorder` | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | OK (valida ownership) |
| `POST /api/v1/buttons/{id}/image` | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-10**, **SEC-11** |
| `PUT\|PATCH /api/v1/buttons/{id}` | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | **SEC-02**, **SEC-03**, **SEC-11** |
| `DELETE /api/v1/buttons/{id}` | `routes/api_buttons.php` | `auth:sanctum` | Nenhum | **SEC-02** |

---

## 4. Detalhamento Técnico das Falhas

### SEC-01 — Bypass Completo de Verificação de E-mail (Crítica)
- **Onde:** `routes/api_auth.php` (L28), `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` (`verifyEmail`, L158–176) e `app/Http/Controllers/Api/OnboardingController.php` (`saveButtons`, L281–283).
- **Análise:** A rota `/api/v1/auth/email/verify` aceita `GET` e `POST` publicamente sem autenticação nem verificação de token/assinatura, marcando `email_verified_at = now()` para qualquer `email` informado. Além disso, `OnboardingController::saveButtons` define `email_verified_at = now()` ao finalizar o onboarding.
- **Remediação:** Exigir token criptográfico temporário/URL assinada (`signed`) em `verifyEmail` e remover a auto-verificação de `OnboardingController::saveButtons`.

---

### SEC-02 — Exclusão Arbitrária de Arquivos Entre Artistas / IDOR no Storage `public` (Crítica — Ampliada)
- **Onde:**
  - `app/Http/Requests/StoreButtonRequest.php` (L31) e `UpdateButtonRequest.php` (L31) -> `image_url` livre (`string|max:2048`).
  - `app/Http/Requests/UpdateProfileRequest.php` (L48, L49, L52) -> aceita `/storage/...` e URLs livres em `avatar_url`, `cover_url` e `wallpaper_url`.
  - `app/Http/Controllers/Api/ButtonController.php` (`destroy` L106–115, `destroyAll` L129–140), `ManagesButtonReorderAndImage.php` (`uploadImage` L65–74), `ManagesProfileMedia.php` (L18, L33, L43, L58, L69, L85), `AuthController.php` (`deleteAccount` L130–137) e `InteractsWithMediaStorage.php` (`deleteFileFromStorage` L34–44).
- **Análise:** Qualquer usuário autenticado pode definir `image_url` (em botões) ou `avatar_url` / `cover_url` / `wallpaper_url` (no perfil via `PUT|PATCH /api/v1/profile`) apontando para `/storage/avatars/...`, `/storage/covers/...`, `/storage/wallpapers/...` ou `/storage/cards/...` de **outro artista** (URLs expostas publicamente em `GET /{username}`). Ao acionar exclusão/substituição de mídia ou `deleteAccount`, o backend executa `Storage::disk('public')->delete($relativePath)` sem verificar propriedade do arquivo.
- **Remediação:**
  - Não aceitar caminhos `/storage/...` livres via payload JSON em `StoreButtonRequest`, `UpdateButtonRequest` ou `UpdateProfileRequest` (ou validar que qualquer caminho local pertença estritamente ao namespace `/{tipo}/{artist_id}/...` do artista autenticado).
  - Isolar uploads em subdiretórios por `artist_id` (`avatars/{id}/`, `covers/{id}/`, `wallpapers/{id}/`, `cards/{id}/`) e validar o prefixo do caminho antes de chamar `Storage::disk('public')->delete()`.

---

### SEC-03 — Injeção de URI Insegura, Bypass SVG Base64 e Open Redirect (Alta — Ampliada)
- **Onde:**
  - `app/Http/Controllers/Api/OnboardingController.php` (`formatPlatformUrl`, `whatsapp` L311–322 e sociais L324–437) e `OnboardingLinksRequest.php` (L78–79).
  - `app/Http/Requests/StoreButtonRequest.php` (L18) e `UpdateButtonRequest.php` (L18) + `RedirectController.php` (L34).
  - `app/Http/Requests/UpdateProfileRequest.php` (`wallpaper_url` L52 e `validateMediaUrl` L109–134).
- **Análise:**
  1. Em `OnboardingController::formatPlatformUrl`, `whatsapp` retorna `$input` bruto quando não há dígitos, permitindo gravar `javascript:...` em `buttons.url`; `StoreButtonRequest`/`UpdateButtonRequest` usam `'url'` sem exigir protocolo exclusivamente `http`/`https`; e `RedirectController` redireciona sem revalidar esquema.
  2. Em `UpdateProfileRequest`, `wallpaper_url` (L52) **não usa `$mediaUrlRule`**, aceitando strings/esquemas arbitrários até 2048 chars.
  3. Em `UpdateProfileRequest::validateMediaUrl` (L126–132), permite `data:image/svg+xml` e tenta filtrar XSS usando `rawurldecode($trimmed)` contra regex (`<script|javascript:|vbscript:|\bon[a-z]+\s*=`). Entretanto, **payloads `;base64,` não são decodificados por `rawurldecode()`** (além de vetores SVG como entidades HTML ou tags `<foreignObject>`/`<animate>`), permitindo burlar a inspeção e armazenar SVG ativo caso seja renderizado em contexto permissivo.
- **Remediação:**
  - Exigir whitelist estrita `http://`/`https://` (e `mailto:` validado apenas para `platform=email`) em todas as URLs de botões e redirecionamento; rejeitar `whatsapp` sem dígitos com `422`.
  - Aplicar validação segura também em `wallpaper_url` e não aceitar `data:image/svg+xml` arbitrário de usuário (ou sanitizar via parser XML estrito após `base64_decode` quando aplicável, preferindo presets estáticos por ID).

---

### SEC-04 — Telemetria Pública (`POST /api/v1/track`) Sem Validação, Sem Throttle e Com Bypass de Estado (Alta — Nova)
- **Onde:** `routes/api_metrics.php` (L17) e `app/Http/Controllers/Api/MetricsController.php` (`track`, L19–59).
- **Análise:**
  1. Endpoint público **sem `throttle` e sem `FormRequest`/validação de tamanho ou tipo**: qualquer cliente pode enviar requisições ilimitadas gravando diretamente em `page_views` e `button_clicks` (risco de esgotamento de disco e contenção de locks no SQLite).
  2. Com `type=click`, `Button::find($linkId)` (L45) **não verifica** se o botão está ativo (`is_active = true`) nem se o artista está publicado (`is_published = true`), permitindo incrementar `clicks_count` e gravar `button_clicks` em links desativados ou perfis `draft`.
  3. Com `type=pageview`, registra visitas para perfis `draft` (`is_published = false`) e aceita `visitor_id` arbitrário para inflar `unique_visitors`, além de `visitor_id` (`string(64)`) e `utm_source` (`string(100)`) sem truncamento/validação (risco de erro 500 de tamanho de coluna em MySQL/Postgres estrito).
- **Remediação:**
  - Validar payload via `FormRequest` (limites estritos `max:64` para `visitor_id`, `max:100` para `utm_source`, `max:2048` para `referrer`, `type in:pageview,click`).
  - Filtrar `Artist::where('is_published', true)` e `Button::where('is_active', true)->whereHas('artist', fn ($q) => $q->where('is_published', true))`.
  - Aplicar `throttle` na rota `/api/v1/track` (ex.: `throttle:120,1` por IP) e considerar deduplicação básica de `clicks_count`.

---

### SEC-05 — Ausência de Rate Limiting em Auth e Falhas no Reset de Senha (Alta)
- **Onde:** `routes/api_auth.php` (L23, L25, L26) e `ManagesPasswordAndEmail.php` (L20–124).
- **Análise:** `register`, `forgot-password` e `reset-password` não possuem `throttle` (permitindo criação em massa e DoS de CPU via bcrypt `BCRYPT_ROUNDS=12`). `resetPassword` ainda possui fallback plaintext `hash_equals((string) $record->token, (string) $token)` (L95) e não revoga tokens Sanctum (`$artist->tokens()->delete()`) após redefinir a senha (L112–118).
- **Remediação:** Aplicar `throttle` nomeado em `register`, `forgot-password` e `reset-password`; remover fallback plaintext e revogar todos os tokens Sanctum ativos no reset.

---

### SEC-06 — Ausência de `trustProxies` e Bloqueio Cruzado em `email/resend` (Alta)
- **Onde:** `bootstrap/app.php` (L50–55), `AuthController.php` (`login` L49–59), `ManagesPasswordAndEmail.php` (`resendVerification` L129–153), `routes/web_public.php` (L21).
- **Análise:** Sem `$middleware->trustProxies(at: '*')`, atrás do load balancer do Azure todas as requisições podem compartilhar o IP do proxy, travando `login` (5/min) e `/r/{id}` (60/min) globalmente. Em `resendVerification`, a chave usa `input('email')` sem `auth:sanctum`, permitindo travar o cooldown de terceiros.
- **Remediação:** Configurar `trustProxies`, compor chave de login com `email + ip`, e exigir `auth:sanctum` (ou validar e-mail + chavear por IP) em `email/resend`.

---

### SEC-07 — Enumeração de Contas/Perfis, Vazamento de `artists.id` e `/r/{id}` em Rascunhos (Média)
- **Onde:** `ManagesPasswordAndEmail.php` (L23 — `'exists:artists,email'`), `UsernameController.php`, `PublicProfileController.php` (L44, L66 — expõe `'id' => (int) $artist->id`), `RedirectController.php` (L18–20).
- **Análise:**
  - `forgot-password` retorna 422 quando o e-mail não existe (enumeração).
  - `GET /api/v1/usernames/check` e `GET /{username}` não possuem `throttle`.
  - `PublicProfileController` vaza o `id` sequencial do artista, e `RedirectController` redireciona botões ativos mesmo que o perfil do artista esteja com `is_published = false` (`draft`), permitindo enumerar links de contas em rascunho via IDs sequenciais `/r/1`, `/r/2`, ...
- **Remediação:** Resposta genérica `200` em `forgot-password`; `throttle` em `usernames/check` e `/{username}`; verificar `is_published` em `RedirectController` (retornando `404` se `draft`); avaliar remoção do `id` sequencial público.

---

### SEC-08 — Tokens Sanctum Sem Expiração, Senha Fraca e Logout Parcial (Média)
- **Onde:** `config/sanctum.php` (L50 `'expiration' => null`), `RegisterRequest.php` (L97 `'min:6'`), `AuthController.php` (`logout` L92–103).
- **Remediação:** Definir `expiration` em `sanctum.php`, exigir `min:8` (com complexidade/confirmação) no cadastro e oferecer revogação global de sessões.

---

### SEC-09 — CORS Amplo e Ausência de Security Headers HTTP (Média)
- **Onde:** `config/cors.php` (L18 `paths: ['api/*', 'sanctum/csrf-cookie', '*']`, L36 `'supports_credentials' => true`), `bootstrap/app.php`.
- **Análise:** Como `bootstrap/app.php` desabilitou `statefulApi()` e o app usa Bearer token (`credentials: 'same-origin'`), `supports_credentials => true` com `paths` contendo `'*'` é mais amplo que o necessário; faltam headers `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy` e `HSTS`.
- **Remediação:** Restringir `paths` (e avaliar `supports_credentials => false` se não houver cookies cross-site) e registrar middleware de Security Headers em `bootstrap/app.php`.

---

### SEC-10 — Stub Silencioso de `finfo` e Execução Estática em `/storage` (Média)
- **Onde:** `bootstrap/app.php` (L10–37), `ManagesProfileMedia.php`, `ManagesButtonReorderAndImage.php`.
- **Análise:** O stub `finfo` retorna `false` se `ext-fileinfo` faltar, mascarando erro crítico na inspeção MIME de uploads. No Nginx, `/storage` precisa negar execução de arquivos `.php`.
- **Remediação:** Exigir `ext-fileinfo` real em produção (não silenciar) e configurar `location ~* ^/storage/.*\.php$ { deny all; }` no Nginx.

---

### SEC-11 & SEC-12 — Validação Visual, Truncamento e Higiene (Baixa)
- `UpdateProfileRequest`, `OnboardingButtonsRequest`, `StoreButtonRequest` e `UpdateButtonRequest` aceitam strings livres de até 20–50 chars para cores (`background_color`, `font_color`, `button_color`, `button_shadow_color`, `border_color`, `cover_color`, etc.) e `ProfileController::update` usa `substr($bg, 6)` / `substr($cov, 6)` sem validar regex hex.
- `RedirectController::redirect` (L30) grava `$request->header('X-Visitor-Id')` sem truncar para `64` chars (`button_clicks.visitor_id`).
- Rotas autenticadas de upload (`/api/v1/profile/*`, `/api/v1/buttons/{id}/image`) não têm `throttle`.
- `Button::$fillable` (L29–46 de `app/Models/Button.php`) mantém `'artist_id'` e `'clicks_count'`.
- `OnboardingController::saveLinks` (L178) faz `$artist->buttons()->delete()` sem remover arquivos de `image_url` no storage (órfãos).
- `PublicProfileController::RESERVED_ROUTES` (9 termos) e `RegisterRequest::RESERVED_WORDS` (30 termos) continuam com listas distintas (embora `ArtistUsername` agora proteja tanto registro quanto edição de perfil).

---

## 4.1 Reverificação no Código (2026-11-15, branch `develop`, `ce73836`)

| ID | Evidência atual | Resultado |
|---|---|---|
| SEC-01 | `routes/api_auth.php` mantém `Route::match(['get','post'], '/email/verify')` público. `OnboardingController.php` L292-293 grava `email_verified_at`. | **Aberta** |
| SEC-02 | `InteractsWithMediaStorage::deleteFileFromStorage` apaga sem checar dono. | **Aberta** |
| SEC-03 | `formatPlatformUrl` retorna `$input` em `whatsapp` (L352). Requests de botão usam só `url`. | **Aberta** |
| SEC-04 | `MetricsController::track` sem `FormRequest`, sem `throttle`, sem filtro `is_published`/`is_active`. | **Aberta** |
| SEC-05 | Sem `throttle` em `register`, `forgot-password` e `reset-password`. `ManagesPasswordAndEmail` L95 mantém `hash_equals` em texto puro. | **Aberta** |
| SEC-06 | `bootstrap/app.php` recebeu `trustProxies(at: '*')` em `API-12` (`5635c9c`); chave composta de `login` e `email/resend` segue pendente. | **Parcial** |
| SEC-07 | `RedirectController` sem `is_published`. `PublicProfileController` L41 e L63 expõem `id`. | **Aberta** |
| SEC-08 | `config/sanctum.php` L50 `expiration` `null`. `RegisterRequest.php` L67 `min:6`. | **Aberta** |
| SEC-09 | `config/cors.php` mantém `'*'` e `supports_credentials` `true`. Existe `tests/Feature/CorsSecurityTest.php`: revise a cobertura. | **Aberta** |
| SEC-10 | `finfo` falso mantido em `bootstrap/app.php`. | **Aberta** |
| SEC-12 | `ReservedUsernames` unifica os slugs reservados. | **Slugs corrigidos** |

> [!WARNING]
> Os cards `#287`, `#288` e `#289` estão em `DONE`. Confirme se as correções vivem em outro repositório ou branch (`SEC-14`).

### SEC-13 — Artefatos Sensíveis e Gerados no Git (Média — Nova)
- **Onde:** `git ls-files` lista `database/database.sqlite`, `bootstrap/cache/packages.php`, `bootstrap/cache/services.php`, `composer.phar`, `storage/logs/laravel.log` e `storage/framework/views/*.php`. `.env.example` define `APP_DEBUG=true`.
- **Análise:** O banco versionado pode conter e-mails e hashes de teste. O log pode vazar dados de requisições. O cache de pacotes referencia `require-dev` e quebra `composer install --no-dev`.
- **Remediação:** `git rm --cached` nos arquivos, reforço do `.gitignore`, `APP_DEBUG=false` no exemplo e inspeção do histórico.

### Riscos Específicos do Deploy no Azure (ver `DEPLOY_PLAN.md`)
- **SQLite em `/home` (CIFS):** escrita pública sem limite em `/track` e `/r/{id}` agrava locks. Corrija **SEC-04** antes do deploy.
- **Nginx:** o startup deve negar `.php` em `/storage` e ocultar arquivos `.`
- **`.env` no pacote:** exclua `.env*` do ZIP. O `.env` local sobrepõe os App Settings.
- **`MAIL_MAILER=log`:** `forgot-password` não envia e-mail. O token cai no log (`LOG_CHANNEL=stderr`), o que expõe tokens de reset nos logs do Azure. Use `LOG_LEVEL=warning` e SMTP real no pós-MVP.

---

## 5. Prioridade de Correção Pré-Deploy

0. **Bloqueio de governança:** **SEC-14** (reverificar SEC-01 a SEC-03 antes de qualquer deploy).
1. **Imediato (Crítico/Alto):** **SEC-01** (email verify + onboarding), **SEC-02** (IDOR de deleção no storage em buttons + profile + account), **SEC-04** (validação, filtro `is_published`/`is_active` e throttle em `POST /api/v1/track`), **SEC-03** (`whatsapp` + whitelist `http(s)` + `wallpaper_url` + SVG base64), **SEC-05** & **SEC-06** (`trustProxies` + `throttle` em auth + revogação de tokens no reset).
2. **Endurecimento:** **SEC-07** a **SEC-13** (draft check em `/r/{id}`, expiração Sanctum, security headers, bloqueio PHP em `/storage/`, validação hex em cores e throttle em uploads).
