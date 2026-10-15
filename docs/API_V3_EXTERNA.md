# API Externa v3 — Guia para o cliente

## Introdução

A **API Externa v3** é a interface oficial do MyBP para que sistemas externos, agents de IA e integrações leiam dados de RH da **sua empresa**, de forma segura e padronizada.

Ela foi pensada para responder perguntas operacionais do dia a dia — “quem está admitido?”, “quais ASOs vencem?”, “como está o processo do colaborador?” — sem abrir o sistema web e sem misturar dados de outros clientes.

### O que ela é

- API **REST** em JSON, prefixo `/api/v3`
- **Somente leitura** (GET): consulta listagens, detalhes e relatórios
- Autenticação por **credenciais da empresa** (`client_id` + `client_secret` → Bearer token)
- Isolamento multi-tenant: o token enxerga **apenas** a empresa da credencial
- Identificadores públicos em **UUID** (`public_id`) — sem IDs numéricos internos
- Campos sensíveis tratados para LGPD (ex.: CPF mascarado)

### Para quem é

| Perfil | Uso típico |
|--------|------------|
| Integrações ERP / BI | Sincronizar efetivo, centros de custo, vagas |
| Agents / automações | Consultar processos, histórico, relatórios |
| Portais e apps parceiros | Exibir status de admissão ou documentos (metadados) |
| Times de dados | Extrair bases para dashboards (via JSON paginado) |

### O que você consegue consultar

| Área | Exemplos |
|------|----------|
| Processos e fluxo | Admitidos, em andamento, demitidos, etapas, exames, férias |
| Currículos e vagas | Currículos vinculados, vagas abertas, simulados |
| Cadastros | Centros de custo, fornecedores, Cloud (arquivos) |
| Operacional | CIH, medidas administrativas, pós-admissão |
| Relatórios | Efetivo, ASO, treinamentos, férias, aniversariantes, NPS, etc. |
| Avaliações | Campanhas de desempenho e resultados |

### O que ela não faz (nesta versão)

- Não cria, altera ou exclui registros (sem POST/PUT/PATCH/DELETE de negócio)
- Não gera Excel/PDF na API (use o MyBP web para exportações em fila)
- Não devolve `empresa_id` nem IDs internos sequenciais
- Não mistura dados entre empresas — filtro é o token, não um parâmetro opcional

### Princípios de uso

1. **Peça as credenciais** ao suporte MyBP (ou gere com `php artisan api-externa:credencial {empresa_id}`).
2. **Obtenha o token** em `POST /api/v3/oauth/token` e renove perto do vencimento.
3. **Respeite os scopes** — cada módulo exige um escopo (`processos:read`, `relatorios:read`, …).
4. **Paginar** listagens (`page`, `per_page`) e seguir `links` nos payloads.
5. **Não logar** `client_secret` nem o Bearer completo.

Documentação interativa: [Swagger UI](/api/v3/docs) · [API Reference](API_V3_EXTERNA_REFERENCE.md) · Collection Postman em `docs/postman/`.

## Pacote de documentação

| Artefato | Uso |
|----------|-----|
| [API Reference](API_V3_EXTERNA_REFERENCE.md) | Catálogo completo de endpoints, scopes, query params e erros |
| [Swagger UI](/api/v3/docs) | Documentação interativa com exemplos (Try it out) |
| [OpenAPI YAML](openapi/api-externa-v3.yaml) | Spec formal: guias, schemas, exemplos de request/response |
| [Postman Collection](postman/Api-Externa-v3.postman_collection.json) | Pasta **0. Getting Started**, exemplos salvos e scripts que capturam IDs |
| [Postman Environment](postman/Api-Externa-v3.postman_environment.json) | `base_url`, credenciais e IDs públicos |

**Postman — fluxo sugerido:** importar collection + environment → preencher `client_id`/`client_secret` → rodar *Obter token* → *Listar processos ativos* → explorar módulos/relatórios.

## Credenciais

O MyBP gera um par por empresa:

- `client_id` (público)
- `client_secret` (privado — mostrado **apenas uma vez** na criação/rotação)

Solicite as credenciais ao suporte MyBP. Guarde o secret em cofre; não versionar em repositório.

## Autenticação

### 1) Obter token

```http
POST /api/v3/oauth/token
Content-Type: application/json

{
  "grant_type": "client_credentials",
  "client_id": "mybp_...",
  "client_secret": "..."
}
```

Resposta:

```json
{
  "success": true,
  "access_token": "...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "cloud:read fornecedores:read ..."
}
```

### 2) Chamar recursos

```http
GET /api/v3/cloud
Authorization: Bearer {access_token}
Accept: application/json
```

Renove o token antes de `expires_in`. Não envie `client_secret` nas rotas de dados.

## Scopes (somente leitura)

