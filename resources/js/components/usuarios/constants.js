/**
 * Constantes do módulo Usuários.
 * Evita magic strings e centraliza refs de modais e paths de API.
 */

export const REFS_MODAL = {
    JANELA_CADASTRAR: 'janelaCadastrar',
    JANELA_CONFIRMAR: 'janelaConfirmar'
}

export const MODAL_IDS = {
    JANELA_CADASTRAR: 'janelaCadastrar',
    JANELA_CONFIRMAR: 'janelaConfirmar'
}

/** Paths relativos à URL_ADMIN para as APIs de usuários */
export const API_PATHS = {
    usuarios: 'usuarios',
    editar: (id) => `usuarios/${id}/editar`,
    simularUsuario: 'usuarios/simularUsuario',
    buscaGrupoEmpresa: (empresaId) => `usuario/busca-grupo-empresa/${empresaId}`,
    whatsappPreferenciasModelo: (empresaId) => `usuarios/whatsapp-preferencias/modelo?empresa_id=${empresaId}`,
    ativaDesativa: (id) => `usuarios/${id}/ativa-desativa`
}
