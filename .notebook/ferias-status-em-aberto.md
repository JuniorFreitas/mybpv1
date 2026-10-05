# Movimentação — status da etapa atual

Tags: flow | ferias, demissao, admissao, filtro, status

## Regra
- Filtro `aberto` em `AppliesApprovalFlowStatusFilter` = pendente na etapa atual (gestor, extra ou RH), sem reprovação e sem RH final. Mesma ideia de `reprovado` (qualquer etapa).
- Card das abas Demissão, Admissão, Férias, Valor Extra, Mudança de Cargo e Intermitente usa `etapaAtualFluxoAprovacao()` em `opcoesStatusFluxoAprovacao.js`. Texto é a etapa atual (`Pendente Gestor`, `Pendente Extra`, `Pendente RH`, `Aprovado RH`, `Reprovado Gestor/Extra/RH`). Campo do gestor: `status_aprovacao` (Demissão, Admissão, Valor Extra, Intermitente) ou `status_aprovacao_gestor` (Férias, Mudança de Cargo).
- Essas abas usam `opcoesStatusFluxoAtual()` e não oferecem `aprovado_gestor` nem `aprovado_extra`. `bloquearStatusAprovacaoIntermediaria()` devolve vazio se a API ainda mandar esses valores. Requisição de Vagas e CIH continuam com o filtro da coluna.
- Transferência: o card segue a etapa atual (origem, destino, único, extra, RH). O combo não oferece `aprovado_gestor_origem`, `aprovado_gestor_destino`, `aprovado_gestor_unico` nem `aprovado_extra`; `TransferenciaPrevistaFilterApplier` devolve vazio para esses valores.

## Não confundir
Badge antigo “Em aberto” era só gestor ainda não aprovado. O filtro `aberto` também trazia quem já foi aprovado pelo gestor e segue pendente na etapa seguinte — por isso a lista mostrava “Aprovado Gestor” dentro de Em aberto.
