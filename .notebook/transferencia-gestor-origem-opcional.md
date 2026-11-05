# Transferência — gestor origem opcional

> CC origem sem gestor não bloqueia; CC destino sem gestor bloqueia.

Entry: `TransferenciaPrevistaFluxoAprovacaoService::validarCentrosCusto()` / `resolverGestorOrigem()`
UI: `SolicitacaoTransferencia.vue` — alertas ao selecionar CC

## Regras
- Origem sem gestor ativo (ausente/inativo) → `gestor_id = null`, etapa origem dispensada, fluxo segue p/ destino; aviso pede cadastrar/reativar se necessário
- Origem inativa → permitida; label ` - (-- Inativo --)`; não gera 422
- Destino inativo → fora da lista e bloqueia salvar
- Destino sem gestor ativo (ausente/inativo) → bloqueia salvar (front + HTTP 422). Mensagem: cadastrar ou reativar o gestor com o Administrador.
- Responsável efetivo = `getGestorResponsavel()` = principal ativo **ou** substituto ativo (principal inativo não bloqueia se houver substituto).
- RH (`privilegio_gestao_rh` / `privilegio_aprovar_por_rh` / `privilegio_aprovar_rh`) pode aprovar etapa origem e destino
- Se o RH não for o gestor/substituto do CC, grava `user_aprovacao_id` (ou destino) + obs `Registrado por RH: {nome}`
- Mesmo gestor origem/destino → etapa destino **continua obrigatória** (aprovação independente)
- Notificação na criação sem origem: `criacao_gestor_destino` (não `criacao_gestor_origem`)
- RH aprovado efetiva `admissao.centro_custo_id` (+ filial se houver) via `resposta_rh`
- Histórico LOGS: `de {CC origem} para {CC destino}` (`mensagemHistoricoAprovacaoRh()`)
- Aviso de origem sem gestor só no cadastro/edição (`emFluxoAprovacao`); extra usa "por {nome}", não "pela"

## Fluxo
`aplicarFluxoGestores()` → `etapaAtual()` pula origem se `!gestor_id` → Extra/RH via `origemEtapaConcluida()`
Após RH: `aprovarRH()` → `decisaoAprovacaoRh()` → `efetivarCentroCustoAdmissao()` + `JobNotificacaoRecursiva` (`aprovado_final`)

Gotcha: `status_aprovacao` fica `null` quando origem é dispensada — não tratar isso como “ainda na etapa origem”.
Gotcha: filtro Status “Em aberto” em `TransferenciaPrevistaFilterApplier::applyCampoStatus()` deve excluir reprova em destino/único/extra/RH (não só `status_aprovacao IS NULL`). “Aprovado” = `resposta_rh=aprovado`; “Reprovado” = qualquer etapa.
Gotcha: filtro granular de fluxo (`pendente|aprovado|reprovado` por etapa: gestor_origem/destino/unico, extra, rh) em `TransferenciaPrevistaFilterApplier::applyCampoStatus()` — UI em `SolicitacaoTransferencia.vue` `opcoesStatus`. `reprovado` = qualquer etapa.
Gotcha: tela envia `resposta_rh`; `status_aprovacao_rh` é legado de outros módulos. Efetivação deve ler `resposta_rh`.
Gotcha: `validarFormularioVisivel()` deve ignorar `:disabled`/`[readonly]` — blur em status origem vazio bloqueava RH.
Gotcha: cadastro de CC mostra gestor inativo com sufixo ` - (-- Inativo --)` (modal + listagem). Transferência só aceita ativo; se principal inativo e há substituto ativo → usar substituto (`getGestorResponsavel`). Ex.: CC 696 REGIONAL 057 (JOAO inativo → IGOR).

Docs: `docs/TRANSFERENCIA_PREVISTA_APROVACAO_GESTORES_CC.md` (RN10–RN12)

Updated: 2026-11-05
