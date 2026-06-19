# API v2 — Integração SPA (BFF)

API somente leitura para sites ou BFF que divulgam empresas e vagas abertas. **Não** substitui o grupo legado em `routes/api.php` (`as` => `vaga`); convive em paralelo com contrato e arquitetura novos.

## Fases de entrega (implementação)

1. **Contratos e dados** — Interfaces em `app/Contracts/IntegracaoSpa/` e DTOs em `app/Data/IntegracaoSpa/` definem o contrato HTTP sem expor Eloquent.
2. **Serviços** — `app/Services/IntegracaoSpa/*Eloquent.php` concentram queries (`Cliente::withoutGlobalScopes()`, `VagasAbertas::withoutGlobalScopes()`), filtros (`ativo` nas vagas, `ativo` + `apelido` nas empresas) e montagem dos DTOs.
3. **HTTP** — `IntegracaoSpaV2Controller` apenas orquestra respostas JSON; rotas em `routes/api.php` com prefixo `v2/integracao`.
4. **Injeção (SOLID / DIP)** — Bindings em `AppServiceProvider::register()`.
5. **Qualidade** — Testes em `tests/Feature/IntegracaoSpaV2ApiTest.php` (403, listagens, paginação, slug) com migrations mínimas via `Tests\Support\IntegracaoSpaV2Schema`; `tests/Feature/IntegracaoSpaMediaTest.php` (proxy por apelido); `tests/Unit/Support/VagaSpaSlugTest.php` para o slug.

## Postman

Importar a collection v2.1:

- Arquivo: [docs/postman/Integracao-SPA-v2.postman_collection.json](postman/Integracao-SPA-v2.postman_collection.json)

Variáveis sugeridas na collection:

| Variável | Exemplo | Uso |
|----------|---------|-----|
| `base_url` | `http://localhost:8000` | Origem da API (Docker mapeia 8000) |
| `X_API_TOKEN` | (seu segredo) | Mesmo valor da variável de ambiente `X_API_TOKEN` no servidor/BFF |
| `apelido` | `minha-empresa` | Slug da empresa no MyBP |
| `vaga_aberta_id` | `42` | ID numérico da vaga |
| `slug_vaga_aberta` | `analista-php` | Slug do título (`Str::slug` no Laravel) |
| `page` | `1` | Paginação da listagem de vagas |

Cada request envia o header `X-API-TOKEN: {{X_API_TOKEN}}`.

## Autenticação

- Middleware: `api` + `apitoken`.
- Header obrigatório: `X-API-TOKEN` (mesmo valor de `X_API_TOKEN` no ambiente; em testes PHPUnit, ver `phpunit.xml` e `putenv` nos testes).

O SPA em **browser** não deve embutir o token; o fluxo esperado é **BFF** ou servidor que repasse o header com segredo.

## Base URL

Todas as rotas abaixo são relativas ao prefixo da API Laravel (em geral `/api`):

`/api/v2/integracao`

## Rotas

### `GET /api/v2/integracao/`

Lista empresas **ativas** com `apelido` preenchido.

**Cache:** a resposta é armazenada em cache com TTL configurável (`INTEGRACAO_SPA_EMPRESAS_ATIVAS_CACHE_TTL_SECONDS`, padrão **7 dias** / 604800 s, mínimo 60). O cache é **invalidado** ao salvar ou excluir um `Cliente` via Eloquent e ao alterar ou excluir (antes do `DELETE`) um `Arquivo` que ainda esteja em `cliente_logotipo`. Atualizações em `clientes` só por Query Builder **não** disparam observer; nesses casos chame a invalidação manualmente ou use o model.

**Resposta (`200`):**

```json
{
  "success": true,
  "dados": [
    {
      "id": 1,
      "razao_social": "…",
      "apelido": "minha-empresa",
      "missao": "…",
      "visao": "…",
      "valores": "…",
      "endereco": {
        "logradouro": "…",
        "numero": "…",
        "complemento": "…",
        "bairro": "…",
        "municipio": "…",
        "uf": "…",
        "cep": "…"
      },
      "endereco_completo": "…",
      "logotipo": {
        "url": "https://seu-dominio/api/v2/integracao/media/minha-empresa/logotipo?thumb=0",
        "url_thumb": "https://seu-dominio/api/v2/integracao/media/minha-empresa/logotipo?thumb=1",
        "imagem": true,
        "layout": "…",
        "logo_date": "2026-05-10T18:30:00Z"
      }
    }
  ]
}
```

