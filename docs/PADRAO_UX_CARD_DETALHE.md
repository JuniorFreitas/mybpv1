# Padrão UX/UI — Card detalhe (listagens operacionais)

Guia reutilizável para cards de listagem com **seções em grid**, **badge de status**, **borda por status** e **fluxo de aprovação** — o mesmo padrão de Demissão / CIH / Férias / Requisição de vagas.

## Quando usar

- Listagens operacionais (movimentação, apontamentos, aprovações) com muitos campos por item
- Precisa mostrar fluxo Solicitante → Gestor → (Extra) → RH
- Quer visual unificado com primary (`#174257`) sem CSS scoped por tela

**Não** substitui o card simples de cadastro (`mybp-front-cardlist` / `mybp-card-details-row`). Complementa: header `mybp-card` + corpo detalhe.

## Arquivos do padrão

| Recurso | Caminho |
|---|---|
| Estilos globais | `resources/sass/_mybp-card-detalhe.scss` |
| Campo label/valor | `resources/js/components/ui/MybpCardCampo.vue` |
| Fluxo de aprovação | `resources/js/components/ui/MybpFluxoAprovacao.vue` |
| Badge de status | `resources/js/components/ui/MybpStatusBadge.vue` |
| Referência viva | `SolicitacaoDemissao.vue` |

## Estrutura mínima

```vue
<div class="mybp-card">
    <div class="mybp-card-header-row">
        <div class="mybp-card-left">
            <span class="mybp-badge-id">#{{ item.id }}</span>
            <div class="mybp-card-titulo">
                <strong>{{ item.nome }}</strong>
            </div>
        </div>
        <div class="mybp-card-right">
            <mybp-status-badge :variante="chaveStatus(item)" :texto="textoStatus(item)" />
            <!-- dropdown ações -->
        </div>
    </div>

    <div class="mybp-card-corpo" :class="`mybp-card-corpo--${chaveStatus(item)}`">
        <section class="mybp-card-secao">
            <div class="mybp-card-row">
                <mybp-card-campo icon="fas fa-calendar" label="Data" :valor="item.data" forte />
                <mybp-card-campo icon="fas fa-tag" label="Tipo" :valor="item.tipo" />
                <mybp-card-campo icon="fas fa-briefcase" label="Cargo" :valor="item.cargo" />
            </div>
        </section>

        <section class="mybp-card-secao mybp-card-secao--fluxo">
            <div class="mybp-card-secao__titulo">
                <i class="fas fa-project-diagram" aria-hidden="true"></i> Fluxo de aprovação
            </div>
            <mybp-fluxo-aprovacao :steps="fluxoSteps(item)" />
        </section>
    </div>
</div>
```

## Classes CSS

| Classe | Uso |
|---|---|
| `mybp-card-corpo` + `--aberto\|gestor\|extra\|rh\|aprovado\|reprovado\|neutro` | Corpo com borda esquerda por status |
| `mybp-card-secao` + `--fluxo\|--meta\|--acao\|--historico` | Bloco interno |
| `mybp-card-secao__titulo` | Título uppercase da seção |
| `mybp-card-row` + `--2\|--4` | Grid 3 cols (default) / 2 / 4 |
| `mybp-card-campo` + `__label` / `__valor` / `--forte` | Campo |
| `mybp-status-badge` + variantes | Pill de status no header |
| `mybp-fluxo*` | Usado pelo componente de fluxo (não duplicar markup) |

## Fluxo — contrato `steps`

```js
[
  { key: 'solicitante', label: 'Solicitante', status: 'aprovado', nome: '...', data: '...' },
  { key: 'gestor', label: 'Gestor', status: 'aguardando' },
  { key: 'extra', label: 'Diretoria', status: 'pendente', oculto: !temExtra },
  { key: 'rh', label: 'RH', status: 'cancelado', statusTexto: 'Cancelada por reprovação' }
]
```

| `status` | Ícone | Texto padrão |
|---|---|---|
| `aprovado` | check verde | nome |
| `reprovado` | X vermelho | nome |
| `aguardando` | relógio | Aguardando |
| `pendente` | círculo cinza | Pendente |
| `cancelado` | ban | Cancelada por reprovação |

## Imports

```js
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'

components: { MybpCardCampo, MybpFluxoAprovacao, MybpStatusBadge }
```

## Checklist

- [ ] Header usa `mybp-card` / `mybp-badge-id` / `mybp-btn-acoes-compact`
- [ ] Corpo usa `mybp-card-corpo` (sem CSS scoped de borda/seção)
- [ ] Campos via `MybpCardCampo` ou classes `mybp-card-campo*`
- [ ] Fluxo via `MybpFluxoAprovacao` (sem markup `fluxo-step` local)
- [ ] Variantes de status alinhadas ao negócio (aberto/gestor/rh/reprovado…)
- [ ] Sem duplicar `.fluxo-*` / `.cih-card-*` / `.demissao-card-*` no scoped da tela
