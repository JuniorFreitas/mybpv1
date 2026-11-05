# Plano — Módulo SST / Saúde Ocupacional (MyBPIN)

> Status: **proposta (v3)** · atualizado em 04/11/2026 · branch de origem `feature/aso-customizacao`
> Referências: `docs/ASO_PERIODICIDADE_EMPRESA.md`, benchmark do manual SST "comeia" (`~/Desktop/WEB/comeia/manual`), NR-7 (Portaria SEPRT 6.734/2020), NR-1 (PGR), LGPD art. 11.

## 1. Objetivo

Desenvolver o módulo de **SST** do MyBPIN, exposto no menu **SAÚDE OCUPACIONAL**, estruturando o **PCMSO como módulo de gestão ocupacional** e integrando **PCMSO, PGR, ASO e gestão de colaboradores** em uma única plataforma.

### Escopo pedido → onde está no plano

| Item | Parte |
|---|---|
| Cadastro de PCMSO por empresa | **Parte 3** |
| Gestão de médicos responsáveis | **Parte 2** |
| Cadastro de exames por função | **Parte 2** (funções e riscos) + **Parte 3** (exames por função no PCMSO) |
| Controle de vencimentos | **Parte 1** (regras) + **Parte 4** (conformidade) |
| Histórico de ASO | **Parte 5** |
| Alertas de exames periódicos | **Parte 4** (alertas) + **Parte 9** (pendências) |
| Gestão documental | **Parte 6** |
| Relatórios de acompanhamento | **Parte 7** |
| Integração PCMSO + PGR + ASO + colaboradores | **Partes 2, 3 e 5** (fluxo na seção 4) |

### Princípios

| Princípio | O que significa na prática |
|---|---|
| **Fácil para o RH** | A primeira tela diz **o que precisa ser feito hoje**. Linguagem simples, status com cores, ações em 1 clique, alertas que chegam sozinhos. |
| **Seguro por padrão** | Dados de saúde são sensíveis (LGPD art. 11). O RH vê **status e prazos**; CID, diagnóstico e laudos só o perfil Saúde. Tudo auditado. |
| **Regras de mercado + regras da empresa** | Já vem com o **Padrão NR-7**; cada empresa customiza e cria as próprias regras, com prévia de impacto, histórico e trava legal. |

### Fora do escopo (decisão)

Agenda/horários médicos, consulta ambulatorial, receituário, farmácia e medicamentos (recursos de **ambulatório/prontuário**). Pesquisa psicossocial já existe no **BP+NR1** (integração na Parte 13).

## 2. Menu SAÚDE OCUPACIONAL

```
SAÚDE OCUPACIONAL
├── Painel                      ← tela inicial do RH ("o que fazer hoje")
├── ASO e Exames
│   ├── Conformidade / Vencimentos
│   ├── Pendências de ASO
│   ├── Controle de exames      (encaminhamento e resultado — tela atual)
│   └── Histórico de ASO
├── Programas
│   ├── PCMSO                   (por empresa, versões, exames por função)
│   └── PGR — Riscos            (inventário de riscos por função)
├── Afastamentos
├── Ficha de saúde do colaborador
├── Documentos SST              (gestão documental)
├── Relatórios                  (acompanhamento + relatório analítico NR-7)
├── Radar de alertas
├── Toxicológico e Vacinas
├── Clínicas e Faturamento
└── Configurações
    ├── Médicos responsáveis
    ├── Funções e Riscos
    ├── Tipos de exame e Exames
    ├── Regras da empresa       ← customização
    └── Notificações
```

- Menu só aparece com o módulo ligado (`ClienteConfig.modulo_saude_ocupacional`) e com permissão; itens sem permissão ficam ocultos.
- **Cadastro → Periodicidade ASO** passa para **Configurações → Regras da empresa** (rota antiga redireciona).

### Painel do RH (tela inicial)

