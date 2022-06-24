const Exportacao = {
    data() {
        return {
            preloadExportacao: false,
        };
    },
    methods: {
        exportaExcel() {
            this.preloadExportacao = true;
            axios.post(`${this.urlExportacao}`, 
            this.paramsExport
            ).then(({ data }) => {
                mostraSucesso(data.msg);
                this.preloadExportacao = false;
            }).catch(erro => {
                mostraErro(erro);
                this.preloadExportacao = false;
            });
        },
    }
};
export default Exportacao;
