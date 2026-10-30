# API Externa v3 — API Reference

Referência completa dos endpoints da API Externa MyBP (`/api/v3`).

Para visão geral e onboarding, use o [Guia do cliente](API_V3_EXTERNA.md).  
Para Try it out interativo: [Swagger UI](/api/v3/docs) · spec [OpenAPI](openapi/api-externa-v3.yaml).

---

## Sumário

1. [Base](#1-base)
2. [Autenticação](#2-autenticação)
3. [Convenções](#3-convenções)
4. [Auth](#4-auth)
5. [Cloud](#5-cloud--cloudread)
6. [Fornecedores](#6-fornecedores--fornecedoresread)
7. [Avaliações](#7-avaliações--avaliacoesread)
8. [Processos](#8-processos--processosread)
9. [Currículos](#9-currículos--curriculosread)
10. [Fluxo](#10-fluxo--fluxoread)
11. [Vagas abertas](#11-vagas-abertas--vagasread)
12. [Centros de custo](#12-centros-de-custo--centros-custoread)
13. [CIH](#13-cih--cihread)
14. [Medidas administrativas](#14-medidas-administrativas--medidasread)
15. [Histórico](#15-histórico--historicoread)
16. [Pós-admissão](#16-pós-admissão--pos-admissaoread)
17. [Weekly Report](#17-weekly-report--weeklyread)
18. [Relatórios](#18-relatórios--relatoriosread)
19. [Erros](#19-erros)
20. [Índice rápido](#20-índice-rápido)

---

## 1. Base

| Item | Valor |
|------|--------|
| Prefixo | `/api/v3` |
| Protocolo | HTTPS em produção |
| Formato | JSON (`Accept: application/json`), exceto downloads binários |
| Versão do contrato | **3.0.0** |
| Modo | Somente leitura (GET), exceto `POST /oauth/token` |

**Headers padrão (recursos autenticados):**

```http
Authorization: Bearer {access_token}
Accept: application/json
```

---

## 2. Autenticação

Fluxo **client credentials**:

1. Credencial por empresa: `client_id` + `client_secret`
2. `POST /api/v3/oauth/token` → `access_token` + `expires_in` + `scope`
3. Demais rotas com Bearer Sanctum

Scopes são **habilidades** da credencial. Sem o scope do módulo → **403**.

| Scope | Módulo |
|-------|--------|
| `cloud:read` | Cloud |
| `fornecedores:read` | Fornecedores |
| `avaliacoes:read` | Avaliações de desempenho |
| `processos:read` | Processos / admissão |
| `curriculos:read` | Currículos |
| `fluxo:read` | Fluxo completo |
| `vagas:read` | Vagas abertas |
| `centros-custo:read` | Centros de custo |
| `cih:read` | CIH |
| `medidas:read` | Medidas administrativas |
| `historico:read` | Histórico |
| `pos-admissao:read` | Pós-admissão |
| `weekly:read` | Weekly Report |
| `relatorios:read` | Relatórios |

---

## 3. Convenções

### Identificadores

- Paths e JSON usam `public_id` / `*_public_id` no formato **UUID**
- IDs numéricos internos **não** são expostos
- `empresa_id` do tenant **não** aparece no JSON (implícito no token)

### Paginação

Query: `page` (default 1), `per_page` (máx. 100).

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 42,
    "last_page": 3
  }
}
```

Relatórios podem incluir `context` (resumo, gráficos, mês).

### Item único

```json
{
  "success": true,
  "data": { "public_id": "3fa85f64-5717-4562-b3fc-2c963f66afa6" }
}
```

### LGPD

- CPF: `***.***.***-XX`
- Pix / PIS: sufixo mascarado
- Entrevista de desligamento: campos livres sensíveis omitidos

### Path params

`{publicId}`, `{itemPublicId}`, etc. = UUID. UUID inválido ou recurso de outra empresa → **404**.

---

## 4. Auth

Sem Bearer. Rate limit dedicado.

### `POST /oauth/token`

Emite access token.

| Body (JSON) | Tipo | Obrigatório | Descrição |
|-------------|------|-------------|-----------|
| `grant_type` | string | sim | Sempre `client_credentials` |
| `client_id` | string | sim | ID público da credencial |
| `client_secret` | string | sim | Secret (só na criação/rotação) |

**200**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `success` | boolean | `true` |
| `access_token` | string | Token Bearer |
| `token_type` | string | `Bearer` |
| `expires_in` | integer | Segundos até expirar |
| `scope` | string | Scopes separados por espaço |

---

## 5. Cloud — `cloud:read`

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/cloud` | Lista clouds (`q`, `ativo`, paginação) |
| GET | `/cloud/{publicId}` | Detalhe + membros/grupos |
| GET | `/cloud/{publicId}/buscar` | Busca itens (`q`) |
| GET | `/cloud/{publicId}/pastas` | Árvore/listagem de pastas |
| GET | `/cloud/{publicId}/resolver-path` | Resolve path textual (`path`) |
| GET | `/cloud/{publicId}/itens` | Lista itens (paginado) |
| GET | `/cloud/{publicId}/itens/{itemPublicId}` | Detalhe do item |
| GET | `/cloud/{publicId}/itens/{itemPublicId}/download` | Download (binário) |
| GET | `/cloud/{publicId}/itens/{itemPublicId}/arquivo` | Stream do arquivo |
| GET | `/cloud/{publicId}/itens/{itemPublicId}/thumb` | Thumbnail |

**Query comuns (listagem):** `page`, `per_page`, `q`, `ativo`.

---

## 6. Fornecedores — `fornecedores:read`

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/fornecedores` | Listagem paginada |
| GET | `/fornecedores/buscar` | Busca (`q`, mín. 2 chars) |
| GET | `/fornecedores/tipos-servico` | Catálogo de tipos |
| GET | `/fornecedores/{publicId}` | Detalhe + serviços/anexos (metadados) |
| GET | `/fornecedores/{publicId}/servicos` | Serviços do fornecedor |
| GET | `/fornecedores/{publicId}/servicos/{servicoPublicId}` | Detalhe do serviço |
| GET | `/fornecedores/{publicId}/anexos/{arquivoPublicId}/download` | Download anexo |
| GET | `/fornecedores/{publicId}/anexos/{arquivoPublicId}/arquivo` | Stream anexo |
| GET | `/fornecedores/{publicId}/servicos/{servicoPublicId}/anexos/{arquivoPublicId}/download` | Download anexo do serviço |
| GET | `/fornecedores/{publicId}/servicos/{servicoPublicId}/anexos/{arquivoPublicId}/arquivo` | Stream anexo do serviço |

---

## 7. Avaliações — `avaliacoes:read`

Avaliações de **desempenho** (campanhas), não a avaliação de experiência de 90 dias (esta está em Relatórios).

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/avaliacoes` | Campanhas (paginado) |
| GET | `/avaliacoes/tipos` | Tipos de avaliação |
| GET | `/avaliacoes/tipos-avaliador` | Tipos de avaliador |
| GET | `/avaliacoes/tipos/{tipoPublicId}/topicos` | Tópicos/subtópicos |
| GET | `/avaliacoes/{publicId}` | Detalhe da campanha |
| GET | `/avaliacoes/{publicId}/feedbacks` | Feedbacks (`status`, `origem_feedback`, `principal`, `q`, …) |
| GET | `/avaliacoes/{publicId}/feedbacks/{feedbackPublicId}` | Detalhe + respostas |
| GET | `/avaliacoes/{publicId}/feedbacks/{feedbackPublicId}/resultado` | Consolidado |
| GET | `/avaliacoes/{publicId}/colaboradores/{funcionarioPublicId}/feedbacks` | Fluxo do colaborador |

---

## 8. Processos — `processos:read`

Pipeline de admissão. Payload alinhado às telas internas; objeto `empresa` (sem `cliente` duplicado).

### Listagens

| Método | Path | Query principais |
|--------|------|------------------|
| GET | `/processos/ativos` | `q`, `cpf`, `empresa_public_id`, `vaga_aberta_public_id`, `uf`, `tipo_admissao`, `data_admissao_de`, `data_admissao_ate`, `page`, `per_page` |
| GET | `/processos/em-andamento` | + `status_admissao`, `parecer_de`, `parecer_ate` |
| GET | `/processos/demitidos` | + `data_desmobilizacao_de`, `data_desmobilizacao_ate` |
| GET | `/processos/buscar` | `q` (mín. 2 chars) |

### Recurso e nested

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/processos/{publicId}` | Detalhe completo |
| GET | `/processos/{publicId}/etapas` | Mapa booleano de etapas |
| GET | `/processos/{publicId}/exames` | Exames/ASO (metadados) |
| GET | `/processos/{publicId}/documentos` | Docs do currículo por tipo |
| GET | `/processos/{publicId}/ferias` | Férias adquiridas |
| GET | `/processos/{publicId}/pre-admissao` | Pré-admissão |

### Objetos frequentes em `data`

| Campo | Conteúdo |
|-------|----------|
| `curriculo` | Nome, CPF mascarado; no detalhe: telefones, dependentes, escolaridade |
| `empresa` | Razão social, CNPJ, área |
| `admissao` | Cargo, status, datas, PIS mascarado |
| `parecer_*` / `resultado_integrado` | Pareceres do fluxo |
| `vaga_aberta` | Vaga + município |
| `links` | `self`, nested (`etapas`, `exames`, …) |

---

## 9. Currículos — `curriculos:read`

Currículos vinculados à empresa via processos (`feedback_curriculos`).

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/curriculos` | `q`, `cpf`, `uf`, `pcd`, `lido`, `vaga_public_id`, `status`, `selecionado`, `updated_de`, `updated_ate` |
| GET | `/curriculos/buscar` | `q` (mín. 2) |
| GET | `/curriculos/{publicId}` | Detalhe enriquecido |
| GET | `/curriculos/{publicId}/feedbacks` | `status`, `selecionado`; link para `/processos/{id}` |
| GET | `/curriculos/{publicId}/documentos` | Metadados por tipo |
| GET | `/curriculos/{publicId}/experiencias` | Experiências profissionais |

---

## 10. Fluxo — `fluxo:read`

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/fluxo/{publicId}` | Detalhe completo do processo + `etapas` |

Mesmo `publicId` de processo. Requer scope próprio além de `processos:read`.

---

## 11. Vagas abertas — `vagas:read`

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/vagas-abertas` | `q`, `ativo`, `ativo_sistema`, `vaga_public_id`, `municipio_public_id`, `uf` |
| GET | `/vagas-abertas/buscar` | `q` (mín. 2) |
| GET | `/vagas-abertas/{publicId}` | Detalhe + simulados/projetos |
| GET | `/vagas-abertas/{publicId}/simulados` | Provas vinculadas |
| GET | `/vagas-abertas/{publicId}/projetos` | Projetos (qnt total/preenchida) |

---

## 12. Centros de custo — `centros-custo:read`

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/centros-custo` | `q`, `ativo` |
| GET | `/centros-custo/buscar` | `q` |
| GET | `/centros-custo/{publicId}` | Detalhe + filiais + gestores |
| GET | `/centros-custo/{publicId}/filiais` | Filiais ativas |
| GET | `/centros-custo/{publicId}/gestores` | Nome/`tipo` (sem login) |

---

## 13. CIH — `cih:read`

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/cih` | `q`, `status`, filtros de tag/área/CC/gestor, datas |
| GET | `/cih/tags` | Catálogo de tags |
| GET | `/cih/{publicId}` | Detalhe (observações do fluxo) |
| GET | `/cih/{publicId}/colaboradores` | CPF mascarado + link para processo |

---

## 14. Medidas administrativas — `medidas:read`

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/medidas-administrativas` | `q`, `tipo`, `causa`, datas, `centro_custo_public_id` — **sem** `motivo` |
| GET | `/medidas-administrativas/{publicId}` | Detalhe **com** `motivo` |

---

## 15. Histórico — `historico:read`

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/historico` | `q`, `cpf`, `matricula`, `cargo`, `demitido`, … |
| GET | `/historico/{publicId}` | Detalhe (mesmo assembler de processo) |
| GET | `/historico/{publicId}/medidas` | Medidas do colaborador |
| GET | `/historico/{publicId}/afastamentos` | Afastamentos |

---

## 16. Pós-admissão — `pos-admissao:read`

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/pos-admissao` | `q`, `demitido`, `com_entrevista`, `vaga_public_id` |
| GET | `/pos-admissao/{publicId}` | Detalhe + resumo da entrevista |
| GET | `/pos-admissao/{publicId}/entrevista-desligamento` | Entrevista sanitizada |

---

## 17. Weekly Report — `weekly:read`

Quadros da empresa. O detalhe traz listas com tarefas completas: descrição, lembrete, membros, checklists (itens e responsáveis), comentários, anexos (nome e extensão) e histórico. Textos em texto puro. Sem login.

| Método | Path | Query / notas |
|--------|------|----------------|
| GET | `/weekly` | `q` (título do quadro) |
| GET | `/weekly/{publicId}` | Quadro completo, inclusive `comentarios` em cada tarefa |
| GET | `/weekly/{publicId}/listas` | Listas com tarefas completas |
| GET | `/weekly/{publicId}/listas/{listaPublicId}/tarefas` | `q`, `concluido` — cada item traz comentários |
| GET | `/weekly/{publicId}/tarefas/{tarefaPublicId}` | Mesmo payload da tarefa no quadro |

---

## 18. Relatórios — `relatorios:read`

JSON paginado. Sem PDF/Excel nesta API.

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/relatorios` | Catálogo (`slug`, `nome`, `filtros`, `links`) |
| GET | `/relatorios/{slug}` | Executa o relatório |

### Slugs

| Slug | Filtros | `context` |
|------|---------|-----------|
| `efetivo` | `q`, `cargo`, `tipo_admissao`, `centro_custo_public_id`, `data_admissao_de/ate` | — |
| `centro-custo` | `q`, `centro_custo_public_id` | — |
| `vencimento-asos` | `q`, `centro_custo_public_id`, `somente_alerta` | `dias_alerta` |
| `vencimento-treinamentos` | `q`, `centro_custo_public_id`, `data_vencimento_de/ate` | `periodo` |
| `vencimento-ferias` | `q`, `centro_custo_public_id`, `cargo` | — |
| `ferias` | `q`, `status_ferias`, `centro_custo_public_id`, `data_saida_de/ate` | — |
| `medidas-administrativas` | `q`, `tipo`, `causa`, datas, CC | `graficos` |
| `avaliacao-experiencia` | `q`, `status`, `cargo`, `funcao` | `resumo` |
| `aniversariantes` | `mes` (1–12) | `mes` |
| `nps` | `data_de`, `data_ate`, `ciclo_public_id` | `resumo` |

**Exemplo**

```http
GET /api/v3/relatorios/efetivo?page=1&per_page=20&q=Ana
Authorization: Bearer {access_token}
```

```json
{
  "success": true,
  "data": [
    {
      "processo_public_id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
      "nome": "Ana Relatorio",
      "cpf": "***.***.***-88",
      "cargo": "Analista",
      "links": { "processo": "/api/v3/processos/..." }
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 1, "last_page": 1 }
}
```

---

## 19. Erros

Sempre JSON (nunca HTML/redirect).

```json
{ "success": false, "msg": "..." }
```

Validação (422) pode incluir `errors` por campo.

| HTTP | Significado |
|------|-------------|
| 200 | OK |
| 401 | Token/credencial inválidos |
| 403 | Escopo insuficiente |
| 404 | Recurso inexistente nesta empresa |
| 422 | Payload inválido |
| 429 | Rate limit |
| 500 | Erro interno |

---

## 20. Índice rápido

| Método | Path | Scope |
|--------|------|-------|
| POST | `/oauth/token` | — |
| GET | `/cloud` … `/cloud/{id}/itens/{itemId}/thumb` | `cloud:read` |
| GET | `/fornecedores` … anexos | `fornecedores:read` |
| GET | `/avaliacoes` … feedbacks/resultado | `avaliacoes:read` |
| GET | `/processos/ativos` … nested | `processos:read` |
| GET | `/curriculos` … experiências | `curriculos:read` |
| GET | `/fluxo/{id}` | `fluxo:read` |
| GET | `/vagas-abertas` … projetos | `vagas:read` |
| GET | `/centros-custo` … gestores | `centros-custo:read` |
| GET | `/cih` … colaboradores | `cih:read` |
| GET | `/medidas-administrativas` … | `medidas:read` |
| GET | `/historico` … afastamentos | `historico:read` |
| GET | `/pos-admissao` … entrevista | `pos-admissao:read` |
| GET | `/weekly` … tarefas | `weekly:read` |
| GET | `/relatorios` · `/relatorios/{slug}` | `relatorios:read` |
| GET | `/docs` · `/openapi.yaml` | público (docs) |

---

## Documentação relacionada

| Artefato | Uso |
|----------|-----|
| [API_V3_EXTERNA.md](API_V3_EXTERNA.md) | Introdução e guia do cliente |
| [openapi/api-externa-v3.yaml](openapi/api-externa-v3.yaml) | Spec OpenAPI 3.0 |
| [postman/](postman/) | Collection + environment |
| [API_V3_EXTERNA_SEGURANCA.md](API_V3_EXTERNA_SEGURANCA.md) | Segurança (interno) |
| [API_V3_EXTERNA_IMPLEMENTACAO.md](API_V3_EXTERNA_IMPLEMENTACAO.md) | Implementação (interno) |
