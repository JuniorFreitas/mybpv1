# Filtros compactos operacionais

**Type:** pattern  
**Tags:** filtro, ux, frontend, carteira, combobox, daterange  
**Updated:** 2026-09-27

## Summary

Padrão reutilizável de filtros densos (carteira/treinamentos) com `FiltroListagem` + `mybp-filtros-compactos`.

Telas: carteira etiquetas, CIH, mobilização, admissão em processo, movimentações.

## Source of truth

- Doc: `docs/PADRAO_UX_FILTROS_COMPACTOS.md`
- Skill: `.cursor/skills/mybp-filtros-compactos/SKILL.md`
- CSS: `resources/sass/_mybp-filtros-compactos.scss`
- DateRange: `resources/js/components/DateRangeFilter.vue`
- Referência: `resources/js/components/treinamentos-carteira-etiquetas/TreinamentosCarteiraEtiquetas.vue`

## Key rules

- Grade `col-md-4`; avançados no shell animado (`mybp-filtros-avancados-shell` + `is-open`)
- `DateRangeFilter`: commit só no blur; tela escuta `@change`; v-model ISO; converter para BR se a API exigir
- Ações: Buscar + Mais filtros (`mybp-btn-mais-filtros`) + ações da tela; sem Atualizar duplicado
- Limpar seleção ao lado de Selecionar todos (`v-if` seleção)
- Colaborador/CPF unificado; Situação unifica admitido/demitido

## Relatórios já padronizados

- `relatorios/vencimentoasos/VencimentoAsos.vue`
- `relatorios/treinamento/index.vue`
- `relatorios/nps/NpsRelatorio.vue`
- `relatorios/AvaliacaoExperiencia.vue`
- `relatorios/ferias/index.vue`
- `relatorios/vencimentoferias/index.vue`
- `relatorios/aniversariantes/Aniversariantes.vue`
- `relatorios/medidasadministrativas/MedidasAdministrativas.vue`
- `relatorios/controleusuarios/ControleUsuarios.vue`
- `relatorios/centrodecusto/CentroCusto.vue` (abas Lista/Gráficos + CNPJ + multi CC)
- `relatorios/efetivo/Efetivo.vue` (abas Lista/Gráficos + CNPJ)
- `relatorios/medidasadministrativas/MedidasAdministrativas.vue` (abas Lista/Gráficos + CNPJ + multi CC)
