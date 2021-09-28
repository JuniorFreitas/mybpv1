<template>
    <div id="componenteAniversariante">
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por conteudo"
                               autocomplete="off"
                               class="form-control" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>


                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
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
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Nome</td>
                        <td class="text-center">Data</td>
                        <td class="text-center">Email</td>
                        <td class="text-center">Idade</td>
                        <td class="text-center">Tipo</td>
                        <td class="text-center">Enviar Parabéns</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="aniversariantes in lista">
                        <td class="text-center">{{aniversariantes.nome}}</td>
                        <td class="text-center">{{aniversariantes.aniversario}}</td>
                        <td class="text-center">{{aniversariantes.email}}</td>
                        <td class="text-center">{{aniversariantes.idade ? aniversariantes.idade : '-'}}</td>
                        <td class="text-center">{{aniversariantes.tipo}}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary"
                                    @click="enviar(aniversariantes)">
                                <i class="fa fa-share"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="urlPaginacao" :por-pagina="qntPag"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>
    </div>
</template>

<script>
    import controlePaginacao from '../../ControlePaginacao';
    import modal from '../../Modal';
    import DatePicker from "../../DatePicker";

    export default {
        components: {
            DatePicker,
            modal,
            controlePaginacao,
        },
        props: {
            qntPag: {
                type: Number,
                required: false,
                default: 20
            },
            filtro: {
                type: Boolean,
                required: false,
                default: true
            },
        },
        data() {
            return {
                hash: String(Math.random()).substr(2),
                titulo_janela: 'Planejamento Diário',

                preload: false,
                editando: false,
                cadastrado: false,
                atualizado: false,

                lista: [],

                urlPaginacao: `${URL_ADMIN}/administracao/aniversariantes/atualizar`,
                controle: {
                    carregando: false,
                    dados: {
                        campoBusca: '',
                    },
                },
            }
        },
        mounted() {
            this.atualizar();
            this.formDefault = _.cloneDeep(this.form);
        },
        methods: {
            enviar(dados) {
                this.preloadAjax = true;
                axios.post(`${URL_ADMIN}/administracao/aniversariantes/enviaEmail`, dados).then(response => {
                    this.$refs.componente.buscar();
                    this.preloadAjax = false;
                }).catch(error => (this.preloadAjax = false));

            },
            carregou(dados) {
                this.lista = dados;
                this.controle.carregando = false;
            },
            carregando() {
                this.controle.carregando = true;
            },
            atualizar() {
                this.$refs.componente.atual = 1;
                this.$refs.componente.buscar();
            },
        }

    }
</script>
