import { computed, onMounted, ref } from 'vue'
import configService from '../services/config'

export function useConfig(options = { autoLoad: true }) {
  const authconfiguracao = ref(null)
  const whatsappStatus = ref(null)

  const load = async () => {
    const [auth, status] = await Promise.all([
      configService.getAuth().catch(() => null),
      configService.getWhatsappStatus(),
    ])
    authconfiguracao.value = auth
    whatsappStatus.value = status
  }

  const whatsappLiberado = computed(() => {
    if (whatsappStatus.value != null) {
      return !!whatsappStatus.value.whatsapp_liberado
    }
    return !!authconfiguracao.value?.whatsappLiberado
  })

  const whatsappModuloHabilitado = (modulo) => {
    if (!whatsappLiberado.value) return false
    if (!whatsappStatus.value?.modulos) return false
    return !!whatsappStatus.value.modulos[modulo]
  }

  /** Empresa liberada E tipo habilitado (espelha mixin Configuracoes). */
  const whatsappTipoHabilitado = (tipo) => {
    if (!whatsappLiberado.value) return false
    if (!whatsappStatus.value?.tipos) return false
    return !!whatsappStatus.value.tipos[tipo]
  }

  const temFilial = computed(() => !!authconfiguracao.value?.temFilial)

  const urlVaga = computed(() => {
    const ambiente = import.meta.env.VITE_AMBIENTE
    const urlPadrao = ambiente === 'dev' ? 'https://hvagas.mybp.com.br' : 'https://vagas.mybp.com.br'
    return `${urlPadrao}/${authconfiguracao.value?.apelido || ''}`
  })

  if (options.autoLoad !== false) {
    onMounted(() => {
      load()
    })
  }

  return {
    authconfiguracao,
    whatsappStatus,
    whatsappLiberado,
    whatsappModuloHabilitado,
    whatsappTipoHabilitado,
    temFilial,
    urlVaga,
    load,
  }
}

export default useConfig
