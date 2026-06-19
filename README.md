# MyBP — Seu negócio na sua mão

Sistema de Gestão de RH em Laravel: recrutamento, admissões, treinamentos, exames ocupacionais, controle de ponto, movimentações de pessoal, assinatura digital, relatórios e módulos administrativos.

**Versão:** 2.0.0 (ver [CHANGELOG.md](CHANGELOG.md))

---

## O que o sistema faz

Aplicação monolítica com frontend híbrido **Blade + Vue 3** que cobre o ciclo de vida do colaborador:

- **Recrutamento e currículos** — vagas abertas, cadastro de candidatos, feedback (seleção/rejeição), histórico, exportações
- **Entrevistas e resultado integrado** — pareceres RH, técnico, rota, teste prático; consolidação e encaminhamento para exame/admissão
- **Admissão e pré-admissão** — processo de admissão, documentos pré-admissionais, carta oferta, CIH, intermitente
- **Pós-admissão** — histórico, movimentações, solicitações de mudança de cargo
- **Treinamentos** — cadastro, vencimentos, carteira por segmento, relatórios, notificações
- **Controle de exames** — exames ocupacionais, fichas de encaminhamento, integração com clínicas
- **Controle de ponto** — registro de ponto, aprovação de extras, escalas
- **Assinatura digital** — documentos para assinatura, fluxo por token, verificação pública
- **Documentos legais** — contratos, SSMA, configuração por empresa
- **Relatórios** — efetivo, férias, avaliações, treinamento, centro de custo, NPS, pesquisa de clima
- **Chat, notificações e weekly report**
- **Administração** — clientes, fornecedores, usuários, habilidades, cadastros mestres (área, centro de custo, cargo, benefício, projeto etc.)

Integrações reais: e-mail e filas (Laravel), Redis/Horizon, Reverb/Pusher (tempo real), S3/Flysystem, WhatsApp (Dynamus), reCAPTCHA, Telegram (log), geolocalização (assinatura e ponto).

---

## Stack técnica

| Camada   | Tecnologia |
|----------|------------|
| Backend  | PHP 8.2, Laravel 12, Sanctum, Horizon, Reverb/Pusher |
| Frontend | Vue 3, Laravel Mix, Bootstrap 4, Axios, Laravel Echo |
| Banco    | MySQL (uso típico; suporta outros drivers) |
| Fila/cache | Redis (Horizon, cache, sessão) |
| Build    | Node ≥ 24 (ver [.nvmrc](.nvmrc)), Laravel Mix, webpack |
| Runtime  | Docker (container `mybpdp`), Nginx + PHP-FPM |

Principais dependências: DomPDF, Maatwebsite Excel, Spatie Activity Log, Guzzle, Intervention Image, FPDF/FPDI, Endroid QR Code, TinyMCE, Leaflet, Chart.js, SweetAlert2.

---

## Requisitos

- **PHP** 8.2+
- **Composer** 2.x
- **Node.js** ≥ 24 (recomendado via `nvm use` com `.nvmrc`)
- **MySQL** (ou compatível) e **Redis** (para filas e cache em produção)
- **Docker** e **Docker Compose** (opcional, para ambiente containerizado)

---

## Setup rápido

### 1. Clonar e instalar dependências

```bash
git clone <repositório> mybp && cd mybp
cp .env.example .env
php artisan key:generate
composer install
npm ci
```

### 2. Configurar ambiente

Editar `.env`: `APP_*`, `DB_*`, `REDIS_*`, `QUEUE_CONNECTION`, `FILESYSTEM_*`, `MAIL_*`, etc. Ver variáveis em `.env.example`.

### 3. Banco e filas

```bash
php artisan migrate
# Se usar Redis/Horizon:
php artisan horizon
```

### 4. Assets (frontend)

```bash
nvm use   # ou ativar Node 24+
npm run dev
# Produção:
npm run production
```

### 5. Com Docker (ambiente local comum)

O projeto usa o container **mybpdp** (ver [docker-compose.yml](docker-compose.yml)). MySQL e Redis podem estar no host ou em outros containers.

```bash
docker compose up -d
docker compose exec mybpdp php artisan migrate
```

Build dos assets continua no host (Node 24):

```bash
npm run dev
```

Logs da aplicação:

```bash
docker compose exec mybpdp tail -f storage/logs/laravel.log
```

Mais comandos úteis: [COMANDOS_UTEIS.md](COMANDOS_UTEIS.md).

---

## Estrutura do projeto

| Pasta / arquivo | Uso |
|-----------------|-----|
| `app/` | Models, Controllers, Jobs, Services, Middlewares |
| `app/Http/Controllers/` | Controllers web e API (Recrutamento, Admissão, Treinamento, etc.) |
| `resources/js/` | Apps Vue 3 por tela (`g/curriculos/`, `g/admissao/`, etc.), componentes, mixins |
| `resources/views/` | Blade (layouts, páginas do sistema) |
| `routes/web.php`, `routes/api.php` | Rotas web e API |
| `database/migrations/` | Migrations Laravel |
| `docs/` | Documentação interna (visão geral, módulos, arquitetura, riscos, migração Vue 3) |
| `agents/` | Guias e prompts para agentes de IA (migração frontend, etc.) |
| `AGENTS.md` | Regras para IA: SOLID, DDD, padrões de exportação, cache, fila, Vue 3 |

---

## Documentação e referências

- **Visão geral e módulos:** `docs/01-visao-geral.md`, `docs/03-modulos-de-negocio.md`
- **Arquitetura e banco:** `docs/04-arquitetura-tecnica.md`, `docs/05-banco-de-dados.md`
- **Fluxos e integrações:** `docs/07-fluxos-criticos.md`, `docs/08-integracoes.md`, `docs/API_V2_INTEGRACAO_SPA.md` (API BFF: empresas e vagas v2), `docs/postman/Integracao-SPA-v2.postman_collection.json` (Postman)
- **Vue 3:** `docs/ANALISE_VUE3_CONFORMIDADE.md`, `docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md`, `agents/migracao-frontend/README.md`
- **Deploy:** ver `docs/` e `.deploy/` quando existirem (ex.: `README-DEPLOY.md`)

Padrões obrigatórios para desenvolvimento (exportação CIH, e-mail em fila, cache com TTL, soft delete em queries, Vue 3): [AGENTS.md](AGENTS.md).

---

## Licença

MIT (ver `composer.json`).
