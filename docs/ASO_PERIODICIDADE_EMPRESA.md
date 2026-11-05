# Periodicidade de ASO por empresa e tipo

## Objetivo

Permitir que cada empresa configure a **validade do ASO por tipo de exame** (Admissional, Periódico, etc.), substituindo o vencimento fixo de +1 ano que gerava relatórios incorretos.

## Regras de negócio

### Cálculo do vencimento

| Unidade | Fórmula |
|---------|---------|
| `anos` | `data_realizacao + N anos − 1 dia` |
| `dias` | `data_realizacao + N dias` (exato) |

- Default sem configuração: **1 ano − 1 dia**
- Presets na UI: 1 ano, 2 anos, 3 anos + personalizado em dias (1–3650)
- Exemplo: realização `15/03/2024` + 1 ano → vencimento `14/03/2025`
- **Demissional** não possui vencimento (`data_vencimento = null`); não entra na config nem no job de recálculo

`ClienteConfig.vencimento_aso` continua sendo apenas a **janela de alerta** (30/60/90 dias) do relatório/e-mail — **não** é a periodicidade.

### Modos ao salvar alteração

| Modo | Comportamento |
|------|----------------|
| `futuro` | Só salva config; **não** roda job; vale para novos resultados / edição |
| `retroativo` | Salva config + enfileira job que recalcula todos os ASOs atuais (`examesesmts.atual = 1`) dos tipos alterados |
| `a_partir_de` | Exige data; job recalcula só ASOs atuais com `data_realizacao >= data_aplicacao` |

### Histórico e auditoria

Cada alteração com tipos mudados grava:

1. Linha em `empresas_exame_tipos_periodicidades_historicos` (quem, quando, modo, data, diff, se job foi despachado)
2. Evento Spatie `activity_log` com **`descricao` obrigatória** (causer + properties)

Sem mudança efetiva nos tipos, não grava histórico nem dispara job.

## Cadastro (UI)

- Menu **Cadastro → Periodicidade ASO**
- URL: `/g/cadastro/aso-periodicidade`
- Permissão: `cadastro_empresa_exame` (mesma de Exames)
- Lista de tipos com preset; Demissional exibe **Sem vencimento** (somente leitura)
- Ao **Salvar**, abre modal **Como aplicar a alteração?**
  - Opções: somente futuros / retroativo / a partir de uma data
  - Rodapé: apenas **Cancelar** e **Confirmar** (`mostrar-botao-fechar-no-rodape="false"`)
  - Fecha pelo X do cabeçalho ou Cancelar
- Seção **Histórico** na mesma tela (usuário, modo, diff, badge `job` se despachou)

## Estrutura

### Tabelas

| Tabela | Uso |
|--------|-----|
| `empresas_exame_tipos_periodicidades` | Config vigente (`empresa_id`, `exame_tipo_id`, `unidade`, `valor`) |
| `empresas_exame_tipos_periodicidades_historicos` | Histórico consultável na tela |

Migrations:

- `2026_11_04_200000_create_empresas_exame_tipos_periodicidades_table.php`
- `2026_11_04_200001_create_empresas_exame_tipos_periodicidades_historicos_table.php`

### Backend

| Peça | Caminho |
|------|---------|
| Service | `App\Services\ControleExames\AsoPeriodicidadeService` |
| Controller | `App\Http\Controllers\EmpresaExameTipoPeriodicidadeController` |
| Models | `EmpresaExameTipoPeriodicidade`, `EmpresaExameTipoPeriodicidadeHistorico` |
| Orquestrador | `JobRecalcularVencimentoAso` |
| Lote | `JobRecalcularVencimentoAsoLote` (cursor `after_id` + LIMIT ~300) |

Rotas (prefixo `g/cadastro`, middleware de permissão):

| Método | Rota | Ação |
|--------|------|------|
| GET | `aso-periodicidade` | Tela |
| GET | `aso-periodicidade/listar` | Config + tipos |
| GET | `aso-periodicidade/historico` | Histórico paginado |
| POST | `aso-periodicidade` | Salvar + modo |

Pontos que usam o service para calcular vencimento:

- `ControleExameController::salvaResultado` / `updateResultado`
- `AdmissaoAso` (create/update e `criarAtualizar`) — tipo Admissional / default
- Mutator/`Examesesmt` permite `data_vencimento` nulo (Demissional)

### Frontend

| Arquivo | Papel |
|---------|-------|
| `resources/js/components/cadastros/aso-periodicidade/AsoPeriodicidade.vue` | Tela |
| `resources/js/g/cadastros/aso-periodicidade/app.js` | Entry Mix |
| `resources/views/g/cadastros/aso-periodicidade/index.blade.php` | Blade |
| `webpack.mix.js` | Bundle `js/g/cadastros/aso-periodicidade.js` |

## Performance do recálculo

- Sem hidratar Eloquent em massa
- Sem carregar todos os IDs da empresa em memória
- Cadeia por cursor (`after_id` + `LIMIT`)
- Um UPDATE SQL por lote
- Lock de cache `aso:recalc:{empresa_id}` anti-duplicidade (TTL)
- Demissional excluído do recálculo
- Baixo uso de RAM/CPU no worker PHP

## Relatórios

`AsoVencimentoRelatorioService` continua lendo `examesesmts.data_vencimento` já calculada/corrigida. Não altera a fórmula do alerta. Ver também `.notebook/relatorio-vencimento-aso.md`.

## Testes

Arquivo: `tests/Unit/Services/ControleExames/AsoPeriodicidadeServiceTest.php`

Cobertura principal: default 1 ano − 1 dia, Demissional sem vencimento, presets anos/dias, modos futuro/retroativo/a_partir_de, activity_log com descrição, job em lote com cursor e lock.

Rodar (banco em memória via `phpunit.xml`):

```bash
docker compose exec mybpdp php artisan test --filter=AsoPeriodicidade
```

## Implantação / cliente novo

1. Rodar migrations.
2. Garantir permissão `cadastro_empresa_exame` no grupo do RH.
3. Em **Cadastro → Periodicidade ASO**, ajustar presets (Periódico costuma ser 1 ou 2 anos conforme PCMSO).
4. Se houver ASOs históricos com vencimento errado, salvar em modo **Retroativo** (ou **A partir de**).
5. Confirmar Horizon/fila processando `JobRecalcularVencimentoAso*`.

Detalhe de alerta (não periodicidade): `docs/CONFIGURACAO_NOVO_CLIENTE.md` → `vencimento_aso`.

## Manual do usuário

Capítulo **4.9 Periodicidade ASO** em `docs/manual-completo/secoes/04-cadastros.html` (PDF/RAG regenerados). Imagens: `cad-aso-periodicidade.jpg`, `cad-aso-periodicidade-modal.jpg`.

## Notebook

Resumo operacional: `.notebook/aso-periodicidade-empresa.md`