| Card | Ação direta |
|---|---|
| ASOs **vencidos** / **vencendo** em X dias | Encaminhar em lote |
| Colaboradores **sem ASO** | Encaminhar |
| **Pendências** (retorno, mudança de função, demissional) | Abrir pendência |
| **Afastados** hoje / voltando esta semana | Preparar ASO de retorno |
| Afastamentos perto dos **15 dias (INSS)** | Ver lista |
| **Documentos SST** vencendo (PCMSO, PGR, laudos) | Abrir documento |
| PCMSO **sem médico responsável** ou **sem vigência** | Corrigir |

Mais: resumo semanal por e-mail para o RH (fila, template oficial), só números e link.

## 3. Regras de mercado (base legal)

| Regra | Fonte |
|---|---|
| Todo empregador elabora e mantém o **PCMSO**, sob responsabilidade de um **médico do trabalho** | NR-7 |
| PCMSO baseado nos riscos do **PGR** | NR-7 / NR-1 |
| Periódico: exposto a risco do PGR ou doente crônico → **anual** (ou menor, a critério médico) | NR-7 7.5.8 II a |
| Periódico: demais empregados → **a cada 2 anos** | NR-7 7.5.8 II b |
| Admissional antes do início das atividades | NR-7 |
| Retorno ao trabalho: afastamento **≥ 30 dias**, antes da volta | NR-7 |
| Mudança de risco: ASO antes da mudança | NR-7 |
| Demissional: até 10 dias após o desligamento; dispensado se último exame há ≤ 135 dias (grau 1–2) ou ≤ 90 dias (grau 3–4) | NR-7 |
| ASO com campos mínimos: empresa (razão social/CNPJ), colaborador (nome, CPF, função), riscos, exames e datas, apto/inapto, médico examinador (nome/CRM), médico responsável pelo PCMSO, data e assinatura | NR-7 |
| **Relatório analítico** anual do PCMSO (exames por tipo, resultados alterados, estatísticas) | NR-7 |
| Empregador recebe o **ASO**; resultados de exames e prontuário são **sigilo médico** | NR-7 / CFM |
| Prontuário guardado por no mínimo **20 anos** após o desligamento | NR-7 |
| Afastamento > 15 dias (ou soma do mesmo CID em 60 dias) → INSS | Lei 8.213/91 |

A regra por idade (<18 e >45 anual) **saiu da NR-7 em 2020**, mas é prática comum e o PCMSO pode adotar — entra como **modelo pronto opcional** ("Idade 40+ / 45+ anual").

> Validar prazos e campos com o texto oficial do MTE e com o médico coordenador do cliente piloto antes de liberar as Partes 1, 5 e 9.

## 4. Integração PGR → PCMSO → ASO → Colaborador

```
PGR (riscos por função)
   └─► PCMSO vigente (exames + periodicidade por função, médico responsável)
          └─► Motor de regras (Padrão NR-7 + regras da empresa)
                 └─► ASO do colaborador (exames sugeridos, vencimento calculado)
                        ├─► Histórico de ASO / Ficha de saúde
                        ├─► Conformidade, alertas e pendências
                        └─► Documentos (ASO PDF assinado) e Relatórios
Eventos do colaborador (admissão, mudança de cargo, afastamento, demissão)
   └─► Pendências automáticas de ASO
```

- A **função** do colaborador é o **cargo da admissão** (decidido em 04/11/2026), identificado pelo **`vaga_id`** do catálogo `vagas` (`nome`, `empresa_id`, `cbo_id`).
  - Origem atual: `admissoes.feedback_id` → `feedbacks_curriculos.vaga_id` → `vagas.id`.
  - `admissoes.cargo` é só o nome gravado em texto: **não** usar como chave. Para admissões antigas sem `vaga_id`, casar pelo nome dentro da empresa e listar as que não casaram para o RH corrigir.
  - Mudança de cargo (`mudanca_cargo.nova_vaga_aberta_id` → `vagas_abertas.vaga_id`) passa a ser o cargo vigente a partir da data efetiva.
  - Todas as tabelas do módulo referenciam `vaga_id`; o CBO vem junto pelo `vagas.cbo_id` (útil no ASO e no eSocial).
