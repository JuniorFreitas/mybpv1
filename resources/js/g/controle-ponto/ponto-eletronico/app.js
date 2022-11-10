import preload from '../../../components/preload';
import datepicker from '../../../components/DatePicker';
import {Loader} from "@googlemaps/js-api-loader"
import {now} from "moment";

const app = new Vue({
    el: '#app',
    components: {
        preload,
        datepicker
    },
    data: {
        URL_ADMIN,
        GOOGLE_MAPS_KEY,
        EMPRESA_ID: null,
        preload: true,
        preloadGoogleMaps: true,

        listaPerimetros: [],
        listaEscalas: [],
        formPonto: {
            editando: false,
            titulo: 'Nova marcação',
            id: null,
            lat: -2.5919,
            long: -44.2322,
            perimetro: 50,
            obrigatorio: true,
            dentro: false, // gps
            telaGPS: true,
            telaCamera: false,
            preload: false,
            foto: null,
            save: false,
        },
        formPontoDefault: null,
        registros: [],

        //Historico ------------
        historico: [],
        formHistorico: {
            data: moment().format('DD/MM/YYYY'),
            preload: false,
        },
        duracaoDiaHistorico:null,
        tempoTrabalhadoHistorico:null,
        formRegistro:{
            preload:false,
        },
        modelRegistro:null,


        map: null,
        marker: null,
        atual: null,
        timeMapa: null,
        webCam: null,
        agora: moment(),
        hoje: ''

    },
    mounted() {

        //this.atualizarListaFuncionarios();
        //this.atualizarListaPeriemetros();
        const loader = new Loader({
            apiKey: this.GOOGLE_MAPS_KEY,
            version: "weekly",
            libraries: ['places', 'geometry']
        });

        loader.load().then(() => {
            this.preloadGoogleMaps = false;
        });

        this.init();
        this.atualizarHistorico();

        String.prototype.capitalize = function () {
            return this.charAt(0).toUpperCase() + this.substr(1);
        }


    },
    computed: {
        escalaUsuario() {
            return this.listaEscalas[0];
        },
        perimetroUsuario() {
            return this.listaPerimetros[0];
        }
    },
    methods: {
        //Marcar ponto ------------------------------------
        init() {
            this.preload = true;
            axios.get(`${URL_ADMIN}/controle-ponto/ponto-eletronico/init/`,)
                .then(response => {
                    this.preload = false;
                    this.listaPerimetros = response.data.lista_perimetros;
                    this.listaEscalas = response.data.lista_escalas;
                    this.agora = moment(response.data.agora, 'YYYY-MM-DD HH:mm:ss');
                    this.registros = response.data.registros;


                    /*if (!this.perimetroUsuario.obrigatorio) { // senao é obrigatorio tela de gps
                        this.formPonto.obrigatorio = false;
                        this.formPonto.telaGPS = true;
                        this.formPonto.telaCamera = false;
                    }*/
                    this.formPonto.obrigatorio = this.perimetroUsuario.obrigatorio;
                    this.formPontoDefault = _.cloneDeep(this.formPonto);

                    setInterval(() => {
                        this.agora.add(1, 'seconds');
                        this.hoje = `${this.agora.format('DD/MM/YYYY')} - ${this.agora.format('HH')}h:${this.agora.format('mm')} - ${this.agora.format('dddd').capitalize()}`;
                    }, 1000);

                }).catch(error => {
                //this.preload = false;
            });
        },
        initMap() {
            this.marker = null;
            this.map = null;
            let latLong = null;
            this.map = new google.maps.Map(document.getElementById("mapaPrimetro"), {
                center: {lat: this.formPonto.lat, lng: this.formPonto.long},
                zoom: 8,
                //streetViewControl: false,
                disableDefaultUI: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            if(this.formPonto.obrigatorio){
                console.log(this.listaPerimetros);
                this.listaPerimetros.forEach((p) => {
                    p.circulo = new google.maps.Circle({
                        strokeColor: '#ff0000',
                        strokeWeigth: 2,
                        strokeOpacity: 1,
                        fillColor: '#ff0000',
                        fillOpacity: .4,
                        center: {lat: p.lat, lng: p.long},
                        radius: p.perimetro,
                        map: this.map,
                        editable: false,
                        draggable: false
                    });

                    /*this.perimetro.addListener('bounds_changed', (event) => {
                        //console.log(event,this.perimetro);
                        this.formPonto.perimetro = this.perimetro.radius;
                        /!*let distancia = google.maps.geometry.spherical.computeDistanceBetween(this.marker.getPosition(), this.perimetro.getCenter());
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
                        }*!/
                    });*/
                    /*this.perimetro.addListener('dragend', (event) => {
                        //this.formPonto.perimetro = this.perimetro.radius;
                        this.formPonto.lat = event.latLng.lat();
                        this.formPonto.long = event.latLng.lng();

                        /!*let distancia = google.maps.geometry.spherical.computeDistanceBetween(this.marker.getPosition(), this.perimetro.getCenter());
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
                        }*!/

                    });*/
                });
            }



            //buscar a localizacao
            navigator.geolocation.getCurrentPosition((dados) => {
                latLong = new google.maps.LatLng(dados.coords.latitude, dados.coords.longitude);
                this.atual = latLong

                this.formPonto.lat = this.atual.lat();
                this.formPonto.long = this.atual.lng();

                this.map.setCenter(new google.maps.LatLng(this.formPonto.lat, this.formPonto.long));
                this.map.setZoom(19);

                //this.perimetro.setCenter(latLong);
                if (!this.marker) {
                    this.map.setCenter(this.atual);
                    this.map.setZoom(19);
                    this.marker = new google.maps.Marker({
                        map: this.map,
                        position: latLong,
                        title: "Você está aqui",
                        //icon: `${URL_SITE}/imagens/map_icon.png`,
                        flat: false,
                        draggable: false
                    });
                }
                //this.marker.setMap(null); //limpar

                this.marker.setPosition(latLong);

                //verificando todos os perimetros se esta dentro deles
                if(this.formPonto.obrigatorio){
                    this.formPonto.dentro = false;
                    this.listaPerimetros.forEach((p) => {

                        let distancia = google.maps.geometry.spherical.computeDistanceBetween(this.marker.getPosition(), p.circulo.getCenter());
                        if ((distancia - p.circulo.radius) < 0) {
                            this.formPonto.dentro = true;
                            p.circulo.setOptions({
                                fillColor: '#3f9827',
                                strokeColor: '#3f9827',
                            })
                        } else {
                            p.circulo.setOptions({
                                fillColor: '#ff0000',
                                strokeColor: '#ff0000',
                            })
                        }
                    });
                }else{
                    this.formPonto.dentro = true;
                }



                //atualizar de 5 em 5 segundos
                this.timeMapa = setInterval(() => {
                    if (this.formPonto.telaGPS) {
                        navigator.geolocation.getCurrentPosition((dados) => {
                            latLong = new google.maps.LatLng(dados.coords.latitude, dados.coords.longitude);
                            this.atual = latLong

                            this.formPonto.lat = this.atual.lat();
                            this.formPonto.long = this.atual.lng();

                            //this.perimetro.setCenter(latLong);
                            if (this.marker) {
                                this.marker.setPosition(latLong);
                                console.log('gps');
                            }
                            //verificando todos os perimetros se esta dentro deles
                            if(this.formPonto.obrigatorio){
                                this.formPonto.dentro = false;
                                this.listaPerimetros.forEach((p) => {

                                    let distancia = google.maps.geometry.spherical.computeDistanceBetween(this.marker.getPosition(), p.circulo.getCenter());
                                    if ((distancia - p.circulo.radius) < 0) {
                                        this.formPonto.dentro = true;
                                        p.circulo.setOptions({
                                            fillColor: '#3f9827',
                                            strokeColor: '#3f9827',
                                        })
                                    } else {
                                        p.circulo.setOptions({
                                            fillColor: '#ff0000',
                                            strokeColor: '#ff0000',
                                        })
                                    }
                                });
                            }else{
                                this.formPonto.dentro = true;
                            }

                            //this.marker.setMap(null); //limpar


                            /* this.marker.addListener('drag', (event) => {
                                 this.marcarMapa(event.latLng);
                             });*/


                            //this.marcarMapa(this.atual);

                            //this.calcularRota();
                        });
                    }

                }, 5000);

                /* this.marker.addListener('drag', (event) => {
                     this.marcarMapa(event.latLng);
                 });*/


                //this.marcarMapa(this.atual);

                //this.calcularRota();
            });

        },
        stopGPS() {
            clearInterval(this.timeMapa);
        },

        atualizarHistorico() {
            this.formHistorico.preload = true;
            axios.post(`${URL_ADMIN}/controle-ponto/ponto-eletronico/atualizarHistorico/`,this.formHistorico)
                .then(response => {
                    this.formHistorico.preload = false;
                    this.historico = response.data.historicos;
                    this.duracaoDiaHistorico = moment('00:00','HH:mm');
                    this.tempoTrabalhadoHistorico = moment('00:00','HH:mm');
                    this.duracaoDiaHistorico.add(response.data.duracao.total_minutos,'minutes');
                    this.tempoTrabalhadoHistorico.add(response.data.minutos_trabalhados,'minutes');
                }).catch(error => {
                this.formHistorico.preload = false;
            });
        },
        verDetalhes(ponto_id,periodo_id) {

            this.formRegistro.preload = true;
            axios.get(`${URL_ADMIN}/controle-ponto/ponto-eletronico/${ponto_id}/${periodo_id}`)
                .then(response => {
                    this.formRegistro.preload = false;
                    this.modelRegistro = response.data;

                }).catch(error => {
                this.formRegistro.preload = false;
            });
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
        formNovoRegistro() {
            this.formPonto = _.cloneDeep(this.formPontoDefault);
            if (!this.preloadGoogleMaps) {
                this.initMap();
            }
        },
        continuar() {
            this.formPonto.telaGPS = false;
            this.formPonto.telaCamera = true;

            setTimeout(() => {
                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                Webcam.attach(this.$refs.camera);
            }, 500)


        },
        registrarPonto() {
            Webcam.snap(data_uri => {
                this.formPonto.foto = data_uri;
                this.formPonto.preload = true;
                axios.post(`${URL_ADMIN}/controle-ponto/ponto-eletronico`, this.formPonto)
                    .then(response => {
                        this.formPonto.preload = false;
                        this.formPonto.save = true;
                        this.registros = response.data.registrosHoje;
                        // this.atualizarHistorico();
                        window.location.reload();

                    }).catch(error => {
                    this.formPonto.preload = false;
                });
            });
            /*$('#janelaFormPerimetro :input:visible:enabled').trigger('blur');
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
            }*/

        },


    }
});
