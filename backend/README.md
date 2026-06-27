# Fintech Wallet — Carteira Digital

API REST de uma carteira digital pessoal com depósitos, saques e histórico de movimentações, acompanhada de uma SPA em Vue.js. Desenvolvido como desafio técnico.

🔗 **Deploy (API):** http://18.190.159.168
🔗 **Repositório:** <COLOQUE_O_LINK_DO_REPOSITORIO>

---

## Visão Geral

O sistema permite que um usuário autenticado gerencie o saldo da própria carteira, realizando **depósitos** e **saques** com validação de regras de negócio, registro de histórico e operações atômicas. Cada movimentação é registrada como uma transação (crédito ou débito) com o saldo resultante.

### Funcionalidades

- Registro, login e logout com autenticação via token (Laravel Sanctum)
- Carteira criada automaticamente com saldo zero no registro
- Depósito e saque com validação de regras financeiras
- Operações atômicas com lock pessimista — em caso de falha, nenhum dado é alterado
- Histórico de transações paginado, com filtros por tipo e período
- Dashboard com saldo atual, últimas 5 transações e totais do mês

> Transferências entre usuários estão fora do escopo — o foco é exclusivamente depósito e saque na própria carteira.

---

## Decisões Técnicas

**Arquitetura modular em camadas.** O código é organizado por módulos de domínio (`app/Modules/Auth`, `app/Modules/Wallet`), cada um com suas próprias camadas: Controllers (entrega HTTP), Requests (validação de input) e Services (regras de negócio). Os Controllers são finos e delegam toda a lógica para os Services, mantendo as responsabilidades isoladas.

**Chaves primárias UUID.** Todas as entidades usam UUID (v7, ordenável) como chave primária em vez de inteiros sequenciais, evitando exposição de volume de registros e enumeração de IDs — relevante para um contexto financeiro.

**API RESTful com respostas JSON padronizadas.** Status HTTP semânticos (200, 201, 401, 422) e envelope de erro consistente.

**Sem over-engineering.** Sem Repository pattern, DDD, filas ou microsserviços. Eloquent é usado diretamente nos Services, conforme o escopo do desafio.

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 13, PHP 8.3+, Laravel Sanctum |
| Frontend | Vue.js 3 (Composition API), Pinia, Vue Router, Axios |
| Banco de dados | PostgreSQL (Neon) |
| Testes | Pest |
| Deploy | AWS EC2 + FrankenPHP (Docker) |

---

## Pré-requisitos

