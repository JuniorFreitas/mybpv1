/**
 * Presence Echo subscriptions for Weekly Report (tenant = empresa_id / cliente_id).
 * Never throws — realtime is optional.
 */
export function joinWeeklyChannels(empresaId, handlers = {}) {
    if (typeof window.Echo === 'undefined' || !empresaId) {
        return () => {}
    }

    const channels = []

    const safeJoin = (name, bind) => {
        try {
            const ch = window.Echo.join(name)
            bind(ch)
            channels.push(name)
        } catch (e) {
            console.warn('[weekly-report] Echo join failed:', name, e)
        }
    }

    safeJoin(`weekly-report.log.${empresaId}`, (ch) => {
        ch.listen('.log', (e) => handlers.onLog && handlers.onLog(e))
    })

    safeJoin(`weekly-report.quadros.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onQuadroInsert && handlers.onQuadroInsert(e))
            .listen('.update', (e) => handlers.onQuadroUpdate && handlers.onQuadroUpdate(e))
            .listen('.delete', (e) => handlers.onQuadroDelete && handlers.onQuadroDelete(e))
    })

    safeJoin(`weekly-report.listas.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onListaInsert && handlers.onListaInsert(e))
            .listen('.update', (e) => handlers.onListaUpdate && handlers.onListaUpdate(e))
            .listen('.delete', (e) => handlers.onListaDelete && handlers.onListaDelete(e))
            .listen('.ordenar', (e) => handlers.onListaOrdenar && handlers.onListaOrdenar(e))
    })

    safeJoin(`weekly-report.tarefas.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onTarefaInsert && handlers.onTarefaInsert(e))
            .listen('.update', (e) => handlers.onTarefaUpdate && handlers.onTarefaUpdate(e))
            .listen('.delete', (e) => handlers.onTarefaDelete && handlers.onTarefaDelete(e))
            .listen('.ordenar', (e) => handlers.onTarefaOrdenar && handlers.onTarefaOrdenar(e))
            .listen('.ordenarChecklist', (e) => handlers.onChecklistOrdenar && handlers.onChecklistOrdenar(e))
            .listen('.updateMembro', (e) => handlers.onTarefaUpdate && handlers.onTarefaUpdate(e))
            .listen('.updateDataHoraInicio', (e) => handlers.onTarefaUpdate && handlers.onTarefaUpdate(e))
            .listen('.updateDataHoraEntrega', (e) => handlers.onTarefaUpdate && handlers.onTarefaUpdate(e))
    })

    safeJoin(`weekly-report.tarefas.anexos.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onAnexo && handlers.onAnexo(e))
            .listen('.update', (e) => handlers.onAnexo && handlers.onAnexo(e))
            .listen('.delete', (e) => handlers.onAnexo && handlers.onAnexo(e))
    })

    safeJoin(`weekly-report.tarefas.checklists.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onChecklist && handlers.onChecklist(e))
            .listen('.update', (e) => handlers.onChecklist && handlers.onChecklist(e))
            .listen('.delete', (e) => handlers.onChecklist && handlers.onChecklist(e))
            .listen('.ordenar_itens', (e) => handlers.onChecklist && handlers.onChecklist(e))
    })

    safeJoin(`weekly-report.tarefas.checklists.itens.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onChecklistItem && handlers.onChecklistItem(e))
            .listen('.update', (e) => handlers.onChecklistItem && handlers.onChecklistItem(e))
            .listen('.delete', (e) => handlers.onChecklistItem && handlers.onChecklistItem(e))
    })

    safeJoin(`weekly-report.tarefas.comentarios.${empresaId}`, (ch) => {
        ch.listen('.insert', (e) => handlers.onComentario && handlers.onComentario(e))
            .listen('.update', (e) => handlers.onComentario && handlers.onComentario(e))
            .listen('.delete', (e) => handlers.onComentario && handlers.onComentario(e))
    })

    return () => {
        channels.forEach((name) => {
            try {
                window.Echo.leave(name)
            } catch (e) {
                /* ignore */
            }
        })
    }
}
