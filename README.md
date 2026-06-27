# Fintech Wallet

Aplicação de carteira digital composta por uma API REST em Laravel (backend) e uma SPA em Vue 3 (frontend). O usuário pode se cadastrar, autenticar, depositar, sacar e acompanhar o histórico de transações da sua carteira.

**Deploy (Frontend):** https://fintech-wallet-gamma.vercel.app/history
**Deploy (API):** https://18.190.159.168.nip.io/api

O repositório é dividido em dois projetos independentes:

```
fintech-wallet/
  backend/    API REST em Laravel 13
  frontend/   SPA em Vue 3
```

---

## Credenciais de Teste

Após rodar os seeders, use o usuário abaixo para acessar a aplicação:

| Campo | Valor |
|-------|-------|
| **E-mail** | `teste@fintech.com` |
| **Senha** | `password` |

O usuário já vem com uma carteira inicializada.

---

## Visão Geral da Arquitetura

O backend expõe uma API stateless protegida por tokens do Laravel Sanctum. Ao se cadastrar ou logar, o cliente recebe um token que deve ser enviado no header `Authorization: Bearer <token>` em todas as rotas protegidas. O frontend guarda esse token, anexa-o automaticamente a cada requisição e usa guardas de rota para impedir o acesso a telas autenticadas sem token válido.

```
Vue 3 (SPA)  ──HTTP/JSON + Bearer token──>  Laravel API  ──>  PostgreSQL
```

---

## Decisões Técnicas

**Arquitetura modular em camadas.** O código de domínio é organizado em módulos dentro de `app/Modules` (Auth, Wallet), cada um com suas próprias camadas: Controllers (entrega HTTP), Requests (validação), Services (regras de negócio). Os Controllers são finos e delegam toda a lógica aos Services, mantendo as responsabilidades isoladas.

**Tabela `wallets` separada de `users`.** O saldo vive em sua própria tabela, isolando a responsabilidade financeira e permitindo travar a linha da carteira durante operações sem bloquear o registro do usuário. O histórico de transações é a fonte de verdade reconciliável contra o saldo.

**Atomicidade e concorrência.** Depósitos e saques rodam dentro de uma transação de banco (`DB::transaction`) com lock pessimista na carteira (`lockForUpdate`). A validação de saldo ocorre dentro da transação, após o lock, evitando condição de corrida entre saques concorrentes. Em caso de falha, o rollback garante que nenhum dado seja alterado.

**Aritmética monetária com precisão.** Valores são armazenados como `DECIMAL(15,2)` e operados com funções `bcmath` (`bcadd`, `bcsub`, `bccomp`), evitando erros de ponto flutuante. Cada transação registra o `balance_after` (saldo resultante).

**Chaves primárias UUID.** Todas as entidades usam UUID como chave primária, evitando exposição de volume de registros e enumeração de IDs — relevante em contexto financeiro.

**Sem over-engineering.** Sem Repository pattern, DDD, filas ou microsserviços. Eloquent é usado diretamente nos Services. Transferências entre usuários estão fora do escopo.

---

## Backend

### Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum (autenticação por token)
- PostgreSQL
- Pest (testes)

### Pré-requisitos

