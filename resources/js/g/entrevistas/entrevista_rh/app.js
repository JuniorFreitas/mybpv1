import endereco from "../../../components/Endereco"
import datepicker from "../../../components/DatePicker"
import DadosPessoais from "../../../components/entrevistas/DadosPessoaisTexto";
import FormRh from "../../../components/entrevistas/FormParecerRh";

const app = new Vue({
    el: '#app',
    components: {
        endereco,
        datepicker,
        DadosPessoais,
        FormRh
    },
    data: {
        tituloJanela: 'Parecer Entrevista RH',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        todos_municipios: `autocomplete/todos-municipios`,

        cliente_id: '',
        cliente_area_id: 0,
        provas: 0,

        preloadForm: true,

        colunasTabela: {
            cliente: false,
            pcd: false,
            rh_nota: true,
            rota_transporte: true,
            entrevista_tecnica: true,
            teste_pratico: true,
            parecer_individual: true,
            nota_individual: true,
        },

        URL_ADMIN,
        selecionados: [],
        selecionaTudo: false,

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
                autocomplete_label_municipio_modal_anterior: '',
            },

            certificados_nr: [],
            certificados_nrDelete: [],
            cursos_formacoes: [],
            cursos_formacoesDelete: [],

            parecer_rh: {
                feedback_id: '',
                formulario_id: '',
                tipo_entrevista: "Fixo",
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
                quem_entrevistou: "",

                nota_digitacao: "",
                dinamicadegrupo: "",
                obs_dinamicadegrupo: "",
                experiencia_callcenter: "",
                disponibilidade_horarios: "",
                turnos_seis_por_um: "",
                horario_preferencial: "",
                obs_call: "",
                obs_horario: "",


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
                },
            },

            simulados: []


        },

        formDefault: null,

        lista: [],
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
                periodo: '',
            },
        },
    },
    computed: {
        // cliente_comercio() {
        //     return this.form.cliente_id === 35;
        // },
        industria() {
            return this.cliente_id === 1 || this.cliente_id !== 35 && this.controle.dados.campoCliente !== 35;
        },
        servico() {
            return this.cliente_id === 1 || this.cliente_id === 35 && this.controle.dados.campoCliente === 35;
        },
        comEntrevista() {
            return this.lista.filter(item => {
                return item.parecer_rh.entrevista_rh;
            });
        },
        tudoMarcado() {
            let totalItens = this.comEntrevista.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }

            this.comEntrevista.forEach(item => {
                let id = item.id;
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++;
                } else {
                    return false;
                }
            });
            let resultado = totalItens === totalEncontrado;
            this.selecionaTudo = resultado;
            return resultado;
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.usuarioAutenticado();
        this.listaVagas();
        setTimeout(() => {
            this.atualizar();
        },200)
    },
    methods: {
        /***Campos de Filtros ****/
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = '';
                this.controle.dados.autocomplete_label = '';
                this.controle.dados.campoVaga = '';
                this.$refs.componente.buscar();
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);
        },
        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = '';
                this.controle.dados.autocomplete_label_cliente = '';
                this.controle.dados.campoCliente = '';
                this.$refs.componente.buscar();
            }
        },
        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id;
            this.controle.dados.autocomplete_label_cliente = obj.label;
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label;
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comEntrevista.map(item => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                });
            } else {
                this.comEntrevista.map(item => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                });
            }
        },

        formEntrevistar(id) {
            Object.assign(this.form, this.formDefault);

            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.cadastrando = false;
            this.visualizar = false;
            this.editando = false;

            this.preload = true;
            this.preloadForm= true;

            this.tituloJanela = `#${id}`;

            formReset();
            axios.get(`${URL_ADMIN}/entrevistas/parecer_rh/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data.feedback);

                    //Se não tiver parecer_rh
                    this.form.parecer_rh = data.feedback.parecer_rh ? data.feedback.parecer_rh : _.cloneDeep(this.formDefault.parecer_rh);
                    this.form.parecer_rh.gestor_rh = data.feedback.parecer_rh.gestor_rh ? data.feedback.parecer_rh.gestor_rh : _.cloneDeep(this.formDefault.parecer_rh.gestor_rh);
                    this.form.parecer_rh.entrevista_rh = data.feedback.parecer_rh.entrevista_rh ? data.feedback.parecer_rh.entrevista_rh : _.cloneDeep(this.formDefault.parecer_rh.entrevista_rh);

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`;
                    this.cadastrando = true;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        cadastrar() {

            if (this.form.curriculo.municipio_id === '') {
                valida_campo_vazio($('#mun_' + this.hash), 1);
                mostraErro('', 'Campo MUNICÍPIO não pode ficar vazio');
                this.resetaCampoMunicipioModal();
                return false;
            }

            if (this.form.vaga_id === '') {
                valida_campo_vazio($('#vaga_' + this.hash), 1);
                mostraErro('', 'Campo VAGA não pode ficar vazio');
                this.resetaCampoVagaModal();
                return false;
            }

            if (this.form.cliente_id === '') {
                valida_campo_vazio($('#cliente_' + this.hash), 1);
                mostraErro('', 'Campo EMPRESA não pode ficar vazio');
                this.resetaCampoClienteModal();
                return false;
            }

            $('#janelaParecerEntrevista :input:visible').trigger('blur');
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }
            if (this.nr_dez === 'sim') {
                if (this.nr.length === 0) {
                    mostraErro('', 'Por favor insira o Certificado NR 10');
                    return false;
                }
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/entrevistas/entrevista-rh/`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Entrevista salva com sucesso!');
                    $('#janelaParecerEntrevista').modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        alterar() {
            if (this.form.curriculo.municipio_id === '') {
                valida_campo_vazio($('#mun_' + this.hash), 1);
                mostraErro('', 'Campo MUNICÍPIO não pode ficar vazio');
                this.resetaCampoMunicipioModal();
                return false;
            }

            if (this.form.vaga_id === '') {
                valida_campo_vazio($('#vaga_' + this.hash), 1);
                mostraErro('', 'Campo VAGA não pode ficar vazio');
                this.resetaCampoVagaModal();
                return false;
            }

            if (this.form.cliente_id === '') {
                valida_campo_vazio($('#cliente_' + this.hash), 1);
                mostraErro('', 'Campo EMPRESA não pode ficar vazio');
                this.resetaCampoClienteModal();
                return false;
            }

            $('#janelaParecerEntrevista :input:visible').trigger('blur');
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }
            if (this.nr_dez === 'sim') {
                if (this.nr.length === 0) {
                    mostraErro('', 'Por favor insira o Certificado NR 10');
                    return false;
                }
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/entrevistas/entrevista-rh/${this.form.parecer_rh.entrevista_rh.id}`, this.form.parecer_rh.entrevista_rh)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Entrevista salva com sucesso!');
                    $('#janelaParecerEntrevista').modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        listaVagas() {
            this.preload = true;
            axios.get(`${URL_PUBLICO}/lista-vagas`)
                .then(res => {
                    this.preload = false;
                    this.vagas = res.data.vagas;
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },

        usuarioAutenticado(){
            this.controle.carregando = true;
            axios.get(`${URL_ADMIN}/usuario/autenticado/`)
                .then(response => {
                    let data = response.data;
                    this.cliente_id = data.cliente_id;
                    this.cliente_area_id = data.area_id;

                    if (this.cliente_id > 0) {

                        if (this.cliente_area_id === 1) { //for Industrial
                            this.colunasTabela.cliente = false;
                            this.colunasTabela.pcd = false;
                            this.colunasTabela.rh_nota = true;
                            this.colunasTabela.rota_transporte = true;
                            this.colunasTabela.entrevista_tecnica = true;
                            this.colunasTabela.teste_pratico = true;
                            this.colunasTabela.parecer_individual = false;
                            this.colunasTabela.nota_individual = false;
                        }
                        if (this.cliente_area_id > 1) { //for Servico ou Comercio
                            this.colunasTabela.cliente = false;
                            this.colunasTabela.pcd = true;
                            this.colunasTabela.rh_nota = false;
                            this.colunasTabela.rota_transporte = false;
                            this.colunasTabela.entrevista_tecnica = false;
                            this.colunasTabela.teste_pratico = false;
                            this.colunasTabela.parecer_individual = true;
                            this.colunasTabela.nota_individual = true;
                        }

                    }else{
                        this.colunasTabela.cliente = true;
                        this.colunasTabela.pcd = false;
                        this.colunasTabela.rh_nota = true;
                        this.colunasTabela.rota_transporte = false;
                        this.colunasTabela.entrevista_tecnica = false;
                        this.colunasTabela.teste_pratico = false;
                        this.colunasTabela.parecer_individual = true;
                        this.colunasTabela.nota_individual = false;
                    }

                    this.colunasTabela.cliente = this.cliente_id === 0;
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente;
                })
                .catch(error => {
                    this.preload = false;
                })
        },
        carregou(dados) {
            this.lista = dados.itens;
            this.selecionaTudo = this.tudoMarcado;
            this.controle.carregando = false;

        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },

    }
})
