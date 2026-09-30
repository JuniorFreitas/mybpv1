import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

// Constantes da aplicação
const CONSTANTS = {
    TITULOS: {
        CADASTRAR: 'Novo grupo de usuários',
        ALTERAR_PREFIXO: 'Editar grupo'
    },
    MENSAGENS: {
        SUCESSO_CADASTRO: 'Grupo cadastrado com sucesso!',
        SUCESSO_ALTERACAO: 'Grupo alterado com sucesso!',
        ERRO_VALIDACAO: 'Verificar os erros'
    },
    STATUS: {
        CRIADO: 201
    },
    ELEMENTOS: {
        JANELA_CADASTRAR: '#janelaCadastrar',
        ABA_IDENTIFICACAO: '#aba-identificacao-tab'
    },
    /** Sempre ligada em todo grupo (usuário precisa alterar a própria senha). */
    HABILIDADE_OBRIGATORIA_ALTERAR_SENHA: 'usuario_alterar-senha'
}

const app = createApp({
    data() {
        return {
            tituloJanela: '',
            empresa_id: '',
            preloadAjax: false,
            /** Texto do overlay enquanto carrega ou salva (evita UI duplicada / faixa presa). */
            mensagemOverlayModal: 'Aguarde…',
            editando: false,

            cadastrado: false,
            atualizado: false,
            urlAjax: '',
            apagado: false,

            /** Evita texto de “fluxo novo” enquanto carrega edição (editando só vira true após o AJAX). */
            mostrarDicasFluxoNovo: false,

            form: {
                id: '',
                nome: '',
                descricao: '',
                email: '',
                ativo: true,
                empresa_id: '',
                listaDeHabilidades: ''
            },

            formDefault: null,

            lista: [],
            listaDeHabilidades: [],
            habilidadesFiltradas: [],
            filtroHabilidades: '',
            categoriaFiltro: '',
            categoriasHabilidades: [],
            todasHabilidades: true,

            // Variáveis para usuários vinculados
            listaUsuarios: [],
            usuariosFiltrados: [],
            filtroUsuarios: '',

            dados: {},
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: ''
                }
            }
        }
    },
    computed: {
        /**
         * Há busca por texto ou categoria selecionada no filtro.
         */
        habilidadesFiltroAtivo() {
            return Boolean(this.filtroHabilidades.trim() || this.categoriaFiltro)
        },

        /**
         * Quantas habilidades visíveis no filtro estão com permissão.
         */
        habilidadesPermitidasNoFiltro() {
            return this.habilidadesFiltradas.filter((h) => h.acesso).length
        },

        /**
         * Conta o número de habilidades selecionadas
         */
        habilidadesSelecionadas() {
            return this.listaDeHabilidades.filter((h) => h.acesso).length
        },

        /**
         * Habilidades filtradas agrupadas por módulo e recurso (metadados do backend).
         */
        habilidadesAgrupadasPorModulo() {
            const modulosMap = new Map()
            for (const h of this.habilidadesFiltradas) {
                const moduloKey = h.modulo || this.extrairCategoria(h.nome).toLowerCase()
                const moduloLabel = h.modulo_label || this.extrairCategoria(h.nome)
                const recursoKey = h.recurso || 'geral'
                const recursoLabel = h.recurso_label || this.labelizeSlug(recursoKey)

                if (!modulosMap.has(moduloKey)) {
                    modulosMap.set(moduloKey, {
                        modulo: moduloKey,
                        modulo_label: moduloLabel,
                        recursos: new Map(),
                        itens: []
                    })
                }
                const modulo = modulosMap.get(moduloKey)
                modulo.itens.push(h)

                if (!modulo.recursos.has(recursoKey)) {
                    modulo.recursos.set(recursoKey, {
                        recurso: recursoKey,
                        recurso_label: recursoLabel,
                        itens: []
                    })
                }
                modulo.recursos.get(recursoKey).itens.push(h)
            }

            const resultado = []
            for (const modulo of modulosMap.values()) {
                for (const recurso of modulo.recursos.values()) {
                    recurso.itens.sort((a, b) => {
                        const aa = String(a.acao_label || a.acao || a.nome)
                        const bb = String(b.acao_label || b.acao || b.nome)
                        return aa.localeCompare(bb, 'pt-BR')
                    })
                }
                const recursos = Array.from(modulo.recursos.values()).sort((a, b) =>
                    a.recurso_label.localeCompare(b.recurso_label, 'pt-BR')
                )
                resultado.push({
                    modulo: modulo.modulo,
                    modulo_label: modulo.modulo_label,
                    itens: modulo.itens,
                    recursos
                })
            }

            return resultado.sort((a, b) => a.modulo_label.localeCompare(b.modulo_label, 'pt-BR'))
        },

        /**
         * @deprecated Mantido como alias durante transição; preferir habilidadesAgrupadasPorModulo.
         */
        habilidadesAgrupadasPorCategoria() {
            return this.habilidadesAgrupadasPorModulo.map((m) => ({
                categoria: m.modulo_label,
                itens: m.itens
            }))
        },

        /**
         * Usuários filtrados separados por situação (ativo / inativo).
         */
        usuariosAgrupadosPorStatus() {
            const ativos = []
            const inativos = []
            for (const u of this.usuariosFiltrados) {
                if (u.ativo) {
                    ativos.push(u)
                } else {
                    inativos.push(u)
                }
            }
            const cmp = (a, b) => String(a.nome).localeCompare(String(b.nome), 'pt-BR')
            ativos.sort(cmp)
            inativos.sort(cmp)
            return { ativos, inativos }
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    methods: {
        /**
         * Filtra os usuários baseado no termo de busca
         */
        filtrarUsuarios() {
            if (!this.filtroUsuarios.trim()) {
                this.usuariosFiltrados = [...this.listaUsuarios]
            } else {
                const termo = this.filtroUsuarios.toLowerCase()
                this.usuariosFiltrados = this.listaUsuarios.filter(
                    (usuario) => usuario.nome.toLowerCase().includes(termo) || usuario.email.toLowerCase().includes(termo)
                )
            }
        },

        /**
         * Formata data para exibição
         * @param {string} data - Data a ser formatada
         * @returns {string} Data formatada
         */
        formatarData(data) {
            if (!data) return 'Nunca'
            const dataObj = new Date(data)
            if (Number.isNaN(dataObj.getTime())) {
                return String(data)
            }
            return dataObj.toLocaleDateString('pt-BR') + ' ' + dataObj.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
        },

        /**
         * Filtra as habilidades baseado no termo de busca e módulo
         */
        filtrarHabilidades() {
            let habilidades = [...this.listaDeHabilidades]

            if (this.filtroHabilidades.trim()) {
                const termo = this.filtroHabilidades.toLowerCase()
                habilidades = habilidades.filter((habilidade) => {
                    const campos = [
                        habilidade.nome,
                        habilidade.descricao,
                        habilidade.modulo_label,
                        habilidade.recurso_label,
                        habilidade.acao_label,
                    ]
                    return campos.some((c) => String(c || '').toLowerCase().includes(termo))
                })
            }

            if (this.categoriaFiltro) {
                habilidades = habilidades.filter((habilidade) => {
                    const label = habilidade.modulo_label || this.extrairCategoria(habilidade.nome)
                    return label === this.categoriaFiltro
                })
            }

            this.habilidadesFiltradas = habilidades
        },

        /**
         * Extrai a categoria de uma habilidade baseado no nome (fallback).
         * @param {string} nomeHabilidade - Nome da habilidade
         * @returns {string} Categoria da habilidade
         */
        extrairCategoria(nomeHabilidade) {
            const partes = String(nomeHabilidade || '').split('_')
            if (partes.length >= 2) {
                return partes[0].charAt(0).toUpperCase() + partes[0].slice(1)
            }
            return 'Outros'
        },

        labelizeSlug(slug) {
            return String(slug || '')
                .replace(/[-_]/g, ' ')
                .replace(/\b\w/g, (c) => c.toUpperCase())
        },

        ehHabilidadeObrigatoriaAlterarSenha(habilidade) {
            return habilidade && String(habilidade.nome) === CONSTANTS.HABILIDADE_OBRIGATORIA_ALTERAR_SENHA
        },

        /**
         * Garante que a permissão de alterar própria senha permaneça concedida em todo grupo.
         */
        garantirHabilidadeAlterarSenha() {
            this.listaDeHabilidades.forEach((h) => {
                if (this.ehHabilidadeObrigatoriaAlterarSenha(h)) {
                    h.acesso = true
                }
            })
        },

        definirAcessoHabilidade(habilidade, conceder) {
            if (this.ehHabilidadeObrigatoriaAlterarSenha(habilidade) && !conceder) {
                return
            }
            habilidade.acesso = Boolean(conceder)
            this.syncFlagTodasHabilidades()
        },

        /**
         * Gera a lista de módulos (labels) únicos das habilidades
         */
        gerarCategorias() {
            const categorias = new Set()
            this.listaDeHabilidades.forEach((habilidade) => {
                categorias.add(habilidade.modulo_label || this.extrairCategoria(habilidade.nome))
            })
            this.categoriasHabilidades = Array.from(categorias).sort((a, b) => a.localeCompare(b, 'pt-BR'))
        },

        marcarTodasDoRecurso(itens) {
            ;(itens || []).forEach((h) => {
                h.acesso = true
            })
            this.syncFlagTodasHabilidades()
        },

        desmarcarTodasDoRecurso(itens) {
            ;(itens || []).forEach((h) => {
                if (!this.ehHabilidadeObrigatoriaAlterarSenha(h)) {
                    h.acesso = false
                }
            })
            this.garantirHabilidadeAlterarSenha()
            this.syncFlagTodasHabilidades()
        },

        /**
         * Marca permissão em todas as habilidades do escopo atual:
         * com filtro (busca ou categoria), só as listadas; sem filtro, todas do grupo.
         */
        marcarTodasNoEscopoAtual() {
            const alvos = this.habilidadesFiltroAtivo ? this.habilidadesFiltradas : this.listaDeHabilidades
            alvos.forEach((h) => {
                h.acesso = true
            })
            this.syncFlagTodasHabilidades()
        },

        /**
         * Remove permissão no mesmo escopo de marcarTodasNoEscopoAtual.
         */
        desmarcarTodasNoEscopoAtual() {
            const alvos = this.habilidadesFiltroAtivo ? this.habilidadesFiltradas : this.listaDeHabilidades
            alvos.forEach((h) => {
                if (!this.ehHabilidadeObrigatoriaAlterarSenha(h)) {
                    h.acesso = false
                }
            })
            this.garantirHabilidadeAlterarSenha()
            this.syncFlagTodasHabilidades()
        },

        /**
         * Atualiza flag auxiliar quando todas da lista completa estão permitidas.
         */
        syncFlagTodasHabilidades() {
            this.todasHabilidades =
                this.listaDeHabilidades.length > 0 && this.listaDeHabilidades.every((h) => h.acesso)
        },

        /**
         * Quantas habilidades do subconjunto (ex.: um grupo na tela) estão permitidas.
         */
        contarPermitidasNoGrupo(itens) {
            return itens.filter((h) => h.acesso).length
        },

        /**
         * Limpa busca e categoria para voltar ao escopo completo nas ações em massa.
         */
        limparFiltroHabilidades() {
            this.filtroHabilidades = ''
            this.categoriaFiltro = ''
            this.filtrarHabilidades()
        },

        /**
         * Inicializa o formulário para cadastro de novo grupo
         * Carrega a lista de habilidades disponíveis
         */
        async formNovo() {
            this.mostrarDicasFluxoNovo = true
            this.tituloJanela = CONSTANTS.TITULOS.CADASTRAR
            $(CONSTANTS.ELEMENTOS.ABA_IDENTIFICACAO).tab('show')
            Object.assign(this.form, this.formDefault)
            this.resetarEstados()
            formReset()

            await this.carregarHabilidades()
            this.$nextTick(() => this.$refs.janelaCadastrar?.abrirModal())
        },

        /**
         * Carrega a lista de habilidades do servidor
         */
        async carregarHabilidades() {
            this.mensagemOverlayModal = 'Carregando permissões…'
            this.preloadAjax = true

            try {
                const { data } = await axios.get(`${URL_ADMIN}/papeis/novo`)
                this.listaDeHabilidades = data
                this.filtroHabilidades = ''
                this.categoriaFiltro = ''
                this.garantirHabilidadeAlterarSenha()
                this.filtrarHabilidades()
                this.gerarCategorias()
                this.syncFlagTodasHabilidades()
            } catch (error) {
                this.tratarErro('Erro ao carregar habilidades', error)
            } finally {
                this.preloadAjax = false
            }
        },

        /**
         * Reseta os estados do formulário
         */
        resetarEstados() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
        },
        /**
         * Valida o formulário antes do envio
         * @returns {boolean} true se válido, false caso contrário
         */
        validarFormulario() {
            $(CONSTANTS.ELEMENTOS.JANELA_CADASTRAR + ' :input:visible:enabled').trigger('blur')

            const camposInvalidos = $(CONSTANTS.ELEMENTOS.JANELA_CADASTRAR + ' :input:visible:enabled.is-invalid').length

            if (camposInvalidos > 0) {
                alert(CONSTANTS.MENSAGENS.ERRO_VALIDACAO)
                return false
            }

            return true
        },

        /**
         * Cadastra um novo grupo
         */
        async cadastrar() {
            if (!this.validarFormulario()) {
                return false
            }

            this.prepararDadosFormulario()
            await this.enviarDados('post', `${URL_ADMIN}/papeis`, CONSTANTS.MENSAGENS.SUCESSO_CADASTRO)
        },

        /**
         * Prepara os dados do formulário para envio
         */
        prepararDadosFormulario() {
            this.garantirHabilidadeAlterarSenha()
            this.form.listaDeHabilidades = this.listaDeHabilidades
            this.form.empresa_id = this.empresa_id
        },

        /**
         * Envia dados para o servidor (POST ou PUT)
         * @param {string} metodo - 'post' ou 'put'
         * @param {string} url - URL do endpoint
         * @param {string} mensagemSucesso - Mensagem de sucesso
         */
        async enviarDados(metodo, url, mensagemSucesso) {
            this.mensagemOverlayModal = 'Salvando grupo…'
            this.preloadAjax = true

            const config = {
                method: metodo,
                url: url,
                data: this.form
            }

            // Para PUT, adiciona o ID na URL
            if (metodo === 'put') {
                config.url = `${url}/${this.form.id}`
            }

            try {
                const response = await axios(config)

                if (response.status === CONSTANTS.STATUS.CRIADO) {
                    mostraSucesso('', mensagemSucesso)
                    $(CONSTANTS.ELEMENTOS.JANELA_CADASTRAR).modal('hide')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                }
            } catch (error) {
                this.tratarErro('Erro ao salvar grupo', error)
            } finally {
                this.preloadAjax = false
            }
        },

        /**
         * Trata erros de forma consistente
         * @param {string} mensagem - Mensagem personalizada
         * @param {Object} error - Objeto de erro do axios
         */
        tratarErro(mensagem, error) {
            console.error(mensagem, error)
            const res = error && error.response
            if (res && res.status === 400 && res.data && res.data.erros) {
                const msgs = Object.values(res.data.erros).flat().join(' ')
                if (typeof mostraErro === 'function') {
                    mostraErro('Não foi possível salvar', msgs || res.data.msg || mensagem)
                }
                return
            }
            if (typeof mostraErro === 'function') {
                mostraErro('', res && res.data && res.data.msg ? res.data.msg : mensagem)
            }
        },
        /**
         * Inicializa o formulário para edição de papel existente
         * @param {number} id - ID do papel a ser editado
         */
        async formAlterar(id) {
            this.mostrarDicasFluxoNovo = false
            this.tituloJanela = `${CONSTANTS.TITULOS.ALTERAR_PREFIXO} · …`
            $(CONSTANTS.ELEMENTOS.ABA_IDENTIFICACAO).tab('show')
            formReset()
            this.resetarEstados()

            await this.carregarDadosPapel(id)
            this.$nextTick(() => this.$refs.janelaCadastrar?.abrirModal())
        },

        /**
         * Carrega os dados do papel para edição
         * @param {number} id - ID do papel
         */
        async carregarDadosPapel(id) {
            this.mensagemOverlayModal = 'Carregando dados do grupo…'
            this.preloadAjax = true

            try {
                const response = await axios.get(`${URL_ADMIN}/papeis/${id}/editar`)

                if (response.status === CONSTANTS.STATUS.CRIADO) {
                    const data = response.data
                    Object.assign(this.form, data.papel)
                    this.listaDeHabilidades = data.listaDeHabilidade

                    this.aplicarHabilidadesSelecionadas(data.papel.habilidades)
                    this.editando = true
                    const nome = (data.papel && data.papel.nome) || 'Grupo'
                    this.tituloJanela = `${CONSTANTS.TITULOS.ALTERAR_PREFIXO} · ${nome}`

                    // Gera as categorias das habilidades
                    this.gerarCategorias()

                    // Carrega os usuários vinculados que já vêm no response
                    if (data.usuariosVinculados) {
                        this.listaUsuarios = data.usuariosVinculados
                        this.usuariosFiltrados = [...data.usuariosVinculados]
                        this.filtroUsuarios = ''
                    }
                }
            } catch (error) {
                this.tratarErro('Erro ao carregar dados do grupo', error)
            } finally {
                this.preloadAjax = false
            }
        },

        /**
         * Aplica as habilidades já selecionadas para o papel
         * @param {Array} habilidadesPapel - Array de habilidades do papel
         */
        aplicarHabilidadesSelecionadas(habilidadesPapel) {
            this.listaDeHabilidades.forEach((habilidade) => {
                const habilidadeSelecionada = habilidadesPapel.find((h) => h.id === habilidade.id)
                habilidade.acesso = !!habilidadeSelecionada
            })
            this.filtrarHabilidades()
            this.garantirHabilidadeAlterarSenha()
            this.syncFlagTodasHabilidades()
        },
        /**
         * Altera um papel existente
         */
        async alterar() {
            if (!this.validarFormulario()) {
                return false
            }

            this.prepararDadosFormulario()
            await this.enviarDados('put', `${URL_ADMIN}/papeis`, CONSTANTS.MENSAGENS.SUCESSO_ALTERACAO)
        },
        /**
         * Atualiza a lista de papéis
         */
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },

        /**
         * Callback executado quando os dados são carregados
         * @param {Object} dados - Dados retornados do servidor
         */
        carregou(dados) {
            this.lista = dados.itens
            this.empresa_id = dados.empresa_id
            this.controle.carregando = false
        },

        /**
         * Callback executado quando inicia o carregamento
         */
        carregando() {
            this.controle.carregando = true
        },

        /**
         * Limpa estado auxiliar ao fechar a modal (evita flash de dados antigos na próxima abertura).
         */
        limparEstadoModal() {
            this.mostrarDicasFluxoNovo = false
            this.preloadAjax = false
            this.mensagemOverlayModal = 'Aguarde…'
            this.listaUsuarios = []
            this.usuariosFiltrados = []
            this.filtroUsuarios = ''
            this.listaDeHabilidades = []
            this.habilidadesFiltradas = []
            this.filtroHabilidades = ''
            this.categoriaFiltro = ''
            this.categoriasHabilidades = []
            this.todasHabilidades = true
        }
    }
})

registerGlobals(app)
app.mount('#app')
