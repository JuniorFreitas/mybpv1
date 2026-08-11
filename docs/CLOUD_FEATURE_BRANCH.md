# Cloud — Documentação da branch `feature/cloud`

Documentação do que foi implementado na branch `feature/cloud` (módulo Cloud / gestão de pastas e arquivos).

---

## Resumo

A branch endurece segurança (IDOR, anexos privados assinados), melhora UX do cadastro e da navegação (URLs amigáveis com slug + path), garante permissões do grupo Administradores e corrige bugs de frontend (Vue 3, modais, mover pasta).

---

## Commits principais

| Commit | Escopo |
|--------|--------|
| `6b7fcc58` | Sync Administradores ↔ Clouds + permissão obrigatória em itens |
| `86a23d11` | UX cadastro Cloud (membros por colaborador/grupo) |
| `9b9ce6a0` | Proteção IDOR em Controllers Cloud / ItensCloud |
| `f4c5bcdf` | Slug nas URLs + `?path=` para pastas |
| `532b8120` | Anexos privados com URL assinada + autorização |

---

## 1. Grupo Administradores (permissões e sync)

### Problema
O grupo **Administradores** podia perder permissão em pastas/arquivos; membros do grupo não eram sincronizados automaticamente nos Clouds.

### O que foi feito

- **`ItensCloud::permissoesComAdministradores()`** — sempre inclui o grupo Administradores na lista de permissões.
- **`ItensCloud::garantirPermissaoAdministradores()`** — força sync do grupo em um item.
- Store/update/upload de pastas e arquivos passam a usar `permissoesComAdministradores`.
- Command: `php artisan cloud:garantir-permissao-administradores`  
  (`app/Console/Commands/CloudGarantirPermissaoAdministradoresCommand.php`) — backfill em itens existentes.
- **`Cloud::sincronizarMembrosAdministradores()`** — ao alterar membros do grupo Administradores em Configurações, adiciona/remove esses usuários em todos os `user_clouds` da empresa.
- UI: não permite negar/remover Administradores; botão “Nenhum” mantém o grupo.

### Arquivos

- `app/Models/ItensCloud.php`
- `app/Models/GrupoCloud.php` (`NOME_ADMINISTRADORES`, `idAdministradores`)
- `app/Models/Cloud.php`
- `app/Http/Controllers/ItensCloudController.php`
- `app/Http/Controllers/CloudConfiguracaoController.php`
- `resources/js/components/Cloud.vue`
- `resources/js/components/cloud/cadastro.vue`

---

## 2. UX — Cadastro de Cloud (`/g/clouds/cadastro`)

### O que foi feito

- Listagem com busca por `nome`, `withCount('Usuarios')`, botão Editar.
- Formulário em abas **Dados** + **Acesso**.
- Toggle de inclusão: **Grupo** (principal) ou **Colaborador**.
- Adicionar por grupo = expandir membros ativos na lista (persistência continua em `user_clouds`; sem vínculo permanente grupo↔cloud).
- Administradores fora do select de grupos; coluna **Grupo**; sem excluir admins na lista.
- Após cadastrar, abre edição com o `id` retornado.
- Endpoint: `GET clouds/cadastro/grupos/{grupocloud}/usuarios`.

### Arquivos

- `app/Http/Controllers/CloudController.php` (`listarClouds`, `edit`, `usuariosDoGrupo`, `storeCloud`, `updateCloud`)
- `resources/js/components/cloud/cadastro.vue`
- `routes/web.php`

---

## 3. Segurança — IDOR

### Problema
URL do tipo `/g/cloud/{id}/{titulo}` e APIs por ID permitiam enumeração / acesso cruzado sem membership.

### O que foi feito

