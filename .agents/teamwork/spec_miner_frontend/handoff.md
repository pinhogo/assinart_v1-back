# Relatório de Especificação Frontend (Handoff) — Assin.art v1

> **Agente:** `spec_miner_frontend` (teamwork_preview_spec_miner)  
> **Data:** 2026-10-21  
> **Origem da Investigação:** `c:\Users\pinho\Desktop\Assin.art\assinart_v1`  
> **Destino do Backend:** `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend`  
> **Objetivo:** Mapeamento integral e minucioso do contrato de API exigido pelo frontend para garantir 100% de integração sem atrito.

---

## 1. Observation (Observações Diretas do Código)

A análise detalhada de 124 arquivos no frontend (`assinart_v1/src/`, `assinart_v1/test/`, `assinart_v1/PO/`) revelou as seguintes evidências concretas:

### 1.1 Configuração de Cliente HTTP e Comunicação
- **Biblioteca HTTP:** O arquivo `package.json` (linhas 22-29) lista unicamente `react`, `react-dom`, `react-easy-crop`, `react-router-dom` e `tailwindcss`. **Não há Axios instalado**. O frontend utiliza `fetch` nativo do navegador.
- **Base URL:** Definido no arquivo `backend.md` (linha 391) como `VITE_API_BASE_URL=http://localhost:8000` via Vite (`import.meta.env.VITE_API_BASE_URL`).
- **Cabeçalhos Mandatórios:**
  - `Accept: application/json`
  - `Content-Type: application/json` (para payloads JSON)
  - `Content-Type: multipart/form-data` (gerenciado automaticamente pelo navegador ao usar `FormData`)
  - `Authorization: Bearer <sanctum_token>` para autenticação de API, ou Cookies de Sessão Sanctum/Web com header `X-XSRF-TOKEN` e `credentials: 'include'`.

---

### 1.2 Autenticação, Conta e Disponibilidade de Username

#### A. Verificação de `@username`
- **Arquivo de Origem:** `src/controllers/hooks/landing/useLandingController.ts` (linhas 4-55) e `src/controllers/hooks/auth/useSignupController.ts` (linhas 47-79).
- **Rota:** `GET /api/v1/usernames/check?username={username}`
- **Normalização e Sanitização do Input:**
  1. Converte para minúsculas (`.toLowerCase()`)
  2. Remove todos os espaços em branco (`.replace(/\s/g, '')`)
  3. Remove `@` no início (`.replace(/^@+/, '')`)
- **Regras de Validação no Frontend:**
  - Tamanho: entre 3 e 30 caracteres (`normalized.length >= 3 && normalized.length <= 30`)
  - Caracteres permitidos: `/^[a-z0-9_.-]+$/` (letras minúsculas, números, ponto, sublinhado e hífen)
  - Lista de nomes reservados bloqueados no frontend: `admin`, `teste`, `api`, `assinart`, `cadastro`, `entrar`, `login`, `privacidade`, `sac`, `suporte`, `termos`, `www`. (O backend estende para rotas do sistema como `r`, `app`, `auth`, `editor`, `playground`, `status`, etc.)
- **Estrutura de Resposta Esperada:**
  - **Sucesso (Disponível - 200 OK):**
    ```json
    {
      "username": "gabriel_arte",
      "available": true,
      "reason": null,
      "message": "Disponível"
    }
    ```
  - **Em uso (Duplicado - 200 OK):**
    ```json
    {
      "username": "gabriel_arte",
      "available": false,
      "reason": "taken",
      "message": "Esse @ já está em uso"
    }
    ```
  - **Reservado pelo Sistema (200 OK):**
    ```json
    {
      "username": "admin",
      "available": false,
      "reason": "reserved",
      "message": "Este nome de usuário é reservado pelo sistema."
    }
    ```
  - **Formato Inválido (200 OK):**
    ```json
    {
      "username": "a",
      "available": false,
      "reason": "invalid_format",
      "message": "Use de 3 a 30 caracteres: letras, números, ponto ou traço inferior"
    }
    ```
  - **Vazio / Ausente (400 Bad Request):**
    ```json
    {
      "username": "",
      "available": false,
      "reason": "empty",
      "message": "Digite um nome de usuário"
    }
    ```

#### B. Cadastro de Artista (`POST /api/v1/auth/register`)
- **Arquivo de Origem:** `src/models/auth/signup.model.ts` e `src/controllers/hooks/auth/useSignupController.ts` (linhas 8-13, 270-340).
- **Campos de Requisição:**
  ```json
  {
    "name": "Nati Arte",
    "username": "natiarte",
    "email": "nati@example.com",
    "password": "senhaSegura123",
    "agree_terms": true
  }
  ```
  *(Compatibilidade de Casing: O backend deve aceitar `agree_terms` ou `agreeTerms`, e derivar `name` a partir de `username` se omitido).*
