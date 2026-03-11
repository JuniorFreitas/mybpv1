# 05. Banco de Dados

> Convenções usadas neste documento
> - Confirmado no código: sustentado diretamente por migrations/models.
> - Inferido: relação ou risco deduzido a partir de nomes, FKs e uso em código.

## Panorama

### Linha do tempo de migrations

**Confirmado no código**

| Ano | Quantidade de migrations |
|---|---:|
| 2019 | 1 |
| 2021 | 407 |
| 2022 | 74 |
| 2023 | 35 |
| 2024 | 5 |
| 2025 | 15 |
| 2026 | 49 |

**Leitura**

- 2021 concentra a base estrutural original do sistema.
- 2025 e 2026 concentram a maior parte das evoluções recentes relevantes para senha forçada, aprovação extra, NPS, requisição de vaga moderna e assinatura digital.

## Entidades centrais

### `users`

**Confirmado no código**

Campos importantes:

- `id`
- `nome`, `login`, `password`
- `tipo`
- `grupo_id`
- `empresa_id`
- `ativo`, `temp`
- `require_password_reset`, `password_reset_days`, `password_changed_at`

**Arquivos-base**

- `database/migrations/2021_07_05_220855_create_users_table.php`
- `database/migrations/2025_06_10_002717_add_forced_password_reset_fields_to_users_table.php`
- `app/Models/User.php`

### `clientes`

**Confirmado no código**

Campos importantes:

- `id` como chave primária sem auto-incremento
- `apelido`, `cnpj`, `cpf`
- `razao_social`, `nome_fantasia`
- `area_id`
- `ativo`

**Observação confirmada**

A model `Cliente` usa `id` que, na prática, se conecta ao mesmo universo de IDs de `users` do tipo empresa.

**Arquivos-base**

- `database/migrations/2021_07_05_220855_create_clientes_table.php`
- `app/Models/Cliente.php`
- `app/Models/User.php`

### `curriculos`

**Confirmado no código**

Campos importantes:

- `id` primário
- `cpf` único
- identificação civil e endereço
- formação e vaga pretendida
- `usuario_lido`, `lido`, `datalido`

**Arquivos-base**

- `database/migrations/2021_07_05_220855_create_curriculos_table.php`
- `app/Models/Curriculo.php`

### `feedback_curriculos`

**Confirmado no código**

É a tabela mais central do domínio de RH. Campos importantes:

- `curriculo_id`
- `vaga_id`, `vagas_abertas_id`, `vaga_projeto_id`
- `cliente_id`, `empresa_id`
- `telefone_id`
- `selecionado`, `status`
- flags de contato, entrevista, envio de e-mail e WhatsApp

**Arquivos-base**

- `database/migrations/2021_07_05_220855_create_feedback_curriculos_table.php`
- `app/Models/FeedbackCurriculo.php`

### `admissoes`

**Confirmado no código**

Campos importantes:

- `feedback_id`
- `status`
- `tipo_admissao`
- `prazo_experiencia`
- `salario`, `cargo`, `funcao`
- `data_aso`, `data_admissao`, `data_entrega_area`, `data_desmobilizacao`
- `formulario_id`

**Arquivos-base**

- `database/migrations/2021_07_05_220855_create_admissoes_table.php`
- `app/Models/Admissao.php`

### `requisicao_vagas_movimentacao`

**Confirmado no código**

Entidade nova de planejamento com trilha completa de aprovação. Campos importantes:

- `empresa_id`, `cliente_id`
- `centro_custo_id`, `cargo_id`, `area_id`
- `quantidade`, `tipo_contratacao`, `prioridade`
- `gestor_id`
- `status_aprovacao`, `status_aprovacao_extra`, `status_aprovacao_rh`
- `custom_values`
- `deleted_at`

**Arquivos-base**

- `database/migrations/2026_02_10_000001_create_requisicao_vagas_movimentacao_table.php`
- `database/migrations/2026_02_20_000002_add_custom_values_to_requisicao_vagas_movimentacao.php`
- `app/Models/RequisicaoVagaMovimentacao.php`

### `documento_para_assinatura`, `documento_assinatura_signatarios`, `documento_assinatura_eventos`

**Confirmado no código**

Formam o agregado de assinatura digital.

**Campos críticos**

- documento: `empresa_id`, `tipo_documento`, `documentable_type`, `documentable_id`, `arquivo_id`, `arquivo_assinado_id`, `hash_sha256`, `status`, `token`, `data_expiracao`
- signatário: `email`, `nome`, `cpf`, `token`, `status`, `ip`, `user_agent`, `geolocalizacao`, `hash_evidencia`, `consentimento_*`
- evento: `evento`, `payload`

