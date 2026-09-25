const base = () => (typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : '')

export function weeklyBase(empresaId) {
    return `${base()}/weekly-report/${empresaId}`
}

export function weeklyRel(empresaId) {
    return `weekly-report/${empresaId}`
}

export function quadroUrl(empresaId, quadroId = null, absolute = true) {
    const root = absolute ? `${weeklyBase(empresaId)}/quadros` : `${weeklyRel(empresaId)}/quadros`
    return quadroId ? `${root}/${quadroId}` : root
}

export function listasUrl(empresaId, quadroId, listaId = null, absolute = true) {
    const root = `${quadroUrl(empresaId, quadroId, absolute)}/listas`
    return listaId ? `${root}/${listaId}` : root
}

export function tarefasUrl(empresaId, quadroId, listaId, tarefaId = null, absolute = true) {
    const root = `${listasUrl(empresaId, quadroId, listaId, absolute)}/tarefas`
    return tarefaId ? `${root}/${tarefaId}` : root
}

export function checklistUrl(empresaId, quadroId, listaId, tarefaId, checklistId = null, absolute = true) {
    const root = `${tarefasUrl(empresaId, quadroId, listaId, tarefaId, absolute)}/checklist`
    return checklistId ? `${root}/${checklistId}` : root
}

export function itemUrl(empresaId, quadroId, listaId, tarefaId, checklistId, itemId = null, absolute = true) {
    const root = `${checklistUrl(empresaId, quadroId, listaId, tarefaId, checklistId, absolute)}/item`
    return itemId ? `${root}/${itemId}` : root
}

export function comentariosUrl(empresaId, quadroId, listaId, tarefaId, comentarioId = null, absolute = true) {
    const root = `${tarefasUrl(empresaId, quadroId, listaId, tarefaId, absolute)}/comentarios`
    return comentarioId ? `${root}/${comentarioId}` : root
}

