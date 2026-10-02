<template>
    <div id="componenteAniversariante">
        <modal ref="modalParabensMassa" id="janelaParabensMassa" :fechar="!preload" titulo="Enviar Parabéns">
            <template #conteudo>
                <div class="row" v-show="!enviado && !preload">
                    <div class="col-12">
                        <h5>
                            Enviar os parabéns para os <strong>{{ selecionadosMassa.length }}</strong> funcionário(s) selecionado(s)?
                        </h5>
                        <div v-if="aniversarioWhatsapp" class="mt-3">
                            <p class="mb-2">Como deseja enviar?</p>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="canal-email" v-model="enviarPorEmail" />
                                <label class="custom-control-label" for="canal-email">E-mail</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="canal-whatsapp" v-model="enviarPorWhatsapp" />
                                <label class="custom-control-label" for="canal-whatsapp">WhatsApp</label>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #rodape>
                <div v-show="!preload">
                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        @click="enviar()"
                        v-show="!enviado"
                        :disabled="aniversarioWhatsapp && !canalEnvio"
                    >
                        <i class="fa fa-envelope"></i> Enviar
                    </button>
                </div>
            </template>
        </modal>

        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente && $refs.componente.buscar ? $refs.componente.buscar() : null">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="nome_filtrado"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Aniversariantes do dia</label>
                        <select v-model="niver_dia" @change="aniversariantes_do_dia()" class="custom-select custom-select-sm" :disabled="controle.carregando">
                            <option value="todos">Todos</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        :style="!selecionadosMassa.length ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="!selecionadosMassa.length"
                        @click="abrirModalParabens"
                    >
                        <i class="fa fa-envelope"></i> Enviar Parabéns <span class="badge badge-light">{{ selecionadosMassa.length }}</span>
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-outline-secondary" :disabled="controle.carregando" @click="mostrarMensagem = !mostrarMensagem">
                        <i class="fa fa-edit"></i> {{ mostrarMensagem ? 'Ocultar mensagem' : 'Mensagem de Aniversário' }}
                    </button>
                </div>
            </form>
        </fieldset>

        <fieldset v-if="mostrarMensagem" class="mt-3">
            <legend>Mensagem de Aniversário</legend>
            <aniversariante-mensagem></aniversariante-mensagem>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

            <div class="alert alert-warning text-center" v-if="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-if="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <th class="text-center">
                                <input type="checkbox" @click="selecionaTodosMassa" v-model="selecionaTudoMassa" />
                            </th>
                            <td class="text-center">Nome</td>
                            <td class="text-center">Data</td>
                            <td class="text-center">Email</td>
                            <td class="text-center">WhatsApp</td>
                            <td class="text-center">Enviado</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="aniversariantes in filtrarLista"
                            :key="aniversariantes.id"
                            :class="{
                                'table-success': aniversariantes.enviado == 'enviado',
                                'table-warning': aniversariantes.enviado == 'enviando'
                            }"
                        >
                            <td class="text-center">
                                <label :for="aniversariantes.id" v-if="podeSelecionar(aniversariantes)">
                                    <input
                                        type="checkbox"
                                        v-model="selecionadosMassa"
                                        :value="aniversariantes.id"
                                        :id="aniversariantes.id"
                                        :style="aniversariantes.id ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    />
                                </label>
                            </td>
                            <td class="text-center"><i class="fa fa-birthday-cake mr-2" v-if="aniversariantes.hoje"></i>{{ aniversariantes.nome }}</td>
                            <td class="text-center">{{ aniversariantes.aniversario }}</td>
                            <td class="text-center">{{ aniversariantes.email }}</td>
                            <td class="text-center">{{ aniversariantes.whatsapp }}</td>
                            <td class="text-center">{{ aniversariantes.enviado }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import modal from '../../Modal'
import DatePicker from '../../DatePicker'
import AniversarianteMensagem from './AniversarianteMensagem.vue'

export default {
    components: {
        DatePicker,
        modal,
        AniversarianteMensagem
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
        }
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
            mostrarMensagem: false,
            aniversarioWhatsapp: false,
            enviarPorEmail: false,
            enviarPorWhatsapp: false,

            urlPaginacao: `${URL_ADMIN}/administracao/aniversariantes/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    computed: {
        canalEnvio() {
            if (!this.aniversarioWhatsapp) return 'email'
            if (this.enviarPorEmail && this.enviarPorWhatsapp) return 'ambos'
            if (this.enviarPorEmail) return 'email'
            if (this.enviarPorWhatsapp) return 'whatsapp'
            return ''
        },
        filtrarLista() {
            if (this.lista && this.lista.length) {
                return this.lista.filter((item) => item.nome.toLowerCase().includes(this.nome_filtrado.toLowerCase()))
            }
        }
    },
    methods: {
        podeSelecionar(item) {
            const emailPendente = item.enviado == 'Não' && !item.email_ignorado
            const whatsappPendente = this.aniversarioWhatsapp
                && item.whatsapp
                && item.whatsapp !== 'Não informado'
                && item.whatsapp_status !== 'enfileirado'
            return emailPendente || whatsappPendente
        },
        abrirModalParabens() {
            if (!this.selecionadosMassa.length) return
            this.enviarPorEmail = false
            this.enviarPorWhatsapp = false
            if (this.$refs && this.$refs.modalParabensMassa && typeof this.$refs.modalParabensMassa.abrirModal === 'function') {
                this.$refs.modalParabensMassa.abrirModal()
            }
        },
        enviar() {
            if (this.aniversarioWhatsapp && !this.canalEnvio) return
            this.preload = true
            axios
                .post(`${URL_ADMIN}/administracao/aniversariantes/enviaEmail`, {
                    selecionados: this.selecionadosMassa,
                    canal: this.aniversarioWhatsapp ? this.canalEnvio : 'email'
                })
                .then((response) => {
                    if (this.$refs && this.$refs.modalParabensMassa && typeof this.$refs.modalParabensMassa.fecharModal === 'function') {
                        this.$refs.modalParabensMassa.fecharModal()
                    }
                    const avisos = {
                        email: 'Estamos enviando a mensagem de parabéns por e-mail',
                        whatsapp: 'Estamos enviando a mensagem de parabéns por WhatsApp',
                        ambos: 'Estamos enviando a mensagem de parabéns por e-mail e WhatsApp'
                    }
                    mostraSucesso('', avisos[this.canalEnvio] || avisos.email)
                    this.atualizar()
                    this.selecionadosMassa = []
                    this.selecionaTudoMassa = false
                    this.preload = false
                })
                .catch((error) => (this.preload = false))
        },
        aniversariantes_do_dia() {
            this.lista = this.lista_original
            if (this.niver_dia !== 'todos') {
                this.lista = this.lista.filter((item) => item.hoje === this.niver_dia)
            }
        },
        async atualizar() {
            this.controle.carregando = true
            await axios
                .post(this.urlPaginacao)
                .then(({ data }) => {
                    this.lista = data.dados
                    this.lista_original = data.dados
                    this.aniversarioWhatsapp = !!data.aniversario_whatsapp
                    this.aniversariantes_do_dia()
                    this.controle.carregando = false
                })
                .catch()
        },

        selecionaTodosMassa() {
            this.selecionaTudoMassa = !this.selecionaTudoMassa
            if (this.selecionaTudoMassa) {
                this.lista.map((item) => {
                    let id = item.id
                    if (this.selecionadosMassa.indexOf(id) === -1 && this.podeSelecionar(item)) {
                        this.selecionadosMassa.push(id)
                    }
                })
            } else {
                this.lista.map((item) => {
                    let id = item.id
                    let index = this.selecionadosMassa.indexOf(id)
                    if (index >= 0) {
                        this.selecionadosMassa.splice(index, 1)
                    }
                })
            }
        }
    }
}
</script>
