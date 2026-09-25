# Weekly Report (kanban)

## Flow
- Entrada: `resources/views/g/weekly-report/index.blade.php` passa `empresa_id` (cliente_id) para Vue.
- API nested: `weekly-report/{empresa}/quadros/.../listas/.../tarefas/...`
- Tenant guard: `app/Http/Controllers/Concerns/GuardsWeeklyReportTenant.php`
- Scope: `Quadro` usa `ScopeEmpresa` em `empresa_id`.
- Realtime: PresenceChannels `weekly-report.*.{empresaId}` em `routes/channels.php` (int, não User).
- Frontend: `resources/js/components/Weekly-report.vue` + `resources/js/components/weekly-report/*`

## Gotchas
- `empresa_id` = `cliente_id` (não é `users.id`).
- Permissões de lista: `weekly_report_quadro_lista_*` (sem `_quadro_` extra no meio).
- AutoComplete prepende `URL_ADMIN`; usar path relativo em `caminho`.
- Anexos só em rotas nested (rotas soltas sem tenant removidas).
- TaskModal: NÃO emitir `updated` de forma que o pai reatribua `tarefaAtiva` (loop de `loadShow`). Usar `loadedId` + `Object.assign` só nas listas.
- Checklist: formulário inline (sem `window.prompt` — bloqueado em browsers embutidos).
- Excluir checklist: `@mousedown.prevent` + remoção otimista; Echo NÃO faz `reloadBoardSoft` (isso “revivia” a checklist no modal). Sync via `applyChecklistRealtime` / `patchTarefaChecklists`.
- Item add/remove: otimista; `applyChecklistItemRealtime` usa payload real (`itens`/`item`/`item_id`) — sem `reloadBoardSoft` (era a lentidão).
- Backend destroy: apaga itens antes da checklist; evento DELETE depois do commit.
- Datas: DatePicker sem valor inventava data no `mounted`/`emitUpdate`. No modal, datepicker só aparece com data definida; senão botão "Definir …".
- Backend datas: `DataHora::converterDatePicker` exige `DD/MM/YYYY às HH:mm`; formatos inválidos → 422 (não 500).
- Atividade/histórico: `LogWeekly` precisa de `tarefa_id` para aparecer no modal. Relação `Tarefa::Logs()` sem `take()` (limit no `show`). Echo `.log` atualiza `TaskModal` via `prependLog` (não só `tarefaAtiva`).
- Atividades no modal: paginação 20 (`show` traz `logs` + `logs_meta`; `GET .../tarefas/{id}/logs?page=` carrega mais).
- Lembrete de entrega: select no sidebar grava código (`5m`…`2d`) → `tarefas.lembrete` datetime. Job `LembreteTarefaJob` a cada minuto em `routes/schedule.php` (Horizon). Dispara `NotificacaoEvent::LEMBRETE_TAREFA` + e-mail `LembreteTarefaMail` (fila) aos membros. Precisa de membros no card. `Request::exists('lembrete')` para limpar (não usar `has`).
- Performance (DBA): sem `$with` global em Tarefa/Lista/Checklist/LogWeekly. Board usa `WeeklyReportEagerLoads::applyBoardTarefas` (sem descrição/anexos/membros de item). Show carrega completo. Job lembrete enxuto. Índices em `2026_09_25_160000_add_weekly_report_performance_indexes`.
- Prazo checklist/item: coluna `datahora_entrega` em `checklists_tarefas` e `checklists_tarefa_items`. Update via `acao=add|remove` + `datahora_entrega`. UI: ícone relógio no header/item.
- Comentários: tabela `tarefas_comentarios` (tipos `comentario|bloqueio|dependencia`). Rotas nested `/comentarios`. Echo `weekly-report.tarefas.comentarios.{empresaId}`. Badge de bloqueio no card.
- Descrição/comentários: TinyMCE `preset=basico` (formatação básica + imagem). HTML sanitizado via `WeeklyReportHtml`.
- Imagem no editor: colar ou anexar → `uploadEditorImage` (só JPG/PNG/GIF); vira anexo da tarefa e `<img src>` na URL nested.
- Membros no item de checklist: pivot `checklists_tarefa_items_membros`; PUT `.../item/{item}/updateMembro` (`acao=add|remove`).
- Menções `@` na descrição e comentários (TinyMCE): busca `buscarMembros` e grava `<span class="wr-mention" data-user-id data-nome>`.
- Comentários: store responde na hora; broadcast Echo + AttachMentionedMembers rodam `afterResponse()` (evita travar o botão Publicar com Reverb/e-mail sync).

## Verify
- Abrir card → modal com descrição/checklist/ações (1 GET).
- + Checklist → input inline → Criar → aparece na lista.
- Novo item → Adicionar → checkbox aparece.
- Definir/Remover início e entrega sem erro 500.
- Atividade: concluir item / mover / editar → histórico no modal (e no topo do quadro).
- Prazo: ícone relógio na checklist e no item → define/altera/remove data de entrega.
- Comentários: publicar comentário/bloqueio/dependência → lista no modal; badge no card se houver bloqueio.
- Tiny: descrição e comentários com toolbar básica; HTML preservado ao salvar/exibir.
- Item: ícone user-plus → buscar membro e avatares no item.
- Digitar `@nome` na descrição/comentário → menu de menções e chip `@Nome`.
- Query params (histórico + F5): `?quadro=&lista=&tarefa=` em `weekly-report/queryParams.js`. Restore valida quadro do tenant; tarefa via board ou GET show (403/404 limpa URL). Ações continuam pelos `perms.*`.
- Lembrete: definir entrega + membros + lembrete (ex. 5m) → no horário, notificação in-app + e-mail; card atrasado fica vermelho.

- Menção → avatar aparece em Membros da tarefa; mencionado recebe notificação/e-mail (fluxo updateMembro).
