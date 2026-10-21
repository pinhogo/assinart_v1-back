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
