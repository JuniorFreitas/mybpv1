# Saúde Ocupacional (módulo SST)

Documentação técnica do que já está implementado. O plano completo, com todas as partes, fica em
[PLANO_SAUDE_OCUPACIONAL.md](PLANO_SAUDE_OCUPACIONAL.md).

| Parte | Situação |
|---|---|
| 0 — Fundação (menu, perfis, segurança, Painel) | **Implementada** |
| 1 — Motor de regras + Padrão NR-7 + Regras da empresa | **Implementada** |
| 2 — Médicos responsáveis + PGR básico (riscos por cargo) + exposição no motor | **Implementada** |
| 3 — PCMSO por programa: versões, coordenador, exames por cargo, ficha da clínica, PDF | **Implementada** |
| 4 — Conformidade de ASO, encaminhamento em lote, exportação e alertas periódicos | **Implementada** |
| 5 — Histórico de ASO + ASO em PDF (NR-7) com assinatura do médico e ciência do colaborador | **Implementada** |
| 6 a 13 | Planejadas |

## Ligar para um cliente

1. Rodar as migrations (`2026_11_05_100000_*` e `2026_11_05_100001_*`).
2. **Administração → Clientes → Configurações → Saúde Ocupacional**: `Habilitado`.
3. Conferir os grupos de acesso (seção Permissões).

Com o módulo desligado, nada muda para o cliente: o menu não aparece, as rotas devolvem 403
e a Periodicidade ASO continua em **Cadastro**.

## Permissões

| Habilidade | Perfil | Liberação automática (migration) |
|---|---|---|
| `saude_ocupacional` | RH: menu + Painel | Grupos que já tinham `admissao_controle_exames`, `relatorio_asos` ou `cadastro_empresa_exame` |
| `saude_ocupacional_configuracao` | Configurador | Grupos que já tinham `cadastro_empresa_exame` |
| `saude_ocupacional_dados_sensiveis` | Saúde/SESMT: CID, laudos, atestados | **Nenhum.** O cliente escolhe quem recebe (LGPD art. 11) |

## Menu SAÚDE OCUPACIONAL

Aparece com o módulo ligado + `saude_ocupacional`:

- **Painel** (`g.saude_ocupacional.painel`)
- **ASO e Exames**: Controle de exames (`admissao_controle_exames`), Vencimento de ASOs (`relatorio_asos`)
- **Configurações** (`saude_ocupacional_configuracao` + `cadastro_empresa_exame`): Exames, Periodicidade ASO

As telas são as mesmas de antes (mesmas rotas e permissões). Com o módulo ligado, a Periodicidade ASO
sai do menu Cadastro para quem a enxerga no menu novo.

## Painel do RH

Rotas (middleware `saude.ocupacional.habilitado` + `can:saude_ocupacional`):

| Método | Rota | Ação |
|---|---|---|
| GET | `g/saude-ocupacional/painel` | Tela |
| GET | `g/saude-ocupacional/painel/dados` | JSON do resumo (throttle 60/min) |

**População:** colaboradores com admissão `ADMITIDO` ou `PRONTO PARA ADMISSÃO`, sem demissão,
sem exclusão lógica (mesma regra do relatório de vencimento de ASO).

**Último ASO válido:** `examesesmts` com `atual = 1`, `exame_realizado = 1`, `resultado.aprovado = Sim`
e `data_vencimento` preenchida. Vale o maior vencimento.

| Card | Regra |
|---|---|
| Vencidos | último vencimento < hoje |
| Vencendo | hoje ≤ vencimento ≤ hoje + janela (`ClienteConfig.vencimento_aso`; sem config: 90 dias, com aviso na tela) |
| Sem ASO | ativo sem nenhum ASO válido |
| Em dia | total − (sem ASO + vencidos + vencendo) |
| Afastados hoje | afastamento com `data_inicio ≤ hoje ≤ data_fim` |
| Voltando em breve | volta em até 7 dias |
| Mais de 15 dias | duração > 15 dias (encaminhar ao INSS) |

A lista de afastados mostra nome, período, dias, volta em, e as marcas **ASO de retorno**
(≥ 30 dias, NR-7) e **INSS**. O **motivo não é enviado** ao navegador: é texto livre e pode
conter dado de saúde.

Links dos cards só vêm quando o usuário tem permissão na tela de destino.

### Performance e cache

- Query Builder: uma consulta agregada para os ASOs (subquery do último vencimento por colaborador)
  e uma para os afastamentos em curso. Sem N+1.
