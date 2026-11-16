# Plano de Implementação — Backend Assin.art v1 (`tasks_backend.md`)

> **Repositório:** `assinart_v1_backend`  
> **Stack:** PHP 8.2+ · Laravel 11 · SQLite (Dev) / MySQL 8+ / PostgreSQL (Prod)  
> **Arquitetura:** MVC Padrão do Laravel (2 Tabelas Integradas: `artists` e `buttons`)  
> **Status:** Todas as Fases (0 a 6) Concluídas e 100% Testadas

---

## Situação Atual (Fase 0 Concluída)

- [x] **Task 0.1: Repositório Isolado e Versionamento Inicial**
  - Repositório `assinart_v1_backend` criado com Git, branch `main` e commits semânticos.
- [x] **Task 0.2: Migrations das 2 Tabelas Integradas**
  - Criação de `artists` (com suporte a `cover_url`, auth e estilos neo-brutalistas) e `buttons` (com `type`, `platform`, `clicks_count` e `order_index`).
- [x] **Task 0.3: Models Eloquent e Acessors de Herança Visual**
  - `Artist.php` (Authenticatable, hasMany buttons, route binding por `@username`).
  - `Button.php` (belongsTo, casts e accessors de herança visual cascade).
- [x] **Task 0.4: Seeders e Factories Realistas**
  - `ArtistFactory`, `ButtonFactory` e `DatabaseSeeder` com perfil de demonstração completo.
- [x] **Task 0.5: Dicionário de Dados Consolidado**
  - Documentação de todas as 31 colunas, tipos, regras e integridade em `docs/DATA_DICTIONARY.md`.

---

## Fase 1: Autenticação, Conta e Segurança (Auth Core)

- [x] **Task 1.1: Disponibilidade de Username em Tempo Real**
  - **Arquivos:** `app/Http/Controllers/Api/V1/UsernameController.php`, `routes/api.php`
  - **Escopo:**
    - [x] Sanitização rigorosa do `@` (remover `@` inicial, converter para minúsculas, espaços).
    - [x] Validação de formato via regex: `^[a-z0-9_.-]{2,30}$`.
    - [x] Verificação contra lista de rotas reservadas (`admin`, `api`, `login`, `cadastro`, `termos`, `privacidade`, `editor`, `playground`, `r`).
    - [x] Consulta otimizada de existência: `Artist::where('username', $clean)->exists()`.
    - [x] Resposta padronizada `{ "username": "...", "available": true/false, "message": "..." }`.
  - **Critério de aceite:** Rota pública `GET /api/v1/usernames/check?username=...` responde em < 20ms com mensagens alinhadas ao frontend. (Validado em `UsernameCheckTest.php` com 11 testes aprovados).

- [x] **Task 1.2: Cadastro e Login com Sessão Segura / Sanctum**
  - **Arquivos:** `app/Http/Controllers/Api/AuthController.php`, `app/Http/Requests/RegisterRequest.php`, `app/Http/Requests/LoginRequest.php`
  - **Escopo:**
    - [x] `RegisterRequest`: validação de username único, e-mail válido único, senha mínima de 6 caracteres e aceite dos termos (`agree_terms` / `agreeTerms`).
    - [x] Criação do registro em `artists` com hash de senha `Hash::make()`.
    - [x] Login seguro com proteção contra ataques de força bruta via `RateLimiter` (máx. 5 tentativas por minuto por IP com status 429).
    - [x] Geração de sessão web segura (cookies HttpOnly) e token Sanctum para clientes API.
    - [x] Rota `POST /api/v1/auth/logout` para revogação da sessão/token.
    - [x] Rota `GET /api/v1/auth/me` para hidratação de sessão no frontend.
  - **Critério de aceite:** Fluxo completo de cadastro, login, me e logout funcionando com proteção CSRF e rate limit. (Validado em `AuthTest.php` com 16 testes aprovados).

