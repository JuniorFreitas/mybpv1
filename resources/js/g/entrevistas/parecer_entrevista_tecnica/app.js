import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import DadosPessoais from '../../../components/entrevistas/DadosPessoaisTexto';
import Editor from '@tinymce/tinymce-vue';
import configTinyMCE from '../../../components/configEntrevistaTinyMCE';

const app = new Vue({
    el: '#app',
    components: {
        endereco,
        datepicker,
        Editor,
        DadosPessoais
    },
    data: {
        config: configTinyMCE,
        tituloJanela: 'Parecer Entrevista Técnica',
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

        URL_ADMIN,
        selecionados: [],
        selecionaTudo: false,

        colunasTabela: {
            pcd: false,
            cliente: false,
            parecer_rh: true,
            parecer_rota: false,
            teste_pratico_nota: false
        },

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

            parecer_tecnica: {
                feedback_id: '',
                tipo_entrevista: 'Fixo',
                tipo_contratacao: 'Operacional',
                curriculo_id: '',
                tempo_funcao: '',
                trabalhou_alumar: '',
                indicado: '',
                indicado_por: '',
                rota: '',
                ssma: '',
                ssma_ex: '',
                roupa_pvc: '',
                roupa_pvc_ex: '',
                roupa_pvc_dificuldade: '',
                turno: '',
                trabalhou_mecanico_manutencao: '',
                trabalhou_mecanico_manutencao_ex: '',
                trabalhou_raquete_produto_quimico: '',
                trabalhou_raquete_produto_quimico_ex: '',
                tipos_de_talha: '',
                fechamento_flange: '',
                fechamento_flange_ex: '',
                milimetros_polegada: '',
                manuseio_macarico: '',
                manuseio_macarico_ex: '',
                trocou_valvulas: '',
                trocou_valvulas_ex: '',
                ferramentas_elevacao_carga: '',
                opera_plat_movel: '',
                opera_plat_movel_ex: '',
                opera_plat_ponte: '',
                opera_plat_onte_ex: '',
                experiencia_cargas_rigger: '',
                experiencia_cargas_rigger_ex: '',
                trabalhou_overhaul: '',
                trabalhou_overhaul_ex: '',
                abertura_tubo_seis_polegada: '',
                vareta_seis_polegada: '',
                filete_acabemento: '',
                texto_livre: '',
                observacao: '',
                resultado_final: '',
                nota: '',
                quem_entrevistou: '',
            },

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
                campoLido: '',
                campoFiltro: '',
                campoPcd: '',
                campoRota: '',
                campoCliente: '',
                campoUf: '',

                filtroPeriodo: false,
                periodo: '',
            },
        },
    },
    computed: {
        comTecnica() {
            return this.lista.filter(item => {
                return item.parecer_tecnica;
            });
        },
        tudoMarcado() {
            let totalItens = this.comTecnica.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }

            this.comTecnica.forEach(item => {
                let id = item.curriculo_id;
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
        this.formDefault = _.cloneDeep(this.form) //copia
        this.usuarioAutenticado();
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
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comTecnica.map(item => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                });
            } else {
                this.comTecnica.map(item => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                });
            }
        },

        formEntrevistar(id) {

            this.cadastrado = false;
            this.atualizado = false;
            this.cadastrando = false;
            this.visualizar = false;
            this.editando = false;

            this.preload = true;
            this.form = _.cloneDeep(this.formDefault);
            this.form.id = id;

            formReset();
            axios.get(`${URL_ADMIN}/entrevistas/parecer-entrevista-tecnica/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);

                    //Se não tiver parecer_tecnica
                    this.form.parecer_tecnica = data.parecer_tecnica ? data.parecer_tecnica : _.cloneDeep(this.formDefault.parecer_tecnica);
                    this.form.parecer_tecnica.rota_tipo = data.parecer_rh.tipo_entrevista;

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`;
                    this.cadastrando = true;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        cadastrar() {

            $('#janelaParecerEntrevista :input:visible').trigger('blur');
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            this.form.parecer_tecnica.feedback_id = this.form.id;
            this.form.parecer_tecnica.curriculo_id = this.form.curriculo_id;

            axios.post(`${URL_ADMIN}/entrevistas/parecer-entrevista-tecnica/`, this.form.parecer_tecnica)
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
            $('#janelaParecerEntrevista :input:visible').trigger('blur');
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/entrevistas/parecer-entrevista-tecnica/${this.form.parecer_tecnica.id}`, this.form.parecer_tecnica)
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
                    this.colunasTabela.cliente = this.cliente_id === 0;
                    this.controle.dados.campoCliente = this.cliente_id !== 1 ? this.cliente_id : this.controle.dados.campoCliente;
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
