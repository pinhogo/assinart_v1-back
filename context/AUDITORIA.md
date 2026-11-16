# Relatório de Auditoria de Arquitetura — Assin.art Backend (`assinart_v1_backend`)

> **Metas arquiteturais de referência (Regra 5):**
> - Controllers, Concerns, Models, FormRequests, Services, Rules, Middleware e Support: **≤ 150 linhas de código efetivo**
> - Comentários e linhas em branco não entram na contagem efetiva (Regra 5.2).
>
> Regras relacionadas em [`context/rules.md`](rules.md): Regra 2 (Arquitetura MVC), Regra 5 (Limites de linhas e proibição de burla), Regra 6 (Reuso obrigatório), Regra 7 (Escopo estrito), Regra 10 (Auditoria cumulativa), Regra 11 (Padrão ASD-STE100), Regra 13 (Sincronização Frontend-Backend), Regra 14 (Paridade Tripla) e Regra 15 (Orquestração Multi-Agente).

## Status Geral da Auditoria

- **Total inspecionado em `app/` (2026-11-15 pós-lote `SEC-*` + `API-14..19`):** **35 arquivos PHP**.
- **Conformes ao limite efetivo (≤ 150 linhas efetivas):** **35 de 35 arquivos (100% conforme)**.
- **Arquivos Críticos Refatorados (≤ 150 linhas efetivas):**
  - ✅ [`app/Http/Controllers/Api/OnboardingController.php`](../app/Http/Controllers/Api/OnboardingController.php) (483 brutas / 391 efetivas ➔ **154 brutas / 126 efetivas**; extraído [`OnboardingPlatformUrlFormatter.php`](../app/Support/OnboardingPlatformUrlFormatter.php)).
  - ✅ [`app/Http/Controllers/Api/ButtonController.php`](../app/Http/Controllers/Api/ButtonController.php) (225 ➔ 206 ➔ **139 brutas / 113 efetivas**; helpers e validação de seção movidos para [`ManagesButtonReorderAndImage.php`](../app/Http/Controllers/Api/Concerns/ManagesButtonReorderAndImage.php)).

---

## 1. Arquivos com Muitas Linhas (Auditoria de Tamanho em `app/`)

### 1.1 Controllers Críticos Refatorados (> 150 linhas efetivas originalmente)

> `Bruto` equivale a `wc -l`. `Efetivo` exclui linhas em branco e comentários (`//`, `/* ... */`).

| # | Arquivo | Bruto Inicial | Efetivo Inicial | Bruto Atual | Efetivo Atual | Status | Observação e Histórico de Refatoração |
|---|---|---|---|---|---|---|---|
| 1 | `app/Http/Controllers/Api/OnboardingController.php` | 483 | 391 | **154** | **126** | ✅ **REFATORADO** | **Meta atingida (≤ 150 ef.).** Extraídas constantes `PALETTE_PRESETS`, `PLATFORM_TITLES` e `formatPlatformUrl` para `app/Support/OnboardingPlatformUrlFormatter.php` (167 brutas / 142 ef.). |
| 2 | `app/Http/Controllers/Api/ButtonController.php` | 225 | — | **139** | **113** | ✅ **REFATORADO** | **Meta atingida (≤ 150 ef.).** Centralizou exclusão em `InteractsWithMediaStorage.php` (`API-13`) e delegou regras auxiliares para `ManagesButtonReorderAndImage.php` (133 brutas / 111 ef.). |

### 1.2 Acima de 150 Linhas Brutas porém Dentro do Limite Efetivo (≤ 150 linhas efetivas — Conforme Regra 5)