- **Regras:** Senha mínima de 6 caracteres; e-mail válido regex `/^[^\s@]+@[^\s@]+\.[^\s@]+$/`; `agree_terms === true`.
- **Resposta de Sucesso (201 Created):**
  ```json
  {
    "status": "success",
    "message": "Conta criada com sucesso.",
    "token": "1|sanctum_token...",
    "artist": {
      "id": 1,
      "name": "Nati Arte",
      "username": "natiarte",
      "email": "nati@example.com",
      "bio": null,
      "avatar_url": null,
      "cover_url": null,
      "email_verified_at": null,
      "created_at": "2026-10-21T03:00:00Z",
      "updated_at": "2026-10-21T03:00:00Z"
    }
  }
  ```
- **Resposta de Erro de Validação (422 Unprocessable Entity):**
  ```json
  {
    "message": "Os dados fornecidos são inválidos.",
    "errors": {
      "username": ["Esse @ já está em uso"],
      "email": ["E-mail inválido"],
      "password": ["A senha deve ter pelo menos 6 caracteres"],
      "agree_terms": ["Você deve aceitar os Termos e a Política de Privacidade"]
    }
  }
  ```

#### C. Login (`POST /api/v1/auth/login`)
- **Arquivo de Origem:** `src/models/auth/login.model.ts` e `src/controllers/hooks/auth/useLoginController.ts` (linhas 12-15, 85-150).
- **Campos de Requisição:**
  ```json
  {
    "email": "nati@example.com",
    "password": "senhaSegura123"
  }
  ```
- **Proteção:** RateLimiter de máx. 5 tentativas por minuto por IP (retorna status HTTP 429 se exceder).
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Login realizado com sucesso.",
    "token": "2|sanctum_token...",
    "artist": {
      "id": 1,
      "name": "Nati Arte",
      "username": "natiarte",
      "email": "nati@example.com"
    }
  }
  ```
- **Resposta de Erro de Autenticação (401 Unauthorized ou 422):**
  ```json
  {
    "status": "error",
    "message": "Credenciais inválidas. Verifique seu e-mail e senha."
  }
  ```

#### D. Logout (`POST /api/v1/auth/logout`)
- **Autenticado (Sanctum / Sessão)**
- **Resposta (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Sessão encerrada com sucesso."
  }
  ```

#### E. Sessão / Perfil Logado (`GET /api/v1/auth/me`)
- **Autenticado**
- **Resposta (200 OK):**
  ```json
  {
    "status": "success",
    "data": {
      "id": 1,
      "name": "Nati Arte",
      "username": "natiarte",
      "email": "nati@example.com",
      "bio": "Cerâmica e pintura feitas à mão.",
      "avatar_url": "/storage/avatars/nati.webp",
      "cover_url": null,
      "background_color": "#F7F6F2",
      "background_id": "paper",
      "button_shape": "rounded",
      "avatar_shape": "circle",
      "font_family": "Space Grotesk",
      "font_color": "#000000",
      "border_style": "solid-3px",
      "icon_variant": "default",
      "email_verified_at": "2026-10-21T03:00:00Z"
    }
  }
  ```

#### F. Recuperação de Senha & Confirmação de E-mail
- **Esqueci Minha Senha (`POST /api/v1/auth/forgot-password`):**
  - Payload: `{ "email": "nati@example.com" }`
  - Resposta (200 OK): `{ "status": "success", "message": "Enviamos um link de recuperação para o seu e-mail." }`
- **Redefinição de Senha (`POST /api/v1/auth/reset-password`):**
  - Payload: `{ "token": "...", "email": "nati@example.com", "password": "novaSenha123", "password_confirmation": "novaSenha123" }`
- **Reenvio de Confirmação de E-mail (`POST /api/v1/auth/email/resend`):**
  - Arquivo: `src/models/auth/emailConfirmation.model.ts`
  - Cooldown rígido: 60 segundos (`COOLDOWN_INITIAL_SECONDS = 60`). Responde 429 se solicitado antes.
- **Confirmação de Titularidade (`POST /api/v1/auth/email/verify`):**
  - Valida token/assinatura e define `artists.email_verified_at = now()`.

---

### 1.3 Fluxos de Onboarding em 3 Etapas

#### Etapa 1: Links Iniciais e Bio (`PUT /api/v1/onboarding/links`)
- **Arquivos:** `src/models/onboarding/linkSelection.model.ts`, `src/content/onboarding/platforms.data.ts`, `src/services/profileDraft.service.ts`.
- **Limites e Regras:**
  - Máximo de 5 links na etapa inicial (`MAX_INITIAL_PLATFORMS = 5`).
  - Bio com máximo de 160 caracteres (`MAX_BIO_LENGTH = 160`).
