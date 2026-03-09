const Exportacao = {
    data() {
        return {
            preloadExportacao: false,
        };
    },
    methods: {
        async exportaExcel() {
            this.preloadExportacao = true;
            mostraSucesso("Estamos gerando seu arquivo excel, assim que finalizado você será notificado.");
            setTimeout(() => {
                this.preloadExportacao = false;
            }, 500);
            try {
                await axios.post(`${this.urlExportacao}`, this.paramsExport);
                this.preloadExportacao = false;
            } catch (erro) {
                mostraErro(erro);
                this.preloadExportacao = false;
            }
        },

        async exportaPdf() {
            this.preloadExportacao = true;
            mostraSucesso("Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.");
            setTimeout(() => {
                this.preloadExportacao = false;
            }, 500);
            try {
                await axios.post(`${this.urlPdf}`, this.paramsExport);
                this.preloadExportacao = false;
            } catch (erro) {
                mostraErro(erro);
                this.preloadExportacao = false;
            }
        },
    }
};
export default Exportacao;