- `Cloud::usuarioTemAcesso()` — membership em `user_clouds` + cloud ativo.
- `Cloud::encontrarAutorizadoOuAbortar($id)` — 404 se não existe/tenant; 403 se sem acesso.
- `Cloud::encontrarAutorizadoPorSlugOuAbortar($slug)` — mesma lógica por slug.
- Controllers passam a autorizar cloud antes de listar/criar/editar/mover/upload.
- Itens validados com `where('cloud_id', ...)`; troca de `cloud_id` via payload bloqueada no update.
- Middleware `can:cloud` nas rotas de acesso ao Cloud.

### Arquivos

- `app/Models/Cloud.php`
- `app/Http/Controllers/CloudController.php`
- `app/Http/Controllers/ItensCloudController.php`
- `routes/web.php`

---

## 4. URLs amigáveis (slug + path)

### Formato

```
/g/cloud/dp-rh
/g/cloud/dp-rh?path=documentos/folha
```

- Cloud identificado por **slug** (único por `empresa_id`), gerado de `nome` (`Str::slug` + sufixo se colidir).
- Pastas: query `path` com segmentos slugificados dos labels.
- URL antiga `/g/cloud/{id}/{titulo}` → redirect **301** para o slug.
- Menu atualizado para linkar pelo slug.

### Backend

- Migration: `database/migrations/2026_08_11_004541_add_slug_to_clouds_table.php` (coluna + backfill + unique `(empresa_id, slug)`).
- Model gera/atualiza slug no `saving`.
- `GET cloud/{slug}` → `getSingle`.
- `GET cloud/{slug}/resolver-path?path=a/b/c` → resolve pasta + breadcrumb (com `TemPermissao` por nível).
- Lista de itens inclui `slug` do label para montar o path no frontend.

### Frontend

- `Cloud.vue`: `history.pushState` / `replaceState` / `popstate` sincronizam `?path=`.
- Deep-link: ao abrir com `?path=`, chama `resolver-path` e monta breadcrumb.

### Arquivos

- `app/Models/Cloud.php`
- `app/Http/Controllers/CloudController.php`
- `resources/js/components/Cloud.vue`
- `resources/views/g/cloud/index.blade.php`
- `resources/views/layouts/menu.blade.php`
- `routes/web.php`
- Migration de slug

---

## 5. Anexos privados e URLs assinadas

### Problema
Anexos ficavam em `/publico/cloud/anexo/...` (públicos). Dados sensíveis.

### O que foi feito

- Disco `disco-cloud` com `visibility: private`.
- Rotas públicas `/publico/cloud/*` **removidas**.
- Accessors em `Arquivo` para `disco-cloud`:
  - `url` / `urlThumb` / `urlDownload` / `urlDelete` → `URL::temporarySignedRoute` (TTL 60 min).
  - URLs **relativas** + middleware `signed:relative` (estável atrás de proxy/CDN; não quebra por APP_URL/host).
  - **Thumb** (`_p`) só na **listagem** (`urlThumb`).
  - **Visualização** usa a imagem **original** (`url`).
- Rotas autenticadas + assinadas:
  - `GET g/cloud/anexo/{arquivo}` — show (`signed:relative` + `can:cloud`)
  - `GET g/cloud/anexoDownload/{arquivo}` — download (`signed:relative` + `can:cloud`)
  - `DELETE g/cloud/anexo/{arquivo}` — delete (`signed:relative` + `can:cloud`)
- `TrustProxies` com `$proxies = '*'` para respeitar `X-Forwarded-*` (Cloudflare/LB).
- Autorização em `CloudController::autorizarArquivoCloud()`:
  1. Usuário autenticado
  2. Arquivo existe no disco Cloud
  3. Item vinculado existe
  4. Membership no Cloud (`encontrarAutorizadoOuAbortar`)
  5. Mesma `empresa_id`
  6. `TemPermissao` no item  
  Caso contrário → 401/403/404.
- Ordem das rotas: anexos **antes** de `cloud/{id}/{titulo}` (evita 404 por captura de `anexo` como ID).
- Constraint `where('arquivo', '.*')` para nomes com extensão.
- Frontend: download via `urlDownload`; listagem via `urlThumb`; visualizar via `url`; preview de não-imagem em iframe direto (sem Google Viewer, para respeitar auth).

