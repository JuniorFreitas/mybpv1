import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import autocomplete from '../../../components/AutoComplete'
import MixinConfig from '../../../mixins/Configuracoes'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete'
import FiltroListagem from '../../../components/ui/FiltroListagem'
import { lerFiltrosDaUrl, lerPaginacaoDaUrl, sincronizarFiltrosNaUrl, criarWatchQueryParams, montarExtrasPaginacao, aplicarPaginaInicialListagem, buscarListagem, temFiltrosPreenchidos, limparFiltrosListagem } from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = [
    'campoBusca',
    'campoAtivoSite',
    'campoAtivoSistema',
    'campoCargoId',
    'campoMunicipioId',
    'campoComProvas',
    'campoProjetoId'
]
const PAGES_DEFAULT = 100

const app = createApp({
    components: {
        autocomplete,
        ComboboxAutoComplete
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

            filtroOpcoes: {
                cargos: [],
                municipios: [],
                projetos: []
            },

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
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

                projetos: [],
                projetosDelete: [],

                ativo: true,
                ativo_sistema: true
            },

            formDefault: null,
            campoNome: null,

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
                    campoAtivoSite: '',
                    campoAtivoSistema: '',
                    campoCargoId: '',
                    campoMunicipioId: '',
                    campoComProvas: '',
                    campoProjetoId: '',
                    pages: PAGES_DEFAULT
                }
            },

            dropdownAbertoKey: null
        }
    },
    computed: {
        filtroAtivoSiteOpcoes() {
            return [
                { value: '', label: 'Todos' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        },
        filtroAtivoSistemaOpcoes() {
            return [
                { value: '', label: 'Todos' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        },
        filtroCargoOpcoes() {
            const cargos = (this.filtroOpcoes.cargos || []).map((cargo) => ({
                value: cargo.id,
                label: cargo.nome
            }))

            return [{ value: '', label: 'Todos os cargos' }, ...cargos]
        },
        filtroMunicipioOpcoes() {
            const municipios = (this.filtroOpcoes.municipios || []).map((municipio) => ({
                value: municipio.id,
                label: `${municipio.nome} - ${municipio.uf}`
            }))

            return [{ value: '', label: 'Todas as cidades' }, ...municipios]
        },
        filtroComProvasOpcoes() {
            return [
                { value: '', label: 'Todas' },
                { value: 'sim', label: 'Com provas' },
                { value: 'nao', label: 'Sem provas' }
            ]
        },
        filtroProjetoOpcoes() {
            const fonte =
                this.listaProjetos && this.listaProjetos.length
                    ? this.listaProjetos
                    : this.filtroOpcoes.projetos || []

            const projetos = fonte.map((projeto) => {
                const disponivel = this.quantidadeLivreProjeto(projeto)

                return {
                    value: projeto.id,
                    label: projeto.nome,
                    meta:
                        disponivel > 0
                            ? `${disponivel} vaga${disponivel === 1 ? '' : 's'} disponível${disponivel === 1 ? '' : 'eis'}`
                            : 'sem vagas disponíveis'
                }
            })

            return [
                { value: '', label: 'Todos os projetos' },
                { value: 'com_vinculo', label: 'Com vínculo' },
                { value: 'sem_vinculo', label: 'Sem vínculo' },
                ...projetos
            ]
        },
        temProjetosDisponiveis() {
            return (this.listaProjetos || []).some((projeto) => this.quantidadeLivreProjeto(projeto) > 0)
        },
        podeAdicionarNovoProjeto() {
            if (this.form.projetos.length === 0) {
                return this.temProjetosDisponiveis || this.editando
            }

            const ultimaLinha = this.form.projetos[this.form.projetos.length - 1]

            return this.projetoLinhaCompleta(ultimaLinha) && this.existeProjetoDisponivelParaAdicionar()
        },
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
        }
    },
    mounted() {
        this.urlParamGetFiltros()
        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
        this.formDefault = _.cloneDeep(this.form) //copia
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })
        document.addEventListener('click', this.onClickOutside)
        // this.listaVagas();
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    watch: {
        'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })
    },
    methods: {
        urlParamGetFiltros() {
            lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        },
        syncUrlFiltros() {
            sincronizarFiltrosNaUrl(
                this.controle.dados,
                CAMPOS_FILTRO_URL,
                montarExtrasPaginacao(this, { pagesDefault: PAGES_DEFAULT })
            )
        },
        onSubmitFiltro() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [
                ['filtro-ativo-site', 'comboFiltroAtivoSite'],
                ['filtro-ativo-sistema', 'comboFiltroAtivoSistema'],
                ['filtro-cargo', 'comboFiltroCargo'],
                ['filtro-municipio', 'comboFiltroMunicipio'],
                ['filtro-provas', 'comboFiltroProvas'],
                ['filtro-projeto', 'comboFiltroProjeto']
            ]

            combos.forEach(([id, refName]) => {
                if (manter !== id && this.$refs[refName]?.close) {
                    this.$refs[refName].close()
                }
            })
        },
        comboboxContemAlvo(event) {
            const refs = [
                'comboFiltroAtivoSite',
                'comboFiltroAtivoSistema',
                'comboFiltroCargo',
                'comboFiltroMunicipio',
                'comboFiltroProvas',
                'comboFiltroProjeto'
            ]

            return refs.some((refName) => this.$refs[refName]?.containsTarget?.(event.target))
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (this.comboboxContemAlvo(event)) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
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
        resumoDescricao(vaga) {
            if (vaga?.descricao_tem_conteudo && vaga?.descricao_resumo) {
                return vaga.descricao_resumo
            }

            return 'Sem descrição'
        },

        descricaoCardVazia(vaga) {
            return !vaga?.descricao_tem_conteudo
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
        resumoProjetos(vaga) {
            const total = Number(vaga?.projetos_count ?? 0)

            if (total === 0) {
                return 'Nenhum projeto'
            }

            return total === 1 ? '1 projeto' : `${total} projetos`
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
            if (!this.podeAdicionarNovoProjeto) {
                if (!this.existeProjetoDisponivelParaAdicionar()) {
                    mostraErro('', 'Não há projetos com vagas disponíveis para vincular.')
                } else {
                    mostraErro('', 'Preencha o projeto e a quantidade antes de adicionar outro.')
                }
                return
            }

            this.form.projetos.push({
                novo: true,
                projeto_id: '',
                qnt_disponivel: '',
                qnt_total: '',
                qnt_preenchida: 0
            })
        },

        removerLIProjeto(index) {
            const linha = this.form.projetos[index]

            if (this.projetoPossuiVinculos(linha)) {
                mostraErro(
                    '',
                    `Não é possível remover "${this.nomeProjetoPorId(linha.projeto_id, linha)}" porque já possui ${linha.qnt_preenchida} vaga(s) preenchida(s).`
                )
                return
            }

            if (this.editando && !linha.novo) {
                this.form.projetosDelete.push(linha.id)
            }
            this.form.projetos.splice(index, 1)
        },

        projetoPossuiVinculos(obj) {
            return Number(obj?.qnt_preenchida) > 0
        },

        podeRemoverProjeto(obj) {
            return !this.projetoPossuiVinculos(obj)
        },

        quantidadeMinimaProjeto(obj) {
            const preenchidas = Number(obj?.qnt_preenchida) || 0

            if (this.projetoLinhaExistente(obj)) {
                return preenchidas
            }

            return Math.max(1, preenchidas)
        },

        quantidadeLivreProjetoLinha(obj, index = null) {
            const projeto = this.buscarProjeto(obj?.projeto_id)

            if (!projeto) {
                return 0
            }

            if (this.projetoLinhaExistente(obj) && obj.qnt_livre_projeto != null) {
                return Math.max(0, Number(obj.qnt_livre_projeto) || 0)
            }

            let livre = this.quantidadeLivreProjeto(projeto)

            if (!this.projetoLinhaExistente(obj)) {
                const alocadaFormulario = this.quantidadeAlocadaFormulario(obj.projeto_id, index)
                livre = Math.max(0, livre - alocadaFormulario)
            }

            return livre
        },

        mensagemProjetoExistente(obj) {
            const preenchidas = Number(obj.qnt_preenchida) || 0

            if (preenchidas > 0) {
                return `Este vínculo possui ${preenchidas} vaga(s) preenchida(s). Não é possível remover. A quantidade total deve ficar entre ${preenchidas} e o livre do projeto.`
            }

            return 'Vínculo salvo. A quantidade total não pode ser menor que as preenchidas nem maior que o livre do projeto.'
        },

        projetoLinhaCompleta(obj) {
            if (!obj) {
                return false
            }

            const qnt = parseInt(obj.qnt_total, 10)

            return Boolean(obj.projeto_id) && !Number.isNaN(qnt) && qnt >= 1
        },

        projetoLinhaExistente(obj) {
            return Boolean(this.editando && obj?.id && !obj?.novo)
        },

        projetoIdsSelecionados(excluirIndex = null) {
            return this.form.projetos
                .filter((_, index) => index !== excluirIndex)
                .map((item) => Number(item.projeto_id))
                .filter(Boolean)
        },

        projetosOpcoesPara(index) {
            const selecionados = this.projetoIdsSelecionados(index)
            const linha = this.form.projetos[index]
            const projetoAtual = Number(linha?.projeto_id)

            return (this.listaProjetos || []).filter((projeto) => {
                const id = Number(projeto.id)

                if (id === projetoAtual) {
                    return true
                }

                return !selecionados.includes(id)
            })
        },

        projetoOpcaoDesabilitada(projeto, linha) {
            const id = Number(projeto.id)
            const selecionado = Number(linha?.projeto_id)

            if (selecionado === id) {
                return false
            }

            return this.quantidadeLivreProjeto(projeto) <= 0
        },

        existeProjetoDisponivelParaAdicionar() {
            const selecionados = this.projetoIdsSelecionados()

            return (this.listaProjetos || []).some((projeto) => {
                return this.quantidadeLivreProjeto(projeto) > 0 && !selecionados.includes(Number(projeto.id))
            })
        },

        buscarProjeto(projetoId) {
            const id = Number(projetoId)

            if (!id) {
                return null
            }

            return _.find(this.listaProjetos, (projeto) => Number(projeto.id) === id)
        },

        nomeProjetoPorId(projetoId, obj = null) {
            if (obj?.projeto_nome) {
                return obj.projeto_nome
            }

            const rel = obj?.projeto || obj?.Projeto
            if (rel?.nome) {
                return rel.nome
            }

            const projeto = this.buscarProjeto(projetoId)

            return projeto?.nome || 'Projeto'
        },

        labelOpcaoProjeto(projeto) {
            const disponivel = this.quantidadeLivreProjeto(projeto)
            const sufixo =
                disponivel > 0
                    ? ` (${disponivel} vaga${disponivel === 1 ? '' : 's'} disponível${disponivel === 1 ? '' : 'eis'})`
                    : ' (sem vagas disponíveis)'

            return `${projeto.nome}${sufixo}`
        },

        capacidadeProjeto(obj) {
            const projeto = this.buscarProjeto(obj?.projeto_id)

            if (!projeto) {
                return 0
            }

            if (obj?.projeto_qnt_total != null) {
                return Number(obj.projeto_qnt_total) || 0
            }

            return Number(projeto.qnt_total) || 0
        },

        quantidadeLivreProjeto(projeto) {
            if (!projeto) {
                return 0
            }

            if (projeto.qnt_disponivel_projeto != null) {
                return Math.max(0, Number(projeto.qnt_disponivel_projeto) || 0)
            }

            const capacidade = Number(projeto.qnt_total) || 0
            const alocada = Number(projeto.qnt_alocada) || 0

            if (alocada > 0 || capacidade > 0) {
                return Math.max(0, capacidade - alocada)
            }

            return Math.max(0, Number(projeto.qnt_total_restante) || 0)
        },

        quantidadeAlocadaFormulario(projetoId, excluirIndex = null) {
            return this.form.projetos.reduce((total, item, index) => {
                if (index === excluirIndex) {
                    return total
                }

                if (Number(item.projeto_id) !== Number(projetoId)) {
                    return total
                }

                const qnt = parseInt(item.qnt_total, 10)

                return total + (Number.isNaN(qnt) ? 0 : qnt)
            }, 0)
        },

        quantidadeMaximaProjeto(obj, index = null) {
            const minimo = this.quantidadeMinimaProjeto(obj)
            const livre = this.quantidadeLivreProjetoLinha(obj, index)

            if (this.projetoLinhaExistente(obj) && obj.qnt_maxima_permitida != null) {
                return Math.max(minimo, Number(obj.qnt_maxima_permitida) || minimo)
            }

            return Math.max(minimo, livre)
        },

        quantidadeDisponivelProjeto(obj, index = null) {
            return this.quantidadeMaximaProjeto(obj, index)
        },

        inicializarProjetosFormulario() {
            this.form.projetos = (this.form.projetos || []).map((item) => {
                const linha = { ...item }
                linha.projeto_id = linha.projeto_id || ''
                linha.projeto_nome = this.nomeProjetoPorId(linha.projeto_id, linha)
                linha.qnt_disponivel = this.quantidadeMaximaProjeto(linha)
                linha.qnt_preenchida = Number(linha.qnt_preenchida) || 0

                return linha
            })
        },

        selecionaProjeto(projeto_id, index) {
            const linha = this.form.projetos[index]

            if (!projeto_id) {
                linha.qnt_disponivel = ''
                linha.qnt_total = ''
                linha.projeto_nome = ''
                return
            }

            if (this.projetoIdsSelecionados(index).includes(Number(projeto_id))) {
                mostraErro('', 'Este projeto já foi adicionado nesta vaga.')
                linha.projeto_id = ''
                linha.qnt_disponivel = ''
                linha.qnt_total = ''
                linha.projeto_nome = ''
                return
            }

            linha.projeto_nome = this.nomeProjetoPorId(projeto_id)
            linha.qnt_disponivel = this.quantidadeMaximaProjeto(linha, index)

            if (this.quantidadeLivreProjeto(this.buscarProjeto(projeto_id)) <= 0) {
                mostraErro('', 'Este projeto não possui vagas disponíveis no momento.')
                linha.projeto_id = ''
                linha.qnt_disponivel = ''
                linha.qnt_total = ''
                linha.projeto_nome = ''
                return
            }

            const qntAtual = parseInt(linha.qnt_total, 10)
            if (!Number.isNaN(qntAtual) && qntAtual > linha.qnt_disponivel) {
                linha.qnt_total = linha.qnt_disponivel > 0 ? linha.qnt_disponivel : ''
            }
        },

        ajustarQuantidadeProjeto(index) {
            const linha = this.form.projetos[index]

            if (!linha) {
                return
            }

            if (!linha.projeto_id) {
                mostraErro('', 'Selecione um projeto antes de informar a quantidade.')
                linha.qnt_total = ''
                return
            }

            let qnt = parseInt(linha.qnt_total, 10)
            const minimo = this.quantidadeMinimaProjeto(linha)
            const maximo = this.quantidadeMaximaProjeto(linha, index)

            if (Number.isNaN(qnt)) {
                linha.qnt_total = ''
                return
            }

            if (qnt < minimo) {
                mostraErro(
                    '',
                    `Quantidade mínima para "${this.nomeProjetoPorId(linha.projeto_id, linha)}": ${minimo} vaga(s) preenchida(s).`
                )
                linha.qnt_total = minimo
                return
            }

            if (qnt > maximo) {
                mostraErro(
                    '',
                    `Quantidade máxima para "${this.nomeProjetoPorId(linha.projeto_id, linha)}": ${maximo} vaga(s) livre(s) no projeto.`
                )
                linha.qnt_total = maximo > 0 ? maximo : minimo
            }
        },

        validarProjetosFormulario() {
            const itens = this.form.projetos || []

            for (let index = 0; index < itens.length; index++) {
                const linha = itens[index]

                if (!linha.projeto_id) {
                    mostraErro('', `Selecione o projeto na linha #${index + 1}.`)
                    return false
                }

                const qnt = parseInt(linha.qnt_total, 10)
                const minimo = this.quantidadeMinimaProjeto(linha)
                const maximo = this.quantidadeMaximaProjeto(linha, index)

                if (Number.isNaN(qnt) || qnt < minimo) {
                    mostraErro('', `A quantidade na linha #${index + 1} não pode ser menor que ${minimo} (preenchidas).`)
                    return false
                }

                if (qnt > maximo) {
                    mostraErro(
                        '',
                        `A quantidade na linha #${index + 1} não pode ser maior que ${maximo} (livre do projeto).`
                    )
                    return false
                }
            }

            const ids = itens.map((item) => Number(item.projeto_id)).filter(Boolean)

            if (ids.length !== new Set(ids).size) {
                mostraErro('', 'Não é permitido vincular o mesmo projeto mais de uma vez.')
                return false
            }

            return true
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
            this.editando = false

            this.tituloJanela = 'Cadastrando Vaga Aberta'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.treinamentosCargo = []
            this.limparCargoCboResumo()
            this.leitura = false
            if (!this.listaProjetos.length) {
                this.atualizar()
            }
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

            if (!this.validarProjetosFormulario()) {
                return false
            }

            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/cadastro/vagas-abertas`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadAjax = false
                        this.$refs.janelaCadastrar?.fecharModal()
                        mostraSucesso('', 'Vaga cadastrada com sucesso')
                        this.atualizar()
                    }
                })
                .catch((error) => {
                    this.preloadAjax = false
                    const msg = error?.response?.data?.msg
                    if (msg) {
                        mostraErro('', msg)
                    }
                })
        },
        formAlterar(id) {
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
                    this.form.simulados = response.data.simulados || response.data.Simulados || []
                    this.form.simuladosDelete = []
                    this.form.projetos = response.data.projetos || response.data.Projetos || []
                    this.form.projetosDelete = []
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
                    this.inicializarProjetosFormulario()
                    this.editando = true
                    this.preloadAjax = false
                    if (!this.listaProjetos.length) {
                        this.atualizar()
                    }
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

            if (!this.validarProjetosFormulario()) {
                return false
            }

            this.form._method = 'PUT'
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/cadastro/vagas-abertas/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.$refs.janelaCadastrar?.fecharModal()
                    mostraSucesso('', 'Vaga alterada com sucesso')
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                    const msg = error?.response?.data?.msg
                    if (msg) {
                        mostraErro('', msg)
                    }
                })
        },

        selecionaSimulado(simulado_id, index) {
            let simulado = _.find(this.listaSimulados, { id: simulado_id })
            this.form.simulados[index].tipo_prova = simulado.tipo_prova
        },

        imprimeProva(simulado, vaga_aberta) {
            window.location.href = `${URL_ADMIN}/cadastro/vagas-abertas/prova/${simulado}/${vaga_aberta}`
        },

        carregou(dados) {
            if (!dados || typeof dados !== 'object' || Array.isArray(dados)) {
                this.controle.carregando = false
                return
            }

            this.lista = dados.itens || []
            this.listaSimulados = dados.simulados || []
            this.listaProjetos = dados.projetos || []
            this.listaProjetosAdicionais = dados.projetos || []
            if (dados.filtros) {
                this.filtroOpcoes.cargos = dados.filtros.cargos || []
                this.filtroOpcoes.municipios = dados.filtros.municipios || []
                this.filtroOpcoes.projetos = dados.filtros.projetos || []
            }
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            buscarListagem(this, { resetPagina: true })
        }
    }
})

registerGlobals(app)
app.component('FiltroListagem', FiltroListagem)
app.mount('#app')
