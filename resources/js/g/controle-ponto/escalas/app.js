import preload from '../../../components/preload';
import datepicker from '../../../components/DatePicker';
import escala from '../../../components/controle-ponto/Escala';

const app = new Vue({
    el: '#app',
    components: {
        preload,
        datepicker,
        escala
    },
    data: {
        URL_ADMIN,
        EMPRESA_ID:null,
        preload: true,
        preloadGoogleMaps:true,

        escalas_insert:false,
        escalas_update:false,
        escalas_delete:false,
        escalas_funcionarios:false,
        ocorrencias:[],
        ocorrencia_id_padrao:0,

        formEscala:{
            editando:false,
            titulo:'Adicionar escala',
            id:null,
            descricao:'',
            inicio:moment().format('DD/MM/YYYY'),
            jornadas:[],
            jornadasDelete:[],
            periodosDelete:[],
            preload:false,
            save:false,
        },
        formEscalaDefault:null,

        paginacaoEscalas: {
            carregando: false,
            dados: {
                campoBusca:'',
            },
        },
        formPerimetro:{
            editando:false,

            titulo:'Adicionar perímetro',
            id:null,
            descricao:'',

            preload:false,
            save:false,
        },
        formPerimetroDefault:null,
        listaEscalas:[],
        listaFuncionarios:[],
        paginacaoFuncionarios: {
            carregando: false,
            dados: {
                campoBusca:'',
            },
        },
        todosFuncionariosSelecionados:false,
        formEscalaFuncionarios:{
            funcionariosSelecionados:[],
            escala_id:null,
            preload:false,
            update:false,
        },

        map:null,
        marker:null,
        //latLong,
        atual : null,
        destino : null,
        directionService : null,
        directionDisplay : null,
        perimetro:null

    },
    mounted() {
        this.formEscalaDefault = _.cloneDeep(this.formEscala);
        this.atualizarListaFuncionarios();
        this.atualizarListaEscalas();

        axios.get(`${URL_ADMIN}/usuario/autenticado`,)
            .then(response => {
                this.preload = false;
                this.EMPRESA_ID = response.data.empresa_id;
                this.getPermissoes();

            }).catch(error => {
            this.preload = false;
        });


    },
    computed: {

    },
    methods: {

        getPermissoes(){
            this.preload = true;
            axios.get(`${URL_ADMIN}/controle-ponto/escalas/getPermissoes/`,)
                .then(response => {
                    this.preload = false;
                    this.escalas_insert = response.data.escalas_insert;
                    this.escalas_update = response.data.escalas_update;
                    this.escalas_delete = response.data.escalas_delete;
                    this.escalas_funcionarios = response.data.escalas_funcionarios;
                    this.ocorrencias = response.data.ocorrencias_jornadas;
                    this.ocorrencia_id_padrao = response.data.ocorrencia_id_padrao;

                }).catch(error => {
                this.preload = false;
            });
        },

        //Escalas ---------------------------------------
        carregandoEscalas: function () {
            //this.formEscalaFuncionarios.preload = true;
            this.paginacaoEscalas.carregando = true;
        },
        carregouEscalas: function (dados) {
            this.listaEscalas = dados;
            this.paginacaoEscalas.carregando = false;
            //this.formEscalaFuncionarios.preload = false;
        },
        atualizarListaEscalas(){
            this.$refs.paginacaoEscalas.atual = 1;
            this.$refs.paginacaoEscalas.buscar();
        },

        formNovaEscala() {
            this.formEscala = _.cloneDeep(this.formEscalaDefault);
            this.formEscala.jornadas.push({
                id:null,
                escala_id:null,
                inicio: moment().format('DD/MM/YYYY'),
                periodos:[
                    {
                        id:null,
                        jornada_id:null,
                        entrada:'',
                        saida:'',
                    },
                    {
                        id:null,
                        jornada_id:null,
                        entrada:'',
                        saida:'',
                    }
                ],
                ocorrencia_id:this.ocorrencia_id_padrao,
                ocorrencias:this.ocorrencias,
                repetir:1,
            })
        },
        formEditarEscala(escala) {
            this.formEscala = _.cloneDeep(this.formEscalaDefault);
            this.formEscala.editando=true;
            this.formEscala.titulo='Editar escala';
            this.formEscala.preload=true;

            axios.get(`${URL_ADMIN}/controle-ponto/escalas/${escala.id}/editar`)
                .then(response => {
                    this.formEscala.preload = false;
                    Object.assign(this.formEscala,response.data);
                }).catch(error => {
                this.formEscala.preload = false;
            });


        },
        salvarEscala(){
            $('#janelaFormEscalas :input:visible:enabled').trigger('blur');
            if ($('#janelaFormEscalas :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.formEscala.preload = true;
            if(this.formEscala.editando){
                axios.put(`${URL_ADMIN}/controle-ponto/escalas/${this.formEscala.id}`, this.formEscala)
                    .then(response => {
                        this.formEscala.preload = false;
                        this.formEscala.save = true;
                        this.atualizarListaEscalas();
                        this.atualizarListaFuncionarios();

                    }).catch(error => {
                    this.formEscala.preload = false;
                    this.atualizarListaEscalas();
                    this.atualizarListaFuncionarios();
                });
            }else{
                axios.post(`${URL_ADMIN}/controle-ponto/escalas`, this.formEscala)
                    .then(response => {
                        this.formEscala.preload = false;
                        this.formEscala.save = true;
                        this.atualizarListaEscalas();
                        //this.atualizarListaFuncionarios();

                    }).catch(error => {
                    this.formEscala.preload = false;
                    this.atualizarListaEscalas();
                    this.atualizarListaFuncionarios();
                });
            }

        },
        apagarEscala: function () {

            this.formEscala.preload = true;

            axios.delete(`${URL_ADMIN}/controle-ponto/escalas/${this.formEscala.id}`, null)
                .then((data) => {
                    this.formEscala.preload = false;
                    this.formEscala.save = true;
                    this.atualizarListaEscalas();
                    this.atualizarListaFuncionarios();
                })
                .catch((data) => {
                    this.formEscala.preload = false;
                    this.atualizarListaEscalas();
                    this.atualizarListaFuncionarios();
                });
        },
        //-----Escalas a funcionarios------------

        carregando: function () {
            this.paginacaoFuncionarios.carregando = true;
        },
        carregou: function (dados) {
            this.listaFuncionarios = dados;
            this.paginacaoFuncionarios.carregando = false;
            this.checarMarcarTodosFuncionarios();
        },
        atualizarListaFuncionarios() {
            this.$refs.paginacaoFuncionarios.atual = 1;
            this.$refs.paginacaoFuncionarios.buscar();
        },

        selecionarTodosFuncionarios(){
            if(this.todosFuncionariosSelecionados){
                this.listaFuncionarios.forEach((user)=>{
                    if(!this.formEscalaFuncionarios.funcionariosSelecionados.includes(user.id)){
                        this.formEscalaFuncionarios.funcionariosSelecionados.push(user.id);
                    }
                });
            }else{
                this.listaFuncionarios.forEach((user)=>{
                    let index = this.formEscalaFuncionarios.funcionariosSelecionados.indexOf(user.id);
                    if(index !== -1){
                        this.formEscalaFuncionarios.funcionariosSelecionados.splice(index,1);
                    }
                });
            }
        },
        selecionarFuncionario(user){
            if(!this.formEscalaFuncionarios.funcionariosSelecionados.includes(user.id)){
                this.formEscalaFuncionarios.funcionariosSelecionados.push(user.id);
            }else{
                let index = this.formEscalaFuncionarios.funcionariosSelecionados.indexOf(user.id);
                if(index !== -1){
                    this.formEscalaFuncionarios.funcionariosSelecionados.splice(index,1);
                }
            }
            this.checarMarcarTodosFuncionarios();
        },
        checarMarcarTodosFuncionarios(){
            let quantidade = this.listaFuncionarios.length;
            let marcados = this.listaFuncionarios.filter((funcionario=>this.formEscalaFuncionarios.funcionariosSelecionados.includes(funcionario.id))).length
            this.todosFuncionariosSelecionados = quantidade===marcados;
        },

        formAssociarEscala(){
            this.formEscalaFuncionarios.escala_id=null;
            this.formEscalaFuncionarios.update=false;
        },
        assosicarEscala(){
            this.formEscalaFuncionarios.preload = true;
            axios.put(`${URL_ADMIN}/controle-ponto/escalas/assosicarEscalas`,this.formEscalaFuncionarios)
                .then(response => {
                    this.formEscalaFuncionarios.preload = false;
                    this.formEscalaFuncionarios.update = true;
                    this.atualizarListaFuncionarios();
                    this.checarMarcarTodosFuncionarios();
                }).catch(error => {
                this.formEscalaFuncionarios.preload = false;
                this.atualizarListaFuncionarios();
            });
        },
        resetFuncionariosSelecionados(){
            if(this.formEscalaFuncionarios.update){
                this.formEscalaFuncionarios.funcionariosSelecionados=[];
                this.todosFuncionariosSelecionados=false;
            }
        }


    }
});
