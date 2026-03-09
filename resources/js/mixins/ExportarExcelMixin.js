const XLSX = require("@e965/xlsx");
const ExportExcel = {
    methods: {
        gerarNomeArquivo(dataHoraFormatada) {
            return `${this.nomeArquivo}_${dataHoraFormatada}.xlsx`;
        },
        async gerarArquivoXls() {
            try {
                this.preload = true;

                const dados = await this.obterDados();

                const dataHoraAtual = new Date().toLocaleString("en-US", {
                    timeZone: "America/Sao_Paulo",
                    hour12: false,
                });
                const dataHoraFormatada = dataHoraAtual
                    .replace(/\/|,|\s|:/g, "_")
                    .replace(/\//g, "-");

                const dadosFormatados = this.formatarDados(dados);

                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.json_to_sheet(dadosFormatados);
                XLSX.utils.book_append_sheet(wb, ws, "Dados");

                const xlsBuffer = XLSX.write(wb, {type: "array", bookType: "xlsx"});
                const blob = new Blob([xlsBuffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });

                const nomeArquivo = this.gerarNomeArquivo(dataHoraFormatada);

                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = nomeArquivo;
                a.style.display = "none";

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                console.log("Arquivo XLS gerado e iniciando o download...");
                this.preload = false;
            } catch (error) {
                this.preload = false;
                console.error("Erro ao obter os dados ou gerar o arquivo XLS:", error);
            }
        },

        async gerarXls() {
            await this.gerarArquivoXls.call(this);
        },
    },
};
export default ExportExcel;