| # | Arquivo | Meta | Bruto | Efetivo | Situação | Observação |
|---|---|---|---|---|---|---|
| 1 | `app/Services/ButtonOrderService.php` | ≤ 150 | **231** | **145** | ✅ **CONFORME** | Próximo ao teto efetivo (145/150); vigiar novas adições. |
| 2 | `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` | ≤ 150 | **180** | **141** | ✅ **CONFORME** | Trait de recuperação de senha e verificação assinada dentro do teto efetivo. |
| 3 | `app/Http/Controllers/Api/Concerns/InteractsWithMediaStorage.php` | ≤ 150 | **176** | **142** | ✅ **CONFORME** | Centraliza disco `MEDIA_DISK`, extração Azure/local e guarda anti-IDOR dentro do teto efetivo. |
| 4 | `app/Support/OnboardingPlatformUrlFormatter.php` | ≤ 150 | **167** | **142** | ✅ **CONFORME** | Extraído de `OnboardingController.php` dentro do teto efetivo. |
| 5 | `app/Http/Requests/OnboardingLinksRequest.php` | ≤ 150 | **164** | **124** | ✅ **CONFORME** | Normalização de rascunho e validação de links dentro do teto efetivo. |
| 6 | `app/Http/Controllers/Api/AuthController.php` | ≤ 150 | **156** | **131** | ✅ **CONFORME** | Auth core com rate limit `email+ip` e logout global dentro do teto efetivo. |
| 7 | `app/Http/Requests/UpdateProfileRequest.php` | ≤ 150 | **155** | **134** | ✅ **CONFORME** | Validação anti-IDOR, SVG base64 e `HexColor` dentro do teto efetivo. |
| 8 | `app/Models/Artist.php` | ≤ 150 | **151** | **130** | ✅ **CONFORME** | Model principal com accessors polimórficos dentro do teto efetivo. |

### 1.3 Demais Arquivos de `app/` (≤ 150 linhas brutas e efetivas)

| # | Arquivo | Camada | Bruto | Efetivo | Situação |
|---|---|---|---|---|---|
| 1 | `app/Http/Controllers/Api/Concerns/ManagesButtonReorderAndImage.php` | Concern | 133 | 111 | ✅ **CONFORME** |
| 2 | `app/Http/Requests/OnboardingButtonsRequest.php` | Request | 133 | 101 | ✅ **CONFORME** |
| 3 | `app/Models/Button.php` | Model | 129 | 109 | ✅ **CONFORME** |
| 4 | `app/Http/Controllers/Concerns/AggregatesMetrics.php` | Concern | 117 | 103 | ✅ **CONFORME** |
| 5 | `app/Http/Controllers/Api/ProfileController.php` | Controller | 110 | 95 | ✅ **CONFORME** |
| 6 | `app/Http/Controllers/Concerns/FormatsPublicProfile.php` | Concern | 106 | 101 | ✅ **CONFORME** |
| 7 | `app/Rules/ArtistUsername.php` | Rule | 104 | 71 | ✅ **CONFORME** |
| 8 | `app/Http/Requests/RegisterRequest.php` | Request | 104 | 69 | ✅ **CONFORME** |
| 9 | `app/Http/Requests/UpdateButtonRequest.php` | Request | 97 | 87 | ✅ **CONFORME** |
| 10 | `app/Http/Controllers/PublicProfileController.php` | Controller | 95 | 79 | ✅ **CONFORME** |
| 11 | `app/Support/ReservedUsernames.php` | Support | 92 | 66 | ✅ **CONFORME** |
| 12 | `app/Http/Requests/StoreButtonRequest.php` | Request | 89 | 80 | ✅ **CONFORME** |
| 13 | `app/Http/Controllers/Api/Concerns/ManagesProfileMedia.php` | Concern | 88 | 73 | ✅ **CONFORME** |
| 14 | `app/Http/Controllers/Api/MetricsController.php` | Controller | 85 | 74 | ✅ **CONFORME** |
| 15 | `app/Http/Requests/OnboardingBackgroundRequest.php` | Request | 78 | 53 | ✅ **CONFORME** |
| 16 | `app/Http/Controllers/Api/V1/UsernameController.php` | Controller | 75 | 56 | ✅ **CONFORME** |
| 17 | `app/Http/Requests/LoginRequest.php` | Request | 57 | 35 | ✅ **CONFORME** |
| 18 | `app/Http/Requests/ReorderButtonsRequest.php` | Request | 53 | 35 | ✅ **CONFORME** |
| 19 | `app/Http/Controllers/RedirectController.php` | Controller | 39 | 27 | ✅ **CONFORME** |
| 20 | `app/Models/PageView.php` | Model | 37 | 30 | ✅ **CONFORME** |
| 21 | `app/Rules/HexColor.php` | Rule | 34 | 27 | ✅ **CONFORME** |
| 22 | `app/Http/Middleware/SecurityHeaders.php` | Middleware | 34 | 22 | ✅ **CONFORME** |
| 23 | `app/Models/ButtonClick.php` | Model | 32 | 25 | ✅ **CONFORME** |
| 24 | `app/Http/Requests/TrackEventRequest.php` | Request | 29 | 22 | ✅ **CONFORME** |
| 25 | `app/Http/Controllers/Controller.php` | Controller | 8 | 5 | ✅ **CONFORME** |