- Cache por empresa e por dia, TTL 5 min, chave versionada (`PainelCache`).
- Invalidação: ao salvar/excluir `Examesesmt` e `Afastamento`. Admissões e demissões entram no próximo
  recálculo (até 5 min).

## Regras da empresa (Parte 1)

Tela: **Saúde Ocupacional → Configurações → Regras da empresa** (`g/saude-ocupacional/regras`,
módulo ligado + `saude_ocupacional` + `saude_ocupacional_configuracao`).

### Como o vencimento é calculado

1. **Padrão do tipo de exame** = Periodicidade ASO (`empresas_exame_tipos_periodicidades`; sem config: 1 ano − 1 dia).
2. **Regras da empresa** ativas para o tipo: `QUANDO` condições (todas precisam valer) `ENTÃO` validade.
3. Vale o **menor prazo** entre o padrão e as regras que se aplicam. Regra nunca alonga o padrão.
4. **Idade** medida na data do vencimento padrão (quem faz 40 durante o ciclo já entra na "40+").
   Sem `curriculos.nascimento`, regra de idade não se aplica.
5. **Cargo vigente** = `vagas_abertas.vaga_id` do `feedback.vagas_abertas_id` (atualizado na mudança de cargo
   aprovada) ou `feedback.vaga_id` (admissão).
6. Módulo desligado: regras são ignoradas (vencimento volta ao padrão).

### Padrão NR-7 (regras de mercado)

Ficam no código (`TravaLegalNr7`), sempre ativas, não podem ser apagadas e aparecem na tela:

- **Trava:** nenhuma periodicidade acima de **2 anos / 730 dias** (vale também para a tela Periodicidade ASO;
  o preset "3 anos" saiu e config antiga acima do limite mostra aviso).
- Exposto a risco do PGR = anual: orientação até a Parte 2 (PCMSO). Hoje a empresa expressa exposição por **cargo**.
- Idade não é exigência legal desde 2020: modelos opcionais "Idade 40+" / "Idade 45+".

### Condições disponíveis

| Condição | Valor | Classe |
|---|---|---|
| Idade a partir de | 14–100 | `CondicaoIdadeMinima` |
| Idade até | 14–100 (ex.: 17 = menores de 18) | `CondicaoIdadeMaxima` |
| Cargo | lista de `vagas.id` **da própria empresa** | `CondicaoCargo` |

Condição nova = classe que implementa `CondicaoRegra` + registro em `CatalogoCondicoes`.

### Tela

- **Regras de mercado** (quadro NR-7), lista de regras, **Nova regra** e **Usar modelo** (Idade 40+, Idade 45+,
  Menores de 18, Cargos com exposição).
- **Ver impacto** (prévia): quantos ASOs atuais mudam de vencimento, antecipados/adiados e exemplos.
- **Modos de aplicação**: só próximos · retroativo · a partir de uma data (job em lote existente). Bloqueia
  retroativo se já houver recálculo em andamento.
- Ativar / desativar / excluir (exclusão lógica) com prévia e modo.
- **Simulador**: busca colaborador ativo e mostra, por tipo, o vencimento "se fizer hoje", o último ASO
  (gravado x pelas regras de hoje) e cada condição ✓/✗.
- **Histórico** (`saude_regras_historicos`) + activity_log `saude_regras` com antes/depois.

### Rotas (`g/saude-ocupacional/regras`)

| Método | Rota | Ação |
|---|---|---|
| GET | `/` | Tela |
| GET | `dados` | Regras + opções (tipos, cargos, condições, modelos, regras de mercado) |
| POST | `/` · PUT `{id}` | Criar · editar (`SalvarRegraRequest`) |
| PATCH | `{id}/ativo` | Ativar/desativar |
| DELETE | `{id}` | Excluir |
| POST | `previa` · GET `{id}/previa-remocao` | Impacto (throttle 30/min) |
| GET | `colaboradores?busca=` · `simular/{feedbackId}` | Simulador (throttle 60/min) |
| GET | `historico` | Histórico paginado |

### Onde o motor entra

- `AsoPeriodicidadeService::calcularDataVencimento(..., $feedbackId)` — usado em `ControleExameController`
  (salvar/editar resultado) e `AdmissaoAso`.
- `JobRecalcularVencimentoAsoLote` — com regras para o tipo, calcula por colaborador e grava o lote em
  **um UPDATE** (`CASE id`). Sem regras, segue o UPDATE SQL antigo. No fim, invalida o cache do Painel.
- Cache das regras ativas por empresa (`RegrasAtivasRepositorio`, TTL 10 min, invalidado ao salvar).

## Médicos responsáveis (Parte 2)