- Mudou o PGR ou o PCMSO → o motor recalcula os vencimentos afetados (com prévia e modo de aplicação).

## 5. Segurança (protocolo obrigatório)

### 5.1 Perfis de acesso

| Perfil | Vê | Não vê |
|---|---|---|
| **RH** (`saude_ocupacional_rh`) | Painel, status, validade, pendências, apto/inapto, datas de afastamento, documentos não sensíveis | CID, diagnóstico, laudos, resultados de exames, atestados |
| **Saúde/SESMT** (`saude_ocupacional_saude`) | Tudo do RH + dados sensíveis | — |
| **Configurador** (`saude_ocupacional_config`) | Médicos, funções/riscos, PCMSO, PGR, regras, notificações | Dados sensíveis (sem o perfil Saúde) |
| **Gestor** | Se o colaborador está apto e se há afastamento ativo (sem motivo) | Todo o resto |
| **Clínica** (portal) | Os próprios encaminhamentos e faturamento | Dados de outras clínicas/empresas |

### 5.2 Controles técnicos

- Campo sensível **não sai na API** para quem não tem permissão (resources/DTOs por perfil).
- **Criptografia** em repouso (cast `encrypted`) para CID, observação médica e resultado detalhado.
- **Anexos privados** com URL assinada e temporária + checagem de permissão.
- **Auditoria de leitura e escrita** (activity_log com `descricao`); relatório de acessos para o DPO.
- **Logs sem dados sensíveis**; **exportações** sanitizadas (padrão CIH) com registro de quem exportou.
- **Multi-tenant**: `empresa_id` + scope global + teste de isolamento entre empresas.
- **Retenção** de 20 anos; exclusão só lógica e auditada.
- Rate limit + Form Request; revisão `pentest-owasp` antes de cada release.

## 6. Customização: Regras da empresa

```
QUANDO  [condições]                     ENTÃO  [ação]
- idade ≥ 40                            - periodicidade do Periódico = 1 ano
- função em (Motorista, Operador)       - exigir exame "Audiometria"
- exposto ao risco "Ruído" (PGR)        - criar pendência de ASO em N dias
- departamento / estabelecimento        - alertar RH X dias antes do vencimento
- tipo de exame / afastamento ≥ N dias  - dispensar demissional se último ASO ≤ N dias
```

- **Condições:** idade, função, risco (PGR/PCMSO), departamento, estabelecimento, tipo de exame, tipo/duração de afastamento, tipo de contrato. Critérios sensíveis (sexo, condição de saúde) só após validação jurídica.
- **Ações:** definir periodicidade, exigir exame, criar pendência, enviar alerta, dispensar exame, definir prazo.
- **Trava legal**: bloqueia prazo maior que o da NR-7.
- **Prévia de impacto** ("muda o vencimento de 128 colaboradores") e **simulador** por colaborador ("vence em 14/03/2027 porque: Idade 40+ → 1 ano").
- **Prioridade**: vale o menor prazo; empate → regra mais específica (função > departamento > empresa).
- **Modos de aplicação**: só futuros · retroativo · a partir de uma data (job em lote).
- **Histórico/versão** por regra; desativar sem apagar.
- **Modelos prontos**: Padrão NR-7, Idade 40+, Idade 45+, Motoristas, Retorno após 30 dias.

## 7. Arquitetura

