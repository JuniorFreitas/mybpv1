import preload from '../../../components/preload';
import datepicker from '../../../components/DatePicker';
import {Loader} from "@googlemaps/js-api-loader"
import autoComplete from "../../../components/AutoComplete";
import escala from "../../../components/controle-ponto/Escala";
import formataNome from "../../../filters/formataNomeUser";


const app = new Vue({
    el: '#app',
    components: {
        preload,
        datepicker,
        autoComplete,
        escala
    },
    filters: {
        formataNome
    },
    data: {
        URL_ADMIN,
        GOOGLE_MAPS_KEY,
        EMPRESA_ID: null,
        preload: true,
        preloadGoogleMaps: true,
        formBusca: {
            intervalo: `${moment().startOf('month').format('DD/MM/YYYY')} até ${moment().endOf('month').format('DD/MM/YYYY')}`,
            preload: false,
            funcionario_id: null,
            funcionarioNome: '',
            funcionarioNomeAnterior: ''
        },
        listaPendentes: [],
        listaIncompletas: [],
        listaVerificada: [],

        listaPerimetros: [],
        listaEscalas: [],
        formPonto: {
            preload: true,
            titulo: 'Nova marcação',
            ocorrencia_id: null,
            verificado:false,
            ocorrencia_id_padrao: null,
            ocorrencias_jornadas: [],
            periodosDelete:[],
            jornada: null,
            justificativa: null,
            id: null,
            save: false,
        },
        formPontoDefault: null,
        porPagina:20,
        porPaginaPadrao:20,
        paginaAtualAntes:null,
        paginacaoRef:null,
        fezAjusteJornada:false,
        irParaProximaJornada:false,


        jornada_prevista: null,
        jornada_realizada: null,
        jornada_atual: null,


        agora: moment(),
        hoje: ''

    },
    mounted() {

        String.prototype.capitalize = function () {
            return this.charAt(0).toUpperCase() + this.substr(1);
        }
        this.formPontoDefault = _.cloneDeep(this.formPonto);
        this.porPagina=this.porPaginaPadrao;
        this.atualizarTudo();

    },
    computed: {
        ocorrenciaSelecionada(){
            if(this.formPonto.ocorrencia_id){
                return this.formPonto.ocorrencias_jornadas.find(oco=>oco.id===this.formPonto.ocorrencia_id);
            }
            return null;
        }
    },
    methods: {
        selecionaFuncionario(obj) {
            this.formBusca.funcionario_id = obj.id;
            this.formBusca.funcionarioNomeAnterior = obj.nome;
            this.formBusca.funcionarioNome = obj.nome
            this.atualizarTudo()
            /*
            this.form.autocomplete_label_cliente_modal = obj.label;
            this.form.autocomplete_label_cliente_modal_anterior = obj.label;
            //reseta Colaborador
            this.form.autocomplete_label_colaborador_anterior = '';
            this.form.autocomplete_label_colaborador = '';
            this.form.colaborador_id = '';
            setTimeout(() => {
                this.listaCentroCusto();
                this.form.centro_custo_id = '';
            }, 100);*/
        },
        resetaCampoFunccionario() {
            if (this.formBusca.funcionarioNomeAnterior !== this.formBusca.funcionarioNome) {
                this.formBusca.funcionarioNomeAnterior = '';
                this.formBusca.funcionarioNome = '';
                this.formBusca.funcionario_id = null;
                this.atualizarTudo();
            }
        },
        botaoResetCampos() {
            this.formBusca.funcionarioNomeAnterior = '';
            this.formBusca.funcionarioNome = '';
            this.formBusca.funcionario_id = null;
            this.atualizarTudo();
        },

        atualizarTudo() {
            setTimeout(()=>{
                this.atualizarPendentes();
                this.atualizarIncompletas();
                this.atualizarVerificadas();
                //this.paginacaoRef=null;
            },100)
        },
        //Aba pendentes ---------------------
        atualizarPendentes() {
            this.$refs.pag_pendentes.atual = 1;
            this.$refs.pag_pendentes.buscar();
        },
        carregouPendentes(dados) {
            this.listaPendentes = dados;
            this.formBusca.preload = false;

            if(this.paginacaoRef){
                //this.formPonto.preload=false;
                if(dados.length > 0){
                    this.verDetalhes(dados[0].id)
                }
            }
        },
        carregandoPendentes() {
            this.formBusca.preload = true;
        },
        //Aba incompletas ---------------------
        carregouIncompletas(dados) {
            this.listaIncompletas = dados;
            this.formBusca.preload = false;
            if(this.paginacaoRef){
                //this.formPonto.preload=false;
                if(dados.length > 0){
                    this.verDetalhes(dados[0].id)
                }
            }
        },
        carregandoIncompletas() {
            this.formBusca.preload = true;
        },
        atualizarIncompletas() {
            this.$refs.pag_incompletas.atual = 1;
            this.$refs.pag_incompletas.buscar();
        },
        //Aba verificadas ---------------------
        carregouVerificadas(dados) {
            this.listaVerificada = dados;
            this.formBusca.preload = false;
            if(this.paginacaoRef){
                //this.formPonto.preload=false;
                if(dados.length > 0){
                    this.verDetalhes(dados[0].id)
                }
            }
        },
        carregandoVerificadas() {
            this.formBusca.preload = true;
        },
        atualizarVerificadas() {
            this.$refs.pag_verificadas.atual = 1;
            this.$refs.pag_verificadas.buscar();
        },


        verDetalhes(id,ref=null,) {
            this.formPonto = _.cloneDeep(this.formPontoDefault);
            this.formPonto.preload = true;
            axios.get(`${URL_ADMIN}/controle-ponto/ajustar-jornadas/${id}/editar`)
                .then(response => {

                    Object.assign(this.formPonto, response.data.ponto);
                    Object.assign(this.formPonto.ocorrencias_jornadas, response.data.ocorrencias_jornadas);
                    this.formPonto.ocorrencia_id_padrao = response.data.ocorrencia_id_padrao
                    this.jornada_atual = response.data.jornada_atual;
                    // tratamento
                    this.jornada_prevista = _.cloneDeep(this.formPonto.jornada.escala);

                    this.formPonto.jornada.escala.periodosDelete = [];
                    this.formPonto.jornada.escala.jornadas = this.formPonto.jornada.escala.jornadas.filter(j => j.id === this.jornada_atual.id);
                    this.formPonto.jornada.escala.jornadas[0].periodos = [];
                    let listaOrdenadaPeriodos = _.orderBy(this.formPonto.periodos, ['horaEntrada'], ['asc'])
                    listaOrdenadaPeriodos.forEach(p => {
                        this.formPonto.jornada.escala.jornadas[0].periodos.push({
                            id: p.id,
                            jornada_id: this.jornada_atual.id,
                            entrada: p.horaEntrada,
                            autenticacao_entrada: p.autenticacao_entrada,
                            saida: p.horaSaida ? p.horaSaida : '',
                            horas_trabalhadas: '',
                            horas_descanso: '',
                        });
                    });

                    //ajustando a paginacao
                    if(ref){

                        this.paginacaoRef= this.$refs[ref];
                        this.paginaAtualAntes = this.paginacaoRef.atual;
                        if(ref==='pag_pendentes'){
                            let indexOf = this.listaPendentes.findIndex(item=>item.id ===id);

                            // saber em que paginas está
                            let lista = _.fill(Array(this.paginacaoRef.total), false)
                            lista = _.chunk(lista,this.porPagina);
                            let pagina = lista[this.paginacaoRef.atual-1];
                            pagina[indexOf] = this.listaPendentes.find(item=>item.id ===id);
                            let nova = [];
                            lista.forEach(pagina=>{
                                nova = _.concat(nova,pagina);
                            });
                            this.porPagina=1;
                            this.paginacaoRef.atual= nova.findIndex(item=>item.id ===id)+1
                        }
                        if(ref==='pag_incompletas'){
                            let indexOf = this.listaIncompletas.findIndex(item=>item.id ===id);

                            // saber em que paginas está
                            let lista = _.fill(Array(this.paginacaoRef.total), false)
                            lista = _.chunk(lista,this.porPagina);
                            let pagina = lista[this.paginacaoRef.atual-1];
                            pagina[indexOf] = this.listaIncompletas.find(item=>item.id ===id);
                            let nova = [];
                            lista.forEach(pagina=>{
                                nova = _.concat(nova,pagina);
                            });
                            this.porPagina=1;
                            this.paginacaoRef.atual= nova.findIndex(item=>item.id ===id)+1
                        }
                        if(ref==='pag_verificadas'){
                            let indexOf = this.listaVerificada.findIndex(item=>item.id ===id);

                            // saber em que paginas está
                            let lista = _.fill(Array(this.paginacaoRef.total), false)
                            lista = _.chunk(lista,this.porPagina);
                            let pagina = lista[this.paginacaoRef.atual-1];
                            pagina[indexOf] = this.listaVerificada.find(item=>item.id ===id);
                            let nova = [];
                            lista.forEach(pagina=>{
                                nova = _.concat(nova,pagina);
                            });
                            this.porPagina=1;
                            this.paginacaoRef.atual= nova.findIndex(item=>item.id ===id)+1
                        }



                    }
                    this.formPonto.preload = false;


                }).catch(error => {
                this.formPonto.preload = false;
            });
        },
        jornadaAnterior(){
            this.paginacaoRef.voltar();
            this.formPonto.preload=true;
        },
        proximaJornada(){
            this.paginacaoRef.avancar();
            this.formPonto.preload=true;
        },
        atualizarComponentePaginacao(){

            this.porPagina=this.porPaginaPadrao;
            this.paginacaoRef.atual = this.paginaAtualAntes;
            if(this.fezAjusteJornada){
                this.atualizarTudo();
                this.paginacaoRef=null;
            }else{
                setTimeout(()=>{
                    this.paginacaoRef.buscar();
                    this.paginacaoRef=null;
                },100)
            }
            this.fezAjusteJornada=false;
            this.irParaProximaJornada=false;

        },
        salvar() {

            //tratamento
            //console.log(this.formPonto.jornada.escala.jornadas[0].periodos, this.formPonto.periodos);
            $('#janelaFormDetalhes :input:visible:enabled').trigger('blur');
            if ($('#janelaFormDetalhes :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            let diaEntrada = moment(this.formPonto.periodos[0].entrada,'DD/MM/YYYY [às] HH:mm:ss');


            this.formPonto.periodos = this.formPonto.jornada.escala.jornadas[0].periodos.map(p => {
                let periodo = {};
                if (p.id) {
                    periodo.id=p.id;
                    periodo.entradaCompleto = `${diaEntrada.format('DD/MM/YYYY')} às ${p.entrada}:00`;
                    periodo.entrada = `${p.entrada}`;
                    periodo.saidaCompleto = `${diaEntrada.format('DD/MM/YYYY')} às ${p.saida}:00`;
                    periodo.saida = `${p.saida}`;
                    periodo.autenticacao_entrada = p.autenticacao_entrada;
                    //peirodo.bloqueado
                }else{
                    periodo.id=null;
                    periodo.entradaCompleto = `${diaEntrada.format('DD/MM/YYYY')} às ${p.entrada}:00`;
                    periodo.entrada = `${p.entrada}`;
                    periodo.saidaCompleto = `${diaEntrada.format('DD/MM/YYYY')} às ${p.saida}:00`;
                    periodo.saida = `${p.saida}`;
                    periodo.autenticacao_entrada = null;
                }
                return periodo;
            });
            this.formPonto.periodosDelete = this.formPonto.jornada.escala.periodosDelete;
            this.formPonto.preload = true;

            axios.put(`${URL_ADMIN}/controle-ponto/ajustar-jornadas/${this.formPonto.id}`, this.formPonto)
                .then(response => {
                    this.formPonto.preload = false;
                    if(this.irParaProximaJornada && this.paginacaoRef.atual < this.paginacaoRef.total){
                        this.proximaJornada();
                    }else{
                        this.formPonto.save = true;
                        this.fezAjusteJornada=true;
                    }


                }).catch(error => {
                this.formPonto.preload = false;
            });
        }


    }
});
