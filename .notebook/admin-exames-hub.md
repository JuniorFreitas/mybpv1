# Admin exames (hub)

**Tags:** flow | exames, formulario, admin

Hub Cadastros → Exames: clínicas + tipos tenant-aware + builder Formulario/Setores/Alternativas + lista de exames + resultado SESMT.

## Lista de exames → encaminhamento

- Cadastro: aba **Lista de exames** (`exames`, opcional `exame_tipo_id`)
- Operacional: ao escolher tipo no Controle de Exames / Pré-admissão, carrega `GET cadastro/exame-catalogo/ativos?exame_tipo_id=`
- Persistência: JSON `exame_funcionarios.exames_catalogo` = `[{id, label}, …]` (snapshot)
- PDF ficha: bloco **Exames solicitados**
- Service: `ExameCatalogoService::montarSnapshot()` / `listarAtivos()`

Respostas históricas do formulário: `alternativa_id_*`. Resolver fallback título `Exames` / `Resultado SESMT`. Command: `exames:seed-formularios-admin`.
