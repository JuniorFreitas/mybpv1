# Férias — status Em aberto

Tags: flow | ferias, filtro, status

## Regra
- Filtro `aberto` em `AppliesApprovalFlowStatusFilter` = pendente na etapa atual (gestor, extra ou RH), sem reprovação e sem RH final. Mesma ideia de `reprovado` (qualquer etapa).
- Card em `SolicitacaoFerias.vue` `etapaAtualLista()`: texto segue a etapa (`Pendente Gestor` / `Pendente Extra` / `Pendente RH`), não “Em aberto” nem o último “Aprovado”.

## Não confundir
Badge antigo “Em aberto” era só gestor ainda não aprovado. O filtro `aberto` também trazia quem já foi aprovado pelo gestor e segue pendente na etapa seguinte — por isso a lista mostrava “Aprovado Gestor” dentro de Em aberto.
