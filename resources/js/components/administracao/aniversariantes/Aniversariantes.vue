<template>
    <div id="componenteAniversariante">
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control" :disabled="controle.carregando"
                               v-model="nome_filtrado">
                    </div>
                </div>


                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button class="btn btn-sm btn-primary"
                            :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                            @click.pre.prevent="abrirFormParabens()"
                            data-toggle="modal"
                            data-target="#janelaParabensMassa"
                            :disabled="!selecionadosMassa.length">
                        <i class="fa fa-envelope"></i> Enviar Parabéns <span class="badge badge-light">{{ selecionadosMassa.length }}</span>
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
                        <th class="text-center">
                            <input type="checkbox"
                                   :checked="tudoMarcadoMassa"
                                   @click="selecionaTodosMassa">
                        </th>
                        <td class="text-center">Nome</td>
                        <td class="text-center">Data</td>
                        <td class="text-center">Email</td>
                        <td class="text-center">Idade</td>
                        <td class="text-center">Enviado</td>
<!--                        <td class="text-center">Enviar Parabéns</td>-->
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="filtrarLista.length" v-for="aniversariantes in filtrarLista">
                        <td class="text-center">
                            <label :for="aniversariantes.id" v-if="aniversariantes.enviado == 'Não'">
                                <input
                                    type="checkbox"
                                    v-model="selecionadosMassa"
                                    :value="aniversariantes.id"
                                    :id="aniversariantes.id"
                                    :style="aniversariantes.id ? 'cursor:pointer' : 'cursor: not-allowed'"
                                >
                            </label>
                        </td>
                        <td class="text-center">{{aniversariantes.nome}}</td>
                        <td class="text-center">{{aniversariantes.aniversario}}</td>
                        <td class="text-center">{{aniversariantes.email}}</td>
                        <td class="text-center">{{aniversariantes.idade ? aniversariantes.idade : '-'}}</td>
                        <td class="text-center">{{aniversariantes.enviado}}</td>
<!--                        <td class="text-center">-->
<!--                            <button type="button" class="btn btn-sm btn-primary" v-if="aniversariantes.enviado == 'Não' && aniversariantes.email != 'sistema@mybp.com.br'"-->
<!--                                    @click="enviar(aniversariantes)">-->
<!--                                <i class="fa fa-share"></i>-->
<!--                            </button>-->
<!--                        </td>-->
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import modal from '../../Modal';
    import DatePicker from "../../DatePicker";

    export default {
        components: {
            DatePicker,
            modal
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
                nome_filtrado: '',
                selecionadosMassa: [],
                selecionaTudoMassa: false,

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
            console.log('Iniciando');
            this.atualizar();
            this.formDefault = _.cloneDeep(this.form);
        },
        computed: {
            filtrarLista(){
                if (this.lista && this.lista.length) {
                    return this.lista.filter(item => item.nome.toLowerCase().includes(this.nome_filtrado.toLowerCase()))
                }
            },
            emTreinamentosMassa() {

            },
            tudoMarcadoMassa() {

            }
        },
        methods: {
            enviar(dados) {
                this.preloadAjax = true;
                axios.post(`${URL_ADMIN}/administracao/aniversariantes/enviaEmail`, dados).then(response => {
                    this.atualizar();
                    this.preloadAjax = false;
                }).catch(error => (this.preloadAjax = false));

            },
            atualizar() {
                this.controle.carregando = true;
                axios.post(this.urlPaginacao).then(({data}) => {
                    this.lista = data.dados;
                    this.controle.carregando = false;
                }).catch();
            },

            selecionaTodosMassa() {
                this.selecionaTudoMassa = !this.selecionaTudoMassa
                if (this.selecionaTudoMassa) {
                    this.lista.map(item => {
                        let id = item.id
                        if (this.selecionadosMassa.indexOf(id) === -1) {
                            this.selecionadosMassa.push(id)
                        }
                    })
                } else {
                    this.lista.map(item => {
                        let id = item.id
                        let index = this.selecionadosMassa.indexOf(id)
                        if (index >= 0) {
                            this.selecionadosMassa.splice(index, 1)
                        }
                    })
                }
            },
        }

    }
</script>
