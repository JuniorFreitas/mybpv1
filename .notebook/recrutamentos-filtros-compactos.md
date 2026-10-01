# Recrutamentos — filtros compactos

## Escopo
- Tela: `/g/curriculos/recrutamentos`
- UI: `resources/views/g/curriculos/recrutamento/index.blade.php`
- JS: `resources/js/g/curriculos/recrutamento/app.js` → `public/js/g/recrutamento/app.js`
- Backend: `RecrutamentoController` filtro período

## Filtros
- `FiltroListagem` + `mybp-filtros-compactos`
- Primários: período (`DateRangeFilter`), Candidato/CPF unificado, Cargo, UF, Lido, PCD, Exibir
- Sem painel “Mais filtros” (todos visíveis)
- Combos `rec-filtro-*` (IDs estáticos)
- Export: `btn-outline-primary`; Buscar no lugar de Atualizar
- Paginacao: `:por-pagina="controle.dados.pages"`

## Período
- Front: `dataInicio`/`dataFim` ISO + sync `periodo` BR (`dd/mm/yyyy até dd/mm/yyyy`)
- Backend: preferir ISO; fallback `periodo` BR