- **Catálogo Oficial das 17 Redes Sociais Suportadas:**
  1. `website` — Nome: Website | Prefixo: `https://` | Placeholder: `seusite.com` | Tipo: `url`
  2. `youtube` — Nome: YouTube | Prefixo: `youtube.com/@` | Placeholder: `seu_canal` | Tipo: `username`
  3. `x` — Nome: X | Prefixo: `x.com/` | Placeholder: `seu_perfil` | Tipo: `username`
  4. `linkedin` — Nome: LinkedIn | Prefixo: `linkedin.com/in/` | Placeholder: `seu_perfil` | Tipo: `username`
  5. `soundcloud` — Nome: SoundCloud | Prefixo: `soundcloud.com/` | Placeholder: `seu_perfil` | Tipo: `username`
  6. `instagram` — Nome: Instagram | Prefixo: `instagram.com/` | Placeholder: `seu_perfil` | Tipo: `username`
  7. `spotify` — Nome: Spotify | Prefixo: `open.spotify.com/artist/` | Placeholder: `seu_artista` | Tipo: `username`
  8. `threads` — Nome: Threads | Prefixo: `threads.net/@` | Placeholder: `seu_perfil` | Tipo: `username`
  9. `pinterest` — Nome: Pinterest | Prefixo: `pinterest.com/` | Placeholder: `seu_perfil` | Tipo: `username`
  10. `discord` — Nome: Discord | Prefixo: `discord.gg/` | Placeholder: `seu_servidor` | Tipo: `username`
  11. `whatsapp` — Nome: WhatsApp | Prefixo: `wa.me/55 ` | Placeholder: `(11) 99999-9999` | Tipo: `phone` (com máscara BR)
  12. `tiktok` — Nome: TikTok | Prefixo: `tiktok.com/@` | Placeholder: `seu_perfil` | Tipo: `username`
  13. `facebook` — Nome: Facebook | Prefixo: `facebook.com/` | Placeholder: `sua_pagina` | Tipo: `username`
  14. `behance` — Nome: Behance | Prefixo: `behance.net/` | Placeholder: `seu_portfolio` | Tipo: `username`
  15. `telegram` — Nome: Telegram | Prefixo: `t.me/` | Placeholder: `seu_usuario` | Tipo: `username`
  16. `twitch` — Nome: Twitch | Prefixo: `twitch.tv/` | Placeholder: `seu_canal` | Tipo: `username`
  17. `email` — Nome: E-mail | Prefixo: `mailto:` | Placeholder: `seu@email.com` | Tipo: `email`
- **Payload Esperado:**
  ```json
  {
    "bio": "Cerâmica e pintura feitas à mão, com afeto.",
    "links": [
      { "platform": "instagram", "value": "natiarte" },
      { "platform": "whatsapp", "value": "(11) 99999-9999" },
      { "platform": "website", "value": "natiarte.com" }
    ]
  }
  ```
  *(Suporte alternativo aceito do rascunho frontend: `{ "bio": "...", "selectedPlatforms": [...], "linkValues": { ... } }`).*
- **Ação no Backend:** Atualiza `artists.bio` e sincroniza botões iniciais na tabela `buttons` com `order_index: 0..N`.

#### Etapa 2: Personalização do Fundo (`PUT /api/v1/onboarding/background`)
- **Arquivos:** `src/models/onboarding/backgroundCustomization.model.ts`, `src/content/onboarding/backgroundCustomization.content.ts`.
- **A Paleta Oficial de 17 Cores:**
  1. `paper`: `#F7F6F2` (Papel - Neutro claro)
  2. `linen`: `#EFECE6` (Linho - Areia suave)
  3. `butter`: `#F3E7B8` (Manteiga)
  4. `peach`: `#F6D5BC` (Pêssego)
  5. `clay`: `#E9C5B5` (Argila)
  6. `rose`: `#E8D5D7` (Rosa)
  7. `lavender`: `#DAD3EC` (Lavanda)
  8. `snow`: `#D5E0EA` (Névoa)
  9. `sky`: `#A8CCE4` (Céu)
  10. `sage`: `#C9D9D0` (Sálvia)
  11. `celadon`: `#ACE1AF` (Celadon Mint)
  12. `olive`: `#A9AE86` (Oliva)
  13. `terracotta`: `#E15B3A` (Terracotta)
  14. `wine`: `#6B2D3C` (Vinho)
  15. `forest`: `#2F4A3C` (Floresta)
  16. `navy`: `#1F2B47` (Marinho)
  17. `noir`: `#121316` (Noturno profundo)
  - `custom`: Hexadecimal arbitrário via seletor (padrão `#E85c00`).
