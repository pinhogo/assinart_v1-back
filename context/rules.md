# Regras Oficiais do Backend Assin.art (`context/rules.md`)

Diretrizes operacionais, arquiteturais e de qualidade obrigatórias para agentes de IA e desenvolvedores no repositório `assinart_v1_backend`.

---

> [!CAUTION]
> ## REGRA 0: CONSULTA ÚNICA POR ORDEM E OTIMIZAÇÃO DE TOKENS
>
> 1. **Leia uma única vez:** Consulte `context/rules.md` apenas uma vez no início de cada ordem ou lote de tarefas do usuário.
> 2. **Não releia entre tarefas:** Execute múltiplas tarefas da mesma ordem em sequência sem reler este arquivo.
> 3. **Não releia antes de comandos:** Nunca leia este arquivo antes de executar comandos de terminal (`git`, `artisan test`, `composer`).
> 4. **Economize tokens no chat:** Nunca reproduza o conteúdo integral de arquivos modificados na resposta. Use links markdown `[arquivo](caminho)`.

---

## 1. Arquitetura, Código e Padrões Laravel 11

### Regra 1: Arquitetura MVC e Camadas Rigorosas

Organize arquivos e restrinja responsabilidades conforme o padrão Laravel 11:

| Camada | Pasta Obrigatória | Responsabilidade | Proibições Estritas |
| :--- | :--- | :--- | :--- |
| **Controllers** | `app/Http/Controllers/Api/` | Orquestração de requisição e resposta JSON | Proibido conter regras de negócio complexas ou SQL cru |
| **Requests** | `app/Http/Requests/` | Validação e sanitização de payloads de entrada | Proibido executar persistência de banco |
| **Models** | `app/Models/` | Relacionamentos, accessors, casts e scopes | Proibido conter lógica de apresentação ou renderização |
| **Resources/DTOs** | `app/Http/Resources/` | Transformação e envelope JSON da API | Proibido manipular requisições diretamente |
| **Migrations** | `database/migrations/` | Estrutura declarativa e versionada do banco | Proibido alterar banco de produção via comandos manuais |
| **Seeders/Factories** | `database/seeders/`, `factories/` | Dados consistentes para desenvolvimento e teste | Proibido inserir dados fictícios que violem contratos |
| **Testes Feature** | `tests/Feature/` | Validação de ponta a ponta dos endpoints HTTP | Proibido ignorar status codes ou validações de payload |

### Regra 2: Contratos de API Estritos e Envelope Universal

1. **Envelope Universal:** Responda todas as requisições com corpo no formato:
   ```json
   {
     "status": "success",
     "data": {}
   }
   ```
2. **Respostas de Erro de Validação (422):**
   ```json
   {
     "message": "Os dados fornecidos são inválidos.",
     "errors": {
       "campo": ["Descrição da falha de validação."]
     }
   }
   ```
3. **Status Codes Semânticos:**
   - `200 OK`: Operações de leitura e atualização com retorno de corpo.
   - `201 Created`: Recursos criados com sucesso (`register`, `buttons.store`).
   - `204 No Content`: Exclusões ou eventos de telemetria sem corpo (`destroy`, `track`).
   - `401 Unauthorized`: Token ausente ou inválido.
   - `403 Forbidden`: Recurso não pertence ao usuário autenticado (anti-IDOR).
   - `404 Not Found`: Recurso inexistente.
   - `422 Unprocessable Entity`: Erros de validação de formulário ou regras de negócio.
   - `429 Too Many Requests`: Throttling e rate limiting estritos.

### Regra 3: Limites de Linhas de Código

- **Limite para Controllers:** Máximo de **150 linhas de código efetivo** por arquivo.
- **Limite para Models e Requests:** Máximo de **150 linhas de código efetivo** por arquivo.
- Se um controller ultrapassar 150 linhas, extraia métodos auxiliares para Services, Traits ou Form Requests dedicados.

---

## 2. Reuso, Integridade de Dados e Segurança

### Regra 4: Prevenção de Arquivos Órfãos e Integridade no Storage

1. **Exclusão Prévia:** Ao atualizar avatar, capa ou wallpaper, exclua fisicamente o arquivo anterior do disco `public` antes de persistir o novo caminho.
2. **Exclusão em Cascata:** Ao excluir botões ou conta de usuário, remova todos os arquivos de mídia associados do storage.
3. **Mídias Polimórficas:** Suporte simultâneo a objetos `ResolvedMediaDto` (`{ type, value, url }`) e colunas planas para garantir retrocompatibilidade.

### Regra 5: Transações Atômicas e Proteção Anti-IDOR

1. **Atomicidade:** Encapsule operações em lote (reordenação de links, exclusão de conta) em `DB::transaction`.
2. **Anti-IDOR:** Sempre valide a propriedade do recurso através do relacionamento com o usuário autenticado (`$request->user()->buttons()->findOrFail($id)`). Nunca acerte registros via `Button::findOrFail($id)` direto sem escopo de artista.