- [x] **Task 1.3: Recuperação de Senha e Confirmação de E-mail**
  - **Arquivos:** `app/Http/Controllers/Api/AuthController.php`, `routes/api_auth.php`
  - **Escopo:**
    - [x] `POST /api/v1/auth/forgot-password`: envio de e-mail com token seguro de redefinição.
    - [x] `POST /api/v1/auth/reset-password`: validação de token e atualização de senha.
    - [x] `POST /api/v1/auth/email/resend`: reenvio com throttling de 60 segundos (status 429).
    - [x] `POST /api/v1/auth/email/verify`: confirmação da titularidade do e-mail.
  - **Critério de aceite:** Telas `/esqueci-senha` e `/confirmacao-email` do frontend operam de ponta a ponta com as rotas.

---

## Fase 2: Onboarding em 3 Etapas e Publicação do Perfil

- [x] **Task 2.1: Etapa 1 do Onboarding — Seleção Inicial de Links e Bio**
  - **Arquivos:** `app/Http/Controllers/Api/OnboardingController.php`, `app/Http/Requests/OnboardingLinksRequest.php`
  - **Escopo:**
    - [x] Validação de array de até 5 links iniciais dentre as 17 redes sociais suportadas (`instagram`, `whatsapp`, `tiktok`, `youtube`, `spotify`, etc.).
    - [x] Sanitização e validação de URLs / números de telefone com DDI para WhatsApp.
    - [x] Atualização da `bio` no modelo `Artist` (limite estrito de 160 caracteres).
    - [x] Criação síncrona dos botões iniciais na tabela `buttons` com `order_index` sequencial (`0..N`).
  - **Critério de aceite:** Rota `PUT /api/v1/onboarding/links` salva dados e reflete instantaneamente no rascunho do artista. (Validado em `OnboardingTest.php` com 11 testes aprovados).

- [x] **Task 2.2: Etapa 2 do Onboarding — Personalização do Plano de Fundo**
  - **Arquivos:** `app/Http/Controllers/Api/OnboardingController.php`, `app/Http/Requests/OnboardingBackgroundRequest.php`
  - **Escopo:**
    - [x] Validação de `backgroundColor` (hexadecimal `#RRGGBB` válido).
    - [x] Validação de `backgroundId` (preset da paleta de 17 cores ou textura neo-brutalista).
    - [x] Atualização imediata em `artists.background_color` e `artists.background_id`.
  - **Critério de aceite:** Rota `PUT /api/v1/onboarding/background` persiste as escolhas de cores da Fase 6.2 do frontend.

- [x] **Task 2.3: Etapa 3 do Onboarding — Botões e Conclusão**
  - **Arquivos:** `app/Http/Controllers/Api/OnboardingController.php`, `app/Http/Requests/OnboardingButtonsRequest.php`
  - **Escopo:**
    - [x] Validação de `button_shape` (`square`, `rounded`, `pill`).
    - [x] Conclusão do onboarding (definição de `email_verified_at` e publicação).
    - [x] Resposta com URL de redirecionamento para o `/editor`.
  - **Critério de aceite:** Finaliza o fluxo guiado do artista e o habilita ao painel administrativo.

---

## Fase 3: Gestão de Mídia e Armazenamento (Uploads)

- [x] **Task 3.1: Upload de Foto de Perfil (Avatar)**
  - **Arquivos:** `app/Http/Controllers/Api/ProfileController.php`
  - **Escopo:**
    - [x] Validação de arquivo: JPG, PNG, WebP de até 5 MB (`5120 KB`).
    - [x] Exclusão automática do avatar antigo do disco antes de salvar o novo (prevenção de arquivos órfãos).
    - [x] Armazenamento em `storage/app/public/avatars/`.
    - [x] Retorno da URL absoluta pública formatada.
    - [x] Endpoint `DELETE /api/v1/profile/avatar` para remoção da foto.
  - **Critério de aceite:** Upload, substituição e remoção com integridade de disco. (Validado em `MediaStorageTest.php` com 12 testes aprovados).

