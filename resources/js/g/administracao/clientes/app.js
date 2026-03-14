import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import telefone from '../../../components/Telefones'
import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import upload from '../../../components/Upload'
import Filial from '../../../components/administracao/clientes/Filial'

const app = createApp({
    components: {
        telefone,
        endereco,
        datepicker,
        upload,
        Filial
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Cliente',
            preloadAjax: false,
            editando: false,
            apagado: false,

            pages: 10,

            listaDeHabilidades: [],
            listaModeloCih: [],
            todosMenu: [],
            todasHabilidades: true,
            menuHabilidades: true,

            form: {
                tipo_cliente: '',
                cnpj: '',
                cpf: '',
                nome: '',
                tipo: 'Pessoa Jurídica',
                razao_social: '',
                nome_fantasia: '',
                area_id: '',
                ramo: '',
                cep: '',
                logradouro: '',
                numero: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                contato: '',
                como_conheceu: '',
                como_conheceu_outro: '',
                email: '',
                aniversario: '',
                ativo: '',

                politica_ehs: '',
                missao: '',
                visao: '',
                valores: '',
                politica_gq: '',
                apelido: '',
                tel_principal: '',

                servicos_cliente: [],
                servicos_clienteDelete: [],

                servicos_prospect: [],
                servicos_prospectDelete: [],

                anexosDel: [],
                anexosProspectDel: [],

                logo: [],
                logoDel: [],

                mascote: [],
                mascoteDel: [],

                telefones: [
                    {
                        tipo: 'comercial',
                        pais: 55,
                        numero: '',
                        ramal: '',
                        detalhe: ''
                    }
                ],
                telefonesDelete: [],

                cliente_config: {
                    id: '',
                    verifica_mes_vencimento: '',
                    envia_whatsapp: '',
                    vencimento_aso: '',
                    modelo_cih: '',
                    supervisor_etiqueta_bloqueio: true,
                    schedule_avaliacao_experiencia: true,
                    schedule_treinamento_vencimento: true,
                    treinamento_permitir_desmarcar_realizado: false,
                    assinatura_digital_habilitada: false,
                    limite_assinaturas_mensal: '',
                    assinatura_alerta_user_ids: [],
                    assinatura_alerta_grupo_ids: []
                },
                segmentos_treinamento_ids: [],

                listaDeHabilidades: ''
            },
            listaSegmentosTreinamento: [],

            urlAnexoUpload: `${URL_ADMIN}/administracao/clientes/uploadAnexos`,
            anexoUploadAndamento: false,

            urlLogoUpload: `${URL_ADMIN}/administracao/clientes/uploadLogo`,
            logoUploadAndamento: false,

            urlMascoteUpload: `${URL_ADMIN}/administracao/clientes/uploadMascote`,
            mascoteUploadAndamento: false,

            formDefault: null,
            campoNome: null,

            cadastrado: false,
            atualizado: false,
            leitura: false,

            lista: [],
            listaServicos: [],
            listaAreas: [],
            usuariosAlertaAssinatura: [],
            gruposAlertaAssinatura: [],
            buscaAlertaUsuario: '',
            buscaAlertaGrupo: '',

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoTipo: '',
                    campoStatus: ''
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
        axios
            .get(`${URL_ADMIN}/cadastro/segmentostreinamento/lista`)
            .then((res) => {
                this.listaSegmentosTreinamento = res.data || []
            })
            .catch(() => {})
    },
    methods: {
        selecionarTodas() {
            this.todasHabilidades = !this.todasHabilidades
            var valor = this.todasHabilidades
            _.forEach(this.listaDeHabilidades, function (habilidade) {
                habilidade.acesso = valor
            })
        },
        selecionarPorModulo(menu) {
            this.menuHabilidades = !this.menuHabilidades
            var valor = this.menuHabilidades
            _.forEach(this.listaDeHabilidades, function (habilidade) {
                if (habilidade.menu === menu) {
                    habilidade.acesso = valor
                }
            })
        },
        addLIServicoCliente() {
            const obj = {}
            obj.nova = true
            obj.servico_id = ''
            obj.data_inicio = moment().format('L')
            obj.data_encerramento = moment().add(6, 'months').format('L')
            obj.escopo = ''
            obj.valor = '0.00'
            obj.tipo_faturamento = 'Único'
            obj.status = 'Iniciado'
            obj.feedback = ''
            obj.tipo_contrato = 'FIXO'
            obj.ativo = true

            obj.anexos = []
            obj.anexosDel = []
            this.form.servicos_cliente.unshift(obj)
        },
        removerLIServicoCliente(index) {
            if (this.editando) {
                this.form.servicos_clienteDelete.push(this.form.servicos_cliente[index].id)
            }
            this.form.servicos_cliente.splice(index, 1)
        },
        addLIServicoProspect() {
            const obj = {}
            obj.nova = true
            obj.servico_id = ''
            obj.data_envio_proposta = moment().format('L')
            obj.escopo = ''
            obj.status = 'Iniciado'
            obj.feedback = ''
            obj.anexos = []
            obj.anexosDel = []

            this.form.servicos_prospect.unshift(obj)
        },
        removerLIServicoProspect(index) {
            if (this.editando) {
                this.form.servicos_prospectDelete.push(this.form.servicos_prospect[index].id)
            }
            this.form.servicos_prospect.splice(index, 1)
        },
        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.tituloJanela = 'Cadastrando Cliente'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false
            this.usuariosAlertaAssinatura = []
            this.gruposAlertaAssinatura = []
            this.buscaAlertaUsuario = ''
            this.buscaAlertaGrupo = ''
        },
        cadastrar() {
            formReset()

            $('#janelaCadastrar :input:enabled').trigger('blur')
            // Validações de abas
            // $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0 ? $('#nav-dados-cadastrais-tab').addClass('bg-danger text-white') : $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
            // $('#nav-servicos :input:enabled.is-invalid').length > 0 ? $('#nav-servicos-tab').addClass('bg-danger text-white') : $('#nav-servicos-tab').removeClass('bg-danger text-white');

            let abas = ['nav-dados-cadastrais', 'nav-servicos', 'nav-proposta', 'nav-config', 'nav-proposta']

            abas.forEach((aba) => {
                $(`#${aba} :input:enabled.is-invalid`).length > 0
                    ? $(`#${aba}-tab`).addClass('bg-danger text-white')
                    : $(`#${aba}-tab`).removeClass('bg-danger text-white')
            })

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            if (this.form.telefones.length === 0) {
                mostraErro('', 'Por favor insira um Telefone')
                return false
            }
            this.form.listaDeHabilidades = this.listaDeHabilidades
            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/administracao/clientes`, this.form)
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
            this.tituloJanela = 'Alterando Cliente'
            this.preloadAjax = true
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true

            axios
                .get(`${URL_ADMIN}/administracao/clientes/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data.cliente)
                    this.form.segmentos_treinamento_ids = (response.data.cliente.segmentos_treinamento || []).map((s) => s.id)
                    this.editando = true
                    this.preloadAjax = false
                    if (!response.data.cliente.cliente_config) {
                        this.form.cliente_config = {
                            verifica_mes_vencimento: '',
                            envia_whatsapp: '',
                            vencimento_aso: '',
                            modelo_cih: '',
                            supervisor_etiqueta_bloqueio: true,
                            schedule_avaliacao_experiencia: true,
                            schedule_treinamento_vencimento: true,
                            treinamento_permitir_desmarcar_realizado: false,
                            assinatura_digital_habilitada: false,
                            limite_assinaturas_mensal: '',
                            assinatura_alerta_user_ids: [],
                            assinatura_alerta_grupo_ids: []
                        }
                    } else {
                        this.form.cliente_config.treinamento_permitir_desmarcar_realizado = !!this.form.cliente_config.treinamento_permitir_desmarcar_realizado
                        this.form.cliente_config.assinatura_digital_habilitada = !!this.form.cliente_config.assinatura_digital_habilitada
                        this.form.cliente_config.limite_assinaturas_mensal = this.form.cliente_config.limite_assinaturas_mensal ?? ''
                        this.form.cliente_config.assinatura_alerta_user_ids = (this.form.cliente_config.assinatura_alerta_user_ids || []).map((id) =>
                            Number(id)
                        )
                        this.form.cliente_config.assinatura_alerta_grupo_ids = (this.form.cliente_config.assinatura_alerta_grupo_ids || []).map((id) =>
                            Number(id)
                        )
                    }

                    this.usuariosAlertaAssinatura = response.data.usuariosAlertaAssinatura || []
                    this.gruposAlertaAssinatura = response.data.gruposAlertaAssinatura || []
                    this.buscaAlertaUsuario = ''
                    this.buscaAlertaGrupo = ''
                    this.listaModeloCih = response.data.listaModeloCih
                    this.form.como_conheceu = !this.form.como_conheceu ? '' : this.form.como_conheceu

                    this.listaDeHabilidades = response.data.listaDeHabilidades
                    this.todosMenu = response.data.todosMenu

                    var habilidades_papel = response.data.cliente.papel.habilidades
                    _.forEach(this.listaDeHabilidades, function (habilidade) {
                        var achou = _.find(habilidades_papel, { id: habilidade.id })
                        if (achou) {
                            habilidade.acesso = true
                        }
                    })

                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')
            // Validações de abas
            let abas = ['nav-dados-cadastrais', 'nav-servicos', 'nav-proposta', 'nav-config', 'nav-proposta']

            abas.forEach((aba) => {
                $(`#${aba} :input:enabled.is-invalid`).length > 0
                    ? $(`#${aba}-tab`).addClass('bg-danger text-white')
                    : $(`#${aba}-tab`).removeClass('bg-danger text-white')
            })

            // $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0 ? $('#nav-dados-cadastrais-tab').addClass('bg-danger text-white') : $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
            // $('#nav-config :input:enabled.is-invalid').length > 0 ? $('#nav-config-tab').addClass('bg-danger text-white') : $('#nav-config-tab').removeClass('bg-danger text-white');
            // $('#nav-servicos :input:enabled.is-invalid').length > 0 ? $('#nav-servicos-tab').addClass('bg-danger text-white') : $('#nav-servicos-tab').removeClass('bg-danger text-white');
            // $('#nav-servicos :input:enabled.is-invalid').length > 0 ? $('#nav-servicos-tab').addClass('bg-danger text-white') : $('#nav-servicos-tab').removeClass('bg-danger text-white');

            if ($('#nav-dados-cadastrais :input:enabled.is-invalid,#nav-config :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            // if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
            //     mostraErro('', 'Verificar os erros');
            //     return false;
            // }

            if (this.form.telefones.length === 0) {
                mostraErro('', 'Por favor insira um Telefone')
                return false
            }

            this.form._method = 'PUT'
            this.form.listaDeHabilidades = this.listaDeHabilidades
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/administracao/clientes/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        apagar() {
            this.erros = []
            this.form._method = 'DELETE'
            this.preloadAjax = true

            axios
                .delete(`${URL_ADMIN}/administracao/clientes/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.apagado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preloadAjax = false
        },
        carregou(dados) {
            this.lista = dados.itens
            this.listaServicos = dados.servicos
            this.listaAreas = dados.areas
            this.listaDeHabilidades = dados.listaDeHabilidades
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },

        async verificaCpf() {
            if (!this.editando) {
                try {
                    await axios.get(`${URL_ADMIN}/administracao/clientes/buscar-cpf?cpf=${this.form.cpf}`)
                } catch (err) {}
            }
        },
        async verificaCnpj() {
            if (!this.editando) {
                try {
                    await axios.get(`${URL_ADMIN}/administracao/clientes/buscar-cnpj?cnpj=${this.form.cnpj}`)
                } catch (err) {}
            }
        },
        filtrarUsuariosAlertaDisponiveis() {
            const idsSelecionados = this.form.cliente_config.assinatura_alerta_user_ids || []
            const termo = (this.buscaAlertaUsuario || '').trim().toLowerCase()
            return (this.usuariosAlertaAssinatura || [])
                .filter((u) => !idsSelecionados.includes(Number(u.id)))
                .filter((u) => {
                    if (!termo) {
                        return true
                    }
                    const nome = (u.nome || '').toLowerCase()
                    const email = (u.email || '').toLowerCase()
                    return nome.includes(termo) || email.includes(termo)
                })
                .slice(0, 20)
        },
        filtrarGruposAlertaDisponiveis() {
            const idsSelecionados = this.form.cliente_config.assinatura_alerta_grupo_ids || []
            const termo = (this.buscaAlertaGrupo || '').trim().toLowerCase()
            return (this.gruposAlertaAssinatura || [])
                .filter((g) => !idsSelecionados.includes(Number(g.id)))
                .filter((g) => {
                    if (!termo) {
                        return true
                    }
                    const nome = (g.nome || '').toLowerCase()
                    return nome.includes(termo)
                })
                .slice(0, 20)
        },
        adicionarUsuarioAlerta(usuario) {
            if (!usuario || !usuario.id) return
            const id = Number(usuario.id)
            const lista = this.form.cliente_config.assinatura_alerta_user_ids || []
            if (lista.includes(id)) return
            this.form.cliente_config.assinatura_alerta_user_ids.push(id)
            this.buscaAlertaUsuario = ''
        },
        removerUsuarioAlerta(index) {
            this.form.cliente_config.assinatura_alerta_user_ids.splice(index, 1)
        },
        adicionarGrupoAlerta(grupo) {
            if (!grupo || !grupo.id) return
            const id = Number(grupo.id)
            const lista = this.form.cliente_config.assinatura_alerta_grupo_ids || []
            if (lista.includes(id)) return
            this.form.cliente_config.assinatura_alerta_grupo_ids.push(id)
            this.buscaAlertaGrupo = ''
        },
        removerGrupoAlerta(index) {
            this.form.cliente_config.assinatura_alerta_grupo_ids.splice(index, 1)
        },
        getUsuarioAlerta(id) {
            return (this.usuariosAlertaAssinatura || []).find((u) => Number(u.id) === Number(id)) || null
        },
        getGrupoAlerta(id) {
            return (this.gruposAlertaAssinatura || []).find((g) => Number(g.id) === Number(id)) || null
        }
    }
})

registerGlobals(app)
app.mount('#app')
