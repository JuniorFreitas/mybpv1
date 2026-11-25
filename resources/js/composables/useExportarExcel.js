/**
 * Encapsula o fluxo legado ExportarExcelMixin.
 * XLSX no browser foi descontinuado — delega ao padrão CIH (Job + exportacaoService).
 */
import { useExportacao } from './useExportacao'

export function useExportarExcel() {
  const { preloadExportacao, exportaExcel, exportaPdf } = useExportacao()

  const gerarArquivoXls = async (url, params = {}) => {
    if (!url) {
      console.error('urlExportacao não definida — use o padrão CIH (JobExportaExcel).')
      return
    }
    await exportaExcel(url, params)
  }

  const gerarXls = async (url, params = {}) => gerarArquivoXls(url, params)

  return { preloadExportacao, gerarArquivoXls, gerarXls, exportaExcel, exportaPdf }
}

export default useExportarExcel
