import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import datepicker from '../../../components/DatePicker'
import preload from '../../../components/preload'
import autoComplete from '../../../components/AutoComplete'
import escala from '../../../components/controle-ponto/Escala'

const app = createApp({
    components: {
        datepicker
    },
    data() {
        return {
            tituloJanela: 'Cadastrando feriado',

            form: {
                id: null,
                descricao: '',
                data: null,
                ativo: true,
                editando: false,
                cadastrado: false,
                atualizado: false,
                apagado: false,
                preload: false
            },
            formDefault: null,

            lista: [],
            controle: {
                carregando: false,
                dados: {}
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
    },
    methods: {
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },
        carregou(dados) {
            this.lista = dados
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault)
            this.tituloJanela = 'Cadastrando feriado'
        },
        cadastrar() {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.form.preload = true
            axios
                .post(`${URL_ADMIN}/controle-ponto/feriados`, this.form)
                .then((data) => {
                    this.form.preload = false
                    this.form.cadastrado = true
                    this.atualizar()
                })
                .catch((error) => {
                    this.form.preload = false
                })
        },
        formAlterar(id) {
            this.form = _.cloneDeep(this.formDefault)
            this.form.id = id

            this.tituloJanela = 'Alterando feriado'

            this.form.preload = true

            axios
                .get(`${URL_ADMIN}/controle-ponto/feriados/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.editando = true
                    this.form.preload = false
                    this.atualizar()
                })
                .catch((error) => {
                    this.form.preload = false
                })
        },
        alterar() {
            $('#janelaCadastrar :input:visible:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }
            this.form.preload = true

            axios
                .put(`${URL_ADMIN}/controle-ponto/feriados/${this.form.id}`, this.form)
                .then((response) => {
                    this.form.preload = false
                    this.form.atualizado = true
                    this.atualizar()
                })
                .catch((error) => {
                    this.form.preload = false
                })
        },
        janelaConfirmar(id) {
            this.form = _.cloneDeep(this.formDefault)
            this.form.id = id
        },
        apagar() {
            this.form.preload = true

            axios
                .delete(`${URL_ADMIN}/controle-ponto/feriados/${this.form.id}`)
                .then((response) => {
                    this.form.preload = false
                    this.form.apagado = true
                    this.atualizar()
                })
                .catch((error) => {
                    this.form.preload = false
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')

$().ready(function () {
    $('#janelaCadastrar').on('shown.bs.modal', function () {
        $('#descricao').focus() // ja foca no descricao quando a janela abrir
    })
    $('#btnAtualizar').on('click', atualizar)
    atualizar()

    $('#formBusca').on('submit', function (e) {
        e.preventDefault()
        app.controle.dados.campoBusca = $('#campoBusca').val()
        atualizar()
    })
})

function atualizar() {
    app.$refs.componente.atual = 1
    app.this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
}