### Arquivos

- `config/filesystems.php`
- `app/Models/Arquivo.php`
- `app/Http/Controllers/CloudController.php`
- `resources/js/components/Cloud.vue`
- `routes/web.php`

---

## 6. Frontend — correções UX e bugs Vue 3

### Modais / estado inicial

- Botão **Nova Pasta** não chamava `formNovaPasta()` (faltavam `()`).
- Ao fechar qualquer modal (pasta, apagar, aprovar, revisar, mover, atualizar, e-mails, visualizar, detalhes), o estado volta ao inicial (`@fechou` + resets).
- Abrir Nova Pasta / ações do dropdown sempre partem limpo.

### Vue 3 — `v-if` + `v-for`

- Em `Cloud.vue` e `PastaCloud.vue`: `v-for` no `<template>` e `v-if` no elemento interno (prioridade do Vue 3).
- Corrige erro `Property "item" was accessed during render` no modal **Mover**.

### Dropdown de ações

- Menu via `Teleport` + posição `fixed` (evita corte por `overflow` da tabela / `.main-content`).

### Arquivos

- `resources/js/components/Cloud.vue`
- `resources/js/components/PastaCloud.vue`

---

## Como testar (checklist)

1. **Cadastro Cloud**  
   - Criar/editar, adicionar por grupo e por colaborador; admins protegidos.

2. **Administradores**  
   - Negar permissão de Administradores na pasta (não deve permitir).  
   - Alterar membros do grupo Administradores e conferir `user_clouds`.  
   - Opcional: `php artisan cloud:garantir-permissao-administradores`.

3. **IDOR**  
   - Usuário sem vínculo no cloud acessar `/g/cloud/{slug}` → 403.  
   - URL antiga `/g/cloud/{id}/{titulo}` → 301 para slug.

4. **Navegação**  
   - Entrar em pastas → URL com `?path=...`.  
   - Voltar/avançar do browser restaura breadcrumb.  
   - Abrir link com path direto.

5. **Anexos**  
   - Listagem mostra thumb.  
   - Visualizar imagem mostra original.  
   - Download só logado + mesma empresa + membership + permissão + assinatura válida.  
   - URL sem assinatura / sem auth → bloqueada.  
   - `/publico/cloud/...` não deve mais servir arquivo.

6. **Modais**  
   - Criar pasta → fechar → Nova Pasta de novo (form limpo).  
   - Mover arquivo/pasta sem erro de console.

---

## Migration / deploy

```bash
php artisan migrate --path=database/migrations/2026_08_11_004541_add_slug_to_clouds_table.php
# ou migrate completo no ambiente

php artisan cloud:garantir-permissao-administradores   # se ainda houver itens sem Admin

npm run prod   # ou npm run dev — assets Cloud.vue / PastaCloud / cadastro
```

Garantir `APP_KEY` estável (assinatura de URL depende dela) e `APP_URL` alinhado ao host usado no browser.

---

## Arquivos tocados (visão geral)

| Área | Arquivos |
|------|----------|
| Models | `Cloud.php`, `ItensCloud.php`, `GrupoCloud.php`, `Arquivo.php` |
| Controllers | `CloudController.php`, `ItensCloudController.php`, `CloudConfiguracaoController.php` |
| Command | `CloudGarantirPermissaoAdministradoresCommand.php` |
| Migration | `2026_08_11_004541_add_slug_to_clouds_table.php` |
| Config | `filesystems.php` |
| Routes | `routes/web.php` |
| Frontend | `Cloud.vue`, `PastaCloud.vue`, `cloud/cadastro.vue` |
| Views | `g/cloud/index.blade.php`, `layouts/menu.blade.php` |

---

## Fora de escopo nesta branch

- Slug persistido em cada pasta (só slug runtime do label na URL).
- Migração completa do Cloud para Composition API / services.
- Alteração do padrão de anexos de outros módulos (CIH, admissão, etc.).
