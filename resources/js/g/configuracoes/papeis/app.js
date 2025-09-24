// Constantes da aplicação
const CONSTANTS = {
    TITULOS: {
        CADASTRAR: 'Cadastrando grupo',
        ALTERAR: 'Alterando grupo'
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
    }
}

const app = new Vue({
    el: '#app',
    data: {
        tituloJanela: '',
        empresa_id: '',
        preloadAjax: false,
        editando: false,

        cadastrado: false,
        atualizado: false,
        urlAjax: '',
        apagado: false,

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
        paginaHabilidades: 1,
        itensPorPaginaHabilidades: 10,

        // Variáveis para usuários vinculados
        listaUsuarios: [],
        usuariosFiltrados: [],
        filtroUsuarios: '',
        paginaUsuarios: 1,
        itensPorPaginaUsuarios: 10,

        dados: {},
        controle: {
            carregando: false,
            dados: {
                pages: 20,
                campoBusca: ''
            }
        }
    },
    computed: {
        /**
         * Conta o número de habilidades selecionadas
         */
        habilidadesSelecionadas() {
            return this.listaDeHabilidades.filter((h) => h.acesso).length
        },

        /**
         * Calcula o total de páginas para as habilidades filtradas
         */
        totalPaginasHabilidades() {
            return Math.ceil(this.habilidadesFiltradas.length / this.itensPorPaginaHabilidades)
        },

        /**
         * Retorna as habilidades da página atual
         */
        habilidadesPaginadas() {
            const inicio = (this.paginaHabilidades - 1) * this.itensPorPaginaHabilidades
            const fim = inicio + this.itensPorPaginaHabilidades
            return this.habilidadesFiltradas.slice(inicio, fim)
        },

        /**
         * Calcula o total de páginas para os usuários filtrados
         */
        totalPaginasUsuarios() {
            return Math.ceil(this.usuariosFiltrados.length / this.itensPorPaginaUsuarios)
        },

        /**
         * Retorna os usuários da página atual
         */
        usuariosPaginados() {
            const inicio = (this.paginaUsuarios - 1) * this.itensPorPaginaUsuarios
            const fim = inicio + this.itensPorPaginaUsuarios
            return this.usuariosFiltrados.slice(inicio, fim)
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
            // Reset da paginação quando filtrar
            this.paginaUsuarios = 1
        },

        /**
         * Formata data para exibição
         * @param {string} data - Data a ser formatada
         * @returns {string} Data formatada
         */
        formatarData(data) {
            if (!data) return 'Nunca'
            const dataObj = new Date(data)
            return dataObj.toLocaleDateString('pt-BR') + ' ' + dataObj.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
        },

        /**
         * Filtra as habilidades baseado no termo de busca e categoria
         */
        filtrarHabilidades() {
            let habilidades = [...this.listaDeHabilidades]

            // Filtro por texto
            if (this.filtroHabilidades.trim()) {
                const termo = this.filtroHabilidades.toLowerCase()
                habilidades = habilidades.filter(
                    (habilidade) => habilidade.nome.toLowerCase().includes(termo) || habilidade.descricao.toLowerCase().includes(termo)
                )
            }

            // Filtro por categoria
            if (this.categoriaFiltro) {
                habilidades = habilidades.filter((habilidade) => this.extrairCategoria(habilidade.nome) === this.categoriaFiltro)
            }

            this.habilidadesFiltradas = habilidades
            // Reset da paginação quando filtrar
            this.paginaHabilidades = 1
        },

        /**
         * Extrai a categoria de uma habilidade baseado no nome
         * @param {string} nomeHabilidade - Nome da habilidade
         * @returns {string} Categoria da habilidade
         */
        extrairCategoria(nomeHabilidade) {
            const partes = nomeHabilidade.split('_')
            if (partes.length >= 2) {
                return partes[0].charAt(0).toUpperCase() + partes[0].slice(1)
            }
            return 'Outros'
        },

        /**
         * Gera a lista de categorias únicas das habilidades
         */
        gerarCategorias() {
            const categorias = new Set()
            this.listaDeHabilidades.forEach((habilidade) => {
                categorias.add(this.extrairCategoria(habilidade.nome))
            })
            this.categoriasHabilidades = Array.from(categorias).sort()
        },

        /**
         * Seleciona todas as habilidades de uma categoria específica
         * @param {string} categoria - Categoria a ser selecionada
         */
        selecionarPorCategoria(categoria) {
            this.listaDeHabilidades.forEach((habilidade) => {
                if (this.extrairCategoria(habilidade.nome) === categoria) {
                    habilidade.acesso = true
                }
            })
        },

        /**
         * Desmarca todas as habilidades
         */
        desmarcarTodas() {
            this.listaDeHabilidades.forEach((habilidade) => {
                habilidade.acesso = false
            })
            this.todasHabilidades = false
        },

        /**
         * Alterna a seleção de todas as habilidades
         * Se todas estavam selecionadas, desmarca todas
         * Se nem todas estavam selecionadas, marca todas
         */
        selecionarTodas() {
            this.todasHabilidades = !this.todasHabilidades
            const valor = this.todasHabilidades

            this.listaDeHabilidades.forEach((habilidade) => {
                habilidade.acesso = valor
            })
        },
        /**
         * Inicializa o formulário para cadastro de novo grupo
         * Carrega a lista de habilidades disponíveis
         */
        async formNovo() {
            this.tituloJanela = CONSTANTS.TITULOS.CADASTRAR
            $(CONSTANTS.ELEMENTOS.ABA_IDENTIFICACAO).tab('show')
            Object.assign(this.form, this.formDefault)
            this.resetarEstados()
            formReset()

            await this.carregarHabilidades()
        },

        /**
         * Carrega a lista de habilidades do servidor
         */
        async carregarHabilidades() {
            this.preloadAjax = true

            try {
                const { data } = await axios.get(`${URL_ADMIN}/papeis/novo`)
                this.listaDeHabilidades = data
                this.habilidadesFiltradas = [...data]
                this.filtroHabilidades = ''
                this.categoriaFiltro = ''
                this.gerarCategorias()
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
            this.preloadAjax = true
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
                    this.$refs.componente.buscar()
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
            // Aqui você pode adicionar lógica para mostrar notificações de erro
            // ou enviar logs para um serviço de monitoramento
        },
        /**
         * Inicializa o formulário para edição de papel existente
         * @param {number} id - ID do papel a ser editado
         */
        async formAlterar(id) {
            this.tituloJanela = CONSTANTS.TITULOS.ALTERAR
            $(CONSTANTS.ELEMENTOS.ABA_IDENTIFICACAO).tab('show')
            formReset()
            this.resetarEstados()

            await this.carregarDadosPapel(id)
        },

        /**
         * Carrega os dados do papel para edição
         * @param {number} id - ID do papel
         */
        async carregarDadosPapel(id) {
            this.preloadAjax = true

            try {
                const response = await axios.get(`${URL_ADMIN}/papeis/${id}/editar`)

                if (response.status === CONSTANTS.STATUS.CRIADO) {
                    const data = response.data
                    Object.assign(this.form, data.papel)
                    this.listaDeHabilidades = data.listaDeHabilidade

                    this.aplicarHabilidadesSelecionadas(data.papel.habilidades)
                    this.editando = true

                    // Gera as categorias das habilidades
                    this.gerarCategorias()

                    // Carrega os usuários vinculados que já vêm no response
                    if (data.usuariosVinculados) {
                        this.listaUsuarios = data.usuariosVinculados
                        this.usuariosFiltrados = [...data.usuariosVinculados]
                        this.filtroUsuarios = ''
                        this.paginaUsuarios = 1
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
            // Atualiza a lista filtrada após aplicar as seleções
            this.filtrarHabilidades()
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
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
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
        }
    }
})
