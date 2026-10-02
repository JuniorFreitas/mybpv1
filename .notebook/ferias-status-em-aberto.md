# Férias — status Em aberto

Tags: flow | ferias, filtro, status

## Regra
- Filtro `aberto` em `AppliesApprovalFlowStatusFilter` = pendente na etapa atual (gestor, extra ou RH), sem reprovação e sem RH final. Mesma ideia de `reprovado` (qualquer etapa).
- Card em `SolicitacaoFerias.vue` `etapaAtualLista()`: texto é a etapa atual (`Pendente Gestor`, `Pendente Extra`, `Pendente RH`, `Aprovado RH`, `Reprovado Gestor/Extra/RH`).
- Filtro de férias não oferece `aprovado_gestor` nem `aprovado_extra`. Essas aprovações avançam o fluxo; o status visível é a etapa seguinte. `FeriasPrevistaFilterApplier` devolve vazio se a API ainda mandar esses valores.

## Não confundir
Badge antigo “Em aberto” era só gestor ainda não aprovado. O filtro `aberto` também trazia quem já foi aprovado pelo gestor e segue pendente na etapa seguinte — por isso a lista mostrava “Aprovado Gestor” dentro de Em aberto.
