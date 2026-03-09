/**
 * Constantes do módulo Controle de Exames.
 * Evita magic strings e centraliza refs de modais e paths de API.
 */

export const REFS_MODAL = {
    JANELA_PARCER_ENTREVISTA: 'janelaParecerEntrevista',
    MODAL_VALIDA_SESMT: 'modalValidaSesmt',
    MODAL_FILTRO_COLUNAS: 'modalFiltroColunas'
}

export const MODAL_IDS = {
    JANELA_PARCER_ENTREVISTA: 'janelaParecerEntrevista',
    VALIDA_SESMT: 'validaSesmt',
    FILTRO_COLUNAS: 'filtroColunas'
}

/** Paths relativos à URL_ADMIN para as APIs de controle de exames */
export const API_PATHS = {
    carregaResposta: 'controle-exames/carregaResposta',
    salvaUpdate: 'controle-exames/salvaUpdate',
    resultado: 'controle-exames/resultado',
    salvaResultado: 'controle-exames/salvaResultado',
    atualizar: 'controle-exames/atualizar',
    anexo: 'controle-exames-resultado/uploadAnexos'
}