Tela: **Saúde Ocupacional → Configurações → Médicos responsáveis** (`g/saude-ocupacional/medicos`,
`saude_ocupacional_configuracao`).

- `saude_medicos`: nome, CRM (só dígitos, 4–10), UF, RQE, especialidade, contato, ativo. **CRM/UF único por empresa.**
- `saude_medicos_vinculos`: papel `coordenador` (do PCMSO) ou `examinador`, clínica (`empresa_exames`, só examinador e
  da própria empresa) e vigência (início obrigatório, fim opcional).
- **Um coordenador vigente por vez**: vigências de coordenador não se sobrepõem na empresa.
- Coordenador vigente = médico ativo + vínculo dentro da vigência (`coordenadorVigente`).
- Painel: card "Médico coordenador" (alerta se não houver) e "Vínculos vencendo" (fim em até 30 dias).
- Auditoria: activity_log `saude_medicos`. Dados profissionais apenas.

## PGR — riscos por cargo (Parte 2)

Tela: **Saúde Ocupacional → Programas → PGR — Riscos por cargo** (`g/saude-ocupacional/pgr`,
`saude_ocupacional_configuracao`).

- Versões em `pgr_inventarios` (`rascunho` → `vigente` → `arquivado`), cargos em `pgr_inventarios_cargos`
  (`vaga_id`, `ausencia_risco`) e riscos em `pgr_inventarios_riscos` (risco do catálogo, fonte geradora,
  intensidade, medidas de controle).
- **Nova versão** copia a vigente. Só um rascunho por vez; só o rascunho é editável.
- **Publicar**: pede início da vigência (depois do início da vigente), mostra a prévia (ASOs que mudam e cargos com
  colaboradores não avaliados) e o modo de aplicação. A anterior vai para `arquivado` com fim = início − 1 dia.
- **Catálogo de riscos** (`exame_riscos`, por empresa) na própria tela. Tipos ganharam **Acidentes** e
  **Psicossocial** (NR-1).
- Lista de cargos mostra quantos colaboradores ativos têm cada cargo (filtro "só com colaboradores").
- Auditoria: activity_log `saude_pgr`.

### Exposição a risco no motor

- Colaborador **exposto** = cargo vigente com ao menos um risco no **PGR vigente** (`PerfilColaborador::riscoIds`).
- Com PGR vigente, entra a regra de mercado **"NR-7: exposto a risco do PGR" = 1 ano** em todos os tipos com
  vencimento (`TravaLegalNr7::regraExpostoRisco`, id −1, não editável, aparece no simulador).
- Novas condições nas Regras da empresa: **Exposto a qualquer risco do PGR** (`exposto_risco`) e
  **Exposto a um destes riscos** (`risco`, só riscos da própria empresa). Ex.: "Ruído → 180 dias".
- Sem PGR vigente (ou com módulo desligado), exposição não é considerada.
- Cache do PGR vigente por empresa (`PgrVigenteRepositorio`, TTL 10 min, invalidado ao publicar).
- Prévias de regra e de PGR usam o mesmo `ComparadorCenariosVencimento`.

### Saneamento de cargos

Colaborador ativo sem cargo no catálogo não é alcançado por PGR nem por regra de cargo:

```bash
docker compose exec mybpdp php artisan mybp:saude-sanear-cargos {empresa_id}            # simula
docker compose exec mybpdp php artisan mybp:saude-sanear-cargos {empresa_id} --aplicar  # grava feedback.vaga_id
```

Casa `admissoes.cargo` com `vagas.nome` da mesma empresa (maiúsculas/espaços ignorados), só para quem está sem
`vaga_id`. Lista os cargos sem correspondente (nunca nomes de pessoas).

## PCMSO (Parte 3)

Tela: **Saúde Ocupacional → Programas → PCMSO** (`g/saude-ocupacional/pcmso`, `saude_ocupacional_configuracao`).

- **Programa** = linha de `pcmsos` (já existente, ids preservados; em terceirização, um por contrato/cliente).
  O cadastro antigo de nomes (Cadastro → PCMSO) e o encaminhamento com `pcmso_id` continuam iguais.
- **Versões** em `pcmsos_versoes` (`rascunho` → `vigente` → `arquivado`), uma vigente e um rascunho por programa.
  Nova versão copia a vigente.
- **Publicar** exige **médico coordenador com vínculo de coordenador vigente no início** (NR-7), ao menos um cargo com
  exames e início depois da vigente; a anterior é arquivada com fim = início − 1 dia. Fim opcional (sugestão: 1 ano).
