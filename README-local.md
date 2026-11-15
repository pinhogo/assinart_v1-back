# Guia de Execução Local — Backend Assin.art v1 (`README-local.md`)

Instruções para configurar, inicializar e testar o backend Laravel 11 em ambiente Windows local com o WAMP.

---

## 1. Versão do PHP (Requisito Obrigatório)

O projeto exige **PHP >= 8.4.1**. Caso o comando global `php` aponte para uma versão anterior (como 8.2), utilize diretamente o binário do PHP 8.4 instalado no WAMP:

```text
C:\wamp64\bin\php\php8.4.15\php.exe
```

> **Dica Opcional (PowerShell):** Crie um atalho na sua sessão para simplificar os comandos:
> ```powershell
> function php84 { & "C:\wamp64\bin\php\php8.4.15\php.exe" @args }
> ```

---

## 2. Iniciar o Servidor de Desenvolvimento

1. Abra o terminal PowerShell no diretório do projeto:
   ```powershell
   Set-Location "C:\Users\pinho\Desktop\Assin.art\assinart_v1_backend"
   ```

2. Inicie o servidor embutido do Laravel:
   ```powershell
   & "C:\wamp64\bin\php\php8.4.15\php.exe" artisan serve
   ```

3. O servidor responde nas seguintes URLs:
   - **Base da API:** `http://127.0.0.1:8000/api/v1`
   - **Página Pública:** `http://127.0.0.1:8000/{username}`
   - **Healthcheck:** `http://127.0.0.1:8000/up`

---

## 3. Comandos de Banco de Dados e Storage

Execute os comandos a partir da raiz do repositório:

- **Executar migrações do banco (SQLite):**
  ```powershell
  & "C:\wamp64\bin\php\php8.4.15\php.exe" artisan migrate
  ```

- **Resetar o banco e carregar dados de demonstração (seed):**
  ```powershell
  & "C:\wamp64\bin\php\php8.4.15\php.exe" artisan migrate:fresh --seed
  ```

- **Criar o link simbólico de uploads (`storage/app/public` -> `public/storage`):**
  ```powershell
  & "C:\wamp64\bin\php\php8.4.15\php.exe" artisan storage:link
  ```

---

## 4. Testes Automatizados (Feature)

Execute a suíte completa de testes para verificar integridade e conformidade de contratos:

```powershell
& "C:\wamp64\bin\php\php8.4.15\php.exe" artisan test
```

> **Resultado Esperado:** 91 testes aprovados (371 asserções) com 0 falhas.

---

## 5. Integração com o Frontend (`assinart_v1`)

1. Mantenha o servidor backend ativo na porta `8000`.
2. O arquivo `config/cors.php` já libera requisições de:
   - `http://localhost:5173` (Vite dev)
   - `https://assin.art` (Produção)
3. No repositório frontend (`assinart_v1`), inicie o Vite com:
   ```bash
   npm run dev
   ```