- **PHP** >= 8.3 (extensões: `pdo_pgsql`, `mbstring`, `bcmath`, `openssl`, `tokenizer`, `zip`)
- **Composer** >= 2.x
- **Node.js** >= 18 e **npm** >= 9
- **PostgreSQL** >= 14 (ou uma conta no [Neon](https://neon.tech))
- **Git**

---

## Como Rodar Localmente

O projeto é um monorepo com `backend/` (API Laravel) e `frontend/` (SPA Vue). Rode cada parte em um terminal.

### 1. Clonar o repositório

```bash
git clone <COLOQUE_O_LINK_DO_REPOSITORIO>
cd fintech-wallet
```

### 2. Backend (Laravel)

```bash
cd backend

# Instalar dependências PHP
composer install

# Criar o arquivo de ambiente
cp .env.example .env

# Gerar a chave da aplicação
php artisan key:generate
```

#### Configurar o `.env`

Ajuste as credenciais do banco PostgreSQL no `.env`:

```env
APP_NAME="Fintech Wallet"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fintech_wallet
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
DB_SSLMODE=prefer

FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

> Para usar o Neon, copie a connection string do painel e ajuste `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, e defina `DB_SSLMODE=require`.

#### Rodar migrations e seeders

```bash
php artisan migrate --seed
```

Isso cria as tabelas e popula um **usuário de teste** (credenciais abaixo).

#### Iniciar o servidor

```bash
php artisan serve
```

A API ficará disponível em `http://localhost:8000`.

### 3. Frontend (Vue.js)

Em um novo terminal:

```bash
cd frontend
npm install
cp .env.example .env
```

Ajuste a URL da API no `.env` do frontend, se necessário:

```env
VITE_API_URL=http://localhost:8000/api
```

Inicie o servidor:

```bash
npm run dev
```

A aplicação ficará disponível em `http://localhost:5173`.

---

## Rodar os Testes

Os testes usam um banco PostgreSQL dedicado (`fintech_wallet_test`). Crie-o antes:

```sql
CREATE DATABASE fintech_wallet_test;
```

E configure as credenciais em `backend/.env.testing`. Então:

```bash
cd backend
php artisan test
```

Os testes cobrem os cenários críticos das operações financeiras, incluindo casos de falha (saque com saldo insuficiente, valores inválidos) e a **atomicidade** das operações (rollback completo em caso de falha no meio da transação).

---

## Credenciais de Teste

Após rodar os seeders, use o usuário abaixo:

| Campo | Valor |
|-------|-------|
| **E-mail** | `teste@fintech.com` |
| **Senha** | `password` |

O usuário já vem com uma carteira inicializada para facilitar os testes.

---

## Endpoints da API

| Método | Rota | Descrição | Auth |
|--------|------|-----------|------|
| `POST` | `/api/register` | Registro de usuário (cria a carteira) | Não |
| `POST` | `/api/login` | Login (retorna token Bearer) | Não |
| `POST` | `/api/logout` | Logout (revoga o token) | Sim |
| `GET` | `/api/wallet` | Saldo da carteira | Sim |
| `POST` | `/api/wallet/deposit` | Realizar depósito | Sim |
| `POST` | `/api/wallet/withdraw` | Realizar saque | Sim |
| `GET` | `/api/transactions` | Histórico paginado (filtros: `type`, `start_date`, `end_date`, `per_page`) | Sim |
| `GET` | `/api/dashboard` | Saldo, últimas 5 transações e totais do mês | Sim |

> Rotas protegidas exigem o header `Authorization: Bearer <token>`.

### Exemplo de uso

```bash
# Registrar
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"name":"Felipe","email":"felipe@teste.com","password":"password","password_confirmation":"password"}'

# Depositar (com o token retornado no login/registro)
curl -X POST http://localhost:8000/api/wallet/deposit \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -d '{"amount":"100.00"}'
```

---

## Deploy

A API está hospedada na **AWS EC2** usando **FrankenPHP** em container Docker, com banco **PostgreSQL no Neon**.

O processo de deploy usa um `Dockerfile` de produção (em `backend/deploy/`) que instala as extensões PHP necessárias (incluindo `pdo_pgsql`), roda `composer install`, aplica os caches de produção e executa as migrations no boot via entrypoint.

```bash
# Build da imagem
docker build -f deploy/Dockerfile -t fintech-app .

# Subir o container
docker run -d --name fintech \
  -e SERVER_NAME=":80" \
  --env-file .env \
  -p 80:80 \
  fintech-app
```

---

## Estrutura do Projeto

```
backend/
├── app/
│   ├── Models/                 # User, Wallet, Transaction (UUID + PHP Attributes)
│   └── Modules/
│       ├── Auth/
│       │   ├── Controllers/
│       │   ├── Requests/
│       │   └── Services/
│       └── Wallet/
│           ├── Controllers/
│           ├── Requests/
│           ├── Services/        # WalletService: deposit, withdraw, histórico, dashboard
│           └── Exceptions/
├── database/migrations/
├── deploy/                      # Dockerfile, entrypoint
├── routes/api.php
└── tests/
    ├── Feature/                 # Auth, Wallet (inclui teste de atomicidade)
    └── Unit/
```

---

## Licença

Projeto desenvolvido para fins de avaliação técnica.