- **Exames por cargo** (`pcmsos_versoes_exames`): exame do catálogo × tipos de ASO × obrigatório/opcional ×
  periodicidade própria opcional (até 2 anos; "Do ASO" = acompanha a validade do ASO).
- **Catálogo de exames**: tabela `exames` reaproveitada (estava vazia), com `codigo_esocial` (Tabela 27, 4 dígitos).
- **Exames por risco** (`exame_riscos_exames`): ex.: Ruído → Audiometria (Admissional, Periódico, Demissional).
  **Sugerir pelo PGR** acrescenta ao rascunho os exames dos riscos de cada cargo no PGR vigente (não remove nada).
- **Ficha de encaminhamento** (PDF para a clínica e envio para assinatura): com PCMSO no encaminhamento, módulo ligado e
  versão vigente na data do encaminhamento, lista "Exames previstos no PCMSO" do cargo vigente para aquele tipo de ASO
  (`ExamesPrevistosPcmso`). Falha nessa seção nunca impede a ficha.
- **PDF formal** do PCMSO (`pdf.saude-ocupacional.pcmso`): empresa, programa, versão, vigência, coordenador, base de
  riscos e tabela cargo × riscos × exames × tipos × periodicidade, com assinatura do coordenador.
- **Painel**: card PCMSO (programas com versão vigente, vencidos, vencendo em 60 dias).
- Auditoria: activity_log `saude_pcmso`.
- O vencimento do ASO **não** muda pelo PCMSO nesta parte (continua no motor de regras).

## Conformidade de ASO e alertas (Parte 4)

Tela: **Saúde Ocupacional → ASO e Exames → Conformidade** (`g/saude-ocupacional/conformidade`, perfil RH `saude_ocupacional`).
Os cards do Painel (vencidos, vencendo, sem ASO) abrem esta tela já filtrada (`?status=`).

### Situação de cada colaborador ativo (`ConformidadeService`, fonte única)

| Situação | Regra |
|---|---|
| Vencido | último ASO válido com vencimento < hoje |
| Vencendo | hoje ≤ vencimento ≤ hoje + janela (`ClienteConfig.vencimento_aso`; sem config 90 dias) |
| Sem ASO | nenhum ASO válido |
| Em dia | demais |

- O Painel usa os mesmos totais (números idênticos). Conferido no MySQL local com a regra antiga do relatório: iguais.
- Lista: colaborador, cargo vigente, situação, vencimento (dias), último ASO (tipo/data), **por que vence**
  (motor: "Regra Idade 40+: 1 ano") e **encaminhado** (encaminhamento ainda sem resultado).
- Filtros: situação (chips com contagem), busca por nome e cargo, guardados na URL. Paginação de 30.
- Performance: consulta base leve (nome, vencimento, status) para totais/filtro/página; detalhes só dos ids da página.
  Índice novo `examesesmts.exame_funcionario_id` (sem ele, "encaminhamento sem resultado" levava 9 s; com ele, totais
  ~60 ms e página ~150 ms em 2.358 colaboradores). O índice também beneficia o controle de exames.

### Encaminhamento em lote

- Botão **Encaminhar selecionados** (até 200; exige `admissao_controle_exames`): tipo (sem Demissional), PCMSO, clínica,
  data de realização e avisos (e-mail clínica/colaborador, WhatsApp).
- Mesmo caminho do encaminhamento individual com PCMSO (`ExameFuncionario` com formulário "Exames" da empresa), em fila
  (`JobEncaminhamentoLote`, 25 por job; erro em um colaborador não derruba os outros).
- Anti-duplicidade: pula quem tem encaminhamento sem resultado (padrão) e trava em cache por colaborador + tipo (10 min).
- Auditoria: activity_log `saude_encaminhamento_lote` (só contagem e parâmetros).

### Exportação

**Exportar** gera Excel em fila (`JobExportarConformidade` → `mybp:exportarExcel` → Exportações + notificação), com
cabeçalho fixo, colunas consistentes, "Não informado" no vazio e neutralização de fórmula (`=`, `+`, `-`, `@`).
A mesma exportação não é gerada duas vezes em 10 min (cache).

### Alertas de exames periódicos

- `mybp:saude-alertas` (agendado diariamente às 07:00, `routes/schedule.php`), só empresas com o módulo ligado.
- **Marcos**: ASO que hoje está a N dias do vencimento (padrão 60, 30, 7; até 4 valores, 1–180) e o que **venceu ontem**.
  Cada marco de cada vencimento é avisado **uma vez** (cache 400 dias; novo ASO = novos avisos).
