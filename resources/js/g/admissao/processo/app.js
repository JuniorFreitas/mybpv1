import endereco from "../../../components/Endereco";
import DadosBancarios from "../../../components/DadosBancarios";
import datepicker from "../../../components/DatePicker";
import upload from "../../../components/Upload";
import telefone from "../../../components/Telefones";
import DadosPessoais from "../../../components/entrevistas/DadosPessoaisTexto";
import FormResultadoIntegrado from "../../../components/entrevistas/FormResultadoIntegrado";
import formAdmissao from "../../../components/admissao/processo/formAdmissao";

const app = new Vue({
    el: "#app",
    components: {
        endereco,
        datepicker,
        upload,
        telefone,
        DadosPessoais,
        formAdmissao,
        FormResultadoIntegrado,
        DadosBancarios
    },
    data: {
        tituloJanela: "Admissão",
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        // disabled: true,
        disabledInput: false,
        btnBuscar: false,

        AUTENTICADO,
        cliente_id: "",
        cliente_area_id: 0,

        colunasTabela: {
            cliente: false,
            pcd: false,
            rh_nota: true,
            rota_transporte: true,
            entrevista_tecnica: true,
            teste_pratico: true,
            parecer_individual: true,
            nota_individual: true
        },

        exibiFormulario: false,
        possuiCadastro: false,

        urlAnexoUpload: "",
        anexoUploadAndamento: false,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        todos_municipios: `autocomplete/todos-municipios`,

        URL_ADMIN,

        selecionados: [],
        selecionaTudo: false,

        formAvulsa: {
            preload: false,
            cadastrado: false,
            cadastrando: false,

            curriculo: {
                cpf: "",
                rg: "",
                rg_data_emissao: "",
                naturalidade: "",
                nome: "",
                nascimento: "",
                pcd: "",
                cid: "",
                email: "",
                logradouro: "",
                complemento: "",
                bairro: "",
                municipio: "",
                uf: "",
                cep: "",
                municipio_id: "",
                cnh: "",

                filiacao_pai: "",
                filiacao_mae: "",

                formacao: 7,
                formacao_curso: "",

                autocomplete_label_municipio_modal: "",
                autocomplete_label_municipio_modal_anterior: "",

                foto_tres: [],
                foto_tres_delete: [],

                telefones: [{
                    detalhe: "",
                    id: 0,
                    numero: "",
                    pais: "55",
                    principal: true,
                    ramal: "",
                    tipo: "whatsapp"
                }],
                telefonesDelete: []
            },

            feedback: {
                selecionado: "sim",
                vaga_id: "",

                interesse: true,

                autocomplete_label_vaga_modal: "",
                autocomplete_label_vaga_modal_anterior: "",

                autocomplete_label_cliente_modal: "",
                autocomplete_label_cliente_modal_anterior: "",

                banco_conta: {
                    banco: "Banco do Brasil",
                    agencia: "",
                    conta: "",
                    pix: false,
                    tipochavepix: "",
                    chavepix: ""
                },
            },

            parecer_rh: {
                ex_funcionario: "",
                calca: "",
                bota: "",
                camisa_protecao: "",
                camisa_meia: "",
                turnos_seis_por_dois: "",
                indicacao: "",
                indicado_por: ""
            },

            parecer_tecnica: {
                indicado_area: "",
                experiencia_cargas_rigger: "NÃO SE APLICA",
                opera_plat_movel: "NÃO SE APLICA",
                opera_plat_ponte: "NÃO SE APLICA"
            },

            parecer_rota: {
                bairro_rota: "",
                ponto_referencia_rota: "",
                ponto_referencia_residencia: ""
            },

            parecer_teste: {
                qual_teste: "",
                parecer_final_teste: ""
            },

            resultado_integrado: {
                documentos_entregue: "",
                documentos_entregue_data: "",
                encaminhado_exame: "",
                encaminhado_exame_data: "",
                encaminhado_treinamento: "",
                encaminhado_treinamento_data: "",
                excessao: "",
                autorizado_por: "",
                responsavel_envio: ""
            },

            admissao: {
                area_etiqueta_id: "",
                contrato: "",
                funcao: "",
                cargo: "",
                salario: "0,00",
                status: "",
                documento: "",
                documento_portaria: "",
                tipo_admissao: "",
                tipo_treinamento: "",
                treinamento: "",
                data_treinamento: "",
                carteira_treinamento: "",
                nr_trinta_tres: "",
                data_nr_trinta_tres: "",
                nr_trinta_cinco: "",
                data_nr_trinta_cinco: "",
                trinta_dois_sessenta: "",
                data_trinta_dois_sessenta: "",
                numero_cracha: "",
                pis: "",
                prazo_experiencia: '',
                data_encerramento: '',
                dados_admissoes: {
                    ctps_numero: '',
                    ctps_serie: '',
                    ctps_data_emissao: '',
                    titulo_eleitor_numero: '',
                    titulo_eleitor_sessao: '',
                    titulo_eleitor_zona: '',
                },
                data_aso: "",
                foto_escaneada: "",
                status_carteira_treinamento: "",
                data_admissao: "",

                data_entrega_area: "",
                biometria: "",
                data_biometria: "",

                indicado_por: "",
                indicado_area: "",

                filiacao_pai: "",
                filiacao_mae: "",
                nome: "",
                calca: "",
                bota: "",
                camisa_protecao: "",
                camisa_meia: "",

                foto_tres: [],
                foto_tresDel: []
            },
        },

        formAvulsaDefault: null,

        form: {
            id: "",
            vaga_id: "",
            autocomplete_label_vaga_modal: "",
            autocomplete_label_vaga_modal_anterior: "",

            cliente_id: "",
            autocomplete_label_cliente_modal: "",
            autocomplete_label_cliente_modal_anterior: "",

            banco_conta: {
                banco: "Banco do Brasil",
                agencia: "",
                conta: "",
                pix: false,
                tipochavepix: "",
                chavepix: ""
            },

            curriculo: {
                nome: "",
                email: "",
                nascimento: "",
                municipio_id: "",
                rg: "",
                pcd: "",
                rg_data_emissao: "",
                naturalidade: "",
                autocomplete_label_municipio_modal: "",
                autocomplete_label_municipio_modal_anterior: "",
                foto_tres: [],
                foto_tres_delete: [],

                telefones: [{
                    detalhe: "",
                    id: 0,
                    numero: "",
                    pais: "55",
                    principal: true,
                    ramal: "",
                    tipo: "whatsapp"
                }],
                telefonesDelete: []
            },

            certificados_nr: [],
            certificados_nrDelete: [],
            cursos_formacoes: [],
            cursos_formacoesDelete: [],

            parecer_rh: {
                feedback_id: "",
                formulario_id: "",
                destro: "",
                ex_funcionario: "",
                cnh: "",
                cnh_tipo: "",
                mora_com_quem: "",
                rota_bairro: "",
                calca: "",
                bota: "",
                camisa_protecao: "",
                camisa_meia: "",
                casado: "",
                tempodeconvivencia: "",
                filhos: "",
                qnt_filhos: "",
                conjuge_trabalha: "",
                trabalho_conjuge: "",
                religioso: "",
                religiao_praticante: "",
                fuma: "",
                frequencia_fuma: "",
                bebe: "",
                frequencia_bebe: "",
                nr_dez: "",
                indicacao: "",
                indicado_por: "",
                alumar_experiencia: "",
                alumar_experiencia_area: "",
                outra_industria_experiencia: "",
                outra_industria_nome: "",
                grau_instrucao: "",
                horaextra: "",
                turnos_seis_por_dois: "",
                noturno: "",
                acidente_trabalho: "",
                acidente_trabalho_qual: "",
                afastamento_inss: "",
                afastamento_inss_qual: "",
                situacao_saude: "",
                comportamento_seguro: "",
                energia_para_trabalho: "",
                postura: "",
                historico_profissional: "",
                historico_educacional: "",
                objetivos_expectativas: "",
                auto_imagem: "",
                competencias: "",
                comportamento_etico: "",
                comprometimento: "",
                comunicacao: "",
                cultura_qualidade: "",
                foco_cliente: "",
                iniciativa: "",
                orientacao_resultados: "",
                trabalho_equipe: "",
                parecer_final: "",
                parecer_final_um: "",
                nota: "",
                comentarios: "",
                entrevistador: "",
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
                    parecer: "",
                    nota: "",
                    entrevistado_por: "",
                    comentario: "",
                    avaliacao_psicologica: ""
                },

                gestor_rh: {
                    parecer: "",
                    indicado_para: "",
                    nota: "",
                    entrevistado_por: "",
                    comentario: ""
                },

                entrevista_rh: {
                    parecer: "",
                    indicado_para: "",
                    nota: "",
                    entrevistado_por: "",
                    comentario: ""
                }
            },

            admissao: {
                feedback_id: "",
                area_etiqueta_id: "",
                contrato: "",
                funcao: "",
                cargo: "",
                salario: "0,00",
                status: "",
                documento: "",
                documento_portaria: "",
                tipo_admissao: "",
                tipo_treinamento: "",
                treinamento: "",
                data_treinamento: "",
                carteira_treinamento: "",
                nr_trinta_tres: "",
                data_nr_trinta_tres: "",
                nr_trinta_cinco: "",
                data_nr_trinta_cinco: "",
                trinta_dois_sessenta: "",
                data_trinta_dois_sessenta: "",
                numero_cracha: "",
                pis: "",
                prazo_experiencia: '',
                data_encerramento: '',
                dados_admissoes: {
                    ctps_numero: '',
                    ctps_serie: '',
                    ctps_data_emissao: '',
                    titulo_eleitor_numero: '',
                    titulo_eleitor_sessao: '',
                    titulo_eleitor_zona: '',
                },
                data_aso: "",
                foto_escaneada: "",
                status_carteira_treinamento: "",
                data_admissao: "",

                data_entrega_area: "",
                biometria: "",
                data_biometria: "",

                indicado_por: "",
                indicado_area: "",

                filiacao_pai: "",
                filiacao_mae: "",
                nome: "",
                calca: "",
                bota: "",
                camisa_protecao: "",
                camisa_meia: "",

                foto_tres: [],
                foto_tresDel: []
            },

            resultado_integrado: {
                documentos_entregue: "",
                documentos_entregue_data: "",
                encaminhado_exame: "",
                encaminhado_exame_data: "",
                encaminhado_treinamento: "",
                encaminhado_treinamento_data: "",
                excessao: "",
                autorizado_por: "",
                responsavel_envio: ""
            },
        },

        formDefault: null,

        formResultadoIntegrado: {
            curriculo_id: null
        },
        formResultadoIntegradoDefault: null,

        lista: [],
        vagas: [],
        areasEtiquetas: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: "",
                autocomplete_label: "",
                caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
                autocomplete_label_cliente_anterior: "",
                autocomplete_label_cliente: "",
                pages: 20,
                cliente_custom: "",
                campoBusca: "",
                campoVaga: "",
                campoLido: "",
                campoFiltro: "",
                campoPcd: "",
                campoCliente: "",
                campoUf: "MA"
            }
        }
    },
    computed: {
        comAdm() {
            return this.lista.filter(item => {
                return item.admissao;
            });
        },
        tudoMarcado() {
            let totalItens = this.comAdm.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }

            this.comAdm.forEach(item => {
                let id = item.id;
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++;
                    //faz nada
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
        this.formDefault = _.cloneDeep(this.form); //copia
        this.formAvulsaDefault = _.cloneDeep(this.formAvulsa); //copia
        this.formResultadoIntegradoDefault = _.cloneDeep(this.formResultadoIntegrado); //copia
        this.cliente_id = $("#cliente_id").val();
        if (this.cliente_id) { //diferente de BPSE
            this.controle.dados.campoCliente = parseInt(this.cliente_id);
            this.controle.dados.cliente_custom = parseInt(this.cliente_id);
        }
        this.atualizar();
        this.listaVagas();
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comAdm.map(item => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id);
                    }
                });
            } else {
                this.comAdm.map(item => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1);
                    }
                });
            }
        },
        // AVULSA
        buscaCpf() {
            if (valida_cpf_vazio(this.$refs.cpf)) {
                if (this.formAvulsa.curriculo.cpf.length === 14) {
                    this.disabledInput = true;
                    this.exibiFormulario = false;
                    this.formAvulsa.preload = true;

                    axios.post(`${URL_ADMIN}/admissao/busca-cpf`, {
                        cpf: this.formAvulsa.curriculo.cpf
                    }).then(response => {
                        let data = response.data;
                        if (data.achou) {
                            Object.assign(this.formAvulsa, response.data);
                            this.exibiFormulario = true;
                            this.formAvulsa.preload = false;
                        }

                        if (!data.achou) {
                            let cpf = this.formAvulsa.curriculo.cpf;
                            this.formAvulsa = _.cloneDeep(this.formAvulsaDefault);
                            this.formAvulsa.curriculo.cpf = cpf;
                            this.exibiFormulario = true;
                            this.formAvulsa.preload = false;
                        }
                    })
                        .catch(error => {
                            this.formAvulsa.preload = false;
                            this.disabledInput = false;
                            this.exibiFormulario = false;
                        });
                }
            } else {
                this.disabledInput = false;
                this.exibiFormulario = false;
                this.formAvulsa.preload = false;
            }

        },

        selecionaMunicipioModal(obj) {
            this.formAvulsa.curriculo.municipio_id = obj.id;
            this.formAvulsa.curriculo.autocomplete_label_municipio_modal = obj.label;
            this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior = obj.label;
        },

        resetaCampoMunicipioModal() {
            if (this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior !== this.formAvulsa.curriculo.autocomplete_label_municipio_modal) {
                this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior = "";
                this.formAvulsa.curriculo.autocomplete_label_municipio_modal = "";
                this.formAvulsa.curriculo.municipio_id = "";

                setTimeout(() => {
                    if (this.formAvulsa.curriculo.municipio_id === "") {
                        valida_campo_vazio($("#mun_" + this.hash), 1);
                        $("#janelaAdmissaoAvulsa #mun_" + this.hash).focus().trigger("blur");
                        mostraErro("Erro", "O Campo Município não pode ficar vazio");
                    }
                }, 100);
            }
        },

        selecionaVagaModal(obj) {
            this.formAvulsa.feedback.vaga_id = obj.id;
            this.formAvulsa.feedback.autocomplete_label_vaga_modal = obj.label;
            this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior = obj.label;
        },
        resetaCampoVagaModal() {
            if (this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior !== this.formAvulsa.feedback.autocomplete_label_vaga_modal) {
                this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior = "";
                this.formAvulsa.feedback.autocomplete_label_vaga_modal = "";
                this.formAvulsa.feedback.vaga_id = "";
                setTimeout(() => {
                    if (this.formAvulsa.feedback.vaga_id === "") {
                        mostraErro("Erro", "O Campo Vaga não pode ficar vazio");
                    }
                }, 100);
            }
        },

        selecionaVagaModalEditar(obj) {
            this.form.vagas_abertas_id = obj.id;
            this.form.autocomplete_label_vaga_modal = obj.label;
            this.form.autocomplete_label_vaga_modal_anterior = obj.label;
        },
        resetaCampoVagaModalEditar() {
            if (this.form.autocomplete_label_vaga_modal_anterior !== this.form.autocomplete_label_vaga_modal) {
                this.form.autocomplete_label_vaga_modal_anterior = "";
                this.form.autocomplete_label_vaga_modal = "";
                this.form.vagas_abertas_id = "";
                setTimeout(() => {
                    if (this.form.vagas_abertas_id === "") {
                        mostraErro("Erro", "O Campo Vaga não pode ficar vazio");
                    }
                }, 100);
            }
        },
        selecionaClienteModal(obj) {
            setTimeout(() => {
                this.formAvulsa.feedback.cliente_id = 0;
                this.formAvulsa.feedback.cliente_id = obj.id;
                this.formAvulsa.feedback.autocomplete_label_cliente_modal = obj.label;
                this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior = obj.label;
            }, 50);
        },
        resetaCampoClienteModal() {
            if (this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior !== this.formAvulsa.feedback.autocomplete_label_cliente_modal) {
                this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior = "";
                this.formAvulsa.feedback.autocomplete_label_cliente_modal = "";
                this.formAvulsa.feedback.cliente_id = "";
                setTimeout(() => {
                    if (this.formAvulsa.feedback.cliente_id === "") {
                        mostraErro("", "O Campo Cliente não pode ficar vazio");
                    }
                }, 100);
            }

        },

        formCadastraAvulsa() {
            this.exibiFormulario = false;
            this.disabledInput = false;
            this.formAvulsa = _.cloneDeep(this.formAvulsaDefault); //copia
            this.form = _.cloneDeep(this.formDefault); //copia

            this.form.foto_tres = [];
            this.form.foto_tresDel = [];

            formReset();
            setupCampo();
        },

        CadastraAvulsa() {
            formReset();

            if (this.formAvulsa.feedback.vaga_id === "") {
                valida_campo_vazio($("#vaga_" + this.hash), 1);
                $("#janelaAdmissaoAvulsa #vaga_" + this.hash).focus().trigger("blur");
                mostraErro("", "O campo vaga não pode ficar vazio");
                return false;
            }

            // if (this.formAvulsa.curriculo.municipio_id === "") {
            //     valida_campo_vazio($("#mun_" + this.hash), 1);
            //     $("#janelaAdmissaoAvulsa #mun_" + this.hash).focus().trigger("blur");
            //     mostraErro("", "O Campo Cidade não pode ficar vazio");
            //     return false;
            // }

            // if (this.formAvulsa.feedback.cliente_id === "") {
            //     valida_campo_vazio($("#cliente_" + this.hash), 1);
            //     $("#janelaAdmissaoAvulsa #cliente_" + this.hash).focus().trigger("blur");
            //     mostraErro("", "O Campo Cliente não pode ficar vazio");
            //     return false;
            // }

            if (this.formAvulsa.curriculo.telefones.length === 0) {
                this.formAvulsa.curriculo.telefones.push({
                    detalhe: "",
                    id: 0,
                    numero: "",
                    pais: "55",
                    principal: true,
                    ramal: "",
                    tipo: "whatsapp"
                });
                mostraErro("", "Insira pelo menos UM telefone de contato");
                return false;
            }


            $("#janelaAdmissaoAvulsa :input:visible").trigger("blur");
            if ($("#janelaAdmissaoAvulsa :input:visible.is-invalid").length) {
                mostraErro("", "Verifique os erros");
                return false;
            }

            this.formAvulsa.admissao = this.form.admissao;
            this.formAvulsa.preload = true;


            axios.post(`${URL_ADMIN}/admissao`, this.formAvulsa)
                .then(response => {
                    if (response.status === 201) {
                        this.formAvulsa.preload = false;
                        this.formAvulsa.cadastrado = true;
                        this.atualizar();
                    }
                }).catch(error => (this.formAvulsa.preload = false));
        },

        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = "";
                this.controle.dados.autocomplete_label = "";
                this.controle.dados.campoVaga = "";
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = "";
                this.controle.dados.autocomplete_label_cliente = "";
                this.controle.dados.campoCliente = "";
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id;
            this.controle.dados.autocomplete_label_cliente = obj.label;
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label;
        },

        //Form Normal
        formEntrevistar(id) {
            Object.assign(this.form, this.formDefault);

            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.cadastrando = false;

            this.preload = true;
            this.preloadForm = true;

            this.tituloJanela = `#${id}`;

            formReset();
            axios.get(`${URL_ADMIN}/admissao/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    let admissao = data.feedback.admissao;
                    Object.assign(this.form, data.feedback);

                    //Se não tiver parecer_rh
                    this.form.admissao = admissao ? admissao : _.cloneDeep(this.formDefault.admissao);

                    this.form.parecer_rh.indicado_por = data.feedback.parecer_rh ? data.feedback.parecer_rh.indicado_por : "";
                    this.form.parecer_rh.calca = data.feedback.parecer_rh ? data.feedback.parecer_rh.calca : "";
                    this.form.parecer_rh.bota = data.feedback.parecer_rh ? data.feedback.parecer_rh.bota : "";
                    this.form.parecer_rh.camisa_protecao = data.feedback.parecer_rh ? data.feedback.parecer_rh.camisa_protecao : "";
                    this.form.parecer_rh.camisa_meia = data.feedback.parecer_rh ? data.feedback.parecer_rh.camisa_meia : "";
                    this.form.admissao.area_etiqueta_id = admissao.area_etiqueta_id == null ? "" : admissao.area_etiqueta_id;
                    this.form.curriculo.pcd = data.feedback.curriculo.pcd ?? 'false';

                    this.form.parecer_tecnica.indicado_area = data.parecer_tecnica ? data.parecer_tecnica.indicado_area : "";

                    if (!admissao.dados_admissoes) {
                        this.form.admissao.dados_admissoes = {
                            'ctps_numero': '',
                            'ctps_serie': '',
                            'ctps_data_emissao': '',
                            'titulo_eleitor_numero': '',
                            'titulo_eleitor_sessao': '',
                            'titulo_eleitor_zona': '',
                        }
                    }

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`;
                    this.cadastrando = true;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });
        },


        alterar() {
            $("#janelaCadastrar :input:visible").trigger("blur");
            if ($("#janelaCadastrar :input:visible.is-invalid").length) {
                mostraErro("", "Verifique os erros");
                return false;
            }
            this.preload = true;

            axios.put(`${URL_ADMIN}/admissao/${this.form.id}`, this.form)
                .then(response => {
                    this.preload = false;
                    this.atualizado = true;
                    this.$refs.componente.buscar();
                }).catch(error => {
                this.preload = false;
            });

        },
        apagar() {
            this.erros = [];
            this.form._method = "DELETE";
            this.preload = true;

            $.post(`${URL_ADMIN}/admissao/${this.form.id}`, this.form)
                .done((data) => {
                    this.preload = false;
                    this.apagado = true;
                    this.atualizar();
                })
                .fail((data) => {
                    this.preload = false;
                    this.erros = data.erros;
                    mostraErro(data.responseJSON);
                });
        },

        listaVagas() {
            this.preload = true;
            axios.get(`${URL_PUBLICO}/lista-vagas`)
                .then(res => {
                    this.preload = false;
                    this.vagas = res.data.vagas;
                })
                .catch(err => {
                    this.preload = false;
                });
        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },
        carregou(dados) {
            this.lista = dados.itens;
            this.editando = dados.admissao_processo_dados_editar;
            this.selecionaTudo = this.tudoMarcado;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
});
