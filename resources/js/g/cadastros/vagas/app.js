import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando Vaga',
            preloadAjax: false,
            editando: false,
            apagado: false,

            pages: 10,

            form: {
                nome: '',
                ativo: true,
                vencimentos: [],
                vencimento_ids: [],
                vencimento_id: '',
                autocomplete_label_vencimento: '',
                segmento_treinamento_id: ''
            },

            formDefault: null,
            campoNome: null,
            hash: `vaga_${parseInt(Math.random() * 999999)}`,

            cadastrado: false,
            atualizado: false,

            lista: [],
            segmentos: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: ''
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.carregarSegmentos()
        this.atualizar()
    },
    methods: {
        carregarSegmentos() {
            axios
                .get(`${URL_ADMIN}/cadastro/segmentostreinamento/lista`)
                .then((response) => {
                    this.segmentos = response.data || []
                })
                .catch(() => {
                    this.segmentos = []
                })
        },
        mudouSegmentoTreinamento() {
            this.form.autocomplete_label_vencimento = ''
        },
        caminhoAutocompleteVencimentos() {
            if (!this.form.segmento_treinamento_id) {
                return 'autocomplete/vencimentos-ativos'
            }

            return `autocomplete/vencimentos-ativos?segmento_treinamento_id=${this.form.segmento_treinamento_id}`
        },
        removerVencimento(index) {
            this.form.vencimentos.splice(index, 1)
            this.form.vencimento_ids = this.form.vencimentos.map((item) => item.id)
        },

        selecionaVencimento(obj) {
            const vencimento = {
                id: obj.id,
                label: obj.label || obj.nome,
                segmento_nome: obj.segmento_nome || obj.segmento_treinamento?.nome || 'Geral'
            }

            const atual = this.form.vencimentos.findIndex((val) => val.id === vencimento.id)
            if (atual >= 0) {
                mostraErro('', `O treinamento ${vencimento.label} já está na lista.`)
                this.form.autocomplete_label_vencimento = ''
                return
            }

            this.form.vencimentos.push(vencimento)
            this.form.vencimento_ids = this.form.vencimentos.map((item) => item.id)
            this.form.autocomplete_label_vencimento = ''
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.tituloJanela = 'Cadastrando Vaga'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false
        },
        cadastrar() {
            formReset()

            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true
            this.form.vencimento_ids = this.form.vencimentos.map((item) => item.id)
            axios
                .post(`${URL_ADMIN}/cadastro/vagas`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadAjax = false
                        this.cadastrado = true
                        this.atualizar()
                    }
                })
                .catch((error) => (this.preloadAjax = false))
        },
        formAlterar(id) {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando Vaga'
            this.preloadAjax = true
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true

            axios
                .get(`${URL_ADMIN}/cadastro/vagas/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.vencimentos = (response.data.vencimentos || []).map((item) => ({
                        id: item.id,
                        label: item.label,
                        segmento_nome: item.segmento_treinamento?.nome || 'Geral'
                    }))
                    this.form.vencimento_ids = this.form.vencimentos.map((item) => item.id)
                    this.form.autocomplete_label_vencimento = ''
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.form._method = 'PUT'
            this.form.vencimento_ids = this.form.vencimentos.map((item) => item.id)
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/cadastro/vagas/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        carregou(dados) {
            this.lista = dados
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
})

registerGlobals(app)
app.mount('#app')