- **Padrões Neo-brutalistas:** `dots-grid`, `grid`, `lines`, `noise`, `none`.
- **Payload Esperado:**
  ```json
  {
    "background_color": "#F7F6F2",
    "background_id": "paper"
  }
  ```
  *(Aceitar camelCase `backgroundColor` e `backgroundId`).*

#### Etapa 3: Formato dos Botões e Publicação (`PUT /api/v1/onboarding/buttons`)
- **Arquivos:** `src/models/onboarding/buttonCustomization.model.ts`, `src/content/onboarding/buttonCustomization.content.ts`.
- **Formatos Suportados:**
  - `square` ("Quadrado" - cantos retos neo-brutalistas)
  - `rounded` ("Arredondado" - raio padrão de 16px)
  - `pill` ("Mais arredondado" / Pílula - totalmente arredondado)
- **Cores de Botões:** `#FFFFFF` (padrão), paleta de 17 cores ou `custom`.
- **Payload Esperado:**
  ```json
  {
    "button_shape": "rounded",
    "button_color": "#FFFFFF"
  }
  ```
- **Resposta (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Onboarding concluído com sucesso.",
    "redirect_url": "/editor"
  }
  ```

---

### 1.4 Gestão de Mídia e Armazenamento (Uploads)

#### A. Upload de Avatar (`POST /api/v1/profile/avatar`)
- **Arquivos:** `src/models/onboarding/imageCrop.model.ts`, `src/components/mockups/ProfileMockup.tsx`.
- **Formato:** `multipart/form-data`
- **Nome do Campo:** `avatar`
- **Formatos permitidos:** `image/jpeg`, `image/png`, `image/webp`
- **Tamanho Máximo:** 5 MB (`5120 KB` / `5242880 bytes`)
- **Proporção de Corte:** 1:1 (circular)
- **Exclusão de Órfãos:** O backend deve deletar o arquivo de avatar anterior no storage antes de persistir o novo.
- **Resposta (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Avatar atualizado com sucesso.",
    "avatar_url": "/storage/avatars/random_hash.webp"
  }
  ```
- **Remoção de Avatar (`DELETE /api/v1/profile/avatar`):**
  - Remove o arquivo do storage e atualiza `artists.avatar_url = null`.

#### B. Upload de Capa/Banner (`POST /api/v1/profile/cover`)
- **Arquivos:** `src/models/onboarding/coverColor.model.ts`, `src/components/mockups/ProfileMockup.tsx`.
- **Formato:** `multipart/form-data`
- **Nome do Campo:** `cover`
- **Formatos permitidos:** `image/jpeg`, `image/png`, `image/webp`
- **Tamanho Máximo:** 10 MB (`10240 KB`)
- **Proporção de Corte:** 16:9 retangular
- **Resposta (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Capa atualizada com sucesso.",
    "cover_url": "/storage/covers/random_hash.webp"
  }
  ```
- **Remoção de Capa (`DELETE /api/v1/profile/cover`):**
  - Remove o arquivo do storage e define `artists.cover_url = null` (o frontend faz fallback para a cor sólida de capa `#2A4252` ou fundo).

#### C. Upload de Imagem de Card Featured (`POST /api/v1/buttons/{id}/image`)
- **Nome do Campo:** `image`
- **Tamanho Máximo:** 5 MB
- **Destino no Storage:** `storage/app/public/cards/`
- **Resposta (200 OK):**
  ```json
  {
    "status": "success",
    "message": "Imagem do card atualizada com sucesso.",
    "image_url": "/storage/cards/random_hash.webp"
  }
  ```

---

### 1.5 Links & Buttons Editor Core

#### CRUD de Botões
- **Listar Botões (`GET /api/v1/buttons`):**
  - Retorna todos os botões do artista logado ordenados por `order_index ASC`.
  - Resposta (200 OK):
    ```json
    {
      "status": "success",
      "data": [
        {
          "id": 1,
          "artist_id": 1,
          "title": "Portfólio 2026",
          "url": "https://meuportfolio.art",
          "platform": "website",
          "type": "standard",
          "button_color": null,
          "text_color": null,
          "font_family": null,
          "image_url": null,
          "order_index": 0,
          "clicks_count": 120,
          "is_active": true,
          "created_at": "...",
          "updated_at": "..."
        }
      ]
    }
    ```
- **Criar Botão (`POST /api/v1/buttons`):**
  - Payload:
    ```json
    {
      "title": "Novo Link",
      "url": "https://instagram.com/artista",
      "platform": "instagram",
      "type": "standard",
      "button_color": null,
      "text_color": null,
      "font_family": null,
      "image_url": null
    }
    ```
  - Atribui automaticamente `order_index = max(order_index) + 1` e `is_active = true`.
