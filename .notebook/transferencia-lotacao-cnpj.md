# transferencia-lotacao-cnpj

> Tags: flow | transferencia, lotacao, cnpj

## Summary
Modal de Transferência segue Lotação (CNPJ) → CC filtrado (`lista_ccs`), origem e destino separados. Listagem exibe `lotacao_origem` e `lotacao`/`lotacao_destino`.

## Key refs
- `SolicitacaoTransferencia.vue` — `formLotacaoOrigemCnpj` / `formLotacaoDestinoCnpj` + `listaCcPorLotacao()`
- `TransferenciaPrevistaController::atualizar` — atributos `lotacao_origem` / `lotacao`
- Padrão: Admissão/Intermitente (`formLotacaoCnpj` + `lista_ccs`)

## Gotchas
- Transferência grava só `centro_custos.id` (não `filial_id`); valor do combo = `item.id`.
- Origem desabilitada quando colaborador já tem CC; lotação origem é resolvida automaticamente.
