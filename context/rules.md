# Regras Oficiais do Projeto Assin.art (`context/rules.md`)

Diretrizes operacionais, arquiteturais e de qualidade obrigatórias para agentes de IA e desenvolvedores no repositório `assinart_v1`.

---

> [!CAUTION]
> ## REGRA 0: CONSULTA ÚNICA POR ORDEM E OTIMIZAÇÃO DE TOKENS
>
> 1. **Leia uma única vez:** Consulte `context/rules.md` apenas uma vez no início de cada ordem ou lote de tarefas do usuário.
> 2. **Não releia entre tarefas:** Execute múltiplas tarefas da mesma ordem em sequência sem reler este arquivo.
> 3. **Não releia antes de comandos:** Nunca leia este arquivo antes de executar comandos de terminal (`git`, `npm test`, `npm run lint`, `npm run build`).
> 4. **Economize tokens no chat:** Nunca reproduza o conteúdo integral de arquivos modificados na resposta. Use links markdown `[arquivo](caminho)`.

---

## 1. Arquitetura, Código e Design

### Regra 1: Zero Tailwind no JSX

- Proíba classes Tailwind diretamente no JSX de componentes (`src/components/`) ou views (`src/views/`).
- Declare todas as classes Tailwind em arquivos dedicados `<Nome>.style.ts` dentro da subpasta `style/` do respectivo domínio (ex.: `src/components/ui/style/NeoButton.style.ts`).
- Importe apenas constantes tipadas no arquivo `.tsx` e aplique-as na propriedade `className`.

### Regra 2: Arquitetura MVC e Fronteiras de Camadas

Organize os arquivos e restrinja as importações estritamente conforme as tabelas abaixo:

**Mapa de Pastas e Nomenclatura:**

| Tipo de Arquivo | Pasta Obrigatória | Convenção de Nome |
| :--- | :--- | :--- |
| Tipos, entidades e funções puras | `src/models/<dominio>/` | `<nome>.model.ts` |
| Hooks de controle (Controller) | `src/controllers/hooks/<dominio>/` | `use<Nome>.ts` |
| Contextos React | `src/controllers/contexts/` | `<Nome>Context.ts` |
| Telas e seções (View) | `src/views/<dominio>/` | `<Nome>View.tsx` |
| Componentes visuais atômicos | `src/components/<grupo>/` | `<Nome>.tsx` |
| Estilos (Tailwind) | `style/` ao lado do arquivo | `<Nome>.style.ts` |
| Chamadas de API e persistência | `src/services/` | `<dominio>.service.ts` |
| Textos e copies | `src/content/<dominio>/` | `<nome>.content.ts` |
| Ícones sociais | `src/assets/social-icons/` | `<nome>.svg` |
| Ícones de UI (SVG) | `src/components/icons/` | `<Nome>Icon.tsx` |
| Cores globais | `src/assets/colors.ts` | — |
| Testes automatizados | `test/` (espelhando `src/`) | `<Nome>.test.tsx` |

**Fronteiras de Importação:**

| Camada | Pode Importar | PROIBIDO Importar |
| :--- | :--- | :--- |
| **View** | `controllers`, `components`, `content`, `style` local, tipos de `models` | `services`, `fetch`, `axios`, `localStorage`, `sessionStorage` |
| **Component** | Outros `components`, `style`, `content` via props, tipos/funções de `models` | `controllers`, `services`, contextos de negócio, chamadas HTTP/Storage |
| **Controller** | `models`, `services`, `content`, outros `hooks` | `views`, `components` (JSX) |
| **Model** | Outros `models` | React, JSX, DOM, `services`, `controllers` |
| **Service** | Tipos de `models` | React, `controllers`, `views`, `components` |
| **Content** | Nada (apenas dados puros) | Qualquer outra camada |

### Regra 3: Centralização de Conteúdo (Zero Strings Fixas no JSX)

- Centralize todos os textos, títulos, labels, links institucionais e mensagens de erro/sucesso em `src/content/<dominio>/<nome>.content.ts`.
- Nunca declare strings literais visíveis ao usuário diretamente no JSX.

### Regra 4: Fidelidade ao Design Neo-Brutalista e Mobile-First

- **Estilo Visual:** Use bordas pretas sólidas (2px a 3px), cantos arredondados (`20px`, `28px`, `full`) e sombras pretas sólidas sem blur (`4px 8px 0px 0px #000`, `0px 8px 0px 0px #000`).
- **Interatividade Tátil:** Aplique efeito físico de clique nos botões (`active:translate-x-0.5 active:translate-y-1 active:shadow-none`).
- **Tokens:** Reutilize obrigatoriamente os tokens de `src/components/style/tokens.ts` (`brutalShadows`, `brutalBorders`).
- **Responsividade:** Desenvolva sob o padrão Mobile-First (base 390px) e expanda para desktop (1440px) com os breakpoints `sm:`, `md:` e `lg:` nos arquivos `.style.ts`.

