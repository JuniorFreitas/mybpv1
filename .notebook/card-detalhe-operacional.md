# Card detalhe operacional (padrão MyBP)

- CSS global: `resources/sass/_mybp-card-detalhe.scss` (import em `app.scss`)
- Componentes: `MybpCardCampo`, `MybpFluxoAprovacao`, `MybpStatusBadge` em `resources/js/components/ui/`
- Doc: `docs/PADRAO_UX_CARD_DETALHE.md` · skill: `.cursor/skills/mybp-card-detalhe/SKILL.md`
- Referências migradas:
  - `SolicitacaoDemissao.vue`, `SolicitacaoFerias.vue`, `SolicitacaoAdmissao.vue`
  - `SolicitacaoValorExtra.vue` (Liderança de Pessoal)
  - `SolicitacaoMudaCargo.vue`
  - `SolicitacaoIntermitenteFixo.vue`
  - `SolicitacaoTransferencia.vue`
- Próximos candidatos: CIH, Requisição de Vagas — remover CSS scoped `fluxo-*` / `*-card-corpo` locais