### Regra 6: Escopo Estrito e Proteção de Ativos

1. **Declare a lista de arquivos:** Liste antes de iniciar a tarefa quais arquivos você vai ler, criar ou editar.
2. **Altere apenas o solicitado:** Nunca edite arquivos fora do escopo da tarefa.
3. **Mantenha o `README.md` limpo:** Destine o `README.md` da raiz para humanos (stack, comandos artisan). Guarde documentações técnicas dentro de `context/`.

---

## 3. Fluxo de Execução, Validação, Branch e Entrega

### Regra 7: Fluxo Oficial de Execução em 3 Etapas

Execute todo plano de tarefas (`context/tasks_*.md`) seguindo estritamente estas 3 etapas:

#### 1. EXECUÇÃO DE TASKS
- **Responsável:** Modelo de IA Principal (alta capacidade).
- **Ação:** Implemente código de produção (`app/`, `routes/`, `database/migrations/`).
- **Restrição:** Não altere arquivos de documentação nem adicione testes nesta etapa.

#### 2. CORREÇÃO E REVISÃO DE CÓDIGO
- **Responsável:** Agente Revisor ou Modelo Principal em revisão.
- **Ação:** Audite o código da Etapa 1:
  1. Reverta qualquer alteração em arquivo fora da lista da tarefa.
  2. Confirme o cumprimento das Regras 1, 2, 3 e 5 (envelope, status codes, limites de 150 linhas).
  3. Corrija imediatamente violações ou duplicações.

#### 3. TESTES, INFRA E DOCUMENTAÇÃO
- **Responsável:** Modelo de IA Simplificado (baixo custo de tokens).
- **Ação:** Execute após concluir a Etapa 2:
  1. Crie ou atualize os testes Feature em `tests/Feature/`.
  2. Atualize catálogos em `context/` (`context/data_dictionary.md`, `context/inventory.md`, `context/api_contracts.md`) seguindo ASD-STE100.
  3. Registre as alterações na tabela de auditoria `context/REVISAO_3_DIAS_FLASH_E_QWEN.md`.

### Regra 8: Commits Atômicos e Validação Final Única

1. **Faça commits atômicos por tarefa:** Ao concluir cada tarefa de um checklist (`context/tasks_*.md`), marque `[x]` e execute um commit imediato com prefixo semântico (`feat:`, `fix:`, `refactor:`, `test:`, `docs:`, `chore:`).
2. **Proíba validações intermediárias:** Não execute `artisan test` a cada linha ou micro-tarefa.
3. **Valide uma única vez no encerramento:** Execute os testes com PHP 8.4 (`& "C:\wamp64\bin\php\php8.4.15\php.exe" artisan test`) **exclusivamente uma vez**, ao finalizar todas as tarefas da ordem do usuário.

### Regra 9: Protocolo de Branch e Auditoria (`FLASH-AND-QWEN-BEST-FRIENDS`)

1. **Use a branch exclusiva:** Trabalhe unicamente na branch `FLASH-AND-QWEN-BEST-FRIENDS`.
2. **Bloqueie a `main`:** Nunca faça commit na `main`, nunca abra Pull Requests para a `main` e nunca faça merge para a `main` até 03/11/2026.
3. **Registre na auditoria:** Adicione cada arquivo criado, editado ou utilizado na tabela de `context/REVISAO_3_DIAS_FLASH_E_QWEN.md`.
4. **Preserve o histórico:** Adicione apenas novas linhas ao final da tabela. Nunca sobrescreva nem apague registros anteriores.

### Regra 10: Padrão de Escrita ASD-STE100

Aplique obrigatoriamente o padrão ASD-STE100 em documentos dentro de `context/`:

1. **Use apenas a voz ativa:** Nunca escreva na voz passiva.
2. **Inicie instruções no imperativo:** Comece cada item com verbo de ação ("Crie", "Edite", "Remova", "Execute").
3. **Escreva frases curtas:** Use no máximo 20 palavras em instruções e 25 palavras em descrições.
4. **Declare uma ação por frase:** Separe ações distintas em itens independentes.
5. **Use vocabulário controlado:** Adote um único termo técnico por conceito.

### Regra 11: Relatório de Entrega Obrigatório

Apresente na resposta final ao usuário, ao concluir o lote, o relatório padronizado:

- **Etapa executada (1. Execução | 2. Correção e Revisão | 3. Testes e Documentação):**
- **Arquivos criados:**
- **Arquivos editados:**
- **Arquivos removidos/movidos:**
- **Registro adicionado em `context/REVISAO_3_DIAS_FLASH_E_QWEN.md`:** sim/não
- **Escopo conferido no `git status` (sem arquivos fora da lista):** sim/não
- **Limite de linhas de código respeitado (Controllers/Models/Requests ≤ 150):** sim/não
- **Resultado consolidado de `artisan test`:**
- **Dúvidas ou decisões pendentes:**
