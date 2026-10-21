# Subagent Workspace: spec_miner_specs
Target: Mine context/tasks_backend.md, docs/DATA_DICTIONARY.md, context/data_dictionary.md.

## 2026-10-21T02:58:40Z
Sender: 5587c924-51ec-4071-acca-5106909deee8
Priority: MESSAGE_PRIORITY_HIGH

You are a teamwork_preview_spec_miner agent.
Your Working Directory: c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\spec_miner_specs
Your Identity: spec_miner_specs (read-only specification investigator).

MANDATORY FIRST STEP:
Read the Original Request file at:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\ORIGINAL_REQUEST.md

YOUR MISSION:
Investigate all authoritative backend specification documents in:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend

Specifically examine:
- c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\context\tasks_backend.md
- c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\docs\DATA_DICTIONARY.md
- c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\context\data_dictionary.md
- any other documentation or context files in c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\context\ or docs\

Investigate and document thoroughly:
1. Complete Feature Inventory: Enumerate all tasks from Phase 1 through Phase 6 with all acceptance criteria, HTTP endpoints, methods, headers, status codes, and error responses.
2. Complete Data Dictionary and Database Schema Specifications:
   - Full column definitions for `artists`, `buttons`, and any related tables (auth, tokens, password resets).
   - Data types, nullability, default values, foreign key relationships, indexes, constraints, cascades.
   - The visual style inheritance cascade logic (how artist level styles cascade to buttons when button styles are null).
3. Specific Business Rules:
   - Username validation regex, blacklist/reserved routes, sanitization rules.
   - Onboarding 3-step rules, validations, transition logic, and published status.
   - Media upload rules: allowed MIME types, max file sizes (5MB avatar, 10MB cover), storage directories, deletion of old files.
   - Button formats: standard, icon_pill, card_featured.
   - Reordering logic: POST /api/v1/buttons/reorder atomic transaction expectations.
   - Public profile GET /{username}: single-query eager loading, status handling (found, no_links, not_found).
   - Click tracker GET /r/{id}: atomic increment and 302 redirect.
4. Documentation / Wiki requirements for documentacao/index.html.

OUTPUT REQUIREMENTS:
- Update progress.md in your working directory with your heartbeat.
- Write your comprehensive, detailed findings to:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\spec_miner_specs\handoff.md
Follow standard Handoff structure.
- Send a message back to the orchestrator when completed.
