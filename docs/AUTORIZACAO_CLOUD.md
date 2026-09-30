# Autorização Cloud (MyBP)

O Cloud usa **dois níveis** de permissão, paralelos ao registry MyBP (`habilidades` / Gates). A **unificação lógica** é o [`CloudAuthorizationService`](../app/Services/Cloud/CloudAuthorizationService.php) — as tabelas `habilidades` e `habilidade_clouds` **permanecem separadas** de propósito (domínios distintos).

## Nível 1 — Módulo (MyBP)

Entrada no produto Cloud:

| Gate | Uso |
|------|-----|
| `cloud` | Navegar, itens, anexos |
| `cloud_cadastro` | Cadastro de clouds |
| `cloud_configuracoes` | Grupos e permissões de cloud |

Definidas em papéis MyBP (`papeis_habilidades`), middleware `can:` em [`routes/web.php`](../routes/web.php).

## Nível 2 — Capacidades por grupo Cloud

Usuário pode ter:

1. `users.grupo_cloud_id` (grupo principal)
2. N grupos em `user_grupo_cloud`

Capacidades efetivas = **união** das `habilidade_clouds` de todos esses grupos.

| Nome (banco/UI) | Slug | Ação típica |
|-----------------|------|-------------|
| Download | download | Baixar arquivo |
| Visualizar | visualizar | Ver anexo |
| Detalhes | detalhes | Metadados |
| Editar | editar | Editar item |
| Mover | mover | Mover pasta/arquivo |
| Deletar | deletar | Excluir item/anexo |
| Atualizar | atualizar | Atualizar versão |
| Revisar | revisar | Fluxo revisão |
| Aprovar | aprovar | Fluxo aprovação |

Catálogo: [`CloudCapabilityCatalog`](../app/Authorization/Cloud/CloudCapabilityCatalog.php).  
UI: [`Cloud.vue`](../resources/js/components/Cloud.vue) (`temHabilidade('Visualizar')`).

## Nível 3 — Item (pasta/arquivo)

`permissoes_itens_clouds` liga item ↔ grupos.  
`TemPermissao` / `userCanAccessItem` aceitam **qualquer** grupo do usuário (`idsGruposCloud()`).

## Policy

[`ItensCloudPolicy`](../app/Policies/ItensCloudPolicy.php): `view`, `update`, `delete`, `move`, `review`, `approve`.  
Usada em `ItensCloudController` via `$this->authorize(...)`.

## Service central

- `userHasCloudModule` / `userGrupoCloudIds` / `userCloudCapabilityNames`
- `userCanCloudAction` / `userCanAccessItem`
- `authorizeCloudFile` (+ capability)

## Limitações

- Anexos **fora** de `ItensCloud` (ex.: FAT) **não** usam `authorizeCloudFile` — ver [`.notebook/fat-treinamento-cloud-404.md`](../.notebook/fat-treinamento-cloud-404.md).
- Spatie Permission **não** adotado; Gates + Policies locais bastam.

Ver também: [`AUTORIZACAO_HABILIDADES.md`](AUTORIZACAO_HABILIDADES.md).