- [x] **Task 3.2: Upload de Capa/Banner de Topo**
  - **Arquivos:** `app/Http/Controllers/Api/ProfileController.php`
  - **Escopo:**
    - [x] Validação de arquivo: JPG, PNG, WebP de até 10 MB.
    - [x] Armazenamento em `storage/app/public/covers/`.
    - [x] Atualização de `artists.cover_url`.
    - [x] Endpoint `DELETE /api/v1/profile/cover` para voltar ao fundo sólido.
  - **Critério de aceite:** Banner responsivo salvo e servido publicamente com suporte a visualização no editor e na página pública.

- [x] **Task 3.3: Upload de Thumbnail para Cards Featured**
  - **Arquivos:** `app/Http/Controllers/Api/ButtonController.php`
  - **Escopo:**
    - [x] Upload de imagem para botões do tipo `card_featured` (`buttons.image_url`).
    - [x] Armazenamento em `storage/app/public/cards/`.
  - **Critério de aceite:** Cards com thumbnail renderizam suas capas corretamente.

---

## Fase 4: Gestão Avançada de Links e Reordenação (Editor Core)

- [x] **Task 4.1: CRUD de Botões e Redes Sociais**
  - **Arquivos:** `app/Http/Controllers/Api/ButtonController.php`, `app/Http/Requests/StoreButtonRequest.php`, `app/Http/Requests/UpdateButtonRequest.php`
  - **Escopo:**
    - [x] `GET /api/v1/buttons`: Listagem de todos os links do artista logado ordenados por `order_index ASC`.
    - [x] `POST /api/v1/buttons`: Criação de novo botão calculando automaticamente o próximo `order_index`.
    - [x] Suporte a links de plataformas sociais conhecidas e links genéricos customizados (`platform = 'custom'`).
    - [x] `PUT /api/v1/buttons/{id}`: Atualização de título, URL, cor individual, fonte e visibilidade (`is_active`).
    - [x] `DELETE /api/v1/buttons/{id}`: Remoção com garantia de posse (`artist_id == user->id`).
  - **Critério de aceite:** Todas as operações do menu de links e modais de edição do `/editor` funcionando. (Validado em `ButtonTest.php` com 12 testes aprovados).

- [x] **Task 4.2: Reordenação Atômica via Drag & Drop**
  - **Arquivos:** `app/Http/Controllers/Api/ButtonController.php`, `app/Http/Requests/ReorderButtonsRequest.php`
  - **Escopo:**
    - [x] Endpoint `POST /api/v1/buttons/reorder`.
    - [x] Validação de payload `{ "ordered_ids": [5, 2, 8, 1] }`.
    - [x] Execução em transação atômica (`DB::transaction`) reatribuindo `order_index = $index`.
    - [x] Garantia de que nenhum botão de outro artista seja manipulado por ID injection (anti-IDOR).
  - **Critério de aceite:** Ao soltar o card no frontend, a nova ordenação é persistida instantaneamente no banco.

---

## Fase 5: Página Pública de Alta Performance e Métricas

- [x] **Task 5.1: Endpoint da Página Pública (`GET /{username}`)**
  - **Arquivos:** `app/Http/Controllers/PublicProfileController.php`, `routes/web_public.php`
  - **Escopo:**
    - [x] Rota pública limpa `GET /{username}`.
    - [x] Busca do artista por `@username` com eager loading de `activeButtons` em 1 query única.
    - [x] Resolução de estilos via Herança Inteligente (se `button.font_family` for nulo, herda `artist.font_family`).
    - [x] Tratamento de status:
      - `200 found`: Artista encontrado com links ativos.
      - `200 no_links`: Artista cadastrado, mas sem links publicados.
      - `404 not_found`: Username inexistente.
  - **Critério de aceite:** Resposta JSON ultra-rápida estruturada exatamente como esperado por `PublicProfileView.tsx`. (Validado em `PublicProfileTest.php` com 6 testes aprovados).

