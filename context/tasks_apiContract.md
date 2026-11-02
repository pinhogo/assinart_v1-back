# Plano de Alinhamento de Contratos de API e Banco — Backend (`context/tasks_apiContract.md`)

> **Repositório:** `assinart_v1_backend`  
> **Status:** Em Andamento  
> **Branch:** `FLASH-AND-QWEN-BEST-FRIENDS`  
> **Padrão de Redação:** ASD-STE100  
> **Alinhamento:** `context/rules.md`, `context/api_contracts.md`, `context/inventory.md`, `context/data_dictionary.md`

---

## Checklist de Tarefas

- [ ] **Task B1: Criar Migrations de Tipografia/Wallpaper e Tabelas Analíticas**
  - [ ] Criar migration `2026_11_02_000001_add_typography_and_style_columns.php` adicionando campos tipográficos e de wallpaper em `artists` e `buttons`.
  - [ ] Criar migration `2026_11_02_000002_create_page_views_and_button_clicks_tables.php` criando `page_views` e `button_clicks` com índices.

- [ ] **Task B2: Atualizar Models e Mídias Polimórficas**
  - [ ] Criar model `app/Models/PageView.php` com relacionamento com `Artist`.
  - [ ] Criar model `app/Models/ButtonClick.php` com relacionamento com `Button`.
  - [ ] Atualizar `app/Models/Artist.php` com `$fillable`, relacionamentos `pageViews` e accessors polimórficos `$appends = ['avatar', 'cover', 'background']`.
  - [ ] Atualizar `app/Models/Button.php` com `$fillable`, relacionamento `buttonClicks`, casts inteiros e accessors de estilo.

- [ ] **Task B3: Exclusão Definitiva de Conta**
  - [ ] Implementar método `deleteAccount` em `app/Http/Controllers/Api/AuthController.php` com validação de senha e expurgo de arquivos.
  - [ ] Registrar rota `POST /api/v1/auth/account/delete` em `routes/api_auth.php`.

- [ ] **Task B4: Perfil, Verbo PATCH e Upload Simétrico de Plano de Fundo**
  - [ ] Registrar `Route::patch('/profile', ...)` em `routes/api_profile.php`.
  - [ ] Implementar rotas simétricas `POST /api/v1/profile/background` e `DELETE /api/v1/profile/background`.
  - [ ] Atualizar `ProfileController.php` com suporte a `uploadBackground`, `deleteBackground`, tipografia e mídias polimórficas no `update`.

- [ ] **Task B5: Gestão de Botões, Limpeza em Massa e Tipografia Individual**
  - [ ] Implementar `destroyAll` em `app/Http/Controllers/Api/ButtonController.php` para rota `DELETE /api/v1/buttons`.
  - [ ] Suportar tipos de botões (`featured`, `standard`, `icon_pill`, `card_featured`) e campos tipográficos individuais.

- [ ] **Task B6: Nó Theme no Perfil Público**
  - [ ] Atualizar `app/Http/Controllers/PublicProfileController.php` para incluir campos tipográficos e de estilo no nó `theme`.

- [ ] **Task B7: Rastreio de Cliques no Redirecionador**
  - [ ] Atualizar `app/Http/Controllers/RedirectController.php` para registrar cliques na tabela `button_clicks`.

- [ ] **Task B8: Telemetria e Sumário Analítico de Métricas**
  - [ ] Criar `app/Http/Controllers/Api/MetricsController.php` com `track` e `summary`.
  - [ ] Registrar `POST /api/v1/track` e `GET /api/v1/metrics/summary` em `routes/api.php`.

- [ ] **Task B9: Testes Automatizados e Validação Consolidada**
  - [ ] Criar testes Feature: `tests/Feature/AccountDeletionTest.php`, `tests/Feature/MetricsTest.php`, `tests/Feature/BackgroundMediaTest.php`.
  - [ ] Executar suíte completa via PHP 8.4 e garantir 100% dos testes aprovados.