`logotipo` pode ser `null` se não houver arquivo vinculado.

**`logo_date`:** data/hora da última alteração do arquivo do logo, em **UTC** no formato `YYYY-MM-DDTHH:MM:SSZ` (comparável como string). Reflete `arquivos.updated_at` (ou `created_at` se o `updated_at` bruto estiver vazio). O valor é obtido a partir das colunas no banco (formato `Y-m-d H:i:s`), evitando conflito com o cast de exibição do model `Arquivo`. O front pode guardar esse valor e, na próxima resposta da API, comparar com o anterior: se for igual, não precisa refazer download da imagem; se mudou, atualizar `src` da imagem (por exemplo acrescentando `&v=` + `encodeURIComponent(logo_date)` na URL do proxy para quebrar cache do browser).

Os campos `url` e `url_thumb` **não** apontam direto para AWS/CDN: são URLs do próprio MyBP com o **apelido** da empresa no path. O navegador ou o BFF pode usar esses links em `<img src="…">` **sem** header `X-API-TOKEN` (rota pública).

**Origem absoluta no JSON:** se a API for montada em um host interno mas o SPA abrir imagens em outro domínio, defina `INTEGRACAO_SPA_PUBLIC_URL` com a origem pública (ex.: `https://rh.empresa.com`); caso contrário usa `APP_URL`.

---

### `GET /api/v2/integracao/media/{apelido}/logotipo` (pública)

**Middleware:** apenas `api` (não usa `apitoken` nem assinatura).

**Path:** `apelido` — mesmo valor do campo `apelido` da empresa na API (`[a-zA-Z0-9_-]+`).

**Query opcional:**

| Parâmetro | Padrão | Descrição |
|-----------|--------|-----------|
| `thumb` | `0` | `0` = imagem principal, `1` = miniatura (se existir no cadastro) |

**Resposta:** stream da imagem (`Content-Type` conforme extensão) servida pelo Laravel a partir do disco configurado (**proxy**). `404` se empresa inexistente/inativa, sem logotipo ou arquivo ausente no storage.

---

### `GET /api/v2/integracao/{apelido}`

Dados da empresa ativa + até **6** vagas abertas com `ativo = true` (ordenadas por `updated_at` desc).

**Resposta (`200`):**

```json
{
  "success": true,
  "dados": {
    "empresa": { "… mesmo formato do item da lista …" },
    "vagas_abertas": [
      {
        "id": 10,
        "slug": "titulo-da-vaga",
        "titulo": "…",
        "descricao": "…",
        "municipio": { "id": 1, "nome": "…", "uf": "MA" },
        "cargo": { "id": 2, "nome": "…" },
        "publicado_em": "2026-01-01T12:00:00+00:00"
      }
    ]
  }
}
```

**`404`:** empresa inexistente ou inativa / sem `apelido` correspondente.

---

### `GET /api/v2/integracao/{apelido}/vagas-abertas?page=1`

Mesmo bloco `empresa` + lista paginada de vagas ativas (**50** por página; configurável em `INTEGRACAO_SPA_VAGAS_POR_PAGINA` / `config/integracao_spa.php`). Query `page` opcional (padrão `1`).

**Cache:** o bloco `vagas_abertas` (itens + paginação) é cacheado por empresa/página/TTL (**padrão 24 h** / 86400 s, `INTEGRACAO_SPA_VAGAS_PAGINA_CACHE_TTL_SECONDS`). O objeto **`empresa`** é montado **a cada request** (dados da empresa e logo sempre atuais). A invalidação do cache de vagas ocorre quando uma `VagasAbertas` dessa empresa é salva ou excluída pelo Eloquent (observer). Atualizações só via Query Builder **não** disparam observer — use `IntegracaoSpaVagasAbertasPaginaCache::bumpEmpresa($empresaId)` ou prefira o model.

**Resposta (`200`):**