### Regra 5: Limites de Linhas e Imposição Mecânica (Lint)

- **Limite para Views e Componentes:** Máximo de **120 linhas de código efetivo** por arquivo.
- **Limite para Hooks e Models:** Máximo de **150 linhas de código efetivo** por arquivo.
- **Exceções de contagem:**
  1. **Arquivos de conteúdo (`*.content.ts`):** Isentos do limite de linhas.
  2. **Comentários de código:** Não entram na contagem de 120 ou 150 linhas.
- **Proibição de burla:** Nunca adicione `eslint-disable`, `@ts-ignore` ou `@ts-expect-error`. Nunca afrouxe regras de lint ou TypeScript.
- **Entrada de agentes:** Mantenha `.github/copilot-instructions.md`, `AGENTS.md` e `CLAUDE.md` apontando para `context/rules.md`.

---

## 2. Reuso, Criação, Escopo e Proteção

### Regra 6: Reuso Obrigatório, Limite de Criação e Dependências

1. **Busque antes de criar:**
   - Execute busca rápida por termos (`grep` ou `glob`) antes de criar qualquer arquivo.
   - Consulte apenas o catálogo necessário uma única vez por ordem (`context/context_components.md` para componentes visuais; `context/inventory.md` para hooks, models e services; `context/api_contracts.md` para rotas CRUD, DTOs, mocks e esquema do banco).
2. **Critério de Reuso vs. Criação de Componente:**
   - **Priorize componentes existentes:** Reutilize o componente como está ou adapte-o com novas *props* opcionais retrocompatíveis que não alterem o comportamento em outros locais.
   - **Proíba gambiarras:** Se a adaptação exigir lógica forçada ("gambiarra"), violar o limite de 120 linhas ou comprometer outras telas, crie um componente novo e registre a justificativa técnica na *task*.
   - **Proíba cópia de código:** Nunca copie e cole o código de um componente para criar outro.
   - **Reutilize ícones:** Use exclusivamente os ícones de `src/assets/social-icons/` e `src/components/icons/`. Nunca crie SVGs inline repetidos.
3. **Limite de Criação por Tarefa:**
   - Crie no máximo **3 novas unidades lógicas por tarefa** sem um plano aprovado.
   - Considere o conjunto `.tsx` + `.style.ts` + `.content.ts` de um mesmo elemento como **1 única unidade lógica**.
4. **Proibição de Duplicatas e Nomes Genéricos:**
   - Mantenha apenas um serviço por domínio (`<dominio>.service.ts`). Se já existir, estenda-o.
   - Nunca use os sufixos `2`, `New`, `Copy`, `Temp`, `V2`, `Old`, nem nomes genéricos como `utils.ts` ou `helpers.ts` sem domínio.
5. **Controle de Novas Dependências (`npm install`):**
   - Instale pacotes apenas para resolver problemas complexos sem solução nativa simples.
   - Antes de rodar `npm install`: (1) verifique no `package.json` se já existe biblioteca similar instalada; (2) descarte pacotes para funções triviais; (3) documente na *task* o pacote instalado e a justificativa técnica.

### Regra 7: Escopo Estrito

1. **Declare a lista de arquivos:** Liste antes de iniciar a tarefa quais arquivos você vai ler, criar ou editar.
2. **Altere apenas o solicitado:** Nunca edite arquivos fora da lista da tarefa. Nunca crie novas rotas, contextos ou refatorações entre domínios fora do escopo descrito. Na dúvida, pergunte ao usuário antes de agir.
3. **Mantenha o `README.md` limpo:** Destine o `README.md` da raiz apenas para humanos (visão geral, stack e comandos). Guarde toda documentação e regras de IA dentro da pasta `context/`.

---

## 3. Fluxo de Execução, Validação, Branch e Entrega

### Regra 8: Fluxo Oficial de Execução e Ciclo Fechado de 3 Pilares

Opere todo o ciclo de desenvolvimento em 3 pilares contínuos com workflows e esqueletos integrados:

- **Pilar 1 — Backlog, Análise e Investigação (`context/BACKLOG_MELHORIAS.md`):** Receba contexto bruto (no arquivo ou via card *draft* no GitHub Project nº 2), desmembre em IDs únicos por domínio (`PREFIXO-XX`), investigue o codebase e estruture os 5 blocos técnicos (`BACKLOG - FLUXO A` ➔ `BACKLOG - FLUXO B`).
- **Pilar 2 — Tasks, Execução, Revisão e Documentação (`context/esqueleto_tasks.md` ➔ `context/tasks_<dominio>.md`):** Refine a pré-task do Fluxo B com achados do código (`F1..Fn`), atualize o card do GitHub Project nº 2 e o `coordination/BOARD.md` com o mesmo texto e os mesmos checkboxes, e execute as Etapas 1, 2 e 3 (`EXECUÇÃO - FLUXO C` ➔ `CORREÇÃO E REVISÃO - FLUXO D` ➔ `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` ➔ `DONE`).
- **Pilar 3 — Alinhamento, Auditoria e Correção (`context/ALINHAMENTO_COMPORTAMENTOS.md`):** Receba o contexto bruto de homologação pós-entrega (Desktop 1440px e Mobile 390px) e realimente o ciclo fechado com 3 destinos possíveis:
  - **Aprovado:** Mantenha o card em `DONE` e arquive a task em `context/tasks_concluidas/`.
  - **Reabertura de Task:** Reabra a task atual e retorne o card para `EXECUÇÃO - FLUXO C` ou `CORREÇÃO E REVISÃO - FLUXO D`.
  - **Novo Ajuste / Melhoria:** Gere novos itens `FIX-<DOM>-XX` ou novas pré-tasks diretamente em `context/BACKLOG_MELHORIAS.md` e no GitHub Project nº 2.

Execute todo plano de tarefas (`context/tasks_*.md`) seguindo estritamente estas 3 etapas:

#### 1. EXECUÇÃO DE TASKS
- **Responsável:** Modelo de IA Principal (alta capacidade).
- **Ação:** Implemente apenas código de produção (`models`, `controllers`, `components`, `views`, `services`, `content` e `style`).
- **Restrição:** Não crie testes em `test/`, não edite `/playground` e não atualize catálogos (`context/context_components.md`, `context/inventory.md` e `context/api_contracts.md`) nesta etapa.

#### 2. CORREÇÃO E REVISÃO DE CÓDIGO
- **Responsável:** Agente Revisor ou Modelo Principal em revisão.
- **Ação:** Audite o código da Etapa 1:
  1. Reverta qualquer alteração em arquivo fora da lista da *task*.
  2. Confirme o cumprimento das Regras 1, 2, 3 e 5 (zero Tailwind no JSX, zero strings fixas, fronteiras MVC e limites de 120/150 linhas de código).
  3. Corrija imediatamente qualquer violação arquitetural ou duplicação.

#### 3. TESTES, PLAYGROUND E DOCUMENTAÇÃO
- **Responsável:** Modelo de IA Simplificado (baixo custo de tokens).
- **Ação:** Execute após concluir a Etapa 2:
  1. Crie ou atualize os testes unitários e de integração na pasta `test/`.
  2. Adicione exemplos funcionais dos novos componentes em `src/views/playground/PlaygroundView.tsx`.
  3. Atualize `context/context_components.md`, `context/inventory.md` e `context/api_contracts.md` (sempre que uma task criar ou alterar `service`, `mock` ou `DTO`) seguindo o padrão ASD-STE100.

### Regra 9: Commits Atômicos, Saída Silenciosa e Validação Única no Fim do Lote

1. **Faça commits atômicos por tarefa:** Ao concluir cada tarefa de um checklist (`context/tasks_*.md`), marque `[x]` e execute um commit imediato com prefixo semântico (`feat:`, `fix:`, `style:`, `test:`, `docs:`, `chore:`).
2. **Proíba validações intermediárias:** Nunca execute `npm run lint`, `npm run build`, `npm test` ou `git diff` entre micro-tarefas do mesmo lote.
3. **Valide uma única vez no encerramento:** Execute `git diff --stat`, `npm run lint` (0 erros e 0 warnings), `npm run build` (`tsc -b && vite build --logLevel error`) e `npm test` (`100%` de aprovação), mais `npm run test:e2e` antes de PR para `main`, **exclusivamente uma vez** ao finalizar todas as tarefas da ordem do usuário.
4. **Adote saída compacta padrão e blocos de domínio (`GLB-09`):**
   - Execute `npm test` (`vitest run --reporter=agent --silent=passed-only`), `npm run build` (`tsc -b && vite build --logLevel error`), `npm run test:e2e` (`playwright test --reporter=line`) e comandos Git resumidos (`git status -s`, `git diff --stat`).
   - Em correções localizadas, teste apenas o bloco afetado com `npm run test:changed` ou `npm run test:<bloco>` (`auth`, `onboarding`, `editor`, `profile`, `services`, `global`); no fechamento do lote e no CI pré-`main`, execute a suíte completa `npm test`.
   - Use `npm run test:verbose` apenas quando precisar inspecionar logs completos de uma falha.
   - **Proibição estrita:** Nunca use `eslint --quiet` (viola a exigência de `0` warnings na Regra 9.3) nem `vite --logLevel silent` (oculta erros de build).

