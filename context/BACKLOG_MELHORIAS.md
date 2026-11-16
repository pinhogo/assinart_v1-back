# 📋 Backlog de Melhorias & Pré-Tasks (`context/BACKLOG_MELHORIAS.md`)

> **Pilar 1 do Ciclo de Desenvolvimento:** Backlog, Análise, Organização e Investigação (Recebe Contexto Bruto).  
> **Sincronização Obrigatória (Regra 14):** Paridade bidirecional com o **GitHub Project nº 2** (`https://github.com/users/pinhogo/projects/2`, Project ID `PVT_kwHOCM7jCs4BqpNv`) e [`coordination/BOARD.md`](../coordination/BOARD.md).

---

> [!CAUTION]
> ## 🤖 WORKFLOW INTEGRADO DA IA — COMO PROCESSAR ESTE DOCUMENTO
>
> Você atua como **Tech Lead & Product Manager Técnico**. Execute estritamente este fluxo sempre que o usuário adicionar **Contexto Bruto** neste arquivo, criar *drafts* no GitHub Project nº 2 ou solicitar atualização do backlog:
>
> ### Passo 1: Conferência Bidirecional (`BACKLOG_MELHORIAS.md` ↔ GitHub Project nº 2)
> 1. Liste os cards do GitHub Project nº 2 via CLI (`gh project item-list 2 --owner pinhogo --format json`).
> 2. **Se o Contexto Bruto existir como *draft* na coluna `BACKLOG - FLUXO A` do GitHub Project nº 2, mas não estiver neste `.md`:** Copie o contexto bruto para este arquivo.
> 3. **Se o Contexto Bruto estiver em um bloco `[ID-00]` neste `.md` (ou vier de `ALINHAMENTO_COMPORTAMENTOS.md`), mas não existir no GitHub Project nº 2:** Crie o card correspondente via `gh project item-create 2 --owner pinhogo`.
>
> ### Passo 2: Desmembramento Atômico e Investigação (Fluxo A)
> 1. Leia o **Contexto Bruto** e separe problemas ou melhorias independentes em IDs únicos sequenciais por domínio:
>    - `CAD-XX` (`/cadastro` e criação de conta) | `PRF-XX` (`/cadastro/perfil`) | `LNK-XX` (`/cadastro/links`)
>    - `BOT-XX` (`/cadastro/botoes`) | `COR-XX` (`/cadastro/cores`) | `FON-XX` (`/cadastro/fontes`)
>    - `EDT-XX` (`/editor`) | `PUB-XX` (`/:username`) | `GLB-XX` (Arquitetura/Global) | `FIX-<DOM>-XX` (Correções vindas da Auditoria)
> 2. **Regra de Ouro de Numeração:** Nunca reutilize um ID listado na linha de `IDs Históricos / Já Tratados` ou em `context/tasks_concluidas/INDEX.md`. Incremente sempre `+1`.
> 3. Inspecione o codebase (`grep`/`glob`) para identificar os arquivos reais afetados e preencha os **5 Blocos Obrigatórios** do Esqueleto abaixo em padrão **ASD-STE100 (Regra 11)**.
> 4. Quando gerar múltiplos IDs interdependentes, adicione ao final do grupo o bloco `### Ordem sugerida e dependências`.
>
> ### Passo 3: Promoção para o Fluxo B e Transição para Task Oficial (Pilar 2)
> 1. Após estruturar a pré-task neste arquivo, atualize o card correspondente no GitHub Project nº 2 com o mesmo título e texto resumido e mova-o da coluna `BACKLOG - FLUXO A` para `BACKLOG - FLUXO B` via `gh project item-edit`.
> 2. Quando o item for promovido para implementação (gerando `context/tasks_<dominio>.md` a partir do `context/esqueleto_tasks.md`):
>    - Acompanhe a execução nos planos do Pilar 2 (`context/tasks_<dominio>.md`) e no `coordination/BOARD.md`.
>    - Atualize o card no GitHub Project nº 2 e o `coordination/BOARD.md` mantendo paridade de título, subtarefas e checkboxes, e mova o card para `EXECUÇÃO - FLUXO C`.
> 3. **Expurgo em `DONE` e Transição:** Assim que a task concluir a Etapa 3 e for para `DONE`, remova o bloco completo deste arquivo, adicione apenas o seu código na linha `IDs Históricos / Já Tratados` abaixo e arquive o plano em `context/tasks_concluidas/`. As tarefas em revisão/testes ficam registradas exclusivamente nos planos do Pilar 2 e no `coordination/BOARD.md`.

---

## 🛠️ Esqueleto Obrigatório de Item do Backlog (5 Blocos)

```markdown
### [PREFIXO-00] Título Claro e Direto em Voz Ativa
- **Estado no Backlog:** Pendente *(mudar para `Em Tratativa` ao gerar `context/tasks_<dominio>.md`)*
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `PREFIXO (Domínio)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**
> [Trecho exato do relato do usuário ou da auditoria correspondente a este ID específico]

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**
- **Problema:** [Explicação técnica concisa da dor, bug ou regressão atual]
- **Solução Proposta:** [Abordagem arquitetural, de componentes, props ou estado]
- **OBS (Opcional):** [Regras de herança, ativos congelados ou casos de borda]
- **Como Executar (Subtarefas):**
  - [ ] [Passo técnico 1 no imperativo citando arquivos reais]
  - [ ] [Passo técnico 2 de ajuste de estado, props ou estilos]
  - [ ] [Passo técnico 3 de validação em 390px/1440px e testes]

**Localização & Referências:**
- **Rota / URL:** `/rota-afetada`
- **Arquivos / Pastas:** `src/views/...` | `src/components/...` *(ou `confirmar` se exigir auditoria)*
- **Docs / Assets:** `context/tasks_<dominio>.md` | `context/ALINHAMENTO_COMPORTAMENTOS.md`
```

> **IDs Históricos / Já Tratados (`DONE` no Project nº 2 — arquivados em `context/tasks_concluidas/INDEX.md`):**  
> `CAD-01` a `CAD-10`, `PRF-01` a `PRF-05`, `LNK-01` a `LNK-09` *(nota: `LNK-03` #19 consolidada em `#246` `PUB-09`)*, `BOT-01` a `BOT-04`, `COR-01`, `FON-01` a `FON-05`, `EDT-01` a `EDT-13`, `EDT-15`, `EDT-16`, `PUB-01`, `GLB-01` a `GLB-03`, `GLB-06` (`#238`), `INT-FASES-1-6` (`#6`), `BOARD-5.1` (`#7`), `FIX-CAD-01` a `FIX-CAD-03`, `FIX-PRF-01` a `FIX-PRF-04`, `FIX-GOV-01` a `FIX-GOV-03`, `FIX-LAY-01` a `FIX-LAY-05`, `FIX-LNK-01` a `FIX-LNK-02`, `FIX-BOT-01` a `FIX-BOT-05`, `FIX-API-01` a `FIX-API-04`, `FIX-FON-01` a `FIX-FON-05` (`#261`), `FIX-COR-01`, `TASK-PUB-01` a `TASK-PUB-03`, `API-T1` a `API-T9`, `TASK-E1` a `TASK-E10`, `TASK-M1` a `TASK-M8`, `AJUSTE-1` a `AJUSTE-11`, `AJUSTE-2.1` a `AJUSTE-2.3`.  
> *(Entregas `DONE` no Project nº 2 integradas neste ciclo)*: `PUB-02` (`#233`), `PUB-03` (`#234` — c/ `PUB-08` `#244`), `PUB-04` (`#235`), `PUB-05` (`#236`), `PUB-06` (`#242`), `PUB-07` (`#243` — c/ `FIX-PUB-07` `#240` & `FIX-PUB-08` `#241`), `PUB-09` (`#246` — c/ `FIX-PUB-06` `#239` & `LNK-03` `#19`), `PUB-10` (`#247`), `PUB-11` (`#248`), `PUB-12` (`#282`), `FIX-PUB-10` (`#281`), `FIX-PUB-11` (`#292`), `FIX-PUB-12` (`#293`), `FIX-FON-05` (`#261`), `FIX-FON-06` (`#298`), `FIX-BOT-06` (`#298`), `FIX-API-09` (`#298`), `GLB-07` (`#249`), `GLB-09` (`#286`), `PRF-06` (`#245`), `FIX-GLB-09` (`#256`), `FIX-EDT-17` (`#257`), `FIX-GLB-10` (`#258`), `FIX-GLB-14` (`#274`), `FIX-PRF-05` (`#276`), `FIX-PRF-07` (`#297`), `FIX-API-05` (`#250`), `FIX-API-06` (`#259`), `FIX-API-07` (`#262`), `FIX-CAD-04` (`#251`), `FIX-CAD-09` (`#272`), `FIX-EDT-01` (`#252`), `FIX-EDT-18` (`#275`), `FIX-GLB-16` (`#283`), `FIX-PUB-09` (`#253`), `FIX-GLB-11` (`#263`), `FIX-CAD-06` (`#269`), `SEC-01` (`#287`), `SEC-02` (`#288`), `SEC-03` (`#289`), `DOC-02` (`#295`), além de `EDT-14` (`#225`), `FIX-GLB-04` (`#232`) e `GLB-05` (`#237`) na `develop` *(movimentação manual de `#298` para `DONE` pendente no Project nº 2 por falta de escopo `read:project` no token CLI)*.
> *(Itens consolidados/encerrados sem execução separada em 2026-11-13)*: `FIX-PUB-06` (`#239`) & `LNK-03` (`#19`) → **`#246` (`PUB-09`)** | `FIX-GLB-07` (`#254`) → **`#237` (`GLB-05`)** | `FIX-GLB-08` (`#255`) → **`#232` (`FIX-GLB-04`)** | `FIX-PUB-07` (`#240`) & `FIX-PUB-08` (`#241`) → **`#243` (`PUB-07`)** | `PUB-08` (`#244`) → **`#234` (`PUB-03`)** | Itens v2 em `FREEZE` (`assinart_v2#1..#7`).

> [!NOTE]
> **Tarefas em Execução, Revisão e Testes (Pilar 2 — espelhadas em `coordination/BOARD.md`):**
> - **Fluxo C (`EXECUÇÃO - FLUXO C`):** Nenhum item ativo (`0`).
> - **Fluxo D (`CORREÇÃO E REVISÃO - FLUXO D`):** Nenhum item ativo (`0`).
> - **Fluxo E (`TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E`):** `17` itens do lote PR `#321` + Backend `#1` aguardam homologação (`13` cards `FIX-*` / `PUB-*` nesta fila + `4` `PUB-13`..`PUB-16`); **`#268`** (`FIX-API-08`), **`#325`** (`FIX-API-12`), **`#326`** (`LNK-15`), **`#299`** (`API-10..11`, `LNK-11..14`), **`#298`** (`FIX-API-09`, `FIX-BOT-06`, `FIX-FON-06`) e **`#245`**, **`#256`**, **`#257`**, **`#258`**, **`#274`**, **`#276`**, **`#297`** concluíram a Etapa 3 e avançaram para `DONE`.

---

## 📥 Fila Técnica — `BACKLOG - FLUXO B` (`0` entradas restantes — `#268`, `#325`, `#326` e `#299` promovidos para `EXECUÇÃO - FLUXO C`, `0` em Fluxo D, `17` em Fluxo E, `0` em Fluxo A)

> `#298` (`FIX-API-09`, `FIX-BOT-06`, `FIX-FON-06`), `#245` (`PRF-06`), `#256` (`FIX-GLB-09`), `#257` (`FIX-EDT-17`), `#258` (`FIX-GLB-10`), `#272` (`FIX-CAD-09`), `#274` (`FIX-GLB-14`), `#275` (`FIX-EDT-18`), `#276` (`FIX-PRF-05`), `#283` (`FIX-GLB-16`), `#297` (`FIX-PRF-07`), o lote completo `run_6d1ef873450a` (Ondas 1–4: `#264`, `#270`, `#271`, `#273`, `#277`, `#278`, `#279`, `#280`, `#285`, `#304`, `#305`, `#306`, `#313`, `#322`, `#324`) e `#284` (`FIX-GLB-17`, após remoção da Regra 7.3/7.4) concluídos e promovidos a `DONE` (Project nº 2); permanecem planejados em `BACKLOG - FLUXO B`: `#268` (`FIX-API-08`, backend), `#325` (`FIX-API-12`, backend pareada c/ `#326`), `#326` (`LNK-15`, cópia de e-mail com pop-up flutuante + correção no `/editor`) e o pacote full-stack `#299` (`API-10`, `API-11`, `LNK-11`–`LNK-14`).

---

## 📋 Backlog Planejado — `BACKLOG - FLUXO B` (`3` Entradas + `#299`) + Cards em Homologação (`13` em `FLUXO E`)

