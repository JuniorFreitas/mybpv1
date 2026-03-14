/**
 * Constantes do módulo Treinamentos Carteira/Etiquetas.
 * Centraliza refs de modais e paths de API.
 */

export const REFS_MODAL = {
    MODAL_FILTRO_COLUNAS: 'modalFiltroColunas',
    JANELA_TREINAMENTO: 'janelaTreinamento',
    JANELA_TREINAMENTO_MASSA: 'janelaTreinamentoMassa',
    JANELA_ENVIAR: 'janelaEnviar',
    JANELA_ENVIAR_AVISO: 'janelaEnviarAviso'
}

export const MODAL_IDS = {
    FILTRO_COLUNAS: 'filtroColunas',
    JANELA_TREINAMENTO: 'janelaTreinamento',
    JANELA_TREINAMENTO_MASSA: 'janelaTreinamentoMassa',
    JANELA_ENVIAR: 'janelaEnviar',
    JANELA_ENVIAR_AVISO: 'janelaEnviarAviso'
}

/** Paths relativos à URL_ADMIN */
export const API_PATHS = {
    treinamento: 'treinamento',
    atualizar: 'treinamento/atualizar',
    vencimentosPorSegmento: 'treinamento/vencimentos-por-segmento',
    carteiras: 'treinamento/carteiras',
    editar: 'treinamento',
    store: 'treinamento',
    salvarMassa: 'treinamento/salvar-massa',
    enviarCarteira: 'treinamento/enviar-carteira',
    proximovencimento: 'treinamento/proximovencimento',
    uploadAnexos: 'treinamento/uploadAnexos',
    export: 'treinamento/export',
    segmentosHabilitados: 'cadastro/segmentostreinamento/habilitados-empresa',
    desmarcarTreinamentoRealizado: 'treinamento/desmarcar-treinamento-realizado',
    atualizarVencimento: 'treinamento/atualizar-vencimento'
}