- [x] **Task 5.2: Rastreador de Cliques Atômico e Redirecionador (`GET /r/{id}`)**
  - **Arquivos:** `app/Http/Controllers/RedirectController.php`, `routes/web_public.php`
  - **Escopo:**
    - [x] Rota pública `GET /r/{id}`.
    - [x] Busca atômica de botão ativo.
    - [x] Incremento leve de métrica: `$button->increment('clicks_count')`.
    - [x] Redirecionamento HTTP 302 direto para `button->url`.
    - [x] Rate limiting leve por IP para mitigar flood de bots.
  - **Critério de aceite:** Ao clicar no link, o contador sobe em +1 e o visitante é redirecionado imediatamente para o destino. (Validado em `RedirectTest.php` com 5 testes aprovados).

---

## Fase 6: Testes Automatizados, CORS e Integração com o Frontend

- [x] **Task 6.1: Suíte de Testes Automatizados no Backend (PHPUnit / Feature)**
  - **Arquivos:** `tests/Feature/AuthTest.php`, `tests/Feature/PublicProfileTest.php`, `tests/Feature/ButtonTest.php`, `tests/Feature/RedirectTest.php`, `tests/Feature/OnboardingTest.php`, `tests/Feature/MediaStorageTest.php`, `tests/Feature/UsernameCheckTest.php`, `tests/Feature/CorsSecurityTest.php`
  - **Escopo:**
    - [x] 77 testes Feature automatizados cobrindo 100% dos fluxos.
    - [x] 299 asserções rigorosas sem nenhuma falha.
  - **Critério de aceite:** 100% dos testes passando via `artisan test` (77/77 PASS).

- [x] **Task 6.2: Configuração de CORS e Segurança**
  - **Arquivos:** `config/cors.php`, `bootstrap/app.php`
  - **Escopo:**
    - [x] Habilitar origens: `http://localhost:5173` (Vite dev) e domínio de produção `https://assin.art`.
    - [x] Headers de proteção contra CSRF, XSS e Clickjacking.
  - **Critério de aceite:** Validado em `CorsSecurityTest.php` com 4 testes aprovados.

- [x] **Task 6.3: Wiki Interativa e Documentação HTML**
  - **Arquivo:** `documentacao/index.html` (296 KB)
  - **Escopo:**
    - [x] Portal interativo autônomo com design Neo-Brutalista.
    - [x] Catálogo de todos os 28 endpoints com métodos, headers, payloads e respostas.
    - [x] Dicionário de dados completo (35 colunas) e herança de estilos.
  - **Critério de aceite:** Abre diretamente com 2 cliques em qualquer navegador web.

---

## Fase 7: Manutenção e Evolução de Contratos (`FIX-API-07`)

- [x] **Task 7.1 (`#262` / `FIX-API-07`): Persistência de Publicação (`is_published` / `publication_status`) e Guarda de Rascunho**
  - **Arquivos:** `database/migrations/2026_11_13_000001_add_is_published_to_artists_table.php`, `app/Models/Artist.php`, `database/factories/ArtistFactory.php`, `app/Http/Controllers/Api/AuthController.php`, `app/Http/Controllers/Api/ProfileController.php`, `app/Http/Controllers/PublicProfileController.php`
  - **Escopo:**
    - [x] Adicionar coluna booleana `is_published` na tabela `artists` (padrão `true` no schema para contas legadas e `false` no endpoint `POST /api/v1/auth/register`).
    - [x] Expor `is_published` (`boolean`) e `publication_status` (`"draft" | "published"`) no model `Artist` e nas respostas de `auth/register`, `auth/login`, `auth/me` e `/api/v1/profile`.
    - [x] Validar e normalizar `is_published` e `publication_status` em `PATCH /api/v1/profile`.
    - [x] Bloquear `GET /{username}` com HTTP `404 not_found` para terceiros quando `is_published` é `false`, preservando pré-visualização `200 OK` para o proprietário autenticado via Sanctum.
    - [x] Cobrir o fluxo completo com testes Feature em `tests/Feature/AuthTest.php`, `tests/Feature/PublicProfileTest.php` e `tests/Feature/ProfilePublicationTest.php`.