- **Atualizar Botão (`PUT /api/v1/buttons/{id}`):**
  - Payload:
    ```json
    {
      "title": "Portfólio Atualizado",
      "url": "https://novosite.art",
      "platform": "custom",
      "type": "standard",
      "button_color": "#ACE1AF",
      "text_color": "#121316",
      "font_family": "Syne",
      "is_active": true
    }
    ```
- **Excluir Botão (`DELETE /api/v1/buttons/{id}`):**
  - Garante validação de posse (`artist_id == user->id`). Retorna 403/404 se não for o dono.

#### Reordenação Drag & Drop (`POST /api/v1/buttons/reorder`)
- **Arquivo de Origem:** `backend.md` (linhas 347-379), `useEditorController.ts` (linhas 150-173).
- **Payload Rigoroso Esperado:**
  ```json
  {
    "ordered_ids": [14, 10, 12, 11]
  }
  ```
- **Execução:** O backend deve rodar em transação (`DB::transaction`), atribuindo o índice do array como o novo `order_index`:
  ```php
  foreach ($validated['ordered_ids'] as $index => $id) {
      $artist->buttons()->where('id', $id)->update(['order_index' => $index]);
  }
  ```

---

### 1.6 Consumo da Página Pública e Rastreador de Cliques

#### A. Página Pública (`GET /{username}`)
- **Arquivos de Origem:** `src/models/profile/publicProfile.model.ts`, `src/controllers/hooks/profile/usePublicProfileController.ts`, `src/views/profile/PublicProfileView.tsx`.
- **Consulta:** 1 query única com Route Model Binding no backend:
  `Artist::where('username', $cleanUsername)->with(['activeButtons'])->firstOrFail()`
- **Os 3 Estados de Visualização no Frontend:**
  1. **`200 found` (Artista existe e tem botões ativos):**
     - O frontend renderiza o header (Avatar, Nome, `@`, Bio), a lista de botões com styles aplicados e o footer com link de CTA.
  2. **`200 no_links` (Artista existe, mas `buttons.length === 0`):**
     - O frontend renderiza o header e um card vazio com badge `"Em preparação"`, título `"Nenhum link adicionado ainda"` e descrição `"Este artista ainda não publicou links em sua vitrine."`.
  3. **`404 not_found` (Username inexistente ou não registrado):**
     - O frontend renderiza a tela de `@ livre` com badge `"Disponível"`, título `@{username} está livre!`, descrição explicativa e botão CTA de `"Reivindicar @{username}"` apontando para `/cadastro?username={username}`.
- **Estrutura de Dados do Perfil Público Retornado:**
  ```json
  {
    "status": "success",
    "data": {
      "id": 1,
      "name": "Nati Arte",
      "username": "natiarte",
      "bio": "Cerâmica e pintura feitas à mão, com afeto. Crio peças únicas para colorir o cotidiano.",
      "avatar_url": "/storage/avatars/nati.webp",
      "cover_url": null,
      "theme": {
        "background_color": "#F7F6F2",
        "background_id": "paper",
        "button_shape": "rounded",
        "avatar_shape": "circle",
        "font_family": "Space Grotesk",
        "font_color": "#000000",
        "border_style": "solid-3px",
        "icon_variant": "default"
      },
      "buttons": [
        {
          "id": 1,
          "title": "Instagram",
          "redirect_url": "http://localhost:8000/r/1",
          "target_url": "https://instagram.com/natiarte",
          "platform": "instagram",
          "type": "standard",
          "image_url": null,
          "styles": {
            "button_color": "#ACE1AF",
            "text_color": "#121316",
            "font_family": "Space Grotesk"
          }
        }
      ]
    }
  }
  ```

#### B. Rastreador de Cliques & Redirecionamento Atômico (`GET /r/{id}`)
- **Arquivo de Origem:** `backend.md` (linhas 313-338) e `RedirectController.php`.
- **Funcionamento:**
  1. O visitante clica no botão público cujo link aponta para `https://assin.art/r/{id}`.
  2. O backend localiza o botão ativo (`is_active = true`).
  3. Incrementa atomicamente no banco sem locks pesados: `$button->increment('clicks_count')`.
  4. Retorna imediatamente um redirecionamento HTTP **302 Found** para `buttons.url`.

---

## 2. Logic Chain (Cadeia de Raciocínio Lógico)

1. **Evidência:** O frontend foi construído em arquitetura estrita MVC desacoplada (models puros sem React, views sem strings nem Tailwind no JSX, hooks controladores).
   **Inferência:** Todas as validações visuais feitas pelos hooks controladores (`useSignupController`, `useLoginController`, `useLandingController`) possuem mensagens exatas em arquivos de conteúdo (`.content.ts`). Se o backend retornar mensagens ou códigos em formato diferente, a UI do usuário exibirá inconsistências ou falhará nos testes automatizados.