- **Bounded context** `App\Domain\SaudeOcupacional\` com subcontextos `Medicos`, `Funcoes`, `Pgr`, `Pcmso`, `Regras`, `Aso`, `Conformidade`, `Documentos`, `Relatorios`, `Afastamentos`, `Alertas`, `Clinicas`. Controllers só orquestram.
- **Motor de regras** com condições/ações como **strategies** (OCP); regras em JSON validado por schema.
- Reaproveitar: `App\Services\ControleExames\*`, `AsoVencimentoRelatorioService`, `AssinaturaDigitalService`, `DocumentoSsma`, `entity_anexos`, skill `mybp-pdf-documento-formal`.
- **Banco**: plural nas tabelas, prefixo da principal, `$table/$fillable/$casts`, mutators `_br`.
- **Query Builder** nos painéis/relatórios (`whereNull('deleted_at')` em todo join); **jobs** para recálculo, exportação CIH, PDFs em lote e e-mails; **cache** com TTL e invalidação por empresa.
- **Frontend Vue 3** nos padrões do `AGENTS.md`.
- **Testes** PHPUnit em SQLite `:memory:`; **manual** atualizado a cada parte (sem dados reais).

## 8. Visão das partes

| Parte | Nome | Depende de | Tamanho |
|---|---|---|---|
| 0 | Fundação: menu, perfis, segurança, Painel (esqueleto) | — | M |
| 1 | Motor de regras + Padrão NR-7 + Regras da empresa | 0 | G |
| 2 | Médicos responsáveis + Funções e Riscos (PGR básico) | 0 | M |
| 3 | PCMSO por empresa (versões, exames por função) | 1, 2 | G |
| 4 | Controle de vencimentos, conformidade e alertas periódicos | 1 (melhor com 3) | M |
| 5 | Histórico de ASO + ASO em PDF (NR-7) com assinatura | 2 | M |
| 6 | Gestão documental SST | 0 | M |
| 7 | Relatórios de acompanhamento + relatório analítico NR-7 | 3, 4, 5 | M |
| 8 | Afastamentos 2.0 | 0 | M |
| 9 | Pendências e gatilhos automáticos de ASO | 1, 8 (melhor com 3) | M |
| 10 | Ficha de saúde + Radar de alertas | 5, 8, 9 | M |
| 11 | Toxicológico e caderneta de vacinas | 1 | M |
| 12 | Clínicas: tabela de preços e faturamento | 0 | M |
| 13 | Futuro: PGR completo, eSocial (S-2220/S-2210/S-2240), BP+NR1 | 3, 8 | G |

Tamanho: **P** ≈ até 3 dias · **M** ≈ 1 semana · **G** ≈ 2 semanas (estimativa inicial).

```
P0 ─┬─ P1 ─────────┬─ P3 ─┬─ P4 ─┐
    │              │      │      ├─ P7
    ├─ P2 ─────────┘      │      │
    │    └─ P5 ───────────┼──────┘
    ├─ P6                 │
    ├─ P8 ─── P9 ─────────┴─ P10
    ├─ P11 (após P1)
    └─ P12                          P13 (após P3 + P8)
