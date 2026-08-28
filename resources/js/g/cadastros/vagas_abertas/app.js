import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import { tinyPadrao } from '../../../utils'
import autocomplete from '../../../components/AutoComplete'
import Editor from '@tinymce/tinymce-vue'
import MixinConfig from '../../../mixins/Configuracoes'
import FiltroListagem from '../../../components/ui/FiltroListagem'
const app = createApp({
    components: {
        autocomplete,
        Editor
    },
    mixins: [MixinConfig],
    data() {
        return {
            tituloJanela: 'Cadastrando Vaga Aberta',
            preloadAjax: false,
            editando: false,
            apagado: false,

            pages: 10,

            cargos_ativos: `autocomplete/cargos_ativos`,
            todos_municipios: `autocomplete/todos-municipios`,

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            tinyPadrao,
            URL_SITE,

            form: {
                vaga_id: '',

                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',

                descricao: '',
                titulo: '',
                requerimentos: '',
                municipio_id: '',

                simulados: [],
                simuladosDelete: [],

                // projetos: [],
                // projetosDelete: [],

                ativo: true,
                ativo_sistema: true
            },

            formDefault: null,
            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            listaSimulados: [],
            listaProjetos: [],
            listaProjetosAdicionais: [],

            treinamentosCargo: [],

            /** Resumo do CBO do cargo (mesmos campos exibidos na tela de Cargos). */
            cargoCboResumo: {
                cbo_codigo: '',
                codigo_familia: '',
                cbo_titulo: '',
                cbo_familia: '',
                cbo_descricao_sumaria: ''
            },

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: ''
                }
            },

            dropdownAbertoKey: null
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
        document.addEventListener('click', this.onClickOutside)
        // this.listaVagas();
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        onSubmitFiltro() {
            this.atualizar()
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            this.dropdownAbertoKey = null
        },
        toggleDropdown(vagaId) {
            if (!vagaId) return
            const key = `vaga-aberta:${vagaId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(vagaId) {
            return this.dropdownAbertoKey === `vaga-aberta:${vagaId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirEdicaoVagaAberta(vagaId) {
            this.fecharDropdown()
            this.formAlterar(vagaId)
            this.$refs.janelaCadastrar?.abrirModal()
        },
        resumoTreinamentos(vaga) {
            const vinculados = Number(vaga?.treinamentos_vinculados_count ?? 0)
            const globais = Number(vaga?.treinamentos_globais_count ?? 0)
            const total = Number(vaga?.treinamentos_total_count ?? vinculados + globais)

            if (total === 0) {
                return 'Nenhum'
            }

            if (vinculados === 0 && globais > 0) {
                return globais === 1
                    ? '1 treinamento (todos os cargos)'
                    : `${globais} treinamentos (todos os cargos)`
            }

            if (globais === 0) {
                return vinculados === 1 ? '1 treinamento' : `${vinculados} treinamentos`
            }

            const labelTotal = total === 1 ? '1 treinamento' : `${total} treinamentos`
            const labelVinculados = vinculados === 1 ? '1 vinculado' : `${vinculados} vinculados`
            const labelGlobais = globais === 1 ? '1 global' : `${globais} globais`

            return `${labelTotal} (${labelVinculados}, ${labelGlobais})`
        },
        temCbo(vaga) {
            return !!(vaga?.cbo_vinculado || vaga?.cbo_codigo || vaga?.cbo_titulo)
        },
        urlVagaPublica(vaga) {
            if (!vaga?.slug) {
                return this.urlVaga
            }

            return `${this.urlVaga}/${vaga.slug}`
        },
        copiarLinkPublico(vaga) {
            this.copiarTexto(this.urlVagaPublica(vaga), 'Link copiado!')
        },
        copiarTexto(texto, mensagem = 'Link copiado!') {
            if (!texto) {
                return
            }

            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(texto)
                    .then(() => {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(mensagem)
                        }
                    })
                    .catch(() => this.copiarTextoFallback(texto, mensagem))

                return
            }

            this.copiarTextoFallback(texto, mensagem)
        },
        copiarTextoFallback(texto, mensagem = 'Link copiado!') {
            const el = document.createElement('textarea')
            el.value = texto
            el.setAttribute('readonly', '')
            el.style.position = 'absolute'
            el.style.left = '-9999px'
            document.body.appendChild(el)
            el.select()
            document.execCommand('copy')
            document.body.removeChild(el)

            if (typeof toastr !== 'undefined') {
                toastr.success(mensagem)
            }
        },
        copiarLinkPublicoFallback(url) {
            this.copiarTextoFallback(url, 'Link copiado!')
        },
        textoCompartilharVaga(vaga) {
            const partes = []

            if (vaga?.titulo) {
                partes.push(`Vaga: ${vaga.titulo}`)
            }
            if (vaga?.cargo_nome) {
                partes.push(`Cargo: ${vaga.cargo_nome}`)
            }
            if (vaga?.municipio_label) {
                partes.push(`Local: ${vaga.municipio_label}`)
            }

            partes.push(this.urlVagaPublica(vaga))

            return partes.join('\n')
        },
        urlCompartilharWhatsapp(vaga) {
            return `https://api.whatsapp.com/send?text=${encodeURIComponent(this.textoCompartilharVaga(vaga))}`
        },
        urlCompartilharFacebook(vaga) {
            return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.urlVagaPublica(vaga))}`
        },
        urlCompartilharLinkedin(vaga) {
            return `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(this.urlVagaPublica(vaga))}`
        },
        compartilharInstagram(vaga) {
            this.copiarTexto(
                this.urlVagaPublica(vaga),
                'Link copiado! Cole no Instagram para compartilhar.'
            )
        },
        resumoSimulados(vaga) {
            const total = Number(vaga?.simulados_count ?? 0)
            const ativos = Number(vaga?.simulados_ativos_count ?? 0)

            if (total === 0) {
                return 'Nenhuma prova'
            }

            if (ativos === total) {
                return total === 1 ? '1 prova' : `${total} provas`
            }

            if (ativos === 0) {
                return total === 1 ? '1 prova (inativa)' : `${total} provas (todas inativas)`
            }

            return `${ativos} de ${total} provas ativas`
        },
        addLISimulado() {
            const obj = {}
            obj.novo = true

            obj.vaga_id = this.form.vaga_id
            obj.vagas_abertas_id = this.form.id
            obj.simulado_id = ''
            obj.duracao = 30
            obj.tipo_prova = ''
            obj.online = true
            obj.ativo = false

            this.form.simulados.push(obj)
        },

        removerLISimulado(index) {
            if (this.editando && !this.form.simulados[index].novo) {
                this.form.simuladosDelete.push(this.form.simulados[index].id)
            }
            this.form.simulados.splice(index, 1)
        },

        addLIProjeto() {
            const obj = {}
            obj.novo = true

            obj.projeto_id = ''
            obj.qnt_disponivel = ''
            obj.qnt_total = ''

            this.form.projetos.push(obj)
        },

        removerLIProjeto(index) {
            if (this.editando && !this.form.projetos[index].novo) {
                this.form.projetosDelete.push(this.form.projetos[index].id)
            }
            this.form.projetos.splice(index, 1)
        },

        selecionaProjeto(projeto_id, index) {
            let projeto = _.find(this.listaProjetos, { id: projeto_id })
            this.form.projetos[index].qnt_disponivel = projeto.qnt_total_restante
        },

        verificaQuantidadeVagas(qnt_disponivel, qnt_informado, projeto_id) {
            if (qnt_informado > qnt_disponivel) {
                let projeto = _.find(this.listaProjetosAdicionais, { id: projeto_id })
                mostraErro('', 'Não há quantidade disponível para o projeto: ' + projeto.nome)
                return false
            }
        },

        limparCargoCboResumo() {
            this.cargoCboResumo = {
                cbo_codigo: '',
                codigo_familia: '',
                cbo_titulo: '',
                cbo_familia: '',
                cbo_descricao_sumaria: ''
            }
        },

        preencherCargoCboResumoDeVaga(vaga) {
            this.limparCargoCboResumo()
            if (!vaga) {
                return
            }
            const cbo = vaga.cbo || vaga.Cbo
            if (!cbo) {
                return
            }
            const fam = cbo.familia || cbo.Familia || {}
            this.cargoCboResumo = {
                cbo_codigo: cbo.codigo != null ? String(cbo.codigo) : '',
                codigo_familia: cbo.codigo_familia != null ? String(cbo.codigo_familia) : '',
                cbo_titulo: cbo.titulo || '',
                cbo_familia: fam.titulo || '',
                cbo_descricao_sumaria: fam.descricao_sumaria || ''
            }
        },

        selecionaVagaModal(obj) {
            this.form.vaga_id = obj.id
            this.form.autocomplete_label_vaga_modal = obj.label
            this.form.autocomplete_label_vaga_modal_anterior = obj.label
            this.preencherCargoCboResumoDeVaga(obj)
            this.carregarTreinamentosCargo(obj.id)
        },
        carregarTreinamentosCargo(vagaId) {
            if (!vagaId) {
                this.treinamentosCargo = []
                return
            }
            axios
                .get(`${URL_ADMIN}/cadastro/vagas-abertas/cargo/${vagaId}/treinamentos`)
                .then((res) => {
                    this.treinamentosCargo = res.data || []
                })
                .catch(() => {
                    this.treinamentosCargo = []
                })
        },
        resetaCampoVagaModal() {
            if (this.form.autocomplete_label_vaga_modal_anterior !== this.form.autocomplete_label_vaga_modal) {
                this.form.autocomplete_label_vaga_modal_anterior = ''
                this.form.autocomplete_label_vaga_modal = ''
                this.form.vaga_id = ''
                this.treinamentosCargo = []
                this.limparCargoCboResumo()

                setTimeout(() => {
                    if (this.form.vaga_id === '') {
                        valida_campo_vazio($('#' + this.hash), 1)
                        $('#janelaCadastrar #' + this.hash)
                            .focus()
                            .trigger('blur')
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio')
                    }
                }, 100)
            }
        },

        selecionaMunicipioModal(obj) {
            this.form.municipio_id = obj.id
            this.form.autocomplete_label_municipio_modal = obj.label
            this.form.autocomplete_label_municipio_modal_anterior = obj.label
        },
        resetaCampoMunicipioModal() {
            if (this.form.autocomplete_label_municipio_modal_anterior !== this.form.autocomplete_label_municipio_modal) {
                this.form.autocomplete_label_municipio_modal_anterior = ''
                this.form.autocomplete_label_municipio_modal = ''
                this.form.municipio_id = ''

                setTimeout(() => {
                    if (this.form.municipio_id === '') {
                        valida_campo_vazio($('#mun_' + this.hash), 1)
                        $('#janelaCadastrar #mun_' + this.hash)
                            .focus()
                            .trigger('blur')
                        mostraErro('Erro', 'O Campo Cidade não pode ficar vazio')
                    }
                }, 100)
            }
        },

        listaVagas() {
            this.preloadAjax = true
            axios
                .get(`${URL_PUBLICO}/cadastro/lista-vagas`)
                .then((response) => {
                    let data = response.data
                    this.preloadAjax = false
                    this.vagas = data.vagas
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.tituloJanela = 'Cadastrando Vaga Aberta'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.treinamentosCargo = []
            this.limparCargoCboResumo()
            this.leitura = false
        },
        cadastrar() {
            formReset()
            if (this.form.vaga_id === '') {
                valida_campo_vazio($('#' + this.hash), 1)
                $('#janelaCadastrar #' + this.hash)
                    .focus()
                    .trigger('blur')
                mostraErro('Erro', 'O campo vaga não pode ficar vazio')
                return false
            }
            if (this.form.municipio_id === '') {
                valida_campo_vazio($('#mun_' + this.hash), 1)
                $('#janelaCadastrar #mun_' + this.hash)
                    .focus()
                    .trigger('blur')
                mostraErro('Erro', 'O Campo Cidade não pode ficar vazio')
                return false
            }

            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/cadastro/vagas-abertas`, this.form)
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
                .get(`${URL_ADMIN}/cadastro/vagas-abertas/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.autocomplete_label_vaga_modal = response.data.vaga.nome
                    this.form.autocomplete_label_vaga_modal_anterior = response.data.vaga.nome

                    const vencimentos = response.data.vaga.vencimentos || []
                    this.treinamentosCargo = vencimentos.map((v) => ({
                        id: v.id,
                        label: v.label,
                        padrao_treinamento: v.segmento_treinamento && v.segmento_treinamento.nome ? v.segmento_treinamento.nome : 'Geral',
                        todos_cargos: !!v.vinculo_todos_cargos
                    }))

                    this.form.autocomplete_label_municipio_modal = response.data.municipio.nome + ' - ' + response.data.municipio.uf
                    this.form.autocomplete_label_municipio_modal_anterior = response.data.municipio.nome + ' - ' + response.data.municipio.uf
                    this.preencherCargoCboResumoDeVaga(response.data.vaga)
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
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/cadastro/vagas-abertas/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        selecionaSimulado(simulado_id, index) {
            let simulado = _.find(this.listaSimulados, { id: simulado_id })
            this.form.simulados[index].tipo_prova = simulado.tipo_prova
        },

        imprimeProva(simulado, vaga_aberta) {
            window.location.href = `${URL_ADMIN}/cadastro/vagas-abertas/prova/${simulado}/${vaga_aberta}`
        },

        carregou(dados) {
            this.lista = dados.itens
            this.listaSimulados = dados.simulados
            this.listaProjetos = dados.projetos
            this.listaProjetosAdicionais = dados.projetos
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
app.component('FiltroListagem', FiltroListagem)
app.mount('#app')