### Regra 10: Protocolo de Branch `develop` e Auditoria Cumulativa

1. **Use a branch oficial de desenvolvimento:** Trabalhe na branch `develop`.
2. **Bloqueie a `main`:** Nunca faça commit direto na `main`. A branch `main` recebe merges apenas após homologação aprovada em `context/ALINHAMENTO_COMPORTAMENTOS.md`.
3. **Registre na auditoria:** Adicione cada arquivo criado, editado ou utilizado na tabela de `context/AUDITORIA.md`.
4. **Preserve o histórico:** Adicione apenas novas linhas ao final da tabela. Nunca sobrescreva nem apague registros anteriores.

### Regra 11: Padrão de Escrita ASD-STE100

Aplique obrigatoriamente o padrão ASD-STE100 em novos documentos em `context/`, novas entradas em `context/tasks_*.md`, registros em `context/AUDITORIA.md` e respostas ao usuário (mantenha textos antigos já concluídos sem refatorar):

1. **Use apenas a voz ativa:** Nunca escreva na voz passiva.
2. **Inicie instruções no imperativo:** Comece cada item com um verbo de ação ("Crie", "Edite", "Remova", "Extraia", "Execute").
3. **Escreva frases curtas:** Use no máximo 20 palavras em instruções e 25 palavras em descrições ou registros de auditoria.
4. **Declare uma ação por frase:** Separe ações distintas em itens independentes.
5. **Use vocabulário controlado:** Adote um único termo técnico por conceito e elimine adjetivos subjetivos.

### Regra 12: Relatório de Entrega Obrigatório

Apresente na resposta final ao usuário, ao concluir o lote, o relatório padronizado abaixo:

- **Etapa executada (1. Execução | 2. Correção e Revisão | 3. Testes, Playground e Documentação):**
- **Unidades/arquivos criados (com busca de reuso e justificativa):**
- **Arquivos editados:**
- **Arquivos removidos/movidos:**
- **Registro adicionado em `context/AUDITORIA.md`:** sim/não
- **Paridade sincronizada entre `tasks_<dominio>.md`, `coordination/BOARD.md` e GitHub Project nº 2:** sim/não
- **Escopo conferido no `git diff --stat` (sem arquivos fora da lista):** sim/não
- **Limite de linhas de código respeitado (Views/Comp ≤ 120; Hooks/Models ≤ 150):** sim/não
- **Resultado consolidado de `npm run lint` / `npm run build` / `npm test`:**
- **Dúvidas ou decisões pendentes:**


---

### Regra 13: Sincronização Obrigatória Frontend-Backend (Contratos e DTOs)

1. **Adote fonte única da verdade:** Trate `context/api_contracts.md` como contrato oficial e soberano de todas as rotas REST.
2. **Abra tarefa pareada no backend:** Crie uma tarefa correspondente no backend antes de alterar DTOs, rotas ou payloads no frontend.
3. **Preserve retrocompatibilidade:** Garanta compatibilidade regressiva em alterações de campos. Suporte propriedades legadas até a transição completa.
4. **Respeite convenções de nomes:** Mapeie propriedades em `snake_case` do backend Laravel para `camelCase` nos modelos do frontend.
5. **Atualize mocks imediatamente:** Sincronize os manipuladores em `src/services/api/mock/` sempre que atualizar contratos em `context/api_contracts.md`.

---

### Regra 14: Paridade Tripla Obrigatória (`.md` + `BOARD.md` + GitHub Project nº 2)

