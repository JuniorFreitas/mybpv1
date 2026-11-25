import { ref } from 'vue'
import exportacaoService from '../services/exportacao'

export function useExportacao() {
  const preloadExportacao = ref(false)

  const exportaExcel = async (url, params = {}) => {
    preloadExportacao.value = true
    try {
      await exportacaoService.exportarExcel(url, params)
    } finally {
      setTimeout(() => {
        preloadExportacao.value = false
      }, 500)
    }
  }

  const exportaPdf = async (url, params = {}) => {
    preloadExportacao.value = true
    try {
      await exportacaoService.exportarPdf(url, params)
    } finally {
      setTimeout(() => {
        preloadExportacao.value = false
      }, 500)
    }
  }

  return { preloadExportacao, exportaExcel, exportaPdf }
}

export default useExportacao