**Arquivos-base**

- `database/migrations/2026_02_24_000001_create_documento_para_assinatura_table.php`
- `database/migrations/2026_02_24_000002_create_documento_assinatura_signatarios_table.php`
- `database/migrations/2026_02_24_000003_create_documento_assinatura_eventos_table.php`
- `app/Models/DocumentoParaAssinatura.php`
- `app/Models/DocumentoAssinaturaSignatario.php`
- `app/Models/DocumentoAssinaturaEvento.php`

### `cliente_configs`

**Confirmado no código**

Tabela de feature flags e comportamento por empresa.

**Campos importantes**

- `envia_whatsapp`
- `modelo_cih`
- `schedule_avaliacao_experiencia`
- `schedule_treinamento_vencimento`
- `assinatura_digital_habilitada`
- `limite_assinaturas_mensal`
- `assinatura_alerta_user_ids`
- `assinatura_alerta_grupo_ids`
- `assinatura_exibir_ip_completo`
- `assinatura_exibir_cpf_completo`

**Arquivos-base**

- `database/migrations/2022_04_10_061731_create_cliente_configs_table.php`
- `database/migrations/2026_02_28_000002_add_limite_assinaturas_mensal_to_cliente_configs_table.php`
- `database/migrations/2026_02_28_000005_add_assinatura_digital_habilitada_to_cliente_configs_table.php`
- `app/Models/ClienteConfig.php`

## Relacionamentos inferidos/confirmados

### Confirmados no código

- `User` -> `Cliente` por `id` ou `empresa_id`, dependendo do papel
- `Curriculo` -> `FeedbackCurriculo`
- `FeedbackCurriculo` -> `ResultadoIntegrado`, `Admissao`, `Treinamento`, `Demissao`, `ExameFuncionario`, `MedidasAdministrativas`, `CartaOferta`
- `Admissao` -> `FeedbackCurriculo`
- `DocumentoParaAssinatura` -> morph `documentable` + `Arquivo` + `User` solicitante + muitos `DocumentoAssinaturaSignatario`
- `RequisicaoVagaMovimentacao` -> `Cliente`, `CentroCusto`, `Vaga`, `AreaEtiqueta`, `User`
- `PontoEletronico` -> `User` funcionário + muitos `PeriodoPontoEletronico`

## Convenções de modelagem observadas

### Confirmado no código

- Grande parte das models define explicitamente `$table`, `$fillable` e `$casts`
- Há uso frequente de accessors/mutators para datas em formato brasileiro
- Tabelas de vários módulos históricos usam nomes no plural
- Várias entidades estratégicas usam soft delete

### Desvios relevantes confirmados

- Existem duas classes `ScopeEmpresa`, uma em `app/Scopes` e outra em `app/Tenant/Scopes`
- Nem toda model do projeto segue o padrão indicado no `AGENTS.md`
- Parte do legado usa relacionamentos e tabelas pivot com nomenclaturas heterogêneas

**Arquivos-base**

- `app/Scopes/ScopeEmpresa.php`
- `app/Tenant/Scopes/ScopeEmpresa.php`
- `app/Models/*`

## Riscos de consistência de dados

### Confirmados no código

- `users.empresa_id` referencia `users.id`, enquanto várias rotas de negócio usam `clientes.id`; isso exige atenção porque a identidade da empresa é compartilhada entre dois modelos.
- Múltiplos fluxos dependem de `withoutGlobalScopes()`, o que aumenta o risco de vazamento cross-tenant caso filtros adicionais falhem.
- Parte dos relacionamentos documentais não tem FK forte por incompatibilidade histórica de tipos, especialmente no agregado de assinatura (`arquivo_id`).
- O agregado `FeedbackCurriculo` concentra grande volume de relações opcionais; isso amplia a chance de inconsistência entre etapas quando um fluxo falha no meio.

### Achados de bug/fragilidade confirmados

- `Admissao::setDataAsoAttribute()` zera `data_aso` mesmo quando recebe valor válido.
- `DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa()` chama `Cache::forget()` antes de ler, anulando o benefício do cache.
- `DocumentosPreAdmissaoController::autenticar()` usa `$candidato` antes da checagem de null.

**Arquivos-base**

- `app/Models/Admissao.php`
- `app/Models/DocumentosCurriculosAdmissaoEmpresa.php`
- `app/Http/Controllers/DocumentosPreAdmissaoController.php`