1. **Atribua ID único por domínio:** Identifique cada tarefa com prefixo de domínio e número sequencial único (ex.: `CAD-05`, `FON-03`, `BOT-02`, `DOC-01`, `DOC-REF-01`).
2. **Garanta rastreabilidade ponta a ponta:** Vincule o mesmo ID único no card do GitHub Project nº 2 (`https://github.com/users/pinhogo/projects/2`), na documentação interna (`context/` e `coordination/BOARD.md`) e na mensagem de commit.
3. **Sincronize colunas via GitHub CLI (`gh`):** Atualize o status do card no GitHub Project nº 2 (`BACKLOG - FLUXO A`, `BACKLOG - FLUXO B`, `EXECUÇÃO - FLUXO C`, `CORREÇÃO E REVISÃO - FLUXO D`, `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E`, `DONE`, `FREEZE`, além das raias auxiliares `DOCUMENTAÇÃO` e `FIGMA`) usando `gh project item-create 2 --owner pinhogo` e `gh project item-edit`.
4. **Mantenha paridade de texto e checkboxes:** Garanta que os títulos, descrições resumidas e checkboxes (`[ ]` / `[x]`) possuam exatamente o mesmo texto e status em `context/BACKLOG_MELHORIAS.md` (ou `context/tasks_<dominio>.md`), `coordination/BOARD.md` e no card correspondente do GitHub Project nº 2 (`https://github.com/users/pinhogo/projects/2`).
5. **Atualize checkboxes simultaneamente:** Sempre que marcar `[x]` em uma subtarefa ou etapa nos arquivos `.md`, atualize imediatamente o `coordination/BOARD.md` e o card no GitHub Project nº 2 via `gh project item-edit`.
6. **Opere as raias auxiliares (`DOCUMENTAÇÃO` e `FIGMA`) fora da esteira linear:**
   - Mantenha `TESTES, PLAYGROUND E DOCUMENTAÇÃO - FLUXO E` exclusiva da Etapa 3 de features/fixes com código, encerrando direto em `DONE` sem passar por `DOCUMENTAÇÃO`.
   - Use **Draft Issues `[DOC-REF-XX]`** em `DOCUMENTAÇÃO` para índices vivos dos arquivos canônicos (`Etapa 3 (Testes/Docs)`, `Domínio: GLB`), que permanecem estacionários na coluna e não entram no expurgo de `BACKLOG_MELHORIAS.md` até arquivamento em `context/OFF/`.
   - Use **Issues `[DOC-XX]`** em `DOCUMENTAÇÃO` apenas para demandas 100% documentais sem alterações em `src/` (`Fluxo B (Planejado)` + `Labels` `docs:*`), fechando em `DONE` após merge e registro em `context/AUDITORIA.md`.
   - Use `FIGMA` para referências canônicas do Design System e auditorias visuais v1. Preserve as opções atuais de `Domínio` e `Etapa` sem criar novos valores estruturais.

---

### Regra 15: Orquestração Multi-Agente

**Aplicação**

1. Aplique esta regra somente quando você despachar duas ou mais tasks para workers ou quando receber um despacho com Task ID.
2. Ignore esta regra ao trabalhar sozinho.
3. Declare o modo no início da ordem: `Modo: orquestração` ou `Modo: independente`.
4. Mantenha as Regras 0 a 14 em vigor para o orquestrador e para os workers. Esta regra prevalece apenas sobre as Regras 9.3, 10.1, 10.3 e 14.
5. Siga o procedimento passo a passo da seção 4 de `context/ROTINA_E_ORGANIZACAO.md`.

**Papéis**

6. O orquestrador cria branches e worktrees, despacha as tasks, faz merge e faz push.
7. Somente o orquestrador executa `git diff --stat`, `npm run lint`, `npm run build` e `npm test`.
8. Somente o orquestrador escreve em `context/AUDITORIA.md`, `coordination/BOARD.md` e no GitHub Project nº 2.
9. O worker executa uma única task e faz commits atômicos em `task/<ID>` (Regra 9.1).
10. O worker entrega o relatório da Regra 12 com os arquivos criados e editados. Ele informa lint, build e test como "não executado (orquestrador)".
11. O worker nunca faz merge, push nem troca de branch.

**Isolamento**

12. Use uma branch `task/<ID>` e um worktree por worker. Mantenha-os somente no diretório local. Nunca os publique.
13. Limite a 3 workers simultâneos. Despache em paralelo apenas tasks com listas de arquivos disjuntas.

**Merge e Conflitos**

14. Faça merge em `develop` somente depois que `git diff --stat`, lint, build e test passarem sobre o resultado do merge.
15. Resolva os conflitos de merge preservando a intenção das duas tasks.
16. Aborte o merge quando você não conseguir resolver o conflito. Marque o card como bloqueado e avise o usuário imediatamente.
17. Liste no relatório da Regra 12 todo conflito, resolvido ou não, e a forma como você o tratou.

**Proibições**

18. Nunca use `git push --force`, `git reset --hard` nem `--no-verify`.
19. Nunca apague uma branch com commits sem merge.
