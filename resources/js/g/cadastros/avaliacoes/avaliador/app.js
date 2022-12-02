import preload from '../../../../components/preload';
import { Loader } from "@googlemaps/js-api-loader"

const app = new Vue({
    el: '#app',
    components: {
        preload
    },
    data: {
        URL_ADMIN,
        GOOGLE_MAPS_KEY,
        EMPRESA_ID:null,
        preload: true,
        preloadGoogleMaps:true,

        perimetros_insert:false,
        perimetros_update:false,
        perimetros_delete:false,
        perimetros_funcionarios:false,
        config_empresa:false,

        preloadConfig:false,
        formConfig: {
            tipo_frequencia: '',
            limite_tolerancia: '',
            tempo_limite_falta: '',
            tempo_limite_saida: '',
            dia_nova_frequencia: '',
        },

        paginacaoPerimetros: {
            carregando: false,
            dados: {
                campoBusca:'',
            },
        },
        form:{
            editando:false,
            titulo:'Adicionar perímetro',
            id:null,
            descricao:'',
            lat:-2.5919,
            long:-44.2322,
            perimetro:50,
            obrigatorio:true,
            preload:false,
            save:false,
        },
        formPerimetroDefault:null,
        listaPerimetros:[],
        listaPerimetrosDefault:[],
        listaFuncionarios:[],
        paginacaoFuncionarios: {
            carregando: false,
            dados: {
                campoBusca:'',
            },
        },
        todosFuncionariosSelecionados:false,

        formPerimetroFuncionarios:{
            funcionariosSelecionados:[],
            perimetrosSelecionados:[],
            perimetro_id:0,
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
        this.formPerimetroDefault = _.cloneDeep(this.formPerimetro);
        this.atualizarListaFuncionarios();
        this.atualizarListaPeriemetros();
        const loader = new Loader({
            apiKey: this.GOOGLE_MAPS_KEY,
            version: "weekly",
            libraries:['places','geometry']
        });

        loader.load().then(()=>{
            this.preloadGoogleMaps=false;
        });

        axios.get(`${URL_ADMIN}/usuario/autenticado`,)
            .then(response => {
                this.preload = false;
                Object.assign(this.formConfig, response.data.config_empresa);
                this.EMPRESA_ID = response.data.empresa_id;
                this.getPermissoes();

            }).catch(error => {
            this.preload = false;
        });


    },
    computed: {

    },
    methods: {
        removerLIColaborador(index) {
            if (this.editando && !this.form.usuarios[index].novo) {
                this.form.usuariosDelete.push(this.form.usuarios[index].id);
            }
            this.form.usuarios.splice(index, 1);
        },
        selecionaColaborador(obj) {
            const usuario = {};
            usuario.novo = true;
            usuario.id = obj.id;
            usuario.nome = obj.nome;

            let atual = this.form.usuarios.findIndex(val => val.id === usuario.id);

            if (atual < 0) {//Se não existir ainda no array
                this.form.usuarios.push(usuario);
            } else {
                mostraErro("", `O colaborador(a) ${usuario.nome} já está na lista.`);
                this.form.autocomplete_label_colaborador = "";
                return false;
            }
            this.form.autocomplete_label_colaborador = "";
        },

        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = "";
                this.form.autocomplete_label_colaborador = "";
                this.form.colaborador_id = "";

                setTimeout(() => {
                    if (this.form.colaborador_id === "") {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger("blur");
                        mostraErro("Erro", "O Campo Colaborador não pode ficar vazio");
                    }
                }, 100);
            }
        },

        //Configurações ------------------------------------
        getPermissoes(){
            this.preload = true;
            axios.get(`${URL_ADMIN}/controle-ponto/configuracoes/getPermissoes/`,)
                .then(response => {
                    this.preload = false;
                    this.perimetros_insert = response.data.perimetros_insert;
                    this.perimetros_update = response.data.perimetros_update;
                    this.perimetros_delete = response.data.perimetros_delete;
                    this.perimetros_funcionarios = response.data.perimetros_funcionarios;
                    this.config_empresa = response.data.config_empresa;

                }).catch(error => {
                this.preload = false;
            });
        },
        salvarConfiguracoes() {

            $('#config_frequencia :input:visible:enabled').trigger('blur');
            if ($('#config_frequencia :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }


            this.preloadConfig = true;
            axios.put(`${URL_ADMIN}/controle-ponto/configuracoes/${this.EMPRESA_ID}`, this.formConfig)
                .then(response => {
                    this.preloadConfig = false;
                    mostraSucesso('', 'Configuração salva');
                }).catch(error => {
                this.preloadConfig = false;
            });
        },

        //Perimetros ---------------------------------------
        carregandoPerimetros () {
            this.formPerimetroFuncionarios.preload = true;
            this.paginacaoPerimetros.carregando = true;
        },
        carregouPerimetros(dados) {
            this.listaPerimetros = dados;
            this.listaPerimetrosDefault = _.cloneDeep(dados);
            this.paginacaoPerimetros.carregando = false;
            this.formPerimetroFuncionarios.preload = false;
        },
        atualizarListaPeriemetros(){
            this.$refs.paginacaoPerimetros.atual = 1;
            this.$refs.paginacaoPerimetros.buscar();
        },
        initMap(){


            // AutoCompletar
            let autocomplete = new google.maps.places.Autocomplete($(".enderecoGoogle")[0], {});
            google.maps.event.addListener(autocomplete, 'place_changed', () => {

                let place = autocomplete.getPlace();
                let latlng = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
                this.perimetro.setCenter(latlng);
                this.map.setCenter(latlng);
                this.map.setZoom(19);
                //$('#teste').html("LAT:"++ "LONG: "+)
                //alert(place.geometry.location);
                //console.log(place.geometry);
                //this.marcarMapa(latlng);
            });

            //this.directionService = new google.maps.DirectionsService();
            //this.directionDisplay = new google.maps.DirectionsRenderer();
            //this.destino = new google.maps.LatLng(-2.5139499605005433, -44.288847760913214);

            let latLong = null;
            this.map = new google.maps.Map(document.getElementById("mapaPrimetro"), {
                center: {lat: this.formPerimetro.lat, lng: this.formPerimetro.long},
                zoom: 8,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            this.perimetro = new google.maps.Circle({
                strokeColor:'#ff0000',
                strokeWeigth:2,
                strokeOpacity:1,
                fillColor:'#ff0000',
                fillOpacity:.4,
                center: {lat:this.formPerimetro.lat,lng:this.formPerimetro.long},
                radius:this.formPerimetro.perimetro,
                map:this.map,
                editable:true,
                draggable: true
            });

            this.perimetro.addListener('bounds_changed', (event) => {
                //console.log(event,this.perimetro);
                this.formPerimetro.perimetro = this.perimetro.radius;
                /*let distancia = google.maps.geometry.spherical.computeDistanceBetween(this.marker.getPosition(), this.perimetro.getCenter());
                if((distancia - this.perimetro.radius) < 0){
                    this.perimetro.setOptions({
                        fillColor:'#3f9827',
                        strokeColor:'#3f9827',
                    })
                }else{
                    this.perimetro.setOptions({
                        fillColor:'#ff0000',
                        strokeColor:'#ff0000',
                    })
                }*/
            });
            this.perimetro.addListener('dragend', (event) => {
                //this.formPerimetro.perimetro = this.perimetro.radius;
                this.formPerimetro.lat = event.latLng.lat();
                this.formPerimetro.long = event.latLng.lng();

                /*let distancia = google.maps.geometry.spherical.computeDistanceBetween(this.marker.getPosition(), this.perimetro.getCenter());
                if((distancia - this.perimetro.radius) < 0){
                    this.perimetro.setOptions({
                        fillColor:'#3f9827',
                        strokeColor:'#3f9827',
                    })
                }else{
                    this.perimetro.setOptions({
                        fillColor:'#ff0000',
                        strokeColor:'#ff0000',
                    })
                }*/

            });

            //this.directionDisplay.setMap(this.map);
            //this.directionDisplay.setPanel(document.getElementById('directionsPanel'));

            /*google.maps.event.addListener(this.map, 'click', (event) => {
                this.marcarMapa(event.latLng);
            });*/
            //buscar a localizacao
            if(!this.formPerimetro.editando){
                navigator.geolocation.getCurrentPosition((dados) => {
                    latLong = new google.maps.LatLng(dados.coords.latitude, dados.coords.longitude);
                    this.atual = latLong

                    this.formPerimetro.lat = this.atual.lat();
                    this.formPerimetro.long = this.atual.lng();

                    this.perimetro.setCenter(latLong);

                    this.map.setCenter(this.atual);
                    this.map.setZoom(19);

                    /*this.marker = new google.maps.Marker({
                        map: this.map,
                        position: latLong,
                        title: "Você está aqui",
                        //icon: `${URL_SITE}/imagens/map_icon.png`,
                        flat: false,
                        draggable: true
                    });*/
                    /* this.marker.addListener('drag', (event) => {
                         this.marcarMapa(event.latLng);
                     });*/


                    //this.marcarMapa(this.atual);

                    //this.calcularRota();
                });
            }else{
                this.map.setCenter(new google.maps.LatLng(this.formPerimetro.lat, this.formPerimetro.long));
                this.map.setZoom(19);
            }

        },
        initMapTime(){
            setTimeout(()=>{
                this.initMap();
            },500);
        },
        /*calcularRota() {
            const request = {
                origin: this.atual,
                destination: this.destino,
                //travelMode: 'DRIVING',
                travelMode: 'WALKING',
                unitSystem: google.maps.UnitSystem.METRIC
            }
            this.directionService.route(request, (result, status) => {

                if (status === 'OK') {
                    this.directionDisplay.setDirections(result);
                    console.log(result);

                }

            });

        },*/
        /*marcarMapa: function (latLng) {
            //this.marker.setMap(null); //limpar

            /!* this.marker = new google.maps.Marker({
                 position: latLng,
                 map: this.map,
                 draggable: true,
                 //icon: `${URL_SITE}/imagens/map_icon.png`
             });*!/
            //this.map.setCenter(latLng);
            //this.map.setZoom(19);
            this.marker.setPosition(latLng);
            //this.perimetro.setCenter(latLng);

            this.formPerimetro.lat = this.marker.position.lat();
            this.formPerimetro.long = this.marker.position.lng();

        },*/
        formNovoPerimetro() {
            this.formPerimetro = _.cloneDeep(this.formPerimetroDefault);
            if (!this.preloadGoogleMaps) {
                this.initMap();
            }
        },
        formEditarPerimetro(perimetro) {
            this.formPerimetro = _.cloneDeep(this.formPerimetroDefault);
            this.formPerimetro.editando=true;
            this.formPerimetro.titulo='Editar perimetro';
            this.formPerimetro.preload=true;

            axios.get(`${URL_ADMIN}/controle-ponto/perimetros/${perimetro.id}/editar`)
                .then(response => {
                    this.formPerimetro.preload = false;
                    Object.assign(this.formPerimetro,response.data);
                    if (!this.preloadGoogleMaps) {
                        this.initMap();
                    }
                }).catch(error => {
                this.formPerimetro.preload = false;
            });


        },
        salvarPerimetro(){
            $('#janelaFormPerimetro :input:visible:enabled').trigger('blur');
            if ($('#janelaFormPerimetro :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.formPerimetro.preload = true;
            if(this.formPerimetro.editando){
                axios.put(`${URL_ADMIN}/controle-ponto/perimetros/${this.formPerimetro.id}`, this.formPerimetro)
                    .then(response => {
                        this.formPerimetro.preload = false;
                        this.formPerimetro.save = true;
                        this.atualizarListaPeriemetros();
                        this.atualizarListaFuncionarios();

                    }).catch(error => {
                    this.formPerimetro.preload = false;
                    this.atualizarListaPeriemetros();
                    this.atualizarListaFuncionarios();
                });
            }else{
                axios.post(`${URL_ADMIN}/controle-ponto/perimetros`, this.formPerimetro)
                    .then(response => {
                        this.formPerimetro.preload = false;
                        this.formPerimetro.save = true;
                        this.atualizarListaPeriemetros();
                        this.atualizarListaFuncionarios();

                    }).catch(error => {
                    this.formPerimetro.preload = false;
                    this.atualizarListaPeriemetros();
                    this.atualizarListaFuncionarios();
                });
            }

        },
        formApagarPerimetro(id){
            this.formPerimetro.id = id;
            this.formPerimetro.save=false;
        },
        apagarPerimetro: function () {

            this.formPerimetro.preload = true;

            axios.delete(`${URL_ADMIN}/controle-ponto/perimetros/${this.formPerimetro.id}`, null)
                .then((data) => {
                    this.formPerimetro.preload = false;
                    this.formPerimetro.save = true;
                    this.atualizarListaPeriemetros();
                    this.atualizarListaFuncionarios();
                })
                .catch((data) => {
                    this.formPerimetro.preload = false;
                    this.atualizarListaPeriemetros();
                    this.atualizarListaFuncionarios();
                });
        },
        //-----Perimetros a funcionarios------------

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
                    if(!this.formPerimetroFuncionarios.funcionariosSelecionados.includes(user.id)){
                        this.formPerimetroFuncionarios.funcionariosSelecionados.push(user.id);
                    }
                });
            }else{
                this.listaFuncionarios.forEach((user)=>{
                    let index = this.formPerimetroFuncionarios.funcionariosSelecionados.indexOf(user.id);
                    if(index !== -1){
                        this.formPerimetroFuncionarios.funcionariosSelecionados.splice(index,1);
                    }
                });
            }
        },
        selecionarFuncionario(user){
            if(!this.formPerimetroFuncionarios.funcionariosSelecionados.includes(user.id)){
                this.formPerimetroFuncionarios.funcionariosSelecionados.push(user.id);
            }else{
                let index = this.formPerimetroFuncionarios.funcionariosSelecionados.indexOf(user.id);
                if(index !== -1){
                    this.formPerimetroFuncionarios.funcionariosSelecionados.splice(index,1);
                }
            }
            this.checarMarcarTodosFuncionarios();
        },

        selecionarPerimetro(perimetro){
            if(!this.formPerimetroFuncionarios.perimetrosSelecionados.includes(perimetro.id)){
                this.formPerimetroFuncionarios.perimetrosSelecionados.push(perimetro.id);
            }else{
                let index = this.formPerimetroFuncionarios.perimetrosSelecionados.indexOf(perimetro.id);
                if(index !== -1){
                    this.formPerimetroFuncionarios.perimetrosSelecionados.splice(index,1);
                }
            }
            this.checarMarcarTodosFuncionarios();
            this.formPerimetroFuncionarios.perimetrosSelecionados.length === 0 ? this.formPerimetroFuncionarios.perimetro_id = 0 : this.formPerimetroFuncionarios.perimetro_id = null;
        },

        checarMarcarTodosFuncionarios(){
            let quantidade = this.listaFuncionarios.length;
            let marcados = this.listaFuncionarios.filter((funcionario=>this.formPerimetroFuncionarios.funcionariosSelecionados.includes(funcionario.id))).length
            this.todosFuncionariosSelecionados = quantidade===marcados;
        },

        formAssociarPerimetro(){
            this.listaPerimetros = _.cloneDeep(this.listaPerimetrosDefault);
            this.formPerimetroFuncionarios.perimetro_id=0;
            this.formPerimetroFuncionarios.perimetrosSelecionados=[];

            if(this.formPerimetroFuncionarios.funcionariosSelecionados.length === 1){
                let funcionarioId = this.formPerimetroFuncionarios.funcionariosSelecionados[0];
                let perimetros = _.filter(this.listaFuncionarios, {'id':funcionarioId})[0].perimetros_funcionario;

                console.log(perimetros);

                this.listaPerimetros.forEach((item) => {
                    perimetros.forEach((perimetro) => {
                        if (item.id === perimetro.id){
                            this.formPerimetroFuncionarios.perimetrosSelecionados.push(perimetro.id);
                            item.selecionado = true;
                        }
                    });
                });
                this.formPerimetroFuncionarios.perimetro_id=null;
            }

            this.formPerimetroFuncionarios.update=false;
        },
        assosicarPerimetros(){
            this.formPerimetroFuncionarios.preload = true;
            axios.put(`${URL_ADMIN}/controle-ponto/perimetros/assosicarPerimetro`,this.formPerimetroFuncionarios)
                .then(response => {
                    this.formPerimetroFuncionarios.preload = false;
                    this.formPerimetroFuncionarios.update = true;
                    this.atualizarListaFuncionarios();
                    this.checarMarcarTodosFuncionarios();
                }).catch(error => {
                this.formPerimetroFuncionarios.preload = false;
                this.atualizarListaFuncionarios();
            });
        },
        resetFuncionariosSelecionados(){
            if(this.formPerimetroFuncionarios.update){
                this.formPerimetroFuncionarios.funcionariosSelecionados=[];
                this.todosFuncionariosSelecionados=false;
            }
        },


    }
});
