import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import DateRangeFilter from '../../../components/DateRangeFilter.vue'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete.vue'
import FiltroListagem from '../../../components/ui/FiltroListagem.vue'
import DadosPessoais from '../../../components/entrevistas/DadosPessoaisTexto'
import FormRh from '../../../components/entrevistas/FormParecerRh'
import FormResultadoIntegrado from '../../../components/entrevistas/FormResultadoIntegrado'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import MybpCardCampo from '../../../components/ui/MybpCardCampo.vue'
import MybpStatusBadge from '../../../components/ui/MybpStatusBadge.vue'
const app = createApp({
    mixins: [ExportacaoMixin, ComboboxValidation],
    components: {
        endereco,
        datepicker,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        DadosPessoais,
        FormRh,
        FormResultadoIntegrado,
        MybpCardCampo,
        MybpStatusBadge
    },
    data() {
        return {
            tituloJanela: 'Resultado Integrado',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            preloadExportacao: false,
            filtrosAvancadosAbertos: false,
            dropdownAbertoId: null,

            urlExportacao: `${URL_ADMIN}/entrevistas/resultado-integrado/export`,

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            todos_municipios: `autocomplete/todos-municipios`,

            preloadForm: true,

            cliente_id: '',
            cliente_area_id: 0,
            provas: 0,

            URL_ADMIN,
            selecionados: [],
            selecionaTudo: false,

            formResultado: {
                documentos_entregue: '',
                documentos_entregue_data: '',
                encaminhado_exame: '',
                encaminhado_exame_data: '',
                pcmso_id: '',
                encaminhado_treinamento: '',
                encaminhado_treinamento_data: '',
                excessao: '',
                autorizado_por: '',
                responsavel_envio: '',
                obs: ''
            },

            formResultadoDefault: null,

            form: {
                id: '',

                vaga_id: '',
                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                cliente_id: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

                curriculo: {
                    nome: '',
                    nascimento: '',
                    municipio_id: '',
                    autocomplete_label_municipio_modal: '',
                    autocomplete_label_municipio_modal_anterior: ''
                },

                certificados_nr: [],
                certificados_nrDelete: [],
                cursos_formacoes: [],
                cursos_formacoesDelete: [],

                parecer_rh: {
                    feedback_id: '',
                    formulario_id: '',
                    tipo_entrevista: 'Fixo',
                    curriculo_id: '',
                    destro: '',
                    ex_funcionario: '',
                    cnh: '',
                    cnh_tipo: '',
                    mora_com_quem: '',
                    rota_bairro: '',
                    calca: '',
                    bota: '',
                    camisa_protecao: '',
                    camisa_meia: '',
                    casado: '',
                    tempodeconvivencia: '',
                    filhos: '',
                    qnt_filhos: '',
                    conjuge_trabalha: '',
                    trabalho_conjuge: '',
                    religioso: '',
                    religiao_praticante: '',
                    fuma: '',
                    frequencia_fuma: '',
                    bebe: '',
                    frequencia_bebe: '',
                    nr_dez: '',
                    indicacao: '',
                    indicado_por: '',
                    alumar_experiencia: '',
                    alumar_experiencia_area: '',
                    outra_industria_experiencia: '',
                    outra_industria_nome: '',
                    grau_instrucao: '',
                    horaextra: '',
                    turnos_seis_por_dois: '',
                    noturno: '',
                    acidente_trabalho: '',
                    acidente_trabalho_qual: '',
                    afastamento_inss: '',
                    afastamento_inss_qual: '',
                    situacao_saude: '',
                    comportamento_seguro: '',
                    energia_para_trabalho: '',
                    postura: '',
                    historico_profissional: '',
                    historico_educacional: '',
                    objetivos_expectativas: '',
                    auto_imagem: '',
                    competencias: '',
                    comportamento_etico: '',
                    comprometimento: '',
                    comunicacao: '',
                    cultura_qualidade: '',
                    foco_cliente: '',
                    iniciativa: '',
                    orientacao_resultados: '',
                    trabalho_equipe: '',
                    parecer_final: '',
                    parecer_final_um: '',
                    nota: '',
                    comentarios: '',
                    entrevistador: '',
                    quem_entrevistou: '',

                    nota_digitacao: '',
                    dinamicadegrupo: '',
                    obs_dinamicadegrupo: '',
                    experiencia_callcenter: '',
                    disponibilidade_horarios: '',
                    turnos_seis_por_um: '',
                    horario_preferencial: '',
                    obs_call: '',
                    obs_horario: '',

                    individual_rh: {
                        parecer: '',
                        nota: '',
                        entrevistado_por: '',
                        comentario: '',
                        avaliacao_psicologica: ''
                    },

                    gestor_rh: {
                        parecer: '',
                        indicado_para: '',
                        nota: '',
                        entrevistado_por: '',
                        comentario: ''
                    },

                    entrevista_rh: {
                        parecer: '',
                        indicado_para: '',
                        nota: '',
                        entrevistado_por: '',
                        comentario: ''
                    }
                },

                resultado_integrado: {
                    feedback_id: '',
                    documentos_entregue: '',
                    documentos_entregue_data: '',
                    envia_email_documentos: false,
                    envia_whatsapp_documentos: false,
                    encaminhado_exame: '',
                    encaminhado_exame_data: '',
                    envia_email_exame: false,
                    envia_whatsapp_exame: false,
                    encaminhado_treinamento: '',
                    encaminhado_treinamento_data: '',
                    excessao: '',
                    autorizado_por: '',
                    responsavel_envio: '',
                    obs: ''
                },

                simulados: []
            },

            formDefault: null,

            lista: [],
            listaPcmso: [],
            vagas: [],
            opened: [],

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
                    autocomplete_label_cliente_anterior: '',
                    autocomplete_label_cliente: '',
                    pages: 20,
                    campoBusca: '',
                    campoVaga: '',
                    campoCliente: '',
                    campoFiltro: '',
                    campoUf: '',
                    campoRh: '',
                    campoFinalRh: '',
                    campoRota: '',
                    campoTecnica: '',
                    campoTeste: '',
                    campoPcd: '',
                    campoCPF: '',
                    // campoStatus: '',
                    entrevista_rh: '',
                    entrevista_rh_nota: '',

                    cliente_custom: '',
                    parecer_individual: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    periodo: ''
                }
            }
        }
    },
    computed: {
        // cliente_comercio() {
        //     return this.form.cliente_id === 35;
        // },
        industria() {
            return this.cliente_id === 1 || (this.cliente_id !== 35 && this.controle.dados.campoCliente !== 35)
        },
        servico() {
            return this.cliente_id === 1 || (this.cliente_id === 35 && this.controle.dados.campoCliente === 35)
        },
        comResultado() {
            return this.lista.filter((item) => {
                return item.resultado_integrado //verificar depois
            })
        },
        tudoMarcado() {
            const totalItens = this.comResultado.length
            if (totalItens === 0) {
                return false
            }
            const totalEncontrado = this.comResultado.filter((item) => this.selecionados.indexOf(item.id) >= 0).length
            const resultado = totalItens === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [
                d.campoBusca,
                d.campoCPF,
                d.campoVaga,
                d.campoUf,
                d.parecer_individual,
                d.entrevista_rh,
                d.campoRh,
                d.entrevista_rh_nota
            ].filter((v) => v !== '' && v !== null && v !== undefined).length
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (Number(d.pages) !== 20) total++
            return total
        },
        totalFiltrosAtivosAvancados() {
            const d = this.controle.dados
            let total = [d.campoRh, d.entrevista_rh_nota].filter((v) => v !== '' && v !== null && v !== undefined).length
            if (Number(d.pages) !== 20) total++
            return total
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        filtroUfOpcoes() {
            const ufs = [
                'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
                'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
                'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
            ]
            return [{ value: '', label: 'Todos os estados' }, ...ufs.map((uf) => ({ value: uf, label: uf }))]
        },
        filtroClassIndividualOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'favoravel', label: 'Favorável' },
                { value: 'destaque', label: 'Destaque' }
            ]
        },
        filtroClassRhOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'entrevistado', label: 'Entrevistados' },
                { value: 'nao_entrevistado', label: 'Não Entrevistados' },
                { value: 'favoravel', label: 'Favorável' },
                { value: 'destaque', label: 'Destaque' },
                { value: 'stand_by', label: 'Stand By' },
                { value: 'desfavoravel', label: 'Desfavorável' }
            ]
        },
        filtroNotaOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: '0', label: '0' },
                { value: '1-5', label: '1 à 5' },
                { value: '5-7', label: '5 à 7' },
                { value: '8-10', label: '8 à 10' }
            ]
        },
        filtroPagesOpcoes() {
            return [20, 50, 100].map((n) => ({ value: n, label: String(n) }))
        },
        campoPagesCombo: {
            get() {
                return this.controle.dados.pages
            },
            set(v) {
                const n = Number(v)
                this.controle.dados.pages = Number.isFinite(n) && n > 0 ? n : 20
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.usuarioAutenticado()
        this.listaVagas()
        setTimeout(() => {
            this.atualizar()
        }, 200)
    },
    methods: {
        /***Campos de Filtros ****/
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        formatarCpfDigitos(valor) {
            const d = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = this.controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        },
        onPeriodoChange() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'ri-filtro-uf': 'comboFiltroUf',
                'ri-filtro-class-ind': 'comboFiltroClassInd',
                'ri-filtro-class-rh': 'comboFiltroClassRh',
                'ri-filtro-nota-ind': 'comboFiltroNotaInd',
                'ri-filtro-nota-rh': 'comboFiltroNotaRh',
                'ri-filtro-pages': 'comboFiltroPages'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.fechar === 'function') ref.fechar()
                else if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        limparFiltros() {
            const pages = this.controle.dados.pages || 20
            this.controle.dados = {
                ...this.controle.dados,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                autocomplete_label_cliente_anterior: '',
                autocomplete_label_cliente: '',
                cliente_custom: '',
                campoBusca: '',
                campoCPF: '',
                campoVaga: '',
                campoCliente: this.cliente_id !== 0 ? this.cliente_id : '',
                campoUf: '',
                campoRh: '',
                entrevista_rh: '',
                entrevista_rh_nota: '',
                parecer_individual: '',
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                periodo: '',
                pages
            }
            this.atualizar()
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.comResultado.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comResultado.map((item) => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },

        chaveStatusRi(item) {
            return item && item.resultado_integrado ? 'aprovado' : 'pendente'
        },
        textoStatusRi(item) {
            return item && item.resultado_integrado ? 'Integrado' : 'Pendente'
        },
        textoRespRi(item) {
            const ri = item && item.resultado_integrado
            if (!ri || !ri.responsavel_envio) return ''
            return ri.responsavel_envio
        },
        textoEncRi(item, flagKey, dataKey) {
            const ri = item && item.resultado_integrado
            if (!ri) return '—'
            const sim = !!ri[flagKey]
            const data = ri[dataKey] || ''
            return data ? `${sim ? 'Sim' : 'Não'} · ${data}` : sim ? 'Sim' : 'Não'
        },
        tomEncRi(item, flagKey) {
            const ri = item && item.resultado_integrado
            if (!ri) return 'meta'
            return ri[flagKey] ? 'positivo' : 'negativo'
        },
        toggleDropdown(itemId) {
            this.dropdownAbertoId = this.dropdownAbertoId === itemId ? null : itemId
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoId === itemId
        },
        fecharDropdown() {
            this.dropdownAbertoId = null
        },

        formEntrevistar(id) {
            Object.assign(this.form, this.formDefault)

            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.editando = false

            this.tituloJanela = `#${id}`

            this.preload = true
            this.preloadForm = true

            this.form.resultado_integrado.feedback_id = id

            formReset()
            axios
                .get(`${URL_ADMIN}/entrevistas/resultado-integrado/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data.feedback)

                    //Se não tiver parecer_rh
                    this.form.parecer_rh = data.feedback.parecer_rh ? data.feedback.parecer_rh : _.cloneDeep(this.formDefault.parecer_rh)
                    this.form.parecer_rh.gestor_rh = data.feedback.parecer_rh.gestor_rh
                        ? data.feedback.parecer_rh.gestor_rh
                        : _.cloneDeep(this.formDefault.parecer_rh.gestor_rh)
                    this.form.parecer_rh.entrevista_rh = data.feedback.parecer_rh.entrevista_rh
                        ? data.feedback.parecer_rh.entrevista_rh
                        : _.cloneDeep(this.formDefault.parecer_rh.entrevista_rh)
                    this.form.resultado_integrado = data.feedback.resultado_integrado
                        ? data.feedback.resultado_integrado
                        : _.cloneDeep(this.formDefault.resultado_integrado)

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`
                    this.cadastrando = true

                    this.preload = false
                    this.preloadForm = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        cadastrar() {
            if (this.visualizar) {
                return false
            }

            const formRi = this.$refs.formResultadoIntegrado
            if (formRi && typeof formRi.validarCampos === 'function' && !formRi.validarCampos()) {
                return false
            }

            $('#janelaParecerEntrevista :input:visible:enabled').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible:enabled.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/entrevistas/resultado-integrado/`, this.form.resultado_integrado)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    this.$refs.janelaParecerEntrevista?.fecharModal()
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        alterar() {
            if (this.visualizar) {
                return false
            }

            const formRi = this.$refs.formResultadoIntegrado
            if (formRi && typeof formRi.validarCampos === 'function' && !formRi.validarCampos()) {
                return false
            }

            $('#janelaParecerEntrevista :input:visible:enabled').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible:enabled.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/entrevistas/resultado-integrado/${this.form.resultado_integrado.id}`, this.form.resultado_integrado)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    this.$refs.janelaParecerEntrevista?.fecharModal()
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        listaVagas() {
            this.preload = true
            axios
                .get(`${URL_PUBLICO}/lista-vagas`)
                .then((res) => {
                    this.preload = false
                    this.vagas = res.data.vagas
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preload = false
        },

        usuarioAutenticado() {
            this.controle.carregando = true
            axios
                .get(`${URL_ADMIN}/usuario/autenticado/`)
                .then((response) => {
                    let data = response.data
                    this.cliente_id = data.cliente_id
                    this.cliente_area_id = data.area_id

                    if (this.cliente_id > 0) {
                        if (this.cliente_area_id === 1) {
                            //for Industrial
                            this.colunasTabela.cliente = false
                            this.colunasTabela.pcd = false
                            this.colunasTabela.rh_nota = true
                            this.colunasTabela.rota_transporte = true
                            this.colunasTabela.entrevista_tecnica = true
                            this.colunasTabela.teste_pratico = true
                            this.colunasTabela.parecer_individual = false
                            this.colunasTabela.nota_individual = false
                        }
                        if (this.cliente_area_id > 1) {
                            //for Servico ou Comercio
                            this.colunasTabela.cliente = false
                            this.colunasTabela.pcd = true
                            this.colunasTabela.rh_nota = false
                            this.colunasTabela.rota_transporte = false
                            this.colunasTabela.entrevista_tecnica = false
                            this.colunasTabela.teste_pratico = false
                            this.colunasTabela.parecer_individual = true
                            this.colunasTabela.nota_individual = true
                        }
                    } else {
                        this.colunasTabela.cliente = true
                        this.colunasTabela.pcd = false
                        this.colunasTabela.rh_nota = true
                        this.colunasTabela.rota_transporte = false
                        this.colunasTabela.entrevista_tecnica = false
                        this.colunasTabela.teste_pratico = false
                        this.colunasTabela.parecer_individual = true
                        this.colunasTabela.nota_individual = false
                    }

                    this.colunasTabela.cliente = this.cliente_id === 0
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.itens
            this.listaPcmso = dados.listaPcmso
            this.selecionaTudo = this.tudoMarcado
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
