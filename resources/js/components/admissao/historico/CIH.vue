<template>
    <div>
        <preload class="mt-2" v-if="preload"></preload>

        <modal :id="hash" :titulo="tituloJanela" :fechar="!preloadAjax" size="g">
            <template #conteudo>
                <preload class="mt-2" v-if="preloadAjax"></preload>
                <div v-else>
                    <p><strong>Data da Ocorrência:</strong> {{ form.data_lancamento }}</p>
                    <p><strong>Tipo:</strong> {{ form.tag_id ? form.tag.label : form.outra_tag }}</p>
                    <p><strong>Área:</strong> {{ form.area_id ? form.area.label : form.outra_area }}</p>
                    <p><strong>Ação:</strong> {{ form.acao }}</p>
                    <p><strong>Observação Lançamento:</strong> {{ form.obs_lancamento }}</p>
                    <p><strong>Lançado por:</strong> {{ form.responsavel_lancamento.nome }} em {{ form.data_lancamento }}</p>
                    <hr />
                    <p>
                        <strong>Status: </strong>
                        <span v-if="form.status.includes('aprovado') || form.status.includes('reprovado')">
                            {{ capitalize(form.status) }} por {{ form.responsavel_aprovacao.nome }} em {{ form.updated_at }}
                        </span>
                    </p>
                    <p v-if="form.status.includes('aprovado') || form.status.includes('reprovado')">
                        Observação: <strong>{{ form.obs_lancamento }}</strong>
                    </p>
                </div>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">
            <div class="col-12 mb-2 mt-2 pt-1 pb-1 border-bottom">
                <span class="small">
                    Legenda:
                    <i class="fas fa-circle text-warning"></i> Aberto <i class="fas fa-circle text-success ml-2"></i> Aprovado
                    <i class="fas fa-circle text-danger ml-2"></i> Reprovado
                </span>
            </div>

            <div id="conteudo">
                <div class="alert alert-warning" v-show="lista.length === 0"><i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado</div>

                <div class="table-responsive" v-show="lista.length > 0">
                    <table class="table table-bordered table-hover table-condensed" style="font-size: 0.85em">
                        <thead>
                            <tr class="bg-default">
                                <th class="text-center">ID</th>
                                <th class="text-center">Lançamento</th>
                                <th class="text-center">Aprovação</th>
                                <th class="text-center">Status</th>
                                <th colspan="3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in lista"
                                :class="{
                                    'table-warning': item.status.includes('aberto'),
                                    'table-danger': item.status.includes('reprovado'),
                                    'table-success': item.status.includes('aprovado')
                                }"
                            >
                                <td class="text-center">
                                    {{ item.id }}
                                </td>

                                <td class="text-center">
                                    Lançado por {{ item.responsavel_lancamento.nome }} <br />
                                    em {{ item.data_lancamento }}
                                </td>

                                <td class="text-center">
                                    <span v-if="item.status.includes('aprovado') || item.status.includes('reprovado')">
                                        {{ capitalize(item.status) }} por {{ item.responsavel_aprovacao.nome }} <br />
                                        em {{ item.updated_at }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    {{ capitalize(item.status) }}
                                </td>

                                <td class="text-center">
                                    <a
                                        v-show="item.status === 'aprovado' || item.status === 'reprovado'"
                                        href="javascript://"
                                        class="btn btn-sm btn-primary"
                                        title="Visualizar"
                                        @click.prevent="formAprovar(item.id)"
                                        data-toggle="modal"
                                        :data-target="`#${hash}`"
                                    >
                                        <i class="fa fa-search"></i> Visualizar
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import autocomplete from '../../AutoComplete'
import DatePicker from '../../DatePicker'
import Upload from '../../Upload'

export default {
    props: {
        fc_token: {
            type: String,
            required: true
        },
        model: {
            type: Array
        },
        hash: {
            type: String,
            default: `mastertag_${parseInt(Math.random() * 999999)}`
        }
    },
    data() {
        return {
            tituloJanela: 'CIH',
            preloadAjax: false,
            editando: false,
            leitura: false,
            apagado: false,
            aprovando: false,
            preload: false,
            URL_ADMIN,

            form: {
                tag_id: '',
                outra_tag: '',
                area_id: '',
                outra_area: '',
                acao: '',
                user_lancamento_id: '',
                obs_lancamento: '',
                data_lancamento: '',
                user_aprovacao_id: '',
                obs_aprovacao: '',
                data_aprovacao: '',
                status: '',
                responsavel_lancamento: {
                    nome: ''
                },
                responsavel_aprovacao: {
                    nome: ''
                },
                status_aprovacao: '',
                anexos: [],
                anexosDel: []
            },

            formDefault: null,
            cadastrado: false,
            atualizado: false,

            lista: [],
            listaTags: [],
            listaAreas: [],
            listaClientes: []
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
    },
    methods: {
        capitalize(value) {
            if (!value) return ''
            const texto = value.toString()
            return texto.charAt(0).toUpperCase() + texto.slice(1)
        },
        formAprovar(id) {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = true
            this.tituloJanela = `Visualizando CIH #${id}`
            this.preloadAjax = true
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.preloadAjax = false
                })
                .catch((error) => (this.preloadAjax = false))
        },

        atualizar() {
            this.preload = true
            axios.get(`${URL_ADMIN}/historico/cih/${this.fc_token}`).then((res) => {
                let data = res.data
                this.lista = data.cih
                this.preload = false
            })
        }
    }
}
</script>

<style scoped></style>