| Scope | Endpoints |
|-------|-----------|
| `cloud:read` | Cloud: listagem, detalhe, itens, busca, pastas, path, download/preview |
| `fornecedores:read` | Fornecedores: listagem, busca, tipos, serviços, anexos |
| `avaliacoes:read` | Avaliações: campanhas, feedbacks, resultado, tipos/tópicos |
| `processos:read` | Processos: ativos, em-andamento, demitidos, busca, nested |
| `curriculos:read` | Currículos: listagem, busca, feedbacks, documentos, experiências |
| `fluxo:read` | `/fluxo/{public_id}` |
| `vagas:read` | Vagas abertas: listagem, busca, simulados, projetos |
| `centros-custo:read` | Centros de custo: listagem, filiais, gestores |
| `cih:read` | CIH: listagem, tags, colaboradores |
| `medidas:read` | Medidas administrativas |
| `historico:read` | Histórico: listagem, medidas, afastamentos |
| `pos-admissao:read` | Pós-admissão + entrevista de desligamento (sanitizada) |
| `relatorios:read` | Relatórios do menu RH (efetivo, ASO, férias, NPS, etc.) |

## Paginação

Query params: `page` (default 1), `per_page` (máx. 100, default configurável).

Envelope:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 0,
    "last_page": 1
  }
}
```

## Identificadores (`public_id`)

- Nenhum ID numérico interno é exposto
- Formato **UUID**: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
- Em **todos** os GETs de detalhe o path é `{public_id}` (string UUID) — **não** envie integer
- Fluxo típico: listar → pegar `public_id` do item → chamar detalhe/itens/fluxo com esse valor
- Use `public_id` (e `*_public_id` em vínculos) nas URLs e no payload
- Ex.: `GET /api/v3/cloud/c04baf02-03e4-9e34-b2c3-91a99e0b1a66`
- `empresa_id` **não** vem no response — a empresa já está definida pelas credenciais/token

## Cloud

Endpoints alinhados ao Cloud interno (somente leitura):

| Endpoint | Uso |
|----------|-----|
| `GET /cloud` | Listagem (`?q=&ativo=`) + contagens |
| `GET /cloud/{id}` | Detalhe: membros + grupos |
| `GET /cloud/{id}/itens` | Navegação (`pasta_public_id`, `tipo`, `aprovado`, `revisado`, `q`, `recursivo`) |
| `GET /cloud/{id}/itens/{item}` | Detalhe do item + `caminho` (breadcrumb) e `path` |
| `GET /cloud/{id}/buscar?q=` | Busca global por label (mín. 2 chars) |
| `GET /cloud/{id}/pastas` | Só pastas + contagem de filhos (`pasta_public_id` opcional) |
| `GET /cloud/{id}/resolver-path?path=` | Resolve slugs (`documentos/rh`) → pasta + breadcrumb |
| `GET /cloud/{id}/itens/{item}/download\|arquivo\|thumb` | Binário (Bearer) |

`links` em cada item: `self`, `children`/`pastas` (pasta) ou `url`/`url_download`/`url_thumb` (arquivo). Sem path/chave de storage.

## Fornecedores

Endpoints alinhados à tela de Administração → Fornecedores (somente leitura):

| Endpoint | Uso |
|----------|-----|
| `GET /fornecedores` | Listagem (`q`, `tipo`, `tipo_pessoa`, `ativo`, `uf`, `tipo_servico_public_id`) |
| `GET /fornecedores/buscar?q=` | Busca (mín. 2 chars) |
| `GET /fornecedores/tipos-servico` | Catálogo de tipos de serviço |
| `GET /fornecedores/{id}` | Detalhe + telefones, serviços, anexos |
| `GET /fornecedores/{id}/servicos` | Serviços (`ativo`, `status`, `tipo_servico_public_id`) |
| `GET /fornecedores/{id}/servicos/{servico}` | Detalhe do serviço + anexos |
| `GET .../anexos/{arquivo}/download\|arquivo` | Binário do anexo do fornecedor |
| `GET .../servicos/{s}/anexos/{a}/download\|arquivo` | Binário do anexo do serviço |

Payload com `links`, CPF mascarado; anexos sem chave/path de storage.

## Avaliações

Endpoints alinhados às telas de Avaliações de desempenho (somente leitura):

| Endpoint | Uso |
|----------|-----|
| `GET /avaliacoes` | Campanhas (`q`, `status`, `ativo`, `ano_avaliacao`, `tipo_pj`, `avaliacao_tipo_public_id`) |
| `GET /avaliacoes/tipos` | Catálogo de tipos de avaliação |
| `GET /avaliacoes/tipos-avaliador` | Catálogo de tipos de avaliador |
| `GET /avaliacoes/tipos/{id}/topicos` | Árvore de tópicos/subtópicos |
| `GET /avaliacoes/{id}` | Detalhe da campanha + fluxo + contagens |
| `GET /avaliacoes/{id}/feedbacks` | Etapas (`status`, `origem_feedback`, `principal`, pessoas, `q`) |
| `GET /avaliacoes/{id}/feedbacks/{fid}` | Detalhe + respostas + comentário |
| `GET /avaliacoes/{id}/feedbacks/{fid}/resultado` | Consolidado (médias, etapas, PDI) |
| `GET /avaliacoes/{id}/colaboradores/{uid}/feedbacks` | Fluxo completo do colaborador |

Payload com `links`; sem token de impressão; sem CPF/dados sensíveis de admissão. Comentários e planos de ação só no detalhe/resultado.

## Processos e fluxo (payload de admissão)

Endpoints alinhados às telas de Admissão / Histórico / Pré-admissão (somente leitura), com IDs públicos e campos sensíveis sanitizados.

| Endpoint | Uso |
|----------|-----|
| `GET /processos/ativos` | Admitidos sem demissão (`q`, `cpf`, `empresa_public_id`, `vaga_aberta_public_id`, `uf`, `tipo_admissao`, `data_admissao_de/ate`) |
| `GET /processos/em-andamento` | Com RI e ainda não admitidos (`status_admissao`, `parecer_de/ate`, demais filtros) |
| `GET /processos/demitidos` | Com demissão (`data_desmobilizacao_de/ate` + filtros comuns) |
| `GET /processos/buscar?q=` | Busca global (mín. 2 chars) |
| `GET /processos/{id}` | Detalhe completo (`edit`) + `links` nested |
| `GET /processos/{id}/etapas` | Mapa booleano de etapas do fluxo |
| `GET /processos/{id}/exames` | Lista de exames/ASO (metadados) |
| `GET /processos/{id}/documentos` | Docs do currículo por tipo (sem download) |
| `GET /processos/{id}/ferias` | Férias adquiridas da admissão |
| `GET /processos/{id}/pre-admissao` | Registro de pré-admissão |
| `GET /fluxo/{id}` | Detalhe completo + `etapas` (scope `fluxo:read`) |

Objetos principais no `data`:

- `curriculo` (CPF mascarado; no detalhe: `telefones`, `dependentes`, `escolaridade`)
- `telefone_principal`, `conta_banco` (chave Pix mascarada)
- `parecer_rh`, `parecer_tecnica`, `parecer_rota`, `parecer_teste`
- `resultado_integrado`, `vaga_aberta` (+ vaga/município/label)
- `empresa` (razão social, CNPJ, área)
- `admissao` (cargo, salário, status, datas, EPI/NR, PIS mascarado; no detalhe: `dados_admissao`, `ferias_adquiridas`)
- `ultimo_aso`, `demissao`, `links`
- etapas: `curriculo`, `parecer_rh`, `resultado_integrado`, `pre_admissao`, `admissao`, `pos_admissao`, `demitido`

## Currículos

Currículos vinculados à empresa via `feedback_curriculos` (somente leitura):

| Endpoint | Uso |
|----------|-----|
| `GET /curriculos` | Listagem (`q`, `cpf`, `uf`, `pcd`, `lido`, `vaga_public_id`, `status`, `selecionado`, `updated_de/ate`) |
| `GET /curriculos/buscar?q=` | Busca (mín. 2 chars) |
| `GET /curriculos/{id}` | Detalhe + telefones, dependentes, escolaridade, feedbacks, contagens |
| `GET /curriculos/{id}/feedbacks` | Feedbacks paginados (`status`, `selecionado`) com link para `/processos/{id}` |
| `GET /curriculos/{id}/documentos` | Docs por tipo (metadados, sem download) |
| `GET /curriculos/{id}/experiencias` | Experiências profissionais (sem referências pessoais) |

CPF mascarado; isolamento por empresa da credencial.

## Vagas abertas

| Endpoint | Uso |
|----------|-----|
| `GET /vagas-abertas` | Listagem (`q`, `ativo`, `ativo_sistema`, `vaga_public_id`, `municipio_public_id`, `uf`) |
| `GET /vagas-abertas/buscar?q=` | Busca (mín. 2 chars) |
| `GET /vagas-abertas/{id}` | Detalhe + simulados/projetos |
| `GET /vagas-abertas/{id}/simulados` | Provas vinculadas |
| `GET /vagas-abertas/{id}/projetos` | Vínculos de projeto (qnt total/preenchida) |

## Centros de custo

| Endpoint | Uso |
|----------|-----|
| `GET /centros-custo` | Listagem (`q`, `ativo`) |
| `GET /centros-custo/buscar?q=` | Busca |
| `GET /centros-custo/{id}` | Detalhe + filiais + gestores |
| `GET /centros-custo/{id}/filiais` | Filiais ativas |
| `GET /centros-custo/{id}/gestores` | Gestores (nome/`tipo`, sem login) |

## CIH

| Endpoint | Uso |
|----------|-----|
| `GET /cih` | Listagem (`q`, `status`, tag/área/CC/gestor, datas) |
| `GET /cih/tags` | Catálogo de tags |
| `GET /cih/{id}` | Detalhe (observações do fluxo) |
| `GET /cih/{id}/colaboradores` | Colaboradores (CPF mascarado) + link para processo |

## Medidas administrativas

| Endpoint | Uso |
|----------|-----|
| `GET /medidas-administrativas` | Listagem (`q`, `tipo`, `causa`, datas, CC) — sem `motivo` |
| `GET /medidas-administrativas/{id}` | Detalhe com `motivo` |

## Histórico

Listagem alinhada à tela Histórico (admitidos). Detalhe reutiliza o payload de processo.

| Endpoint | Uso |
|----------|-----|
| `GET /historico` | Listagem (`q`, `cpf`, `matricula`, `cargo`, `demitido`, …) |
| `GET /historico/{id}` | Detalhe completo (assembler) |
| `GET /historico/{id}/medidas` | Medidas do colaborador |
| `GET /historico/{id}/afastamentos` | Afastamentos |

## Pós-admissão

| Endpoint | Uso |
|----------|-----|
| `GET /pos-admissao` | Admitidos (`q`, `demitido`, `com_entrevista`, `vaga_public_id`) |
| `GET /pos-admissao/{id}` | Detalhe + resumo da entrevista |
| `GET /pos-admissao/{id}/entrevista-desligamento` | Entrevista sanitizada (sem textos livres sensíveis / salário) |

## Relatórios

Somente leitura dos relatórios do menu RH. Catálogo em `GET /relatorios`; cada slug em `GET /relatorios/{slug}` (paginado). Totais/resumo extras vêm em `context` quando aplicável.

| Endpoint | Uso |
|----------|-----|
| `GET /relatorios` | Catálogo (slug, nome, filtros, links) |
| `GET /relatorios/efetivo` | Admitidos (`q`, `cargo`, `tipo_admissao`, `centro_custo_public_id`, datas) |
| `GET /relatorios/centro-custo` | Totais por centro de custo |
| `GET /relatorios/vencimento-asos` | Último ASO atual (`somente_alerta`, `q`, CC) |
| `GET /relatorios/vencimento-treinamentos` | Vencimentos no período (`data_vencimento_de/ate`) |
| `GET /relatorios/vencimento-ferias` | Avos / períodos aquisitivos |
| `GET /relatorios/ferias` | Solicitações (aguardando/gozando) |
| `GET /relatorios/medidas-administrativas` | Medidas + `context.graficos` |
| `GET /relatorios/avaliacao-experiencia` | Avaliação 90 dias + `context.resumo` |
| `GET /relatorios/aniversariantes` | Admitidos do mês (`mes=1..12`) |
| `GET /relatorios/nps` | Respostas NPS da empresa + `context.resumo` |

Sem PDF/Excel nesta versão (só JSON). CPF mascarado; IDs públicos.

**Cobertura do menu RELATÓRIOS:** efetivo, centro de custo, ASO, treinamentos, férias, vencimento de férias, medidas, avaliação 90 dias, aniversariantes e NPS.

**Fora desta versão (de propósito):**
- Controle de usuários (auditoria interna por operador)
- Folha de ponto sintética (módulo Controle de Ponto)
- Exports PDF/Excel e links de avaliação 90 dias (ações de escrita/fila)
- CIH/Intermitente PDF — use os endpoints JSON `cih` / processos

## Dados sensíveis (LGPD)

- CPF retornado mascarado (`***.***.***-XX`)
- Chave Pix e PIS com sufixo mascarado
- Sem senhas, tokens internos ou download binário de arquivos do Cloud nesta versão
- Isolamento estrito pela empresa da credencial

## Códigos de erro

Erros da v3 **sempre** vêm em JSON (nunca HTML), no envelope:

```json
{ "success": false, "msg": "..." }
```

Validação (422) inclui `errors` com os campos. Em 500, sem `APP_DEBUG` a mensagem é genérica (`Erro interno do servidor`).

| HTTP | Significado |
|------|-------------|
| 401 | Token ausente/inválido ou credenciais inválidas |
| 403 | Escopo insuficiente |
| 404 | Recurso inexistente na empresa |
| 422 | Payload inválido |
| 429 | Rate limit |
| 500 | Erro interno (JSON) |

## Boas práticas

1. Renovar token automaticamente perto do vencimento
2. Não logar `client_secret` nem Bearer completo
3. Respeitar rate limit do endpoint de token
4. Usar HTTPS em produção

## Versão

- Contrato: **v3.0.0**
- Prefixo: `/api/v3`