---

## 2. Registro Cumulativo de Entregas (Branch `develop`)

| Arquivos Criados / Editados | Pasta / Repo | Camada / Task | Lote / Issue | Status | Resumo das Mudanças |
|---|---|---|---|---|---|
| `Button.php`, `FormatsPublicProfile.php`, `PublicProfileTest.php`, `api_contracts.md`, `tasks_backend.md` | `app/`, `tests/`, `context/` | Model / Concern / Feature Tests | **`#259` (`FIX-API-06`)** | ✅ **DONE** | Prioriza `button_color` individual em `resolved_circular_bg`, cobre fallback por variante/artista em `PublicProfileTest` e atualiza contrato §7.1. |
| `2026_11_13_000001_add_is_published_to_artists_table.php`, `Artist.php`, `AuthController.php`, `ProfileController.php`, `PublicProfileController.php`, `ProfilePublicationTest.php` | `database/`, `app/`, `tests/` | Migration / Controllers / Tests | **`#262` (`FIX-API-07`)** | ✅ **DONE** | Adiciona `is_published` e `publication_status`, protege perfis `draft` com HTTP `404` para terceiros e libera preview autenticado do dono. |
| `2026_11_14_000001_add_shadow_color_and_circular_shadow_to_artists.php`, `Artist.php`, `OnboardingButtonsRequest.php`, `OnboardingController.php`, `ProfileController.php`, `FormatsPublicProfile.php` | `database/`, `app/`, `tests/` | Migration / Requests / Controllers | **`#298` (`FIX-API-09`)** | ✅ **DONE** | Persiste e serializa `button_shadow_color` e `circular_shadow_enabled` com aliases snake_case/camelCase. |
| `ReservedUsernames.php`, `2026_11_14_000002_normalize_artist_usernames.php`, `ArtistUsername.php`, `RegisterRequest.php`, `UpdateProfileRequest.php`, `PublicProfileController.php`, `web_public.php`, `ReservedUsernamesTest.php`, `UsernameNormalizationTest.php` | `app/`, `database/`, `routes/`, `tests/` | Support / Rule / Migration / Tests | **`#268` (`FIX-API-08`), `#309` (`FIX-API-10`), `#310` (`FIX-API-11`)** | ✅ **DONE** | Centraliza slugs reservados em `ReservedUsernames`, normaliza `@username` em `PATCH /profile`, distingue rascunho de `@` livre e ajusta `circular_icon_background` (`#307`). |
| `ButtonOrderService.php`, `ReorderButtonsRequest.php`, `OnboardingLinksRequest.php`, `OnboardingController.php`, `ButtonController.php`, `ManagesButtonReorderAndImage.php`, `StoreButtonRequest.php`, `UpdateButtonRequest.php`, `ButtonTest.php`, `OnboardingTest.php`, `api_contracts.md` | `app/`, `tests/`, `context/` | Service / Requests / Controllers / Tests | **`#299` (`API-10`, `API-11`) & `#325` (`FIX-API-12`)** | ✅ **DONE** | Implementa reorder declarativo `items[]`, limite global de 30 links e teto de 5 circulares, e normaliza/valida e-mail nos endpoints de botões. |
| `config/database.php` (185 l.), `0001_01_01_000004_create_jobs_table.php` (57 l.), `DatabaseProductionConfigTest.php` (132 l.), `.env.example` (70 l.), `bootstrap/app.php` (64 l.), `phpunit.xml` (34 l.) & `data_dictionary.md` (278 l.) | `config/`, `database/migrations/`, `tests/Feature/`, `bootstrap/`, `context/` | Config / Migration / Healthcheck / Docs | **`#327` (`API-12`)** | ✅ **DONE** | Publica `config/database.php` com SSL/TLS PostgreSQL e MySQL, cria migration de filas, configura `trustProxies` e `/up` com DB, isola SQLite ao dev/testes e atualiza `data_dictionary.md`. |
| `composer.json` (71 l.), `composer.lock`, `config/filesystems.php` (82 l.), `InteractsWithMediaStorage.php` (120 l. / 83 ef.), `ManagesProfileMedia.php` (88 l. / 73 ef.), `ManagesButtonReorderAndImage.php` (105 l. / 81 ef.), `ButtonController.php` (206 l. / 166 ef.), `UpdateProfileRequest.php` (135 l. / 114 ef.), `MediaStorageTest.php` (390 l.), `BackgroundMediaTest.php` (217 l.), `AccountDeletionTest.php` (129 l.), `api_contracts.md` (674 l.) & `tasks_backend.md` (319 l.) | `config/`, `app/Http/`, `tests/Feature/`, `context/` | Package / Config / Concerns / Tests / Docs | **`#328` (`API-13`)** | ✅ **DONE** | Instala `azure-oss/storage-blob-laravel`, registra disco `azure` e `media_disk`, centraliza storage em `InteractsWithMediaStorage`, remove hardcode `public`/`/storage/`, valida `wallpaper_url`, cobre testes Azure e atualiza `api_contracts.md`. |
| `routes/api_auth.php`, `routes/web_public.php`, `config/sanctum.php`, `AuthController.php` (131 ef.), `ManagesPasswordAndEmail.php` (141 ef.), `RegisterRequest.php` (69 ef.), `PublicProfileController.php` (79 ef.), `AuthTest.php` & `PublicProfileTest.php` (`d2e55ba`) | `routes/`, `config/`, `app/`, `tests/` | Auth / Throttle / Sanctum / Signed URL | **`SEC-05`, `SEC-06`, `SEC-07`, `SEC-08`, `SEC-14.2`** | ✅ **DONE** | Aplica throttle em `register`/`forgot`/`reset`/`check`/`/{username}`, exige senha `min:8` e `SANCTUM_EXPIRATION`, isola chave `email+ip` no login/resend, implementa logout global (`all`), remove fallback plaintext e exige URL assinada em `verifyEmail`, oculta `artist.id` público. |
| `HexColor.php` (27 ef.), `TrackEventRequest.php` (22 ef.), `OnboardingPlatformUrlFormatter.php` (142 ef.), `OnboardingController.php` (126 ef.), `ButtonController.php` (113 ef.), `InteractsWithMediaStorage.php` (142 ef.), `ManagesButtonReorderAndImage.php` (111 ef.), `ManagesProfileMedia.php` (73 ef.), `MetricsController.php` (74 ef.), `ProfileController.php` (95 ef.), `RedirectController.php` (27 ef.), `Requests/*`, `Button.php` (109 ef.), `routes/api_{metrics,profile,buttons}.php` & `tests/Feature/*` (`6c281d3`) | `app/`, `routes/`, `tests/` | Anti-IDOR / Telemetria / SVG / HexColor / Refatoração | **`SEC-04`, `SEC-07.3`, `SEC-11`, `SEC-12`, `SEC-14`** | ✅ **DONE** | Refatora `OnboardingController` e `ButtonController` para `≤ 150` ef., bloqueia IDOR em storage/requests, decodifica SVG base64, valida `http(s)` e `HexColor`, filtra `draft`/`is_active` e aplica `throttle` em `/track` e uploads, remove `artist_id`/`clicks_count` de `Button::$fillable`. |
| `SecurityHeaders.php` (22 ef.), `config/cors.php`, `config/database.php`, `bootstrap/app.php`, `composer.json`, `.gitignore`, `.gitattributes`, `.env.example`, `public/.user.ini`, `azure/*`, `.github/workflows/deploy-backend.yml`, `context/DEPLOY_PLAN.md`, `CorsSecurityTest.php` & `AzureRuntimeAndSecurityTest.php` (`ef99eed`) | `app/Http/Middleware/`, `config/`, `azure/`, `.github/workflows/`, `context/`, `tests/` | CORS / SecurityHeaders / Git Hygiene / Infra Azure | **`SEC-09`, `SEC-10`, `SEC-13` & `API-14`..`API-19`** | ✅ **DONE** | Restringe CORS + SecurityHeaders, exige `ext-fileinfo`/`ext-mbstring` e guarda polyfills a `local`, remove artefatos sensíveis/cache do Git, cria `azure/*`, `.gitattributes`, `.user.ini`, workflow PHP 8.3, ativa SQLite `WAL` (`5000ms`), atualiza `DEPLOY_PLAN.md` e cobre 439 testes aprovados. |
