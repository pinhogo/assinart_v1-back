# Plano de Tasks de <NOME_DO_MODULO> (`context/tasks_<dominio>.md`)

Checklist oficial das tasks da rota `<ROTA_ALVO>` do **Assin.art** (`assinart_v1`).
Origem: itens `<ID_INICIAL>` a `<ID_FINAL>` de [`<arquivo_origem.md>`](<arquivo_origem.md>) e escopo definido pelo utilizador.

---

> [!CAUTION]
> ## WORKFLOW INTEGRADO DE GERAÇÃO E EXECUÇÃO DE TASKS (PILAR 2)
> 1. Substitua todos os campos `[...]` pelos dados reais do domínio analisado.
> 2. Inspecione o código com `grep`/`glob` antes de planear e preencha a tabela **Achados da Leitura do Código** (`F1`, `F2`...).
> 3. Escreva todo o documento no padrão **ASD-STE100 (Regra 11)**: voz ativa, verbos no imperativo ("Crie", "Edite", "Remova", "Execute"), uma instrução por frase e máximo de 20 palavras por subtarefa.
> 4. Nunca adicione `npm run lint`, `npm run build` ou `npm test` ao final de cada task individual da Etapa 1. A validação ocorre **apenas uma vez no encerramento do lote (Regra 9)**.
> 5. Atribua um ID único por domínio a cada task (ex.: `CAD-05`, `FON-03`, `BOT-02`), mantenha rastreabilidade entre o card do GitHub Project nº 2 (`https://github.com/users/pinhogo/projects/2`), a documentação interna e a mensagem de commit, e sincronize o status das colunas via GitHub CLI (`gh`).
> 6. **Transição do Fluxo B para Execução e Paridade Tripla (Regra 14):**
>    - Ao gerar um plano `context/tasks_<dominio>.md` a partir de itens `BACKLOG - FLUXO B` do `context/BACKLOG_MELHORIAS.md`, defina `- **Estado no Backlog:** Em Tratativa` no `BACKLOG_MELHORIAS.md`.
>    - Atualize o card correspondente no GitHub Project nº 2 (`https://github.com/users/pinhogo/projects/2`) e o arquivo `coordination/BOARD.md` com as informações estruturadas do esqueleto (mantendo exatamente o mesmo texto e os mesmos checkboxes das tasks e subtarefas).
>    - Mova o card no GitHub Project nº 2 de `BACKLOG - FLUXO B` para `EXECUÇÃO - FLUXO C` (com `Etapa`: `Etapa 1 (Execução)`) via `gh project item-edit`.
>    - Ao concluir cada etapa, atualize simultaneamente os checkboxes em `context/tasks_<dominio>.md`, `coordination/BOARD.md` e no card do GitHub Project nº 2 (`EXECUÇÃO - FLUXO C` / `Etapa 1 (Execução)` ➔ `CORREÇÃO E REVISÃO - FLUXO D` / `Etapa 2 (Revisão)` ➔ `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` / `Etapa 3 (Testes/Docs)` ➔ `DONE`).
>    - Ao atingir `DONE`, preencha o resumo de entrega em `context/ALINHAMENTO_COMPORTAMENTOS.md` para auditoria do usuário e mova o arquivo `tasks_<dominio>.md` concluído para `context/tasks_concluidas/`.

---

## Diretrizes Obrigatórias (`context/rules.md` e `context/AUDITORIA.md`)

> [!IMPORTANT]
> **Aprovação do plano (Regra 6) e GitHub Project nº 2 (Regra 14):** Este documento lista previamente todos os ficheiros de cada task e vincula cada item ao seu ID único no GitHub Project nº 2 (`https://github.com/users/pinhogo/projects/2`).
> As tasks respeitam o limite de até 3 unidades lógicas novas (exceções aprovadas: `[nenhuma ou listar tasks]`).

---

## Achados da Leitura do Código (Base das Tasks)

| ID | Achado | Evidência no Código | Efeito no Plano |
| :--- | :--- | :--- | :--- |
| **F1** | `[Descreva o problema ou comportamento atual]` | `[Arquivo.tsx / hook.ts (linhas X-Y)]` | `[Ação necessária. Ex.: Criar hook na Task T1]` |
| **F2** | `[Componente X já existe e atende 80% do caso]` | `[NomeComponente.tsx aceita props A e B]` | `[Adicionar prop opcional retrocompatível na Task T2]` |
| **F3** | `[Falta contrato de API / mock para a operação Y]` | `[dominio.service.ts não possui método Z]` | `[Estender serviço e handler mock na Task T3]` |

