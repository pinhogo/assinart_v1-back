# Assin.art v1 — Backend API (Laravel 11)

API RESTful em PHP 8.2+ e Laravel 11 para a plataforma **Assin.art v1** (Link-in-bio para artistas e criadores de conteúdo com design Neo-Brutalista).

---

## 🚀 Arquitetura e Modelagem do Banco

- **SGBD de Desenvolvimento:** SQLite (`database/database.sqlite`) — zero configuração local.
- **SGBD de Produção:** MySQL 8+ / PostgreSQL (100% agnóstico via Eloquent ORM).
- **Modelagem Integrada:** 2 tabelas principais (`artists` e `buttons`) com herança inteligente de estilos visuais.

Para consultar a especificação completa de cada coluna, regras de integridade e tipos, leia o [Dicionário de Dados](docs/DATA_DICTIONARY.md).

---

## 🛠️ Requisitos
- **PHP** >= 8.2
- **Composer** >= 2.6
- Extensões PHP recomendadas: `pdo_sqlite`, `mbstring`, `openssl`, `curl`

---

## 📦 Instalação e Execução Local

```bash
# 1. Instalar as dependências do Composer
composer install

# 2. Copiar arquivo de ambiente e gerar a chave da aplicação
cp .env.example .env
php artisan key:generate

# 3. Executar as Migrations e popular com dados de demonstração
php artisan migrate --seed

# 4. Criar link simbólico para uploads (avatars e capas)
php artisan storage:link

# 5. Iniciar o servidor local
php artisan serve
```

A API estará respondendo em `http://localhost:8000`.

---

## 🛣️ Rotas Principais

### Públicas
| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/{username}` | Retorna o perfil completo do artista com seus botões ativos e tema visual |
| `GET` | `/r/{id}` | Incrementa atomicamente o contador `clicks_count` e redireciona (HTTP 302) para a URL de destino |

### Privadas (Autenticadas com Sanctum / Session)
| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/api/v1/profile` | Retorna os dados do artista autenticado |
| `PUT` | `/api/v1/profile` | Atualiza dados cadastrais e preferências visuais neo-brutalistas |
| `POST` | `/api/v1/profile/avatar` | Upload de foto de perfil |
| `POST` | `/api/v1/profile/cover` | Upload de banner/capa do topo da página |
| `GET` | `/api/v1/buttons` | Lista todos os botões do artista |
| `POST` | `/api/v1/buttons` | Cadastra novo botão |
| `PUT` | `/api/v1/buttons/{id}` | Edita botão existente |
| `DELETE` | `/api/v1/buttons/{id}` | Remove botão |
| `POST` | `/api/v1/buttons/reorder` | Atualiza a ordenação dos links via Drag & Drop (`{ "ordered_ids": [...] }`) |

---

## 📄 Licença
Proprietário — Assin.art. Todos os direitos reservados.