2. **Evidência:** O estado do rascunho de onboarding em `profileDraft.service.ts` armazena propriedades com nomes mistos como `customBackgroundColor`, `buttonShape`, `avatarUrl`, enquanto o banco Laravel utiliza `background_color`, `button_shape`, `avatar_url`.
   **Inferência:** Os Requests do backend (`OnboardingBackgroundRequest`, `OnboardingButtonsRequest`, `ProfileController`) devem ser inteligentes: validar as chaves em snake_case mas tolerar ou normalizar requisições enviadas em camelCase.
3. **Evidência:** `PublicProfileView.tsx` renderiza links esperando atributos de URL de destino e de redirect (`redirect_url` apontando para `/r/{id}`).
   **Inferência:** `PublicProfileController` deve obrigatoriamente expor o campo `redirect_url` gerado dinamicamente via `url('/r/' . $button->id)` para que a computação de cliques e métricas ocorra sem quebrar os links diretos para plataformas.
4. **Evidência:** Em `usePublicProfileController.ts`, existem 3 estados estritos de perfil (`found`, `no_links`, `not_found`).
   **Inferência:** O endpoint `GET /{username}` deve retornar status HTTP 200 com array de botões vazios quando o artista existe mas não tem links (permitindo ao frontend exibir a tela "Em preparação"), e retornar status HTTP 404 apenas quando o usuário realmente não existe no banco (permitindo a exibição da tela "Reivindicar @").
5. **Evidência:** `useEditorController.ts` possui reordenação que inverte posições e salva arrays ordenados.
   **Inferência:** O payload de reordenação `{ "ordered_ids": [...] }` já implementado no backend atende 100% à expectativa do frontend e garante atomicidade transacional.

---

## 3. Features Discovered

