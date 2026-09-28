# CIH visibilidade vs aprovação

Ver todas ≠ aprovar todas.

## Habilidades
- `admissao_cih_ver_todas` — listagem/export ampliada; **não** amplia aprovação
- `admissao_cih_privilegio_adm` — vê todas **e** override de aprovação gestor
- `privilegio_aprovar_por_gestor` — botão gestor; escopo = `gestor_id == auth`
- `privilegio_aprovar_por_rh` — sem mudança

## Onde
- `CihAcessoService::podeVerTodas()` / `podeAprovarComoGestor()` / `queryBaseComEscopo()`
- Listagem: `CihController::filtro` → `CihQueryBuilder`
- Aprovação gestor: `CihController::aprovar` (403 se fora do escopo)
- UI: `CIH.vue` → `podeAprovarComoGestor(item)`

## Escopo “respeito”
Só `gestor_id` (não o scope `vinculados` completo).

## POG Montisol (legado)
- `grupo_id` = **papel** id `113` (`PAPEL_MONTISOL_CIH_MATRIZ`)
- Sem `ver_todas`/`adm`: vê CIHs dos CCs ativos do CNPJ matriz `12557849000140` (12.557.849/0001-40), não só `vinculados`
- Commit histórico: “CIH SO PRA MONTISOL ajustar depois para todo mundo” (2024)
- Empresa típica: `63122` (não hardcoded no código)
- Preferível migrar esse papel para `admissao_cih_ver_todas` e remover o ramo
