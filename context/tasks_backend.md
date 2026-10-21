# Plano de Implementação — Backend Assin.art v1 (`tasks_backend.md`)

> **Repositório:** `assinart_v1_backend`  
> **Stack:** PHP 8.2+ · Laravel 11 · SQLite (Dev) / MySQL 8+ / PostgreSQL (Prod)  
> **Arquitetura:** MVC Padrão do Laravel (2 Tabelas Integradas: `artists` e `buttons`)  
> **Status:** Fase 0 Concluída | Fases 1 a 6 Disponíveis

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

- [ ] **Task 1.1: Disponibilidade de Username em Tempo Real**
  - **Arquivos:** `app/Http/Controllers/Api/V1/UsernameController.php`, `routes/api.php`
  - **Escopo:**
    - [ ] Sanitização rigorosa do `@` (remover `@` inicial, converter para minúsculas, espaços).
    - [ ] Validação de formato via regex: `^[a-z0-9_.-]{2,30}$`.
    - [ ] Verificação contra lista de rotas reservadas (`admin`, `api`, `login`, `cadastro`, `termos`, `privacidade`, `editor`, `playground`, `r`).
    - [ ] Consulta otimizada de existência: `Artist::where('username', $clean)->exists()`.
    - [ ] Resposta padronizada `{ "username": "...", "available": true/false, "message": "..." }`.
  - **Critério de aceite:** Rota pública `GET /api/v1/usernames/check?username=...` responde em < 20ms com mensagens alinhadas ao frontend.

- [ ] **Task 1.2: Cadastro e Login com Sessão Segura / Sanctum**
  - **Arquivos:** `app/Http/Controllers/Api/V1/AuthController.php`, `app/Http/Requests/RegisterRequest.php`, `app/Http/Requests/LoginRequest.php`
  - **Escopo:**
    - [ ] `RegisterRequest`: validação de username único, e-mail válido único, senha mínima de 6 caracteres e aceite dos termos.
    - [ ] Criação do registro em `artists` com hash de senha `Hash::make()`.
    - [ ] Login seguro com proteção contra ataques de força bruta via `RateLimiter` (máx. 5 tentativas por minuto por IP).
    - [ ] Geração de sessão web segura (cookies HttpOnly) e token Sanctum para clientes API.
    - [ ] Rota `POST /api/v1/auth/logout` para revogação da sessão/token.
    - [ ] Rota `GET /api/v1/auth/me` para hidratação de sessão no frontend.
  - **Critério de aceite:** Fluxo completo de cadastro, login, me e logout funcionando com proteção CSRF e rate limit.

- [ ] **Task 1.3: Recuperação de Senha e Confirmação de E-mail**
  - **Arquivos:** `app/Http/Controllers/Api/V1/AuthController.php`, `app/Http/Requests/ForgotPasswordRequest.php`, `app/Http/Requests/ResetPasswordRequest.php`
  - **Escopo:**
    - [ ] `POST /api/v1/auth/forgot-password`: envio de e-mail com token seguro de redefinição.
    - [ ] `POST /api/v1/auth/reset-password`: validação de token e atualização de senha.
    - [ ] `POST /api/v1/auth/email/resend`: reenvio com throttling de 60 segundos (alinhado ao contador do frontend).
    - [ ] `POST /api/v1/auth/email/verify`: confirmação da titularidade do e-mail.
  - **Critério de aceite:** Telas `/esqueci-senha` e `/confirmacao-email` do frontend operam de ponta a ponta com as rotas.

---

## Fase 2: Onboarding em 3 Etapas e Publicação do Perfil

- [ ] **Task 2.1: Etapa 1 do Onboarding — Seleção Inicial de Links e Bio**
  - **Arquivos:** `app/Http/Controllers/Api/V1/OnboardingController.php`, `app/Http/Requests/OnboardingLinksRequest.php`
  - **Escopo:**
    - [ ] Validação de array de até 5 links iniciais dentre as 17 redes sociais suportadas (`instagram`, `whatsapp`, `tiktok`, `youtube`, `spotify`, etc.).
    - [ ] Sanitização e validação de URLs / números de telefone com DDI para WhatsApp.
    - [ ] Atualização da `bio` no modelo `Artist` (limite estrito de 160 caracteres).
    - [ ] Criação síncrona dos botões iniciais na tabela `buttons` com `order_index` sequencial (`0..N`).
  - **Critério de aceite:** Rota `PUT /api/v1/onboarding/links` salva dados e reflete instantaneamente no rascunho do artista.

