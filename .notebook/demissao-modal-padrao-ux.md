# Demissão — modal e cards no padrão UX MyBP

- Arquivo: `resources/js/components/planejamento/movimentacao/SolicitacaoDemissao.vue`
- Modal: fieldset CIH + `mybp-filtros-compactos` + combobox; CC opcional com confirm
- **Cards listagem** usam o padrão compartilhado **Card detalhe**:
  - Doc: `docs/PADRAO_UX_CARD_DETALHE.md`
  - Skill: `.cursor/skills/mybp-card-detalhe/SKILL.md`
  - CSS: `resources/sass/_mybp-card-detalhe.scss`
  - Componentes: `MybpCardCampo`, `MybpFluxoAprovacao`, `MybpStatusBadge`
  - Seções: Data/Aviso/Cargo → Lotação/CC/Gestor → Fluxo de aprovação
- API `filtro`: `gestor_nome` + `lotacao` via `LotacaoLabelResolver`
