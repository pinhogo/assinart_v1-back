# Original User Request

## 2026-10-21T02:54:49Z

The user requested: "preciso de um agente pra documentaçao e wiki, documentar as rotas bem certinho de acordo com o front, dicionario, todos parametros, pode ser uma pasta chamada documentacao com um index em html".

Construir e validar o backend completo em Laravel 11 para a Assin.art v1 (plataforma de link na bio para artistas), cobrindo da Fase 1 à Fase 6: autenticação, onboarding em 3 etapas, upload de mídia, CRUD e reordenação Drag & Drop de botões, página pública com herança de estilos e redirecionador atômico de cliques, e gerar uma wiki/documentação interativa completa em HTML na pasta documentacao/.

Working directory: c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend
Integrity mode: demo

## Requirements

### R1. Autenticação, Conta e Disponibilidade de Username (Auth Core)
- Implementar verificação de `@username` em tempo real (`GET /api/v1/usernames/check`), sanitizando espaços e `@` inicial, convertendo para minúsculas, validando regex `^[a-z0-9_.-]{2,30}$` e bloqueando rotas do sistema (`admin`, `api`, `login`, `editor`, `r`, etc.).
- Implementar cadastro de artista (`POST /api/v1/auth/register`), login com proteção contra força bruta (`POST /api/v1/auth/login`), logout (`POST /api/v1/auth/logout`) e hidratação de sessão (`GET /api/v1/auth/me`).
- Implementar fluxo de recuperação de senha (`/forgot-password`, `/reset-password`) e confirmação de e-mail com reenvio temporizado (`/email/resend`, `/email/verify`).

### R2. Onboarding em 3 Etapas e Publicação do Perfil
- **Etapa 1 (`PUT /api/v1/onboarding/links`):** Salvar bio (máx. 160 caracteres) e sincronizar até 5 links iniciais dentre as 17 redes sociais suportadas.
- **Etapa 2 (`PUT /api/v1/onboarding/background`):** Salvar cor de fundo (`#HEX` da paleta de 17 cores ou personalizada) e ID de padrão/textura neo-brutalista.
- **Etapa 3 (`PUT /api/v1/onboarding/buttons`):** Salvar formato global dos botões (`square`, `rounded`, `pill`), concluir onboarding e publicar o perfil.

### R3. Gestão de Mídia e Armazenamento (Uploads)
- **Avatar (`POST /api/v1/profile/avatar`):** Upload e substituição de foto (JPG, PNG, WebP até 5MB), com exclusão do arquivo antigo do storage para prevenir arquivos órfãos.
- **Capa/Banner (`POST /api/v1/profile/cover`):** Upload de imagem panorâmica de até 10MB para `artists.cover_url` e endpoint `DELETE` para remoção.
- **Thumbnail de Card (`POST /api/v1/buttons/{id}/image`):** Upload de imagem para botões com layout de card em destaque (`card_featured`).

### R4. Gestão Avançada de Links e Reordenação Drag & Drop (Editor Core)
- CRUD completo de botões/redes sociais (`GET`, `POST`, `PUT`, `DELETE` em `/api/v1/buttons`).
- Suporte aos 3 formatos de botão: `standard` (retangular), `icon_pill` (circular de rede social abaixo da bio) e `card_featured` (card com capa).
- Suporte a estilos individuais com herança inteligente (se nulo, herda fonte e cor da página).
- Reordenação atômica em transação (`POST /api/v1/buttons/reorder`) recebendo `{ "ordered_ids": [...] }` e atualizando `order_index`.

### R5. Página Pública e Rastreador de Cliques
- **Página Pública (`GET /{username}`):** Consulta otimizada com Route Model Binding e eager loading dos botões ativos em 1 query única, retornando dados e estilos com resolução de fallback cascade. Tratamento de status: `found` (200), `no_links` (200) e `not_found` (404).
- **Rastreador de Cliques (`GET /r/{id}`):** Incremento leve e atômico de `clicks_count` e redirecionamento HTTP 302 direto para a URL final do botão, com rate limiting leve contra bots.

### R6. Configuração de Segurança, CORS e Testes Automatizados
- Configurar CORS para permitir requisições do frontend Vite local (`http://localhost:5173`) e do domínio oficial (`https://assin.art`).
- Criar suíte completa de testes automatizados (PHPUnit / Feature Tests) cobrindo todos os fluxos críticos.

### R7. Documentação Completa, Dicionário e Wiki Interativa em HTML
- Criar pasta dedicada `documentacao/` na raiz do projeto backend com um arquivo `index.html` autônomo, visualmente agradável e navegável.
- Documentar todas as rotas (públicas e autenticadas), métodos HTTP, cabeçalhos, query params, corpos de requisição e respostas de sucesso/erro.
- Integrar o Dicionário de Dados completo das 2 tabelas (`artists` e `buttons`) com tipos, restrições e exemplos reais.
- Mapear a correspondência exata de cada endpoint com os formulários e views do frontend (`assinart_v1`).

## Verification Resources
- Banco de dados local configurado em SQLite: `database/database.sqlite`.
- Dicionário de dados consolidado: `docs/DATA_DICTIONARY.md` e `context/data_dictionary.md`.
- Checklist detalhado de tarefas: `context/tasks_backend.md`.

## Acceptance Criteria

### Testes Automatizados e Cobertura
- [ ] 100% dos testes da suíte passam com sucesso via `php artisan test` (ou `vendor/bin/phpunit`).
- [ ] Testes Feature cobrindo:
  - Verificação e reserva de username (válido, duplicado, reservado, formato inválido).
  - Fluxo de autenticação (registro, login, logout, me, rotas protegidas sem token).
  - Onboarding em 3 etapas e publicação de perfil.
  - CRUD e Drag & Drop de links com validação de posse.
  - Carregamento da página pública e resolução de herança visual.
  - Rota de redirecionamento `/r/{id}` incrementando `clicks_count` e retornando 302.

### Conformidade e Segurança
- [ ] Zero erros de linter ou vulnerabilidades críticas.
- [ ] Uploads armazenados em `storage/app/public/` com links simbólicos funcionais (`php artisan storage:link`).
- [ ] CORS liberado sem bloqueios no navegador para `http://localhost:5173`.

### Wiki e Documentação HTML
- [ ] Arquivo `documentacao/index.html` funcional, responsivo, abrível diretamente no navegador, documentando todos os parâmetros, payloads, status HTTP e dicionário de dados da API.

## Follow-up — 2026-10-21T03:47:57Z

na pasta do frontend context/tasks_integration_backend_frontend.md nesse arquivo tem umas tasks e tarefas pra executar



## 2026-10-21T03:04:01Z

O usuário solicitou aceleração máxima com mais subagentes em paralelo para dividir as tarefas das Fases 1 a 6. Por favor, garanta que o orquestrador aloque múltiplos implementadores simultâneos para trabalhar em paralelo nos marcos (M1 Auth, M2 Onboarding, M3 Mídia, M4 Links & Drag-and-Drop, M5 Página Pública e M6 Testes).

## 2026-10-21T18:50:00Z

acabou ontem os creditos
