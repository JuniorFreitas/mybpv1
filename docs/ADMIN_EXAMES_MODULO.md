# Administração de Exames (hub completo)

## Objetivo

Permitir que o cliente configure tipos de exame, formulários de encaminhamento/resultado SESMT e catálogo clínico sem scripts de banco, preservando respostas históricas em JSON (`alternativa_id_*`).

## Acesso

- Menu: **Cadastros → Exames** → `/g/cadastro/exames-admin`
- Permissão base: `cadastro_empresa_exame` (habilidades granulares `cadastro_exame_*` no seeder para uso futuro)

## Abas do hub

| Aba | Função |
|-----|--------|
| Clínicas | CRUD `EmpresaExame` (já existente) |
| Tipos de exame | CRUD tenant-aware (`exame_tipos` com `empresa_id`) |
| Formulários | Builder sobre motor `Formulario` → setores → alternativas |
| Resultado SESMT | Mesmo builder, contexto resultado + `chave_canonica` |
| Lista de exames | CRUD tabela `exames` — selecionáveis no encaminhamento (Controle de Exames / Pré-admissão) e impressos na ficha PDF |

## Modelo

- Tipos globais (`empresa_id` null) do seed: tenant **não edita**; ao salvar, clona para a empresa.
- `formulario_encaminhamento_id` / `formulario_resultado_id` no tipo.
- Fallback operacional: título `Exames` / `Resultado SESMT` via `ExameFormularioResolver`.
- Remoção de campo: soft (desvincula setor + `ativo=false`), IDs preservados.

## Services

- `App\Domain\Exames\Services\ExameTipoAdminService`
- `App\Domain\Exames\Services\ExameFormularioBuilderService`
- `App\Domain\Exames\Services\ExameFormularioResolver`
- `App\Domain\Exames\Services\ExameResultadoCompatService`
- `App\Domain\Exames\Services\ExameFormularioPayloadNormalizer`
- `App\Domain\Exames\Services\ExameCatalogoService`

## Seed resultado SESMT

```bash
php artisan exames:seed-formularios-admin
php artisan exames:seed-formularios-admin --empresa_id=123
php artisan exames:seed-formularios-admin --dry-run
```

## Operacional

`ControleExames` carrega formulário por tipo (`formularios-exame/por-tipo/{id}`). Resultado SESMT usa formulário dinâmico quando não há JSON legado; compat preenche `result`/`aprovado`/etc. para relatórios e WhatsApp.

## Migration

`2026_09_28_000001_enhance_exame_tipos_admin.php` — colunas em `exame_tipos` + `ativo`/`chave_canonica` em `alternativa_formularios`.