---

## Decisões Arquiteturais e de Produto

| ID | Decisão | Resolução | Ação Pendente |
| :--- | :--- | :--- | :--- |
| **D1** | `[Tema da decisão 1]` | **Aprovada / Padrão.** `[Regra decidida pelo utilizador]` | `[Aplicar na Task T1 ou Nenhuma]` |
| **D2** | `[Tema da decisão 2]` | **Confirmada.** `[Regra decidida pelo utilizador]` | `[Parar após Task T2 e aguardar revisão]` |

---

## Quadro Resumo das Tasks e Etapas (Regra 8)

| Etapa / Task | Item | Domínio | Descrição Resumida (ASD-STE100) | Ficheiros Principais | Executor | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Etapa 1 — T1** | `[ID-01]` | `[dominio/sub]` | `[Resumo em até 20 palavras]` | `[Arquivo1.ts, Arquivo2.tsx]` | Agente principal | ⏳ Pendente |
| **Etapa 1 — T2** | `[ID-02]` | `[dominio/ui]` | `[Resumo em até 20 palavras]` | `[Componente.tsx, Componente.style.ts]` | IA simples | ⏳ Pendente |
| **Etapa 1 — T3** | `[ID-03]` | `[dominio/api]` | `[Resumo em até 20 palavras]` | `[dominio.service.ts, mockHandler.ts]` | Agente principal | ⏳ Pendente |
| **Etapa 2 — Revisão** | — | `[code-review]` | Audite escopo, MVC, limites de linhas e responsividade 390px. | Tasks `T1` a `T3` | Agente principal | ⏳ Pendente |
| **Etapa 3 — Final** | — | `[fase-final]` | Crie testes em `test/`, exemplos no `/playground` e atualize catálogos. | `test/**`, `PlaygroundView.tsx`, `context/*` | IA simples | ⏳ Pendente |

### Divisão de Trabalho e Ordem de Execução

1. **Agente Principal** executa as tasks estruturais/complexas: `[T1, T3]`.
2. **IA Simples** executa as tasks de composição visual e testes: `[T2, Etapa 3]`.
3. Ordem de execução do lote: `[T1 -> T2 -> T3]`.
4. Pontos de paragem obrigatória: `[Nenhum ou Pare após T1 e aguarde aprovação]`.

### Grafo de Dependências

~~~mermaid
flowchart LR
  T1 --> T2 --> T3
~~~

### Pendências Futuras (Fora do MVP Atual)

| ID | Pendência | Origem | Observação |
| :--- | :--- | :--- | :--- |
| **P1** | `[Funcionalidade adiada]` | `[D1 / F3]` | `[Motivo do adiamento. Crie task futura após este lote]` |

---

## 1. EXECUÇÃO - FLUXO C (`Etapa 1 (Execução)`)

> [!IMPORTANT]
> **Instruções Operacionais da Etapa 1 (Regras 8, 9, 10 e 14):**
> - Trabalhe exclusivamente na branch `develop`.
> - Sincronize o card do ID único no GitHub Project nº 2 para a coluna `EXECUÇÃO - FLUXO C` (`Etapa`: `Etapa 1 (Execução)`) via GitHub CLI (`gh`).
> - Implemente somente código de produção (`models`, `controllers`, `components`, `views`, `services`, `content`, `style`).
> - Não crie testes em `test/`, não edite `/playground` e não atualize `inventory.md` nem `context_components.md` nesta etapa.
> - Ao concluir cada task: marque `[x]`, registe os ficheiros em `context/AUDITORIA.md`, faça um **commit atómico semântico contendo o ID único** (`feat(ID-XX):`, `fix(ID-XX):`, `refactor(ID-XX):`) e avance imediatamente para a próxima task **sem executar lint, build ou test**.
> - Execute a validação consolidada (`git diff --stat`, `npm run lint` e `npm run build`) **apenas uma vez, ao encerrar a última task do lote**.

---

