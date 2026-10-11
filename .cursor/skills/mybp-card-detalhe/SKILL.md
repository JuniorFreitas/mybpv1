---
name: mybp-card-detalhe
description: Aplica o padrão MyBP de card detalhe em listagens operacionais (seções em grid, badge de status, borda por status, fluxo de aprovação via MybpFluxoAprovacao). Use when the user asks for card detalhe, fluxo de aprovação padronizado, mybp-card-corpo, PADRAO_UX_CARD_DETALHE, ou migrar cards de Demissão/CIH/Férias/Requisição.
---

# MyBP — Card detalhe (operacional)

Padroniza o **corpo** dos cards em listagens com aprovação/fluxo. Complementa `mybp-front-cardlist` (header + filtros), não o substitui.

## Antes de editar

1. Ler `docs/PADRAO_UX_CARD_DETALHE.md`.
2. Confirmar CSS: `resources/sass/_mybp-card-detalhe.scss` (importado em `app.scss`).
3. Componentes: `MybpCardCampo.vue`, `MybpFluxoAprovacao.vue`, `MybpStatusBadge.vue` em `resources/js/components/ui/`.
4. Referência: `SolicitacaoDemissao.vue`.

## Diferença dos padrões

| | Cadastro (`mybp-front-cardlist`) | Card detalhe (esta skill) |
|---|---|---|
| Uso | CRUD leve | Movimentação, CIH, aprovações |
| Corpo | `mybp-card-details-row` / destaque | `mybp-card-corpo` + seções grid |
| Fluxo | Não | `MybpFluxoAprovacao` |
| Status | Badge simples opcional | `MybpStatusBadge` + borda esquerda |

## Workflow

### 1. Imports

```js
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
```

### 2. Header

Manter `mybp-card` + `mybp-badge-id` + `mybp-card-titulo` + dropdown. Trocar badge local por:

```vue
<mybp-status-badge :variante="chaveStatus(item)" :texto="textoStatus(item)" />
```

### 3. Corpo

```vue
<div class="mybp-card-corpo" :class="`mybp-card-corpo--${chaveStatus(item)}`">
  <section class="mybp-card-secao">
    <div class="mybp-card-row">
      <mybp-card-campo icon="fas fa-…" label="…" :valor="…" />
    </div>
  </section>
  <section class="mybp-card-secao mybp-card-secao--fluxo">
    <div class="mybp-card-secao__titulo">
      <i class="fas fa-project-diagram" aria-hidden="true"></i> Fluxo de aprovação
    </div>
    <mybp-fluxo-aprovacao :steps="fluxoSteps(item)" />
  </section>
</div>
```

### 4. Remover CSS scoped duplicado

Apagar `.fluxo-*`, `.cih-card-*`, `.demissao-card-*`, `.card-aprovacao-row` do `<style scoped>` da tela — o global cobre.

### 5. Helper `fluxoSteps(item)`

Mapear status do domínio → `aprovado | reprovado | aguardando | pendente | cancelado`. Usar `oculto: true` para etapas condicionais (aprovação extra).

## Checklist

- [ ] Sem CSS de fluxo/seção duplicado no scoped
- [ ] Classes `mybp-card-corpo*` / `mybp-card-secao*` / `mybp-card-campo*`
- [ ] Fluxo só via `MybpFluxoAprovacao`
- [ ] Badge via `MybpStatusBadge`
- [ ] Primary `#174257` via CSS global (não hardcode novo)
