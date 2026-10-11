# Exemplos — telas já migradas

Use estas telas como referência viva ao aplicar a skill `mybp-front-cardlist`.

## Por complexidade

### Básico (Buscar + Status combobox + cards simples)

| Tela | Rota | Filtros combobox | Arquivos |
|---|---|---|---|
| Áreas | `/g/cadastro/areas` | Status | `Areas.vue`, `AreaEtiquetasController.php` |
| Cargos (Vagas) | `/g/cadastro/vagas` | Status | `vagas/index.blade.php`, `vagas/app.js` |

**Padrão alvo**: Buscar em `<input>`, Status em combobox, cards com `mybp-card-secoes-row`.

### Médio (combobox CNPJ + filtros enum)

| Tela | Rota | Filtros combobox | Arquivos |
|---|---|---|---|
| Centro de Custo | `/g/cadastro/centrocusto` | Status, CNPJ | `CentroCusto.vue` |
| Benefícios | `/g/cadastro/beneficio` | Status, CNPJ | `Beneficio.vue` |

**Padrão**: combobox CNPJ quando há filial, `alert-info` contextual, Composition API no dropdown.

### Avançado (vários comboboxes + métricas nos cards)

| Tela | Rota | Filtros combobox | Arquivos |
|---|---|---|---|
| Projetos | `/g/cadastro/projetos` | Disponibilidade, Vagas abertas | `projeto/index.blade.php`, `projeto/app.js` |
| Vagas Abertas | `/g/cadastro/vagas-abertas` | Ativo site/sistema, Cargo, Cidade, Provas, Projeto | `vagas_abertas/index.blade.php`, `vagas_abertas/app.js` |

**Padrão**: todos os filtros (exceto Buscar) via `ComboboxAutoComplete`, `fecharOutrosComboboxes`, `@select="onSelectFiltro"`.

## Mapeamento tabela → card (exemplos reais)

### Áreas

| Coluna antiga | Bloco do card |
|---|---|
| Nome | Título do header |
| Gestor | Destaque `--primary` |
| Centro de Custo | Destaque `--info` |
| Ativo | `bt-ativo` no header |
| Opções | Dropdown Editar |

### Cargos

| Coluna antiga | Bloco do card |
|---|---|
| Nome | Título |
| CBO | Destaque `--primary` (ou "Não vinculado") |
| Treinamentos | Destaque `--info` na mesma linha (`mybp-card-secoes-row`) |

### Projetos

| Coluna antiga | Bloco do card |
|---|---|
| Nome | Título + data atualização |
| Capacidade / Distribuído / Disponível | `mybp-projeto-resumo` |
| Ocupação | Barra percentual |
| Vagas vinculadas | Destaque ou detalhe |

## Campos obrigatórios por tela (referência)

| Tela | Obrigatórios no modal |
|---|---|
| Áreas | Nome |
| Cargos | Título do cargo |
| Projetos | Nome, Capacidade total |
| Vagas Abertas | Cargo, Título, Cidade |

Sempre confirmar em `store()` / `update()` do controller antes de marcar `*`.

## Comando pós-migração

```bash
npm run dev
```
