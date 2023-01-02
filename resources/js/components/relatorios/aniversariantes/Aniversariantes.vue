<template>
    <div id="componenteAniversariante">
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Aniversariantes do Mês</label>
                        <select v-model="controle.dados.campoMes" @change="atualizar()" class="custom-select custom-select-sm" :disabled="controle.carregando">
                            <option v-for="(item, index) in listaMeses" :value="index">{{ item }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary"
                            @click.prevent="exportaPdf()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0) ">
                        <i class="fas fa-file-pdf"></i> EXPORTAR PDF
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                    >
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <h4 class="text-center mt-3">Aniversariantes de {{listaMeses[this.controle.dados.campoMes]}}</h4>
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Nome</td>
                        <td class="text-center">Data</td>
                        <td class="text-center">Email</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="lista.length" v-for="aniversariantes in lista" :key="aniversariantes.id">
                        <td class="text-center"><i class="fa fa-birthday-cake mr-2" v-if="aniversariantes.hoje"></i>{{aniversariantes.nome}}</td>
                        <td class="text-center">{{aniversariantes.aniversario}}</td>
                        <td class="text-center">{{aniversariantes.email}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import ExportacaoMixin from "../../../mixins/Exportacoes";
    export default {
        mixins: [ExportacaoMixin],
        props: {
            filtro: {
                type: Boolean,
                required: false,
                default: true
            },
        },
        data() {
            let dataAtual = new Date();
            let mesAtual = (dataAtual.getMonth() + 1);

            // console.log(MesAtual);

            return {
                preload: false,
                lista: [],
                listaMeses: [],
                urlPaginacao: `${URL_ADMIN}/relatorios/aniversariantes/atualizar`,
                urlPdf: `${URL_ADMIN}/relatorios/aniversariantes/pdf`,
                urlExportacao: `${URL_ADMIN}/relatorios/aniversariantes/export`,
                controle: {
                    carregando: false,
                    dados: {
                        campoMes: mesAtual,
                    },
                },
            }
        },
        mounted() {
            this.atualizar();
        },
        methods: {
            // enviar() {
            //     this.preload = true;
            //     axios.post(`${URL_ADMIN}/administracao/aniversariantes/enviaEmail`, {selecionados:this.selecionadosMassa}).then(response => {
            //         $('#janelaParabensMassa').modal('hide');
            //         mostraSucesso('', 'Estamos enviando a mensagem de parabéns');
            //         this.atualizar();
            //         this.selecionadosMassa = [];
            //         this.selecionaTudoMassa = false;
            //         this.preload = false;
            //     }).catch(error => (this.preload = false));
            //
            // },
            atualizar() {
                this.controle.carregando = true;
                axios.post(this.urlPaginacao, this.controle.dados).then(({data}) => {
                    this.lista = data.dados.funcionarios;
                    this.listaMeses = data.dados.lista_meses;
                    this.controle.carregando = false;
                }).catch();
            }
        },
        computed: {
            paramsExport() {
                return {
                    campoMes: this.controle.dados.campoMes
                }
            },
        }
    }
</script>