- [x] **Task 7.2 (`#259` / `FIX-API-06`): Cor Individual de Botão Circular em `GET /{username}`**
  - **Arquivos:** `app/Models/Button.php`, `app/Http/Controllers/Concerns/FormatsPublicProfile.php`, `tests/Feature/PublicProfileTest.php`
  - **Escopo:**
    - [x] Priorize `buttons.button_color` hexadecimal válido em `Button::getResolvedCircularBgAttribute()` (commit `168b9f6`).
    - [x] Serialize `button_color` e `resolved_button_color` em `FormatsPublicProfile::formatCircularLinks()` (commit `168b9f6`).
    - [x] Fixe `button_color = null` na fixture circular determinística de `PublicProfileTest` (commit `b84b81b`).
    - [x] Cubra cor individual `#ACE1AF` com fallback `auto` para `black`, `white` e `default` em `PublicProfileTest`.
    - [x] Cubra `button_color` vazio ou inválido com fallback por `icon_variant` em `PublicProfileTest`.
    - [x] Cubra `circular_icon_background` hexadecimal do artista como fallback em `PublicProfileTest`.
    - [x] Atualize o contrato de `circular_links[]` na seção 7.1 de `context/api_contracts.md`.
  - **Critério de aceite:** `resolved_circular_bg` retorna `#ACE1AF` para botão circular com `button_color` hexadecimal válido. Execução de `php artisan test` fica a cargo do orquestrador.

---

## Fase 8: Infraestrutura de Produção no Azure (`DONE`)

> Spec completa em `assinart_v1/context/BACKLOG_MELHORIAS.md` e nas Issues `#327` / `#328`. SQLite fica restrito ao dev local e ao `phpunit.xml`.

- [x] **Task 8.1 (`#327` / `API-12`): Banco de Produção no Azure e SQLite Apenas em Dev**
  - **Arquivos:** `.env.example`, `config/database.php`, `database/migrations/0001_01_01_000004_create_jobs_table.php`, `phpunit.xml`, `bootstrap/app.php`, `tests/Feature/DatabaseProductionConfigTest.php`, `context/data_dictionary.md`
  - **Escopo:**
    - [x] Publique `config/database.php` e configure SSL/TLS via ambiente para Azure PostgreSQL ou MySQL.
    - [x] Crie migration para `jobs`, `job_batches` e `failed_jobs` ou alinhe `QUEUE_CONNECTION`.
    - [x] Atualize `.env.example` restringindo SQLite ao dev local.
    - [x] Audite migrations e `LOWER(username)` em `UsernameController.php` e `ArtistUsername.php` no SGBD alvo.
    - [x] Valide `/up` com conexão ao banco e execute `migrate:fresh --seed` no ambiente alvo.
    - [x] Atualize testes PHPUnit e `context/data_dictionary.md`.

- [x] **Task 8.2 (`#328` / `API-13`): Storage de Mídia no Azure Blob Storage**
  - **Arquivos:** `composer.json`, `composer.lock`, `config/filesystems.php`, `app/Http/Controllers/Api/Concerns/InteractsWithMediaStorage.php`, `ManagesProfileMedia.php`, `ManagesButtonReorderAndImage.php`, `app/Http/Controllers/Api/ButtonController.php`, `app/Http/Requests/UpdateProfileRequest.php`, `tests/Feature/MediaStorageTest.php`, `tests/Feature/BackgroundMediaTest.php`, `tests/Feature/AccountDeletionTest.php`, `context/api_contracts.md`
  - **Escopo:**
    - [x] Adicione adapter Flysystem Azure Blob (`azure-oss/storage-blob-laravel`) e registre disco `azure` em `config/filesystems.php`.
    - [x] Configure `MEDIA_DISK` e `AZURE_STORAGE_*` em `.env.example`.
    - [x] Centralize disco de mídia e extração de path em `InteractsWithMediaStorage.php`.
    - [x] Remova hardcode de `disk('public')` e `'/storage/'` nos Concerns e em `ButtonController.php`.
    - [x] Revise `UpdateProfileRequest.php` e documente CORS e acesso público do container.
    - [x] Atualize `MediaStorageTest.php`, `BackgroundMediaTest.php` e `AccountDeletionTest.php`.

