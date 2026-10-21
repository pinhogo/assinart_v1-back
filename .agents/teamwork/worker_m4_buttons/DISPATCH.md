# Subagent Workspace: worker_m4_buttons
Target: Milestone 4 - Buttons CRUD, Cascade & Reordering.
## 2026-10-21T03:22:05Z

You are a teamwork_preview_worker agent.
Your Working Directory: c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\worker_m4_buttons
Your Identity: worker_m4_buttons

MANDATORY FIRST STEP:
Read the Original Request file at:
c:\Users\pinho\Desktop\Assin.art\assinart_v1_backend\.agents\teamwork\ORIGINAL_REQUEST.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

TASK OBJECTIVE:
Implement Milestone 4 (Buttons CRUD, Style Fallback Cascade & Drag-and-Drop Reordering) for Assin.art v1 backend:

1. `app/Http/Controllers/Api/ButtonController.php` (all endpoints require `auth:sanctum`):
   - `index(Request $request)`: Returns all buttons of authenticated artist ordered by `order_index ASC`. Returns 200 `{ status: "success", data: [...] }`.
   - `store(StoreButtonRequest $request)`:
     Validates `title` (required, max 100), `url` (required, url, max 2048), `platform` (string, default custom), `type` (`in:standard,icon_pill,card_featured`, default standard), `button_color`, `text_color`, `font_family`, `image_url`.
     Calculates `order_index = (max order_index of user) + 1`. Sets `is_active = true`.
     Returns 201 `{ status: "success", message: "Botão criado com sucesso.", data: Button }`.
   - `update(UpdateButtonRequest $request, int $id)`:
     Scopes strictly to `$request->user()->buttons()->findOrFail($id)`.
     Updates validated fields. Returns 200 `{ status: "success", message: "Botão atualizado com sucesso.", data: Button }`.
   - `destroy(Request $request, int $id)`:
     Scopes strictly to `$request->user()->buttons()->findOrFail($id)`.
     Deletes button. Returns 200 `{ status: "success", message: "Botão removido com sucesso." }`.
   - `reorder(ReorderButtonsRequest $request)`:
     Endpoint `POST /api/v1/buttons/reorder`.
     Receives `{ "ordered_ids": [id1, id2, ...] }`.
     Validates `ordered_ids` is array, distinct, not empty.
     Verifies in `DB::transaction` that ALL provided IDs belong to `$request->user()->id`.
     If any ID belongs to another artist or does not exist, reject or filter safely without modifying other users' data.
     Updates `order_index = $index` for each button.
     Returns 200 `{ status: "success", message: "Ordem dos botões atualizada com sucesso." }`.
   - `uploadImage(Request $request, int $id)`:
     Endpoint `POST /api/v1/buttons/{id}/image`.
     Scopes strictly to `$request->user()->buttons()->findOrFail($id)`.
     Accepts `image` file (max 5MB, mimes: `jpeg,png,jpg,webp`).
     Stores in `cards` on `public` disk, deletes old image if exists.
     Updates `$button->image_url = Storage::url($path)`.
     Returns 200 `{ status: "success", message: "Imagem do card atualizada com sucesso.", image_url: "..." }`.
2. FormRequests:
   - `app/Http/Requests/StoreButtonRequest.php`
   - `app/Http/Requests/UpdateButtonRequest.php`
   - `app/Http/Requests/ReorderButtonsRequest.php`
3. `app/Models/Button.php`:
   Verify and ensure style cascade accessors:
   - `resolved_font_family`: `$this->font_family ?? $this->artist?->font_family ?? 'Space Grotesk'`
   - `resolved_text_color`: `$this->text_color ?? $this->artist?->font_color ?? '#000000'`
   - `resolved_button_color`: `$this->button_color ?? '#FFE600'`
4. `routes/api_buttons.php`:
   Define all button routes with `auth:sanctum`:
   `GET /api/v1/buttons`, `POST /api/v1/buttons`, `PUT /api/v1/buttons/{id}`, `DELETE /api/v1/buttons/{id}`, `POST /api/v1/buttons/reorder`, `POST /api/v1/buttons/{id}/image`.

FILES YOU OWN EXCLUSIVELY:
- `app/Http/Controllers/Api/ButtonController.php`
- `app/Http/Requests/StoreButtonRequest.php`
- `app/Http/Requests/UpdateButtonRequest.php`
- `app/Http/Requests/ReorderButtonsRequest.php`
- `app/Models/Button.php`
- `routes/api_buttons.php`

PHP CLI PATH:
`C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe`
Run lint check: `& 'C:\Users\pinho\miniconda3\envs\php_env\Library\bin\php.exe' -l <file>`