- **Resumo semanal** às segundas (totais + link do Painel), configurável.
- Destinatários: quem marcou **"Vencimento ASO"** nas notificações (mesma preferência do e-mail antigo).
- E-mail no layout oficial (`layouts.mail.layout`), em fila (`JobMailSaudeOcupacional`), só nome, cargo e datas.
- Config na própria tela (botão **Alertas**, perfil configurador): `cliente_configs.saude_alerta_janelas` e
  `saude_resumo_semanal`.
- Com o módulo ligado, o e-mail diário antigo (`mybp:vencimentoAso`, lista completa todo dia) **não é mais enviado** para
  a empresa — os marcos substituem. Empresas sem o módulo continuam como antes.

### Arquivos da Parte 4

| Camada | Caminho |
|---|---|
| Migrations | `2026_11_05_140000_add_exame_funcionario_id_index_to_examesesmts_table.php`, `..._140001_add_saude_alertas_to_cliente_configs_table.php` |
| Domínio | `Conformidade/ConformidadeService`, `Conformidade/EncaminhamentoLoteService`, `Alertas/AlertasConfigService`, `Alertas/AlertasPeriodicosService` |
| Jobs | `SaudeOcupacional/JobEncaminhamentoLote`, `JobExportarConformidade`, `JobMailSaudeOcupacional` |
| Comando | `mybp:saude-alertas` (`SaudeAlertasCommand`) |
| E-mail | `Mail/SaudeOcupacional/SaudeOcupacionalMail`, `resources/views/email/saude-ocupacional/*` |
| Controller | `SaudeOcupacional/ConformidadeController` (rotas `g/saude-ocupacional/conformidade/*`) |
| Vue | `resources/js/components/saude-ocupacional/conformidade/*` |

## Histórico de ASO e ASO em PDF (Parte 5)

Tela: **Saúde Ocupacional → ASO e Exames → Histórico de ASO** (`g/saude-ocupacional/historico-aso?colaborador={id}`,
perfil RH). Na Conformidade, o nome do colaborador abre o histórico.

### Histórico (`HistoricoAsoService`)

- Busca colaboradores ativos **e desligados**. Lê o que já existe (encaminhamentos + resultados), do mais recente ao
  mais antigo — nada é reprocessado.
- Por item: tipo, clínica, PCMSO, encaminhado em, situação (aguardando resultado / realizado / não realizado),
  conclusão (Apto / Apto com Restrição / Inapto), aptidões NR-35 (altura) e NR-33 (confinado), vencimento (atual),
  ASO emitido e status da assinatura.
- CPF sempre mascarado na tela (`123.***.***-45`).
- **Perfil Saúde** (`saude_ocupacional_dados_sensiveis`) vê também `observacoes` e `pendencias_quais` do resultado e o
  número de anexos; a leitura é registrada (`activity_log` `saude_ocupacional_acesso`). **RH** vê "anexos restritos".

### ASO em PDF (`AsoDocumentoService`, NR-7 7.5.19.1)

- **Emitir ASO** (exige `admissao_controle_exames`, só para exame realizado): escolhe o **médico examinador**
  (Médicos responsáveis; os da clínica do encaminhamento aparecem primeiro) e confirma os **exames complementares**
  (sugeridos pelo PCMSO vigente na data do exame). Grava a "foto" em `examesesmts_aso`: examinador, coordenador do
  PCMSO vigente na data, função (cargo vigente), riscos do PGR e exames — o PDF não muda se o cadastro mudar.
- PDF (`pdf.saude-ocupacional.aso`): razão social e CNPJ; nome, **CPF completo** (exigido no ASO) e função; tipo de
  exame; riscos do PGR ou "ausência de fatores de risco"; exame clínico + complementares com datas; **APTO/INAPTO PARA A
  FUNÇÃO**; aptidões NR-35/NR-33; médico responsável pelo PCMSO; médico examinador (nome/CRM); assinaturas do médico e
  do empregado (ciência). Observações clínicas **não** entram no ASO.
- **Assinar**: envia pelo módulo Assinatura Digital (`criarEnvio`, tipo `aso`, sequencial): 1º médico examinador
  (e-mail do cadastro), 2º colaborador (e-mail e CPF do currículo). Exige assinatura habilitada e cota. Depois de enviado,
  o ASO não pode ser reemitido.
- Auditoria: `activity_log` `saude_aso` na emissão.

### Arquivos da Parte 5