### Task T1: `[dominio/model-ou-api]` `[Título curto da Task em voz ativa]` (`[ID-ORIGEM]`)

**Contexto:** `[Explique em 1 a 2 frases curtas o problema atual e a solução proposta com base nos achados F1/D1]`.

#### Contrato de Dados / API (Opcional — Remova se a task for puramente visual)

| Método e Rota | Auth | Corpo / Parâmetros | Resposta |
| :--- | :--- | :--- | :--- |
| `[GET / POST / PUT / DELETE /api/v1/rota]` | `[Bearer ou Pública]` | `[Campos do payload ou query params]` | `[DTO de retorno e status HTTP]` |

- **Busca de Reuso (Regra 6):** `[Declare o que procurou em inventory.md / grep e por que vai reutilizar, estender ou criar novo]`.
- **Unidades Lógicas Novas:** `[0 a 3 unidades (lembre: .tsx + .style.ts + .content.ts = 1 unidade)]`.
- **Nova Dependência (`npm install`):** `[Nenhuma ou Nome do pacote + justificativa técnica após verificar package.json]`.
- **Ficheiros a Ler:** `[caminho/completo/arquivo1.ts]`, `[caminho/completo/arquivo2.ts]`.
- **Ficheiros a Criar:**
  - `src/models/<dominio>/<nome>.model.ts` (≤ 150 linhas de código efetivo)
  - `src/content/<dominio>/<nome>.content.ts` (isento do limite de linhas)
- **Ficheiros a Editar:** `src/services/<dominio>.service.ts`, `context/AUDITORIA.md`.
- **Ficheiros Fora de Escopo:** `[Ficheiros próximos que NÃO devem ser tocados]`.
- **Subtarefas (ASD-STE100):**
  - [ ] **T1.1.** Defina a interface `<NomeDto>` e os tipos puros em `src/models/<dominio>/<nome>.model.ts`.
  - [ ] **T1.2.** Crie a função pura `<nomeDaFuncao>` em `<nome>.model.ts` sem importar React, DOM ou `services`.
  - [ ] **T1.3.** Adicione todos os textos, mensagens de erro e rótulos ARIA em `src/content/<dominio>/<nome>.content.ts`.
  - [ ] **T1.4.** Estenda `src/services/<dominio>.service.ts` com o método `<nomeMetodo>` (mantenha ≤ 150 linhas).
  - [ ] **T1.5.** Registe os ficheiros criados e editados em `context/AUDITORIA.md` e faça o commit atómico da `T1`.
- **Critérios de aceite:** `[Condições objetivas e testáveis que comprovam que a task funciona]`.
- **Casos de borda:** `[Lista vazia, falha de rede 500, erro de validação 422, valores nulos]`.

---

### Task T2: `[dominio/ui-ou-view]` `[Título curto da Task em voz ativa]` (`[ID-ORIGEM]`)

**Contexto:** `[Explique se vai adaptar um componente existente com props opcionais ou criar uma nova View/Componente]`.

- **Busca de Reuso (Regra 6):** `[Ex.: Procurei em context_components.md; reutilizarei NeoButton e adaptarei ComponenteX com a prop opcional Y]`.
- **Unidades Lógicas Novas:** `[Ex.: 1 unidade lógica (NomeComponente.tsx + NomeComponente.style.ts)]`.
- **Nova Dependência (`npm install`):** `[Nenhuma]`.
- **Ficheiros a Ler:** `[caminho/completo/ComponenteExistente.tsx]`, `src/components/style/tokens.ts`.
- **Ficheiros a Criar:**
  - `src/components/<grupo>/<NomeComponente>.tsx` (≤ 120 linhas de código efetivo)
  - `src/components/<grupo>/style/<NomeComponente>.style.ts`