export function inicialNome(nome) {
    if (!nome) return '?'
    const parts = String(nome).trim().split(/\s+/)
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

/** Formato esperado pelo DatePicker + DataHora::converterDatePicker */
export function formatDataHoraPicker(value) {
    if (!value) return null
    const str = String(value).trim()
    if (/^\d{2}\/\d{2}\/\d{4}\s+às\s+\d{2}:\d{2}/i.test(str)) {
        return str.replace(/\s+às\s+/i, ' às ').replace(/(\d{2}:\d{2}):\d{2}$/, '$1')
    }
    if (typeof moment !== 'undefined') {
        const m = moment(str, ['DD/MM/YYYY HH:mm', 'DD/MM/YYYY HH:mm:ss', 'YYYY-MM-DD HH:mm:ss', 'YYYY-MM-DDTHH:mm:ssZ', moment.ISO_8601], true)
        if (m.isValid()) return m.format('DD/MM/YYYY [às] HH:mm')
        const loose = moment(str)
        if (loose.isValid()) return loose.format('DD/MM/YYYY [às] HH:mm')
    }
    return str
}

export function normalizeLog(raw) {
    if (!raw) return null
    const usuario = raw.usuario ?? raw.Usuario ?? null
    return {
        ...raw,
        id: raw.id,
        tarefa_id: raw.tarefa_id ?? raw.tarefaId ?? null,
        descricao: raw.descricao || '',
        created_at: raw.created_at || raw.createdAt || '',
        usuario: usuario
            ? { id: usuario.id, nome: usuario.nome || usuario.name || 'Usuário' }
            : { id: raw.user_id || null, nome: 'Usuário' }
    }
}

export function normalizeLogsMeta(raw) {
    if (!raw || typeof raw !== 'object') {
        return { current_page: 1, last_page: 1, per_page: 20, total: 0 }
    }
    return {
        current_page: Number(raw.current_page ?? raw.currentPage ?? 1),
        last_page: Number(raw.last_page ?? raw.lastPage ?? 1),
        per_page: Number(raw.per_page ?? raw.perPage ?? 20),
        total: Number(raw.total ?? 0)
    }
}

export function normalizeComentario(raw) {
    if (!raw) return null
    const usuario = raw.usuario ?? raw.Usuario ?? null
    const tipo = raw.tipo || 'comentario'
    return {
        ...raw,
        id: raw.id,
        tarefa_id: raw.tarefa_id ?? raw.tarefaId ?? null,
        comentario: raw.comentario || '',
        tipo,
        tipo_label: raw.tipo_label || raw.tipoLabel || (
            tipo === 'bloqueio' ? 'Bloqueio' : tipo === 'dependencia' ? 'Dependência externa' : 'Comentário'
        ),
        created_at: raw.created_at || raw.createdAt || '',
        usuario: usuario
            ? { id: usuario.id, nome: usuario.nome || usuario.name || 'Usuário' }
            : { id: raw.user_id || null, nome: 'Usuário' }
    }
}

export function normalizeTarefa(raw) {
    if (!raw) return null
    const t = { ...raw }
    t.emAtraso = !!(t.emAtraso ?? t.em_atraso)
    t.lembreteText = t.lembreteText ?? t.lembrete_text ?? null
    const membros = t.membros ?? t.Membros
    const anexos = t.anexos ?? t.Anexos
    const logs = t.logs ?? t.Logs
    const checklists = t.checklists ?? t.Checklists
    const comentarios = t.comentarios ?? t.Comentarios
    t.membros = Array.isArray(membros) ? membros : []
    t.anexos = Array.isArray(anexos) ? anexos : []
    t.anexos_count = Number(
        t.anexos_count ?? t.anexosCount ?? (Array.isArray(t.anexos) ? t.anexos.length : 0)
    )
    t.logs = (Array.isArray(logs) ? logs : []).map(normalizeLog).filter(Boolean)
    t.comentarios = (Array.isArray(comentarios) ? comentarios : []).map(normalizeComentario).filter(Boolean)
    t.bloqueios_count = Number(t.bloqueios_count ?? t.bloqueiosCount ?? 0)
    t.comentarios_count = Number(
        t.comentarios_count ?? t.comentariosCount ?? t.comentarios.length ?? 0
    )
    t.checklists = (Array.isArray(checklists) ? checklists : []).map((ck) => ({
        ...ck,
        datahora_entrega: formatDataHoraPicker(ck.datahora_entrega ?? ck.datahora_entrega_br ?? null),
        emAtraso: !!(ck.emAtraso ?? ck.em_atraso),
        itens: (Array.isArray(ck.itens ?? ck.Itens) ? (ck.itens ?? ck.Itens) : []).map((item) => {
            const membros = item.membros ?? item.Membros
            return {
                ...item,
                concluido: !!item.concluido,
                datahora_entrega: formatDataHoraPicker(item.datahora_entrega ?? item.datahora_entrega_br ?? null),
                emAtraso: !!(item.emAtraso ?? item.em_atraso),
                membros: Array.isArray(membros) ? membros : []
            }
        })
    }))
    t.datahora_inicio = formatDataHoraPicker(t.datahora_inicio)
    t.datahora_entrega = formatDataHoraPicker(t.datahora_entrega)
    t.concluido = !!t.concluido
    return t
}

export function normalizeLista(raw) {
    if (!raw) return null
    const tarefas = raw.tarefas || raw.Tarefas || []
    return {
        ...raw,
        titulo: raw.titulo || '',
        ordem: raw.ordem || 0,
        tarefas: tarefas.map(normalizeTarefa).sort((a, b) => (a.ordem || 0) - (b.ordem || 0))
    }
}

export function normalizeListas(payload) {
    const arr = Array.isArray(payload) ? payload : []
    return arr.map(normalizeLista).sort((a, b) => (a.ordem || 0) - (b.ordem || 0))
}

export function checklistProgress(tarefa) {
    const checklists = tarefa?.checklists || []
    let total = 0
    let done = 0
    checklists.forEach((ck) => {
        ;(ck.itens || []).forEach((item) => {
            total++
            if (item.concluido) done++
        })
    })
    return { total, done }
}

export function toastErro(msg) {
    if (typeof window.mostraErro === 'function') {
        window.mostraErro('', msg)
    } else if (window.toastr) {
        window.toastr.error(msg)
    }
}

export function toastOk(msg) {
    if (typeof window.mostraSucesso === 'function') {
        window.mostraSucesso('', msg)
    } else if (window.toastr) {
        window.toastr.success(msg)
    }
}
