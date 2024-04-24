import datepicker from "../../components/DatePicker";
import ExportacaoMixin from "../../mixins/Exportacoes";
import Utils from "../../mixins/Utils";
import XLSX from "xlsx";

const app = new Vue({
    mixins: [ExportacaoMixin, Utils],

    el: "#app",
    components: {
        datepicker
    },
    data: {
        AUTENTICADO,
        tituloJanela: "Carregando ...",
        tituloJanelaEntrevista: "Carregando ...",
        preload: false,
        editando: false,
        demitido: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        avaliacao: false,
        desmobilizacao: false,
        entrevista: false,
        revertendo_status: false,
        extensaoDocumento: null,
        preloadExportacao: false,

        urlExportacao: `${URL_ADMIN}/posadmissao/export`,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,
        todos_municipios: `autocomplete/todos-municipios`,

        URL_ADMIN,

        selecionados: [],
        selecionaTudo: false,

        form: {

            demissao: {
                cipa: false,
                data_desmobilizacao: "",
                motivo_rescisao_id: "",
                tipo_aviso_id: "",
                solicitado_por: "",
                comentario: ""
            },

            tipo_form: "",
            data_desmobilizacao: "",
            avaliacao: "",
            obs_avaliacao: "",
            user_avaliacao: "",
            responsavel_feedback: "",
            data_avaliacao: "",
            motivo_rescisao: "",
            classificacao_rescisao: "",
            tipo_aviso: "",

            motivo: "",
            aviso: "",
            classificacao: "",

            //campos extras
            outromotivo: "",
            quem_classificou: "",
            observacoes: "",

            preenchido_por: "",

            alternativas: null,
            pendencia: "",
            pendencias_quais: "",
            outros: "",

            preenchido_por_rh: "",
            preenchido_por_adm: "",
            preenchido_por_ssma: "",

            entrevista_desligamento: {
                curriculo_id: "",
                superior_imediato: "",
                motivo: "",
                trabalharia_novamente: "",
                contr_melhoria: "",
                relacao_interpessoal: "",
                recursos_fisicos: "",
                valores_normas: "",
                planejamento: "",
                sob_superior_imediato: "",
                direcao_empresa: "",
                oportunidades: "",
                salario_beneficio: "",
                atividade: "",
                comentarios: "",
                parecer_entrevistador: "",
                pode_voltar: "",
                porque_pode_voltar: "",
                quem_entrevistou: "",
                data_entrevista: "",
                preenchido_por: ""
            }

        },

        formDefault: null,

        preloadEntrevista: false,
        atualizadoEntrevista: false,
        cadastrandoEntrevista: false,


        entrevista_desligamentoDefault: null,

        alternativasDefault: null,

        lista: [],
        listaMotivos: [],
        listaAvisos: [],
        listaClassificacoes: [],
        formulario: [],
        vagas: [],
        listaAreas: [],
        lista_ccs: null,

        posadmissao_form_rh: false,
        posadmissao_form_adm: false,
        posadmissao_form_ssma: false,

        auditoria_form: {
            empresa_id: "",
            usuario_id: "",
            feedback_id: "",
            colaborador_id: "",
            tipo: "remover_demissao",
            descricao: "",
            dados: {
                nome: "",
                cpf: "",
                vaga: "",
                cargo: "",
                funcao: "",
                data_admissao: "",
                data_demissao: "",
                autenticado_nome: "",
                termo: "",
                motivo: "",
                token: "",
            },
        },

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: "",
                autocomplete_label: "",
                pages: 20,

                campoBusca: "",
                campoArea: "",
                campoVaga: "",
                campoLido: "",
                campoFiltro: "",
                campoPcd: "",
                campoUf: "",
                campoFeedback: "",
                campoCPF: "",
                status: "",
                campoCargo: "",
                campoCnpj: "",
                campoCentroCusto: "",
            }
        }
    },
    computed: {
        textoDefaultAuditoria() {
            return `<p>
                        Ao clicar em "Remover Demissão" e reverter o status de demissão para admissão do colaborador
                        <strong>${this.auditoria_form.dados.nome ?? ""}</strong>, eu,
                        <strong>${this.auditoria_form.dados.autenticado_nome ?? ""}</strong>, reconheço e aceito que estou assumindo a
                        responsabilidade por esta ação.
                        <br>
                        Além disso, declaro que:
                        <br><br>
                        Estou ciente de que a reversão do status de demissão para admissão implica em uma ação
                        irreversível no sistema.
                        <br><br>
                        Confirmo que revisei cuidadosamente todas as informações relevantes relacionadas à remoção da
                        demissão e à restauração do status de admissão.
                        <br><br>
                        Comprometo-me a fornecer um motivo válido e justificável para esta ação, conforme solicitado
                        pelo sistema.
                        <br><br>
                        Aceito total responsabilidade por quaisquer consequências decorrentes da reversão do status.
                        <br><br>
                        Assumo que, ao clicar em "Remover Demissão" e reverter o status de demissão para admissão no
                        sistema MyBP, estou ciente e concordo com as disposições deste termo de responsabilidade.
                    </p>`
        },
        comDemissao() {
            return this.lista.filter(item => {
                return item.demissao;
            });
        },
        formulariosAtivos() {
            const formularios = [];
            if (this.posadmissao_form_rh) {
                formularios.push(this.formulario.setores.filter(item => {
                    return item.nome === "Recursos Humanos";
                })[0]);
            }
            if (this.posadmissao_form_adm) {
                formularios.push(this.formulario.setores.filter(item => {
                    return item.nome === "ALMOXARIFADO / ADM";
                })[0]);
            }
            if (this.posadmissao_form_ssma) {
                formularios.push(this.formulario.setores.filter(item => {
                    return item.nome === "SEGURANÇA DO TRABALHO / SSMA";
                })[0]);
            }
            return formularios;
        },
        tudoMarcado() {
            let totalItens = this.comDemissao.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }
            this.comDemissao.forEach(item => {
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
        },
        filtroListaCentroCustoCnpj() {
            if (this.controle.dados.campoCnpj !== "" && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj];
            }
            if (!this.AUTENTICADO.temFilial && this.lista_ccs) {
                return this.lista_ccs.centros_custos[Object.keys(this.lista_ccs.centros_custos)[0]];
            }
            return [];
        },
        filtroStatusDemitidoOuAdmitido() {
            return ['admitidos', 'demitidos'].includes(this.controle.dados.status);
        },
        paramsExport() {
            let params = {
                selecionados: this.selecionados,
            }
            return _.merge(params, this.controle.dados);
        },

    },

    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.queryParamsCpf();
        this.atualizar();
        this.listaVagas();
        this.listaAreasGeral();
        // ?checkcpf=015.020.903-76
    },
    methods: {
        queryParamsCpf() {
            const queryString = window.location.search;
            const params = new URLSearchParams(queryString);
            this.controle.dados.campoCPF = params.has('checkcpf') ? params.get('checkcpf') : "";
        },
        async gerarArquivoXls() {
            mostraSucesso("", "Aguarde estamos gerando o seu excel");
            const XLSX = require("xlsx");

            const dataHoraAtual = new Date().toLocaleString("en-US", {
                timeZone: "America/Sao_Paulo",
                hour12: false,
            }).replace(/\/|,|\s|:/g, "_")
                .replace(/\//g, "-");

            const filename = `pos_admissao_${AUTENTICADO.empresa_id}_${AUTENTICADO.user_id}_${dataHoraAtual}.xlsx`;

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet([]);

            await axios.post(this.urlExportacao, this.paramsExport)
                .then(({data}) => {
                    let cabecalho = data.head;
                    const jsonDataArray = data.rows;

                    XLSX.utils.sheet_add_aoa(ws, [
                        cabecalho
                    ], {origin: 0});

                    jsonDataArray.forEach(function (jsonData) {
                        XLSX.utils.sheet_add_aoa(ws, [jsonData], {origin: -1});
                    });
                    //
                    XLSX.utils.book_append_sheet(wb, ws, 'planilha');
                    XLSX.writeFile(wb, filename);
                });
        },
        changeCnpj() {
            this.controle.dados.campoCentroCusto = "";
            this.atualizar();
        },
        changeStatus() {
            if (this.controle.dados.status === "") {
                this.controle.dados.campoCnpj = "";
                this.controle.dados.campoCentroCusto = "";
            }
            this.atualizar();
        },
        extensao(item) {
            if (item === "demissao_com_justa_causa") {
                this.extensaoDocumento = ".doc";
            } else {
                this.extensaoDocumento = ".pdf";
            }
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comDemissao.map(item => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id);
                    }
                });
            } else {
                this.comDemissao.map(item => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1);
                    }
                });
            }
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

        formVisualizar(id) {
            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.visualizar = true;

            this.preload = true;
            Object.assign(this.form, this.formDefault);

            formReset();
            axios.get(`${URL_ADMIN}/admissao/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    Object.assign(this.form, data["parecer_teste"]);
                    this.tituloJanela = `Parecer Teste Prático - ${data.curriculo.nome}`;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });

        },
        formAvaliar(curriculo_id) {
            this.tituloJanela = `Demissão ${curriculo_id}`;
            this.cadastrando = true;
            this.atualizado = false;
            this.preload = true;

            this.avaliacao = true;
            this.desmobilizacao = false;
            this.entrevista = false;

            this.form = _.cloneDeep(this.formDefault);
            this.demitido = false;

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.demitido = !!data.demissao;
                    this.tituloJanela = `Demissão: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    Object.assign(this.form, data);
                    this.form.demissao = data.demissao ? data.demissao : _.cloneDeep(this.formDefault.demissao);
                    this.preload = false;
                    // this.form.alternativas = !data.alternativas ? data.alternativas : Object.assign(this.form.alternativas, this.alternativasDefault);
                })
                .catch(error => {
                    this.preload = false;
                });
        },
        demitir() {
            if (!this.form.demissao.cipa) {
                mostraErro("", "Você deve checar estabilidade: CIPA, Acidente Trabalho e Sindicato, Gestante, Aposentadoria (Itens CLT ou CCT)");
                return false;
            }

            $("#janelaAvaliar :input:visible").trigger("blur");
            if ($("#janelaAvaliar :input:visible.is-invalid").length) {
                mostraErro("", "Verifique os erros");
                return false;
            }
            this.preload = true;

            axios.post(`${URL_ADMIN}/posadmissao/demitir`, this.form)
                .then(response => {
                    let data = response.data;
                    this.cadastrando = false;
                    this.atualizado = true;
                    this.preload = false;
                    this.atualizar();
                })
                .catch(error => {
                    this.preload = false;
                });

        },
        formDesmobilizar(curriculo_id) {
            this.tituloJanela = `Desmobilizando ${curriculo_id}`;
            this.form.curriculo_id = curriculo_id;
            this.cadastrando = true;
            this.atualizado = false;
            this.preload = true;

            this.avaliacao = false;
            this.desmobilizacao = true;
            this.entrevista = false;

            this.form = _.cloneDeep(this.formDefault);
            this.form.alternativas = _.cloneDeep(this.alternativasDefault);

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.tituloJanela = `Desmobilizando: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    Object.assign(this.form, data);
                    this.form.avaliacao = data.avaliacao ? data.avaliacao : "";
                    if (data.alternativas) {

                    } else {
                        this.form.alternativas = _.cloneDeep(this.alternativasDefault);
                    }

                    this.preload = false;
                    // this.form.alternativas = !data.alternativas ? data.alternativas : Object.assign(this.form.alternativas, this.alternativasDefault);
                })
                .catch(error => {
                    this.preload = false;
                });
        },
        desmobilizar() {
            $("#janelaAvaliar :input:visible").trigger("blur");
            if ($("#janelaAvaliar :input:visible.is-invalid").length) {
                mostraErro("", "Verifique os erros");
                return false;
            }

            this.preload = true;
            axios.put(`${URL_ADMIN}/posadmissao/desmobilizar`, this.form)
                .then(response => {
                    let data = response.data;
                    this.cadastrando = false;
                    this.atualizado = true;
                    this.atualizar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });

        },
        formEntrevistar(curriculo_id) {
            this.tituloJanela = `Entrevista de desligamento ${curriculo_id}`;
            this.form.curriculo_id = curriculo_id;
            this.cadastrando = true;
            this.atualizado = false;
            this.preload = true;

            this.avaliacao = false;
            this.desmobilizacao = false;
            this.entrevista = true;

            // this.form = _.cloneDeep(this.formDefault)
            Object.assign(this.form, this.formDefault);

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.tituloJanela = `Entrevista de desligamento: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    Object.assign(this.form, data);

                    if (!this.form.feedback.entrevista_desligamento) {
                        this.form.entrevista_desligamento = _.cloneDeep(this.formDefault.entrevista_desligamento);
                        // Object.assign(this.form.entrevista_desligamento, this.formDefault.entrevista_desligamento);
                    } else {
                        this.form.entrevista_desligamento = this.form.feedback.entrevista_desligamento;
                    }
                    // this.form.entrevista_desligamento = !this.form.entrevista_desligamento ? Object.assign(this.form.entrevista_desligamento, this.formDefault.entrevista_desligamento) : Object.assign(this.form, data);
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });
        },
        entrevistar() {
            $("#janelaAvaliar :input:visible").trigger("blur");
            if ($("#janelaAvaliar :input:visible.is-invalid").length) {
                mostraErro("", "Verifique os erros");
                return false;
            }
            // this.form._method = 'PUT';
            this.preload = true;

            if (!this.form.entrevista_desligamento.id) {
                axios.post(`${URL_ADMIN}/posadmissao/entrevistar`, this.form).then(response => {
                    let data = response.data;
                    this.cadastrando = false;
                    this.atualizado = true;
                    this.atualizar();
                    this.preload = false;
                })
                    .catch(error => {
                        this.preload = false;
                    });
            } else {
                // this.form.entrevista_desligamento._method = 'PUT';
                axios.put(`${URL_ADMIN}/posadmissao/entrevistar/${this.form.entrevista_desligamento.id}`, this.form.entrevista_desligamento)
                    .then(response => {
                        let data = response.data;
                        this.cadastrando = false;
                        this.atualizado = true;
                        this.atualizar();
                        this.preload = false;
                    })
                    .catch(error => {
                        this.preload = false;
                    });
            }
        },
        formRetornarStatus(curriculo_id) {
            this.tituloJanela = `Remoção de demissão ${curriculo_id}`;
            this.form.curriculo_id = curriculo_id;
            this.cadastrando = false;
            this.atualizado = false;
            this.atualizado = false;
            this.preload = true;
            this.revertendo_status = true;

            axios.get(`${URL_ADMIN}/posadmissao/${curriculo_id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.demitido = !!data.demissao;
                    this.tituloJanela = `Remoção de demissão: ${data.feedback.curriculo.nome} - ${curriculo_id}`;
                    this.auditoria_form = {
                        empresa_id: AUTENTICADO.empresa_id,
                        usuario_id: AUTENTICADO.user_id,
                        feedback_id: data.feedback.id,
                        colaborador_id: data.feedback.curriculo.id,
                        tipo: "remover_demissao",
                        descricao: "",
                        dados: {
                            nome: data.feedback.curriculo.nome,
                            cpf: data.feedback.curriculo.cpf,
                            vaga: data.feedback.vaga_aberta.vaga.nome,
                            cargo: data.cargo,
                            termo: this.textoDefaultAuditoria,
                            funcao: data.funcao,
                            data_admissao: data.data_admissao,
                            data_demissao: data.demissao.data_desmobilizacao,
                            autenticado_nome: AUTENTICADO.nome,
                            motivo: "",
                            token: this.generateUuid(),
                        },
                    }
                    Object.assign(this.form, data);
                    this.form.demissao = data.demissao ? data.demissao : _.cloneDeep(this.formDefault.demissao);
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });
        },
        reverterDemissao() {
            if (this.auditoria_form.descricao.length === 0) {
                mostraErro("", "Informe o motivo da reversão da demissão");
                return false;
            }
            this.auditoria_form.dados.motivo = this.auditoria_form.descricao;
            this.preload = true;

            axios.put(`${URL_ADMIN}/posadmissao/remover-demissao`, this.auditoria_form)
                .then(response => {
                    mostraSucesso("", "Reversão de demissaão concluida");
                    this.atualizar();
                    this.$refs.janelaRetornarStatus.fecharModal();
                    this.revertendo_status = false;
                    this.preload = false;

                })
                .catch(error => {
                    this.preload = false;
                });
        },
        listaVagas() {
            this.preload = true;
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false;
                    this.vagas = data.vagas;
                })
                .fail((data) => {
                    this.preload = false;
                });
        },

        listaAreasGeral() {
            this.preload = true;
            axios.get(`${URL_PUBLICO}/lista-areas`)
                .then((response) => {
                    this.preload = false;
                    this.listaAreas = response.data.areas;
                }).catch((error) => {
                this.preload = false;
            });
        },
        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },
        gerarPdf(item) {
            let link = `${URL_ADMIN}/posadmissao/demitir/pdf/${item}`;
            open(link, "blank");
        },
        download(item) {
            var extensao = "";
            if (item === "demissao_com_justa_causa") {
                extensao = ".doc";
            } else {
                extensao = ".png";
            }
            open(`https://mybp-prod.s3.amazonaws.com/public/${item}${extensao}`, "blank");
        },

        carregou(dados) {
            this.lista = dados.items;
            this.listaMotivos = dados.motivos_rescisoes;
            this.listaAvisos = dados.tipos_rescisoes;
            this.listaClassificacoes = dados.classificacoes_rescisoes;
            this.formulario = dados.formulario;
            this.alternativasDefault = dados.form_limpo;
            this.selecionaTudo = this.tudoMarcado;
            this.form.alternativas = _.cloneDeep(this.alternativasDefault);
            this.controle.carregando = false;

            this.lista_ccs = dados.cc;
            if (!this.AUTENTICADO.temFilial) {
                this.controle.dados.campoCnpj = Object.keys(dados.cc.cnpjs)[0];
            }

            this.posadmissao_form_rh = dados.posadmissao_form_rh;
            this.posadmissao_form_adm = dados.posadmissao_form_adm;
            this.posadmissao_form_ssma = dados.posadmissao_form_ssma;
        }
        ,
        carregando() {
            this.controle.carregando = true;
        }
        ,
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
});