```json
{
  "success": true,
  "dados": {
    "empresa": { "…" },
    "vagas_abertas": {
      "itens": [ { "…": "mesmo item da preview …" } ],
        "paginacao": {
        "pagina_atual": 1,
        "ultima_pagina": 3,
        "por_pagina": 50,
        "total": 42
      }
    }
  }
}
```

---

### `GET /api/v2/integracao/{apelido}/vagas-abertas/{vaga_aberta_id}/{slug_vaga_aberta}`

Detalhe de uma vaga ativa. O segmento `slug_vaga_aberta` deve ser exatamente o slug gerado a partir do **título** da vaga (`Str::slug` do Laravel; título vazio cai no slug literal `vaga`).

**Resposta (`200`):**

```json
{
  "success": true,
  "dados": {
    "empresa": { "…" },
    "vaga_aberta": { "… mesmo shape do item da lista …" }
  }
}
```

**`404`:** empresa não encontrada, vaga inexistente/inativa, ou slug incorreto.

---

### Recrutamento (currículo) — mesmo contrato do legado `/api/busca-*` / `/api/cadastra-curriculo`

**Middleware:** `api` + `apitoken` + header `X-API-TOKEN`.

O **`empresa_id` não deve ser enviado pelo cliente** (ou, se enviado, é **substituído** pelo id da empresa ativa cujo `apelido` está na URL). Assim o BFF não candidata em nome de outra empresa.

| Método | Rota | Corpo (JSON) | Observação |
|--------|------|--------------|------------|
| `POST` | `/api/v2/integracao/{apelido}/busca-curriculo` | `cpf`, `nascimento` (mesmos campos do legado) | Delega a `VagaAbertaController::buscaCurriculo` com `empresa_id` resolvido. |
| `POST` | `/api/v2/integracao/{apelido}/busca-cpf` | `cpf` | Lista de objetos `{ cpf, created_at }` quando já existe candidato com esse CPF na empresa (vazio `[]` se não houver ou CPF inválido). |
| `POST` | `/api/v2/integracao/{apelido}/cadastra-curriculo` | Igual `POST /api/cadastra-curriculo` (`vaga_aberta_id`, `cpf_padrao`, demais campos do formulário) | Delega a `VagaAbertaController::store` com `empresa_id` da URL. |

**`404`:** `apelido` sem empresa ativa.

A rota legada `POST /api/busca-cpf` também passa a usar `VagaAbertaController::buscaCpf` (é necessário enviar `empresa_id` no body para retornar duplicidade; o front público de vagas pode precisar ser ajustado).

## Restrições de rota

- `apelido`: `[a-zA-Z0-9_-]+`
- `vaga_aberta_id`: numérico
- `slug_vaga_aberta`: `[a-zA-Z0-9\-]+`

## Código relacionado

| Camada | Caminho |
|--------|---------|
| Controller JSON | `app/Http/Controllers/Api/IntegracaoSpaV2Controller.php` |
| Controller mídia (proxy) | `app/Http/Controllers/Api/IntegracaoSpaMediaController.php` |
| Controller recrutamento (currículo v2) | `app/Http/Controllers/Api/IntegracaoSpaCurriculoController.php` |
| Contratos | `app/Contracts/IntegracaoSpa/` |
| Serviços | `app/Services/IntegracaoSpa/` |
| DTOs | `app/Data/IntegracaoSpa/` |
| Slug | `app/Support/IntegracaoSpa/VagaSpaSlug.php` |
| Cache listagem empresas ativas | `app/Support/IntegracaoSpa/IntegracaoSpaEmpresasAtivasCache.php` |
| Cache paginação vagas abertas | `app/Support/IntegracaoSpa/IntegracaoSpaVagasAbertasPaginaCache.php` |
| Observers (invalidação) | `ClienteIntegracaoSpaEmpresasAtivasCacheObserver`, `ArquivoLogotipoIntegracaoSpaEmpresasAtivasCacheObserver`, `VagasAbertasIntegracaoSpaPaginaCacheObserver` |
| Config (URL pública, TTL cache, itens por página vagas) | `config/integracao_spa.php` |

## Observação sobre `Cliente` e escopo global

O model `Cliente` aplica escopo que depende de `auth()->user()`. Nesta integração **todas** as leituras usam `withoutGlobalScopes()` nos serviços para funcionar com `apitoken` sem sessão de usuário.
