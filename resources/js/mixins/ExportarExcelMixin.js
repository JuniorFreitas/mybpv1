/**
 * @deprecated Use o mixin Exportacoes (exportaExcel) + JobExportaExcel no backend (padrão CIH).
 * Exportação XLSX no browser foi descontinuada: Job + Maatwebsite + disco-exportacao + notificação.
 */
import ExportacaoMixin from './Exportacoes'

const ExportExcel = {
    mixins: [ExportacaoMixin],
    methods: {
        async gerarArquivoXls() {
            if (!this.urlExportacao) {
                console.error('urlExportacao não definida — use o padrão CIH (Exportacoes + JobExportaExcel).')
                return
            }
            await this.exportaExcel()
        },
        async gerarXls() {
            await this.gerarArquivoXls()
        }
    }
}

export default ExportExcel
