# Guia de Execução Local no Linux — Backend Assin.art v1 (`README-local.md`)

Instruções para configurar, inicializar e testar o backend Laravel 11 em ambiente local **Linux** (com foco no Arch Linux / EndeavourOS e distribuições baseadas em Debian/Ubuntu).

---

## 1. Pré-requisitos do Sistema e Extensões PHP

O projeto requer **PHP >= 8.2** (recomendado PHP 8.4 ou 8.5) e **Composer**.

### Extensões Obrigatórias do PHP:
- `pdo_sqlite` e `sqlite3` (driver do banco de dados local SQLite)
- `iconv` (obrigatório para dependências do Symfony / Composer)
- `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`
- `bcmath`, `intl`, `exif` (recomendadas para cálculos e manipulação de mídia)

---

### 1.1 Configuração por Distribuição Linux

#### Arch Linux / EndeavourOS
No Arch Linux e derivados, o PHP é modular e as extensões vêm desabilitadas por padrão no `/etc/php/php.ini`:

1. Instale o PHP, Composer e o módulo SQLite:
   ```bash
   sudo pacman -S --needed php php-sqlite composer
   ```

2. Habilite as extensões necessárias no `/etc/php/php.ini`:
   ```bash
   sudo sed -i -E 's/^;(extension=(iconv|pdo_sqlite|sqlite3|bcmath|exif|intl))/\1/' /etc/php/php.ini
   ```

3. Valide os módulos instalados:
   ```bash
   php -m | grep -E "sqlite|iconv|pdo"
   # Saída esperada: iconv, PDO, pdo_sqlite, sqlite3
   ```

#### Ubuntu / Debian / Pop!_OS
```bash
sudo apt update
sudo apt install php-cli php-sqlite3 php-mbstring php-xml php-curl php-zip composer
```

#### Fedora
```bash
sudo dnf install php-cli php-pdo php-sqlite3 php-mbstring php-xml php-intl composer
```

---

## 2. Instalação e Configuração do Projeto

Execute os comandos a partir da raiz do repositório (`assinart_v1_backend`):

1. **Instalar as dependências do Composer:**
   ```bash
   composer install
   ```

2. **Configurar as variáveis de ambiente e chave de criptografia:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Criar o link simbólico de uploads (`storage/app/public` -> `public/storage`):**
   ```bash
   php artisan storage:link
   ```

4. **Executar migrações do banco (SQLite):**
   ```bash
   php artisan migrate
   ```
   > **Para resetar o banco e carregar dados de demonstração (seed):**
   > ```bash
   > php artisan migrate:fresh --seed
   > ```

5. **Permissões de Diretórios (se necessário):**
   Certifique-se de que as pastas de cache e logs tenham permissão de escrita pelo seu usuário:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

---

## 3. Iniciar o Servidor com Comando Único (Recomendado)

Criamos o runner interativo [`run.sh`](run.sh) que valida automaticamente dependências, extensões, banco de dados, storage link e inicializa o servidor:

```bash
./run.sh
```
*(ou via Composer: `composer dev`)*

> **Dica:** É possível customizar a porta passando como argumento:
> ```bash
> ./run.sh 8080
> ```

Ou, se preferir o comando manual do Laravel:
```bash
php artisan serve
```

O servidor responderá nas seguintes URLs:
- **Base da API:** `http://127.0.0.1:8000/api/v1`
- **Página Pública do Artista:** `http://127.0.0.1:8000/{username}`
- **Healthcheck:** `http://127.0.0.1:8000/up`

---

## 4. Testes Automatizados (Feature)

Execute a suíte completa de testes para verificar integridade e conformidade de contratos:

```bash
php artisan test
```

> **Resultado Esperado:** 91 testes aprovados (371 asserções) com 0 falhas.

---

## 5. Resolução de Problemas Comuns (Troubleshooting)

### Erro: `could not find driver (Connection: sqlite...)`
- **Causa:** O pacote `php-sqlite` não está instalado ou a linha `extension=pdo_sqlite` está comentada no `/etc/php/php.ini`.
- **Solução:**
  ```bash
  sudo pacman -S --needed php-sqlite
  sudo sed -i -E 's/^;(extension=(pdo_sqlite|sqlite3))/\1/' /etc/php/php.ini
  ```

### Erro: `requires ext-iconv * -> it is missing from your system`
- **Causa:** A extensão `iconv` do PHP está comentada com `;` no `/etc/php/php.ini`.
- **Solução:**
  ```bash
  sudo sed -i 's/^;extension=iconv/extension=iconv/' /etc/php/php.ini
  ```

### Erro: `No application encryption key has been specified`
- **Causa:** A variável `APP_KEY` no arquivo `.env` está vazia.
- **Solução:**
  ```bash
  php artisan key:generate
  ```

### Erro de Permissão em `storage` ou `bootstrap/cache`
- **Solução:**
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```

---

## 6. Integração com o Frontend (`assinart_v1`)

1. Mantenha o servidor backend ativo na porta `8000` (`php artisan serve`).
2. O arquivo `config/cors.php` já libera requisições originadas de:
   - `http://localhost:5173` (Vite dev)
   - `https://assin.art` (Produção)
3. No repositório frontend (`assinart_v1`), inicie o servidor de desenvolvimento:
   ```bash
   npm run dev
   ```
