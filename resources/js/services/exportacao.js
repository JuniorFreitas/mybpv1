import axios from 'axios'
import notificacaoService from './notificacao'

export const exportacaoService = {
  async exportarExcel(url, params = {}) {
    notificacaoService.sucesso(
      'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.'
    )
    try {
      await axios.post(url, params)
    } catch (erro) {
      notificacaoService.erro(erro)
      throw erro
    }
  },
  async exportarPdf(url, params = {}) {
    notificacaoService.sucesso(
      'Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.'
    )
    try {
      await axios.post(url, params)
    } catch (erro) {
      notificacaoService.erro(erro)
      throw erro
    }
  },
}

export default exportacaoService
