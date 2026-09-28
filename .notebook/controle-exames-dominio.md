# Controle de Exames — domínio

> Formulário dinâmico DB (Formulario→Setores→Alternativas) + respostas JSON; sem CRUD admin de tipos/forms

Entry: `app/Http/Controllers/ControleExameController.php`

## Entidades ativas

- `exame_funcionarios` — encaminhamento (JSON `respostas`, `formulario_id`, clínica, PCMSO/tipo)
- `examesesmts` — resultado SESMT/ASO (JSON `resultado`, anexos N-N)
- `empresa_exames` — clínicas (JSON `dados`, TenantTrait)
- `exame_tipos` / `pcmsos` / `exames` — catálogos (tipos; PCMSO; lista de exames complementares)
- Motor form: `formularios` → `setores_formularios` → `alternativa_formularios` → `resposta_alternativas`
- Encaminhamento grava snapshot em `exame_funcionarios.exames_catalogo` (JSON `{id,label}`)

## Form definition

- Busca por título `"Exames"` via `FormularioController::buscaFormulario`
- Render: `FormularioDefault.vue` (tipos: checkbox/select/text/textarea/number/float)
- Seed/scripts por tenant; **sem** UI CRUD de formulário/tipo de exame
- `controle-exames/Formulario.vue` parece morto (rota `TiposExamesTipoRiscos` inexistente)

## Storage filled data

- Encaminhamento: `exame_funcionarios.respostas` JSON keyed `alternativa_id_{id}`
- Resultado: `examesesmts.resultado` JSON (apto/pendências/aprovado/altura…)
- Não é EAV de respostas; opções do form sim em tabelas

## Reuse form-builder

- Mesmo motor: entrevistas/pareceres (`FormularioDefault`)
- Alternativa mais moderna: `RequisicaoVagaCustomCampo` (CRUD + `opcoes` JSON)

## Tenant

- ScopeEmpresa / TenantTrait em ExameFuncionario, Examesesmt, EmpresaExame, Formulario, Pcmso
- `exame_tipos` sem `empresa_id`

Updated: 2026-09-27