---

## Fase 9: Deploy no Azure App Service (`BACKLOG - FLUXO B`)

> Fonte: `context/DEPLOY_PLAN.md` e revisão de furos. Esta fase usa SQLite em `/home/data` apenas no MVP. As Tasks 8.1 e 8.2 substituem SQLite e disco local após o MVP.
> Execute a Task 9.1 antes das demais. Rode os testes em PHP 8.3 real, não no PHP do Termux (o polyfill de `finfo` mascara a validação de MIME).

- [ ] **Task 9.1 (`API-14`): Proxy Reverso, CORS e Hardening**
  - **Arquivos:** `bootstrap/app.php`, `config/cors.php`, `.env.example`, `tests/Feature/CorsTest.php`
  - **Escopo:**
    - [x] Adicione `$middleware->trustProxies(at: '*')` em `bootstrap/app.php` *(concluído em `API-12` / `5635c9c`)*.
    - [ ] Adicione `https://www.assin.art` e `CORS_ALLOWED_ORIGINS` em `config/cors.php`.
    - [ ] Limite `paths` de CORS a `api/*` e às rotas públicas necessárias. Remova o `'*'`.
    - [ ] Defina `supports_credentials` como `false`. A autenticação usa Bearer Token.
    - [ ] Remova `SANCTUM_STATEFUL_DOMAINS` do plano. `statefulApi()` está desativado.
    - [ ] Cubra o preflight `OPTIONS` e o IP real via `X-Forwarded-For` em testes Feature.
    - [ ] Verifique que `/storage/*` não colide com `GET /{username}`.
  - **Critério de aceite:** O preflight de `https://assin.art` retorna `204` com `Access-Control-Allow-Origin`. O `RateLimiter` usa o IP do cliente.

- [ ] **Task 9.2 (`API-15`): Arquivos de Runtime do Azure (`azure/`)**
  - **Arquivos:** `azure/nginx.conf`, `azure/startup.sh`, `public/.user.ini`, `.gitattributes`
  - **Escopo:**
    - [ ] Crie `azure/nginx.conf` com `root` em `public`, `client_max_body_size 16M` e `try_files`.
    - [ ] Bloqueie arquivos ocultos e a execução de `.php` em `/storage/`.
    - [ ] Crie `public/.user.ini` com `upload_max_filesize=12M` e `post_max_size=16M`. Confirme que o `PHP_INI_SCAN_DIR` do runtime existe antes de usá-lo.
    - [ ] Crie `azure/startup.sh` idempotente. Crie as pastas e o symlink de `storage/app/public` para `/home/data`.
    - [ ] Copie o Nginx e recarregue com `nginx -s reload`. Teste o comando no App Service real.
    - [ ] Copie o backup de `database.sqlite` com timestamp antes do `migrate --force`.
    - [ ] Gere `config:cache`, `route:cache` e `view:cache` no build, não no startup. Valide `route:cache` localmente.
    - [ ] Configure `.gitattributes` com `export-ignore` para `context/`, `docs/`, `documentacao/`, `tests/`, `*.md`, `run.sh` e `composer.phar`.
  - **Critério de aceite:** O startup conclui em menos de 230 s. Um segundo boot não altera dados.

