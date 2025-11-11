# Documentação do Banco de Dados - MyBP

## Visão Geral

Este documento descreve a estrutura completa do banco de dados `mybp`, incluindo todas as tabelas, relacionamentos, índices e constraints.

**Banco de Dados:** `mybp`  
**Collation:** `utf8mb4_unicode_ci`

---

## Índice de Tabelas

### Tabelas Principais

1. [users](#users) - Usuários do sistema
2. [clientes](#clientes) - Clientes/Empresas
3. [curriculos](#curriculos) - Currículos de candidatos
4. [feedback_curriculos](#feedback_curriculos) - Feedback de recrutamento
5. [admissoes](#admissoes) - Admissões de funcionários
6. [vagas](#vagas) - Vagas de emprego
7. [vagas_abertas](#vagas_abertas) - Vagas abertas para candidatura

### Tabelas de Movimentação

8. [mudanca_cargo](#mudanca_cargo) - Mudanças de cargo
9. [transferencia_previstas](#transferencia_previstas) - Transferências previstas
10. [intermitente_fixo_previstas](#intermitente_fixo_previstas) - Intermitente para fixo
11. [admissoes_previstas](#admissoes_previstas) - Admissões previstas
12. [demissao_previstas](#demissao_previstas) - Demissões previstas
13. [ferias_previstas](#ferias_previstas) - Férias previstas

### Tabelas de Histórico e Documentação

14. [log_historico](#log_historico) - Log de histórico
15. [medida_administrativas](#medida_administrativas) - Medidas administrativas
16. [afastamentos](#afastamentos) - Afastamentos
17. [ferias](#ferias) - Férias
18. [dossie](#dossie) - Dossiê de documentos

### Tabelas de Avaliação

19. [avaliacao_noventa_feedbacks](#avaliacao_noventa_feedbacks) - Avaliações de 90 dias
20. [avaliacao_anual_feedbacks](#avaliacao_anual_feedbacks) - Avaliações anuais
21. [avaliacoes_feedbacks](#avaliacoes_feedbacks) - Feedbacks de avaliações

### Tabelas de Entrevistas

22. [parecer_rh](#parecer_rh) - Pareceres de RH
23. [parecer_entrevistas](#parecer_entrevistas) - Pareceres de entrevistas
24. [parecer_rotas](#parecer_rotas) - Pareceres de rotas
25. [parecer_entrevista_tecnica](#parecer_entrevista_tecnica) - Pareceres técnicos

### Tabelas de Treinamento

26. [treinamentos](#treinamentos) - Treinamentos
27. [treinamento_sgi](#treinamento_sgi) - Treinamentos SGI
28. [treinamento_eventos](#treinamento_eventos) - Eventos de treinamento
29. [vencimentos](#vencimentos) - Vencimentos de treinamentos

### Tabelas de Exames

30. [examesesmts](#examesesmts) - Exames SESMT
31. [exame_funcionarios](#exame_funcionarios) - Exames de funcionários
32. [empresa_exames](#empresa_exames) - Exames da empresa

### Tabelas de Centro de Custo

33. [centro_custos](#centro_custos) - Centros de custo
34. [centro_custo_filials](#centro_custo_filials) - Filiais de centro de custo
35. [area_etiquetas](#area_etiquetas) - Etiquetas de área

### Tabelas de Arquivos

36. [arquivos](#arquivos) - Arquivos do sistema
37. [documentos_curriculos](#documentos_curriculos) - Documentos de currículos

### Tabelas de Configuração

38. [habilidades](#habilidades) - Habilidades do sistema
39. [papeis](#papeis) - Papéis de usuário
40. [escolaridades](#escolaridades) - Escolaridades
41. [municipios](#municipios) - Municípios
42. [areas](#areas) - Áreas

### Tabelas de Auditoria

43. [auditoria_internas](#auditoria_internas) - Auditorias internas
44. [activity_log](#activity_log) - Log de atividades

### Tabelas de Sistema

45. [migrations](#migrations) - Migrações do Laravel
46. [failed_jobs](#failed_jobs) - Jobs falhados
47. [jobs](#jobs) - Fila de jobs
48. [personal_access_tokens](#personal_access_tokens) - Tokens de acesso pessoal
49. [password_resets](#password_resets) - Reset de senhas

---

## Detalhamento das Tabelas

### users

**Descrição:** Tabela principal de usuários do sistema (empresas, clientes, funcionários, etc.)

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| nome | varchar(255) | Nome do usuário |
| logradouro | varchar(255) | Endereço |
| complemento | varchar(255) | Complemento do endereço |
| bairro | varchar(255) | Bairro |
| municipio | varchar(255) | Município |
| uf | varchar(255) | Estado |
| cep | varchar(191) | CEP |
| login | varchar(191) | Login/Email |
| password | varchar(191) | Senha (hash) |
| tipo | varchar(191) | Tipo de usuário |
| grupo_id | int unsigned | ID do grupo |
| grupo_cloud_id | int unsigned | ID do grupo cloud |
| cadastrou | varchar(191) | Quem cadastrou |
| ativo | tinyint(1) | Status ativo/inativo |
| temp | tinyint(1) | Usuário temporário |
| ultimo_acesso | datetime | Último acesso |
| remember_token | varchar(100) | Token de lembrar |
| termos | tinyint(1) | Aceitou termos |
| device_token | varchar(191) | Token do dispositivo |
| api_token | char(36) | Token da API |
| empresa_id | bigint unsigned | ID da empresa (self-reference) |
| gestor | tinyint(1) | É gestor |
| deleted_at | timestamp | Soft delete |
| privilegio_gestor_area | tinyint(1) | Privilégio gestor de área |
| privilegio_gestor_centro_custo | tinyint(1) | Privilégio gestor de centro de custo |
| require_password_reset | tinyint(1) | Requer reset de senha |
| password_reset_days | int | Dias para reset de senha |
| password_changed_at | timestamp | Data da última alteração de senha |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `empresa_id` → `users.id` (self-reference)
- `grupo_cloud_id` → `grupo_clouds.id`

**Índices:**
- `idx_empresa_ativo_email` (empresa_id, ativo)
- `idx_users_empresa_tipo_ativo_grupo` (empresa_id, tipo, ativo, grupo_id)

---

### clientes

**Descrição:** Clientes/Empresas do sistema

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária (FK para users) |
| tipo_cliente | enum | 'Prospect' ou 'Cliente' |
| cnpj | varchar(191) | CNPJ |
| cpf | varchar(191) | CPF |
| nome | varchar(191) | Nome |
| apelido | varchar(191) | Apelido |
| tipo | varchar(191) | Tipo |
| razao_social | varchar(191) | Razão social |
| nome_fantasia | varchar(191) | Nome fantasia |
| area_id | int unsigned | ID da área |
| ramo | varchar(191) | Ramo de atividade |
| cep | varchar(191) | CEP |
| logradouro | varchar(255) | Logradouro |
| numero | varchar(191) | Número |
| complemento | varchar(255) | Complemento |
| bairro | varchar(255) | Bairro |
| municipio | varchar(255) | Município |
| uf | varchar(255) | Estado |
| contato | varchar(191) | Contato |
| email | varchar(191) | Email |
| tel_principal | varchar(191) | Telefone principal |
| aniversario | date | Aniversário |
| como_conheceu | varchar(191) | Como conheceu |
| como_conheceu_outro | varchar(191) | Outro |
| politica_ehs | text | Política EHS |
| ativo | tinyint(1) | Status ativo |
| missao | longtext | Missão |
| visao | longtext | Visão |
| valores | longtext | Valores |
| politica_gq | longtext | Política GQ |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `id` → `users.id` (herda de users)
- `area_id` → `areas.id`

---

### curriculos

**Descrição:** Currículos de candidatos

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária (FK para users) |
| cpf | varchar(191) | CPF |
| rg | varchar(191) | RG |
| rg_data_emissao | date | Data de emissão do RG |
| naturalidade | varchar(191) | Naturalidade |
| nacionalidade | varchar(250) | Nacionalidade |
| orgao_expeditor | varchar(191) | Órgão expeditor |
| carteira_trabalho | varchar(191) | Carteira de trabalho |
| nome | varchar(191) | Nome |
| estado_civil | varchar(191) | Estado civil |
| cnh | varchar(191) | CNH |
| cnh_vencimento | date | Vencimento da CNH |
| nascimento | date | Data de nascimento |
| logradouro | varchar(255) | Logradouro |
| end_numero | varchar(191) | Número |
| complemento | varchar(255) | Complemento |
| bairro | varchar(255) | Bairro |
| municipio | varchar(255) | Município |
| uf | varchar(2) | Estado |
| cep | varchar(191) | CEP |
| email | varchar(191) | Email |
| formacao | int unsigned | ID da escolaridade |
| formacao_instituicao | varchar(191) | Instituição |
| formacao_curso | varchar(191) | Curso |
| formacao_status | varchar(191) | Status da formação |
| vaga_pretendida | bigint unsigned | ID da vaga pretendida |
| uf_vaga | varchar(191) | UF da vaga |
| municipio_id | int unsigned | ID do município |
| pcd | tinyint(1) | Pessoa com deficiência |
| cid | varchar(191) | CID |
| viajar | tinyint(1) | Disponibilidade para viajar |
| lido | tinyint(1) | Lido |
| usuario_lido | bigint unsigned | Usuário que leu |
| datalido | datetime | Data de leitura |
| filiacao_pai | varchar(191) | Filiação pai |
| filiacao_mae | varchar(191) | Filiação mãe |
| disponibilidade_sabado | tinyint(1) | Disponibilidade sábado |
| disponibilidade_domingo | tinyint(1) | Disponibilidade domingo |
| sexo | varchar(191) | Sexo |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `id` → `users.id` (herda de users)
- `formacao` → `escolaridades.id`
- `municipio_id` → `municipios.id`
- `usuario_lido` → `users.id`
- `vaga_pretendida` → `vagas.id`

**Índices:**
- `idx_curriculo_id_nome_cpf` (id, nome, cpf)
- `curriculos_vaga_pretendida_foreign` (vaga_pretendida)

---

### feedback_curriculos

**Descrição:** Feedback de recrutamento para currículos

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| curriculo_id | bigint unsigned | ID do currículo |
| selecionado | enum | 'sim', 'nao', 'standby' |
| vaga_id | int unsigned | ID da vaga |
| usuario_entrevista_marcado | bigint unsigned | Usuário que marcou entrevista |
| cliente_id | bigint unsigned | ID do cliente |
| contato_realizado | tinyint(1) | Contato realizado |
| interesse | tinyint(1) | Interesse |
| data_entrevista | datetime | Data da entrevista |
| local_entrevista | varchar(255) | Local da entrevista |
| telefone_id | int unsigned | ID do telefone |
| obs | text | Observações |
| status | varchar(191) | Status |
| envia_mail_provas | tinyint(1) | Enviar email provas |
| data_envia_mail_provas | datetime | Data envio email provas |
| user_envia_mail_provas | bigint unsigned | Usuário que enviou email provas |
| envia_mail_proxima_etapa | tinyint(1) | Enviar email próxima etapa |
| data_envia_mail_proxima_etapa | datetime | Data envio email próxima etapa |
| user_envia_mail_proxima_etapa | bigint unsigned | Usuário que enviou email próxima etapa |
| envia_mail_desclassificacao | tinyint(1) | Enviar email desclassificação |
| data_envia_mail_desclassificacao | datetime | Data envio email desclassificação |
| user_envia_mail_desclassificacao | bigint unsigned | Usuário que enviou email desclassificação |
| envia_whatsapp | tinyint(1) | Enviar WhatsApp |
| data_envia_whatsapp | datetime | Data envio WhatsApp |
| user_envia_whatsapp | bigint unsigned | Usuário que enviou WhatsApp |
| empresa_id | bigint unsigned | ID da empresa |
| vagas_abertas_id | bigint unsigned | ID da vaga aberta |
| vaga_projeto_id | bigint unsigned | ID do projeto de vaga |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `curriculo_id` → `curriculos.id`
- `vaga_id` → `vagas.id`
- `cliente_id` → `clientes.id`
- `empresa_id` → `users.id`
- `vagas_abertas_id` → `vagas_abertas.id`
- `vaga_projeto_id` → `vaga_projetos.id`
- `telefone_id` → `curriculo_telefone.id`
- `usuario_entrevista_marcado` → `users.id`
- `user_envia_mail_provas` → `users.id`
| `user_envia_mail_proxima_etapa` → `users.id`
| `user_envia_mail_desclassificacao` → `users.id`
| `user_envia_whatsapp` → `users.id`

**Índices:**
- `idx_feedback_id_optimized` (feedback_id)
- `idx_empresa_id_optimized` (empresa_id)

---

### admissoes

**Descrição:** Admissões de funcionários

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | int unsigned | Chave primária |
| centro_custo_id | bigint unsigned | ID do centro de custo |
| matricula | varchar(191) | Matrícula |
| usa_lentes_corretivas | tinyint(1) | Usa lentes corretivas |
| feedback_id | bigint unsigned | ID do feedback |
| filial | tinyint(1) | É filial |
| centro_custo_filial_id | bigint unsigned | ID do centro de custo filial |
| contrato | varchar(191) | Tipo de contrato |
| funcao | varchar(191) | Função |
| cargo | varchar(191) | Cargo |
| salario | decimal(11,2) | Salário |
| status | varchar(191) | Status |
| documento | varchar(200) | Documento |
| documento_portaria | varchar(200) | Documento portaria |
| tipo_admissao | varchar(200) | Tipo de admissão |
| data_encerramento | date | Data de encerramento |
| prazo_experiencia | varchar(191) | Prazo de experiência |
| tipo_treinamento | varchar(200) | Tipo de treinamento |
| treinamento | varchar(200) | Treinamento |
| data_treinamento | date | Data do treinamento |
| carteira_treinamento | varchar(200) | Carteira de treinamento |
| nr_trinta_tres | varchar(200) | NR 33 |
| data_nr_trinta_tres | date | Data NR 33 |
| nr_trinta_cinco | varchar(200) | NR 35 |
| data_nr_trinta_cinco | date | Data NR 35 |
| trinta_dois_sessenta | varchar(200) | 32/60 |
| data_trinta_dois_sessenta | date | Data 32/60 |
| numero_cracha | varchar(191) | Número do crachá |
| data_aso | date | Data do ASO |
| foto_escaneada | tinyint(1) | Foto escaneada |
| status_carteira_treinamento | varchar(191) | Status carteira treinamento |
| usuario_id | bigint unsigned | ID do usuário |
| editado_usuario_id | bigint unsigned | ID do usuário que editou |
| data_admissao | date | Data de admissão |
| data_adm_prevista | date | Data de admissão prevista |
| data_desmobilizacao | date | Data de desmobilização |
| avaliacao | varchar(191) | Avaliação |
| obs_avaliacao | text | Observações da avaliação |
| user_avaliacao | bigint unsigned | ID do usuário que avaliou |
| responsavel_feedback | varchar(191) | Responsável pelo feedback |
| data_avaliacao | datetime | Data da avaliação |
| area_etiqueta_id | int unsigned | ID da etiqueta de área |
| deu_baixa_epi | tinyint(1) | Deu baixa EPI |
| cipa | tinyint(1) | CIPA |
| alternativas | longtext | Alternativas |
| data_desmob | datetime | Data de desmobilização |
| usuario_desmob | bigint unsigned | ID do usuário que desmobilizou |
| pendencia | tinyint(1) | Pendência |
| pendencias_quais | text | Quais pendências |
| outros | text | Outros |
| preenchido_por_rh | varchar(191) | Preenchido por RH |
| preenchido_por_adm | varchar(191) | Preenchido por ADM |
| preenchido_por_ssma | varchar(191) | Preenchido por SSMA |
| data_entrega_area | date | Data de entrega na área |
| biometria | tinyint(1) | Biometria |
| data_biometria | date | Data da biometria |
| formulario_id | int unsigned | ID do formulário |
| pis | varchar(255) | PIS |
| deleted_at | timestamp | Soft delete |
| acessar_area_porto | varchar(250) | Acessar área porto |
| avaliacao_psicologica | varchar(250) | Avaliação psicológica |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `feedback_id` → `feedback_curriculos.id`
- `centro_custo_id` → `centro_custos.id`
- `centro_custo_filial_id` → `centro_custo_filials.id`
- `area_etiqueta_id` → `area_etiquetas.id`
- `usuario_id` → `users.id`
- `editado_usuario_id` → `users.id`
- `user_avaliacao` → `users.id`
- `usuario_desmob` → `users.id`
- `formulario_id` → `formularios.id`

**Índices:**
- `idx_centro_custo_filial` (centro_custo_filial_id)
- `idx_status_feedback` (status, feedback_id)

---

### mudanca_cargo

**Descrição:** Mudanças de cargo de funcionários

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| empresa_id | bigint unsigned | ID da empresa |
| admissao_id | int unsigned | ID da admissão |
| colaborador_id | bigint unsigned | ID do colaborador |
| mantem_centro_custo | tinyint(1) | Mantém centro de custo |
| anterior_centro_custo_id | bigint unsigned | ID do centro de custo anterior |
| anterior_filial | tinyint(1) | Filial anterior |
| anterior_centro_custo_filial_id | bigint unsigned | ID do centro de custo filial anterior |
| novo_centro_custo_id | bigint unsigned | ID do novo centro de custo |
| novo_filial | tinyint(1) | Nova filial |
| novo_centro_custo_filial_id | bigint unsigned | ID do novo centro de custo filial |
| mantem_cargo | tinyint(1) | Mantém cargo |
| anterior_vaga_aberta_id | bigint unsigned | ID da vaga aberta anterior |
| nova_vaga_aberta_id | bigint unsigned | ID da nova vaga aberta |
| mantem_funcao | tinyint(1) | Mantém função |
| anterior_funcao | varchar(250) | Função anterior |
| nova_funcao | varchar(250) | Nova função |
| treinamento_funcao | tinyint(1) | Treinamento na função |
| treinamento_data_inicio | date | Data início treinamento |
| treinamento_data_fim | date | Data fim treinamento |
| mantem_salario | tinyint(1) | Mantém salário |
| anterior_salario | decimal(11,2) | Salário anterior |
| novo_salario | decimal(11,2) | Novo salário |
| solicitante_id | bigint unsigned | ID do solicitante |
| obs_solicitante | text | Observações do solicitante |
| data_solicitacao | datetime | Data da solicitação |
| gestor_id | bigint unsigned | ID do gestor |
| gestor_aprovacao_id | bigint unsigned | ID do gestor que aprovou |
| obs_gestor_aprovacao | text | Observações do gestor |
| status_aprovacao_gestor | varchar(250) | Status aprovação gestor |
| data_aprovacao_gestor | datetime | Data aprovação gestor |
| rh_aprovacao_id | bigint unsigned | ID do RH que aprovou |
| obs_rh | text | Observações do RH |
| status_aprovacao_rh | varchar(250) | Status aprovação RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| aprovado_via_script | tinyint(1) | Aprovado via script |
| quem_deletou_id | bigint unsigned | ID de quem deletou |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `empresa_id` → `clientes.id`
- `admissao_id` → `admissoes.id`
- `colaborador_id` → `users.id`
- `anterior_centro_custo_id` → `centro_custos.id`
- `novo_centro_custo_id` → `centro_custos.id`
- `anterior_centro_custo_filial_id` → `centro_custo_filials.id`
- `novo_centro_custo_filial_id` → `centro_custo_filials.id`
- `anterior_vaga_aberta_id` → `vagas_abertas.id`
- `nova_vaga_aberta_id` → `vagas_abertas.id`
- `solicitante_id` → `users.id`
- `gestor_id` → `users.id`
- `gestor_aprovacao_id` → `users.id`
- `rh_aprovacao_id` → `users.id`
- `quem_deletou_id` → `users.id`

---

### mudanca_cargo_anexos

**Descrição:** Anexos de mudança de cargo

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| mudanca_cargo_id | bigint unsigned | ID da mudança de cargo |
| arquivo_id | int unsigned | ID do arquivo |
| tipo_anexo | varchar(250) | Tipo do anexo (default: 'anexo_default') |

**Relacionamentos:**
- `mudanca_cargo_id` → `mudanca_cargo.id`
- `arquivo_id` → `arquivos.id`

---

### medida_administrativas

**Descrição:** Medidas administrativas aplicadas a funcionários

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | int unsigned | Chave primária |
| feedback_id | bigint unsigned | ID do feedback |
| user_id | bigint unsigned | ID do usuário que cadastrou |
| solicitante | varchar(191) | Solicitante |
| tipo | varchar(191) | Tipo da medida |
| definicao | varchar(191) | Definição |
| motivo | text | Motivo |
| causa | varchar(191) | Causa |
| data_solicitacao | date | Data da solicitação |
| data_retorno | date | Data de retorno |
| quem_deletou_id | bigint unsigned | ID de quem deletou |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `feedback_id` → `feedback_curriculos.id`
- `user_id` → `users.id`
- `quem_deletou_id` → `users.id`

---

### log_historico

**Descrição:** Log de histórico de ações no sistema

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| feedback_id | bigint unsigned | ID do feedback |
| empresa_id | bigint unsigned | ID da empresa |
| acao | varchar(250) | Descrição da ação |
| user_id | bigint unsigned | ID do usuário |
| data | datetime | Data da ação |

**Relacionamentos:**
- `feedback_id` → `feedback_curriculos.id`
- `empresa_id` → `users.id`
- `user_id` → `users.id`

---

### centro_custos

**Descrição:** Centros de custo

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| gestor_id | bigint unsigned | ID do gestor |
| cliente_id | bigint unsigned | ID do cliente |
| label | varchar(191) | Nome do centro de custo |
| ativo | tinyint(1) | Status ativo |
| empresa_id | bigint unsigned | ID da empresa |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `gestor_id` → `users.id`
- `cliente_id` → `clientes.id`
- `empresa_id` → `users.id`

**Índices:**
- `idx_centro_custo_id_label` (id, label)

---

### arquivos

**Descrição:** Arquivos do sistema

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | int unsigned | Chave primária |
| quem_enviou | bigint unsigned | ID do usuário que enviou |
| nome | text | Nome do arquivo |
| imagem | tinyint(1) | É imagem |
| layout | varchar(25) | Layout (portrait/landscape) |
| extensao | varchar(5) | Extensão do arquivo |
| file | text | Caminho do arquivo |
| thumb | varchar(100) | Caminho da thumbnail |
| bytes | bigint | Tamanho em bytes |
| temporario | tinyint(1) | Arquivo temporário |
| chave | varchar(90) | Chave temporária |
| disco | varchar(191) | Disco de armazenamento |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `quem_enviou` → `users.id`

---

### vagas

**Descrição:** Vagas de emprego

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | int unsigned | Chave primária |
| categoria_id | int unsigned | ID da categoria |
| nome | varchar(191) | Nome da vaga |
| ativo | tinyint(1) | Status ativo |
| empresa_id | bigint unsigned | ID da empresa |

**Relacionamentos:**
- `categoria_id` → `categoria_vagas.id`
- `empresa_id` → `users.id`

---

### vagas_abertas

**Descrição:** Vagas abertas para candidatura

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| vaga_id | int unsigned | ID da vaga |
| titulo | varchar(191) | Título |
| descricao | text | Descrição |
| municipio_id | int unsigned | ID do município |
| ativo | tinyint(1) | Status ativo |
| ativo_sistema | tinyint(1) | Ativo no sistema |
| empresa_id | bigint unsigned | ID da empresa |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `vaga_id` → `vagas.id`
- `municipio_id` → `municipios.id`
- `empresa_id` → `users.id`

---

### transferencia_previstas

**Descrição:** Transferências previstas de funcionários

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| colaborador_id | bigint unsigned | ID do colaborador |
| centro_custo_origem_id | bigint unsigned | ID do centro de custo origem |
| centro_custo_destino_id | bigint unsigned | ID do centro de custo destino |
| data_transferencia | date | Data da transferência |
| user_id | bigint unsigned | ID do usuário |
| solicitante | varchar(191) | Solicitante |
| obs | text | Observações |
| user_aprovacao_id | bigint unsigned | ID do usuário que aprovou |
| data_aprovacao | datetime | Data de aprovação |
| obs_aprovacao | text | Observações da aprovação |
| status_aprovacao | varchar(191) | Status da aprovação |
| empresa_id | bigint unsigned | ID da empresa |
| gestor_id | bigint unsigned | ID do gestor |
| rh_aprovacao_id | bigint unsigned | ID do RH que aprovou |
| status_aprovacao_rh | varchar(250) | Status aprovação RH |
| obs_rh | text | Observações do RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `colaborador_id` → `curriculos.id`
- `centro_custo_origem_id` → `centro_custos.id`
- `centro_custo_destino_id` → `centro_custos.id`
- `user_id` → `users.id`
- `user_aprovacao_id` → `users.id`
- `empresa_id` → `users.id`
- `gestor_id` → `users.id`
- `rh_aprovacao_id` → `users.id`

---

### intermitente_fixo_previstas

**Descrição:** Conversões de intermitente para fixo

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| cliente_id | bigint unsigned | ID do cliente |
| colaborador_id | bigint unsigned | ID do colaborador |
| centro_custo_id | bigint unsigned | ID do centro de custo |
| cargo_anterior_id | int unsigned | ID do cargo anterior |
| salario_anterior | decimal(11,2) | Salário anterior |
| novo_cargo_id | int unsigned | ID do novo cargo |
| novo_salario | decimal(11,2) | Novo salário |
| user_id | bigint unsigned | ID do usuário |
| data_modificacao | date | Data da modificação |
| autorizado_por | varchar(191) | Autorizado por |
| motivos | text | Motivos |
| user_aprovacao_id | bigint unsigned | ID do usuário que aprovou |
| data_aprovacao | datetime | Data de aprovação |
| obs_aprovacao | text | Observações da aprovação |
| status_aprovacao | varchar(191) | Status da aprovação |
| empresa_id | bigint unsigned | ID da empresa |
| gestor_id | bigint unsigned | ID do gestor |
| filial | tinyint(1) | É filial |
| centro_custo_filial_id | bigint unsigned | ID do centro de custo filial |
| anterior_vaga_aberta_id | bigint unsigned | ID da vaga aberta anterior |
| nova_vaga_aberta_id | bigint unsigned | ID da nova vaga aberta |
| area_etiqueta_id | int unsigned | ID da etiqueta de área |
| rh_aprovacao_id | bigint unsigned | ID do RH que aprovou |
| obs_rh | text | Observações do RH |
| status_aprovacao_rh | varchar(250) | Status aprovação RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| aprovado_via_script | tinyint(1) | Aprovado via script |
| quem_deletou_id | bigint unsigned | ID de quem deletou |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `cliente_id` → `clientes.id`
- `colaborador_id` → `users.id`
- `centro_custo_id` → `centro_custos.id`
- `cargo_anterior_id` → `vagas.id`
- `novo_cargo_id` → `vagas.id`
- `anterior_vaga_aberta_id` → `vagas_abertas.id`
- `nova_vaga_aberta_id` → `vagas_abertas.id`
- `area_etiqueta_id` → `area_etiquetas.id`
- `centro_custo_filial_id` → `centro_custo_filials.id`
- `user_id` → `users.id`
- `user_aprovacao_id` → `users.id`
- `empresa_id` → `users.id`
- `gestor_id` → `users.id`
- `rh_aprovacao_id` → `users.id`
- `quem_deletou_id` → `users.id`

---

### admissoes_previstas

**Descrição:** Admissões previstas

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| cliente_id | bigint unsigned | ID do cliente |
| colaborador_id | bigint unsigned | ID do colaborador |
| centro_custo_id | bigint unsigned | ID do centro de custo |
| tipo_contrato | varchar(191) | Tipo de contrato |
| cargo_id | int unsigned | ID do cargo |
| data_admissao | date | Data de admissão |
| salario | decimal(11,2) | Salário |
| user_id | bigint unsigned | ID do usuário |
| solicitante | varchar(191) | Solicitante |
| obs | text | Observações |
| user_aprovacao_id | bigint unsigned | ID do usuário que aprovou |
| data_aprovacao | datetime | Data de aprovação |
| obs_aprovacao | text | Observações da aprovação |
| status_aprovacao | varchar(191) | Status da aprovação |
| empresa_id | bigint unsigned | ID da empresa |
| gestor_id | bigint unsigned | ID do gestor |
| filial | tinyint(1) | É filial |
| centro_custo_filial_id | bigint unsigned | ID do centro de custo filial |
| rh_aprovacao_id | bigint unsigned | ID do RH que aprovou |
| obs_rh | text | Observações do RH |
| status_aprovacao_rh | varchar(250) | Status aprovação RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| aprovado_via_script | tinyint(1) | Aprovado via script |
| quem_deletou_id | bigint unsigned | ID de quem deletou |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `cliente_id` → `clientes.id`
- `colaborador_id` → `users.id`
- `centro_custo_id` → `centro_custos.id`
- `cargo_id` → `vagas.id`
- `centro_custo_filial_id` → `centro_custo_filials.id`
- `user_id` → `users.id`
- `user_aprovacao_id` → `users.id`
- `empresa_id` → `users.id`
- `gestor_id` → `users.id`
- `rh_aprovacao_id` → `users.id`
- `quem_deletou_id` → `users.id`

---

### demissao_previstas

**Descrição:** Demissões previstas

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| cliente_id | bigint unsigned | ID do cliente |
| colaborador_id | bigint unsigned | ID do colaborador |
| centro_custo_id | bigint unsigned | ID do centro de custo |
| aviso | varchar(255) | Tipo de aviso |
| data_demissao | date | Data da demissão |
| tipo_aviso | varchar(191) | Tipo de aviso |
| valor | decimal(11,2) | Valor |
| user_id | bigint unsigned | ID do usuário |
| solicitante | varchar(191) | Solicitante |
| status | varchar(191) | Status |
| obs | text | Observações |
| user_aprovacao_id | bigint unsigned | ID do usuário que aprovou |
| data_aprovacao | datetime | Data de aprovação |
| obs_aprovacao | text | Observações da aprovação |
| status_aprovacao | varchar(191) | Status da aprovação |
| empresa_id | bigint unsigned | ID da empresa |
| gestor_id | bigint unsigned | ID do gestor |
| filial | tinyint(1) | É filial |
| centro_custo_filial_id | bigint unsigned | ID do centro de custo filial |
| rh_aprovacao_id | bigint unsigned | ID do RH que aprovou |
| obs_rh | text | Observações do RH |
| status_aprovacao_rh | varchar(250) | Status aprovação RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| aprovado_via_script | tinyint(1) | Aprovado via script |
| quem_deletou_id | bigint unsigned | ID de quem deletou |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `cliente_id` → `clientes.id`
- `colaborador_id` → `users.id`
- `centro_custo_id` → `centro_custos.id`
- `centro_custo_filial_id` → `centro_custo_filials.id`
- `user_id` → `users.id`
- `user_aprovacao_id` → `users.id`
- `empresa_id` → `users.id`
- `gestor_id` → `users.id`
- `rh_aprovacao_id` → `users.id`
- `quem_deletou_id` → `users.id`

---

### ferias_previstas

**Descrição:** Férias previstas

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| cliente_id | bigint unsigned | ID do cliente |
| colaborador_id | bigint unsigned | ID do colaborador |
| centro_custo_id | bigint unsigned | ID do centro de custo |
| data_saida | date | Data de saída |
| qnt_dias | int | Quantidade de dias |
| data_retorno | date | Data de retorno |
| dias_saldo | int | Dias de saldo |
| user_id | bigint unsigned | ID do usuário |
| solicitante | varchar(191) | Solicitante |
| status | varchar(191) | Status |
| obs | text | Observações |
| user_aprovacao_id | bigint unsigned | ID do usuário que aprovou |
| data_aprovacao | datetime | Data de aprovação |
| obs_aprovacao | text | Observações da aprovação |
| status_aprovacao | varchar(191) | Status da aprovação |
| tem_faltas | tinyint(1) | Tem faltas |
| qnt_faltas | int | Quantidade de faltas |
| user_rh_id | bigint unsigned | ID do RH |
| resposta_rh | varchar(191) | Resposta do RH |
| obs_rh | text | Observações do RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| empresa_id | bigint unsigned | ID da empresa |
| gestor_id | bigint unsigned | ID do gestor |
| periodo_aquisitivo | varchar(191) | Período aquisitivo |
| ultima_data | date | Última data |
| mes | varchar(191) | Mês |
| periodo_aquisitivo_id | bigint unsigned | ID do período aquisitivo |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `cliente_id` → `clientes.id`
- `colaborador_id` → `users.id`
- `centro_custo_id` → `centro_custos.id`
- `periodo_aquisitivo_id` → `periodos_aquisitivos.id`
- `user_id` → `users.id`
- `user_aprovacao_id` → `users.id`
- `user_rh_id` → `users.id`
- `empresa_id` → `users.id`
- `gestor_id` → `users.id`

---

### ferias

**Descrição:** Férias registradas

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| empresa_id | bigint unsigned | ID da empresa |
| admissao_id | int unsigned | ID da admissão |
| periodo_aquisitivo_id | bigint unsigned | ID do período aquisitivo |
| data_saida | date | Data de saída |
| data_retorno | date | Data de retorno |
| ultima_data | date | Última data |
| qnt_dias | int unsigned | Quantidade de dias |
| dias_saldo | int unsigned | Dias de saldo |
| tem_faltas | tinyint(1) | Tem faltas |
| qnt_faltas | int unsigned | Quantidade de faltas |
| solicitante_id | bigint unsigned | ID do solicitante |
| obs_solicitante | text | Observações do solicitante |
| data_solicitacao | datetime | Data da solicitação |
| gestor_id | bigint unsigned | ID do gestor |
| gestor_aprovacao_id | bigint unsigned | ID do gestor que aprovou |
| obs_gestor | text | Observações do gestor |
| status_aprovacao_gestor | varchar(250) | Status aprovação gestor |
| data_aprovacao_gestor | datetime | Data aprovação gestor |
| rh_aprovacao_id | bigint unsigned | ID do RH que aprovou |
| obs_rh | text | Observações do RH |
| status_aprovacao_rh | varchar(250) | Status aprovação RH |
| data_aprovacao_rh | datetime | Data aprovação RH |
| status_ferias | varchar(250) | Status das férias |
| data_status_ferias | datetime | Data do status |
| ferias_prevista_id | bigint unsigned | ID da férias prevista |
| aprovado_via_script | tinyint(1) | Aprovado via script |
| quem_deletou_id | bigint unsigned | ID de quem deletou |
| deleted_at | timestamp | Soft delete |
| abono_pecuniario | tinyint(1) | Abono pecuniário |
| adiantamento_decimo_terceiro | tinyint(1) | Adiantamento 13º |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `empresa_id` → `users.id`
- `admissao_id` → `admissoes.id`
- `periodo_aquisitivo_id` → `periodos_aquisitivos.id`
- `solicitante_id` → `users.id`
- `gestor_id` → `users.id`
- `gestor_aprovacao_id` → `users.id`
- `rh_aprovacao_id` → `users.id`
- `ferias_prevista_id` → `ferias_previstas.id`
- `quem_deletou_id` → `users.id`

---

### auditoria_internas

**Descrição:** Auditorias internas do sistema

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint unsigned | Chave primária |
| empresa_id | bigint unsigned | ID da empresa |
| usuario_id | bigint unsigned | ID do usuário |
| feedback_id | bigint unsigned | ID do feedback |
| colaborador_id | bigint unsigned | ID do colaborador |
| tipo | varchar(255) | Tipo da auditoria |
| descricao | varchar(255) | Descrição |
| dados | json | Dados adicionais (JSON) |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos:**
- `empresa_id` → `users.id`
- `usuario_id` → `users.id`
- `feedback_id` → `feedback_curriculos.id`
- `colaborador_id` → `users.id`

---

### activity_log

**Descrição:** Log de atividades do sistema (Spatie Activity Log)

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | int unsigned | Chave primária |
| log_name | varchar(191) | Nome do log |
| description | text | Descrição |
| descricao | text | Descrição (PT) |
| subject_id | int | ID do sujeito |
| subject_type | varchar(191) | Tipo do sujeito |
| causer_id | int | ID do causador |
| causer_type | varchar(191) | Tipo do causador |
| properties | text | Propriedades (JSON) |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices:**
- `activity_log_log_name_index` (log_name)

---

## Relacionamentos Principais

### Hierarquia de Usuários

```
users (empresa_id) → users (self-reference)
users (grupo_cloud_id) → grupo_clouds
```

### Fluxo de Recrutamento

```
curriculos → feedback_curriculos → admissoes
vagas → vagas_abertas → feedback_curriculos
```

### Movimentações

```
admissoes → mudanca_cargo
admissoes → transferencia_previstas
admissoes → intermitente_fixo_previstas
admissoes → ferias
admissoes → demissao_previstas
```

### Centro de Custo

```
clientes → centro_custos → centro_custo_filials
centro_custos (gestor_id) → users
```

### Histórico e Documentação

```
feedback_curriculos → log_historico
feedback_curriculos → medida_administrativas
feedback_curriculos → afastamentos
feedback_curriculos → dossie
```

---

## Convenções de Nomenclatura

### Tabelas
- Nomes no plural (ex: `users`, `curriculos`)
- Snake_case (ex: `feedback_curriculos`)
- Tabelas pivot: `tabela1_tabela2` (ex: `mudanca_cargo_anexos`)

### Colunas
- Snake_case (ex: `user_id`, `created_at`)
- Foreign keys: `tabela_id` (ex: `user_id`, `feedback_id`)
- Soft deletes: `deleted_at`
- Timestamps: `created_at`, `updated_at`

### Índices
- `idx_nome` para índices gerais
- `tabela_coluna_foreign` para foreign keys

---

## Soft Deletes

As seguintes tabelas utilizam soft delete (`deleted_at`):

- `users`
- `feedback_curriculos`
- `admissoes`
- `mudanca_cargo`
- `intermitente_fixo_previstas`
- `admissoes_previstas`
- `demissao_previstas`
- `ferias`
- `medida_administrativas`
- `cihs`
- E outras...

---

## Observações Importantes

1. **Multi-tenancy**: O sistema utiliza `empresa_id` em várias tabelas para isolamento de dados
2. **Aprovações**: Muitas tabelas possuem fluxo de aprovação (gestor → RH)
3. **Auditoria**: Sistema robusto de auditoria com `auditoria_internas` e `activity_log`
4. **Anexos**: Sistema flexível de anexos através da tabela `arquivos` e tabelas pivot
5. **Histórico**: Sistema completo de log através de `log_historico`

---

## Versão

**Última atualização:** 2025-01-27  
**Banco de dados:** mybp  
**Collation:** utf8mb4_unicode_ci