- **Ficheiros a Editar:** `src/views/<dominio>/<NomeView>.tsx` (manter ≤ 120 linhas), `context/AUDITORIA.md`.
- **Ficheiros Fora de Escopo:** `src/views/landing/*`, `src/components/style/tokens.ts`, `src/assets/*`.
- **Subtarefas (ASD-STE100):**
  - [ ] **T2.1.** Declare todas as classes Tailwind em `<NomeComponente>.style.ts` usando `brutalBorders`, `brutalShadows` e base 390px.
  - [ ] **T2.2.** Crie `<NomeComponente>.tsx` consumindo apenas estilos de `<NomeComponente>.style.ts` e textos via `props`.
  - [ ] **T2.3.** Adicione suporte a navegação por teclado e atributos `aria-*` vindos do ficheiro `.content.ts`.
  - [ ] **T2.4.** Integre `<NomeComponente>` em `<NomeView>.tsx` consumindo os dados do controller `use<Nome>Controller`.
  - [ ] **T2.5.** Registe os ficheiros criados e editados em `context/AUDITORIA.md` e faça o commit atómico da `T2`.
- **Critérios de aceite:** `[Renderização fiel ao Neo-Brutalismo em 390px e 1440px, zero Tailwind e zero strings fixas no JSX]`.
- **Casos de borda:** `[Textos longos truncados, clique duplo bloqueado durante loading, estado vazio]`.

---

### Fecho da Etapa 1 (Validação Consolidada — Regra 9)

- [ ] **E1.FIM.1.** Execute `git diff --stat` e confirme que nenhum ficheiro fora das listas das tasks foi alterado.
- [ ] **E1.FIM.2.** Execute `npm run lint` e confirme 0 erros e 0 warnings.
- [ ] **E1.FIM.3.** Execute `npm run build` e confirme compilação limpa sem erros de TypeScript.
- [ ] **E1.FIM.4.** Emita o Relatório de Entrega da Etapa 1 (Regra 12).

---

## 2. CORREÇÃO E REVISÃO - FLUXO D (`Etapa 2 (Revisão)`)

> [!IMPORTANT]
> **Instruções da Etapa 2 (`TechLead-Reviewer` / Modelo Principal — Regras 8 e 14):**
> - Execute após a conclusão de todas as tasks da Etapa 1.
> - Sincronize o card do ID único no GitHub Project nº 2 para a coluna `CORREÇÃO E REVISÃO - FLUXO D` (`Etapa`: `Etapa 2 (Revisão)`) via GitHub CLI (`gh`).
> - Corrija violações arquiteturais diretamente. Não crie funcionalidades novas fora do escopo.

- [ ] **2.1 (Branch e Escopo).** Confirme que a branch ativa é `develop`. Execute `git diff --stat` e reverta qualquer ficheiro fora do escopo.
- [ ] **2.2 (Fronteiras MVC — Regra 2).** Confirme zero importação de `services`, `fetch`, `axios`, `localStorage` ou `sessionStorage` em `views/` e `components/`.
- [ ] **2.3 (Limites de Linhas — Regra 5).** Confirme Views e Componentes ≤ 120 linhas de código efetivo e Hooks e Models ≤ 150 linhas (descontando comentários e isentando `*.content.ts`).
- [ ] **2.4 (Zero Tailwind e Strings no JSX — Regras 1 e 3).** Confirme ausência total de classes Tailwind literais e textos fixos em ficheiros `.tsx`.
- [ ] **2.5 (Reuso e Sem Gambiarra — Regra 6).** Confirme que adaptações em componentes existentes usam props retrocompatíveis e não quebraram outras telas.
- [ ] **2.6 (Modelos Puros — Regra 2).** Confirme que ficheiros em `src/models/` não importam React, JSX, DOM nem `services`.
- [ ] **2.7 (Código Órfão).** Procure imports de ficheiros substituídos e remova componentes ou hooks que ficaram sem uso.
- [ ] **2.8 (Responsividade e Neo-Brutalismo — Regra 4).** Verifique o layout em 390px (Mobile-First) e 1440px (Desktop) sem quebras ou sobreposições.
- [ ] **2.9 (Acessibilidade).** Verifique navegação por teclado (`Tab`, `Enter`, `Escape`, setas) e atributos `role` e `aria-*`.
- [ ] **2.10 (Auditoria Cumulativa — Regra 10).** Confirme que todas as alterações da Etapa 1 e da Etapa 2 constam em `context/AUDITORIA.md` sem sobrescrita.
- [ ] **2.11 (Validação Consolidada — Regra 9).** Execute `npm run lint` (0 erros/avisos) e `npm run build` uma única vez ao concluir as correções.
- [ ] **2.12 (Relatório — Regra 12).** Emita o Relatório de Entrega da Etapa 2.

---

