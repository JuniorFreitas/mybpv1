# Transferência — gestor origem opcional

> CC origem sem gestor não bloqueia; CC destino sem gestor bloqueia.

Entry: `TransferenciaPrevistaFluxoAprovacaoService::validarCentrosCusto()` / `resolverGestorOrigem()`
UI: `SolicitacaoTransferencia.vue` — alertas ao selecionar CC

## Regras
- Origem sem gestor → `gestor_id = null`, etapa origem dispensada, fluxo segue p/ destino
- Origem inativa → permitida; label ` - (-- Inativo --)`; não gera 422
- Destino inativo → fora da lista e bloqueia salvar
- Destino sem gestor → bloqueia salvar (front + HTTP 422). Mensagem: cadastrar o gestor e falar com o Administrador.
- RH (`privilegio_gestao_rh` / `privilegio_aprovar_por_rh` / `privilegio_aprovar_rh`) pode aprovar etapa origem e destino
- Se o RH não for o gestor/substituto do CC, grava `user_aprovacao_id` (ou destino) + obs `Registrado por RH: {nome}`
- Mesmo gestor origem/destino continua dispensando etapa destino
- Notificação na criação sem origem: `criacao_gestor_destino` (não `criacao_gestor_origem`)
- RH aprovado efetiva `admissao.centro_custo_id` (+ filial se houver) via `resposta_rh`
- Histórico LOGS: `de {CC origem} para {CC destino}` (`mensagemHistoricoAprovacaoRh()`)
- Aviso de origem sem gestor só no cadastro/edição (`emFluxoAprovacao`); extra usa "por {nome}", não "pela"

## Fluxo
`aplicarFluxoGestores()` → `etapaAtual()` pula origem se `!gestor_id` → Extra/RH via `origemEtapaConcluida()`
Após RH: `aprovarRH()` → `decisaoAprovacaoRh()` → `efetivarCentroCustoAdmissao()` + `JobNotificacaoRecursiva` (`aprovado_final`)

Gotcha: `status_aprovacao` fica `null` quando origem é dispensada — não tratar isso como “ainda na etapa origem”.
Gotcha: tela envia `resposta_rh`; `status_aprovacao_rh` é legado de outros módulos. Efetivação deve ler `resposta_rh`.
Gotcha: `validarFormularioVisivel()` deve ignorar `:disabled`/`[readonly]` — blur em status origem vazio bloqueava RH.

Docs: `docs/TRANSFERENCIA_PREVISTA_APROVACAO_GESTORES_CC.md` (RN10–RN12)

Updated: 2026-08-31
