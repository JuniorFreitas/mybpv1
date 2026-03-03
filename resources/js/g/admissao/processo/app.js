import endereco from '../../../components/Endereco'
import DadosBancarios from '../../../components/DadosBancarios'
import datepicker from '../../../components/DatePicker'
import DateRangeFilter from '../../../components/DateRangeFilter.vue'
import upload from '../../../components/Upload'
import telefone from '../../../components/Telefones'
import DadosPessoais from '../../../components/entrevistas/DadosPessoaisTexto'
import FormResultadoIntegrado from '../../../components/entrevistas/FormResultadoIntegrado'
import formAdmissao from '../../../components/admissao/processo/formAdmissao'
import Select2 from '../../../components/Select2/Select2'
import configselect2 from '../../../components/Select2/mixSelec2'
import Utils from '../../../mixins/Utils'
import Validacoes from '../../../mixins/Validacoes'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import dependentes from '../../../components/admissao/processo/Dependentes'
import FeriasAdquiridas from '../../../components/admissao/processo/FeriasAdquiridas'

const app = new Vue({
    el: '#app',
    mixins: [configselect2, Utils, Validacoes, ExportacaoMixin],
    components: {
        endereco,
        datepicker,
        DateRangeFilter,
        upload,
        telefone,
        DadosPessoais,
        formAdmissao,
        FormResultadoIntegrado,
        DadosBancarios,
        Select2,
        dependentes,
        FeriasAdquiridas
    },
    data: {
        tituloJanela: 'Admissão',
        preload: false,
        preloadExportacao: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        // disabled: true,
        disabledInput: false,
        btnBuscar: false,
        encontrou: false,

        permissoes: [],
        filtrarDemitidos: false,

        AUTENTICADO,
        cliente_id: '',
        cliente_area_id: 0,

        formulario_open: '',

        colunasTabela: [
            {
                label: 'PCD',
                checked: false,
                id: 'pcd'
            },
            {
                label: 'ENC. DOCUMENTO',
                checked: true,
                id: 'enc_documento'
            },
            {
                label: 'ENC. EXAME',
                checked: true,
                id: 'enc_exame'
            },
            {
                label: 'ENC. TREINAMENTO',
                checked: true,
                id: 'enc_treinamento'
            },
            {
                label: 'RESP. ECAMINHAMENTO',
                checked: true,
                id: 'resp_encaminhamento'
            },
            {
                label: 'CRACHÁ',
                checked: true,
                id: 'cracha'
            },
            {
                label: 'FOTO 3x4',
                checked: true,
                id: 'foto_3x4'
            }
        ],

        exibiFormulario: false,
        possuiCadastro: false,

        urlAnexoUpload: '',
        anexoUploadAndamento: false,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,
        urlExportacao: `${URL_ADMIN}/admissao/export`,

        todos_municipios: `autocomplete/todos-municipios`,

        URL_ADMIN,

        selecionados: [],
        selecionaTudo: false,

        lista_sexos: [],
        lista_estados_civis: [],
        lista_ccs: null,

        formAvulsa: {
            preload: false,
            cadastrado: false,
            cadastrando: false,
            ex_funcionario: false,
            pos_admissao_verificar: false,

            curriculo: {
                cpf: '',
                rg: '',
                rg_data_emissao: '',
                naturalidade: '',
                nome: '',
                nascimento: '',
                pcd: '',
                cid: '',
                email: '',
                logradouro: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                cep: '',
                municipio_id: '',
                cnh: '',

                sexo: '',
                estado_civil: '',

                filiacao_pai: '',
                filiacao_mae: '',

                formacao: 7,
                formacao_curso: '',

                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',

                foto_tres: [],
                foto_tres_delete: [],

                telefones: [
                    {
                        detalhe: '',
                        id: 0,
                        numero: '',
                        pais: '55',
                        principal: true,
                        ramal: '',
                        tipo: 'whatsapp'
                    }
                ],
                telefonesDelete: [],

                dependentes: [],
                dependentesDelete: []
            },

            feedback: {
                selecionado: 'sim',
                vaga_id: '',

                interesse: true,
                vaga_projeto_id: '',
                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

                banco_conta: {
                    banco: 'Banco do Brasil',
                    agencia: '',
                    conta: '',
                    pix: false,
                    tipochavepix: '',
                    chavepix: ''
                }
            },

            parecer_rh: {
                ex_funcionario: '',
                calca: '',
                bota: '',
                camisa_protecao: '',
                camisa_meia: '',
                turnos_seis_por_dois: '',
                indicacao: '',
                indicado_por: ''
            },

            parecer_tecnica: {
                indicado_area: '',
                experiencia_cargas_rigger: 'NÃO SE APLICA',
                opera_plat_movel: 'NÃO SE APLICA',
                opera_plat_ponte: 'NÃO SE APLICA'
            },

            parecer_rota: {
                bairro_rota: '',
                ponto_referencia_rota: '',
                ponto_referencia_residencia: ''
            },

            parecer_teste: {
                qual_teste: '',
                parecer_final_teste: 'NÃO SE APLICA'
            },

            resultado_integrado: {
                documentos_entregue: '',
                documentos_entregue_data: '',
                encaminhado_exame: '',
                encaminhado_exame_data: '',
                encaminhado_treinamento: '',
                encaminhado_treinamento_data: '',
                excessao: '',
                autorizado_por: '',
                responsavel_envio: ''
            },

            admissao: {
                filial: false,
                centro_custo_filial_id: '',
                area_etiqueta_id: '',
                centro_custo_id: '',
                contrato: '',
                funcao: '',
                salario: '0,00',
                status: '',
                documento: '',
                documento_portaria: '',
                tipo_admissao: '',
                tipo_treinamento: '',
                treinamento: '',
                data_treinamento: '',
                carteira_treinamento: '',
                nr_trinta_tres: '',
                data_nr_trinta_tres: '',
                nr_trinta_cinco: '',
                data_nr_trinta_cinco: '',
                trinta_dois_sessenta: '',
                data_trinta_dois_sessenta: '',
                numero_cracha: '',
                pis: '',
                prazo_experiencia: '',
                data_encerramento: '',
                dados_admissoes: {
                    ctps_numero: '',
                    ctps_serie: '',
                    ctps_data_emissao: '',
                    titulo_eleitor_numero: '',
                    titulo_eleitor_sessao: '',
                    titulo_eleitor_zona: '',
                    ctps_uf: '',
                    cert_reservista_num: '',
                    cert_reservista_categoria: ''
                },
                // data_aso: "",
                ultimo_aso: {
                    data_realizacao: ''
                },
                foto_escaneada: '',
                status_carteira_treinamento: '',
                data_admissao: '',
                data_adm_prevista: '',

                data_entrega_area: '',
                biometria: '',
                data_biometria: '',

                indicado_por: '',
                indicado_area: '',

                filiacao_pai: '',
                filiacao_mae: '',
                nome: '',
                calca: '',
                bota: '',
                camisa_protecao: '',
                camisa_meia: '',

                foto_tres: [],
                foto_tresDel: [],
                ferias_adquiridas: [],
                ferias_adquiridasDelete: []
            }
        },

        formAvulsaDefault: null,

        form: {
            id: '',
            vaga_id: '',
            autocomplete_label_vaga_modal: '',
            autocomplete_label_vaga_modal_anterior: '',

            cliente_id: '',
            autocomplete_label_cliente_modal: '',
            autocomplete_label_cliente_modal_anterior: '',
            vaga_projeto_id: '',

            banco_conta: {
                banco: 'Banco do Brasil',
                agencia: '',
                conta: '',
                pix: false,
                tipochavepix: '',
                chavepix: ''
            },

            curriculo: {
                nome: '',
                email: '',
                nascimento: '',
                municipio_id: '',
                rg: '',
                pcd: '',
                sexo: '',
                estado_civil: '',
                rg_data_emissao: '',
                naturalidade: '',
                autocomplete_label_municipio_modal: '',
                autocomplete_label_municipio_modal_anterior: '',
                foto_tres: [],
                foto_tres_delete: [],
                formacao: '',

                telefones: [
                    {
                        detalhe: '',
                        id: 0,
                        numero: '',
                        pais: '55',
                        principal: true,
                        ramal: '',
                        tipo: 'whatsapp'
                    }
                ],
                telefonesDelete: [],

                dependentes: [],
                dependentesDelete: []
            },

            certificados_nr: [],
            certificados_nrDelete: [],
            cursos_formacoes: [],
            cursos_formacoesDelete: [],

            parecer_rh: {
                feedback_id: '',
                formulario_id: '',
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

            admissao: {
                filial: false,
                centro_custo_filial_id: '',
                feedback_id: '',
                area_etiqueta_id: '',
                centro_custo_id: '',
                contrato: '',
                funcao: '',
                salario: '0,00',
                status: '',
                documento: '',
                documento_portaria: '',
                tipo_admissao: '',
                tipo_treinamento: '',
                treinamento: '',
                data_treinamento: '',
                carteira_treinamento: '',
                nr_trinta_tres: '',
                data_nr_trinta_tres: '',
                nr_trinta_cinco: '',
                data_nr_trinta_cinco: '',
                trinta_dois_sessenta: '',
                data_trinta_dois_sessenta: '',
                numero_cracha: '',
                pis: '',
                prazo_experiencia: '',
                data_encerramento: '',
                dados_admissoes: {
                    ctps_numero: '',
                    ctps_serie: '',
                    ctps_data_emissao: '',
                    titulo_eleitor_numero: '',
                    titulo_eleitor_sessao: '',
                    titulo_eleitor_zona: '',
                    ctps_uf: '',
                    cert_reservista_num: '',
                    cert_reservista_categoria: ''
                },
                // data_aso: "",
                ultimo_aso: {
                    data_realizacao: ''
                },
                foto_escaneada: '',
                status_carteira_treinamento: '',
                data_admissao: '',
                data_adm_prevista: '',

                data_entrega_area: '',
                biometria: '',
                data_biometria: '',

                indicado_por: '',
                indicado_area: '',

                filiacao_pai: '',
                filiacao_mae: '',
                nome: '',
                calca: '',
                bota: '',
                camisa_protecao: '',
                camisa_meia: '',

                ferias_adquiridas: [],
                ferias_adquiridasDelete: [],

                foto_tres: [],
                foto_tresDel: []
            },

            resultado_integrado: {
                documentos_entregue: '',
                documentos_entregue_data: '',
                encaminhado_exame: '',
                encaminhado_exame_data: '',
                encaminhado_treinamento: '',
                encaminhado_treinamento_data: '',
                excessao: '',
                autorizado_por: '',
                responsavel_envio: ''
            }
        },

        formDefault: null,

        form_massa: {
            selecionados: null,
            preload: false,
            selecionado: 'sim',
            tipo_admissao: '',
            prazo_experiencia: '',
            data_encerramento: '',
            documento_portaria: '',
            // data_aso: "",
            ultimo_aso: {
                data_realizacao: ''
            },
            status_carteira_treinamento: '',
            segmento_treinamento_id: null,
            status: '',
            data_admissao: '',
            data_entrega_area: '',
            biometria: ''
        },
        form_massaDefault: null,
        segmentos_treinamento: [],

        formResultadoIntegrado: {
            curriculo_id: null
        },
        formResultadoIntegradoDefault: null,

        lista: [],
        listaStatusAdmissao: [],
        listaTipoAdmissao: [],
        vagas: [],
        areasEtiquetas: [],
        listaProjetos: [],

        modeldemissao: {
            preload: false,
            form: {
                admissao_id: '',
                feedback_id: '',
                curriculo_id: '',
                nome: '',
                cpf: '',
                cargo: '',
                funcao: '',
                data_admissao: '',
                status: '',

                cipa: 0,
                data_desmobilizacao: '',
                motivo_rescisao_id: 1,
                outro_motivo: 'Demissão avulsa via sistema',
                tipo_aviso_id: 1,
                solicitado_por: '',
                comentario: 'Demissão avulsa via sistema',
                user_id: ''
            }
        },

        modeldemissaoDefault: null,

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
                cliente_custom: '',
                campoBusca: '',
                campoVaga: '',
                campoLido: '',
                campoFiltro: '',
                campoPcd: '',
                campoCliente: '',
                campoStatusAdmissao: '',
                campoTipoAdmissao: '',
                campoUf: '',
                campoAso: '',
                campoAdmissao: '',
                campoDemitido: false,
                campoCnpj: '',
                campoCentroCusto: '',
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                filtroAso: false,
                dataInicioAso: '',
                dataFimAso: '',
                filtroDataAdmissao: false,
                dataInicioAdmissao: '',
                dataFimAdmissao: '',
                campoCPF: ''
            }
        }
    },
    computed: {
        comAdm() {
            return this.lista.filter(item => {
                return item.admissao
            })
        },
        tudoMarcado() {
            let totalItens = this.comAdm.length
            let totalEncontrado = 0

            if (totalItens === 0) {
                return false
            }

            this.comAdm.forEach(item => {
                let id = item.id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                    //faz nada
                } else {
                    return false
                }
            })
            let resultado = totalItens === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        paramsExport() {
            let params = {
                selecionados: this.selecionados
            }
            return _.merge(params, this.controle.dados)
        },

        // Autenticado() {
        //     console.log(this.$root.$data.AUTENTICADO)
        //     return this.$root.$data.AUTENTICADO;
        // },

        filtroListaCentroCustoCnpj() {
            if (this.controle.dados.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj]
            }
            if (!this.AUTENTICADO.temFilial && this.lista_ccs) {
                return this.lista_ccs.centros_custos[Object.keys(this.lista_ccs.centros_custos)[0]]
            }
            return []
        }

    },
    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true
        }
    },
    async mounted() {
        await this.getColunaTabelas()

        this.formDefault = _.cloneDeep(this.form) //copia
        this.formAvulsaDefault = _.cloneDeep(this.formAvulsa) //copia
        this.form_massaDefault = _.cloneDeep(this.form_massa) //copia
        this.formResultadoIntegradoDefault = _.cloneDeep(this.formResultadoIntegrado) //copia
        this.modeldemissaoDefault = _.cloneDeep(this.modeldemissao) //copia
        this.cliente_id = $('#cliente_id').val()
        if (this.cliente_id) { //diferente de BPSE
            this.controle.dados.campoCliente = parseInt(this.cliente_id)
            this.controle.dados.cliente_custom = parseInt(this.cliente_id)
        }
        this.urlParamGet()
        this.$nextTick(() => {
            const page = this.controle.dados.page
            if (this.$refs.componente && page >= 1) {
                this.$refs.componente.atual = page
            }
        })
        this.atualizar()
        this.listaVagas()
    },
    methods: {
        getDataAso(item) {
            if (item.admissao && item.admissao.ultimo_aso && item.admissao.ultimo_aso.data_realizacao) return item.admissao.ultimo_aso.data_realizacao
            if (item.ultimo_aso && item.ultimo_aso.data_realizacao) return item.ultimo_aso.data_realizacao
            return null
        },
        getDataDemissao(item) {
            if (item.admissao && item.admissao.demissao && item.admissao.demissao.data_desmobilizacao) return item.admissao.demissao.data_desmobilizacao
            return null
        },
        getTipoAdmissao(item) {
            return (item.admissao && item.admissao.tipo_admissao) ? item.admissao.tipo_admissao : null
        },
        async getColunaTabelas() {
            await axios.get(`${URL_ADMIN}/admissao/colunas-tabela-processo`).then(response => {
                this.colunasTabela = response.data
            })
        },
        async atualizaColunaTabelas() {
            await axios.put(`${URL_ADMIN}/admissao/colunas-tabela-processo`, {
                colunasTabela: this.colunasTabela
            })
        },
        formDemitir(item) {
            this.modeldemissao = _.cloneDeep(this.modeldemissaoDefault)
            this.modeldemissao.preload = true

            this.modeldemissao.form.admissao_id = item.admissao.id
            this.modeldemissao.form.curriculo_id = item.curriculo.id
            this.modeldemissao.form.feedback_id = item.admissao.feedback_id
            this.modeldemissao.form.status = item.admissao.status
            this.modeldemissao.form.nome = item.curriculo.nome
            this.modeldemissao.form.cpf = item.curriculo.cpf
            this.modeldemissao.form.cargo = item.admissao.cargo
            this.modeldemissao.form.funcao = item.admissao.funcao
            this.modeldemissao.form.data_admissao = item.admissao.data_admissao

            this.modeldemissao.form.user_id = this.AUTENTICADO.user_id
            this.modeldemissao.form.solicitado_por = this.AUTENTICADO.nome

            this.modeldemissao.preload = false
        },

        async demiteColaborador() {
            this.modeldemissao.preload = true
            await axios.post(`${URL_ADMIN}/admissao/demitir-via-privilegio`, this.modeldemissao.form).then(response => {
                $('#janelaDemitir').modal('hide')
                toastr.success(`Colaborador(a) ${this.modeldemissao.form.nome} foi demitido(a)!`, 'Sucesso!')
                this.modeldemissao = _.cloneDeep(this.modeldemissaoDefault)
                this.atualizar()
            }).catch(error => {
                this.modeldemissao.preload = false
            })
        },
        exibiColunaTabela(label) {
            let coluna = this.colunasTabela.find(item => item.label === label)
            return coluna.checked
        },
        exibiColunaSingleCheckedTabela(label) {
            let coluna = this.colunasTabela.find(item => item.label === label)
            return coluna.checked
        },
        changeCnpj() {
            this.controle.dados.campoCentroCusto = ''
            this.atualizar()
        },
        buscaProjeto(vaga_aberta_id) {
            this.listaProjetos = []
            axios.get(`${URL_ADMIN}/busca-projetos/${vaga_aberta_id}`).then(response => {
                this.listaProjetos = response.data.dados
            }).catch(error => (this.formAvulsa.preload = false))
        },

        buscaTodosProjeto() {
            axios.get(`${URL_ADMIN}/busca-projetos/`).then(response => {
                this.listaProjetos = response.data.dados
            }).catch(error => (this.form.preload = false))
        },

        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.comAdm.map(item => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comAdm.map(item => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        // AVULSA
        buscaCpf() {
            if (valida_cpf_vazio(this.$refs.cpf)) {
                if (this.formAvulsa.curriculo.cpf.length === 14) {
                    this.disabledInput = true
                    this.exibiFormulario = false
                    this.formAvulsa.preload = true

                    axios.post(`${URL_ADMIN}/admissao/busca-cpf`, {
                        cpf: this.formAvulsa.curriculo.cpf
                    }).then(response => {
                        let data = response.data
                        if (data.achou) {
                            Object.assign(this.formAvulsa, response.data)
                            Object.assign(this.form.admissao, response.data.admissao)
                            this.exibiFormulario = true
                            this.formAvulsa.preload = false
                            this.formAvulsa.ex_funcionario = data.ex_funcionario
                            this.formAvulsa.pos_admissao_verificar = data.pos_admissao_verificar
                        }

                        if (!data.achou) {
                            let cpf = this.formAvulsa.curriculo.cpf
                            this.formAvulsa = _.cloneDeep(this.formAvulsaDefault)
                            this.formAvulsa.curriculo.cpf = cpf
                            this.exibiFormulario = true
                            this.formAvulsa.preload = false
                        }
                    })
                        .catch(error => {
                            this.formAvulsa.preload = false
                            this.disabledInput = false
                            this.exibiFormulario = false
                        })
                }
            } else {
                this.disabledInput = false
                this.exibiFormulario = false
                this.formAvulsa.preload = false
            }

        },

        selecionaMunicipioModal(obj) {
            this.formAvulsa.curriculo.municipio_id = obj.id
            this.formAvulsa.curriculo.autocomplete_label_municipio_modal = obj.label
            this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior = obj.label
        },

        resetaCampoMunicipioModal() {
            if (this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior !== this.formAvulsa.curriculo.autocomplete_label_municipio_modal) {
                this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior = ''
                this.formAvulsa.curriculo.autocomplete_label_municipio_modal = ''
                this.formAvulsa.curriculo.municipio_id = ''

                setTimeout(() => {
                    if (this.formAvulsa.curriculo.municipio_id === '') {
                        valida_campo_vazio($('#mun_' + this.hash), 1)
                        $('#janelaAdmissaoAvulsa #mun_' + this.hash).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Município não pode ficar vazio')
                    }
                }, 100)
            }
        },

        selecionaVagaModal(obj) {
            this.formAvulsa.feedback.vaga_id = obj.id
            this.formAvulsa.feedback.autocomplete_label_vaga_modal = obj.label
            this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior = obj.label
            this.buscaProjeto(obj.id)
        },
        resetaCampoVagaModal() {
            if (this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior !== this.formAvulsa.feedback.autocomplete_label_vaga_modal) {
                this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior = ''
                this.formAvulsa.feedback.autocomplete_label_vaga_modal = ''
                this.formAvulsa.feedback.vaga_id = ''
                this.form.feedback.vaga_projeto_id = ''
                this.listaProjetos = []
                setTimeout(() => {
                    if (this.formAvulsa.feedback.vaga_id === '') {
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio')
                    }
                }, 100)
            }
        },

        selecionaVagaModalEditar(obj) {
            this.form.vagas_abertas_id = obj.id
            this.form.autocomplete_label_vaga_modal = obj.label
            this.form.autocomplete_label_vaga_modal_anterior = obj.label
            this.buscaProjeto(obj.id)
        },
        resetaCampoVagaModalEditar() {
            if (this.form.autocomplete_label_vaga_modal_anterior !== this.form.autocomplete_label_vaga_modal) {
                this.form.autocomplete_label_vaga_modal_anterior = ''
                this.form.autocomplete_label_vaga_modal = ''
                this.form.vagas_abertas_id = ''
                this.form.feedback.vaga_projeto_id = ''
                this.listaProjetos = []
                setTimeout(() => {
                    if (this.form.vagas_abertas_id === '') {
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio')
                    }
                }, 100)
            }
        },
        selecionaClienteModal(obj) {
            setTimeout(() => {
                this.formAvulsa.feedback.cliente_id = 0
                this.formAvulsa.feedback.cliente_id = obj.id
                this.formAvulsa.feedback.autocomplete_label_cliente_modal = obj.label
                this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior = obj.label
            }, 50)
        },
        resetaCampoClienteModal() {
            if (this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior !== this.formAvulsa.feedback.autocomplete_label_cliente_modal) {
                this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior = ''
                this.formAvulsa.feedback.autocomplete_label_cliente_modal = ''
                this.formAvulsa.feedback.cliente_id = ''
                setTimeout(() => {
                    if (this.formAvulsa.feedback.cliente_id === '') {
                        mostraErro('', 'O Campo Cliente não pode ficar vazio')
                    }
                }, 100)
            }

        },

        formCadastraAvulsa() {
            this.exibiFormulario = false
            this.disabledInput = false
            this.formAvulsa = _.cloneDeep(this.formAvulsaDefault) //copia
            this.form = _.cloneDeep(this.formDefault) //copia
            this.formulario_open = 'Avulsa'

            this.form.foto_tres = []
            this.form.foto_tresDel = []

            formReset()
            setupCampo()
        },

        CadastraAvulsa() {
            formReset()
            this.validaBlur()
            $('#janelaAdmissaoAvulsa :input:visible').trigger('blur')
            $('#janelaAdmissaoAvulsa :input:visible.is-invalid').length
            let countErro = document.querySelectorAll('.is-invalid').length

            if (countErro > 0) {
                toastr.error('Verifique os campos', 'Atenção!')
                return false
            }

            if (['ADMITIDO', 'PRONTO PARA ADMISSÃO'].includes(this.form.admissao.status)) {
                if (this.form.admissao.data_admissao === '') {
                    valida_campo_vazio($('#data_admissao_' + this.hash), 1)
                    $('#janelaAdmissaoAvulsa #data_admissao_' + this.hash).focus().trigger('blur')
                    return
                }
            }

            if (this.formAvulsa.curriculo.telefones.length === 0) {
                this.formAvulsa.curriculo.telefones.push({
                    detalhe: '',
                    id: 0,
                    numero: '',
                    pais: '55',
                    principal: true,
                    ramal: '',
                    tipo: 'whatsapp'
                })
                mostraErro('', 'Insira pelo menos UM telefone de contato')
                return false
            }

            if (this.formAvulsa.feedback.vaga_id === '') {
                valida_campo_vazio($('#vaga_' + this.hash), 1)
                $('#janelaAdmissaoAvulsa #vaga_' + this.hash).focus().trigger('blur')
                mostraErro('', 'O campo vaga não pode ficar vazio')
                return false
            }

            this.formAvulsa.admissao = this.form.admissao
            this.formAvulsa.preload = true

            axios.post(`${URL_ADMIN}/admissao/admissao`, this.formAvulsa)
                .then(response => {
                    if (response.status === 201) {
                        this.formAvulsa.preload = false
                        this.formAvulsa.cadastrado = true
                        this.atualizar()
                    }
                }).catch(error => (this.formAvulsa.preload = false))
        },


        formCadastraMassa() {
            this.form_massa = _.cloneDeep(this.form_massaDefault) //copia

            formReset()
            setupCampo()
        },

        CadastraMassa() {
            formReset()

            $('#janelaAdmissaoMassa :input:visible').trigger('blur')
            if ($('#janelaAdmissaoMassa :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.form_massa.preload = true
            this.form_massa.selecionados = this.selecionados


            axios.post(`${URL_ADMIN}/admissao/admissao/cadastra-massa`, this.form_massa)
                .then(response => {
                    if (response.status === 201) {
                        this.form_massa.preload = false
                        this.form_massa.cadastrado = true
                        $('#janelaAdmissaoMassa').modal('hide')
                        this.atualizar()
                    }
                }).catch(error => (this.form_massa.preload = false))
        },

        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
        },

        //Form Normal
        formEntrevistar(id) {
            Object.assign(this.form, this.formDefault)
            this.formulario_open = 'Comum'

            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false

            this.preload = true
            this.preloadForm = true

            this.tituloJanela = `#${id}`

            formReset()
            axios.get(`${URL_ADMIN}/admissao/admissao/${id}/editar`)
                .then(response => {
                    let data = response.data
                    this.buscaProjeto(data.feedback.vagas_abertas_id)

                    let admissao = data.feedback.admissao

                    if (!data.feedback.parecer_tecnica) {
                        data.feedback.parecer_tecnica = {
                            'indicado_area': ''
                        }
                    }

                    if (!data.feedback.banco_conta) {
                        data.feedback.banco_conta = {
                            'banco': 'Banco do Brasil',
                            'agencia': '',
                            'conta': '',
                            'pix': false,
                            'tipochavepix': '',
                            'chavepix': ''
                        }
                    }

                    Object.assign(this.form, data.feedback)

                    //Se não tiver parecer_rh
                    this.form.admissao = admissao ? admissao : _.cloneDeep(this.formDefault.admissao)

                    this.form.parecer_rh.indicado_por = data.feedback.parecer_rh ? data.feedback.parecer_rh.indicado_por : ''
                    this.form.parecer_rh.calca = data.feedback.parecer_rh ? data.feedback.parecer_rh.calca : ''
                    this.form.parecer_rh.bota = data.feedback.parecer_rh ? data.feedback.parecer_rh.bota : ''
                    this.form.parecer_rh.camisa_protecao = data.feedback.parecer_rh ? data.feedback.parecer_rh.camisa_protecao : ''
                    this.form.parecer_rh.camisa_meia = data.feedback.parecer_rh ? data.feedback.parecer_rh.camisa_meia : ''
                    this.form.admissao.area_etiqueta_id = admissao.area_etiqueta_id == null ? '' : admissao.area_etiqueta_id
                    this.form.curriculo.pcd = data.feedback.curriculo.pcd ?? 'false'
                    this.form.curriculo.formacao = data.feedback.curriculo.formacao ?? ''

                    this.form.vaga_projeto_id = !data.feedback.vaga_projeto_id ? '' : data.feedback.vaga_projeto_id
                    this.form.parecer_rh.indicacao = !data.feedback.parecer_rh.indicacao ? '' : data.feedback.parecer_rh.indicacao

                    if (!admissao.dados_admissoes) {
                        this.form.admissao.dados_admissoes = {
                            'ctps_numero': '',
                            'ctps_serie': '',
                            'ctps_data_emissao': '',
                            'titulo_eleitor_numero': '',
                            'titulo_eleitor_sessao': '',
                            'titulo_eleitor_zona': ''
                        }
                    }

                    if (!admissao.ultimo_aso) {
                        this.form.admissao.ultimo_aso = {
                            'data_realizacao': ''
                        }
                    }

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`
                    this.cadastrando = true
                    this.preload = false
                })
                .catch(error => {
                    this.preload = false
                })
        },


        alterar() {
            this.validaBlur()
            let countErro = document.querySelectorAll('.is-invalid').length
            if (countErro > 0) {
                toastr.error('Verifique os campos', 'Atenção!')
                return false
            }

            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true

            axios.put(`${URL_ADMIN}/admissao/admissao/${this.form.id}`, this.form)
                .then(response => {
                    this.preload = false
                    this.atualizado = true
                    this.$refs.componente.buscar()
                }).catch(error => {
                this.preload = false
            })

        },
        apagar() {
            this.erros = []
            this.form._method = 'DELETE'
            this.preload = true

            $.post(`${URL_ADMIN}/admissao/admissao/${this.form.id}`, this.form)
                .done((data) => {
                    this.preload = false
                    this.apagado = true
                    this.atualizar()
                })
                .fail((data) => {
                    this.preload = false
                    this.erros = data.erros
                    mostraErro(data.responseJSON)
                })
        },

        listaVagas() {
            this.preload = true
            axios.get(`${URL_PUBLICO}/lista-vagas`)
                .then(res => {
                    this.preload = false
                    this.vagas = res.data.areas
                })
                .catch(err => {
                    this.preload = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preload = false
        },
        carregou(dados) {
            this.lista = dados.itens
            this.listaStatusAdmissao = dados.status_admissao
            this.listaTipoAdmissao = dados.tipos_admissao
            this.editando = dados.admissao_processo_dados_editar
            this.lista_sexos = dados.lista_sexos
            this.lista_estados_civis = dados.lista_estados_civis
            this.segmentos_treinamento = dados.segmentos_treinamento || []
            this.selecionaTudo = this.tudoMarcado
            this.permissoes = dados.permissoes
            this.lista_ccs = dados.cc
            if (!this.AUTENTICADO.temFilial) {
                this.controle.dados.campoCnpj = Object.keys(dados.cc.cnpjs)[0]
            }
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            if (urlParams.get('page')) {
                const p = parseInt(urlParams.get('page'), 10)
                if (p >= 1) this.controle.dados.page = p
            }
            if (urlParams.get('pages')) {
                const pp = parseInt(urlParams.get('pages'), 10)
                if ([10, 20, 50, 100].indexOf(pp) >= 0) this.controle.dados.pages = pp
            }
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoCliente')) this.controle.dados.campoCliente = urlParams.get('campoCliente')
            if (urlParams.get('campoVaga')) this.controle.dados.campoVaga = urlParams.get('campoVaga')
            if (urlParams.get('campoStatusAdmissao')) this.controle.dados.campoStatusAdmissao = urlParams.get('campoStatusAdmissao')
            if (urlParams.get('campoTipoAdmissao')) this.controle.dados.campoTipoAdmissao = urlParams.get('campoTipoAdmissao')
            if (urlParams.get('campoUf')) this.controle.dados.campoUf = urlParams.get('campoUf')
            if (urlParams.get('campoCnpj')) this.controle.dados.campoCnpj = urlParams.get('campoCnpj')
            if (urlParams.get('campoCentroCusto')) this.controle.dados.campoCentroCusto = urlParams.get('campoCentroCusto')
            const fp = urlParams.get('filtroPeriodo')
            if (fp === '1' || fp === 'true') this.controle.dados.filtroPeriodo = true
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            const fa = urlParams.get('filtroAso')
            if (fa === '1' || fa === 'true') this.controle.dados.filtroAso = true
            if (urlParams.get('dataInicioAso')) this.controle.dados.dataInicioAso = urlParams.get('dataInicioAso')
            if (urlParams.get('dataFimAso')) this.controle.dados.dataFimAso = urlParams.get('dataFimAso')
            const fda = urlParams.get('filtroDataAdmissao')
            if (fda === '1' || fda === 'true') this.controle.dados.filtroDataAdmissao = true
            if (urlParams.get('dataInicioAdmissao')) this.controle.dados.dataInicioAdmissao = urlParams.get('dataInicioAdmissao')
            if (urlParams.get('dataFimAdmissao')) this.controle.dados.dataFimAdmissao = urlParams.get('dataFimAdmissao')
            const cd = urlParams.get('campoDemitido')
            if (cd === '1' || cd === 'true') this.controle.dados.campoDemitido = true
            if (cd === '0' || cd === 'false') this.controle.dados.campoDemitido = false
        },
        syncUrlFiltros() {
            const d = this.controle.dados
            const atual = (this.$refs.componente && this.$refs.componente.atual) ? this.$refs.componente.atual : 1
            const params = {}
            if (atual > 1) params.page = atual
            if (d.pages && d.pages !== 20) params.pages = d.pages
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoCliente) params.campoCliente = d.campoCliente
            if (d.campoVaga) params.campoVaga = d.campoVaga
            if (d.campoStatusAdmissao) params.campoStatusAdmissao = d.campoStatusAdmissao
            if (d.campoTipoAdmissao) params.campoTipoAdmissao = d.campoTipoAdmissao
            if (d.campoUf) params.campoUf = d.campoUf
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodo) params.filtroPeriodo = 1
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.filtroAso) params.filtroAso = 1
            if (d.filtroAso && d.dataInicioAso) params.dataInicioAso = d.dataInicioAso
            if (d.filtroAso && d.dataFimAso) params.dataFimAso = d.dataFimAso
            if (d.filtroDataAdmissao) params.filtroDataAdmissao = 1
            if (d.filtroDataAdmissao && d.dataInicioAdmissao) params.dataInicioAdmissao = d.dataInicioAdmissao
            if (d.filtroDataAdmissao && d.dataFimAdmissao) params.dataFimAdmissao = d.dataFimAdmissao
            if (d.campoDemitido) params.campoDemitido = 1
            const qs = new URLSearchParams(params).toString()
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname
            window.history.replaceState({}, '', url)
        }
    }
})