- [ ] **Task 9.3 (`API-16`): Limpeza do Repositório e Pacote de Build**
  - **Arquivos:** `.gitignore`, `bootstrap/cache/`, `database/database.sqlite`, `composer.phar`, `.github/workflows/deploy-backend.yml`
  - **Escopo:**
    - [ ] Remova `bootstrap/cache/*.php`, `database/database.sqlite` e `composer.phar` do índice Git.
    - [ ] Adicione os mesmos caminhos ao `.gitignore`.
    - [ ] Crie o workflow com PHP 8.3 (`shivammathur/setup-php@v2`), `composer install --no-dev` e `php artisan test`.
    - [ ] Gere o ZIP sem `.env*`, sem `tests/` e sem `vendor` de desenvolvimento.
    - [ ] Publique o ZIP com `az webapp deploy`. Guarde o ZIP anterior como artefato de rollback.
    - [ ] Confirme que nenhum `.env` entra no ZIP.
  - **Critério de aceite:** O ZIP não contém `collision`, `termwind`, `.env` nem `.sqlite`. A suíte passa em PHP 8.3.

- [ ] **Task 9.4 (`API-17`): Provisionamento Azure e App Settings**
  - **Arquivos:** `azure/provision.sh`, `azure/appsettings.example.env`
  - **Escopo:**
    - [ ] Valide a assinatura Azure for Students, a quota de B1 e a região (`brazilsouth` ou `eastus2`).
    - [ ] Valide a unicidade do nome do Web App.
    - [ ] Crie `azure/provision.sh` com grupo, plano B1 Linux e Web App `PHP:8.3`.
    - [ ] Ative `https-only`, `always-on`, TLS 1.2 e FTPS desativado.
    - [ ] Defina `WEBSITES_CONTAINER_START_TIME_LIMIT` e fixe uma única instância.
    - [ ] Configure `APP_KEY`, `APP_URL`, `FILESYSTEM_DISK=public`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=file`, `CACHE_STORE=file` e `LOG_CHANNEL=stderr`.
    - [ ] Crie um alerta de custo e de orçamento no Azure.
  - **Critério de aceite:** `GET /up` retorna `200` em `https://<app>.azurewebsites.net`.

- [ ] **Task 9.5 (`API-18`): Risco de SQLite em `/home` e Backup**
  - **Arquivos:** `azure/backup.sh`, `app/Http/Controllers/Api/TrackController.php`
  - **Escopo:**
    - [ ] Execute teste de carga em `POST /api/v1/track` e `GET /r/{id}` com SQLite em `/home/data`.
    - [ ] Habilite `PRAGMA journal_mode=WAL` e `busy_timeout` na conexão SQLite, se o teste mostrar `database is locked`.
    - [ ] Crie `azure/backup.sh` para copiar o banco com timestamp.
    - [ ] Documente o procedimento de restauração.
    - [ ] Defina o gatilho de migração para a Task 8.1 (PostgreSQL ou MySQL).
  - **Critério de aceite:** O teste não registra `database is locked`. O backup restaura o banco.

- [ ] **Task 9.6 (`API-19`): Smoke Test, E2E e Rollback**
  - **Arquivos:** `context/DEPLOY_PLAN.md`
  - **Escopo:**
    - [ ] Conecte o frontend: defina `VITE_API_BASE_URL` e `VITE_USE_MOCK_API=false` e dispare `deploy-pages.yml`.
    - [ ] Valide DNS e HTTPS de `assin.art` e `www.assin.art`.
    - [ ] Execute os smoke tests: `/up`, `/`, `/api/v1/usernames/check` e o preflight `OPTIONS`.
    - [ ] Execute o E2E anônimo: cadastro, upload maior que 2 MB, publicação, `GET /{username}`, `POST /track` e `GET /r/{id}`.
    - [ ] Reinicie o Web App e confirme que banco e uploads persistem.
    - [ ] Documente que `forgot-password` não envia e-mail (`MAIL_MAILER=log`).
    - [ ] Atualize o rollback: restaure o ZIP anterior, restaure o backup do banco e reverta o schema.
    - [ ] Atualize `context/DEPLOY_PLAN.md` com as correções desta fase.
  - **Critério de aceite:** O E2E passa em produção. O rollback roda em menos de 15 minutos.

