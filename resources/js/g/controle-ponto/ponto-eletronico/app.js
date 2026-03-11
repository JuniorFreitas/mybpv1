import { createApp, nextTick } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import preload from '../../../components/preload'
import datepicker from '../../../components/DatePicker'
import { now } from 'moment'

const app = createApp({
    components: {
        preload,
        datepicker
    },
    data() {
        return {
            URL_ADMIN,
            EMPRESA_ID: null,
            preload: true,
            mapLoading: false,

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
                dentro: false,
                telaGPS: true,
                telaCamera: false,
                preload: false,
                foto: null,
                save: false
            },
            formPontoDefault: null,
            registros: [],

            // Contador regressivo para captura de foto
            contadorRegressivo: 0,
            contadorTimer: null,

            // Histórico
            historico: [],
            formHistorico: {
                data: moment().format('DD/MM/YYYY'),
                preload: false
            },
            duracaoDiaHistorico: null,
            tempoTrabalhadoHistorico: null,
            formRegistro: {
                preload: false
            },
            modelRegistro: null,

            // Mapa e localização
            map: null,
            marker: null,
            userLocation: null,
            locationTimer: null,
            webCam: null,
            agora: moment(),
            hoje: ''
        }
    },
    mounted() {
        this.init()
        this.atualizarHistorico()

        String.prototype.capitalize = function () {
            return this.charAt(0).toUpperCase() + this.substr(1)
        }
    },
    computed: {
        escalaUsuario() {
            return this.listaEscalas ? this.listaEscalas[0] : null
        },
        perimetroUsuario() {
            return this.listaPerimetros ? this.listaPerimetros[0] : null
        }
    },
    methods: {
        // Inicialização da aplicação
        async init() {
            this.preload = true
            try {
                const response = await axios.get(`${URL_ADMIN}/controle-ponto/ponto-eletronico/init/`)
                this.preload = false
                this.listaPerimetros = response.data.lista_perimetros
                this.listaEscalas = response.data.lista_escalas
                this.agora = moment(response.data.agora, 'YYYY-MM-DD HH:mm:ss')
                this.registros = response.data.registros

                if (this.perimetroUsuario) {
                    this.formPonto.obrigatorio = this.perimetroUsuario.obrigatorio
                }
                this.formPontoDefault = _.cloneDeep(this.formPonto)

                // Iniciar o relógio
                setInterval(() => {
                    this.agora.add(1, 'seconds')
                    this.hoje = `${this.agora.format('DD/MM/YYYY')} - ${this.agora.format('HH')}h:${this.agora.format('mm')} - ${this.agora.format('dddd').capitalize()}`
                }, 1000)
            } catch (error) {
                console.error('Erro ao inicializar aplicação:', error)
                this.preload = false
            }
        },

        // Função auxiliar para encapsular a geolocalização em uma Promise
        getPosition(options = {}) {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, options)
            })
        },

        // Inicialização do mapa Leaflet
        async initMap() {
            this.mapLoading = true

            // Limpar mapa anterior se existir
            if (this.map) {
                this.map.remove()
                this.map = null
                this.marker = null
            }

            // Inicializar mapa após o DOM estar pronto
            await new Promise((resolve) => nextTick(resolve))

            try {
                // Criar mapa Leaflet
                this.map = L.map('mapaPrimetro').setView([this.formPonto.lat, this.formPonto.long], 13)

                // Adicionar camada de mapa base
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(this.map)

                // Forçar recálculo de tamanho após renderização
                await new Promise((resolve) =>
                    setTimeout(() => {
                        this.map.invalidateSize()
                        resolve()
                    }, 300)
                )

                this.mapLoading = false

                // Adicionar perímetros ao mapa
                this.adicionarPerimetros()

                // Iniciar rastreamento de localização
                await this.iniciarRastreamento()
            } catch (e) {
                console.error('Erro ao inicializar mapa:', e)
                this.mapLoading = false
            }
        },

        // Adicionar perímetros ao mapa
        adicionarPerimetros() {
            if (!this.map || !this.formPonto.obrigatorio || !this.listaPerimetros) return

            this.listaPerimetros.forEach((perimetro) => {
                if (!perimetro.lat || !perimetro.long) return

                // Definir raio do perímetro (com valor padrão se não for definido)
                const raio = perimetro.perimetro || 50

                // Criar círculo no mapa
                perimetro.circulo = L.circle([perimetro.lat, perimetro.long], {
                    color: '#ff0000',
                    weight: 2,
                    fillColor: '#ff0000',
                    fillOpacity: 0.4,
                    radius: raio
                }).addTo(this.map)
            })
        },

        // Iniciar rastreamento de localização do usuário
        async iniciarRastreamento() {
            // Opções para a API de geolocalização
            const geoOptions = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }

            try {
                // Obter posição inicial
                const posicao = await this.getPosition(geoOptions)
                this.atualizarPosicao(posicao)
            } catch (erro) {
                this.handleGeoError(erro)
            }

            // Configurar atualização contínua da posição
            this.locationTimer = setInterval(async () => {
                if (this.formPonto.telaGPS && this.map) {
                    try {
                        const posicao = await this.getPosition(geoOptions)
                        this.atualizarPosicao(posicao)
                    } catch (erro) {
                        this.handleGeoError(erro)
                    }
                }
            }, 5000)
        },

        // Atualizar a posição do usuário
        atualizarPosicao(posicao) {
            if (!this.map) return

            const coords = posicao.coords
            const latLng = [coords.latitude, coords.longitude]

            // Armazenar posição atual
            this.userLocation = {
                lat: coords.latitude,
                lng: coords.longitude
            }

            this.formPonto.lat = coords.latitude
            this.formPonto.long = coords.longitude

            // Centralizar mapa na posição
            this.map.setView(latLng, this.map.getZoom() || 16)

            // Criar ou atualizar marcador
            if (!this.marker) {
                this.marker = L.marker(latLng, {
                    title: 'Sua localização atual'
                }).addTo(this.map)
            } else {
                this.marker.setLatLng(latLng)
            }

            // Verificar se está dentro de algum perímetro
            this.verificarPerimetros(latLng)
        },

        // Tratar erro de geolocalização
        handleGeoError(erro) {
            console.error('Erro ao obter localização: ', erro.message)

            // Se não conseguir obter a localização, definir como não estando dentro de nenhum perímetro
            if (this.formPonto.obrigatorio) {
                this.formPonto.dentro = false
            } else {
                this.formPonto.dentro = true
            }
        },

        // Verificar se o usuário está dentro de algum perímetro
        verificarPerimetros(posicao) {
            if (!this.formPonto.obrigatorio || !this.listaPerimetros) {
                this.formPonto.dentro = true
                return
            }

            // Começar assumindo que não está dentro de nenhum perímetro
            this.formPonto.dentro = false

            this.listaPerimetros.forEach((perimetro) => {
                if (!perimetro.lat || !perimetro.long || !perimetro.circulo) return

                // Calcular distância entre usuário e centro do perímetro
                const distancia = this.calcularDistancia(posicao, [perimetro.lat, perimetro.long])

                const raio = perimetro.perimetro || 50

                // Verificar se está dentro do perímetro
                if (distancia < raio) {
                    this.formPonto.dentro = true
                    perimetro.circulo.setStyle({
                        color: '#3f9827',
                        fillColor: '#3f9827'
                    })
                } else {
                    perimetro.circulo.setStyle({
                        color: '#ff0000',
                        fillColor: '#ff0000'
                    })
                }
            })
        },

        // Calcular distância entre dois pontos usando fórmula de Haversine
        calcularDistancia(ponto1, ponto2) {
            const R = 6371e3 // Raio da Terra em metros
            const lat1 = Array.isArray(ponto1) ? ponto1[0] : ponto1.lat || 0
            const lng1 = Array.isArray(ponto1) ? ponto1[1] : ponto1.lng || 0
            const lat2 = Array.isArray(ponto2) ? ponto2[0] : ponto2.lat || 0
            const lng2 = Array.isArray(ponto2) ? ponto2[1] : ponto2.lng || 0

            const φ1 = this.toRadians(lat1)
            const φ2 = this.toRadians(lat2)
            const Δφ = this.toRadians(lat2 - lat1)
            const Δλ = this.toRadians(lng2 - lng1)

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2)
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))

            return R * c // Distância em metros
        },

        // Converter graus para radianos
        toRadians(degrees) {
            return (degrees * Math.PI) / 180
        },

        // Parar rastreamento GPS e limpar recursos
        stopGPS() {
            if (this.locationTimer) {
                clearInterval(this.locationTimer)
                this.locationTimer = null
            }

            if (this.map) {
                this.map.remove()
                this.map = null
                this.marker = null
            }
        },

        // Atualizar histórico de registros
        async atualizarHistorico() {
            this.formHistorico.preload = true

            try {
                const response = await axios.post(`${URL_ADMIN}/controle-ponto/ponto-eletronico/atualizarHistorico/`, this.formHistorico)
                this.formHistorico.preload = false
                this.historico = response.data.historicos

                // Inicializar tempos
                this.duracaoDiaHistorico = moment('00:00', 'HH:mm')
                this.tempoTrabalhadoHistorico = moment('00:00', 'HH:mm')

                // Adicionar minutos
                this.duracaoDiaHistorico.add(response.data.duracao.total_minutos, 'minutes')
                this.tempoTrabalhadoHistorico.add(response.data.minutos_trabalhados, 'minutes')
            } catch (error) {
                console.error('Erro ao atualizar histórico:', error)
                this.formHistorico.preload = false
            }
        },

        // Ver detalhes de um registro
        async verDetalhes(ponto_id, periodo_id) {
            this.formRegistro.preload = true

            try {
                const response = await axios.get(`${URL_ADMIN}/controle-ponto/ponto-eletronico/${ponto_id}/${periodo_id}`)
                this.formRegistro.preload = false
                this.modelRegistro = response.data
            } catch (error) {
                console.error('Erro ao buscar detalhes:', error)
                this.formRegistro.preload = false
            }
        },

        // Preparar formulário para novo registro
        async formNovoRegistro() {
            this.formPonto = _.cloneDeep(this.formPontoDefault)

            // Inicializar mapa após um breve atraso para garantir que o modal está visível
            await new Promise((resolve) => setTimeout(resolve, 300))
            await this.initMap()
        },

        // Continuar para a tela da câmera
        async continuar() {
            this.formPonto.telaGPS = false
            this.formPonto.telaCamera = true

            // Inicializar webcam após transição
            await new Promise((resolve) => setTimeout(resolve, 500))

            // Configurar a webcam
            Webcam.set({
                width: 320,
                height: 240,
                image_format: 'jpeg',
                jpeg_quality: 90
            })

            // Iniciar a webcam
            Webcam.attach(this.$refs.camera)
        },

        // Iniciar contagem regressiva para tirar foto
        iniciarContagem() {
            // Limpar contador anterior se existir
            if (this.contadorTimer) {
                clearInterval(this.contadorTimer)
            }

            // Iniciar contagem de 3 segundos
            this.contadorRegressivo = 3

            this.contadorTimer = setInterval(() => {
                this.contadorRegressivo--

                if (this.contadorRegressivo <= 0) {
                    clearInterval(this.contadorTimer)
                    this.capturarFoto()
                }
            }, 1000)
        },

        // Capturar a foto após a contagem
        async capturarFoto() {
            try {
                // Encapsular Webcam.snap em uma Promise
                const data_uri = await new Promise((resolve) => {
                    Webcam.snap((data_uri) => resolve(data_uri))
                })

                this.formPonto.foto = data_uri
                this.formPonto.preload = true

                const response = await axios.post(`${URL_ADMIN}/controle-ponto/ponto-eletronico`, this.formPonto)
                this.formPonto.preload = false
                this.formPonto.save = true
                this.registros = response.data.registrosHoje
                window.location.reload()
            } catch (error) {
                console.error('Erro ao registrar ponto:', error)
                this.formPonto.preload = false
            }
        },

        // Registrar ponto - inicia a contagem regressiva
        registrarPonto() {
            this.iniciarContagem()
        }
    }
})

registerGlobals(app)
app.mount('#app')
