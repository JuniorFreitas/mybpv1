const Exportacao = {
    data() {
        return {
            preloadExportacao: false,
        };
    },
    methods: {
        exportaExcel() {
            this.preloadExportacao = true;
            mostraSucesso("Estamos gerando seu arquivo excel, assim que finalizado você será notificado.");
            setTimeout(() => {
                this.preloadExportacao = false;
            }, 500);
            axios.post(`${this.urlExportacao}`,
            this.paramsExport
            ).then(({ data }) => {
                this.preloadExportacao = false;
            }).catch(erro => {
                mostraErro(erro);
                this.preloadExportacao = false;
            });
        },

        exportaPdf() {
            this.preloadExportacao = true;
            mostraSucesso("Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.");
            setTimeout(() => {
                this.preloadExportacao = false;
            }, 500);
            axios.post(`${this.urlPdf}`,
                this.paramsExport
            ).then(({ data }) => {
                this.preloadExportacao = false;
            }).catch(erro => {
                mostraErro(erro);
                this.preloadExportacao = false;
            });
        },
    }
};
export default Exportacao;