| Camada | Caminho |
|---|---|
| Migration | `2026_11_05_150000_create_examesesmts_aso_table.php` |
| Model | `ExamesesmtAso` |
| Domínio | `Aso/HistoricoAsoService`, `Aso/AsoDocumentoService` |
| Controller | `SaudeOcupacional/HistoricoAsoController` (rotas `g/saude-ocupacional/historico-aso/*`) |
| PDF | `resources/views/pdf/saude-ocupacional/aso.blade.php` |
| Vue | `resources/js/components/saude-ocupacional/historico/*` |

## Documentos SST (Parte 6)

Tela: **Saúde Ocupacional → Documentos SST** (`g/saude-ocupacional/documentos`, perfil RH `saude_ocupacional` para ver e
baixar; enviar, nova versão, editar e arquivar exigem `saude_ocupacional_configuracao`). Filtros por situação (chips),
tipo, busca e colaborador (`?feedback_id=`), todos em query params. No Histórico de ASO há o link "Documentos SST" do
colaborador.

### Modelo

- `saude_documentos` (empresa, tipo, título, sigilo, colaborador opcional, origem, responsável) →
  `saude_documentos_versoes` (arquivo, hash SHA-256, emissão, vigência, assinatura digital) →
  `saude_documentos_acessos` (quem enviou/visualizou/baixou, IP, quando).
- Tipos: PCMSO, PGR, relatório analítico, LTCAT, laudos de insalubridade/periculosidade, ordem de serviço (NR-1),
  certificado, ASO, laudo/exame médico (sempre sigiloso) e outro.
- **Versão atual** = maior número. A situação vem da vigência dela (`SituacaoDocumentoSst`): vencido, vencendo
  (até 60 dias), vigente ou sem validade (sem fim de vigência).
- Arquivar = soft delete: some das listas, mas arquivo e trilha ficam guardados (prazo legal de guarda).

### Segurança

- Disco **privado** `disco-saude-documentos` (pasta por empresa, nome aleatório, sem URL pública). O arquivo só sai pela
  rota `documentos/versoes/{id}/arquivo` (permissão, empresa, sigilo, `Cache-Control: no-store`, `nosniff`).
  PDF e imagem abrem no navegador (`?visualizar=1`); Word/Excel sempre baixam.
- Upload: PDF, JPG/PNG, DOCX, XLSX até 20 MB; o tipo é conferido pelo **conteúdo** (finfo), não pela extensão.
  Se a validação falhar, nada fica gravado.
- **Sigiloso**: só o perfil Saúde (`saude_ocupacional_dados_sensiveis`) envia e vê; para o RH o documento responde
  como inexistente. Abrir um sigiloso grava também a auditoria LGPD (`saude_ocupacional_acesso`).
- Auditoria das alterações em `activity_log` `saude_documentos` (só referências, nunca conteúdo).

### Integrações automáticas (`ArquivamentoAutomaticoSst`, idempotente por origem)

- **PCMSO publicado** → `JobArquivarPcmsoSst` (fila) gera o PDF da versão e guarda com a vigência dela. Ao publicar a
  versão seguinte, o documento da anterior recebe o fim de vigência.
- **ASO assinado** → na conclusão da assinatura (`AssinaturaDigitalService::processarPosConclusao`) vira documento do
  colaborador, reaproveitando o PDF assinado (sem cópia). Sem vigência: o vencimento do ASO é da Conformidade.
- Documento gerado pelo sistema não aceita nova versão manual.
- **Painel**: card "Documentos SST" com vencidos/vencendo e link já filtrado.

### Arquivos da Parte 6

| Camada | Caminho |
|---|---|
| Migration | `2026_11_05_160000_create_saude_documentos_tables.php` |
| Models | `SaudeDocumento`, `SaudeDocumentoVersao` |
| Domínio | `Documentos/DocumentosSstService`, `Documentos/ArmazenamentoDocumentoSst`, `Documentos/ArquivamentoAutomaticoSst`, `Documentos/SituacaoDocumentoSst` |
| Job | `SaudeOcupacional/JobArquivarPcmsoSst` |
| Controller / Request | `SaudeOcupacional/DocumentosSstController`, `SaudeOcupacional/EnviarDocumentoSstRequest` |
| Config | disco `disco-saude-documentos` em `config/filesystems.php` (`Arquivo::DISCO_SAUDE_DOCUMENTOS`) |
| Vue | `resources/js/components/saude-ocupacional/documentos/*` |

## Segurança (base para as próximas partes)

