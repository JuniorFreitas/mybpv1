import exportacaoService from '../services/exportacao'

/**
 * Thin wrapper: delega ao exportacaoService (FE-001 Fase 4).
 * Telas ainda usam this.urlExportacao / this.paramsExport / this.urlPdf.
 */
const Exportacao = {
  data() {
    return {
      preloadExportacao: false,
    }
  },
  methods: {
    async exportaExcel() {
      this.preloadExportacao = true
      try {
        await exportacaoService.exportarExcel(this.urlExportacao, this.paramsExport)
      } catch (_erro) {
        // erro já notificado pelo service
      } finally {
        setTimeout(() => {
          this.preloadExportacao = false
        }, 500)
      }
    },

    async exportaPdf() {
      this.preloadExportacao = true
      try {
        await exportacaoService.exportarPdf(this.urlPdf, this.paramsExport)
      } catch (_erro) {
        // erro já notificado pelo service
      } finally {
        setTimeout(() => {
          this.preloadExportacao = false
        }, 500)
      }
    },
  },
}

export default Exportacao
