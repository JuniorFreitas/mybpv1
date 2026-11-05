/** Constantes e URLs compartilhadas pelas telas de Documentos SST. */

export const BASE_DOCUMENTOS = () => `${URL_ADMIN}/saude-ocupacional/documentos`

export const SITUACOES = {
    vencido: { label: 'Vencido', badge: 'badge-danger', ativo: 'btn-danger' },
    vencendo: { label: 'Vencendo', badge: 'badge-warning', ativo: 'btn-warning' },
    vigente: { label: 'Vigente', badge: 'badge-success', ativo: 'btn-success' },
    sem_validade: { label: 'Sem validade', badge: 'badge-secondary', ativo: 'btn-secondary' }
}

/** Download/visualização passa sempre pela rota (permissão + trilha de acesso). */
export function urlArquivo(versaoId, visualizar) {
    return `${BASE_DOCUMENTOS()}/versoes/${versaoId}/arquivo${visualizar ? '?visualizar=1' : ''}`
}

export function abreNoNavegador(extensao) {
    return ['pdf', 'jpg', 'jpeg', 'png'].includes(String(extensao || '').toLowerCase())
}

export function tamanhoLegivel(bytes) {
    if (!bytes && bytes !== 0) return ''
    if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1).replace('.', ',')} MB`
}
