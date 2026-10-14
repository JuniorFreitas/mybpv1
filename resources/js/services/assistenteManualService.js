/**
 * Chamadas do Assistente do Manual (chatbot de dúvidas de uso).
 * Backend: AssistenteManualController@perguntar.
 */

const MENSAGEM_PADRAO = 'Não foi possível responder agora. Tente novamente em instantes.'

const MENSAGENS_POR_STATUS = {
    401: 'Sua sessão expirou. Atualize a página e entre novamente.',
    419: 'Sua sessão expirou. Atualize a página e tente de novo.',
    429: 'O limite de perguntas foi atingido. Aguarde um pouco e tente novamente.',
}

function mensagemDeErro(error) {
    const response = error && error.response
    if (!response) {
        return 'Sem conexão com o servidor. Verifique sua internet e tente novamente.'
    }

    const data = response.data || {}
    if (response.status === 422 && data.errors) {
        const primeiro = Object.values(data.errors)[0]
        return (Array.isArray(primeiro) ? primeiro[0] : primeiro) || MENSAGEM_PADRAO
    }

    return data.msg || MENSAGENS_POR_STATUS[response.status] || MENSAGEM_PADRAO
}

/**
 * @param {string} pergunta
 * @param {Array<{papel: 'usuario'|'assistente', texto: string}>} historico
 * @returns {Promise<{resposta: string, encontrou: boolean, fontes: Array}>}
 */
export async function perguntarAoAssistente(pergunta, historico = []) {
    try {
        const { data } = await window.axios.post(`${window.URL_ADMIN}/ajuda/assistente/perguntar`, {
            pergunta,
            historico,
        })

        return data.data
    } catch (error) {
        throw new Error(mensagemDeErro(error))
    }
}