| # | Categoria | Feature | Descrição | Inputs | Outputs | Error Behavior | Descoberto Via |
|---|---|---|---|---|---|---|---|
| 1 | Auth | Verificação de Username | Checa disponibilidade em tempo real | `GET /api/v1/usernames/check?username=...` | `{ username, available, reason, message }` | 400 se vazio, 200 com available=false se em uso/reservado/inválido | `useLandingController.ts`, `useSignupController.ts` |
| 2 | Auth | Cadastro de Artista | Cria conta e inicia sessão | `POST /api/v1/auth/register` (`username`, `email`, `password`, `agree_terms`) | `{ status, token, artist }` (201) | 422 com mensagens padronizadas de validação | `signup.model.ts`, `useSignupController.ts` |
| 3 | Auth | Login de Artista | Autentica via e-mail e senha com rate limit | `POST /api/v1/auth/login` (`email`, `password`) | `{ status, token, artist }` (200) | 401 para credenciais incorretas, 429 para limite excedido | `login.model.ts`, `useLoginController.ts` |
| 4 | Auth | Logout | Revoga token/sessão | `POST /api/v1/auth/logout` | `{ status, message }` (200) | 401 se não autenticado | `AccountMenu.tsx`, `backend.md` |
| 5 | Auth | Hidratação de Sessão | Obtém dados do usuário logado | `GET /api/v1/auth/me` | `{ status, data: Artist }` (200) | 401 se token expirado | `tasks_backend.md`, `backend.md` |
| 6 | Auth | Esqueci Senha | Envia e-mail de recuperação | `POST /api/v1/auth/forgot-password` (`email`) | `{ status, message }` (200) | 422 para e-mail inexistente/inválido | `forgotPassword.model.ts` |
| 7 | Auth | Redefinir Senha | Atualiza senha com token | `POST /api/v1/auth/reset-password` (`token`, `email`, `password`, `password_confirmation`) | `{ status, message }` (200) | 422 para token inválido/expirado | `tasks_backend.md` |
| 8 | Auth | Reenvio de Confirmação | Reenvia e-mail com throttling de 60s | `POST /api/v1/auth/email/resend` | `{ status, message }` (200) | 429 se solicitado antes de 60s | `emailConfirmation.model.ts` |
| 9 | Onboarding | Etapa 1: Links e Bio | Salva bio (≤160) e até 5 redes das 17 suportadas | `PUT /api/v1/onboarding/links` (`bio`, `links[]`) | `{ status, message, data }` (200) | 422 se bio > 160 ou > 5 redes | `linkSelection.model.ts`, `platforms.data.ts` |
| 10 | Onboarding | Etapa 2: Fundo | Salva cor da paleta de 17 cores ou hex e padrão | `PUT /api/v1/onboarding/background` (`background_color`, `background_id`) | `{ status, message }` (200) | 422 se hex inválido | `backgroundCustomization.model.ts` |
| 11 | Onboarding | Etapa 3: Botões & Conclusão | Define formato (`square`, `rounded`, `pill`) e conclui | `PUT /api/v1/onboarding/buttons` (`button_shape`, `button_color`) | `{ status, message, redirect_url: "/editor" }` (200) | 422 se formato inválido | `buttonCustomization.model.ts` |
| 12 | Mídia | Upload de Avatar | Upload de foto (JPG/PNG/WebP ≤5MB) com exclusão do antigo | `POST /api/v1/profile/avatar` (`avatar` file) | `{ status, message, avatar_url }` (200) | 422 se formato não suportado ou > 5MB | `imageCrop.model.ts`, `ProfileMockup.tsx` |
| 13 | Mídia | Remoção de Avatar | Deleta foto e define avatar_url como null | `DELETE /api/v1/profile/avatar` | `{ status, message }` (200) | 401 se não autenticado | `tasks_backend.md` |
| 14 | Mídia | Upload de Capa | Upload de banner 16:9 (≤10MB) | `POST /api/v1/profile/cover` (`cover` file) | `{ status, message, cover_url }` (200) | 422 se formato não suportado ou > 10MB | `ProfileMockup.tsx`, `coverColor.model.ts` |
| 15 | Mídia | Remoção de Capa | Deleta banner e volta à cor sólida | `DELETE /api/v1/profile/cover` | `{ status, message }` (200) | 401 se não autenticado | `tasks_backend.md` |
| 16 | Mídia | Thumbnail de Card | Upload de capa para card_featured (≤5MB) | `POST /api/v1/buttons/{id}/image` (`image` file) | `{ status, message, image_url }` (200) | 422 se formato inválido ou ID inexistente | `ORIGINAL_REQUEST.md`, `Button.php` |
| 17 | Editor | Listar Botões | Retorna botões ordenados por order_index ASC | `GET /api/v1/buttons` | `{ status, data: Button[] }` (200) | 401 se não autenticado | `ButtonController.php`, `useEditorController.ts` |
| 18 | Editor | Criar Botão | Cria novo link atribuindo order_index sequencial | `POST /api/v1/buttons` (`title`, `url`, `platform`, `type`, etc.) | `{ status, message, data: Button }` (201) | 422 se URL inválida | `ButtonController.php`, `editor.model.ts` |
| 19 | Editor | Atualizar Botão | Edita dados, visibilidade ou estilos do botão | `PUT /api/v1/buttons/{id}` (`title`, `url`, `is_active`, etc.) | `{ status, message, data: Button }` (200) | 404 se não encontrado ou não pertencer ao artista | `ButtonController.php` |
| 20 | Editor | Excluir Botão | Remove botão do artista | `DELETE /api/v1/buttons/{id}` | `{ status, message }` (200) | 404 se não for o dono | `ButtonController.php`, `ConfirmDeleteModal.tsx` |
| 21 | Editor | Reordenação Drag & Drop | Persiste nova ordem atômica | `POST /api/v1/buttons/reorder` (`{ ordered_ids: [...] }`) | `{ status, message }` (200) | 422 se array contiver IDs de terceiros | `ButtonController.php`, `useEditorController.ts` |
| 22 | Público | Página do Artista | Retorna perfil, estilos cascade e botões ativos | `GET /{username}` | `{ status, data: { ...theme, buttons } }` (200) | 200 no_links se sem botões; 404 not_found se inexistente | `PublicProfileView.tsx`, `PublicProfileController.php` |
| 23 | Público | Rastreador de Cliques | Incrementa clicks_count e redireciona (302) | `GET /r/{id}` | HTTP 302 Redirect para `buttons.url` | 404 se botão inativo ou inexistente | `RedirectController.php`, `backend.md` |

---

## 4. Edge Cases Mined

