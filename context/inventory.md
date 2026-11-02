# Inventário de Ativos do Backend (`context/inventory.md`)

> Catálogo vivo de ativos de software (Models, Controllers, Requests, Migrations, Seeders e Testes) do repositório `assinart_v1_backend`.
> Mantido em conformidade com as **Regras 1, 3 e 10** de `context/rules.md`.
> Padrão de Redação: ASD-STE100.

---

## 1. Models Eloquent

| Nome | Caminho | Tabela | Finalidade | Relacionamentos / Accessors |
| :--- | :--- | :--- | :--- | :--- |
| `Artist` | `app/Models/Artist.php` | `artists` | Representa o artista autenticado, credenciais e configurações visuais | `hasMany(buttons)`, `hasMany(pageViews)`, `avatar`, `cover`, `background` |
| `Button` | `app/Models/Button.php` | `buttons` | Representa links e botões da página neo-brutalista | `belongsTo(artist)`, `hasMany(buttonClicks)`, styles herdados |
| `PageView` | `app/Models/PageView.php` | `page_views` | Registra acessos e pageviews do perfil público com telemetria | `belongsTo(artist)` |
| `ButtonClick` | `app/Models/ButtonClick.php` | `button_clicks` | Registra cliques pontuais para métricas e telemetria | `belongsTo(button)` |

---

## 2. Controllers de API

| Nome | Caminho | Endpoints Atendidos | Finalidade |
| :--- | :--- | :--- | :--- |
| `AuthController` | `app/Http/Controllers/Api/AuthController.php` | `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`, `POST /auth/forgot-password`, `POST /auth/reset-password`, `POST /auth/email/resend`, `POST /auth/email/verify`, `POST /auth/account/delete` | Autenticação, sessão Sanctum, recuperação de senha e exclusão definitiva de conta |
| `ProfileController` | `app/Http/Controllers/Api/ProfileController.php` | `GET /profile`, `PUT /profile`, `PATCH /profile`, `POST /profile/avatar`, `DELETE /profile/avatar`, `POST /profile/cover`, `DELETE /profile/cover`, `POST /profile/background`, `DELETE /profile/background` | Gestão de dados do perfil, tema visual e uploads simétricos de mídia |
| `ButtonController` | `app/Http/Controllers/Api/ButtonController.php` | `GET /buttons`, `POST /buttons`, `PUT /buttons/{id}`, `PATCH /buttons/{id}`, `DELETE /buttons/{id}`, `DELETE /buttons`, `POST /buttons/reorder`, `POST /buttons/{id}/image` | CRUD de botões, reordenação atômica, exclusão em massa e upload de thumbnails |
| `OnboardingController` | `app/Http/Controllers/Api/OnboardingController.php` | `PUT /onboarding/links`, `PUT /onboarding/background`, `PUT /onboarding/buttons` | Fluxo guiado em 3 etapas para novos artistas |
| `UsernameController` | `app/Http/Controllers/Api/V1/UsernameController.php` | `GET /usernames/check` | Checagem de disponibilidade e validação de @username em tempo real |
| `PublicProfileController` | `app/Http/Controllers/PublicProfileController.php` | `GET /{username}` | Perfil público de alta performance com partição de botões e nó `theme` |
| `RedirectController` | `app/Http/Controllers/RedirectController.php` | `GET /r/{id}` | Redirecionamento 302 com incremento de cliques e telemetria analítica |
| `MetricsController` | `app/Http/Controllers/Api/MetricsController.php` | `POST /track`, `GET /metrics/summary` | Rastreio de pageviews/cliques e sumário analítico com filtros temporais |

---

## 3. Form Requests de Validação

