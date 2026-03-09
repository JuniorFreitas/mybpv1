import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const BASE_URL = `${URL_ADMIN}/habilidades`

function tratarRespostaApi(data, fallbackMsg) {
    if (data?.erro === 's') {
        return { ok: false, erros: data.erros || [], msg: data.msg || fallbackMsg }
    }
    return { ok: true }
}

const app = createApp({
    data() {
        return {
            tituloJanela: 'Cadastrando habilidade',
            preloadAjax: false,
            editando: false,
            id: 0,
            cadastrado: false,
            atualizado: false,
            apagado: false,
            erros: [],
            lista: [],
            controle: {
                carregando: false,
                dados: {}
            }
        }
    },

    mounted() {
        $('#janelaCadastrar').on('shown.bs.modal', () => $('#nome').focus())
        $('#btnAtualizar').on('click', () => this.atualizar())
        $('#formBusca').on('submit', (e) => {
            e.preventDefault()
            this.controle.dados.campoBusca = $('#campoBusca').val()
            this.atualizar()
        })
        this.atualizar()
    },

    methods: {
        atualizar() {
            const comp = this.$refs?.componente
            if (!comp) return
            comp.atual = 1
            if (typeof comp.buscar === 'function') comp.buscar()
        },

        formNovo() {
            $('#form')[0]?.reset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Cadastrando habilidade'
            formReset()
        },

        validarFormModal() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }
            return true
        },

        async cadastrar() {
            if (!this.validarFormModal()) return
            this.erros = []
            const dados = {
                nome: $('#nome').val(),
                descricao: $('#descricao').val()
            }
            this.preloadAjax = true
            try {
                const { data } = await axios.post(BASE_URL, dados)
                const result = tratarRespostaApi(data, 'Erro ao cadastrar.')
                if (!result.ok) {
                    this.erros = result.erros
                    alert(result.msg)
                    return
                }
                this.cadastrado = true
                this.atualizar()
            } catch (err) {
                const res = err.response?.data || {}
                this.erros = res.erros || []
                alert(res.msg || 'Erro ao cadastrar.')
            } finally {
                this.preloadAjax = false
            }
        },

        async formAlterar(id) {
            this.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando habilidade'
            this.erros = []
            this.preloadAjax = true
            formReset()

            try {
                const { data } = await axios.get(`${BASE_URL}/${id}/editar`)
                const result = tratarRespostaApi(data, 'Erro ao carregar.')
                if (!result.ok) {
                    this.erros = result.erros
                    alert(result.msg)
                    return
                }
                $('#nome').val(data.nome)
                $('#descricao').val(data.descricao)
                this.editando = true
            } catch (err) {
                const res = err.response?.data || {}
                this.erros = res.erros || []
                alert(res.msg || 'Erro ao carregar.')
            } finally {
                this.preloadAjax = false
            }
        },

        async alterar() {
            if (!this.validarFormModal()) return
            this.erros = []
            const dados = {
                nome: $('#nome').val(),
                descricao: $('#descricao').val()
            }
            this.preloadAjax = true
            try {
                const { data } = await axios.put(`${BASE_URL}/${this.id}`, dados)
                const result = tratarRespostaApi(data, 'Erro ao alterar.')
                if (!result.ok) {
                    this.erros = result.erros
                    alert(result.msg)
                    return
                }
                this.atualizado = true
                this.atualizar()
            } catch (err) {
                const res = err.response?.data || {}
                this.erros = res.erros || []
                alert(res.msg || 'Erro ao alterar.')
            } finally {
                this.preloadAjax = false
            }
        },

        janelaConfirmar(id) {
            this.id = id
            this.apagado = false
            this.erros = []
            this.preloadAjax = false
        },

        async apagar() {
            this.erros = []
            this.preloadAjax = true
            try {
                const { data } = await axios.delete(`${BASE_URL}/${this.id}`)
                const result = tratarRespostaApi(data, 'Erro ao apagar.')
                if (!result.ok) {
                    this.erros = result.erros
                    alert(result.msg)
                    return
                }
                this.apagado = true
                this.atualizar()
            } catch (err) {
                const res = err.response?.data || {}
                this.erros = res.erros || []
                alert(res.msg || 'Erro ao apagar.')
            } finally {
                this.preloadAjax = false
            }
        },

        carregou(dados) {
            this.lista = dados
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        }
    }
})

registerGlobals(app)
app.mount('#app')
