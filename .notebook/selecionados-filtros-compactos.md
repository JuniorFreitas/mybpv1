# Selecionados — filtros compactos + features SGI

## Escopo
- Tela: `/g/curriculos/curriculos-selecionados`
- UI: `resources/views/g/curriculos/selecionados/index.blade.php`
- JS: `resources/js/g/curriculos/selecionados/app.js` → `public/js/g/selecionados/app.js`
- Backend: `CurriculosSelecionadosController` (raiz `FeedbackCurriculo` + `interesse=true`, paridade SGI — sem exigir `selecionado=sim`)

## Filtros
- `FiltroListagem` + `mybp-filtros-compactos`
- Primários: período (`DateRangeFilter`), Candidato/CPF unificado, Cargo, UF, Status, PCD, Exibir
- Mais filtros: Prova, Nota (se prova finalizada), Etapa (+ Stand by), Carta Oferta, Disp. sáb/dom
- Combos `sel-filtro-*`; Buscar no lugar de Atualizar
- Paginação: `:por-pagina="controle.dados.pages"`

## Cards / modal
- Lista `mybp-cards-lista` + `MybpStatusBadge` por etapa/status
- Campos: CPF, data seleção, vaga, prova, etapa, PCD, carta, disponibilidade
- Modal `mybp-modal-form` + seção Carta Oferta (resumo + link gerencial `/g/admissao/documentos/carta-oferta`)

## Backend
- **Regra de listagem (SGI):** só `interesse = true` — **não** exige `selecionado = sim` (standby com interesse também entra)
- Período: ISO `dataInicio`/`dataFim` + fallback BR; filtro em `created_at` (data seleção)
- Filtros UF/PCD/disponibilidade via `whereHas('Curriculo')`
- Carta: `Aguardando` | `Sim` | `Não` mapeados para status MyBP + `CartaOfertaAceite`
- Relação `FeedbackCurriculo::CartaOferta()`; payload `carta_resumo`, `data_selecao_br`, `curriculo_ctoken`

## Referência SGI
- Regra: `Curriculo::whereHas('FeedBack', interesse=true)` em `bpsesgi/.../CurriculosSelecionadosController@atualizar`
- UX: padrão Recrutamentos MyBP (filtros compactos + cards)
