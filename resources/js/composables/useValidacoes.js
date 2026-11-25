import notificacaoService from '../services/notificacao'

function replaceAll(string, token, newtoken) {
  let out = String(string ?? '')
  while (out.indexOf(token) !== -1) {
    out = out.replace(token, newtoken)
  }
  return out
}

function mostraErro(retornoLaravel, titulo = 'Ocorreu um erro', quantidade = 3) {
  if (retornoLaravel === undefined || retornoLaravel === null) {
    return false
  }

  let payload = retornoLaravel
  if (payload && typeof payload === 'object' && payload.isAxiosError) {
    payload = payload.response?.data || {
      msg: titulo,
      message: payload.message || 'Ocorreu um erro',
    }
  }

  if (typeof payload === 'string') {
    notificacaoService.erro(payload, titulo)
    return
  }

  let mensagem = ''
  const tituloFinal = payload.msg ? payload.msg : titulo
  const erros = payload.erros || payload.errors || {}
  const lista = Object.keys(erros)
  if (lista.length) {
    mensagem += '<ul>'
    let total = 1
    for (const key of lista) {
      const descricao = Array.isArray(erros[key]) ? erros[key][0] : erros[key]
      mensagem += `<li> <strong>${key}:</strong> ${descricao} </li>`
      total++
      if (total === quantidade) break
    }
    mensagem += '</ul>'
  } else {
    mensagem = payload.message || payload.msg || ''
  }

  if (window.toastr) {
    window.toastr.error(mensagem || 'Ocorreu um erro', tituloFinal)
  } else {
    notificacaoService.erro(mensagem || 'Ocorreu um erro', tituloFinal)
  }
}

function mostraSucesso(mensagem, titulo) {
  notificacaoService.sucesso(mensagem, titulo)
}

function validaBlur() {
  const el = document.querySelectorAll('.validacampo')
  el.forEach((input) => {
    input.focus()
    input.blur()
  })
}

function callGlobal(nome, ...args) {
  if (typeof window !== 'undefined' && typeof window[nome] === 'function') {
    return window[nome](...args)
  }
  return true
}

/**
 * Composition API do mixin Validacoes (notificação + blur + CPF/CNPJ).
 * CPF/CNPJ delegam às funções globais de funcoes.js (fonte única).
 */
export function useValidacoes() {
  return {
    mostraErro,
    mostraSucesso,
    validaBlur,
    replaceAll,
    valida_cpf: (evt) => callGlobal('valida_cpf', evt),
    valida_cpf_vazio: (evt) => callGlobal('valida_cpf_vazio', evt),
    validaCpfVazio: (evt) => callGlobal('valida_cpf_vazio', evt),
    valida_cnpj_vazio: (evt) => callGlobal('valida_cnpj_vazio', evt),
  }
}

export default useValidacoes