- [ ] **Task 2.2: Etapa 2 do Onboarding — Personalização do Plano de Fundo**
  - **Arquivos:** `app/Http/Controllers/Api/V1/OnboardingController.php`, `app/Http/Requests/OnboardingBackgroundRequest.php`
  - **Escopo:**
    - [ ] Validação de `backgroundColor` (hexadecimal `#RRGGBB` válido).
    - [ ] Validação de `backgroundId` (preset da paleta de 17 cores ou textura neo-brutalista).
    - [ ] Atualização imediata em `artists.background_color` e `artists.background_id`.
  - **Critério de aceite:** Rota `PUT /api/v1/onboarding/background` persiste as escolhas de cores da Fase 6.2 do frontend.

- [ ] **Task 2.3: Etapa 3 do Onboarding — Botões e Conclusão**
  - **Arquivos:** `app/Http/Controllers/Api/V1/OnboardingController.php`, `app/Http/Requests/OnboardingButtonsRequest.php`
  - **Escopo:**
    - [ ] Validação de `button_shape` (`square`, `rounded`, `pill`).
    - [ ] Conclusão do onboarding (definição de `email_verified_at` ou flag de conclusão).
    - [ ] Resposta com URL de redirecionamento para o `/editor`.
  - **Critério de aceite:** Finaliza o fluxo guiado do artista e o habilita ao painel administrativo.

---

## Fase 3: Gestão de Mídia e Armazenamento (Uploads)

- [ ] **Task 3.1: Upload de Foto de Perfil (Avatar)**
  - **Arquivos:** `app/Http/Controllers/Api/V1/ProfileController.php`, `app/Http/Requests/UploadAvatarRequest.php`
  - **Escopo:**
    - [ ] Validação de arquivo: JPG, PNG, WebP de até 5 MB (`5120 KB`).
    - [ ] Exclusão automática do avatar antigo do disco antes de salvar o novo (prevenção de arquivos órfãos).
    - [ ] Armazenamento em `storage/app/public/avatars/`.
    - [ ] Retorno da URL absoluta pública formatada.
    - [ ] Endpoint `DELETE /api/v1/profile/avatar` para remoção da foto.
  - **Critério de aceite:** Upload, substituição e remoção com integridade de disco e validação idêntica ao `validateAvatarFile` do frontend.

- [ ] **Task 3.2: Upload de Capa/Banner de Topo**
  - **Arquivos:** `app/Http/Controllers/Api/V1/ProfileController.php`, `app/Http/Requests/UploadCoverRequest.php`
  - **Escopo:**
    - [ ] Validação de arquivo: JPG, PNG, WebP de até 10 MB.
    - [ ] Armazenamento em `storage/app/public/covers/`.
    - [ ] Atualização de `artists.cover_url`.
    - [ ] Endpoint `DELETE /api/v1/profile/cover` para voltar ao fundo sólido.
  - **Critério de aceite:** Banner responsivo salvo e servido publicamente com suporte a visualização no editor e na página pública.

- [ ] **Task 3.3: Upload de Thumbnail para Cards Featured**
  - **Arquivos:** `app/Http/Controllers/Api/V1/ButtonController.php`
  - **Escopo:**
    - [ ] Upload de imagem para botões do tipo `card_featured` (`buttons.image_url`).
    - [ ] Armazenamento em `storage/app/public/cards/`.
  - **Critério de aceite:** Cards com thumbnail renderizam suas capas corretamente.

---

## Fase 4: Gestão Avançada de Links e Reordenação (Editor Core)

- [ ] **Task 4.1: CRUD de Botões e Redes Sociais**
  - **Arquivos:** `app/Http/Controllers/Api/V1/ButtonController.php`, `app/Http/Requests/StoreButtonRequest.php`, `app/Http/Requests/UpdateButtonRequest.php`
  - **Escopo:**
    - [ ] `GET /api/v1/buttons`: Listagem de todos os links do artista logado ordenados por `order_index ASC`.
    - [ ] `POST /api/v1/buttons`: Criação de novo botão calculando automaticamente o próximo `order_index`.
    - [ ] Suporte a links de plataformas sociais conhecidas e links genéricos customizados (`platform = 'custom'`).
    - [ ] `PUT /api/v1/buttons/{id}`: Atualização de título, URL, cor individual, fonte e visibilidade (`is_active`).
    - [ ] `DELETE /api/v1/buttons/{id}`: Remoção com garantia de posse (`artist_id == user->id`).
  - **Critério de aceite:** Todas as operações do menu de links e modais de edição do `/editor` funcionando.

