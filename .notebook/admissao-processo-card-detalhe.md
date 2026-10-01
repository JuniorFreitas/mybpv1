# Admissão > Processo — card detalhe

## Padrão
- Lista em `mybp-cards-lista` / `mybp-card` (Blade: `resources/views/g/admissao/processo/index.blade.php`)
- Corpo: `mybp-card-corpo` + seções Dados / Encaminhamentos / Datas
- Campos: `MybpCardCampo`; badge: `MybpStatusBadge` (sem fluxo de aprovação nesta tela)
- Registro em `resources/js/g/admissao/processo/app.js`

## Status badge
- Sem admissão → `aberto` / "Em processo"
- `ADMITIDO` → `aprovado`
- `DEMITIDO` → `reprovado`
- Demais com admissão → `gestor`

## Colunas
- Encaminhamentos só aparece se alguma coluna `pcd|enc_*|resp_encaminhamento|cracha|foto_3x4` estiver checked (`colunaVisivel` / `temSecaoEncaminhamentos`)

## Densidade
- Classes `mybp-cards-lista--denso` + `mybp-card--denso`
- Uma seção principal (dados + datas) + Encaminhamentos
- Grid 4 colunas no desktop; padding/tipografia reduzidos no SCSS global
