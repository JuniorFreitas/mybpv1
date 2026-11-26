export const notificacaoService = {
  sucesso(mensagem, titulo = 'Sucesso') {
    if (typeof mostraSucesso === 'function') {
      mostraSucesso(mensagem, titulo)
      return
    }
    if (window.toastr) {
      window.toastr.success(mensagem, titulo)
    }
  },
  erro(err, titulo = 'Erro') {
    if (typeof mostraErro === 'function') {
      mostraErro(err, titulo)
      return
    }
    const msg =
      (err && err.response && err.response.data && (err.response.data.msg || err.response.data.message)) ||
      (err && err.message) ||
      'Ocorreu um erro'
    if (window.toastr) {
      window.toastr.error(msg, titulo)
    }
  },
}

export default notificacaoService
