import api from './api'

export const configService = {
  getAuth() {
    return api.get('/usuario/autenticado/').then((res) => res.data)
  },
  getWhatsappStatus() {
    return api
      .get('/configuracoes/whatsapp/status')
      .then((res) => res.data)
      .catch(() => null)
  },
}

export default configService
