<template>
    <div id="componenteAniversariante">
        <modal id="janelaParabensMassa" :fechar="!preload"
               titulo="Enviar Parabéns">
            <template slot="conteudo">
                <div class="row" v-show="!enviado && !preload">
                    <div class="col-12">
                        <h5>Enviar os parabéns para os <strong>{{ selecionadosMassa.length }}</strong> funcionário(s)
                            selecionado(s)?</h5>
                    </div>
                </div>
            </template>
            <template slot="rodape">
                <div v-show="!preload">
                    <button type="button" class="btn btn-sm btn-primary"
                            @click="enviar()"
                            v-show="!enviado">
                        <i class="fa fa-envelope"></i> Enviar
                    </button>
                </div>
            </template>
        </modal>

        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="nome_filtrado">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Aniversariantes do dia</label>
                        <select v-model="niver_dia" @change="aniversariantes_do_dia()"
                                class="custom-select custom-select-sm" :disabled="controle.carregando">
                            <option value="todos" selected>Todos</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
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
                            :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                            data-toggle="modal"
                            data-target="#janelaParabensMassa"
                            :disabled="!selecionadosMassa.length">
                        <i class="fa fa-envelope"></i> Enviar Parabéns <span
                        class="badge badge-light">{{ selecionadosMassa.length }}</span>
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
            </p>

            <div class="alert alert-warning text-center" v-if="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-if="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center">
                            <input type="checkbox"
                                   @click="selecionaTodosMassa" v-model="selecionaTudoMassa">
                        </th>
                        <td class="text-center">Nome</td>
                        <td class="text-center">Data</td>
                        <td class="text-center">Email</td>
                        <td class="text-center">Enviado</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="filtrarLista.length" v-for="aniversariantes in filtrarLista" :key="aniversariantes.id"
                        :class="{
                        'table-success': aniversariantes.enviado == 'enviado',
                        'table-warning': aniversariantes.enviado == 'enviando',
                      }">
                        <td class="text-center">
                            <label :for="aniversariantes.id"
                                   v-if="aniversariantes.enviado == 'Não' && aniversariantes.email != 'sistema@mybp.com.br'">
                                <input
                                    type="checkbox"
                                    v-model="selecionadosMassa"
                                    :value="aniversariantes.id"
                                    :id="aniversariantes.id"
                                    :style="aniversariantes.id ? 'cursor:pointer' : 'cursor: not-allowed'"
                                >
                            </label>
                        </td>
                        <td class="text-center"><i class="fa fa-birthday-cake mr-2"
                                                   v-if="aniversariantes.hoje"></i>{{ aniversariantes.nome }}
                        </td>
                        <td class="text-center">{{ aniversariantes.aniversario }}</td>
                        <td class="text-center">{{ aniversariantes.email }}</td>
                        <td class="text-center">{{ aniversariantes.enviado }}</td>
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
            preload: false,
            enviado: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            lista: [],
            lista_original: [],
            nome_filtrado: '',
            selecionadosMassa: [],
            selecionaTudoMassa: false,
            niver_dia: 'todos',

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
    computed: {
        filtrarLista() {
            if (this.lista && this.lista.length) {
                return this.lista.filter(item => item.nome.toLowerCase().includes(this.nome_filtrado.toLowerCase()))
            }
        }
    },
    methods: {
        enviar() {
            this.preload = true;
            axios.post(`${URL_ADMIN}/administracao/aniversariantes/enviaEmail`, {selecionados: this.selecionadosMassa}).then(response => {
                $('#janelaParabensMassa').modal('hide');
                mostraSucesso('', 'Estamos enviando a mensagem de parabéns');
                this.atualizar();
                this.selecionadosMassa = [];
                this.selecionaTudoMassa = false;
                this.preload = false;
            }).catch(error => (this.preload = false));

        },
        aniversariantes_do_dia() {
            this.lista = this.lista_original;
            if (this.niver_dia !== 'todos') {
                this.lista = this.lista.filter(item => item.hoje === this.niver_dia);
            }
        },
        async atualizar() {
            this.controle.carregando = true;
            await axios.post(this.urlPaginacao).then(({data}) => {
                this.lista = data.dados;
                this.lista_original = data.dados;
                this.aniversariantes_do_dia();
                this.controle.carregando = false;
            }).catch();
        },

        selecionaTodosMassa() {
            this.selecionaTudoMassa = !this.selecionaTudoMassa
            if (this.selecionaTudoMassa) {
                this.lista.map(item => {
                    let id = item.id
                    if (this.selecionadosMassa.indexOf(id) === -1 && (item.enviado == 'Não' && item.email != 'sistema@mybp.com.br')) {
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