- [ ] **Task 4.2: Reordenação Atômica via Drag & Drop**
  - **Arquivos:** `app/Http/Controllers/Api/V1/ButtonController.php`, `app/Http/Requests/ReorderButtonsRequest.php`
  - **Escopo:**
    - [ ] Endpoint `POST /api/v1/buttons/reorder`.
    - [ ] Validação de payload `{ "ordered_ids": [5, 2, 8, 1] }`.
    - [ ] Execução em transação atômica (`DB::transaction`) reatribuindo `order_index = $index`.
    - [ ] Garantia de que nenhum botão de outro artista seja manipulado por ID injection.
  - **Critério de aceite:** Ao soltar o card no frontend, a nova ordenação é persistida instantaneamente no banco.

---

## Fase 5: Página Pública de Alta Performance e Métricas

- [ ] **Task 5.1: Endpoint da Página Pública (`GET /{username}`)**
  - **Arquivos:** `app/Http/Controllers/PublicProfileController.php`, `app/Http/Resources/PublicProfileResource.php`
  - **Escopo:**
    - [ ] Rota pública limpa `GET /{username}`.
    - [ ] Busca do artista por `@username` com eager loading de `activeButtons` em 1 query única.
    - [ ] Resolução de estilos via Herança Inteligente (se `button.font_family` for nulo, herda `artist.font_family`).
    - [ ] Tratamento de status:
      - `200 found`: Artista encontrado com links ativos.
      - `200 no_links`: Artista cadastrado, mas sem links publicados.
      - `404 not_found`: Username inexistente.
  - **Critério de aceite:** Resposta JSON ultra-rápida estruturada exatamente como esperado por `PublicProfileView.tsx`.

- [ ] **Task 5.2: Rastreador de Cliques Atômico e Redirecionador (`GET /r/{id}`)**
  - **Arquivos:** `app/Http/Controllers/RedirectController.php`, `routes/web.php`
  - **Escopo:**
    - [ ] Rota pública `GET /r/{id}`.
    - [ ] Busca atômica de botão ativo.
    - [ ] Incremento leve de métrica: `$button->increment('clicks_count')`.
    - [ ] Redirecionamento HTTP 302 direto para `button->url`.
    - [ ] Rate limiting leve (`throttle:60,1`) por IP para mitigar flood de bots.
  - **Critério de aceite:** Ao clicar no link, o contador sobe em +1 e o usuário é redirecionado imediatamente para o destino.

---

## Fase 6: Testes Automatizados, CORS e Integração com o Frontend

- [ ] **Task 6.1: Suíte de Testes Automatizados no Backend (PHPUnit / Pest)**
  - **Arquivos:** `tests/Feature/AuthTest.php`, `tests/Feature/PublicProfileTest.php`, `tests/Feature/ButtonTest.php`, `tests/Feature/RedirectTest.php`
  - **Escopo:**
    - [ ] Testes de validação de username e colisão com rotas reservadas.
    - [ ] Testes de autenticação e proteção de rotas privadas.
    - [ ] Testes de integridade do contador de cliques.
    - [ ] Testes de reordenação Drag & Drop com autorização.
  - **Critério de aceite:** 100% dos testes passando via `php artisan test`.

- [ ] **Task 6.2: Configuração de CORS e Segurança**
  - **Arquivos:** `config/cors.php`, `bootstrap/app.php`
  - **Escopo:**
    - [ ] Habilitar origens: `http://localhost:5173` (Vite dev) e domínio de produção `https://assin.art`.
    - [ ] Headers de proteção contra CSRF, XSS e Clickjacking.
  - **Critério de aceite:** Zero erros de bloqueio de CORS no console do navegador do frontend.

- [ ] **Task 6.3: Conexão End-to-End Frontend <-> Backend**
  - **Arquivos:** `src/services/api.service.ts` no repositório `assinart_v1`
  - **Escopo:**
    - [ ] Configurar cliente HTTP com `VITE_API_BASE_URL=http://localhost:8000`.
    - [ ] Ligar formulários de cadastro, login, editor e página pública aos endpoints reais.
  - **Critério de aceite:** Sistema completo rodando de ponta a ponta com persistência real em SQLite.