### [FIX-CAD-05] Impedir Estado Disponível de Username em Falha de Rede

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via Playwright abortando `/api/v1/usernames/check` e testes unitários)
- **GitHub Project (#2):** Status `DONE` | Domínio `CAD (Cadastro)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/264)

**Contexto Bruto:**

> Além disso, em /cadastro, quando GET /api/v1/usernames/check falha sem rede/CORS, o fallback local exibe "Disponível" em verde para qualquer @, levando o usuário ao erro só no envio. Esperado: nunca exibir "Disponível" quando a checagem no servidor falhar.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** A função validateSignupUsername em src/models/auth/signup.model.ts retorna state 'success' com a mensagem 'Disponível'. O hook useSignupAvailabilityCheck.ts captura falhas HTTP sem alterar o resultado retornado. O hook useSignupController.ts aplica serverUsernameCheck || validation.localUsernameValidation e mostra verde durante debounce e erro. O hook useEditorUsernameController.ts mantém usernameState em 'checking' quando a consulta falha.
- **Solução Proposta:** Separe validação sintática local e confirmação remota em signup.model.ts respeitando a Regra 5. Atualize useSignupAvailabilityCheck.ts para retornar erro explícito de verificação usando chaves em signup.content.ts. Ajuste useSignupController.ts, RegistrationStepView.tsx, SignupView.tsx e useEditorUsernameController.ts para nunca exibir verde sem resposta válida do servidor.
- **OBS:** Respeite Regras 2, 3 e 5 (models e hooks até 150 linhas; textos apenas em *.content.ts). O hook useLandingController.ts na Home também silencia erro de disponibilidade; mantenha src/views/landing/ congelada conforme Regra 7.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-CAD-05.1.** Altere validateSignupUsername em src/models/auth/signup.model.ts para retornar estado neutro sem confirmar disponibilidade.
  - [ ] **FIX-CAD-05.2.** Adicione mensagens de verificação pendente e erro de rede em src/content/auth/signup.content.ts.
  - [ ] **FIX-CAD-05.3.** Atualize src/controllers/hooks/auth/useSignupAvailabilityCheck.ts para propagar erro de rede ou CORS explícito.
  - [ ] **FIX-CAD-05.4.** Ajuste useSignupController.ts para bloquear estado verde durante debounce e falha de conexão.
  - [ ] **FIX-CAD-05.5.** Atualize RegistrationStepView.tsx e SignupView.tsx para exibir aviso quando a checagem falhar.
  - [ ] **FIX-CAD-05.6.** Ajuste src/controllers/hooks/editor/useEditorUsernameController.ts para sair de checking após falha remota.
  - [ ] **FIX-CAD-05.7.** Atualize test/controllers/hooks/auth/useSignupController.test.ts com casos de erro de rede.
  - [ ] **FIX-CAD-05.8.** Valide o campo de username em 390px e 1440px nos fluxos /cadastro e /editor.

**Localização & Referências:**

- **Rota / URL:** `/cadastro`, `/editor`
- **Arquivos / Pastas:** `src/models/auth/signup.model.ts` | `src/content/auth/signup.content.ts` | `src/controllers/hooks/auth/useSignupAvailabilityCheck.ts` | `src/controllers/hooks/auth/useSignupController.ts` | `src/controllers/hooks/editor/useEditorUsernameController.ts` | `src/views/onboarding/RegistrationStepView.tsx` | `src/views/auth/SignupView.tsx` | `test/controllers/hooks/auth/useSignupController.test.ts` | `test/views/onboarding/RegistrationStepView.test.tsx`
- **Docs / Assets:** context/tasks_cadastro.md | context/inventory.md | Issue #264

---

### [GLB-08] Publicar Conteúdo em Termos, Privacidade e Criar Página Ajuda

- **Estado no Backlog:** Pendente *(triagem Fluxo A -> Fluxo B a partir de `PVTI_lAHOCM7jCs4BqpNvzhImmMs`)*
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (https://github.com/pinhogo/assinart_v1/issues/265)

**Contexto Bruto:**

> O rodapé do onboarding exibe o link Ajuda (/ajuda), mas a rota não existe em AppRoutes.tsx: ao clicar, cai em /:username, faz GET /ajuda (404) e mostra "O @ajuda está livre! Reivindicar @ajuda". Já /termos e /privacidade (exigidos no aceite do cadastro) contêm apenas texto provisório ("serão publicados nesta página"). Esperado: Criar página ou ajustar o link /ajuda, publicar conteúdo válido em /termos e /privacidade.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** TermsView.tsx e PrivacyView.tsx delegam a renderização para PlaceholderView.tsx com textos provisórios. O projeto ainda não possui src/content/institutional/ para centralizar textos legais segundo a Regra 3. O arquivo src/routes/AppRoutes.tsx omite /ajuda e direciona cliques do rodapé para /:username.
- **Solução Proposta:** Crie src/content/institutional/institutional.content.ts com textos completos de Termos de Uso, Política de Privacidade e Ajuda. Evolua PlaceholderView.tsx e PlaceholderView.style.ts para exibir seções documentais com tokens de tokens.ts dentro de 120 linhas. Crie HelpView.tsx reutilizando esse layout e registre /ajuda em AppRoutes.tsx e public/sitemap.xml.
- **OBS:** Respeite Regras 1, 3, 4, 5 e 6 (até 3 unidades lógicas novas). A troca de <a href> para <Link> fica em FIX-GLB-12 e os slugs reservados ficam em FIX-GLB-13.
- **Como Executar (Subtarefas):**
  - [ ] **GLB-08.1.** Crie src/content/institutional/institutional.content.ts com seções de termos, privacidade e perguntas frequentes.
  - [ ] **GLB-08.2.** Atualize PlaceholderView.tsx e PlaceholderView.style.ts usando bordas e sombras de tokens.ts.
  - [ ] **GLB-08.3.** Atualize TermsView.tsx e PrivacyView.tsx para ler o conteúdo estruturado de institutional.content.ts.
  - [ ] **GLB-08.4.** Crie src/views/institutional/HelpView.tsx reutilizando o layout documental de PlaceholderView.tsx.
  - [ ] **GLB-08.5.** Registre a rota /ajuda em src/routes/AppRoutes.tsx.
  - [ ] **GLB-08.6.** Adicione a URL /ajuda em public/sitemap.xml.
  - [ ] **GLB-08.7.** Crie testes de renderização em test/views/institutional/InstitutionalViews.test.tsx para /termos, /privacidade e /ajuda.
  - [ ] **GLB-08.8.** Valide as páginas institucionais em 390px e 1440px.

**Localização & Referências:**

- **Rota / URL:** `/termos`, `/privacidade`, `/ajuda`
- **Arquivos / Pastas:** `src/content/institutional/institutional.content.ts` | `src/views/institutional/PlaceholderView.tsx` | `src/views/institutional/style/PlaceholderView.style.ts` | `src/views/institutional/TermsView.tsx` | `src/views/institutional/PrivacyView.tsx` | `src/views/institutional/HelpView.tsx` | `src/routes/AppRoutes.tsx` | `public/sitemap.xml` | `test/views/institutional/InstitutionalViews.test.tsx`
- **Docs / Assets:** context/tasks_global.md | context/context_components.md | Issue #265

---

### [FIX-GLB-12] Adotar Links SPA Internos e Mitigar 404 no GitHub Pages

- **Estado no Backlog:** Pendente *(triagem Fluxo A -> Fluxo B a partir de `PVTI_lAHOCM7jCs4BqpNvzhImmMs`)*
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (https://github.com/pinhogo/assinart_v1/issues/266)

**Contexto Bruto:**

> Além disso, usam <a href> no lugar de <Link> SPA, gerando HTTP 404 real do GitHub Pages no console a cada navegação ou reload direto de rota. Esperado: adotar roteamento SPA interno.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** OnboardingLayoutPreviewAndFooter.tsx usa <a href> para /ajuda, /playground e /termos. NeoButton.tsx renderiza <a href={appPath(link)}> para destinos internos mesmo sem newTab. O workflow deploy-pages.yml copia apenas dist/index.html para dist/404.html, gerando status HTTP 404 do GitHub Pages ao recarregar rotas estáticas.
- **Solução Proposta:** Substitua os elementos <a href> por <Link to> em OnboardingLayoutPreviewAndFooter.tsx. Atualize NeoButton.tsx para usar <Link to> em links internos sem newTab, preservando chamadas congeladas da Home via props sem editar src/views/landing/ (Regra 7). Estenda deploy-pages.yml para copiar dist/index.html aos diretórios das rotas estáticas mantendo 404.html para /:username.
- **OBS:** Regra 7: não modifique src/views/landing/. BrandLogo.tsx já usa <Link to> para caminhos iniciados por barra. Valide testes existentes que renderizam NeoButton com prop link adicionando MemoryRouter onde necessário.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-GLB-12.1.** Substitua os três links <a href> por <Link to> em OnboardingLayoutPreviewAndFooter.tsx.
  - [ ] **FIX-GLB-12.2.** Atualize src/components/ui/NeoButton.tsx para usar <Link to> em rotas internas sem newTab.
  - [ ] **FIX-GLB-12.3.** Atualize .github/workflows/deploy-pages.yml para gerar index.html nos diretórios das rotas estáticas.
  - [ ] **FIX-GLB-12.4.** Ajuste testes que renderizam NeoButton com link interno garantindo MemoryRouter.
  - [ ] **FIX-GLB-12.5.** Valide navegação SPA sem HTTP 404 no console em 390px e 1440px.

**Localização & Referências:**

- **Rota / URL:** `/cadastro`, `/entrar`, `/editor`, `/termos`, `/privacidade`, `/ajuda`, `/playground`
- **Arquivos / Pastas:** `src/components/onboarding/OnboardingLayoutPreviewAndFooter.tsx` | `src/components/ui/NeoButton.tsx` | `.github/workflows/deploy-pages.yml` | `test/views/onboarding/OnboardingResponsiveness.test.tsx` | `test/components/ui/NeoButton.test.tsx`
- **Docs / Assets:** context/tasks_global.md | Issue #266

---

### [FIX-GLB-13] Unificar Slugs Reservados e Bloquear Reivindicação de Rotas

- **Estado no Backlog:** Pendente *(triagem Fluxo A -> Fluxo B a partir de `PVTI_lAHOCM7jCs4BqpNvzhImmMs`)*
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (https://github.com/pinhogo/assinart_v1/issues/267)

**Contexto Bruto:**

> ao clicar, cai em /:username, faz GET /ajuda (404) e mostra "O @ajuda está livre! Reivindicar @ajuda". Esperado: bloquear slugs reservados (ajuda, termos, privacidade, playground).

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Quatro listas independentes mantêm slugs reservados no frontend com omissões para ajuda e playground. A função isClaimableUsername em publicProfile.model.ts valida apenas regex e exibe CTA de reivindicação para palavras reservadas.
- **Solução Proposta:** Centralize RESERVED_USERNAMES em src/models/auth/signup.model.ts incluindo ajuda, termos, privacidade, playground e rotas do aplicativo. Importe essa constante em useEditorUsernameController.ts, useLandingController.ts, mockSeedsAndTypes.ts e isClaimableUsername. Bloqueie slugs reservados em usePublicProfileFetch.ts e PublicProfileUnclaimedCard.tsx antes de consultar a API. Coordene com a task pareada backend FIX-API-08.
- **OBS:** Regra 7: edite apenas useLandingController.ts na camada controller e preserve src/views/landing/. Task pareada de backend criada em FIX-API-08 (Regra 13).
- **Como Executar (Subtarefas):**
  - [ ] **FIX-GLB-13.1.** Unifique RESERVED_USERNAMES em src/models/auth/signup.model.ts incluindo ajuda, playground, termos, privacidade e rotas do app.
  - [ ] **FIX-GLB-13.2.** Substitua listas locais duplicadas em useEditorUsernameController.ts, useLandingController.ts e mockSeedsAndTypes.ts.
  - [ ] **FIX-GLB-13.3.** Atualize isClaimableUsername em src/models/profile/publicProfile.model.ts para rejeitar slugs presentes em RESERVED_USERNAMES.
  - [ ] **FIX-GLB-13.4.** Bloqueie consultas HTTP e o botão Reivindicar em usePublicProfileController.ts e PublicProfileUnclaimedCard.tsx para slugs reservados.
  - [ ] **FIX-GLB-13.5.** Adicione testes unitários em test/controllers/hooks/profile/usePublicProfileController.test.ts cobrindo ajuda, termos, privacidade e playground.
  - [ ] **FIX-GLB-13.6.** Valide a exibição para slug reservado em /:username nas larguras 390px e 1440px.

**Localização & Referências:**

- **Rota / URL:** `/cadastro`, `/editor`, `/:username`
- **Arquivos / Pastas:** `src/models/auth/signup.model.ts` | `src/models/profile/publicProfile.model.ts` | `src/controllers/hooks/editor/useEditorUsernameController.ts` | `src/controllers/hooks/landing/useLandingController.ts` | `src/controllers/hooks/profile/usePublicProfileController.ts` | `src/controllers/hooks/profile/usePublicProfileFetch.ts` | `src/services/api/mock/mockSeedsAndTypes.ts` | `src/components/profile/PublicProfileUnclaimedCard.tsx` | `src/views/profile/PublicProfileView.tsx` | `test/controllers/hooks/profile/usePublicProfileController.test.ts`
- **Docs / Assets:** context/tasks_global.md | context/api_contracts.md | context/inventory.md | Issue #267

---

### [FIX-API-08] Centralizar e Bloquear Slugs Reservados no Registro, Perfil e Rotas Públicas do Backend

- **Estado no Backlog:** Em Tratativa *(promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_backend_api.md`](tasks_backend_api.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `GLB (Global)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/268)

**Contexto Bruto:**

> Task pareada (Regra 13) derivada de FIX-GLB-13 / PVTI_lAHOCM7jCs4BqpNvzhImmMs: ao clicar em /ajuda ou tentar registrar/alterar username para rotas SPA, o backend precisa compartilhar a mesma lista de palavras reservadas e rejeitar esses slugs nas rotas públicas e APIs.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** No repositório Laravel (/home/pinho/Projetos/assinart_v1_backend), UsernameController::RESERVED_WORDS e RegisterRequest::rules() repetem arrays separados sem 'ajuda'. PublicProfileController::RESERVED_ROUTES e routes/web_public.php restringem apenas ['api', 'storage', 'r', 'admin', 'login', 'register', 'dashboard', 'editor', 'up'], expondo rotas do SPA como /ajuda, /termos, /privacidade e /playground. Além disso, ProfileController::update valida apenas 'unique:artists,username' sem checar palavras reservadas.
- **Solução Proposta:** Centralize a lista única de slugs reservados em classe/configuração compartilhada no backend Laravel incluindo 'ajuda' e todas as rotas do app ('cadastro', 'entrar', 'esqueci-senha', 'confirmacao-email', 'playground', 'termos', 'privacidade', 'editor'). Aplique essa lista em UsernameController::check, RegisterRequest, ProfileController::update, PublicProfileController::show e no filtro regex de routes/web_public.php. Documente a regra em context/api_contracts.md.
- **OBS:** Task pareada obrigatória de FIX-GLB-13 (Regra 13). Executar em /home/pinho/Projetos/assinart_v1_backend antes ou em conjunto com FIX-GLB-13.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-API-08.1.** Extraia constante ou config única de palavras reservadas incluindo 'ajuda' e todas as rotas SPA.
  - [ ] **FIX-API-08.2.** Substitua os arrays duplicados em UsernameController.php e RegisterRequest.php pela fonte única.
  - [ ] **FIX-API-08.3.** Aplique validação de palavras reservadas no campo username de app/Http/Controllers/Api/ProfileController.php.
  - [ ] **FIX-API-08.4.** Atualize PublicProfileController::RESERVED_ROUTES e o filtro regex em routes/web_public.php.
  - [ ] **FIX-API-08.5.** Adicione testes Feature PHPUnit cobrindo check, register, PATCH /profile e GET /{username} para slugs reservados.
  - [ ] **FIX-API-08.6.** Atualize context/api_contracts.md com o contrato de slugs reservados.

**Localização & Referências:**

- **Rota / URL:** `GET /{username}`, `GET /api/v1/usernames/check`, `POST /api/v1/auth/register`, `PATCH /api/v1/profile`
- **Arquivos / Pastas:** `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/V1/UsernameController.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Requests/RegisterRequest.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/ProfileController.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/PublicProfileController.php` | `/home/pinho/Projetos/assinart_v1_backend/routes/web_public.php` | `context/api_contracts.md`
- **Docs / Assets:** context/api_contracts.md | context/tasks_global.md | Issue #268

---

### [FIX-CAD-07] Remover Nome Pinho Dev de Prévias e Fallbacks Fora da Home

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use` em `/cadastro` e `/natiarte` e em `/playground`, preservando Home/Landing e `isResidualName`)
- **GitHub Project (#2):** Status `DONE` | Domínio `CAD (Cadastro)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/270)

**Contexto Bruto:**

> Além disso, "Pinho Dev" ainda aparece na prévia de /cadastro antes da digitação e em fallbacks de onboarding/playground/storage. (Respeitar Home/HomeMockup congelados.) Esperado: remover "Pinho Dev" de prévias/fallbacks não congelados.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** A string 'Pinho Dev' aparece em signup.content.ts (previewProfile.name) e linkSelection.content.ts (preview.nameDefault). A mesma string aparece em playground.content.ts (nameValue), mockStorageStore.ts (natiarte) e usePublicProfileController.ts (DEMO_PROFILES.natiarte). A prévia de /cadastro exibe esse nome residual antes de o usuário preencher o formulário.
- **Solução Proposta:** Substitua 'Pinho Dev' por 'Seu Nome' em signup.content.ts e linkSelection.content.ts. Substitua 'Pinho Dev' por 'Nati Arte' nos dados da persona artística em playground.content.ts, mockStorageStore.ts e DEMO_PROFILES. Atualize MiniPhonePreview.tsx para derivar iniciais dinâmicas do nome exibido em vez de 'NA' fixo. Preserve 'Pinho Dev' apenas dentro de isResidualName em editorHydration.model.ts e mantenha a Home congelada (Regra 7).
- **OBS:** Regra 7 estrita: não altere 'Pinho Dev' em src/content/landing/landing.content.ts, src/views/landing/, src/components/mockups/HomeMockup.tsx ou HomeMockup.style.ts. Os testes em PublicProfileView.test.tsx e usePublicProfileController.test.ts esperam 'Pinho Dev' em /natiarte e exigem atualização.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-CAD-07.1.** Substitua 'Pinho Dev' por 'Seu Nome' em src/content/auth/signup.content.ts e src/content/onboarding/linkSelection.content.ts.
  - [ ] **FIX-CAD-07.2.** Atualize playground.content.ts, mockStorageStore.ts e DEMO_PROFILES em usePublicProfileController.ts para usar 'Nati Arte'.
  - [ ] **FIX-CAD-07.3.** Atualize src/components/mockups/MiniPhonePreview.tsx para calcular iniciais a partir do nome recebido.
  - [ ] **FIX-CAD-07.4.** Mantenha 'Pinho Dev' intacto dentro de isResidualName em src/models/editor/editorHydration.model.ts.
  - [ ] **FIX-CAD-07.5.** Atualize as asserções de /natiarte em test/views/profile/PublicProfileView.test.tsx e test/controllers/hooks/profile/usePublicProfileController.test.ts.
  - [ ] **FIX-CAD-07.6.** Valide as prévias em /cadastro, /cadastro/links, /playground e /natiarte em 390px e 1440px.

**Localização & Referências:**

- **Rota / URL:** `/cadastro`, `/cadastro/links`, `/playground`, `/:username`
- **Arquivos / Pastas:** `src/content/auth/signup.content.ts` | `src/content/onboarding/linkSelection.content.ts` | `src/content/playground/playground.content.ts` | `src/services/api/mock/mockStorageStore.ts` | `src/controllers/hooks/profile/usePublicProfileController.ts` | `src/components/mockups/MiniPhonePreview.tsx` | `test/views/profile/PublicProfileView.test.tsx` | `test/controllers/hooks/profile/usePublicProfileController.test.ts`
- **Docs / Assets:** context/tasks_cadastro.md | context/inventory.md | Issue #270

---

### [FIX-CAD-08] Separar Área Clicável do Checkbox de Termos dos Links em /cadastro

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use` e `NeoCheckbox.test.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `CAD (Cadastro)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/271)

**Contexto Bruto:**

> O <label> do checkbox "Li e aceito os Termos e a Política de Privacidade" envolve os links /termos e /privacidade; cliques na área textual próxima aos links redirecionam para /privacidade em vez de apenas marcar o aceite.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Em src/components/ui/NeoCheckbox.tsx (linhas 47-76), o elemento <label htmlFor={inputId}> envolve o indicador visual e todo o conteúdo de <span className={neoCheckboxLabel}>{label}</span>. Tanto src/views/onboarding/RegistrationStepView.tsx (linhas 96-103, rota ativa /cadastro) quanto src/views/auth/SignupView.tsx (linhas 133-144) passam os <Link> para /termos e /privacidade dentro da prop label. Em 390px, o link quebrado em duas linhas amplia a área de toque dentro do label e cliques próximos redirecionam para /privacidade sem marcar o checkbox, violando a semântica HTML de links interativos dentro de <label>.
- **Solução Proposta:** Refatorar NeoCheckbox.tsx para associar <label htmlFor={inputId}> somente ao controle visual e ao texto não interativo (ou usar container div com controle clicável e região textual fora de links acionando onChange), mantendo os <Link> para /termos e /privacidade como irmãos fora de <label>. Preservar estilos em NeoCheckbox.style.ts (Regra 1), cópia em signup.content.ts (Regra 3) e limite de 120 linhas em RegistrationStepView.tsx (atualmente com 119 linhas, Regra 5).
- **OBS:** Separação atômica 1/3 do draft PVTI_lAHOCM7jCs4BqpNvzhImmT4: isola o bug interativo de formulário (NeoCheckbox + RegistrationStepView/SignupView) do alinhamento do rodapé (FIX-CAD-09) e da prévia pré-cadastro (FIX-CAD-10) sem sobreposição de arquivos (Regra 15.13). Atenção ao teto de 120 linhas da Regra 5 em RegistrationStepView.tsx (119 linhas) e ao comportamento esperado em SignupView.tsx.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-CAD-08.1.** Refatore NeoCheckbox.tsx para remover <Link> de dentro de <label htmlFor={inputId}>.
  - [ ] **FIX-CAD-08.2.** Associe o toggle do checkbox apenas à caixa visual e ao texto fora dos links.
  - [ ] **FIX-CAD-08.3.** Ajuste os seletores em NeoCheckbox.style.ts para manter alinhamento e espaçamento sem Tailwind no JSX.
  - [ ] **FIX-CAD-08.4.** Atualize o consumo de NeoCheckbox em RegistrationStepView.tsx respeitando o limite de 120 linhas.
  - [ ] **FIX-CAD-08.5.** Aplique a mesma separação semântica de links em SignupView.tsx.
  - [ ] **FIX-CAD-08.6.** Adicione asserções em NeoCheckbox.test.tsx e RegistrationStepView.test.tsx separando clique de aceite e navegação.
  - [ ] **FIX-CAD-08.7.** Valide o comportamento de clique e toque em 390px e 1440px na rota /cadastro.

**Localização & Referências:**

- **Rota / URL:** `/cadastro`
- **Arquivos / Pastas:** `src/components/ui/NeoCheckbox.tsx` | `src/components/ui/style/NeoCheckbox.style.ts` | `src/views/onboarding/RegistrationStepView.tsx` | `src/views/auth/SignupView.tsx` | `test/components/ui/NeoCheckbox.test.tsx` | `test/views/onboarding/RegistrationStepView.test.tsx`
- **Docs / Assets:** context/tasks_concluidas/tasks_cadastro.md | context/tasks_concluidas/tasks_signup.md | Issue #271

---

### [FIX-CAD-10] Desabilitar Ações de Edição de Capa e Avatar na Prévia de /cadastro

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use` e `OnboardingLayoutMobileToggle.test.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `CAD (Cadastro)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/273)

**Contexto Bruto:**

> Além disso, a prévia na lateral direita já exibe + Alterar capa e + no avatar clicáveis antes da conta existir, sem efeito nem feedback.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Em OnboardingPhoneMockup (src/components/onboarding/OnboardingLayoutPreviewAndFooter.tsx, linhas 75-113), ProfileMockup é renderizado sem passar showCoverEditButton ou isAvatarEditable (ambos assumem true por padrão em ProfileMockup.tsx linhas 60 e 78) e recebe showAvatarEditHint junto de onAvatarChange, onAvatarError, onCoverChange e onBioChange mesmo quando activeTab === 'register'. Na rota /cadastro, os botões '+ Alterar capa' e '+' no avatar aceitam cliques antes de existir conta ou sessão autenticada, sem exibir erro em RegistrationStepView e sem efeito útil persistido. Em ProfileLinksList.tsx (linha 70), os links estáticos sem handlers já permanecem somente leitura.
- **Solução Proposta:** Calcular uma flag booleana isPreSignup = activeTab === 'register' em OnboardingPhoneMockup (OnboardingLayoutPreviewAndFooter.tsx) e passar showCoverEditButton={!isPreSignup}, isAvatarEditable={!isPreSignup} e showAvatarEditHint={!isPreSignup}, além de omitir onCoverChange, onAvatarChange, onAvatarError e onBioChange durante isPreSignup. Respeitar o limite de 120 linhas de OnboardingLayoutPreviewAndFooter.tsx (atualmente 116 linhas, Regra 5), extraindo subcomponente se necessário.
- **OBS:** Separação atômica 3/3 do draft PVTI_lAHOCM7jCs4BqpNvzhImmT4: atua exclusivamente em OnboardingLayoutPreviewAndFooter.tsx, sem colisão com FIX-CAD-08 nem FIX-CAD-09. Respeita a arquitetura MVC (Regra 2) e mantém intacto HomeMockup (Regra 7).
- **Atualização (`PUB-13`, 2026-11-14):** `ProfileMockup` agora aceita a prop `mode` (`'public' | 'preview' | 'edit'`) e `OnboardingPhoneMockup` passa `mode="edit"`. Use `mode={activeTab === 'register' ? 'preview' : 'edit'}` em `OnboardingLayoutPreviewAndFooter.tsx` em vez de passar `showCoverEditButton`, `isAvatarEditable` e `showAvatarEditHint` separados. O modo `preview` oculta "Alterar capa" e o "+" do avatar. Mantenha a omissão dos callbacks `onCoverChange`, `onAvatarChange`, `onAvatarError` e `onBioChange`.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-CAD-10.1.** Defina isPreSignup como activeTab === 'register' em OnboardingPhoneMockup no arquivo OnboardingLayoutPreviewAndFooter.tsx.
  - [ ] **FIX-CAD-10.2.** Passe showCoverEditButton={!isPreSignup}, isAvatarEditable={!isPreSignup} e showAvatarEditHint={!isPreSignup} para ProfileMockup.
  - [ ] **FIX-CAD-10.3.** Omita os callbacks onCoverChange, onAvatarChange, onAvatarError e onBioChange enquanto isPreSignup for true.
  - [ ] **FIX-CAD-10.4.** Mantenha OnboardingLayoutPreviewAndFooter.tsx dentro do teto de 120 linhas da Regra 5.
  - [ ] **FIX-CAD-10.5.** Adicione testes em OnboardingLayoutMobileToggle.test.tsx confirmando ocultação dos botões de capa e avatar em /cadastro.
  - [ ] **FIX-CAD-10.6.** Valide na rota /cadastro em 390px e 1440px que o mockup pré-cadastro está em modo somente leitura.

**Localização & Referências:**

- **Rota / URL:** `/cadastro`
- **Arquivos / Pastas:** `src/components/onboarding/OnboardingLayoutPreviewAndFooter.tsx` | `test/components/onboarding/OnboardingLayoutMobileToggle.test.tsx`
- **Docs / Assets:** context/tasks_concluidas/tasks_cadastro.md | context/tasks_concluidas/tasks_perfil.md | Issue #273

---

### [FIX-GLB-15] Garantir Alto Contraste Explícito no Ícone de Sol do ThemeToggle no Modo Escuro

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use` e `ThemeToggle.test.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/277)

**Contexto Bruto:**

> No modo escuro em /cadastro/perfil, o ícone de sol do botão de tema tem contraste muito baixo contra o fundo escuro.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Em src/components/ui/style/ThemeToggle.style.ts (linhas 4-15) e src/assets/colors.ts (linhas 741-746), componentsColors.themeToggle define apenas bg: 'bg-white', text: 'text-black' e focusRing: 'focus-visible:ring-black' sem variantes dark: explícitas, ao contrário dos tokens de appHeader e landing.header atualizados no commit dd34ace. O SVG do sol em src/components/ui/ThemeToggle.tsx (linhas 33-36) depende de 'fill-none stroke-current stroke-2' herdando color via regra global .theme-dark .text-black em src/index.css (linhas 211-224), enquanto .theme-dark .bg-white (linhas 157-160) converte o fundo do botão para #22252E. Em medição de build atual (4ac172d) em /cadastro/perfil, o estado estabilizado atinge #F4F3EE sobre #22252E com transição intermediária para acinzentado durante a animação (250ms), porém sem variante dark: explícita para o contexto .dark nem destaque de cor próprio para o ícone de sol sobre fundo escuro, além de ausência total de testes unitários para ThemeToggle.
- **Solução Proposta:** Definir contraste explícito no modo escuro para ThemeToggle (em ThemeToggle.style.ts e, mediante autorização pela Regra 7.4, nos tokens de themeToggle em src/assets/colors.ts — ou com classes dark: diretas em ThemeToggle.style.ts sem violar Regra 7.4), combinando fundo escuro de alto contraste (ex.: dark:bg-[#181A20] ou preservando destaque adequado) com traço claro ou solar de alto contraste no ícone de sol (ex.: dark:text-[#F4F3EE] ou dark:text-[#FFE600], dark:focus-visible:ring-white) e stroke='currentColor' explícito no SVG em ThemeToggle.tsx. Remover dependência frágil de override implícito de .theme-dark .bg-white/.text-black e criar suíte unitária dedicada para ThemeToggle.
- **OBS:** Separação atômica 2/2 do draft PVTI_lAHOCM7jCs4BqpNvzhImmbo: trata apenas do contraste do ThemeToggle no modo escuro, mantendo isolamento total de arquivos frente a FIX-PRF-05. Atenção à Regra 7.4: src/assets/colors.ts e tokens.ts não podem ser alterados sem autorização explícita; caso não autorizada, concentrar variantes dark: e cores explícitas em ThemeToggle.style.ts e src/index.css. Observado ainda que headerThemeToggle em HeaderSection.style.ts compartilha tokens parecidos, porém LandingView é ativo congelado pela Regra 7.1.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-GLB-15.1.** Adicione variantes dark: explícitas de fundo, foco e ícone em ThemeToggle.style.ts.
  - [ ] **FIX-GLB-15.2.** Adicione o atributo stroke='currentColor' nos SVGs de ThemeToggle.tsx mantendo zero Tailwind no JSX.
  - [ ] **FIX-GLB-15.3.** Ajuste os seletores em src/index.css para evitar conflito com o contraste de ThemeToggle.
  - [ ] **FIX-GLB-15.4.** Crie testes unitários para ThemeToggle validando classes e contraste no modo claro e escuro.
  - [ ] **FIX-GLB-15.5.** Valide o contraste WCAG do ícone de sol no modo escuro em /cadastro/perfil e /editor.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/perfil`, `/cadastro`, `/editor`, `/playground`
- **Arquivos / Pastas:** `src/components/ui/ThemeToggle.tsx` | `src/components/ui/style/ThemeToggle.style.ts` | `src/index.css`
- **Docs / Assets:** context/tasks_concluidas/tasks_dark_mode.md | context/tasks_concluidas/tasks_global_dark_mode.md | Issue #277

---

### [LNK-10] Exibir Ícone e Nome da Plataforma nos Cards de Links Ativos

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado em `1440px` e `390px` em `/cadastro/links` e em `EditableLinkCard.test.tsx`/`ActiveLinksList.test.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `LNK (Links)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/278)

**Contexto Bruto:**

> Nos cards de "SEUS LINKS ATIVOS", o topo esquerdo mostra apenas a bolinha verde sem exibir o nome da rede/tipo (Instagram, WhatsApp, Link Personalizado), dificultando identificar qual link é qual após personalizar o título (e em 390px os 7 botões comprimem ainda mais o espaço). Esperado: Exibir ícone/nome da plataforma no topo do card.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** EditableLinkCard exibe apenas a bolinha de status em activeLinksCardTop. ActiveLinksList usa getPlatformRule e cai em PLATFORM_RULES.website para links personalizados sem platformId. Em 390px, activeLinksCardTop mantém linha única com 7 botões e comprime o lado esquerdo.
- **Solução Proposta:** Adicione props opcionais platformName e platformIconSrc em EditableLinkCard. Em ActiveLinksList, identifique links sem platformId como Link Personalizado via linkSelectionContent. Passe platformIconSources[link.platformId] ou ícone de link genérico ao card. Em EditableLinkCard.style.ts, faça activeLinksCardTop quebrar linha abaixo do breakpoint sm para proteger os 7 botões em 390px.
- **OBS:** ActiveLinksList atende /cadastro/links e /editor. Preserve src/assets/social-icons/ intacto conforme a Regra 7 usando apenas imports de leitura. Este item não colide com tasks ativas do Fluxo B.
- **Como Executar (Subtarefas):**
  - [ ] **LNK-10.1.** Adicione platformName e platformIconSrc opcionais em EditableLinkCardProps dentro de EditableLinkCard.tsx.
  - [ ] **LNK-10.2.** Renderize ícone e nome da plataforma ao lado da bolinha de status em activeLinksCardTop.
  - [ ] **LNK-10.3.** Ajuste activeLinksCardTop em EditableLinkCard.style.ts para empilhar rótulo e ações abaixo de sm.
  - [ ] **LNK-10.4.** Mapeie links sem platformId para o rótulo Link Personalizado em ActiveLinksList.tsx.
  - [ ] **LNK-10.5.** Forneça platformIconSources[link.platformId] ou ícone padrão de link para EditableLinkCard.
  - [ ] **LNK-10.6.** Atualize os exemplos de EditableLinkCard em OnboardingComponentsPlaygroundSection.tsx.
  - [ ] **LNK-10.7.** Valide o card em 390px e 1440px e atualize EditableLinkCard.test.tsx e ActiveLinksList.test.tsx.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`, `/playground`
- **Arquivos / Pastas:** `src/components/onboarding/EditableLinkCard.tsx` | `src/components/onboarding/style/EditableLinkCard.style.ts` | `src/components/onboarding/ActiveLinksList.tsx` | `src/content/onboarding/linkSelection.content.ts` | `src/components/playground/OnboardingComponentsPlaygroundSection.tsx` | `test/components/onboarding/EditableLinkCard.test.tsx` | `test/components/onboarding/ActiveLinksList.test.tsx`
- **Docs / Assets:** context/context_components.md | context/tasks_concluidas/tasks_links.md | Issue #278

---

### [FIX-LNK-03] Criar Link Personalizado Apenas ao Confirmar Modal sem Salvar Link Fantasma

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use`, `LinkSelectionView.test.tsx` e `LinkDisplayModeLimits.test.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `LNK (Links)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/279)

**Contexto Bruto:**

> Além disso, clicar em + Adicionar novo link personalizado já cria um card vazio antes do envio: ao clicar em Cancelar, um link fantasma ("Novo link" / "Link Personalizado" -> https://assin.art) permanece salvo na lista. Esperado: criar link personalizado apenas ao confirmar Salvar link no modal.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** handleAddCustomLink em useLinkSelectionViewController.ts executa profile.addCustomLink() antes de abrir CustomLinkModal. Ao cancelar o modal, closeCustomModal fecha o diálogo e mantém o link vazio salvo na lista. Em buildCustomPreviewItem, esse item vazio aparece como Novo link na prévia em /cadastro/links e /editor.
- **Solução Proposta:** Remova profile.addCustomLink() de handleAddCustomLink em useLinkSelectionViewController.ts. Crie o link somente dentro de handleSaveCustomLink chamando profile.addCustomLink({ title, url }). Remova a busca por emptyLink e simplifique isModalMaxReached para usar apenas isMaxReached.
- **OBS:** useOnboardingLinksState e applyOverrideAddCustom já aceitam initial com title e url. LinkSelectionView.test.tsx (linhas 53-67, 119-135 e 144-160) testa a criação imediata antes do modal e exige atualização.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-LNK-03.1.** Remova profile.addCustomLink() de handleAddCustomLink em useLinkSelectionViewController.ts.
  - [ ] **FIX-LNK-03.2.** Remova o bloco emptyLink de handleSaveCustomLink mantendo apenas profile.addCustomLink({ title, url }).
  - [ ] **FIX-LNK-03.3.** Simplifique isModalMaxReached para retornar apenas isMaxReached em useLinkSelectionViewController.ts.
  - [ ] **FIX-LNK-03.4.** Atualize LinkSelectionView.test.tsx para salvar o modal antes de validar novos cards.
  - [ ] **FIX-LNK-03.5.** Adicione testes de Cancelar e Escape sem criar link em LinkSelectionView.test.tsx e EditorView.test.tsx.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`
- **Arquivos / Pastas:** `src/controllers/hooks/onboarding/useLinkSelectionViewController.ts` | `test/views/onboarding/LinkSelectionView.test.tsx` | `test/controllers/hooks/onboarding/useLinkSelectionController.test.ts` | `test/views/editor/EditorView.test.tsx`
- **Docs / Assets:** context/inventory.md | context/tasks_editor.md | Issue #279

---

### [FIX-LNK-04] Remover Espaço Extra Após Prefixo de URL na Prévia dos Botões

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — já consolidado em `PUB-14`, verificado visualmente via `/orca-computer-use` em `/playground`, `/cadastro/links` e `/cadastro/botoes`)
- **GitHub Project (#2):** Status `DONE` | Domínio `LNK (Links)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/280)

**Contexto Bruto:**

> na prévia de /cadastro/links, o prefixo instagram.com/ também exibe espaço extra antes do handle. Esperado: remover o espaço sobrando após instagram.com/ na prévia.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** phoneLinkButtonContentWrapper em PhoneLinkButton.style.ts aplica gap-1 entre elementos flex filhos. PhoneLinkButtonEditable e PhoneLinkButtonContent colocam phoneLinkButtonPrefix diretamente ao lado do input ou valor. Esse gap-1 gera um espaço visível após instagram.com/ antes do handle na prévia.
- **Solução Proposta:** Remova gap-1 de phoneLinkButtonContentWrapper em PhoneLinkButton.style.ts preservando min-w-0 e overflow-hidden. Adicione whitespace-pre em phoneLinkButtonPrefix para preservar o espaço final intencional de wa.me/55 em PLATFORMS_DATA.whatsapp.prefix. Verifique o alinhamento interno entre prefixo e texto em PhoneLinkButtonEditable.tsx e PhoneLinkButtonContent.tsx.
- **OBS:** challenger_t1_t2_t3.test.ts (linhas 295-297) valida apenas min-w-0 e overflow-hidden em phoneLinkButtonContentWrapper. Preserve espaço correto tanto no prefixo colado instagram.com/ quanto no prefixo com espaço wa.me/55 .
- **Atualização (`PUB-14`, 2026-11-14):** O botão da prévia mostra só o título do link. `PhoneLinkButtonContent` não renderiza mais `prefix`, `value` nem `placeholder`, e o ramo `PhoneLinkButtonEditable` saiu de `PhoneLinkButton`. O espaço após `instagram.com/` deixa de existir na prévia. Valide em `/cadastro/links` e `/editor`. Encerre este item como consolidado em `PUB-14` se a validação confirmar.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-LNK-04.1.** Remova a classe gap-1 de phoneLinkButtonContentWrapper em PhoneLinkButton.style.ts.
  - [ ] **FIX-LNK-04.2.** Adicione whitespace-pre em phoneLinkButtonPrefix para manter o espaço de wa.me/55 .
  - [ ] **FIX-LNK-04.3.** Confirme a colagem entre prefixo e texto em PhoneLinkButtonEditable.tsx e PhoneLinkButtonContent.tsx.
  - [ ] **FIX-LNK-04.4.** Valide prévia de Instagram e WhatsApp e atualize PhoneLinkButton.test.tsx.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`, `/:username`
- **Arquivos / Pastas:** `src/components/mockups/style/PhoneLinkButton.style.ts` | `src/components/mockups/PhoneLinkButton/PhoneLinkButtonEditable.tsx` | `src/components/mockups/PhoneLinkButton/PhoneLinkButtonContent.tsx` | `test/components/mockups/PhoneLinkButton.test.tsx`
- **Docs / Assets:** context/context_components.md | Issue #280

---

### [FIX-GLB-17] Definir Estratégia e Restaurar Cor Escura Real na Variante dark do NeoButton

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` após remoção das proteções da Regra 7.3/7.4 — `neoButton.dark` em `src/assets/colors.ts` restaurado para `bg-[#121316]` / `hover:bg-[#18191D]` e Home preservada com `variant="primary"` em `EarlyPortfolioSection.tsx` e `FinalCtaSection.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/284)

**Contexto Bruto:**

> No /playground, NeoButton DARK renderiza em laranja idêntico a PRIMARY.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** componentsColors.neoButton.dark.bg em src/assets/colors.ts define bg-[#E85c00], cor laranja idêntica à variante primary. Assim, NeoButton variant="dark" renderiza em laranja no Playground, Home, ForgotPasswordView e EmailConfirmationView.
- **Solução Proposta:** Restaure `bg-[#121316]` e `hover:bg-[#18191D]` em `componentsColors.neoButton.dark` no arquivo `src/assets/colors.ts`, consumindo `hoverBg` em `NeoButton.style.ts`, e ajuste `EarlyPortfolioSection.tsx` e `FinalCtaSection.tsx` para `variant="primary"` preservando os CTAs laranjas na Home.
- **Como Executar (Subtarefas):**
  - [x] **FIX-GLB-17.1.** Decisão confirmada pelo usuário junto à remoção das proteções de ativos congelados (`AGENTS.md`, `.github/copilot-instructions.md`, `context/rules.md`, `context/ROTINA_E_ORGANIZACAO.md`).
  - [x] **FIX-GLB-17.2.** Atualize `componentsColors.neoButton.dark` em `src/assets/colors.ts`, `NeoButton.style.ts` e passe `variant="primary"` em `EarlyPortfolioSection.tsx` e `FinalCtaSection.tsx`.
  - [x] **FIX-GLB-17.3.** Valide `NeoButton variant="dark"` em `/playground`, `/esqueci-senha` e `/` (light/dark) e atualize `PlaygroundView.test.tsx`.

**Localização & Referências:**

- **Rota / URL:** `/playground`, `/esqueci-senha`, `/confirmacao-email`, `/`
- **Arquivos / Pastas:** `src/assets/colors.ts` | `src/components/ui/style/NeoButton.style.ts` | `src/components/playground/UiPrimitivesPlaygroundSection.tsx` | `test/views/playground/PlaygroundView.test.tsx`
- **Docs / Assets:** context/tasks_global.md | Issue #284

---

### [EDT-17] Remover Entradas Redundantes de Avatar e Configurações no Menu da Conta

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `AccountMenu.test.tsx`, `EditorView.test.tsx` e suíte completa)
- **GitHub Project (#2):** Status `DONE` | Domínio `EDT (Editor)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/285)

**Contexto Bruto:**

> No /editor, o menu do avatar no cabeçalho repete "Configurações da conta" em paralelo com a aba fixa Configurações, e "Alterar avatar" compete com + na prévia e Aparência > Mídia. Esperado: Simplificar os pontos de entrada redundantes no /editor.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** AccountMenu.tsx exibe Alterar avatar e Configurações da conta no cabeçalho de /editor. A aba fixa Configurações já abre configurações da conta, e ProfileAvatar (+) mais Aparência > Mídia já alteram o avatar com recorte. Além disso, o fluxo de Alterar avatar abre EditorAvatarUploadModal sem usar ImageCropModal.
- **Solução Proposta:** Remova os botões changeAvatar e accountSettings de AccountMenu.tsx mantendo Ver minha página e Sair da conta. Remova as props onAvatarClick e onSettingsClick em AccountMenu.tsx, EditorHeaderAndBioSection.tsx e EditorView.tsx. Coordene com #257 (FIX-EDT-17) a limpeza de openAvatarModal e EditorAvatarUploadModal em useEditorModalsController.ts e EditorView.tsx.
- **OBS:** Coordenação obrigatória com #257 (FIX-EDT-17), que altera useEditorModalsController.ts e EditorView.tsx. EditorView.test.tsx (linha 212) verifica o texto Configurações da conta no menu e precisa de atualização. EditorSettingsPlaygroundSection.tsx e EditorAvatarUploadModal.test.tsx ainda importam EditorAvatarUploadModal.
- **Como Executar (Subtarefas):**
  - [ ] **EDT-17.1.** Remova os botões changeAvatar e accountSettings de AccountMenu.tsx mantendo viewMyPage e logout.
  - [ ] **EDT-17.2.** Remova props onAvatarClick e onSettingsClick em AccountMenu.tsx, EditorHeaderAndBioSection.tsx e EditorView.tsx.
  - [ ] **EDT-17.3.** Remova chaves obsoletas de editorModalsContent.accountMenu em src/content/editor/editorModals.content.ts.
  - [ ] **EDT-17.4.** Coordene com #257 a remoção de openAvatarModal e EditorAvatarUploadModal em useEditorModalsController.ts e EditorView.tsx.
  - [ ] **EDT-17.5.** Atualize EditorView.test.tsx, EditorStickyMobileViewBar.test.tsx e useEditorModalsController.test.ts.

**Localização & Referências:**

- **Rota / URL:** `/editor`
- **Arquivos / Pastas:** `src/components/editor/AccountMenu.tsx` | `src/components/editor/EditorHeaderAndBioSection.tsx` | `src/views/editor/EditorView.tsx` | `src/content/editor/editorModals.content.ts` | `src/controllers/hooks/editor/useEditorModalsController.ts` | `test/views/editor/EditorView.test.tsx` | `test/components/editor/EditorStickyMobileViewBar.test.tsx` | `test/controllers/hooks/editor/useEditorModalsController.test.ts`
- **Docs / Assets:** context/tasks_editor.md | context/context_components.md | Issue #285

---

### [FIX-GLB-18] Corrigir Posição dos Menus Flutuantes ⋮ sob o zoom 0.9 do :root em Desktop

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use` em `/cadastro/botoes` e `floatingMenuPosition.model.test.ts`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #304: https://github.com/pinhogo/assinart_v1/issues/304)

**Contexto Bruto:**

> Revisão `PUB-REV`: em 1440x900, o menu `⋮` de `/cadastro/botoes`, `/cadastro/fontes` e `/editor` (Aparência > Botões e Fonte) abre deslocado para cima e à esquerda e cobre o próprio botão. Em 390px alinha ao gatilho. Esperado: menu junto ao gatilho em qualquer largura.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `src/index.css` aplica `zoom: 0.9` ao `:root` a partir de 768px. `useFloatingMenuPosition` calcula `left` e `top` com `getBoundingClientRect()` e `window.innerWidth`. `LinkActionMenu` e `FontLinkActionMenu` aplicam o resultado em `position: fixed` num portal em `document.body`. O `body` também recebe o zoom e escala a coordenada pela segunda vez. Medido: gatilho com `right = 1162.275`, `style.left = 922.275px` (= 1162.275 − 240) e menu renderizado em `x = 830.05` (= 922.275 × 0.9). O menu não tem `transform`. Defeito pré-existente: o hook e os menus não mudaram no lote `PUB-13` a `PUB-16`.
- **Solução Proposta:** Divida as coordenadas calculadas pelo fator de zoom efetivo do `:root` dentro de `useFloatingMenuPosition`. Leia o fator de `getComputedStyle(document.documentElement).zoom` e use `1` quando o valor for inválido. Mantenha `MENU_WIDTH`, `MARGIN` e `VIEWPORT_PADDING` em px de CSS. Corrija `maxHeight` e a inversão `bottom` com o mesmo fator.
- **OBS:** Corrija no hook compartilhado, e não em cada menu. Não altere `src/index.css`. Não remova o `zoom: 0.9`. Valide a inversão `anchorAbove` nos três últimos botões da lista.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-GLB-18.1.** Extraia o cálculo do fator de zoom do `:root` para uma função pura em `src/models/` (≤ 150 linhas), sem DOM no model.
  - [ ] **FIX-GLB-18.2.** Divida `left`, `top`, `bottom` e `maxHeight` pelo fator de zoom antes do `setCoords` em `useFloatingMenuPosition.ts`.
  - [ ] **FIX-GLB-18.3.** Aplique o mesmo cálculo nos eventos de `scroll` e `resize`.
  - [ ] **FIX-GLB-18.4.** Valide `LinkActionMenu` e `FontLinkActionMenu` em `/cadastro/botoes`, `/cadastro/fontes` e `/editor` em 390px e 1440px.
  - [ ] **FIX-GLB-18.5.** Valide o menu dos ícones circulares na aba Botões e a abertura acima do gatilho (`anchorAbove`).

**Localização & Referências:**

- **Rota / URL:** `/cadastro/botoes`, `/cadastro/fontes`, `/editor`
- **Arquivos / Pastas:** `src/controllers/hooks/mockup/useFloatingMenuPosition.ts` | `src/components/mockups/LinkActionMenu/LinkActionMenu.tsx` | `src/components/mockups/FontLinkActionMenu/FontLinkActionMenu.tsx` | `src/index.css` (somente leitura)
- **Docs / Assets:** context/tasks_mockup_perfil.md (achado F13) | `PUB-REV` (`/tmp/pub_review/results.json`)

---

### [FIX-EDT-19] Atualizar o Nome do Mockup ao Digitar em /editor > Configurações

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `useEditorProfileInfoController.test.ts` mantendo save somente ao confirmar)
- **GitHub Project (#2):** Status `DONE` | Domínio `EDT (Editor)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #305: https://github.com/pinhogo/assinart_v1/issues/305)

**Contexto Bruto:**

> Revisão `PUB-REV`: em `/editor` > Configurações, editar `#editor-settings-name` não altera o `<h1>` do mockup, mesmo após blur. Bio, fonte e cor refletem na hora. Em `/cadastro/perfil` o nome atualiza ao vivo. Esperado: o mockup reflete o nome digitado na hora.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `OnboardingPhoneMockup` exibe `contextValue.name?.trim() || contextValue.previewName?.trim()`. O nome do contexto tem prioridade sobre `previewName`. `useEditorProfileInfoController.handleNameChange` chama apenas `setPreviewName`. `useProfileCustomizationController.setPreviewName` chama também `handleNameChange` do contexto, que atualiza `name`. Em `/editor`, `name` mantém o valor antigo e o `previewName` novo é ignorado. Defeito pré-existente: os dois hooks não mudaram no lote.
- **Solução Proposta:** Chame `profile?.handleNameChange?.(val)` em `useEditorProfileInfoController.handleNameChange`, junto de `setPreviewName`. Mantenha o salvamento no backend só em `handleSave`.
- **OBS:** Verifique se `handleNameChange` do contexto dispara o auto-salvo do rascunho e o indicador `Salvando`. Se disparar, use apenas o estado local do contexto, para não gravar o nome antes de o usuário confirmar.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-EDT-19.1.** Leia `handleNameChange` em `useOnboardingIdentityState` e confirme os efeitos colaterais (auto-salvo e rascunho).
  - [ ] **FIX-EDT-19.2.** Chame o setter de nome do contexto em `useEditorProfileInfoController.handleNameChange`.
  - [ ] **FIX-EDT-19.3.** Mantenha `setPreviewName` apenas para o nome do cabeçalho do editor, se ainda usado.
  - [ ] **FIX-EDT-19.4.** Valide em `/editor` > Configurações em 390px e 1440px que o `<h1>` do mockup muda a cada tecla.
  - [ ] **FIX-EDT-19.5.** Valide que `/cadastro/perfil` mantém o comportamento atual.

**Localização & Referências:**

- **Rota / URL:** `/editor` (Configurações)
- **Arquivos / Pastas:** `src/controllers/hooks/editor/useEditorProfileInfoController.ts` | `src/controllers/hooks/onboarding/useProfileCustomizationController.ts` | `src/components/onboarding/OnboardingLayoutPreviewAndFooter.tsx`
- **Docs / Assets:** context/tasks_concluidas/tasks_editor.md | `PUB-REV` (`/tmp/pub_review/followup_results.json`)

---

### [FIX-PUB-13] Aplicar Negrito, Itálico e Contorno Individuais dos Botões na Página Pública

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — já consolidado em DTO, `publicProfileAppearance.model.ts`, `publicProfileMockupAdapter.model.ts`, handlers mock, `api_contracts.md` e coberto por `PublicProfileButtonLinksList.test.tsx`, `publicProfileAppearance.model.test.ts` e `publicProfileMockupAdapter.model.test.ts`)
- **GitHub Project (#2):** Status `DONE` | Domínio `PUB (Página Pública)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #306: https://github.com/pinhogo/assinart_v1/issues/306)

**Contexto Bruto:**

> Revisão `PUB-REV`: o backend retorna `Portfólio 2` com `font_weight: "bold"`, `font_style: "italic"` e `text_border: "yellow"`. O mockup de `/cadastro/links` mostra esses estilos. Em `/1997` eles não aparecem, na base (`origin/develop`) e no lote. Esperado: a página pública mostra o estilo individual igual ao mockup.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `FormatsPublicProfile::formatButtonLinks` envia `font_weight`, `font_style` e `text_border` por botão (linhas 89 a 91, já resolvidos). `PublicProfileButton` em `apiResponse.model.ts` e `PublicProfileLink` não têm esses campos. `mapApiButtonToLink` e `adaptPublicProfileLinkToMockupLink` só repassam `resolved_font_family`. `PhoneLinkButtonProps` já aceita `fontWeight`, `fontStyle` e `textBorder`. Defeito pré-existente.
- **Solução Proposta:** Adicione `fontWeight`, `fontStyle` e `textBorder` opcionais em `PublicProfileButton` e `PublicProfileLink`. Mapeie os três em `mapApiButtonToLink` e repasse em `adaptPublicProfileLinkToMockupLink`. Preserve o padrão atual (`normal`, `normal`, `none`) quando o campo faltar.
- **OBS:** Regra 13: o contrato já existe no backend, então não abra tarefa de backend. Atualize `context/api_contracts.md` na Etapa 3 e os mocks em `src/services/api/mock/`. Mapeie `snake_case` para `camelCase` (Regra 13.4).
- **Como Executar (Subtarefas):**
  - [ ] **FIX-PUB-13.1.** Adicione `font_weight`, `font_style` e `text_border` opcionais em `PublicProfileButton` (`src/models/api/apiResponse.model.ts`).
  - [ ] **FIX-PUB-13.2.** Adicione `fontWeight`, `fontStyle` e `textBorder` em `PublicProfileLink` (`src/models/profile/publicProfile.model.ts`).
  - [ ] **FIX-PUB-13.3.** Mapeie os três campos em `mapApiButtonToLink` em `publicProfileMockupAdapter.model.ts`.
  - [ ] **FIX-PUB-13.4.** Repasse os três campos em `adaptPublicProfileLinkToMockupLink`.
  - [ ] **FIX-PUB-13.5.** Atualize os handlers em `src/services/api/mock/` com um botão em negrito, itálico e contorno.
  - [ ] **FIX-PUB-13.6.** Valide `/1997` em 390px e 1440px contra o mockup de `/cadastro/links` com os mesmos dados.

**Localização & Referências:**

- **Rota / URL:** `/:username`
- **Arquivos / Pastas:** `src/models/api/apiResponse.model.ts` | `src/models/profile/publicProfile.model.ts` | `src/models/profile/publicProfileMockupAdapter.model.ts` | `src/services/api/mock/` | `context/api_contracts.md`
- **Docs / Assets:** context/tasks_fontes.md (FIX-FON-05 removeu só os controles globais) | `PUB-REV` (`/tmp/pub_review/classify_rev_public_1440.png`)

---

### [FIX-PUB-14] Remover a Opção Automático do Carrossel de Cor do Fundo dos Ícones Circulares

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir da revisão independente `PUB-REV` do lote `PUB-13` a `PUB-16`, 2026-11-14; decisão do usuário em 2026-11-14: remover a opção Automático)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `PUB (Página Pública)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #307: https://github.com/pinhogo/assinart_v1/issues/307)

**Contexto Bruto:**

> Revisão `PUB-REV`: com `circular_icon_background: "auto"` e `icon_variant: "default"`, os mockups pintam o círculo com a cor do botão (`#F3E7B8`) e `/1997` pinta `#FFFFFF` (`resolved_circular_bg`). Decisão do usuário: remover a opção "Automático" do carrossel de cores. Nenhum outro carrossel de cores do projeto tem essa opção.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O carrossel "Fundo dos ícones circulares" mostra a opção `auto` (`buttonBorder.content.ts` linha 158). O mockup resolve `auto` com a cor do botão. O backend resolve `auto` como `#FFFFFF`. Contas antigas guardam `auto`.
- **Solução Proposta:** Remova a opção `auto` do carrossel e dos textos. O valor padrão passa a ser `#FFFFFF`, igual ao que a página pública mostra hoje. Trate `auto` salvo como `#FFFFFF` na leitura (legado) e nunca o grave de novo. Isso encerra a divergência entre mockup e página pública sem mudar o backend.
- **OBS:** Decisão B de produto, tomada pelo usuário. Preserve a cor individual do botão (`button_color`) e as cores `white` e `black`. Verifique todos os carrosséis de cor do projeto. Só o fundo dos ícones circulares tem `Automático`. Pareada com o backend só se for preciso converter `auto` em dado: a leitura legada dispensa a migração.
- **Como Executar (Subtarefas):**
  - [x] **FIX-PUB-14.1.** Remova a opção `auto` de `circularIconBgOptions` e os textos `circularIconBgAuto` e `circularIconBgColors.auto` em `buttonBorder.content.ts`.
  - [x] **FIX-PUB-14.2.** Troque o padrão `'auto'` por `'#FFFFFF'` nos hooks e models de onboarding, no editor e nos mocks.
  - [x] **FIX-PUB-14.3.** Trate `auto` recebido da API como `#FFFFFF` em `resolveCircularIconStyle` e nos adaptadores do perfil público, sem gravar `auto`.
  - [x] **FIX-PUB-14.4.** Remova os ramos `isAutoCircularBg` de `useButtonBorderAndCircularController.ts` e `ButtonCustomizationView.tsx`.
  - [ ] **FIX-PUB-14.5.** Valide `/cadastro/botoes`, `/editor` e `/1997` em 390px e 1440px: o círculo tem a mesma cor nos três.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/botoes`, `/editor`, `/:username`
- **Arquivos / Pastas:** `src/content/onboarding/buttonBorder.content.ts` | `src/models/onboarding/circularIconStyle.model.ts` | `src/models/onboarding/buttonBorder.model.ts` | `src/models/profile/publicProfileMockupAdapter.model.ts` | `src/controllers/hooks/onboarding/useButtonBorderAndCircularController.ts` | `src/views/onboarding/ButtonCustomizationView.tsx` | `src/services/api/mock/`
- **Docs / Assets:** `PUB-REV` | `context/api_contracts.md` (legado `auto`) | relato do usuário

---

### [FIX-CAD-11] Salvar no Backend o @ Alterado em /cadastro/perfil

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário (conta nova sem perfil público) e do mapeamento `PUB-MAP`, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `CAD (Cadastro)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #308: https://github.com/pinhogo/assinart_v1/issues/308)

**Contexto Bruto:**

> O usuário digitou um `@` em `/cadastro`, mudou o `@` em `/cadastro/perfil` e o perfil público do `@` novo não abre. O `@` novo só existe na prévia. O backend mantém o `@` do cadastro.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `ProfileIdentityForm.tsx` chama `setPreviewUsername` ao digitar, e esse valor só alimenta a prévia (`OnboardingLayoutPreviewAndFooter.tsx` linha 89). `buildOnboardingProfilePayload` (`onboardingSyncPayload.model.ts`) e `useOnboardingAutoSave.ts` não têm `username`. Nenhum passo do onboarding chama `updateProfile({ username })`. Só `useEditorUsernameController.ts` linha 103 salva o `@`, e só no `/editor`. O usuário acredita que o novo `@` vale, abre `/<@novo>` e vê "@x está livre!". O `@` real continua o do cadastro.
- **Solução Proposta:** Troque o `<input>` do `@` em `ProfileIdentityForm.tsx` por `UsernameInput` (`src/components/ui/UsernameInput.tsx`), o mesmo componente que a landing e `/cadastro` usam, com estado `default`, `error` e `success`. Valide em tempo real: regra local com `validateSignupUsername` (`signup.model.ts`, igual ao backend: 3 a 30 caracteres, `[a-z0-9_.-]`) e checagem no servidor com `useSignupAvailabilityCheck` (300 ms, `GET /usernames/check`). Trate o `@` atual da conta como disponível, sem consultar o servidor. Ao Continuar, envie `updateProfile({ username })` com o valor normalizado, atualize `setStoredUser` e bloqueie o avanço se o `@` for inválido, estiver em uso ou o `PATCH` retornar 422.
- **OBS:** A conta já existe nesta etapa: `useSignupController.ts` (linha 111) chama `authService.register` ao enviar `/cadastro` e redireciona para `/cadastro/perfil` (linha 114) com o token salvo. Por isso o `PATCH /profile` com o `@` roda aqui, sem novo registro. Pareada com `FIX-API-10` (Regra 13): o backend hoje grava o `@` sem normalizar. Reuse `validateSignupUsername`, `useSignupAvailabilityCheck` e o tratamento de `isUnchanged` de `useEditorUsernameController.ts` (linha 53). Não copie a lógica. A landing (`useLandingController.ts`, `validateUsername`) é congelada: só leia, não importe nem altere. Ela aceita 3 a 30 caracteres sem ponto e o backend aceita ponto, então use a regra de `signup.model.ts`. Se `useProfileCustomizationController.ts` passar de 150 linhas, extraia `useProfileUsernameField.ts`. Os slugs reservados ficam em `FIX-GLB-13` e `FIX-API-08`.
- **Como Executar (Subtarefas):**
  - [x] **FIX-CAD-11.1.** Adicione ao `src/content/onboarding/` os textos de ajuda, erro e sucesso do `@` em `/cadastro/perfil` (ASD-STE100), sem texto fixo no JSX.
  - [x] **FIX-CAD-11.2.** Troque o `<input id="profile-username">` de `ProfileIdentityForm.tsx` por `UsernameInput`, com `state`, `errorMessage` e `successMessage`, e normalize ao digitar (minúsculas, sem espaços, sem `@`).
  - [x] **FIX-CAD-11.3.** Crie `useProfileUsernameField.ts` com `validateSignupUsername` e `useSignupAvailabilityCheck` (300 ms), e trate o `@` atual da conta como disponível sem consultar o servidor.
  - [x] **FIX-CAD-11.4.** Ligue o hook em `useProfileCustomizationController.ts` e exponha `usernameState`, `usernameMessage` e `isUsernameBlocking` ao `ProfileIdentityForm`.
  - [x] **FIX-CAD-11.5.** Ao Continuar, envie `updateProfile({ username })`, atualize `setStoredUser` e a prévia, trate o 422 e bloqueie o avanço se `isUsernameBlocking` for `true`.
  - [ ] **FIX-CAD-11.6.** Valide com conta nova: `@` em uso mostra erro, `@` livre mostra sucesso, o `@` atual não dá erro, `GET /<@novo>` responde 200 depois de publicar e `GET /<@antigo>` responde 404.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/perfil`
- **Arquivos / Pastas:** `src/components/onboarding/ProfileIdentityForm.tsx` | `src/components/ui/UsernameInput.tsx` (somente uso) | `src/controllers/hooks/onboarding/useProfileCustomizationController.ts` | `src/controllers/hooks/auth/useSignupAvailabilityCheck.ts` (somente uso) | `src/models/auth/signup.model.ts` (somente uso) | `src/controllers/hooks/editor/useEditorUsernameController.ts` (somente leitura) | `src/content/onboarding/`
- **Docs / Assets:** `context/api_contracts.md` | relato do usuário (conta `neymarjr1`) | `PUB-MAP` (`/tmp/pub_map/report.md`)

---

### [FIX-API-10] Normalizar e Validar o Username em PATCH /profile no Backend

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário (conta nova sem perfil público) e do mapeamento `PUB-MAP`, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #309: https://github.com/pinhogo/assinart_v1/issues/309)

**Contexto Bruto:**

> Teste do orquestrador: `PUT /api/v1/profile` com `"@TstUsr03"` retornou 200 e gravou `@TstUsr03` no banco. O registro grava `tstusr03`. Esperado: as duas rotas aplicam a mesma regra.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `RegisterRequest` aplica `strtolower`, remove `@` e valida regex de 2 a 30 caracteres. `ProfileController::update` (linha 55) valida só `sometimes|required|string|max:50|unique:artists,username`. `PublicProfileController::show` busca por `strtolower(ltrim($username, '@'))`. Um `@` gravado com maiúscula ou `@` inicial nunca é encontrado pela rota pública e fica ocupado. A regra `unique` também distingue maiúsculas no SQLite.
- **Solução Proposta:** Crie uma regra compartilhada de username (classe `Rule` ou `FormRequest`) com `trim`, `strtolower`, remoção do `@` inicial, regex de 2 a 30 caracteres e `unique` em minúsculas. Aplique em `RegisterRequest` e `ProfileController::update`. Responda 422 com a mensagem do registro.
- **OBS:** Pareada com `FIX-CAD-11` (Regra 13). Execute junto com `FIX-API-08.3` (palavras reservadas no mesmo método), para não editar `ProfileController::update` duas vezes. Contas já gravadas com `@` ou maiúscula precisam de migração de dados: liste e normalize, e trate colisões.
- **Como Executar (Subtarefas):**
  - [x] **FIX-API-10.1.** Extraia a regra de username de `RegisterRequest.php` para uma classe `Rule` compartilhada.
  - [x] **FIX-API-10.2.** Aplique a regra em `ProfileController::update` com normalização antes da validação.
  - [x] **FIX-API-10.3.** Crie migração de dados que normaliza `artists.username` existente e relata colisões sem sobrescrever.
  - [x] **FIX-API-10.4.** Adicione testes Feature PHPUnit: `PATCH /profile` com `@Maiúscula`, com 1 caractere e com nome em uso.
  - [x] **FIX-API-10.5.** Atualize `context/api_contracts.md` com a regra única de username.

**Localização & Referências:**

- **Rota / URL:** `PATCH /api/v1/profile`
- **Arquivos / Pastas:** backend: `app/Http/Controllers/Api/ProfileController.php` | `app/Http/Requests/RegisterRequest.php` | `database/migrations/` | `tests/Feature/`
- **Docs / Assets:** `context/api_contracts.md` | `FIX-API-08` | teste do orquestrador em `/tmp` (conta `tstusr01`, já removida)

---

### [FIX-API-11] Distinguir Rascunho de @ Livre na Resposta 404 de /{username}

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário (conta nova sem perfil público) e do mapeamento `PUB-MAP`, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `PUB (Página Pública)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #310: https://github.com/pinhogo/assinart_v1/issues/310)

**Contexto Bruto:**

> Mapeamento `PUB-MAP`: a página pública de uma conta em rascunho responde 404 igual a um `@` inexistente. O front mostra "@x está livre! Reivindicar @x" para um `@` que já tem dono.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `PublicProfileController::show` devolve `notFoundResponse()` com o mesmo corpo para artista inexistente e para rascunho de outro usuário. O front mapeia 404 para `not_found` em `publicProfile.service.ts` (linhas 119 a 124) e renderiza `PublicProfileUnclaimedCard`. `UsernameController::check` já devolve `taken` para esse `@` (linhas 91 a 99), então os dois endpoints se contradizem.
- **Solução Proposta:** Responda `404` com `status: "draft"` e sem dados do artista quando o `@` existe e `is_published` é `false` para o visitante. Mantenha `status: "not_found"` para `@` inexistente. Documente o campo em `context/api_contracts.md`.
- **OBS:** Pareada com `FIX-PUB-15` (Regra 13). A resposta revela que o `@` existe, mas `usernames/check` já revela isso. Não inclua nome, bio nem mídia no corpo. Não altere a regra de `FIX-API-07`: conta nova continua em rascunho.
- **Como Executar (Subtarefas):**
  - [x] **FIX-API-11.1.** Retorne `status: "draft"` no 404 de `PublicProfileController::show` quando o artista existe e não está publicado.
  - [x] **FIX-API-11.2.** Mantenha `status: "not_found"` para `@` inexistente e para palavras reservadas.
  - [x] **FIX-API-11.3.** Adicione testes Feature PHPUnit para visitante, dono logado e `@` inexistente.
  - [x] **FIX-API-11.4.** Atualize `context/api_contracts.md` com o novo valor de `status`.

**Localização & Referências:**

- **Rota / URL:** `GET /{username}`
- **Arquivos / Pastas:** backend: `app/Http/Controllers/PublicProfileController.php` | `tests/Feature/` | `context/api_contracts.md`
- **Docs / Assets:** `FIX-API-07` (`#262`) | `PUB-MAP` (`/tmp/pub_map/03_visitor_draft.png`)

---

### [FIX-PUB-15] Mostrar Estado de Rascunho na Página Pública em vez de @ Livre

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário (conta nova sem perfil público) e do mapeamento `PUB-MAP`, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `PUB (Página Pública)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #311: https://github.com/pinhogo/assinart_v1/issues/311)

**Contexto Bruto:**

> Mapeamento `PUB-MAP`: o visitante e o dono (em nova aba) veem "@x está livre! Reivindicar @x" para uma conta em rascunho. O dono logado na mesma aba vê a página sem aviso de que ela está oculta.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `PublicProfileView.tsx` (linhas 98 e 99) renderiza `PublicProfileUnclaimedCard` para qualquer `not_found`. `usePublicProfileFetch.ts` e `publicProfile.model.ts` ignoram `is_published`. A resposta 200 do dono em rascunho traz `is_published: false`, e a tela não mostra nada.
- **Solução Proposta:** Trate `status: "draft"` (de `FIX-API-11`) com um card neutro "Este perfil ainda não foi publicado", sem botão Reivindicar. Mapeie `is_published` em `publicProfile.model.ts` e mostre um banner "Rascunho: só você vê esta página" com link para `/editor` quando o dono vê o próprio rascunho.
- **OBS:** Dependente de `FIX-API-11` para o card do visitante. O banner do dono não depende e pode sair antes. Os textos vão em `src/content/profile/publicProfile.content.ts`. Não use `isPublicPage` novo: `ProfileMockup` já tem a prop `mode`. Zero Tailwind no JSX.
- **Como Executar (Subtarefas):**
  - [x] **FIX-PUB-15.1.** Adicione os textos do card de rascunho e do banner em `publicProfile.content.ts`.
  - [x] **FIX-PUB-15.2.** Mapeie `is_published` e `status: "draft"` em `publicProfile.service.ts` e `publicProfile.model.ts`.
  - [x] **FIX-PUB-15.3.** Crie `PublicProfileDraftCard.tsx` e `PublicProfileDraftBanner.tsx` com estilos em `style/`.
  - [x] **FIX-PUB-15.4.** Renderize o card no `not_found` com `draft` e o banner no 200 com `is_published: false` em `PublicProfileView.tsx` (≤ 120 linhas).
  - [ ] **FIX-PUB-15.5.** Valide com conta em rascunho: visitante, dono em nova aba e dono na mesma aba.

**Localização & Referências:**

- **Rota / URL:** `/:username`
- **Arquivos / Pastas:** `src/views/profile/PublicProfileView.tsx` | `src/components/profile/PublicProfileUnclaimedCard.tsx` | `src/services/api/publicProfile.service.ts` | `src/models/profile/publicProfile.model.ts` | `src/content/profile/publicProfile.content.ts`
- **Docs / Assets:** `FIX-API-11` | `PUB-MAP` (`/tmp/pub_map/04a_owner_newtab_draft.png`)

---

### [FIX-EDT-20] Avisar no Editor que a Conta Está em Rascunho antes de Copiar ou Abrir o Link

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário (conta nova sem perfil público) e do mapeamento `PUB-MAP`, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `EDT (Editor)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #312: https://github.com/pinhogo/assinart_v1/issues/312)

**Contexto Bruto:**

> Mapeamento `PUB-MAP`: o editor mostra o selo `RASCUNHO` só a partir de 640px. "Copiar link" mostra "Link copiado!" e "Ver página pública" abre uma URL que retorna 404 para quem não é o dono. Nenhum texto explica que a conta nasce oculta.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `EditorSections.style.ts` (linha 18) usa `hidden sm:inline-flex` no selo `Rascunho`. `useEditorAccountActions.ts` (linhas 111 a 118) copia a URL sem checar `publicationStatus`. "Ver página pública" abre nova aba (`EditorHeaderAndBioSection.tsx` linha 56 e `AccountMenu.tsx` linha 77). O token fica em `sessionStorage` desde `FIX-CAD-06.3`, e a nova aba não o herda. O dono vê "@x está livre!".
- **Solução Proposta:** Mostre o selo `Rascunho` também em 390px. Quando `publicationStatus` é `draft`, troque o toast de copiar por "Link copiado. O perfil só abre para outras pessoas depois de Publicar." e mostre uma linha de ajuda sob o botão Publicar. Em rascunho, abra "Ver página pública" na mesma aba, para manter o token.
- **OBS:** Não volte `remember` para `localStorage`: isso desfaz `FIX-CAD-06.3` (segurança). O mapeamento sugeriu `remember=true` e eu descartei. Os textos vão em `src/content/editor/`. Zero Tailwind no JSX.
- **Como Executar (Subtarefas):**
  - [x] **FIX-EDT-20.1.** Adicione os textos de rascunho em `src/content/editor/` (selo, ajuda e toast).
  - [x] **FIX-EDT-20.2.** Remova `hidden sm:inline-flex` do selo em `EditorSections.style.ts` (linha 18) e ajuste o tamanho para 390px.
  - [x] **FIX-EDT-20.3.** Escolha o toast de `copyPublicLink` por `publicationStatus` em `useEditorAccountActions.ts`.
  - [x] **FIX-EDT-20.4.** Abra "Ver página pública" na mesma aba quando `publicationStatus` for `draft` em `EditorHeaderAndBioSection.tsx` e `AccountMenu.tsx`.
  - [ ] **FIX-EDT-20.5.** Valide em 390px e 1440px com conta em rascunho e depois de Publicar.

**Localização & Referências:**

- **Rota / URL:** `/editor`
- **Arquivos / Pastas:** `src/components/editor/EditorHeaderAndBioSection.tsx` | `src/components/editor/AccountMenu.tsx` | `src/controllers/hooks/editor/useEditorAccountActions.ts` | `src/components/editor/style/EditorSections.style.ts` | `src/content/editor/`
- **Docs / Assets:** `PUB-MAP` (`/tmp/pub_map/02b_editor_mobile_draft.png`) | `context/tasks_seguranca.md` (`FIX-CAD-06`)

---

### [FIX-GLB-19] Fazer o Mock de Onboarding Gravar no Artista Autenticado e não no Seed

- **Estado no Backlog:** Concluída (`DONE` em `pinhogo/orquestracao-lote-v1` — reconciliado pelo commit `a0f3171` (`MOCK-298`) e coberto por `mockPublicProfileHandler.test.ts`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #313: https://github.com/pinhogo/assinart_v1/issues/313)

**Contexto Bruto:**

> Mapeamento `PUB-MAP`: em modo mock, os handlers de onboarding gravam no artista `artista` (publicado), e não na conta recém-criada. Isso esconde, nos testes em mock, o problema de conta em rascunho.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `mockOnboardingHandlers.ts` (linhas 31, 91 e 104) usa `mockStorage.getUser('artista')`, e o seed `'artista'` tem `is_published: true` em `mockSeedsAndTypes.ts` (linhas 79 e 80). `mockAdapter.ts` (linha 39) chama `handleMockOnboardingRoutes(method, pathname, parsedBody)` sem o `token`. A conta registrada em mock nasce em rascunho (`mockAuthHandlers.ts` linha 60), então os dois comportamentos divergem.
- **Solução Proposta:** Repasse `options.token` a `handleMockOnboardingRoutes` em `mockAdapter.ts` e use `mockStorage.resolveAuthenticatedArtist(token)` para gravar no artista do token. Mantenha o seed `artista` só sem token.
- **OBS:** Mudança só de mock, sem efeito em produção. Rode os testes `test/services` depois. Mantenha os handlers abaixo de 150 linhas.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-GLB-19.1.** Repasse `token` a `handleMockOnboardingRoutes` em `src/services/api/mockAdapter.ts`.
  - [ ] **FIX-GLB-19.2.** Troque as três leituras de `getUser('artista')` por `resolveAuthenticatedArtist(token)` em `mockOnboardingHandlers.ts`.
  - [ ] **FIX-GLB-19.3.** Atualize os testes de `test/services/` que assumem o seed.
  - [ ] **FIX-GLB-19.4.** Valide em modo mock: registrar, concluir o onboarding e abrir `/<@>` como visitante deve dar 404 até publicar.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/*` em modo mock
- **Arquivos / Pastas:** `src/services/api/mockAdapter.ts` | `src/services/api/mock/mockOnboardingHandlers.ts` | `src/services/api/mock/mockSeedsAndTypes.ts`
- **Docs / Assets:** `PUB-MAP` | `context/api_contracts.md` (seção de mocks)

---

### [FIX-GLB-20] Permitir Ponto no @ da Landing (Regra Única de Username)

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14` a pedido do usuário: "permitir `.` em todos os lugares")*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #314: https://github.com/pinhogo/assinart_v1/issues/314)

**Contexto Bruto:**

> O usuário pediu: "atualiza pra permitir `.` em todos lugares". O backend (`RegisterRequest.php`, `UsernameController.php`, `web_public.php`) e o front (`signup.model.ts` linha 66, `publicProfile.model.ts` linha 93) já aceitam `.` no `@`. A landing não aceita.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `useLandingController.ts` (linha 41) usa `/^[a-z0-9_-]+$/` e rejeita `.`. Quem digita `joao.silva` na landing recebe "Use apenas letras, números, hífen ou sublinhado." e não chega ao `/cadastro`, mesmo que o cadastro aceite o nome.
- **Solução Proposta:** Permita `.` na validação da landing e atualize a mensagem. Rejeite `.` no início, no fim e `..`, a mesma regra de `FIX-API-10` e de `FIX-CAD-11`. Reutilize `validateSignupUsername` se mantiver as mensagens e os limites de 3 a 30 caracteres.
- **OBS:** A Home é congelada. Este pedido explícito do usuário autoriza só a validação de `@` em `useLandingController.ts`. Não altere visual, textos nem `HomeMockup.tsx`. Os testes ficam para a Etapa 3.
- **Como Executar (Subtarefas):**
  - [x] **FIX-GLB-20.1.** Permita `.` na regex de `validateUsername` em `src/controllers/hooks/landing/useLandingController.ts` e atualize a mensagem de erro.
  - [x] **FIX-GLB-20.2.** Rejeite `.` no início e no fim e `..`, com mensagem própria.
  - [x] **FIX-GLB-20.3.** Liste no relatório outros pontos do front que bloqueiem `.` no `@`, sem editá-los.

**Localização & Referências:**

- **Rota / URL:** `/` (campo de `@` da landing)
- **Arquivos / Pastas:** `src/controllers/hooks/landing/useLandingController.ts` | `src/models/auth/signup.model.ts` (somente uso)
- **Docs / Assets:** `FIX-API-10` | `FIX-CAD-11` | pedido do usuário de 2026-11-14

---

### [FIX-PRF-08] Aumentar a Capa e Alinhar a Moldura do Crop e o Header à Mesma Proporção

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário sobre crop, capa, mockup do editor e laterais da página pública, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `PRF (Perfil)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #315: https://github.com/pinhogo/assinart_v1/issues/315)

**Contexto Bruto:**

> A moldura do crop da capa não tem a mesma proporção da capa real. A capa é muito achatada. O recorte tem que mostrar exatamente a parte que aparece, sem barras pretas na moldura. O modal pode ser maior. Com a capa maior, o header (avatar, nome, @ e bio) tem que descer. Mockup e página pública têm que continuar idênticos.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `getAspectFromShape` (`imageCrop.model.ts` linha 110) usa 16:9 para a capa. A capa renderizada usa `h-[176px] @min-[580px]:h-[188px]` em `ProfileMockup.style.ts` (linha 44), que dá cerca de 3,1:1 em 580px e outra proporção abaixo disso. A área do crop tem `h-[280px]` fixa e fundo `#121316` em `ImageCropModal.style.ts`, o que mostra barras pretas. `PublicProfileView` e `ProfileMockup` repetem a altura da capa em arquivos separados.
- **Solução Proposta:** Crie uma constante única de proporção da capa em um model (`COVER_ASPECT_RATIO`, proposta 5:2) e use-a no crop, no mockup e na página pública. A capa usa `aspect-ratio` em vez de altura fixa. O crop usa a mesma constante e a moldura preenche a área sem barras. Aumente o modal e a área do crop. Aumente o recuo do header (`-mt-12` e `@min-[580px]:-mt-14`) na mesma medida nos dois lados.
- **OBS:** Pareada com `FIX-PRF-09` (mesmo modal) e `FIX-PUB-16` (mesmo `PublicProfileView`). Não altere `tokens.ts`, `src/assets/**` nem `HomeMockup.tsx`. A capa já salva continua válida: ela é exibida com `object-cover`. Registre a proporção escolhida em `context/guide_components_and_pages.md`. Meça no DOM a proporção da moldura do crop e da capa nos dois lados.
- **Como Executar (Subtarefas):**
  - [x] **FIX-PRF-08.1.** Defina `COVER_ASPECT_RATIO` em `src/models/onboarding/imageCrop.model.ts` e use-a em `getAspectFromShape` para `target === 'cover'`.
  - [x] **FIX-PRF-08.2.** Troque a altura fixa da capa por `aspect-ratio` em `ProfileMockup.style.ts` e nos estilos da capa da página pública, com o mesmo valor.
  - [x] **FIX-PRF-08.3.** Amplie o modal (`cropModalDialog`) e a área do crop em `ImageCropModal.style.ts` e faça a moldura preencher a área sem barras pretas.
  - [x] **FIX-PRF-08.4.** Aumente o recuo do header do perfil no mockup e na página pública com a mesma regra.
  - [x] **FIX-PRF-08.5.** Meça no DOM, em 390px e 1440px, a proporção da moldura, da capa no mockup e da capa em `/1997`. Os três valores têm que ser iguais.
  - [x] **FIX-PRF-08.6.** Permita zoom out em todo recorte (avatar, capa e fundo). O recorte abre no zoom 1. O zoom mínimo faz a imagem inteira caber na moldura. A área vazia recebe a própria imagem desfocada, igual no modal e no arquivo (`cropZoomBounds.model.ts`, `useCropViewport.ts`, `cropImage.util.ts`). Pedido do usuário em `2026-11-15`.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/perfil`, `/editor`, `/:username`
- **Arquivos / Pastas:** `src/models/onboarding/imageCrop.model.ts` | `src/components/onboarding/ImageCropModal.tsx` | `src/components/onboarding/style/ImageCropModal.style.ts` | `src/components/mockups/ProfileMockup/ProfileCover.tsx` | `src/components/mockups/style/ProfileMockup.style.ts` | `src/views/profile/PublicProfileView.tsx` | `src/views/profile/style/`
- **Docs / Assets:** relato do usuário | `FIX-PRF-03` | `FIX-PRF-07` | `context/guide_components_and_pages.md`

---

### [FIX-PRF-09] Usar no Crop do Plano de Fundo a Mesma Proporção do Plano de Fundo Renderizado

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário sobre crop, capa, mockup do editor e laterais da página pública, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `PRF (Perfil)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #316: https://github.com/pinhogo/assinart_v1/issues/316)

**Contexto Bruto:**

> Quando o fundo do perfil (não a capa) é uma foto personalizada, o crop precisa ter a mesma proporção do plano de fundo renderizado. O recorte tem que ser o que aparece de fato no mockup e na página pública.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `getAspectFromShape` usa 9:16 para `wallpaper`. O mockup e a página pública aplicam o fundo em caixas de tamanhos diferentes (`wallpaper.model.ts`), com `background-size: cover`. O enquadramento do usuário não é o que aparece.
- **Solução Proposta:** Defina `WALLPAPER_ASPECT_RATIO` em um model e renderize o fundo no mockup e na página pública em uma caixa com essa proporção, ancorada no topo da coluna do perfil. O crop usa a mesma constante. Documente como a página se comporta quando é mais alta ou mais baixa que a caixa.
- **OBS:** Depende de `FIX-PRF-08` (mesmo modal e mesma estrutura de constantes). Execute depois dele. `FIX-PUB-16` usa o mesmo fundo para as laterais, então alinhe a leitura do fundo em `wallpaper.model.ts` com esse item. Não altere `tokens.ts`.
- **Como Executar (Subtarefas):**
  - [x] **FIX-PRF-09.1.** Defina `WALLPAPER_ASPECT_RATIO` em `imageCrop.model.ts` e use-a no crop com `target === 'wallpaper'`.
  - [x] **FIX-PRF-09.2.** Ajuste `wallpaper.model.ts` para o fundo usar a mesma proporção no mockup e na página pública.
  - [x] **FIX-PRF-09.3.** Teste com foto retrato, foto paisagem e foto quadrada: o enquadramento do crop é o que aparece nos dois lados.
  - [x] **FIX-PRF-09.4.** Meça no DOM a proporção da moldura do crop e do fundo renderizado.
  - [x] **FIX-PRF-09.6.** Faça a foto de fundo cobrir a tela inteira. Na página pública a foto fica presa à janela (`sticky`, altura da tela) com `cover`. No mockup a foto cobre a moldura. Antes, a caixa 9:16 deixava uma faixa sem foto em telas mais altas que 9:16 (por exemplo 390x844). Pedido do usuário em `2026-11-15`.
  - [ ] **FIX-PRF-09.5.** Valide `/cadastro/fundo`, `/editor` e `/:username` em 390px e 1440px, em modo claro e escuro.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/fundo`, `/editor`, `/:username`
- **Arquivos / Pastas:** `src/models/onboarding/imageCrop.model.ts` | `src/models/onboarding/wallpaper.model.ts` | `src/components/onboarding/WallpaperAndPresetsControl.tsx` | `src/components/onboarding/ImageCropModal.tsx`
- **Docs / Assets:** relato do usuário | `FIX-PRF-08` | `FIX-PRF-03`

---

### [FIX-EDT-21] Aumentar em 45% o Celular do Mockup no Editor em Desktop

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário sobre crop, capa, mockup do editor e laterais da página pública, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `EDT (Editor)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #317: https://github.com/pinhogo/assinart_v1/issues/317)

**Contexto Bruto:**

> No editor em desktop, o celular do mockup é pequeno. Aumente o celular em cerca de 45%, como o zoom do navegador. Os itens crescem junto. O efeito tem que vir por padrão, sem zoom ou rolagem do usuário. O mobile está bom e não muda. Não pode estourar nem cortar em telas de cerca de 1366px.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O mockup do editor usa `md:max-w-[288px] lg:max-w-[306px]` e `lg:h-[clamp(530px,calc((100dvh-50px)*0.9),660px)]` em `ProfileMockup.style.ts`. O `:root` aplica `zoom: 0.9` a partir de 768px (`src/index.css`). A escala interna vem de `useMockupScale`, que depende da largura do contêiner.
- **Solução Proposta:** Aumente a largura e a altura da moldura do celular em 1,45 vezes no desktop. Limite a escala pela altura e pela largura disponíveis, para não estourar em 1366x768. Registre a escala efetiva em 1366x768 e em 1920x1080. Não mude nada abaixo do breakpoint `lg`.
- **OBS:** Pareada com `FIX-CAD-12` (mesma moldura). Dependente visual de `FIX-PRF-08`: a capa maior muda a altura do conteúdo. Não use `zoom` CSS novo: use as variáveis de tamanho da moldura. O mockup de `/cadastro/links` não pode mudar o tamanho de forma não pedida.
- **Como Executar (Subtarefas):**
  - [x] **FIX-EDT-21.1.** Leia as classes de tamanho da moldura em `ProfileMockup.style.ts` e em `EditorView.style.ts`.
  - [x] **FIX-EDT-21.2.** Defina o fator 1,45 em um model (`mockupFrameSize.model.ts`) e a regra de limite por altura e largura disponíveis.
  - [x] **FIX-EDT-21.3.** Aplique o fator só a partir do breakpoint `lg` no `/editor`.
  - [x] **FIX-EDT-21.4.** Meça em 1366x768, 1440x900 e 1920x1080 que o celular não corta nem gera barra de rolagem horizontal.
  - [x] **FIX-EDT-21.5.** Confirme que 390px e 768px ficam iguais ao estado anterior.
  - [x] **FIX-EDT-21.6.** Use a mesma regra no `/editor` e no `/cadastro/*` (pedido do usuário em `2026-11-15`): fator 1,35, topo da moldura em 197px CSS nas duas rotas e o balão de dica por cima do celular, sem espaço reservado. Medido em 1440x900: moldura 372x676 nas duas rotas, topo 177 e 178. Em 1366x768: 314x544 nas duas.

**Localização & Referências:**

- **Rota / URL:** `/editor`
- **Arquivos / Pastas:** `src/components/mockups/style/ProfileMockup.style.ts` | `src/views/editor/style/EditorView.style.ts` | `src/controllers/hooks/mockup/useMockupScale.ts` | `src/models/profile/mockupScale.model.ts`
- **Docs / Assets:** relato do usuário | `PUB-15` | `FIX-CAD-12`

---

### [FIX-CAD-12] Travar a Moldura do Mockup em 580px em /cadastro/links nas Telas Médias

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário sobre crop, capa, mockup do editor e laterais da página pública, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `CAD (Cadastro)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #318: https://github.com/pinhogo/assinart_v1/issues/318)

**Contexto Bruto:**

> Revisão `PUB-REV2`: em `/cadastro/links` com 768px a moldura do mockup fica com 753px, e o conteúdo fica limitado a 580px. Sobram faixas laterais vazias. Decisão do usuário: travar a moldura em 580px, igual ao limite do conteúdo, sem faixas vazias.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `ScaledProfileViewport` limita o conteúdo a `PUBLIC_PROFILE_MAX_WIDTH` (580px), mas a moldura do `ProfileMockup` em `<lg` usa `w-full max-w-full`. A moldura passa de 580px e deixa as laterais vazias.
- **Solução Proposta:** Limite a largura da moldura a 580px e centralize-a. Reaproveite `PUBLIC_PROFILE_MAX_WIDTH`. Não mude o desktop (a partir de `lg`) nem 390px.
- **OBS:** Pareada com `FIX-EDT-21` (mesma moldura). A captura da falha está em `/tmp/pub_review/pub15b_cadastro_768.png`. Não altere `HomeMockup.tsx`.
- **Como Executar (Subtarefas):**
  - [x] **FIX-CAD-12.1.** Aplique `max-w` com `PUBLIC_PROFILE_MAX_WIDTH` e `mx-auto` na moldura `<lg` em `ProfileMockup.style.ts`.
  - [x] **FIX-CAD-12.2.** Confirme que a moldura e o conteúdo têm a mesma largura em 600px, 768px e 1023px.
  - [x] **FIX-CAD-12.3.** Confirme que 390px e a partir de `lg` ficam iguais ao estado anterior.
  - [x] **FIX-CAD-12.4.** Repita a medição em `/editor` na aba Prévia (`<lg`).

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`
- **Arquivos / Pastas:** `src/components/mockups/style/ProfileMockup.style.ts` | `src/components/mockups/ScaledProfileViewport.tsx` | `src/models/profile/mockupScale.model.ts`
- **Docs / Assets:** `PUB-REV2` (`/tmp/pub_review/pub15b_cadastro_768.png`) | `FIX-EDT-21`

---

### [FIX-PUB-16] Diferenciar o Fundo das Laterais da Coluna do Perfil na Página Pública

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir do relato do usuário sobre crop, capa, mockup do editor e laterais da página pública, 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `PUB (Página Pública)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #319: https://github.com/pinhogo/assinart_v1/issues/319)

**Contexto Bruto:**

> Hoje as laterais direita e esquerda têm a mesma cor do fundo do perfil. No Linktree, a cor atrás da coluna central é diferente das laterais (a coluna é um pouco mais clara, no mesmo tom). Com foto de fundo, as laterais devem ter o mesmo efeito (cor de maior ocorrência da foto, ou a foto com blur forte e camada escura). O perfil fica por cima, nítido.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `PublicProfileView.tsx` aplica o mesmo fundo (`backgroundColor`, gradiente ou foto) no wrapper `[data-public-profile="true"]`. A coluna e as laterais usam a mesma cor (achado `F28` em `context/tasks_link_publico.md`).
- **Solução Proposta:** Crie uma camada de laterais atrás da coluna. Cor sólida e gradiente: derive um tom mais escuro do mesmo fundo com `color-mix`, em CSS puro. Foto: use a própria foto com `filter: blur` forte e uma camada escura translúcida. Não extraia a cor com canvas: a leitura de pixels depende de CORS e falha com imagens da API. A coluna fica em `z-index` maior e mantém o fundo atual, igual ao do mockup.
- **OBS:** Escolha estável: CSS puro para os três casos, sem canvas. Pareada com `FIX-PRF-09` (leitura do fundo). Só a página pública muda: o mockup do celular não tem laterais. Cubra o modo escuro e os temas existentes. Em fundo quase preto, registre como a separação aparece.
- **Como Executar (Subtarefas):**
  - [x] **FIX-PUB-16.1.** Crie um model `publicProfileSides.model.ts` que resolve o estilo das laterais por tipo de fundo (sólido, gradiente, foto).
  - [x] **FIX-PUB-16.2.** Renderize a camada das laterais atrás da coluna em `PublicProfileView.tsx`, com estilos em `style/`.
  - [x] **FIX-PUB-16.3.** Garanta que a coluna mantém o fundo atual e fica acima das laterais.
  - [x] **FIX-PUB-16.4.** Valide com cor sólida, gradiente e foto, em 1440px, 1920px e 390px (sem laterais), em modo claro e escuro.
  - [x] **FIX-PUB-16.5.** Confirme que `/1997` em 390px não muda e que o mockup continua igual à coluna.

**Localização & Referências:**

- **Rota / URL:** `/:username`
- **Arquivos / Pastas:** `src/views/profile/PublicProfileView.tsx` | `src/views/profile/style/` | `src/models/profile/` | `src/models/onboarding/wallpaper.model.ts`
- **Docs / Assets:** relato do usuário | `context/tasks_link_publico.md` (F28) | `FIX-PRF-09`

---

### [FIX-EDT-22] Impedir que o Cabeçalho do /editor Corte o Tema e o Menu da Conta em 768px

- **Estado no Backlog:** Em Tratativa *(Promovida para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` em `2026-11-14`; triagem Fluxo A -> Fluxo B a partir da validação em tela `PUB-REV3` (768px no /editor), 2026-11-14)*
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `EDT (Editor)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #320: https://github.com/pinhogo/assinart_v1/issues/320)

**Contexto Bruto:**

> Validação `PUB-REV3`: em `/editor` publicado com 768px e 800px, o botão de tema e o menu da conta ficam fora da tela. O `scrollWidth` é 851px e o `overflow-x` está oculto, então não há rolagem para alcançá-los. Em 900px e 1024px o cabeçalho cabe. Captura: `/tmp/pub_rev3/11_editor_768.png`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `editorStatusText` em `EditorSections.style.ts` (linha 25) usa `hidden md:inline-flex` e mostra "Todas as alterações salvas" a partir de 768px. Somado ao selo, a Copiar link, a Publicado e a Ver página pública, o cabeçalho passa de 768px. O defeito vem de antes do `FIX-EDT-20`: as classes de `sm` e `md` não mudaram nele.
- **Solução Proposta:** Mostre o texto de salvamento só a partir de `lg`. Aplique a mesma mudança na cópia legada em `EditorView.style.ts` (linha 38). Mantenha o texto visível em 1024px ou mais e o cabeçalho de 390px sem mudança.
- **OBS:** Pareada com `FIX-GLB-10` (contenção do cabeçalho). Sem o menu da conta visível, o usuário não consegue sair da conta em 768px. Verifique 768px, 800px, 900px e 1024px.
- **Como Executar (Subtarefas):**
  - [x] **FIX-EDT-22.1.** Troque `hidden md:inline-flex` por `hidden lg:inline-flex` em `editorStatusText` de `EditorSections.style.ts` e de `EditorView.style.ts`.
  - [x] **FIX-EDT-22.2.** Confirme em 768px, 800px, 900px e 1024px que `scrollWidth` é igual a `clientWidth`.
  - [x] **FIX-EDT-22.3.** Confirme que o botão de tema e o menu da conta ficam visíveis e clicáveis em 768px.
  - [x] **FIX-EDT-22.4.** Confirme que 390px e 1440px ficam iguais ao estado anterior.

**Localização & Referências:**

- **Rota / URL:** `/editor`
- **Arquivos / Pastas:** `src/components/editor/style/EditorSections.style.ts` | `src/views/editor/style/EditorView.style.ts`
- **Docs / Assets:** `PUB-REV3` (`/tmp/pub_rev3/11_editor_768.png`) | `FIX-GLB-10` | `FIX-EDT-20`

---

### [FIX-PRF-10] Extrair Textos Fixos de ProfileCover.tsx para Arquivo de Conteúdo

- **Estado no Backlog:** Concluída (`DONE` na branch `pinhogo/orquestracao-lote-v1` — lote Orca `run_6d1ef873450a`, validado via `/orca-computer-use` em `/cadastro/perfil` e `ProfileMockup.test.tsx`)
- **GitHub Project (#2):** Status `DONE` | Domínio `PRF (Perfil)` | Etapa `Etapa 3 (Testes/Docs)` (Issue #322: https://github.com/pinhogo/assinart_v1/issues/322)

**Contexto Bruto:**

> Revisão da Etapa 2 (Regra 3 — Zero Strings Fixas no JSX): `src/components/mockups/ProfileMockup/ProfileCover.tsx` tem "Alterar capa", `aria-label="Alterar imagem de capa"` e `alt="Capa do perfil"` escritos direto no JSX. Isso já era assim antes do lote `PUB-13..16`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `ProfileCover.tsx` (linhas 45, 57, 59 e 60) declara strings literais diretamente no JSX (`"Capa do perfil"`, `"Alterar imagem de capa"`, `"+"` e `"Alterar capa"`), em desacordo com a Regra 3 de `context/rules.md`.
- **Solução Proposta:** Centralize os textos da capa em `linkSelectionContent.preview` (`src/content/onboarding/linkSelection.content.ts`) e passe-os via props de `ProfileMockup.tsx` para `ProfileCover.tsx`, eliminando todas as strings literais do JSX.
- **OBS:** Mantém as fronteiras da Regra 2 (Component recebe `content` via props) e preserva os textos e `aria-label` já validados nos testes automatizados. Não edite `ProfileCover.tsx` até a aprovação do card.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-PRF-10.1.** Adicione `coverAlt`, `coverEditButtonLabel`, `coverEditAriaLabel` e `coverEditPlusSymbol` em `linkSelectionContent.preview` (`src/content/onboarding/linkSelection.content.ts`).
  - [ ] **FIX-PRF-10.2.** Passe os textos de capa via props de `ProfileMockup.tsx` para `ProfileCover.tsx` e remova as strings fixas do JSX.
  - [ ] **FIX-PRF-10.3.** Confirme que os testes de `ProfileMockup` continuam encontrando `Alterar imagem de capa` e `Capa do perfil`.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/perfil`, `/editor`, `/:username`
- **Arquivos / Pastas:** `src/components/mockups/ProfileMockup/ProfileCover.tsx` | `src/components/mockups/ProfileMockup/ProfileMockup.tsx` | `src/content/onboarding/linkSelection.content.ts`
- **Docs / Assets:** `context/rules.md` (Regra 3) | `context/tasks_mockup_perfil.md` (item 2.4)

---

### [API-10] Implementar Reordenação Transacional com Troca de Seção e Normalização em `POST /buttons/reorder`

- **Estado no Backlog:** Em Tratativa *(triagem Fluxo A -> Fluxo B a partir da Issue `#299` — promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_backend_api.md`](tasks_backend_api.md) e [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `API/LNK` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/299)

**Contexto Bruto:**

> Alterar/criar um único endpoint de reordenação (POST /api/v1/buttons/reorder, ajustado), chamado uma vez por drop, em TRANSAÇÃO: recebe a lista completa ordenada com display_mode de cada item; atualiza display_mode e normaliza circulares (0..K-1) depois retangulares (K..M-1) corrigindo colisões pré-existentes; retorna lista atualizada; incluir testes (topo, meio, fim, trocar de seção, colisão pré-existente).

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `ManagesButtonReorderAndImage::reorder` renumera apenas os `ordered_ids` recebidos sem alterar `display_mode`. A tabela `buttons` não possui índice único em `(artist_id, order_index)` e permite colisões. Reordenar parte da lista pode gerar `order_index` duplicados ou deixar botões retangulares na faixa dos circulares.
- **Solução Proposta:** Atualize `POST /api/v1/buttons/reorder` para receber `{ items: [{ id, display_mode }] }` na ordem final em `DB::transaction` com `lockForUpdate()`. Valide posse de todos os IDs, limite máximo de `5` circulares e regra de ícone circular quando `icon_variant === 'none'`. Grave `circular` em `0..K-1` e `button` em `K..M-1` e retorne a lista completa ordenada.
- **OBS:** Repo `/home/pinho/Projetos/assinart_v1_backend`. Contrato fechado pela decisão `3B`/`4A`: lista completa declarativa garante idempotência em fila e corrige colisões pré-existentes. Ao promover item com `icon_variant === 'none'` para `circular`, aplicar `show_icon_background = true` e `icon_variant = 'default'`.
- **Como Executar (Subtarefas):**
  - [ ] **API-10.1.** Atualize `ReorderButtonsRequest.php` exigindo `items` (`id` inteiro distinto e `display_mode` `button|circular`).
  - [ ] **API-10.2.** Extraia normalizador único (`circular` `0..K-1` acima de `button` `K..M-1`) e limite de `5` circulares em service backend.
  - [ ] **API-10.3.** Execute validação de posse, troca de `display_mode` e renumeração integral em `DB::transaction` com `lockForUpdate()`.
  - [ ] **API-10.4.** Aplique regra automática de ícone circular ao promover botão retangular com `icon_variant === 'none'` durante reordenação.
  - [ ] **API-10.5.** Retorne `data.buttons` ordenada (`0..M-1`) para conferência otimista no frontend.
  - [ ] **API-10.6.** Adicione testes PHPUnit cobrindo topo, meio, fim, troca de seção, colisão pré-existente, teto circular e `403`.

**Localização & Referências:**

- **Rota / URL:** `POST /api/v1/buttons/reorder`
- **Arquivos / Pastas:** `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/Concerns/ManagesButtonReorderAndImage.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/ButtonController.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Requests/ReorderButtonsRequest.php` | `/home/pinho/Projetos/assinart_v1_backend/routes/api_buttons.php` | `/home/pinho/Projetos/assinart_v1_backend/tests/Feature/ButtonTest.php` | `context/api_contracts.md`
- **Docs / Assets:** `context/api_contracts.md` | Issue #299

---

### [API-11] Elevar Limite Global para 30 Botões e Proteger Sequência no Onboarding e Update

- **Estado no Backlog:** Em Tratativa *(triagem Fluxo A -> Fluxo B a partir da Issue `#299` — promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_backend_api.md`](tasks_backend_api.md) e [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `API/LNK` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/299)

**Contexto Bruto:**

> Limite de 30 botões por artista (máx. 5 circulares): validar em POST /buttons, PUT /onboarding/links e no que mais criar botões; erro claro ao exceder. Em PATCH /buttons/{id}, deixar de aceitar order_index; PUT /onboarding/links normaliza sempre. Incluir teste limite de 30.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `ButtonController::store`, `OnboardingController::saveLinks` e `OnboardingLinksRequest.php` bloqueiam criação acima de `10` botões. Além disso, `UpdateButtonRequest` aceita `order_index` bruto e `saveLinks` grava índices sem normalizar circulares antes de retangulares nem limitar `5` circulares.
- **Solução Proposta:** Centralize `MAX_BUTTONS_PER_ARTIST = 30` e `MAX_CIRCULAR_BUTTONS = 5` no backend com erro `422` claro. Remova/ignore `order_index` em `UpdateButtonRequest.php` e `ButtonController::update`, mantendo normalização quando `display_mode` mudar via `PATCH`. Normalize sempre `circular` (`0..K-1`) e `button` (`K..M-1`) na transação de `PUT /onboarding/links`.
- **OBS:** Depende do normalizador único de `API-10`. Decisões `1A` e `6A` fechadas: teto global `30` + máximo `5` circulares + `PATCH /buttons/{id}` sem mutação direta de `order_index`.
- **Como Executar (Subtarefas):**
  - [ ] **API-11.1.** Centralize `MAX_BUTTONS_PER_ARTIST = 30` e `MAX_CIRCULAR_BUTTONS = 5` em `ButtonController.php`, `OnboardingController.php` e requests.
  - [ ] **API-11.2.** Retorne mensagem de erro `422` clara ao exceder `30` botões totais ou `5` circulares.
  - [ ] **API-11.3.** Remova `order_index` de `UpdateButtonRequest.php` e normalize a sequência quando `display_mode` mudar em `ButtonController::update`.
  - [ ] **API-11.4.** Normalize `order_index` (`circular` `0..K-1`, `button` `K..M-1`) na transação de `PUT /api/v1/onboarding/links`.
  - [ ] **API-11.5.** Atualize `ButtonTest.php` e `OnboardingTest.php` cobrindo limite de `30`, bloqueio do `31º`, `5` circulares e `UpdateButtonRequest`.
  - [ ] **API-11.6.** Atualize `context/api_contracts.md` refletindo limite `30` global, teto `5` circulares e normalização de sequência.

**Localização & Referências:**

- **Rota / URL:** `POST /api/v1/buttons`, `PATCH /api/v1/buttons/{id}`, `PUT /api/v1/onboarding/links`
- **Arquivos / Pastas:** `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/ButtonController.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/OnboardingController.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Requests/OnboardingLinksRequest.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Requests/UpdateButtonRequest.php` | `/home/pinho/Projetos/assinart_v1_backend/tests/Feature/ButtonTest.php` | `/home/pinho/Projetos/assinart_v1_backend/tests/Feature/OnboardingTest.php` | `context/api_contracts.md`
- **Docs / Assets:** `context/api_contracts.md` | Issue #299

---

### [LNK-11] Elevar Limite Global de Links para 30 nos Modelos, Contadores, Copy e Mocks

- **Estado no Backlog:** Em Tratativa *(triagem Fluxo A -> Fluxo B a partir da Issue `#299` — promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `LNK (Links)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/299)

**Contexto Bruto:**

> Limite de links: 30 (contador "x de 30", "máx. 30" nas redes selecionadas). Remover o limite de 5 dos retangulares e manter o limite de 5 apenas nos botões circulares.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Constantes `MAX_TOTAL_LINKS`, `MAX_INITIAL_PLATFORMS`, `MAX_ALLOWED_LINKS`, copy em `linkSelection.content.ts` e mocks limitam links em `10`. Além disso, `MAX_LINKS_PER_MODE = 5` e `MAX_PER_DISPLAY_MODE = 5` bloqueiam links `button` após `5` e auto-convertem novos links para `circular`.
- **Solução Proposta:** Eleve o limite global para `30` nos models, componentes, proxy `1..30`, subtítulo e mocks. Remova a restrição de `5` para `button` (`retangulares` até `30` globais), mantendo `MAX_CIRCULAR_LINKS = 5` exclusivo para `Botões circulares` no menu `⋮` e nas operações de adição/conversão.
- **OBS:** Pareada à `API-11` (Regra 13) seguindo decisão `1A`: `30` links totais com máximo de `5` apenas nos circulares. Coordenação recomendada com `#279` (`FIX-LNK-03`) em `useLinkSelectionViewController.ts`.
- **Como Executar (Subtarefas):**
  - [ ] **LNK-11.1.** Eleve limites globais para `30` em `linkSelection.model.ts` e `onboardingLinksOperations.model.ts`.
  - [ ] **LNK-11.2.** Remova o teto de `5` para `button` e mantenha limite `5` apenas para `circular` nos models.
  - [ ] **LNK-11.3.** Atualize subtítulo, alertas, `x de 30`, `máx. 30` e proxy `1..30` em `src/content/onboarding/linkSelection.content.ts`.
  - [ ] **LNK-11.4.** Propague teto `30` em `useLinkSelectionViewController.ts`, `SelectedNetworksHeader.tsx`, `LinkSelectionCardContent.tsx` e `ActiveLinksList.tsx`.
  - [ ] **LNK-11.5.** Atualize validações de `30` globais e `5` circulares em `mockOnboardingHandlers.ts` e `mockStorageStore.ts`.
  - [ ] **LNK-11.6.** Atualize e execute testes Vitest cobrindo `30` links, bloqueio do `31º` e teto `5` circular.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`
- **Arquivos / Pastas:** `src/models/onboarding/linkSelection.model.ts` | `src/models/onboarding/onboardingLinksOperations.model.ts` | `src/models/onboarding/buildPhonePreviewLinks.model.ts` | `src/models/onboarding/linkActionMenu.model.ts` | `src/content/onboarding/linkSelection.content.ts` | `src/controllers/hooks/onboarding/useLinkSelectionViewController.ts` | `src/controllers/hooks/onboarding/useLinkSelectionController.ts` | `src/components/onboarding/SelectedNetworksHeader.tsx` | `src/components/onboarding/LinkSelectionCardContent.tsx` | `src/components/onboarding/ActiveLinksList.tsx` | `src/services/api/mock/mockOnboardingHandlers.ts` | `src/services/api/mock/mockStorageStore.ts` | `test/components/onboarding/LinkDisplayModeLimits.test.tsx` | `test/models/onboarding/onboardingLinksOperations.model.test.ts` | `test/services/api/mock/mockStorageStore.test.ts`
- **Docs / Assets:** `context/api_contracts.md` | Issue #299

---

### [LNK-12] Separar Links Ativos em Seções Circulares e Retangulares Alinhadas ao Mockup

- **Estado no Backlog:** Em Tratativa *(triagem Fluxo A -> Fluxo B a partir da Issue `#299` — promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `LNK (Links)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/299)

**Contexto Bruto:**

> Separar "Seus links ativos" em duas seções: "Botões circulares" e "Botões retangulares". Circulares sempre acima. Ordem visual: circulares com menor order_index ficam mais à esquerda; retangulares com menor order_index ficam mais ao topo. Seção vazia sempre visível como dropzone tracejada e cards verticais com botões ▲/▼ mantidos dentro da seção.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `ActiveLinksList.tsx` renderiza lista única sem distinguir `displayMode`, pois `ActiveLinkItemData` nem recebe esse campo. A mistura visual oculta que `ProfileMockup` separa circulares na horizontal acima da pilha retangular.
- **Solução Proposta:** Adicione `displayMode` em `ActiveLinkItemData` e extraia subcomponente de seção respeitando `<= 120` linhas pela Regra 5. Renderize `Botões circulares` sempre acima de `Botões retangulares` com cards verticais, botões `▲`/`▼` restritos à própria seção e dropzone tracejada sempre visível quando vazia. Garanta ordem esquerda-direita (circulares) e topo-base (retangulares) no `ProfileMockup`.
- **OBS:** `ActiveLinksList.tsx` (`118` linhas) e `EditableLinkCard.tsx` (`108` linhas) estão perto do teto da Regra 5. Decisão `5` fechada: lista vertical nas duas seções, seção vazia tracejada visível e `▲`/`▼` preservados por acessibilidade. Coordenar com `#278` (`LNK-10`).
- **Como Executar (Subtarefas):**
  - [ ] **LNK-12.1.** Adicione `displayMode` em `ActiveLinkItemData` dentro de `onboardingControllersHelpers.model.ts` e repasse em `useLinkSelectionViewController.ts`.
  - [ ] **LNK-12.2.** Cadastre títulos das seções e textos de dropzone vazia em `src/content/onboarding/linkSelection.content.ts` (Regra 3).
  - [ ] **LNK-12.3.** Extraia subcomponente de seção com dropzone tracejada vazia e circulares sempre acima de retangulares em `ActiveLinksList.tsx`.
  - [ ] **LNK-12.4.** Restrinja `▲`/`▼` ao escopo interno de cada seção e alinhe esquerda-direita/topo-base em `buildPhonePreviewLinks.model.ts`.
  - [ ] **LNK-12.5.** Atualize `ActiveLinksList.test.tsx` e `LinkSelectionView.test.tsx` validando seções, dropzone vazia e 390px/1440px.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`
- **Arquivos / Pastas:** `src/models/onboarding/onboardingControllersHelpers.model.ts` | `src/models/onboarding/buildPhonePreviewLinks.model.ts` | `src/controllers/hooks/onboarding/useLinkSelectionViewController.ts` | `src/components/onboarding/ActiveLinksList.tsx` | `src/components/onboarding/EditableLinkCard.tsx` | `src/content/onboarding/linkSelection.content.ts` | `test/components/onboarding/ActiveLinksList.test.tsx` | `test/views/onboarding/LinkSelectionView.test.tsx`
- **Docs / Assets:** `context/context_components.md` | Issue #299

---

### [LNK-13] Implementar Drag and Drop com dnd-kit entre Seções e Atualização Otimista

- **Estado no Backlog:** Em Tratativa *(triagem Fluxo A -> Fluxo B a partir da Issue `#299` — promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `LNK (Links)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/299)

**Contexto Bruto:**

> Drag and drop funcional com dnd-kit (@dnd-kit/core + @dnd-kit/sortable), com suporte a toque. Arrastar dentro da seção reordena; arrastar para a outra seção muda o display_mode do botão (circular <-> button). O onboarding e o mockup do celular atualizam NA HORA ao soltar o botão (atualização otimista), mostrando imediatamente a conversão de icon_variant: 'none' para o ícone padrão ao virar circular.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Reordenação atual usa apenas botões `▲`/`▼` em `EditableLinkCard.tsx` sem arraste por ponteiro ou toque e sem mover entre modos visuais. `package.json` não possui `@dnd-kit/core` nem `@dnd-kit/sortable`.
- **Solução Proposta:** Instale `@dnd-kit/core`, `@dnd-kit/sortable` e `@dnd-kit/utilities` checando peer dependencies com React 19 (avisar usuário antes se exigir `--force` ou `--legacy-peer-deps`). Envolva as duas seções com `DndContext`, `PointerSensor`, `TouchSensor` e `KeyboardSensor`, bloqueando drop na seção circular se já houver `5` circulares e convertendo imediatamente `icon_variant === 'none'` na tela e no mockup.
- **OBS:** Depende de `LNK-12`. Regra 6.5 (`7`): nunca executar `--force` ou `--legacy-peer-deps` sem confirmação explícita do usuário caso haja conflito de peer dependency. Manter `▲`/`▼` no card.
- **Como Executar (Subtarefas):**
  - [ ] **LNK-13.1.** Instale `@dnd-kit/core`, `@dnd-kit/sortable` e `@dnd-kit/utilities` parando para avisar se houver conflito React 19.
  - [ ] **LNK-13.2.** Configure `DndContext`, `SortableContext` e sensores de ponteiro, toque e teclado sobre as duas seções.
  - [ ] **LNK-13.3.** Crie wrapper ordenável preservando `EditableLinkCard.tsx` com até 120 linhas, alça tátil e `▲`/`▼`.
  - [ ] **LNK-13.4.** Implemente handler otimista intra-seção e cross-seção bloqueando entrada na seção circular quando atingir `5` itens.
  - [ ] **LNK-13.5.** Converta imediatamente `icon_variant: 'none'` para ícone padrão visível e `show_icon_background: true` ao virar circular.
  - [ ] **LNK-13.6.** Adicione testes de drag intra-seção, cross-seção, bloqueio do 6º circular e conversão visual imediata.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`
- **Arquivos / Pastas:** `package.json` | `src/components/onboarding/ActiveLinksList.tsx` | `src/components/onboarding/EditableLinkCard.tsx` | `src/models/onboarding/onboardingLinksOperations.model.ts` | `src/models/onboarding/linkActionMenu.model.ts` | `src/controllers/hooks/onboarding/useOnboardingLinksState.ts` | `src/controllers/hooks/onboarding/useLinkSelectionViewController.ts` | `test/components/onboarding/ActiveLinksList.test.tsx` | `test/models/onboarding/onboardingLinksOperations.model.test.ts`
- **Docs / Assets:** `context/context_components.md` | Issue #299

---

### [LNK-14] Enfileirar Reordenação na API e Mock com Rollback e Toast Vermelho de Erro

- **Estado no Backlog:** Em Tratativa *(triagem Fluxo A -> Fluxo B a partir da Issue `#299` — promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `LNK/EDT (Links e Editor)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/299)

**Contexto Bruto:**

> No /editor, POST /api/v1/buttons/reorder roda por baixo dos panos (1 chamada por drop com lista { items: [{ id, display_mode }] }); no /cadastro/links, PUT /api/v1/onboarding/links roda em fila por drop. Se falhar: cancelar pendentes, desfazer para o último estado confirmado pelo servidor e mostrar toast flutuante persistente com borda vermelha, botão fechar e sem sumir sozinho (mantendo "Erro ao salvar").

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `useEditorBackendSync.ts` usa debounce `600ms` e payload `{ ordered_ids }` sem fila FIFO para drops rápidos. `useOnboardingAutoSave.ts` envia `PUT /onboarding/links` sem rollback e sem toast explicativo, e `mockStorage.reorderButtons` não suporta `{ items }` com troca de seção.
- **Solução Proposta:** Atualize `buttons.service.ts` e mocks para `{ items: [{ id, display_mode }] }` de `API-10` com retorno `data.buttons`. Crie fila FIFO serializada separada por caminho (`/editor` via `POST /buttons/reorder`; `/cadastro/links` via `PUT /onboarding/links`). Em falha, aborte pendentes da fila, faça rollback para o último snapshot confirmado pelo servidor e exiba toast flutuante vermelho persistente mantendo `Erro ao salvar`.
- **OBS:** Depende de `API-10` e `LNK-13`. Decisões `2B`, `3B`, `4A` e `5` fechadas: caminhos isolados, payload completo `{ items }`, toast vermelho visível também na `Prévia` em `390px` sem auto-dismiss. Respeitar `<= 150` / `<= 120` linhas.
- **Como Executar (Subtarefas):**
  - [ ] **LNK-14.1.** Atualize `buttons.service.ts` e `context/api_contracts.md` para enviar `{ items: [{ id, display_mode }] }` e ler resposta `API-10`.
  - [ ] **LNK-14.2.** Atualize `mockButtonsHandlers.ts` e `mockStorageStore.ts` replicando `{ items }`, teto `5` circulares e normalização `0..K-1`/`K..M-1`.
  - [ ] **LNK-14.3.** Crie fila FIFO serializada no `/editor` (`POST /buttons/reorder`) e no `/cadastro/links` (`PUT /onboarding/links`).
  - [ ] **LNK-14.4.** Cancele pendências e restaure lista e `ProfileMockup` para o último snapshot confirmado quando a API falhar.
  - [ ] **LNK-14.5.** Adicione toast flutuante persistente com borda vermelha e botão fechar mantendo `Erro ao salvar` em `390px`/`1440px`.
  - [ ] **LNK-14.6.** Adicione testes Vitest cobrindo drops rápidos, aborto da fila, rollback em ambos fluxos, toast vermelho e mocks.

**Localização & Referências:**

- **Rota / URL:** `/cadastro/links`, `/editor`
- **Arquivos / Pastas:** `src/services/api/buttons.service.ts` | `src/services/api/mock/mockButtonsHandlers.ts` | `src/services/api/mock/mockStorageStore.ts` | `src/models/editor/editorButtonsSync.model.ts` | `src/models/onboarding/onboardingAutoSave.model.ts` | `src/controllers/hooks/editor/useEditorBackendSync.ts` | `src/controllers/hooks/onboarding/useOnboardingAutoSave.ts` | `src/content/onboarding/linkSelection.content.ts` | `test/services/api/buttons.service.test.ts` | `test/services/api/mock/mockStorageStore.test.ts` | `test/controllers/hooks/editor/useEditorBackendSync.test.ts` | `test/controllers/hooks/onboarding/useOnboardingAutoSave.test.ts`
- **Docs / Assets:** `context/api_contracts.md` | Issue #299

---

### Decisões Fechadas para Promoção ao Fluxo C (`#299`)

1. **Limite por modo (`1A` ajustada):** Limite global de `30` botões (`API-11`/`LNK-11`), removendo o teto de `5` dos botões retangulares (`button`) e mantendo o limite de **`5` apenas em `Botões circulares`**.
2. **Rota por fluxo (`2B`):** `/editor` chama `POST /api/v1/buttons/reorder` (1x por drop na fila FIFO); `/cadastro/links` mantém `PUT /api/v1/onboarding/links` em fila por drop com snapshot de rollback e toast vermelho.
3. **Contrato de reorder (`3B`):** Payload declarativo completo `{ items: [{ id, display_mode }] }` na ordem final, executado em `DB::transaction` (`lockForUpdate()`), validando posse de todos os IDs e normalizando `circular` `0..K-1` e `button` `K..M-1`.
4. **Conversão visual e política de erro (`4A`):** Ao arrastar `icon_variant: 'none'` para `Botões circulares`, converter imediatamente para ícone padrão (`'default'`, `show_icon_background: true`) visível na UI/mockup e na transação BE. Em falha na fila, cancelar pendentes, reverter ao último estado confirmado pelo servidor e abrir toast vermelho.
5. **UX e layout (`5`):** Seção vazia sempre visível como dropzone tracejada; cards verticais em ambas as seções; botões `▲`/`▼` preservados dentro da própria seção (acessibilidade + mobile); toast flutuante com borda vermelha, botão fechar e **sem sumir sozinho**, mantendo `"Erro ao salvar"`.
6. **Proteção de `order_index` (`6A`):** `PATCH /api/v1/buttons/{id}` (`UpdateButtonRequest`) deixa de aceitar `order_index` (normalizando se `display_mode` mudar); `PUT /api/v1/onboarding/links` normaliza sempre.
7. **Dependência externa (`7` / Regra 6.5):** Instalação de `@dnd-kit/core`, `@dnd-kit/sortable` e `@dnd-kit/utilities` autorizada; se `npm install` acusar conflito de `peerDependency` com React 19 (`react@^19.2.8`), parar e avisar o usuário antes de usar `--force` ou `--legacy-peer-deps`.

---

### Ordem sugerida e dependências (Fila Ativa v1 — Matriz de Orquestração Orca)

1. **Lote Fluxo C/D/E Concluído (`DONE` — planos arquivados em [`context/tasks_concluidas/`](tasks_concluidas/)):**
   - `#298` (`FIX-API-09` BE `df2a75e`, `FIX-BOT-06` `b646a9e`, `FIX-FON-06` `6182224`, `a0f3171` + Fluxo E em `tasks_ajuste_298.md`), `#234` (`PUB-03`), `#243` (`PUB-07`), `#245` (`PRF-06`), `#256` (`FIX-GLB-09`), `#257` (`FIX-EDT-17`), `#258` (`FIX-GLB-10`), `#259` (`FIX-API-06`), `#261` (`FIX-FON-05`), `#262` (`FIX-API-07`), `#272` (`FIX-CAD-09`), `#274` (`FIX-GLB-14`), `#275` (`FIX-EDT-18`), `#276` (`FIX-PRF-05`), `#281` (`FIX-PUB-10`), `#282` (`PUB-12`), `#283` (`FIX-GLB-16`), `#286` (`GLB-09`), `#292` (`FIX-PUB-11`), `#293` (`FIX-PUB-12`), `#295` (`DOC-02`), `#297` (`FIX-PRF-07`), além do lote Orca `run_6d1ef873450a` na branch `pinhogo/orquestracao-lote-v1` (`#264`, `#270`, `#271`, `#273`, `#277`, `#278`, `#279`, `#280`, `#285`, `#304`, `#305`, `#306`, `#313`, `#322`, `#324`).
   - Nenhum item ativo em `EXECUÇÃO - FLUXO C` ou `CORREÇÃO E REVISÃO - FLUXO D` (`0`). `17` itens do lote PR `#321` + Backend `#1` aguardam homologação em `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E`.
2. **Pendências Remanescentes em `BACKLOG - FLUXO B` (`3` itens + pacote `#299`):**
   - **Backend Pareado (Regra 13):** `#268` (`FIX-API-08` em `/home/pinho/Projetos/assinart_v1_backend`).
   - **Botão de E-mail (Cópia + Pop-up + `/editor`):** `#325` (`FIX-API-12` backend) ➔ desbloqueia `#326` (`LNK-15` frontend).
   - **Links e Reordenação `#299` (Decisões 1–7 fechadas; pronta para Fluxo C):** Backend `API-10` ➔ desbloqueia `API-11` e `LNK-14`; Backend `API-11` pareado à `LNK-11`; Frontend em cadeia `LNK-11` ➔ `LNK-12` ➔ `LNK-13` ➔ `LNK-14`.

---

### [FIX-API-12] Aceitar e Normalizar E-mail nos Endpoints `/api/v1/buttons` sem Bloquear com Regra HTTP `url`

- **Estado no Backlog:** Em Tratativa *(promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_backend_api.md`](tasks_backend_api.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `GLB (Global)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/325)

**Contexto Bruto:**

> No /editor, ao adicionar ou editar link da plataforma email, POST /api/v1/buttons e PATCH /api/v1/buttons/{id} rejeitam mailto: (e endereços de e-mail) com HTTP 422 "A URL de destino deve ser um link válido.", diferente de PUT /api/v1/onboarding/links.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `StoreButtonRequest.php` e `UpdateButtonRequest.php` aplicam a regra `'url'` do Laravel sobre o campo `url`, rejeitando `mailto:...` e endereços `nome@dominio.com` da plataforma `email`.
- **Solução Proposta:** Crie validação específica para `platform === 'email'` em `StoreButtonRequest.php` e `UpdateButtonRequest.php` aceitando e-mail válido (ou `mailto:` legado), normalizando para formato compatível com o contrato e preservando validação `'url'` para demais plataformas.
- **OBS:** Repo `/home/pinho/Projetos/assinart_v1_backend`. Preservar compatibilidade regressiva (Regra 13) para botões legados com prefixo `mailto:` já gravados por `OnboardingController::saveLinks`.
- **Como Executar (Subtarefas):**
  - [ ] **FIX-API-12.1.** Atualize `StoreButtonRequest.php` e `UpdateButtonRequest.php` para validar e-mail válido (com ou sem `mailto:`) quando `platform === 'email'`.
  - [ ] **FIX-API-12.2.** Normalize o destino salvo em `ButtonController.php` / `OnboardingController.php` e revise `PublicProfileController.php` mantendo retrocompatibilidade.
  - [ ] **FIX-API-12.3.** Adicione testes PHPUnit em `ButtonTest.php` cobrindo `POST /api/v1/buttons` e `PATCH /api/v1/buttons/{id}` para plataforma `email` e atualize `context/api_contracts.md`.

**Localização & Referências:**

- **Rota / URL:** `POST /api/v1/buttons`, `PATCH /api/v1/buttons/{id}`, `PUT /api/v1/onboarding/links`
- **Arquivos / Pastas:** `/home/pinho/Projetos/assinart_v1_backend/app/Http/Requests/StoreButtonRequest.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Requests/UpdateButtonRequest.php` | `/home/pinho/Projetos/assinart_v1_backend/app/Http/Controllers/Api/ButtonController.php` | `/home/pinho/Projetos/assinart_v1_backend/tests/Feature/ButtonTest.php` | `context/api_contracts.md`
- **Docs / Assets:** `context/api_contracts.md` | Issue #325 | Pareada `#326` (`LNK-15`)

---

### [LNK-15] Copiar E-mail ao Clicar com Pop-up `"E-mail copiado!"` e Corrigir Fluxo de E-mail no `/editor`

- **Estado no Backlog:** Em Tratativa *(promovida para `EXECUÇÃO - FLUXO C` em [`context/tasks_links_dnd.md`](tasks_links_dnd.md))*
- **GitHub Project (#2):** Status `EXECUÇÃO - FLUXO C` | Domínio `LNK (Links)` | Etapa `Etapa 1 (Execução)` (https://github.com/pinhogo/assinart_v1/issues/326)

**Contexto Bruto:**

> O e-mail pode ter um comportamento diferente dos outros botões: ele poderia apenas copiar o e-mail da pessoa como se fosse um Ctrl + C, ninguém usa essa função de mailto, ela só atrapalha a vida. Colocar aquele pop-up que levanta informando quando algo é copiado ("E-mail copiado!").

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** (1) No `/editor`, criar/editar link `email` falha com HTTP `422` + rollback e hidrata o input com prefixo `mailto:` por causa de `sanitizePlatformValue`; (2) na página pública (`/:username`) e nas prévias, clicar no botão `E-mail` (retangular ou circular) abre `mailto:` em vez de copiar o endereço para a área de transferência com pop-up de confirmação (`animate-bounce`, padrão `editorToast`).
- **Solução Proposta:** Limpe o prefixo `mailto:` na hidratação/edição do campo `email`, alinhe o payload com `FIX-API-12`, e altere o clique em botões `platformId === 'email'` (retangular e circular) para copiar o e-mail limpo (`Ctrl+C`), registrar clique via `POST /api/v1/track` (`metricsService.trackEvent`) e exibir o toast flutuante `"E-mail copiado!"` no estilo `editorToast`.
- **OBS:** Reutilizar exatamente o estilo visual de `editorToast` em `EditorView.style.ts` (`fixed bottom-6 right-6 z-50 ... bg-[#ACE1AF] ... animate-bounce`) e centralizar cópias em `publicProfile.content.ts` (Regra 3). Tratar formato `button` (`PhoneLinkButton` / `PublicProfileButtonLinksList`) e `circular` (`ProfileCircularItem` / `PublicProfileCircularLinksRow`).
- **Como Executar (Subtarefas):**
  - [ ] **LNK-15.1.** Remova prefixo `mailto:` na hidratação em `platformValueSanitizer.model.ts` e ajuste envio/extração de e-mail em `editorButtonsSync.model.ts` e `platformContactAndUrlRules.model.ts` (pareada à `FIX-API-12`).
  - [ ] **LNK-15.2.** Altere o clique de itens `email` (retangular e circular) em `PhoneLinkButton.tsx`, `ProfileCircularItem.tsx`, `PublicProfileButtonLinksList.tsx` e `PublicProfileCircularLinksRow.tsx` para copiar o e-mail (`navigator.clipboard.writeText` com fallback) sem disparar `mailto:`.
  - [ ] **LNK-15.3.** Adicione o pop-up flutuante com `animate-bounce` (`editorToast`) exibindo `"E-mail copiado!"` em `PublicProfileView.tsx` (e prévia clicável) com texto em `publicProfile.content.ts` e dispare `metricsService.trackEvent({ type: 'click', link_id, username })`.
  - [ ] **LNK-15.4.** Crie e atualize testes Vitest cobrindo `/editor`, `sanitizePlatformValue`, clique retangular/circular copiando e-mail, toast `"E-mail copiado!"` e telemetria.

**Localização & Referências:**

- **Rota / URL:** `/editor`, `/cadastro/links`, `/:username`
- **Arquivos / Pastas:** `src/models/onboarding/platformValueSanitizer.model.ts` | `src/models/onboarding/platformContactAndUrlRules.model.ts` | `src/models/editor/editorButtonsSync.model.ts` | `src/models/profile/publicProfileMockupAdapter.model.ts` | `src/components/mockups/PhoneLinkButton/PhoneLinkButton.tsx` | `src/components/mockups/ProfileMockup/ProfileCircularItem.tsx` | `src/components/profile/PublicProfileButtonLinksList.tsx` | `src/components/profile/PublicProfileCircularLinksRow.tsx` | `src/views/profile/PublicProfileView.tsx` | `src/content/profile/publicProfile.content.ts`
- **Docs / Assets:** `context/api_contracts.md` | Issue #326 | Pareada `#325` (`FIX-API-12`)

---

### [FIX-GLB-21] Restaurar Contraste do Toggle Switch Desligado no Menu ⋮ em Tema Escuro

- **Estado no Backlog:** Concluída (`DONE` no Project nº 2 — descoberta e corrigida na Onda 4 do lote Orca `run_6d1ef873450a`, commit `0d743e0` em `pinhogo/orquestracao-lote-v1`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global / UI)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1/issues/324)
- **Causa Raiz e Solução:** Os overrides `.theme-dark .bg-white:not([data-public-profile] *)` e `.theme-dark .bg-neutral-300:not([data-public-profile] *)` em `src/index.css` forçavam a trilha e o thumb do `MenuToggleSwitch` no estado desligado para `var(--landing-surface-raised)` (`#22252e`, contraste `1.00`). Adicionados atributos explícitos (`data-action-menu-toggle="true"`, `data-checked`, `data-action-menu-toggle-thumb="true"`) em `MenuToggleSwitch.tsx`, bordas explícitas em `LinkActionMenu.style.ts`, tokens de alto contraste em `src/index.css` (`#3E4452`/`#787F8E` desligado, `#ACE1AF` ligado, thumb `#FFFFFF`/`#121316`) e testes em `test/components/mockups/MenuToggleSwitch.test.tsx`, validados com Chromium em `/cadastro/botoes` (`.theme-dark`).

---

## 🛡️ Segurança e Deploy do Backend — `BACKLOG - FLUXO B` (`17` entradas: `SEC-04`..`SEC-14`, `API-14`..`API-19`)
> Origem: `context/SECURITY_AUDIT.md` (reverificação de 2026-11-15) e `context/DEPLOY_PLAN.md`. Plano de execução em `context/tasks_backend.md` (Fase 9).

### [SEC-14] Reabrir e Reverificar SEC-01 a SEC-03 no Código do Backend

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Auditoria de 2026-11-15: o backlog marca SEC-01 (#287), SEC-02 (#288) e SEC-03 (#289) como DONE, mas o código do backend ainda contém as três falhas.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `routes/api_auth.php` mantém `/email/verify` público. `OnboardingController.php` (L292-293) define `email_verified_at`. `InteractsWithMediaStorage::deleteFileFromStorage` apaga sem checar dono. `formatPlatformUrl` retorna `$input` bruto em `whatsapp` (L352). `StoreButtonRequest` e `UpdateButtonRequest` usam apenas a regra `url`.
- **Solução Proposta:** Confirme se as correções vivem em outro repositório ou branch. Reabra os cards `#287`, `#288` e `#289` se a falha persistir. Execute as correções no backend com testes Feature.
- **OBS:** Bloqueia o deploy (Fase 9). Execute antes de `API-17`.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-14.1.** Compare os commits de `#287`, `#288` e `#289` com a branch `develop` do backend.
  - [ ] **SEC-14.2.** Exija URL assinada em `ManagesPasswordAndEmail::verifyEmail` e remova a auto-verificação de `OnboardingController::saveButtons`.
  - [ ] **SEC-14.3.** Valide o dono do caminho em `deleteFileFromStorage` e isole uploads por `artist_id`.
  - [ ] **SEC-14.4.** Aceite apenas `http`/`https` (e `mailto:` para `email`) em botões e em `RedirectController`. Rejeite `whatsapp` sem dígitos com `422`.
  - [ ] **SEC-14.5.** Valide `wallpaper_url` e decodifique `base64` antes de aceitar `data:image/svg+xml` em `UpdateProfileRequest`.
  - [ ] **SEC-14.6.** Cubra cada correção com testes Feature em `tests/Feature/`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` | `app/Http/Controllers/Api/OnboardingController.php` | `app/Http/Controllers/Api/Concerns/InteractsWithMediaStorage.php` | `app/Http/Requests/StoreButtonRequest.php` | `app/Http/Requests/UpdateButtonRequest.php` | `app/Http/Requests/UpdateProfileRequest.php` | `app/Http/Controllers/RedirectController.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-04] Validar, Limitar e Filtrar a Telemetria Pública em POST /api/v1/track

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-04 (Alta) em `context/SECURITY_AUDIT.md`: telemetria pública sem validação, sem throttle e com bypass de `draft` e `is_active`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `MetricsController::track` grava `page_views` e `button_clicks` sem `FormRequest`, sem `throttle` e sem checar `is_published` e `is_active`. `visitor_id` e `utm_source` não têm limite de tamanho.
- **Solução Proposta:** Crie `TrackEventRequest` com limites estritos. Filtre artistas publicados e botões ativos. Aplique `throttle:120,1` na rota.
- **OBS:** O SQLite em `/home` do Azure sofre com escrita ilimitada. Esta task reduz o risco de `database is locked` (`API-18`).
- **Como Executar (Subtarefas):**
  - [ ] **SEC-04.1.** Crie `app/Http/Requests/TrackEventRequest.php` com `type in:pageview,click`, `visitor_id max:64`, `utm_source max:100` e `referrer max:2048`.
  - [ ] **SEC-04.2.** Filtre `Artist::where('is_published', true)` em `pageview`.
  - [ ] **SEC-04.3.** Filtre `Button` ativo com artista publicado em `click`.
  - [ ] **SEC-04.4.** Adicione `throttle:120,1` em `routes/api_metrics.php`.
  - [ ] **SEC-04.5.** Cubra validação, `draft`, botão inativo e throttle em `tests/Feature/`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `app/Http/Controllers/Api/MetricsController.php` | `app/Http/Requests/TrackEventRequest.php` | `routes/api_metrics.php` | `tests/Feature/MetricsTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-05] Aplicar Throttle em Auth e Corrigir o Reset de Senha

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-05 (Alta): `register`, `forgot-password` e `reset-password` sem rate limit. O reset aceita token em texto puro e não revoga tokens Sanctum.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `routes/api_auth.php` não limita `register`, `forgot-password` e `reset-password`. `ManagesPasswordAndEmail.php` (L95) aceita `hash_equals` com token em texto puro. O reset não executa `$artist->tokens()->delete()`.
- **Solução Proposta:** Adicione `throttle` nomeado nas três rotas. Remova o fallback em texto puro. Revogue todos os tokens após o reset.
- **OBS:** Cada `register` custa CPU de bcrypt (`BCRYPT_ROUNDS=12`) no plano B1.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-05.1.** Adicione `throttle:5,1` em `forgot-password` e `reset-password` em `routes/api_auth.php`.
  - [ ] **SEC-05.2.** Adicione `throttle:10,1` em `register`.
  - [ ] **SEC-05.3.** Remova a comparação em texto puro de `ManagesPasswordAndEmail::resetPassword`.
  - [ ] **SEC-05.4.** Execute `$artist->tokens()->delete()` após redefinir a senha.
  - [ ] **SEC-05.5.** Cubra o limite `429` e a revogação de tokens em `tests/Feature/AuthTest.php`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `routes/api_auth.php` | `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` | `tests/Feature/AuthTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-06] Confiar no Proxy do Azure e Corrigir a Chave de email/resend

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-06 (Alta): sem `trustProxies`, o IP do load balancer colapsa os limites. `email/resend` usa o e-mail informado como chave.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `API-12` (`5635c9c`) adicionou `$middleware->trustProxies(at: '*')` em `bootstrap/app.php`, mas `login` ainda usa chave frágil e `resendVerification` chaveia por `input('email')` sem autenticação. O `login` limita 5/min por IP. `/r/{id}` limita 60/min por IP. `resendVerification` chaveia por `input('email')` sem autenticação.
- **Solução Proposta:** Confirme a configuração de `trustProxies`. Componha a chave de login com e-mail e IP. Chaveie `email/resend` por IP.
- **OBS:** Task pareada com `API-14`. O Azure App Service envia `X-Forwarded-*`.
- **Como Executar (Subtarefas):**
  - [x] **SEC-06.1.** Adicione `$middleware->trustProxies(at: '*')` em `bootstrap/app.php` *(concluído em `API-12` / `5635c9c`)*.
  - [ ] **SEC-06.2.** Componha a chave do `RateLimiter` de `login` com e-mail e IP em `AuthController.php`.
  - [ ] **SEC-06.3.** Chaveie `resendVerification` por IP e e-mail validado.
  - [ ] **SEC-06.4.** Cubra o IP real via `X-Forwarded-For` em `tests/Feature/AuthTest.php`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `bootstrap/app.php` | `app/Http/Controllers/Api/AuthController.php` | `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` | `tests/Feature/AuthTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-07] Reduzir Enumeração de Contas e Bloquear /r/{id} de Perfil em Rascunho

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-07 (Média): enumeração por `forgot-password`, `usernames/check` e `/{username}`. `/r/{id}` redireciona perfis `draft`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `forgot-password` retorna `422` para e-mail inexistente. `usernames/check` e `GET /{username}` não têm throttle. `PublicProfileController` expõe `artists.id` (L41, L63). `RedirectController` não checa `is_published`.
- **Solução Proposta:** Responda `200` genérico em `forgot-password`. Adicione throttle nas rotas públicas. Retorne `404` em `/r/{id}` para perfil `draft`. Avalie remover o `id` público.
- **OBS:** Retrocompatibilidade (Regra 13): confirme se o frontend usa `id` do perfil público.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-07.1.** Substitua `exists:artists,email` por resposta `200` genérica em `forgot-password`.
  - [ ] **SEC-07.2.** Adicione `throttle:60,1` em `usernames/check` e em `GET /{username}`.
  - [ ] **SEC-07.3.** Filtre `is_published` em `RedirectController::redirect` e retorne `404` se `draft`.
  - [ ] **SEC-07.4.** Avalie a remoção de `id` em `PublicProfileController` e atualize `context/api_contracts.md`.
  - [ ] **SEC-07.5.** Cubra os quatro casos em `tests/Feature/`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` | `routes/api_auth.php` | `routes/web_public.php` | `app/Http/Controllers/RedirectController.php` | `app/Http/Controllers/PublicProfileController.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-08] Definir Expiração do Token Sanctum e Fortalecer a Senha

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-08 (Média): tokens sem expiração, senha `min:6` e logout parcial.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `config/sanctum.php` define `expiration` como `null` (L50). `RegisterRequest.php` aceita `min:6`. `resetPassword` também aceita `min:6`. Não existe revogação global de sessões.
- **Solução Proposta:** Defina a expiração via `SANCTUM_EXPIRATION`. Exija `min:8` no cadastro e no reset. Ofereça revogação global.
- **OBS:** Alinhe a mensagem de erro com o frontend. Expiração curta exige tratamento de `401` no `apiClient.ts`.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-08.1.** Defina `expiration` em `config/sanctum.php` com `env('SANCTUM_EXPIRATION')`.
  - [ ] **SEC-08.2.** Altere `RegisterRequest.php` e `resetPassword` para `min:8`.
  - [ ] **SEC-08.3.** Crie a opção de revogação global em `AuthController::logout`.
  - [ ] **SEC-08.4.** Atualize `context/api_contracts.md` e os testes de `AuthTest.php`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `config/sanctum.php` | `app/Http/Requests/RegisterRequest.php` | `app/Http/Controllers/Api/AuthController.php` | `tests/Feature/AuthTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-09] Restringir CORS e Registrar Security Headers

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-09 (Média): CORS amplo e ausência de security headers.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `config/cors.php` mantém `'*'` em `paths` (L18) e `supports_credentials` como `true` (L36). O backend usa Bearer Token. Faltam `nosniff`, `X-Frame-Options`, `Referrer-Policy` e `HSTS`.
- **Solução Proposta:** Remova `'*'` de `paths`. Defina `supports_credentials` como `false`. Adicione `https://www.assin.art` e `CORS_ALLOWED_ORIGINS`. Registre um middleware de headers.
- **OBS:** Existe `tests/Feature/CorsSecurityTest.php`. Estenda esse teste.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-09.1.** Edite `config/cors.php` conforme a solução proposta.
  - [ ] **SEC-09.2.** Crie o middleware `SecurityHeaders` e registre em `bootstrap/app.php`.
  - [ ] **SEC-09.3.** Estenda `tests/Feature/CorsSecurityTest.php` com preflight e headers.
  - [ ] **SEC-09.4.** Atualize `.env.example` com `CORS_ALLOWED_ORIGINS`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `config/cors.php` | `bootstrap/app.php` | `app/Http/Middleware/SecurityHeaders.php` | `.env.example` | `tests/Feature/CorsSecurityTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-10] Remover o Stub de finfo e Bloquear PHP em /storage

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-10 (Média): o stub de `finfo` retorna `false` e mascara a validação MIME. `/storage` precisa negar `.php`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `bootstrap/app.php` (L10-37) define `finfo` falso. O `mimes:` do upload passa sem inspeção real. O Termux local usa esse stub e os testes não validam MIME.
- **Solução Proposta:** Exija `ext-fileinfo` e `ext-mbstring` no `composer.json`. Carregue o stub apenas com `APP_ENV=local`. Bloqueie `.php` em `/storage` no Nginx (`API-15`).
- **OBS:** Rode a suíte em PHP 8.3 real para validar MIME.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-10.1.** Adicione `ext-fileinfo` e `ext-mbstring` em `composer.json`.
  - [ ] **SEC-10.2.** Condicione os polyfills de `bootstrap/app.php` a `APP_ENV=local`.
  - [ ] **SEC-10.3.** Cubra o upload com MIME inválido em `tests/Feature/MediaStorageTest.php`.
  - [ ] **SEC-10.4.** Registre a regra de Nginx em `azure/nginx.conf` (`API-15`).

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `bootstrap/app.php` | `composer.json` | `tests/Feature/MediaStorageTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-11] Validar Cores, Truncar X-Visitor-Id e Limitar Uploads

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-11 (Baixa): cores sem regex, `X-Visitor-Id` sem truncamento e uploads sem throttle.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Requests de perfil e botões aceitam strings livres de cor. `ProfileController::update` usa `substr` sem regex. `RedirectController` grava `X-Visitor-Id` sem limite de 64 caracteres. Rotas de upload não têm `throttle`.
- **Solução Proposta:** Crie uma regra hexadecimal reutilizável. Trunque `visitor_id` em 64 caracteres. Adicione `throttle:20,1` nos uploads.
- **OBS:** MySQL e PostgreSQL em modo estrito retornam erro `500` em coluna excedida (`API-12`).
- **Como Executar (Subtarefas):**
  - [ ] **SEC-11.1.** Crie `app/Rules/HexColor.php` e aplique nos Requests de perfil, onboarding e botões.
  - [ ] **SEC-11.2.** Valide o hexadecimal em `ProfileController::update` antes de `substr`.
  - [ ] **SEC-11.3.** Trunque `visitor_id` em `RedirectController` e `MetricsController`.
  - [ ] **SEC-11.4.** Adicione `throttle:20,1` em `routes/api_profile.php` e `routes/api_buttons.php` (uploads).
  - [ ] **SEC-11.5.** Cubra os casos em `tests/Feature/`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `app/Rules/HexColor.php` | `app/Http/Requests/UpdateProfileRequest.php` | `app/Http/Requests/OnboardingButtonsRequest.php` | `app/Http/Controllers/RedirectController.php` | `routes/api_profile.php` | `routes/api_buttons.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-12] Proteger o fillable de Button e Limpar Arquivos Órfãos

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> SEC-12 (Baixa): `Button::$fillable` expõe `artist_id` e `clicks_count`. `saveLinks` deixa arquivos órfãos. A lista de slugs reservados já foi unificada em `App\Support\ReservedUsernames`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `app/Models/Button.php` mantém `artist_id` e `clicks_count` em `$fillable`. `OnboardingController::saveLinks` executa `buttons()->delete()` sem apagar `image_url`.
- **Solução Proposta:** Remova os dois campos de `$fillable`. Apague os arquivos de `image_url` antes de `delete()`.
- **OBS:** Mantenha a ordem: execute depois de `SEC-14` para evitar conflito em `deleteFileFromStorage`.
- **Como Executar (Subtarefas):**
  - [ ] **SEC-12.1.** Remova `artist_id` e `clicks_count` de `Button::$fillable`.
  - [ ] **SEC-12.2.** Atribua `artist_id` por relação e `clicks_count` por `increment`.
  - [ ] **SEC-12.3.** Apague os arquivos de `image_url` em `OnboardingController::saveLinks`.
  - [ ] **SEC-12.4.** Cubra os dois casos em `tests/Feature/`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `app/Models/Button.php` | `app/Http/Controllers/Api/OnboardingController.php` | `tests/Feature/ButtonTest.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [SEC-13] Remover Artefatos Sensíveis e Gerados do Controle de Versão

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Reverificação de 2026-11-15: o Git rastreia `database/database.sqlite`, `bootstrap/cache/*.php`, `composer.phar`, `storage/logs/laravel.log` e views compiladas.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `database.sqlite` pode conter hashes e e-mails de teste. `laravel.log` pode vazar dados. `bootstrap/cache` referencia `require-dev` e quebra `--no-dev`. `.env.example` define `APP_DEBUG=true`.
- **Solução Proposta:** Remova os arquivos do índice com `git rm --cached`. Reforce o `.gitignore`. Defina `APP_DEBUG=false` como padrão seguro no exemplo.
- **OBS:** Avalie `git filter-repo` para limpar o histórico se o `.sqlite` tiver dados reais. Não use `git push --force` (Regra 15).
- **Como Executar (Subtarefas):**
  - [ ] **SEC-13.1.** Execute `git rm --cached` em `database/database.sqlite`, `bootstrap/cache/*.php`, `composer.phar`, `storage/logs/laravel.log` e `storage/framework/views/*.php`.
  - [ ] **SEC-13.2.** Adicione os caminhos ao `.gitignore`, com exceção de `.gitignore` das pastas.
  - [ ] **SEC-13.3.** Defina `APP_DEBUG=false` e `APP_ENV=production` em `.env.example`.
  - [ ] **SEC-13.4.** Inspecione o histórico por dados reais em `database.sqlite`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `.gitignore` | `.env.example` | `database/database.sqlite` | `bootstrap/cache/` | `composer.phar` | `storage/logs/laravel.log`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [API-14] Validar Proxy, CORS e Colisão de Rotas no Ambiente Azure

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Revisão de `context/DEPLOY_PLAN.md`: validar `trustProxies`, CORS e a colisão entre `/storage` e `/{username}`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O plano de deploy não prova o IP real, o preflight de `https://assin.art` nem a colisão de `/storage/*` com `GET /{username}`.
- **Solução Proposta:** Cubra os três pontos com testes Feature. A implementação vive em `SEC-06` e `SEC-09`.
- **OBS:** Depende de `SEC-06` e `SEC-09`.
- **Como Executar (Subtarefas):**
  - [ ] **API-14.1.** Teste o preflight `OPTIONS` com `Access-Control-Request-Method`.
  - [ ] **API-14.2.** Teste o IP real com `X-Forwarded-For`.
  - [ ] **API-14.3.** Confirme que `/storage/x` não casa com `ReservedUsernames::routePattern()`.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `tests/Feature/CorsSecurityTest.php` | `app/Support/ReservedUsernames.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [API-15] Criar os Arquivos de Runtime do Azure (azure/)

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Fase 9 de `context/tasks_backend.md`: Nginx, startup, limites PHP e `.gitattributes`.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O repositório não contém `azure/`. O `startup.sh` do plano depende de `service nginx reload`, que pode falhar no boot.
- **Solução Proposta:** Crie `azure/nginx.conf`, `azure/startup.sh` idempotente, `public/.user.ini` e `.gitattributes` com `export-ignore`.
- **OBS:** Teste o startup no App Service real com `az webapp ssh`. Gere os caches no build.
- **Como Executar (Subtarefas):**
  - [ ] **API-15.1.** Crie `azure/nginx.conf` com `client_max_body_size 16M` e bloqueio de `.php` em `/storage`.
  - [ ] **API-15.2.** Crie `azure/startup.sh` com symlink de `storage/app/public` para `/home/data` e backup do banco antes de `migrate --force`.
  - [ ] **API-15.3.** Crie `public/.user.ini` com `upload_max_filesize=12M` e `post_max_size=16M`.
  - [ ] **API-15.4.** Crie `.gitattributes` com `export-ignore` para `context/`, `docs/`, `tests/` e `*.md`.
  - [ ] **API-15.5.** Valide que o startup termina em menos de 230 s.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `azure/nginx.conf` | `azure/startup.sh` | `public/.user.ini` | `.gitattributes`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [API-16] Criar o Workflow de Build e Deploy do Backend

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Fase 9 de `context/tasks_backend.md`: build limpo em PHP 8.3 com testes.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O plano gera o ZIP à mão a partir de `git archive HEAD`. O PHP local do Termux mascara a validação MIME.
- **Solução Proposta:** Crie `.github/workflows/deploy-backend.yml` com `shivammathur/setup-php`, `php artisan test` e `az webapp deploy`.
- **OBS:** Depende de `SEC-13`. Guarde o ZIP anterior como artefato de rollback.
- **Como Executar (Subtarefas):**
  - [ ] **API-16.1.** Crie o workflow com PHP 8.3 e `composer install --no-dev`.
  - [ ] **API-16.2.** Execute `php artisan test` antes do pacote.
  - [ ] **API-16.3.** Gere o ZIP sem `.env*`, `tests/` e `.sqlite`.
  - [ ] **API-16.4.** Publique com `az webapp deploy` e guarde o ZIP anterior.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `.github/workflows/deploy-backend.yml`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [API-17] Provisionar o Azure e Configurar App Settings

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Fase 9 de `context/tasks_backend.md`: grupo, plano B1, Web App e variáveis de ambiente.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O plano não valida a quota de B1 na assinatura Azure for Students nem define alerta de custo.
- **Solução Proposta:** Valide assinatura, região e quota. Crie `azure/provision.sh` e `azure/appsettings.example.env`.
- **OBS:** Bloqueado por `SEC-14`. Fixe uma única instância por causa do SQLite.
- **Como Executar (Subtarefas):**
  - [ ] **API-17.1.** Valide `az account show`, quota de B1 e região (`brazilsouth` ou `eastus2`).
  - [ ] **API-17.2.** Crie `azure/provision.sh` com plano B1 Linux, `https-only`, `always-on` e TLS 1.2.
  - [ ] **API-17.3.** Defina `WEBSITES_CONTAINER_START_TIME_LIMIT` e instância única.
  - [ ] **API-17.4.** Configure `APP_KEY`, `APP_URL`, `FILESYSTEM_DISK=public`, `SESSION_DRIVER=file` e `QUEUE_CONNECTION=sync`.
  - [ ] **API-17.5.** Crie um alerta de custo.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `azure/provision.sh` | `azure/appsettings.example.env`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [API-18] Mitigar o Risco de SQLite em /home e Criar Backup

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Revisão de `context/DEPLOY_PLAN.md`: `/home` do App Service usa CIFS, com risco de lock e corrupção.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `POST /api/v1/track` e `GET /r/{id}` escrevem a cada visita em `page_views` e `button_clicks`. Não existe backup do `.sqlite`.
- **Solução Proposta:** Execute teste de carga. Habilite WAL e `busy_timeout` se houver lock. Crie `azure/backup.sh`.
- **OBS:** Depende de `SEC-04`. O gatilho de saída é `#327` (`API-12`, Task 8.1) (PostgreSQL ou MySQL).
- **Como Executar (Subtarefas):**
  - [ ] **API-18.1.** Execute teste de carga em `/api/v1/track` e `/r/{id}`.
  - [ ] **API-18.2.** Defina WAL e `busy_timeout` em `config/database.php`, se necessário.
  - [ ] **API-18.3.** Crie `azure/backup.sh` com timestamp e documente a restauração.
  - [ ] **API-18.4.** Defina o gatilho de migração para o banco gerenciado.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `azure/backup.sh` | `config/database.php`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### [API-19] Executar Smoke Test, E2E e Rollback em Produção

- **Estado no Backlog:** Pendente
- **GitHub Project (#2):** Status `BACKLOG - FLUXO B` | Domínio `GLB (Global)` | Etapa `Fluxo B (Planejado)` (`https://github.com/users/pinhogo/projects/2`)

**Contexto Bruto:**

> Fase 9 de `context/tasks_backend.md`: validação ponta a ponta e rollback.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** O rollback do plano reimplanta o ZIP, mas não restaura o schema. O DNS de `assin.art` e `www.assin.art` não aparece no plano.
- **Solução Proposta:** Conecte o frontend, valide DNS e HTTPS, execute o E2E anônimo e documente o rollback completo.
- **OBS:** Depende de `API-15`, `API-16`, `API-17` e `API-18`. `forgot-password` continua sem e-mail (`MAIL_MAILER=log`).
- **Como Executar (Subtarefas):**
  - [ ] **API-19.1.** Defina `VITE_API_BASE_URL` e `VITE_USE_MOCK_API=false` e dispare `deploy-pages.yml`.
  - [ ] **API-19.2.** Valide DNS e HTTPS de `assin.art` e `www.assin.art`.
  - [ ] **API-19.3.** Execute o E2E anônimo: cadastro, upload maior que 2 MB, publicação, `/{username}`, `/track` e `/r/{id}`.
  - [ ] **API-19.4.** Reinicie o Web App e confirme a persistência de banco e uploads.
  - [ ] **API-19.5.** Documente o rollback com ZIP anterior e backup do banco.

**Localização & Referências:**

- **Rota / URL:** API backend (`/api/v1/*`)
- **Arquivos / Pastas:** `context/DEPLOY_PLAN.md`
- **Docs / Assets:** `context/tasks_backend.md` | `context/SECURITY_AUDIT.md` | `context/DEPLOY_PLAN.md`

---

### Ordem sugerida e dependências

1. `SEC-14` (reverificar `SEC-01`..`SEC-03`) ➔ bloqueia o deploy.
2. `SEC-13` ➔ `API-16`.
3. `SEC-06` + `SEC-09` ➔ `API-14`.
4. `SEC-04` ➔ `API-18`.
5. `SEC-05`, `SEC-07`, `SEC-08`, `SEC-10`, `SEC-11`, `SEC-12` em paralelo (listas de arquivos disjuntas, exceto `AuthTest.php`).
6. `API-15` ➔ `API-17` ➔ `API-19`.

---

## ✉️ Confirmação de E-mail, Contato e Carga Containerizada (`API-20..21 DONE` | `API-22..23 FLUXO E`)

### [API-20] Enviar E-mail de Confirmação Real e Validar o Token em /auth/email/verify

- **Estado no Backlog:** Concluída (`1cbcb08` em `develop` via Orca `run_58b77ee84902` — `447 passed, 3 skipped`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1-back/issues/3)

**Contexto Bruto:**

> Tinha que criar tasks pra confirmação de e-mail: depois que o usuário criar a conta, ele confirma o e-mail antes de liberar a rota /editor no frontend.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** Nenhum e-mail sai hoje. `register` e `resendVerification` só retornam sucesso e `MAIL_MAILER=log`. `verifyEmail` é público, ignora o `token` que o frontend envia e marca qualquer e-mail como verificado. `OnboardingController::saveButtons` (L292-293) também grava `email_verified_at` sozinho.
- **Solução Proposta:** Gere um token aleatório e guarde só o hash com expiração. Envie um `Mailable` com o link `FRONTEND_URL/confirmacao-email?email=...&token=...`. Valide o token em `POST /auth/email/verify`. Remova a auto-verificação do onboarding. Conclui o `SEC-01`.
- **OBS:** Decisão pendente do usuário: provedor de e-mail. O Azure App Service não oferece SMTP. Opções: Azure Communication Services Email, Resend, Brevo ou Mailgun. Retrocompatibilidade (Regra 13): mantenha `email_verified_at` nas respostas e o contrato `{ email, token }` já usado em `auth.service.ts`.
- **Como Executar (Subtarefas):**
  - [ ] **API-20.1.** Crie a migration `email_verification_tokens` (`email`, `token_hash`, `expires_at`, `created_at`).
  - [ ] **API-20.2.** Crie o `Mailable` `VerifyEmailMail` e a view em `resources/views/emails/verify-email.blade.php`.
  - [ ] **API-20.3.** Dispare o e-mail em `AuthController::register` e em `resendVerification`, com `throttle` de 60 s por e-mail.
  - [ ] **API-20.4.** Valide `email` e `token` em `verifyEmail`. Retorne `422` para token inválido ou expirado e invalide o token após o uso.
  - [ ] **API-20.5.** Responda `200` genérico em `resendVerification` para e-mail inexistente ou já verificado (sem enumeração).
  - [ ] **API-20.6.** Remova a escrita de `email_verified_at` em `OnboardingController::saveButtons`.
  - [ ] **API-20.7.** Confirme que `register`, `login` e `auth/me` retornam `email_verified_at`.
  - [ ] **API-20.8.** Configure `MAIL_*` e `FRONTEND_URL` em `.env.example` e nos App Settings (`API-17`).
  - [ ] **API-20.9.** Cubra o fluxo em `tests/Feature/EmailVerificationTest.php` com `Mail::fake()`.
  - [ ] **API-20.10.** Atualize `context/api_contracts.md` e `context/data_dictionary.md`.

**Localização & Referências:**

- **Rota / URL:** `/api/v1/auth/email/verify` | `/api/v1/auth/email/resend`
- **Arquivos / Pastas:** `database/migrations/` | `app/Mail/VerifyEmailMail.php` | `resources/views/emails/verify-email.blade.php` | `app/Http/Controllers/Api/AuthController.php` | `app/Http/Controllers/Api/Concerns/ManagesPasswordAndEmail.php` | `app/Http/Controllers/Api/OnboardingController.php` | `routes/api_auth.php` | `.env.example` | `tests/Feature/EmailVerificationTest.php`
- **Docs / Assets:** `context/api_contracts.md` | pareada com `CAD-11` (frontend)

---

### [API-21] Bloquear a Publicação do Perfil sem E-mail Verificado

- **Estado no Backlog:** Concluída (`2866d18` em `develop` via Orca `run_58b77ee84902` — `454 passed, 3 skipped`)
- **GitHub Project (#2):** Status `DONE` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1-back/issues/2)

**Contexto Bruto:**

> Confirmação de e-mail antes de liberar o /editor. O bloqueio só no frontend pode ser burlado chamando a API direto.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** `PATCH /api/v1/profile` aceita `is_published: true` mesmo com `email_verified_at` nulo. Uma conta com e-mail de terceiro pode publicar uma página pública.
- **Solução Proposta:** Rejeite `is_published: true` com `422` (`code: email_not_verified`) enquanto `email_verified_at` for nulo. Mantenha o onboarding e a edição liberados.
- **OBS:** Decisão pendente do usuário: confirme se o backend também bloqueia ou se só o frontend bloqueia o `/editor`. Não use o middleware `verified` nas rotas de onboarding: o frontend as chama antes da confirmação. Depende de `API-20`.
- **Como Executar (Subtarefas):**
  - [ ] **API-21.1.** Valide `email_verified_at` em `UpdateProfileRequest` ou em `ProfileController::update` quando `is_published` ou `publication_status` for `published`.
  - [ ] **API-21.2.** Retorne `422` com `code: email_not_verified` e mensagem em português.
  - [ ] **API-21.3.** Cubra o bloqueio e a liberação após a verificação em `tests/Feature/ProfilePublicationTest.php`.
  - [ ] **API-21.4.** Atualize `context/api_contracts.md` com o novo erro.

**Localização & Referências:**

- **Rota / URL:** `/api/v1/profile`
- **Arquivos / Pastas:** `app/Http/Requests/UpdateProfileRequest.php` | `app/Http/Controllers/Api/ProfileController.php` | `tests/Feature/ProfilePublicationTest.php`
- **Docs / Assets:** `context/api_contracts.md` | pareada com `CAD-11` (frontend)

---


### [API-22] Ativar E-mail de Contato (`sac@assin.art`) com Encaminhamento para Gmail Pessoal

- **Estado no Backlog:** Preparação versionável integrada (`763d540` em `develop`); aguardando configuração manual de DNS na **Hostinger** e validação de entrega no Gmail (`API-22.1..22.4`, `API-22.7`)
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1-back/issues/4)

**Contexto Bruto:**

> Tasks também pra ativar aquele e-mail de contato que tá na landing page, pra encaminhar os e-mails pro meu Gmail pessoal.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** A landing page (`landing.content.ts`) e `/ajuda` (`institutional.content.ts`) exibem `sac@assin.art` com links `mailto:`. O backend usa `contato@assin.art` em `MAIL_FROM_ADDRESS`. O domínio `assin.art` ainda não tem registros MX nem encaminhamento ativo, fazendo mensagens enviadas retornarem erro.
- **Solução Proposta:** Configure roteamento gratuito no DNS de `assin.art` (recomendado: Cloudflare Email Routing; alternativa: ImprovMX ou encaminhamento do registrador) criando os aliases `sac@assin.art` e `contato@assin.art` com destino ao Gmail pessoal. Publique registros `MX`, `SPF` e `DMARC` compatíveis com o provedor transacional da `API-20`. Documente os registros DNS e o teste real em `context/DEPLOY_PLAN.md`.
- **OBS:** Nunca grave o Gmail pessoal do proprietário em arquivos versionados no Git; configure o destino apenas no painel de roteamento ou em segredos privados. Se o registrador já estiver no Cloudflare DNS com GitHub Pages, o Cloudflare Email Routing é nativo e gratuito.
- **Como Executar (Subtarefas):**
  - [ ] **API-22.1.** Escolha o serviço de encaminhamento (Cloudflare Email Routing ou ImprovMX) e confirme o Gmail pessoal de destino no painel.
  - [ ] **API-22.2.** Publique os registros `MX` exigidos pelo provedor no DNS de `assin.art`.
  - [ ] **API-22.3.** Crie as regras de encaminhamento para `sac@assin.art` e `contato@assin.art` apontando para o Gmail pessoal.
  - [ ] **API-22.4.** Publique os registros `TXT` de `SPF` e `DMARC` (`p=none` inicial) combinando o roteador de entrada e o remetente de `API-20`.
  - [ ] **API-22.5.** Alinhe `MAIL_FROM_ADDRESS` e `MAIL_REPLY_TO_ADDRESS` em `.env.example` para usar o domínio `@assin.art`.
  - [ ] **API-22.6.** Documente os registros DNS e o checklist de validação em `context/DEPLOY_PLAN.md`.
  - [ ] **API-22.7.** Valide `dig MX assin.art` e envie um e-mail externo real para `sac@assin.art` confirmando o recebimento no Gmail pessoal.

**Localização & Referências:**

- **Rota / URL:** DNS `assin.art` | `mailto:sac@assin.art` na Landing Page e `/ajuda`
- **Arquivos / Pastas:** `.env.example` | `context/DEPLOY_PLAN.md` | `assinart_v1/src/content/landing/landing.content.ts` *(somente leitura)*
- **Docs / Assets:** `context/DEPLOY_PLAN.md` | relacionada com `API-20`

---

### Ordem sugerida e dependências (confirmação de e-mail, redirecionamento e contato)

1. `API-20` (backend) ➔ `CAD-11` (frontend): o frontend depende do token real.
2. `API-21` (backend) depende de `API-20` e de uma decisão do usuário.
3. `FIX-GLB-22` não depende do backend. Execute após `CAD-11` para reaproveitar a guarda e o estado `isEmailVerified`.
4. `API-20` conclui parte do `SEC-01` (`SEC-14`). Não execute os dois em paralelo (mesmos arquivos).
5. `API-22` (encaminhamento `sac@assin.art` -> Gmail) independe de código, mas coordene o registro `SPF` com o provedor escolhido em `API-20`.

---

## 🧪 Teste de Carga em Container (Simulação Azure `B1`) — `BACKLOG - FLUXO B`

### [API-23] Simular Teste de Carga Local em Container com Limites do Azure App Service (B1)

- **Estado no Backlog:** Infraestrutura de simulação integrada (`0905945` em `develop`); aguardando inicialização de `docker.socket`/`docker.service` para executar ao vivo (`API-23.5`)
- **GitHub Project (#2):** Status `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` | Domínio `GLB (Global)` | Etapa `Etapa 3 (Testes/Docs)` (https://github.com/pinhogo/assinart_v1-back/issues/20)

**Contexto Bruto:**

> coloca nas tasks pra simular teste de carga local em um container com as caractericas limitadas simulando o ambiente da azure.

**Pré-Tasks / Subtarefas Técnicas (Gerado por IA — Padrão ASD-STE100):**

- **Problema:** A `API-18` (`#11`) validou o SQLite `WAL` em um teste sequencial local no PHPUnit, mas ainda não existe um ambiente containerizado local que reproduza os limites reais de hardware e runtime do Azure App Service Linux `B1` (`1 vCPU`, `1,75 GB RAM`, PHP 8.3-FPM + Nginx, `azure/nginx.conf`, `public/.user.ini` e volume `/home/data`) sob requisições concorrentes.
- **Solução Proposta:** Criar um ambiente Docker/Compose local limitado a `1.0 vCPU` e `1792 MB` de RAM espelhando o runtime do `azure/` (`PHP 8.3-FPM` + `Nginx`), junto de um script reproduzível de carga concorrente em `POST /api/v1/track`, `GET /r/{id}`, `GET /{username}` e `GET /up`, registrando métricas de latência, erros `5xx` e eventuais `database is locked`.
- **OBS:** Complementa `API-18` (`#11`) e fornece base empírica para decidir entre subir o MVP em SQLite `WAL` ou virar direto para PostgreSQL (`API-12` / `#327`). Requer Docker Engine/Compose instalado no host ou execução em runner Linux com Docker (ex.: GitHub Actions `ubuntu-latest`) caso a máquina local não tenha Docker instalado.
- **Como Executar (Subtarefas):**
  - [ ] **API-23.1.** Crie `docker/azure-sim/Dockerfile` com PHP 8.3-FPM + Nginx (`mbstring`, `fileinfo`, `pdo_sqlite`, `pdo_pgsql`, `gd`), copiando `azure/nginx.conf`, `public/.user.ini` e aplicando o boot equivalente ao `azure/startup.sh`.
  - [ ] **API-23.2.** Crie `docker/azure-sim/compose.yml` aplicando os limites do Azure `B1` (`cpus: '1.0'`, `mem_limit: 1792m`, 1 instância, `DB_CONNECTION=sqlite`, `DB_SQLITE_JOURNAL_MODE=WAL`, `DB_SQLITE_BUSY_TIMEOUT=5000`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`) com volume mapeando `/home/data`.
  - [ ] **API-23.3.** Crie `scripts/load-test-azure-sim.sh` (usando `k6` em container auxiliar ou workers paralelos configuráveis) disparando carga concorrente simultânea contra `POST /api/v1/track`, `GET /r/{id}`, `GET /{username}` e `GET /up`.
  - [ ] **API-23.4.** Adicione suporte opcional no script/Compose para rodar a mesma bateria contra PostgreSQL em container e comparar p95, throughput e uso de memória/CPU com o SQLite `WAL`.
  - [ ] **API-23.5.** Execute a bateria localmente (ou em workflow manual `workflow_dispatch` com Docker), colete latência p50/p95, taxa de erro e logs do Laravel/Nginx, e registre o resultado e o critério de corte SQLite -> PostgreSQL em `context/DEPLOY_PLAN.md`.

**Localização & Referências:**

- **Rota / URL:** `/api/v1/track` | `/r/{id}` | `/{username}` | `/up`
- **Arquivos / Pastas:** `docker/azure-sim/Dockerfile` | `docker/azure-sim/compose.yml` | `scripts/load-test-azure-sim.sh` | `context/DEPLOY_PLAN.md`
- **Docs / Assets:** `context/DEPLOY_PLAN.md` | `context/tasks_backend.md` | relacionada com `API-18` (`#11`) e `API-12` (`#327`)