## 3. TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E (`Etapa 3 (Testes/Docs)`)

> [!IMPORTANT]
> **Instruções da Etapa 3 (Modelo de IA Simplificado — Regras 8, 9, 10, 11, 12 e 14):**
> - Execute somente após a aprovação da Etapa 2.
> - Sincronize o card do ID único no GitHub Project nº 2 para `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` (`Etapa`: `Etapa 3 (Testes/Docs)`) ao iniciar e para `DONE` ao concluir via GitHub CLI (`gh`).
> - Escreva toda a documentação e registos no padrão **ASD-STE100**.
> - Execute `npm test`, `npm run lint` e `npm run build` **apenas uma vez, ao final da Etapa 3**, e emita o Relatório de Entrega.

### 3.1 Testes Unitários e de Integração (`test/`)

- [ ] **3.1.1 (`T1`).** Teste as funções puras e casos de borda de `<nome>.model.ts` em `test/models/<dominio>/<nome>.model.test.ts`.
- [ ] **3.1.2 (`T1`).** Teste `<dominio>.service.ts` e os handlers mock (sucesso e erros 4xx/5xx) em `test/services/<dominio>.service.test.ts`.
- [ ] **3.1.3 (`T1/T2`).** Teste o hook `use<Nome>Controller.ts` (estados `idle`, `loading`, `success`, `error`) em `test/controllers/hooks/<dominio>/use<Nome>Controller.test.ts`.
- [ ] **3.1.4 (`T2`).** Teste `<NomeComponente>.tsx` e `<NomeView>.tsx` (renderização, interações, teclado e acessibilidade) em `test/components/<grupo>/` e `test/views/<dominio>/`.

### 3.2 Catálogo Interativo no Playground (`/playground`)

- **Ficheiros a Editar:** `src/content/playground/playground.content.ts` e `src/views/playground/PlaygroundView.tsx`.
- [ ] **3.2.1.** Adicione os textos e metadados dos novos componentes em `src/content/playground/playground.content.ts`.
- [ ] **3.2.2.** Renderize exemplos interativos (estados padrão, ativo, desativado e erro) dos novos componentes em `src/views/playground/PlaygroundView.tsx`.

### 3.3 Inventário, Documentação Viva e Validação Final (`context/`)

- [ ] **3.3.1.** Adicione os novos hooks, models, services, content e styles em `context/inventory.md` usando o padrão ASD-STE100.
- [ ] **3.3.2.** Documente os componentes visuais criados ou novas props em `context/context_components.md` usando o padrão ASD-STE100.
- [ ] **3.3.3.** Atualize `context/AUDITORIA.md` com a contagem de linhas dos novos ficheiros.
- [ ] **3.3.4.** Adicione as linhas finais desta etapa na tabela de `context/AUDITORIA.md`.
- [ ] **3.3.5.** Faça o commit atómico da Etapa 3 (`test:` / `docs:`).
- [ ] **3.3.6 (Validação Única — Regra 9).** Execute `git diff --stat`, `npm test` (100% a passar), `npm run lint` (0 erros) e `npm run build` (compilação limpa).
- [ ] **3.3.7 (Relatório Final — Regra 12).** Entregue o Relatório de Entrega abaixo na resposta final ao utilizador:

~~~markdown
## Relatório de Entrega
- Etapa executada (1. Execução | 2. Correção e Revisão | 3. Testes, Playground e Documentação):
- Unidades/arquivos criados (com busca de reuso e justificativa):
- Arquivos editados:
- Arquivos removidos/movidos:
- Registro adicionado em context/AUDITORIA.md: sim/não
- Paridade sincronizada entre tasks_<dominio>.md, coordination/BOARD.md e GitHub Project nº 2: sim/não
- Escopo conferido no git diff --stat (sem arquivos fora da lista): sim/não
- Limite de linhas de código respeitado (Views/Comp ≤ 120; Hooks/Models ≤ 150): sim/não
- Resultado consolidado de npm run lint / npm run build / npm test:
- Dúvidas ou decisões pendentes:
~~~

---

## Fora de Escopo deste Plano

- `[Item 1 que não deve ser tocado neste plano (ex.: alterações na Landing Page / HomeMockup)]`.
- `[Item 2 que fica para outra task (ex.: implementação real no repositório backend)]`.