| Nome | Caminho | Endpoint Associado | Validações Principais |
| :--- | :--- | :--- | :--- |
| `RegisterRequest` | `app/Http/Requests/RegisterRequest.php` | `POST /auth/register` | Username único, e-mail único, senha mín 6 chars, aceite de termos |
| `LoginRequest` | `app/Http/Requests/LoginRequest.php` | `POST /auth/login` | E-mail obrigatório, senha obrigatória |
| `StoreButtonRequest` | `app/Http/Requests/StoreButtonRequest.php` | `POST /buttons` | Título obrigatório, URL válida, limite global de 10 botões |
| `UpdateButtonRequest` | `app/Http/Requests/UpdateButtonRequest.php` | `PATCH /buttons/{id}` | Validação de campos parciais, modos de exibição e cores |
| `ReorderButtonsRequest` | `app/Http/Requests/ReorderButtonsRequest.php` | `POST /buttons/reorder` | Array `ordered_ids` obrigatório, IDs únicos e inteiros |
| `OnboardingLinksRequest` | `app/Http/Requests/OnboardingLinksRequest.php` | `PUT /onboarding/links` | Links selecionados e bio de até 160 caracteres |
| `OnboardingBackgroundRequest` | `app/Http/Requests/OnboardingBackgroundRequest.php` | `PUT /onboarding/background` | Cor hex ou ID de padrão geométrico |
| `OnboardingButtonsRequest` | `app/Http/Requests/OnboardingButtonsRequest.php` | `PUT /onboarding/buttons` | Formato do botão (`square`, `rounded`, `pill`) |

---

## 4. Migrations do Banco de Dados

| Migration | Tabela Criada/Modificada | Finalidade |
| :--- | :--- | :--- |
| `0001_01_01_000000_create_artists_table.php` | `artists`, `password_reset_tokens`, `sessions` | Tabela central de artistas e sessões |
| `0001_01_01_000001_create_buttons_table.php` | `buttons` | Tabela de links e botões com herança visual |
| `0001_01_01_000002_create_cache_table.php` | `cache`, `cache_locks` | Gerenciamento de cache do framework |
| `0001_01_01_000003_create_personal_access_tokens_table.php` | `personal_access_tokens` | Tokens de autenticação Laravel Sanctum |
| `2026_11_02_000001_add_typography_and_style_columns.php` | `artists`, `buttons` | Colunas de tipografia, wallpaper e estilos visuais |
| `2026_11_02_000002_create_page_views_and_button_clicks_tables.php` | `page_views`, `button_clicks` | Tabelas de telemetria analítica e cliques |

---

## 5. Testes Automatizados (Feature)

| Arquivo de Teste | Caminho | Suíte de Testes |
| :--- | :--- | :--- |
| `AuthTest.php` | `tests/Feature/AuthTest.php` | Cadastro, login, logout, me, recuperação de senha e rate limit |
| `AccountDeletionTest.php` | `tests/Feature/AccountDeletionTest.php` | Exclusão definitiva de conta com checagem de senha e expurgo de arquivos |
| `ButtonTest.php` | `tests/Feature/ButtonTest.php` | CRUD, ordenação atômica, exclusão em massa e validação anti-IDOR |
| `PublicProfileTest.php` | `tests/Feature/PublicProfileTest.php` | Perfil público `GET /{username}`, partição de links e nó `theme` |
| `RedirectTest.php` | `tests/Feature/RedirectTest.php` | Redirecionamento `GET /r/{id}` e incremento atômico de métricas |
| `MediaStorageTest.php` | `tests/Feature/MediaStorageTest.php` | Uploads de avatar e capa com prevenção de arquivos órfãos |
| `BackgroundMediaTest.php` | `tests/Feature/BackgroundMediaTest.php` | Uploads de background/wallpaper e mídias polimórficas no PATCH |
| `MetricsTest.php` | `tests/Feature/MetricsTest.php` | Telemetria `POST /track` e agregação analítica `GET /metrics/summary` |
| `UsernameCheckTest.php` | `tests/Feature/UsernameCheckTest.php` | Verificação de disponibilidade de @username e rotas reservadas |
| `CorsSecurityTest.php` | `tests/Feature/CorsSecurityTest.php` | Políticas de CORS e cabeçalhos de segurança |
| `OnboardingTest.php` | `tests/Feature/OnboardingTest.php` | Fluxo guiado em 3 etapas de cadastro e publicação de perfil |