- **PHP** >= 8.3 (extensões: `pdo_pgsql`, `mbstring`, `bcmath`, `openssl`, `tokenizer`, `zip`)
- **Composer** >= 2.x
- **PostgreSQL** >= 14 (ou conta no [Neon](https://neon.tech))

### Como rodar localmente

```bash
cd backend

# 1. Instalar dependências
composer install

# 2. Criar o arquivo de ambiente
cp .env.example .env

# 3. Gerar a chave da aplicação
php artisan key:generate
```

#### Configurar o `.env`

Ajuste as variáveis de banco PostgreSQL:

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

# Origem do frontend (para CORS / Sanctum)
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

> Para usar o Neon, copie os dados de conexão do painel e defina `DB_SSLMODE=require`.

#### Migrations e seeders

```bash
# cria as tabelas e popula o usuário de teste
php artisan migrate --seed
```

#### Iniciar o servidor

```bash
php artisan serve
```

A API fica disponível em `http://localhost:8000/api`.

### Testes

Os testes usam um banco PostgreSQL dedicado. Crie `fintech_wallet_test` e configure `backend/.env.testing`, então:

```bash
php artisan test
```

Cobrem os fluxos críticos das operações financeiras, incluindo cenários de falha (saldo insuficiente, valores inválidos) e a atomicidade das operações (rollback em falha no meio da transação).

---

## Frontend

### Stack

- Vue 3 (Composition API, `<script setup>`)
- Pinia (estado e autenticação)
- Vue Router (navegação e proteção de rotas)
- Axios (HTTP, com interceptors de token e tratamento de 401)
- Tailwind CSS v4
- Vite

### Pré-requisitos

- **Node.js** >= 18
- **npm** >= 9

### Como rodar localmente

```bash
cd frontend

# 1. Instalar dependências
npm install

# 2. Configurar a URL da API
cp .env.example .env.development
# ajuste VITE_API_URL=http://localhost:8000/api

# 3. Iniciar o servidor de desenvolvimento
npm run dev
```

A aplicação fica disponível em `http://localhost:5173`.

A URL da API vem da variável `VITE_API_URL` (`.env.development` em dev, `.env.production` em produção) — nunca é fixada no código.

### Build de produção

```bash
npm run build      # gera dist/
npm run preview    # serve o build para conferência
```

---

## Telas

1. **Registro** — nome, email, senha e confirmação
2. **Login** — email e senha
3. **Dashboard** — saldo atual, totais do mês, últimas transações
4. **Depósito** — informa valor a creditar
5. **Saque** — informa valor a debitar (trata saldo insuficiente)
6. **Histórico** — lista paginada com filtros por tipo e período
7. **Logout** — encerra a sessão e invalida o token

---

## Endpoints da API

Base: `/api`. Header `Accept: application/json` em todas. Rotas protegidas exigem `Authorization: Bearer <token>`.

| Método | Rota | Acesso | Descrição |
|--------|------|--------|-----------|
| POST | `/register` | Público | Cria usuário e retorna token |
| POST | `/login` | Público | Autentica e retorna token |
| POST | `/logout` | Protegido | Invalida o token atual |
| GET | `/wallet` | Protegido | Saldo da carteira |
| POST | `/wallet/deposit` | Protegido | Registra depósito |
| POST | `/wallet/withdraw` | Protegido | Registra saque |
| GET | `/transactions` | Protegido | Histórico paginado com filtros |
| GET | `/dashboard` | Protegido | Saldo, totais do mês e últimas transações |

### Observações sobre os dados

- Valores monetários trafegam como **string** (ex. `"100.00"`) para preservar precisão.
- IDs são **UUID** (strings).
- Erros de validação seguem o padrão Laravel (`errors` com listas por campo).
- Saldo insuficiente retorna 422 com apenas `message` (mensagem geral, não de campo).
- Histórico usa paginação padrão do Laravel (`current_page`, `last_page`, `total`, `data`).

---

## Deploy

A aplicação é dividida em dois deploys independentes: a **API** (backend) e a **SPA** (frontend).

### Backend — AWS EC2 + FrankenPHP (Docker) + PostgreSQL (Neon)

A API roda em uma instância **AWS EC2** (Ubuntu) usando **FrankenPHP** em container Docker, com banco **PostgreSQL gerenciado no Neon**.

**Containerização.** O build usa um `Dockerfile` de produção (em `backend/deploy/`) que:
- parte da imagem `dunglas/frankenphp`;
- instala as extensões PHP necessárias (`pdo_pgsql`, `bcmath`, `intl`, `opcache`, `zip`) via `install-php-extensions`;
- define o document root em `public/` (`SERVER_ROOT=/app/public`);
- copia o código e roda `composer install --no-dev --optimize-autoloader` no build (imagem autocontida);
- ajusta permissões de `storage/` e `bootstrap/cache/`;
- usa um entrypoint que, a cada boot, roda `migrate --force`, aplica os caches de produção (`config:cache`, `route:cache`, `view:cache`) e sobe o servidor.

**HTTPS automático.** O FrankenPHP emite certificado Let's Encrypt automaticamente. Como o deploy é acessado por IP, usa-se o serviço **nip.io** (`<ip>.nip.io`) para fornecer um hostname válido ao certificado, evitando avisos de certificado e habilitando HTTPS sem registrar um domínio próprio.

**Build e execução:**

```bash
cd backend

# Build da imagem
docker build -f deploy/Dockerfile -t fintech-app .

# Subir o container com HTTPS automático
docker run -d --name fintech \
  -e SERVER_NAME="18.190.159.168.nip.io" \
  --env-file .env \
  -p 80:80 -p 443:443 -p 443:443/udp \
  fintech-app
```

**Variáveis de ambiente em produção.** As credenciais (banco, APP_KEY) são injetadas via `--env-file .env` no host — o `.env` nunca é copiado para a imagem (excluído pelo `.dockerignore`). Em produção, `APP_ENV=production` e `APP_DEBUG=false`.

**Infraestrutura AWS.** A instância usa um **Elastic IP** para manter o endereço fixo (necessário para o nip.io e o link do deploy não quebrarem em reinícios). O Security Group libera as portas 22 (SSH), 80 (HTTP) e 443 (HTTPS).

### Frontend — Vercel / Netlify (build estático)

O frontend é um build estático (`dist/`) hospedado em **Vercel** ou **Netlify**. O fallback de rotas da SPA está configurado em `frontend/vercel.json` (Vercel) e `frontend/public/_redirects` (Netlify), garantindo que rotas client-side funcionem em refresh.

**Configuração:**
- Defina `VITE_API_URL` apontando para a URL HTTPS da API (`https://18.190.159.168.nip.io/api`).
- O build (`npm run build`) é executado automaticamente pela plataforma a cada push.

### CORS e Mixed Content

Como o frontend hospedado roda em **HTTPS**, a API também precisa responder em **HTTPS** (resolvido pelo FrankenPHP + nip.io), evitando bloqueio de **Mixed Content** no navegador. Além disso, o backend libera **CORS** para a origem do frontend em `config/cors.php`, permitindo as chamadas cross-origin.

---

## Licença

Projeto desenvolvido para fins de avaliação técnica.