| Peça | Uso |
|---|---|
| `App\Domain\SaudeOcupacional\ModuloSaudeOcupacional` | `habilitado(empresaId)` — flag do cliente com cache (10 min, invalidado ao salvar o cliente) |
| `App\Http\Middleware\VerificaModuloSaudeOcupacional` | alias `saude.ocupacional.habilitado` — 403 com o módulo desligado |
| `App\Domain\SaudeOcupacional\Seguranca\PerfilAcessoSaude` | `podeAcessar`, `podeConfigurar`, `podeVerDadosSensiveis`, `ocultarCamposSensiveis()` — remove campo sensível **no backend** |
| `App\Domain\SaudeOcupacional\Seguranca\AuditoriaAcessoSaude` | `registrar()` — activity_log `saude_ocupacional_acesso` (quem, ação, recurso, id, IP), nunca o conteúdo |

Regras para as próximas partes:

- Todo endpoint com dado sensível passa por `ocultarCamposSensiveis()` e registra leitura/exportação na auditoria.
- Campos sensíveis novos usam cast `encrypted`; anexos sensíveis só por URL assinada e temporária.
- Nada de CID, CPF completo ou conteúdo de atestado em log.

## Arquivos

| Camada | Caminho |
|---|---|
| Migrations | `database/migrations/2026_11_05_100000_add_modulo_saude_ocupacional_to_cliente_configs_table.php`, `2026_11_05_100001_add_saude_ocupacional_habilidades.php` |
| Domínio | `app/Domain/SaudeOcupacional/` (`ModuloSaudeOcupacional`, `Seguranca/*`, `Painel/*`) |
| Controller | `app/Http/Controllers/SaudeOcupacional/PainelController.php` |
| Middleware | `app/Http/Middleware/VerificaModuloSaudeOcupacional.php` |
| View | `resources/views/g/saude-ocupacional/painel/index.blade.php` |
| Vue | `resources/js/components/saude-ocupacional/painel/` (`PainelSaudeOcupacional`, `PainelIndicador`, `PainelAfastadosLista`) |
| Entry Mix | `resources/js/g/saude-ocupacional/painel/app.js` → `public/js/g/saude-ocupacional/painel/` |
| Menu | `resources/views/layouts/menu.blade.php` (bloco `saude_ocupacional`) |

### Arquivos da Parte 1

| Camada | Caminho |
|---|---|
| Migrations | `2026_11_05_110000_create_saude_regras_table.php`, `2026_11_05_110001_create_saude_regras_historicos_table.php` |
| Models | `SaudeRegra`, `SaudeRegraHistorico` |
| Domínio | `app/Domain/SaudeOcupacional/Regras/` (`MotorPeriodicidadeAso`, `Periodicidade`, `PerfilColaborador`, `TravaLegalNr7`, `Condicoes/*`, `RegrasEmpresaService`, `PreviaImpactoRegra`, `SimuladorVencimento`, repositórios) e `Colaboradores/ColaboradoresAtivosQuery` |
| Controller / Request | `SaudeOcupacional/RegrasEmpresaController`, `Requests/SaudeOcupacional/SalvarRegraRequest` |
| Vue | `resources/js/components/saude-ocupacional/regras/` (`RegrasEmpresa`, `RegraFormModal`, `RegraAcaoModal`, `PreviaImpacto`, `ModoAplicacaoCampos`, `RegrasSimulador`, `RegrasHistorico`, `RegrasMercado`) |

### Arquivos da Parte 2

| Camada | Caminho |
|---|---|
| Migrations | `2026_11_05_120000_create_saude_medicos_table.php`, `..._120001_create_saude_medicos_vinculos_table.php`, `..._120002_create_pgr_inventarios_tables.php`, `..._120003_add_tipos_risco_nr1_to_exame_risco_tipos.php` |
| Models | `SaudeMedico`, `SaudeMedicoVinculo`, `PgrInventario`, `PgrInventarioCargo`, `PgrInventarioRisco` |
| Domínio | `Medicos/MedicosResponsaveisService`, `Pgr/PgrInventarioService`, `Pgr/PgrVigenteRepositorio`, `Pgr/RiscosCatalogoService`, `Regras/Condicoes/CondicaoExpostoRisco`, `Regras/Condicoes/CondicaoRisco`, `Regras/ComparadorCenariosVencimento`, `Colaboradores/SaneamentoCargosService` |
| Controllers | `SaudeOcupacional/MedicosResponsaveisController`, `SaudeOcupacional/PgrController` |
| Comando | `mybp:saude-sanear-cargos` (`SaudeSanearCargosCommand`) |
| Vue | `resources/js/components/saude-ocupacional/medicos/*`, `resources/js/components/saude-ocupacional/pgr/*` |

### Arquivos da Parte 3