---

## Fase 10: Confirmação de E-mail e E-mail de Contato (`BACKLOG - FLUXO B`)

> Origem: `context/BACKLOG_MELHORIAS.md` (`API-20`, `API-21`, `API-22`) e `assinart_v1/context/BACKLOG_MELHORIAS.md` (`CAD-11`, `FIX-GLB-22`).
> `API-20` sobrepõe parte de `SEC-14` (`SEC-01`); não execute as duas em paralelo.

- [ ] **Task 10.1 (`API-20`): E-mail de Confirmação Real e Validação de Token**
  - **Arquivos:** `database/migrations/`, `app/Mail/VerifyEmailMail.php`, `resources/views/emails/verify-email.blade.php`, `app/Http/Controllers/Api/AuthController.php`, `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php`, `app/Http/Controllers/Api/OnboardingController.php`, `routes/api_auth.php`, `.env.example`, `tests/Feature/EmailVerificationTest.php`
  - **Escopo:**
    - [ ] Crie a migration `email_verification_tokens` (`email`, `token_hash`, `expires_at`, `created_at`).
    - [ ] Crie `VerifyEmailMail` com link `FRONTEND_URL/confirmacao-email?email=...&token=...`.
    - [ ] Dispare o e-mail em `register` e em `resendVerification` com throttle de 60 s por e-mail.
    - [ ] Valide `email` e `token` em `POST /api/v1/auth/email/verify` e invalide o token após uso.
    - [ ] Remova a auto-verificação de `email_verified_at` em `OnboardingController::saveButtons`.
    - [ ] Cubra todo o fluxo com `Mail::fake()` em `tests/Feature/EmailVerificationTest.php`.
  - **Critério de aceite:** Conta nova mantém `email_verified_at = null` até validar o token recebido por e-mail.

- [ ] **Task 10.2 (`API-21`): Bloqueio de Publicação sem E-mail Verificado**
  - **Arquivos:** `app/Http/Requests/UpdateProfileRequest.php`, `app/Http/Controllers/Api/ProfileController.php`, `tests/Feature/ProfilePublicationTest.php`
  - **Escopo:**
    - [ ] Rejeite `is_published: true` / `publication_status: 'published'` com `422` (`code: email_not_verified`) enquanto `email_verified_at` for nulo.
    - [ ] Mantenha rotas de onboarding e rascunho liberadas antes da confirmação.
    - [ ] Cubra o bloqueio e a liberação pós-verificação em `ProfilePublicationTest.php`.
  - **Critério de aceite:** Conta não verificada recebe `422` ao tentar publicar e `200` após confirmar o e-mail.

- [ ] **Task 10.3 (`API-22`): Roteamento de `sac@assin.art` e `contato@assin.art` para Gmail Pessoal**
  - **Arquivos:** `.env.example`, `context/DEPLOY_PLAN.md`
  - **Escopo:**
    - [ ] Configure Cloudflare Email Routing ou ImprovMX para `sac@assin.art` e `contato@assin.art` encaminhando ao Gmail pessoal (sem commitar o Gmail no Git).
    - [ ] Publique `MX`, `SPF` e `DMARC` no DNS de `assin.art` alinhados ao provedor transacional de `API-20`.
    - [ ] Alinhe `MAIL_FROM_ADDRESS` e `MAIL_REPLY_TO_ADDRESS` em `.env.example` e documente o DNS em `context/DEPLOY_PLAN.md`.
    - [ ] Valide com `dig MX assin.art` e envio de e-mail externo real para `sac@assin.art`.
  - **Critério de aceite:** E-mail enviado para `sac@assin.art` chega à caixa do Gmail pessoal sem erro de DNS/SPF.