| # | Feature | Input / Condição | Comportamento Observado / Requisito Estrito |
|---|---|---|---|
| 1 | Username Check | `username="@Gabriel Pinho "` | Sanitiza para `gabrielpinho` (remove `@`, espaços e lowercases) antes de checar. |
| 2 | Username Check | `username="ab"` (2 chars) | O backend permite 2 caracteres regex (`^[a-z0-9_.-]{2,30}$`), mas o frontend adota cópia de 3 a 30 caracteres. Para máxima compatibilidade, o backend aceita 2-30. |
| 3 | Username Check | `username="admin"` ou rota de sistema | Responde HTTP 200 com `available: false` e `reason: "reserved"`. Não lança erro 500 nem bloqueia por middleware. |
| 4 | Cadastro | E-mail já existente | Responde HTTP 422 com `errors.email: ["E-mail inválido ou já cadastrado."]`. |
| 5 | Login | Mais de 5 tentativas erradas em 60s | Responde HTTP 429 Too Many Requests com header `Retry-After`. |
| 6 | Onboarding Links | WhatsApp com DDI `5511999999999` ou `(11) 99999-9999` | Função `formatBrazilianPhone` e sanitizador extraem dígitos e formatam para `wa.me/55...`. |
| 7 | Onboarding Links | Cole de URL completa no campo username (ex: `https://instagram.com/usuario?utm_source=ig`) | Função `sanitizePlatformValue` faz split e remove protocolo, domínio, query params e hash, guardando somente `usuario`. |
| 8 | Onboarding Links | Bio com mais de 160 caracteres | Backend deve rejeitar com 422 (`max:160`). Frontend corta no input com `maxLength={160}`. |
| 9 | Upload Avatar | Arquivo de 6 MB ou formato `.gif` | Rejeição imediata com HTTP 422 (`max:5120`, `mimes:jpeg,png,jpg,webp`). |
| 10 | Upload Avatar | Substituição de foto existente | Exclusão física do arquivo anterior no storage antes do novo save para evitar acúmulo de arquivos órfãos. |
| 11 | Reordenação | Array `ordered_ids` com ID pertencente a outro artista | A transação não atualiza botões alheios (filtragem rigorosa `$artist->buttons()->where('id', $id)`). |
| 12 | Página Pública | Artista cadastrado sem nenhum botão ativo | Retorna HTTP 200 com array `buttons: []` e flag/status `no_links`, acionando a visualização "Em preparação". |
| 13 | Página Pública | Username inexistente (`/artista_fantasma`) | Retorna HTTP 404, acionando no frontend a view "Reivindicar @artista_fantasma". |
| 14 | Rastreador `/r/{id}` | Botão desativado (`is_active = false`) | Retorna HTTP 404. O clique não é contabilizado e o visitante não é redirecionado. |
| 15 | Herança Visual | Botão sem cor individual (`button_color = null`) | Accessor no Model Button resolve para a cor do tema do artista (`artists.font_color` / `#FFE600` / `#FFFFFF`). |

---

## 5. Caveats (Ressalvas e Suposições)

1. **Campos snake_case vs camelCase:**  
   O frontend possui contratos TypeScript que usam `agreeTerms`, `backgroundColor`, `buttonShape`, enquanto o padrão REST Laravel adota `agree_terms`, `background_color`, `button_shape`.  
   *Mitigação adotada:* Os Requests do Laravel devem aceitar ambos os formatos (com fallback no Request ou FormRequest) para garantir 100% de tolerância.
2. **Armazenamento de Imagens:**  
   Em ambiente local de desenvolvimento, os uploads são salvos em `storage/app/public/` e servidos via `/storage/...` após `php artisan storage:link`. Em produção, pode ser alternado transparentemente para Amazon S3 ou DigitalOcean Spaces configurando o driver do `.env` sem alteração de código.
3. **Autenticação Dupla (Sanctum Tokens vs Cookies de Sessão):**  
   O frontend Vite pode rodar em porta diferente (`http://localhost:5173`) enquanto a API roda em `http://localhost:8000`. Foi assegurado suporte tanto para tokens Sanctum (`Authorization: Bearer ...`) quanto para sessão SPA com `stateful domains` e CORS configurado.

---

## 6. Conclusion (Avaliação Final e Recomendações)

O frontend `assinart_v1` possui uma arquitetura altamente madura, tipada e consistente. Todos os requisitos, constantes, limites de caracteres, formatos de arquivos e cópias estão rigorosamente mapeados.

Para o backend atingir 100% de aderência sem nenhuma quebra de contrato:
1. Manter a estrutura das **2 tabelas integradas** (`artists` e `buttons`).
2. Implementar os controllers `AuthController`, `OnboardingController`, `ProfileController`, `ButtonController`, `PublicProfileController` e `RedirectController` respeitando rigorosamente os payloads e status HTTP detalhados nas tabelas acima.
3. Garantir a suíte de testes cobrindo todos os cenários e edge cases catalogados.

---

## 7. Verification Method (Método de Verificação Independente)

Para auditar e verificar independentemente a exatidão deste relatório contra as bases de código:

1. **Frontend Tests:**
   Executar no diretório `c:\Users\pinho\Desktop\Assin.art\assinart_v1`:
   ```bash
   npm test
   ```
   *Resultado esperado:* Todos os testes de validação de formulários, reordenação e página pública passam com sucesso.

2. **Frontend Type Check e Build:**
   ```bash
   npm run build
   ```
   *Resultado esperado:* Zero erros de TypeScript (`tsc -b`) e bundle Vite gerado com sucesso.

3. **Backend Tests:**
   Executar no diretório `c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend`:
   ```bash
   php artisan test
   ```
   *Resultado esperado:* Todos os testes unitários e de feature de checagem de username, autenticação, onboarding, botões e página pública executam com sucesso.