| Camada | Caminho |
|---|---|
| Migrations | `2026_11_05_130000_add_codigo_esocial_to_exames_table.php`, `..._130001_create_exame_riscos_exames_table.php`, `..._130002_create_pcmsos_versoes_tables.php` |
| Models | `PcmsoVersao`, `PcmsoVersaoExame`, `ExameRiscoExame` (+ `Exame.codigo_esocial`, `Pcmso::Versoes`) |
| Domínio | `Pcmso/PcmsoService`, `Pcmso/ExamesCatalogoService`, `Pcmso/ExamesPrevistosPcmso`, `Pcmso/PcmsoPdfService` |
| Controller | `SaudeOcupacional/PcmsoVersoesController` (rotas `g/saude-ocupacional/pcmso/*`) |
| Ficha | `ControleExameController::examesPcmsoParaFicha` + `resources/views/pdf/controle-exames/ficha.blade.php` |
| PDF | `resources/views/pdf/saude-ocupacional/pcmso.blade.php` |
| Vue | `resources/js/components/saude-ocupacional/pcmso/*` |

## Testes

```bash
docker compose exec mybpdp php artisan test tests/Unit/Domain/SaudeOcupacional
```

Parte 2 (`tests/Unit/Domain/SaudeOcupacional/Parte2`): CRM normalizado e único por empresa, coordenador sem
sobreposição, clínica da empresa, alertas, edição de vínculos, isolamento; PGR (rascunho copia vigente, só um
rascunho, validação de cargo/risco da empresa, ausência x riscos, publicação arquiva anterior e dispara recálculo,
início antes da vigente recusado), exposição automática anual, módulo desligado, regra por risco específico,
prévia da publicação, simulador com riscos, saneamento de cargos. Esquema de teste compartilhado em
`CriaTabelasSaudeOcupacional`.

Parte 3 (`tests/Unit/Domain/SaudeOcupacional/Parte3/PcmsoTest.php`): publicação exige coordenador vigente no início,
nova versão copia e arquiva a anterior, um rascunho por programa, validação de exames (tipos, periodicidade, empresa),
sugestão pelo PGR sem duplicar, sugestão sem PGR recusada, exames previstos para a ficha (por cargo, tipo, vigência e
módulo), alertas, isolamento entre empresas e renderização do PDF.

Parte 4 (`tests/Unit/Domain/SaudeOcupacional/Parte4/ConformidadeTest.php`): situação, ordem, dias, cargo, último ASO,
motivo e "encaminhado" (só sem resultado); filtros de situação, busca e cargo; lote (pula aberto e de outra empresa,
trava contra segundo clique, recusa Demissional e clínica de outra empresa, job cria encaminhamentos no caminho do PCMSO);
exportação ("Não informado" e fórmula neutralizada); alertas por marco avisam uma vez; config de alertas.

Parte 5 (`tests/Unit/Domain/SaudeOcupacional/Parte5/AsoTest.php`): histórico em ordem com aguardando/realizado, CPF
mascarado, RH sem observações e com anexos restritos, perfil Saúde vê e fica auditado, isolamento entre empresas; emissão
grava examinador, coordenador, função, riscos do PGR e exames; recusa exame não realizado e médico de outra empresa;
não reemite depois de enviado para assinatura; PDF com os campos da NR-7.

Parte 6 (`tests/Unit/Domain/SaudeOcupacional/Parte6/DocumentosSstTest.php`): situação pela vigência (limites da
janela), envio e nova versão (situação da versão atual, totais, disco privado por empresa, hash), Painel e filtro de
vencendo, sigiloso invisível para o RH e auditado para o perfil Saúde, RH não envia sigiloso, isolamento no download,
arquivo disfarçado e vigência invertida recusados sem gravar nada, arquivar mantém arquivo, ASO assinado arquivado uma
vez no colaborador, PCMSO publicado arquivado com fim de vigência da versão anterior.

Parte 1: motor (idade no ciclo, menor prazo, regra não alonga, AND das condições, menores de 18),
condições e trava NR-7, integração (cálculo pelo service, módulo desligado, cargo vigente pela mudança de cargo,
histórico + auditoria + job retroativo, cargo de outra empresa recusado, exclusão, job em lote por colaborador,
prévia, simulador isolado por empresa).

Cobrem: contagens do Painel (vencido, vencendo, sem ASO, em dia), exclusão de demitidos/excluídos/outra empresa,
afastamentos (retorno ≥ 30 dias, INSS > 15 dias, motivo fora da resposta), cache e invalidação, perfis e campos
sensíveis, flag por empresa e middleware, migration de permissões (dados sensíveis nunca liberados).