```

**Entregas sugeridas (releases):**

| Release | Partes | O RH ganha |
|---|---|---|
| **R1 — PCMSO** | 0, 1, 2, 3 | Menu, médicos, funções/riscos, PCMSO por empresa com exames por função, regras NR-7 + customizadas |
| **R2 — Acompanhamento** | 4, 5, 6, 7 | Vencimentos, alertas, histórico de ASO, documentos e relatórios |
| **R3 — Eventos** | 8, 9, 10 | Afastamentos, pendências automáticas, ficha de saúde e radar |
| **R4 — Complementos** | 11, 12 | Toxicológico, vacinas, clínicas e faturamento |
| **Futuro** | 13 | PGR completo, eSocial, BP+NR1 |

---

## Parte 0 — Fundação ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Entregue: flag do cliente, 3 habilidades, menu, Painel (ASO + afastamentos), `PerfilAcessoSaude`, `AuditoriaAcessoSaude`, middleware.
> Ficou para quando houver dado sensível (Partes 5, 6 e 8): cast `encrypted` e URL assinada de anexos. Periodicidade continua na rota atual (só mudou de menu).

- Menu **SAÚDE OCUPACIONAL** (seção 2) + flag por cliente.
- Perfis/permissões (seção 5.1) no seeder e grupos; `cadastro_empresa_exame` continua para cadastros atuais.
- Controles da seção 5.2: resources por perfil, cast criptografado, anexos com URL assinada, auditoria de leitura.
- **Painel** com os cards já calculáveis (vencidos/vencendo do relatório atual, afastados ativos).
- Periodicidade ASO movida para Configurações (redirect).

**Pronto quando:** testes provam que RH não recebe CID, que uma empresa não vê outra, e que menu respeita flag e permissão.

## Parte 1 — Motor de regras + Regras da empresa ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Ajustes de desenho na implementação:
> - **Padrão NR-7 no código** (`TravaLegalNr7`), não em linhas por empresa: sempre ativo, sem seeder/comando.
> - **Periodicidade padrão por tipo mantida** (`empresas_exame_tipos_periodicidades`), sem migrar para regras: as regras somam por cima e **só encurtam** (vale o menor prazo).
> - Condições e tipos em JSON (`saude_regras`), sem tabelas de condições/ações separadas. Ação única por enquanto: periodicidade.
> - Condição **exposto a risco** fica para a Parte 2 (vem do PCMSO); até lá, exposição por **cargo**. Departamento também fica para depois.
> - Trava legal: 2 anos / 730 dias também na tela Periodicidade ASO (preset "3 anos" removido).
> - Decisão nº 1 resolvida: mudança de cargo aprovada troca `feedback.vagas_abertas_id` → cargo vigente = `vagas_abertas.vaga_id`.

- Tabelas `saude_regras`, `saude_regras_condicoes`, `saude_regras_acoes`, `saude_regras_historicos`.
- Migrar `empresas_exame_tipos_periodicidades` para regras de periodicidade sem perder histórico.
- **Padrão NR-7** para toda empresa (seeder + comando para clientes atuais).
- Condições iniciais: idade, exposição a risco, função, departamento, tipo de exame. Ações: periodicidade, alerta.
- Idade de referência = idade na data do vencimento padrão; sem `curriculos.nascimento`, a condição de idade não se aplica.
- Tela **Regras da empresa**: editor "Quando… Então…", modelos, prévia de impacto, simulador, modos de aplicação, histórico.
- `AsoPeriodicidadeService` delega ao motor; `JobRecalcularVencimentoAsoLote` traz nascimento/função/risco no mesmo SELECT do lote.

**Testes:** 39 anos que faz 40 no ciclo; sem nascimento; duas regras (vence a menor); trava legal; Demissional sem vencimento; recálculo em lote; isolamento entre empresas.

## Parte 2 — Médicos responsáveis + Funções e Riscos (PGR básico) ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Ajustes de desenho na implementação:
> - Exposição a risco entrou aqui (estava prevista na Parte 1): cargo com risco no **PGR vigente** = exposto, e a regra NR-7 "exposto = anual" é aplicada automaticamente. Condições novas: `exposto_risco` e `risco`.
> - Coordenador do PCMSO é **por empresa** (sem estabelecimento por enquanto); estabelecimento entra com o PCMSO (Parte 3) se necessário.
> - Tabelas: `pgr_inventarios` → `pgr_inventarios_cargos` (com `ausencia_risco`) → `pgr_inventarios_riscos`.
> - Tipos de risco **Acidentes** e **Psicossocial** adicionados (NR-1).
> - Ficaram para depois: **importar riscos de planilha**, documento/PDF do PGR (Parte 6) e assinatura do médico (Partes 3/5).
> - Decisão nº 1 fechada: cargo vigente = `vagas_abertas.vaga_id` (mudança aprovada) ou `feedback.vaga_id`; saneamento por `mybp:saude-sanear-cargos`.

**Médicos responsáveis**
- `saude_medicos`: nome, CRM, UF do CRM, RQE/especialidade (Medicina do Trabalho), e-mail, telefone, tipo (`coordenador` | `examinador`), ativo.
- `saude_medicos_empresas`: médico × empresa/estabelecimento, papel e **vigência** (início/fim) — um coordenador vigente por PCMSO.
- Assinatura: vínculo com o módulo de **Assinatura Digital** (signatário) para ASO e PCMSO.
- Alertas: PCMSO sem coordenador vigente; vínculo do médico vencendo.
- Clínicas (`EmpresaExame`) podem ter médicos examinadores vinculados.

**Funções e Riscos (PGR básico)**
- Função = **cargo da admissão** (`vagas.id`). Não criar cadastro novo de funções: a tela **Funções e Riscos** lista os cargos de `vagas` da empresa e mostra riscos e exames de cada um.
- Serviço `CargoVigenteColaborador`: devolve o `vaga_id` vigente do colaborador (admissão → última mudança de cargo efetivada), em consulta única para listas (sem N+1).
- Comando de saneamento: admissões sem `vaga_id` → casa pelo nome do cargo na empresa; relatório das que não casaram.
- `pgr_inventarios` (empresa, estabelecimento, versão, vigência, responsável técnico, documento) e `pgr_inventarios_riscos` (`vaga_id` × risco de `exame_riscos`, fonte, intensidade, medidas de controle).
- Tela **PGR — Riscos**: riscos por função, importar de planilha, versão vigente.
- O PGR completo (matriz de risco, plano de ação 5W2H) fica para a Parte 13.

## Parte 3 — PCMSO por empresa ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Ajustes de desenho na implementação:
> - O PCMSO é **por programa** (`pcmsos` existente, ids preservados — na BPSE são contratos/clientes, usados em 23.924 encaminhamentos), não um por empresa. Versões em `pcmsos_versoes`.
> - Exames por cargo em `pcmsos_versoes_exames`; catálogo = tabela `exames` reaproveitada (+ `codigo_esocial`); sugestão por risco em `exame_riscos_exames`.
> - Saída prática: a **ficha de encaminhamento da clínica** lista os exames previstos no PCMSO vigente para o cargo e o tipo de ASO.
> - O PCMSO **não** altera o vencimento do ASO nesta parte (o motor de regras/PGR segue sendo a fonte); exposição continua vindo do PGR da empresa.
> - Ficaram para depois: assinatura digital do PDF pelo coordenador (módulo Assinatura Digital), PGR por estabelecimento/contrato, mostrar os exames previstos também no modal de encaminhamento.

- `pcmsos` ganha `versao`, `status` (rascunho/vigente/arquivado), `vigencia_inicio`, `vigencia_fim`, `medico_coordenador_id`, `estabelecimento`, `pgr_inventario_id`.
- `pcmsos_cargos` (`pcmso_id` × `vaga_id`) e `pcmsos_cargos_exames` (exame × tipo de ASO × periodicidade opcional × obrigatório/opcional).
- **Exames por função**: ao abrir a versão, o sistema sugere exames a partir dos riscos do PGR da função; o médico ajusta.
- Ações: Nova versão (copia a vigente), Editar, **Tornar vigente** (arquiva a anterior, `DB::transaction`), Gerar PDF formal, enviar para **assinatura do coordenador**, Histórico.
- Alimenta o motor: "exposto a risco" e "exigir exame" vêm do PCMSO vigente; encaminhamento sugere os exames da função.
- Alerta de vigência do PCMSO vencendo.

## Parte 4 — Controle de vencimentos, conformidade e alertas periódicos ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Ajustes de desenho na implementação:
> - `ConformidadeService` virou a fonte única (Painel, tela, exportação e alertas).
> - Índice novo em `examesesmts.exame_funcionario_id` (performance; beneficia o controle de exames).
> - Lote só pelo caminho com PCMSO (99,8% dos encaminhamentos da BPSE já são assim); Demissional fica individual.
> - Alertas por **marcos** (cada um avisado uma vez) em vez de lista diária; com o módulo ligado, o e-mail diário antigo deixa de ser enviado para a empresa. Destinatários = preferência "Vencimento ASO". Aviso a colaborador/gestor ficou para depois.
> - O relatório `vencimentoasos` continua existindo (não foi redirecionado); os cards do Painel agora levam à Conformidade.

- Status por colaborador ativo: **Válido / Vencendo / Vencido / Sem ASO**, com o motivo ("regra Idade 40+", "PCMSO 2026 — Motorista").
- Tela Conformidade (filtros compactos, lista/tabela, query params), encaminhamento em lote (job + cache anti-duplicidade), exportação CIH.
- **Alertas de exames periódicos**: janelas configuráveis (ex.: 60/30/7 dias) por empresa; e-mail ao RH e, opcional, ao colaborador e ao gestor (sem dado sensível); resumo semanal.
- Painel do RH completo; absorve o relatório `vencimentoasos` (redirect).

## Parte 5 — Histórico de ASO + ASO em PDF ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Ajustes de desenho na implementação:
> - O médico examinador é escolhido na **emissão do ASO** (não no formulário de resultado existente, que não foi alterado).
> - "Foto" do ASO em `examesesmts_aso` (1–1 com `examesesmts`); exames complementares confirmados na emissão (sugestão do PCMSO).
> - Função no ASO = cargo vigente do catálogo (ou cargo da admissão).
> - ASO assinado vai para "Documentos SST" (feito na Parte 6). Ficou para depois: o RH ver restrições liberadas pelo médico (hoje só a conclusão).

- **Histórico** por colaborador: todos os ASOs (tipo, data, resultado apto/inapto, validade, médico examinador, clínica, exames realizados, anexos), com filtros e linha do tempo.
- **ASO em PDF** com os campos mínimos da NR-7 (seção 3), gerado do encaminhamento/resultado.
- Assinatura digital do **médico examinador** e **ciência do colaborador** (módulo Assinatura Digital); ASO assinado vai para **Documentos SST**.
- Resultados detalhados de exames só no perfil Saúde; RH vê apto/inapto e restrições liberadas pelo médico.
- Migração: ASOs atuais (`examesesmts`) aparecem no histórico sem reprocessar.

## Parte 6 — Gestão documental SST ✅ implementada (05/11/2026 — ver `docs/SAUDE_OCUPACIONAL.md`)

> Ajustes de desenho na implementação:
> - Tabelas próprias (`saude_documentos` + versões + acessos) em vez de evoluir `DocumentoSsma` (ligado a contrato, sem uso) e `entity_anexos` (ainda não existe).
> - Disco privado só para SST; download sempre pela rota, com trilha. Situação calculada pela vigência (não há status gravado).
> - Por estabelecimento ficou para depois (o MyBP ainda não tem cadastro de estabelecimento); o colaborador é opcional no documento.
> - PGR: o módulo guarda o inventário; o documento formal do PGR é enviado por upload. PDF do PGR e envio de documento avulso para assinatura ficaram para depois.
> - Ficha de saúde: o Histórico de ASO tem o link "Documentos SST" do colaborador (a Ficha de saúde completa é da Parte 10).


- Repositório por empresa/estabelecimento: **PCMSO, PGR, relatório analítico, ASOs, laudos (LTCAT, insalubridade/periculosidade), ordens de serviço, certificados**.
- Evoluir/integrar `DocumentoSsma` + `entity_anexos`: tipo de documento, versão, **vigência e vencimento**, responsável, status (rascunho/vigente/vencido/arquivado), nível de sigilo (comum/sensível).
- Upload ou documento gerado pelo sistema (PCMSO/ASO/relatórios); fluxo de **assinatura digital**.
- Alertas de documento vencendo; trilha de quem visualizou/baixou.
- Documentos do colaborador aparecem na Ficha de saúde; documentos da empresa na tela Documentos SST.

## Parte 7 — Relatórios de acompanhamento

- **Relatório analítico anual do PCMSO** (NR-7): exames clínicos por tipo, exames complementares por tipo, resultados alterados, estatísticas por função/setor, período de referência e assinatura do coordenador.
- Relatórios operacionais: vencimentos por período, conformidade por empresa/setor/função, ASOs realizados por clínica/médico, pendências por motivo, afastamentos por tipo (CID agregado só no perfil Saúde), custos por clínica (após Parte 12).
- Indicadores no Painel: % de conformidade, ASOs em dia, tempo médio encaminhamento → resultado.
- Geração em job, cache, exportação Excel padrão CIH e PDF formal; o PDF pode ir para Documentos SST.

## Parte 8 — Afastamentos 2.0

- `afastamentos` ganha `empresa_id`, `tipo`, `cid` (criptografado), `dias`, `status`, `data_retorno`, `origem`; atestados em `entity_anexos`.
- Alerta INSS (> 15 dias ou soma do mesmo CID em 60 dias); aba Em aberto com **Registrar retorno**.
- RH vê datas e tipo genérico; CID/atestado só o perfil Saúde. Migração dos registros atuais.

## Parte 9 — Pendências e gatilhos automáticos de ASO

- `exames_pendencias` (empresa_id, feedback_id, exame_tipo_id, motivo, origem, prazo, status, dispensa_motivo, regra_id).
- Eventos em fila: admissão → **Admissional**; retorno de afastamento ≥ 30 dias → **Retorno Trabalho**; mudança de cargo com mudança de risco (PGR/PCMSO) → **Mudança de Função**; demissão → **Demissional** com dispensa 135/90 dias.
- Gatilhos também são **regras** (Padrão NR-7 + customizáveis).
- Pendência cumprida ao salvar o ASO do mesmo tipo; integra com `RestricaoOrdemExameDemitido`.
- Campo **grau de risco** da empresa/estabelecimento.

## Parte 10 — Ficha de saúde + Radar de alertas

- **Ficha**: linha do tempo (ASOs, afastamentos, pendências, vacinas, toxicológico, documentos); visão RH x visão Saúde; PDF; leitura auditada.
- **Radar**: retorno sem exame, afastados há 15+ dias, mesmo CID repetido no setor (só Saúde), faltas curtas repetidas, pendências vencidas, setores acima da média; job + cache; alertas próprios pelo motor de regras.

## Parte 11 — Toxicológico e caderneta de vacinas

- Toxicológico (motoristas CNH C/D/E): exame, laboratório, resultado (só Saúde), validade por regra, alerta.
- Vacinas: catálogo, obrigatoriedade por risco/função (regra), caderneta, comprovante (`carteira_vacina`), alertas.

## Parte 12 — Clínicas: tabela de preços e faturamento

- Preço por clínica × exame com vigência; valor gravado no encaminhamento.
- Faturamento por período e NF (pendente/faturado/pago); portal da clínica só com os próprios dados.

## Parte 13 — Futuro

- **PGR completo**: matriz de risco, plano de ação 5W2H, GHE, revisão periódica.
- **eSocial** S-2220 (ASO), S-2210 (CAT), S-2240 (condições ambientais) — certificado A1, fila de transmissão e retorno.
- **BP+NR1**: resultados agregados da pesquisa psicossocial no Radar e no PGR (riscos psicossociais).

---

## 9. Decisões em aberto

1. ~~Fonte da função do colaborador~~ → **decidido: cargo da admissão (`vaga_id`)**. Falta confirmar se a mudança de cargo efetivada atualiza o vínculo do colaborador ou só fica em `mudanca_cargo` (verificar na Parte 2).
2. Onde guardar o **grau de risco** (empresa ou estabelecimento).
3. Modelo de idade sugerido na implantação (40+ ou 45+).
4. Quem tem o perfil **Saúde** e o que o gestor vê.
5. Médico coordenador: **cadastro interno** ou também vindo da clínica parceira.
6. `DocumentoSsma` atual: evoluir a tabela ou migrar para a nova estrutura de Documentos SST.
7. Cliente **piloto** e médico coordenador para validar regras, ASO em PDF e relatório analítico.
8. Validação jurídica de critérios sensíveis no motor de regras.

## 10. Entrega por parte (checklist)

- [ ] Migrations + models no padrão (table/fillable/casts/_br)
- [ ] Domínio/serviços com testes em `:memory:` (isolamento entre empresas e permissões)
- [ ] Telas Vue nos padrões de listagem/filtros/card/modal
- [ ] Perfis e flag por cliente respeitados no backend
- [ ] Sem dado sensível em log; exportações sanitizadas (CIH); acessos auditados
- [ ] Cache com TTL e invalidação
- [ ] Revisão `pentest-owasp`
- [ ] Manual atualizado (sem dados reais) e base RAG regenerada
- [ ] Doc técnica em `docs/` atualizada
