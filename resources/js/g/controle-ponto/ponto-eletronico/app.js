import { createApp, nextTick } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import preload from '../../../components/preload'
import datepicker from '../../../components/DatePicker'
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
                save: false,
                accuracy: null,
                distanciaPerimetro: null
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
            accuracyCircle: null,
            userLocation: null,
            watchId: null,
            geoStatus: 'idle', // idle | seeking | ready | poor | error
            geoErrorMessage: '',
            webCam: null,
            agora: moment(),
            hoje: '',

            // Limites de precisão GPS (metros)
            ACCURACY_MAX_USABLE: 500,
            ACCURACY_BUFFER_CAP: 80
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
        },
        podeContinuar() {
            return !!this.formPonto.dentro && this.geoStatus === 'ready'
        },
        geoStatusAlertClass() {
            if (this.geoStatus === 'error') return 'alert-danger'
            if (this.geoStatus === 'poor') return 'alert-warning'
            if (this.geoStatus === 'ready' && this.formPonto.dentro) return 'alert-success'
            if (this.geoStatus === 'ready' && !this.formPonto.dentro) return 'alert-warning'
            return 'alert-info'
        },
        geoStatusMessage() {
            if (this.geoStatus === 'error') {
                return this.geoErrorMessage || 'Não foi possível obter a localização.'
            }
            if (this.geoStatus === 'seeking') {
                return 'Obtendo GPS… mantenha a tela aberta e a localização precisa ligada (pode levar até 30s).'
            }
            if (this.geoStatus === 'poor') {
                const acc = this.formPonto.accuracy != null ? Math.round(this.formPonto.accuracy) : '?'
                return `Sinal impreciso (${acc} m). Aguarde um GPS melhor ou vá para área aberta e toque em Atualizar localização.`
            }
            if (this.geoStatus === 'ready') {
                const acc = this.formPonto.accuracy != null ? Math.round(this.formPonto.accuracy) : null
                const precisao =
                    acc != null
                        ? `Precisão atual: ${acc} m${acc > 50 ? ' (ainda um pouco imprecisa)' : ''}.`
                        : 'Localização obtida.'
                if (!this.formPonto.obrigatorio) {
                    return precisao
                }
                if (this.formPonto.dentro) {
                    return `${precisao} Você está dentro do perímetro permitido.`
                }
                const dist =
                    this.formPonto.distanciaPerimetro != null
                        ? Math.round(this.formPonto.distanciaPerimetro)
                        : null
                return dist != null
                    ? `${precisao} Fora do perímetro (a ${dist} m do local permitido).`
                    : `${precisao} Fora do perímetro permitido.`
            }
            return ''
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

        geoOptions() {
            return {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }
        },

        // Inicialização do mapa Leaflet
        async initMap() {
            this.mapLoading = true
            this.clearWatch()

            // Limpar mapa anterior se existir
            if (this.map) {
                this.map.remove()
                this.map = null
                this.marker = null
                this.accuracyCircle = null
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
                this.iniciarRastreamento()
            } catch (e) {
                console.error('Erro ao inicializar mapa:', e)
                this.mapLoading = false
                this.geoStatus = 'error'
                this.geoErrorMessage = 'Não foi possível carregar o mapa.'
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

        // Iniciar rastreamento contínuo via watchPosition (melhor para GPS mobile)
        iniciarRastreamento() {
            if (!navigator.geolocation) {
                this.geoStatus = 'error'
                this.geoErrorMessage = 'Seu navegador não suporta geolocalização.'
                this.formPonto.dentro = !this.formPonto.obrigatorio
                return
            }

            if (!window.isSecureContext) {
                this.geoStatus = 'error'
                this.geoErrorMessage = 'A localização só funciona em conexão segura (HTTPS).'
                this.formPonto.dentro = false
                return
            }

            this.clearWatch()
            this.geoStatus = 'seeking'
            this.geoErrorMessage = ''
            this.formPonto.accuracy = null
            this.formPonto.distanciaPerimetro = null
            if (this.formPonto.obrigatorio) {
                this.formPonto.dentro = false
            }

            this.watchId = navigator.geolocation.watchPosition(
                (posicao) => this.atualizarPosicao(posicao),
                (erro) => this.handleGeoError(erro),
                this.geoOptions()
            )
        },

        // Reinicia o watch (útil após liberar permissão / Localização Precisa no iOS)
        atualizarLocalizacao() {
            this.iniciarRastreamento()
        },

        clearWatch() {
            if (this.watchId != null && navigator.geolocation) {
                navigator.geolocation.clearWatch(this.watchId)
                this.watchId = null
            }
        },

        // Atualizar a posição do usuário
        atualizarPosicao(posicao) {
            if (!this.map || !this.formPonto.telaGPS) return

            const coords = posicao.coords
            const accuracy = typeof coords.accuracy === 'number' ? coords.accuracy : null

            // Ignorar leituras muito ruins até chegar um fix melhor
            if (accuracy != null && accuracy > this.ACCURACY_MAX_USABLE) {
                this.formPonto.accuracy = accuracy
                this.geoStatus = 'poor'
                this.formPonto.dentro = false
                return
            }

            const latLng = [coords.latitude, coords.longitude]

            this.userLocation = {
                lat: coords.latitude,
                lng: coords.longitude,
                accuracy
            }

            this.formPonto.lat = coords.latitude
            this.formPonto.long = coords.longitude
            this.formPonto.accuracy = accuracy
            this.geoStatus = 'ready'
            this.geoErrorMessage = ''

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

            // Círculo de precisão do GPS
            if (accuracy != null && accuracy > 0) {
                if (!this.accuracyCircle) {
                    this.accuracyCircle = L.circle(latLng, {
                        color: '#3388ff',
                        weight: 1,
                        fillColor: '#3388ff',
                        fillOpacity: 0.15,
                        radius: accuracy
                    }).addTo(this.map)
                } else {
                    this.accuracyCircle.setLatLng(latLng)
                    this.accuracyCircle.setRadius(accuracy)
                }
            }

            // Verificar se está dentro de algum perímetro
            this.verificarPerimetros(latLng, accuracy)
        },

        // Tratar erro de geolocalização
        handleGeoError(erro) {
            console.error('Erro ao obter localização: ', erro && erro.message)

            const code = erro && erro.code
            if (code === 1) {
                this.geoErrorMessage =
                    'Permissão de localização negada. Ative a localização do navegador/celular (Localização Precisa no iOS) e toque em Atualizar localização.'
            } else if (code === 2) {
                this.geoErrorMessage =
                    'GPS indisponível. Verifique se a localização do celular está ligada e tente novamente.'
            } else if (code === 3) {
                this.geoErrorMessage =
                    'Tempo esgotado ao obter o GPS. Vá para área aberta, mantenha a tela aberta e toque em Atualizar localização.'
            } else {
                this.geoErrorMessage = (erro && erro.message) || 'Não foi possível obter a localização.'
            }

            this.geoStatus = 'error'
            if (this.formPonto.obrigatorio) {
                this.formPonto.dentro = false
            } else {
                this.formPonto.dentro = true
            }
        },

        // Verificar se o usuário está dentro de algum perímetro (com buffer de accuracy)
        verificarPerimetros(posicao, accuracy) {
            if (!this.formPonto.obrigatorio || !this.listaPerimetros) {
                this.formPonto.dentro = true
                this.formPonto.distanciaPerimetro = 0
                return
            }

            this.formPonto.dentro = false
            let menorDistancia = null
            const buffer =
                accuracy != null && accuracy > 0
                    ? Math.min(accuracy, this.ACCURACY_BUFFER_CAP)
                    : 0

            this.listaPerimetros.forEach((perimetro) => {
                if (!perimetro.lat || !perimetro.long || !perimetro.circulo) return

                const distancia = this.calcularDistancia(posicao, [perimetro.lat, perimetro.long])
                const raio = perimetro.perimetro || 50

                if (menorDistancia == null || distancia < menorDistancia) {
                    menorDistancia = distancia
                }

                if (distancia <= raio + buffer) {
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

            this.formPonto.distanciaPerimetro = menorDistancia
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
            this.clearWatch()
            this.geoStatus = 'idle'
            this.geoErrorMessage = ''
            this.accuracyCircle = null

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
            this.geoStatus = 'seeking'
            this.geoErrorMessage = ''

            // Inicializar mapa após um breve atraso para garantir que o modal está visível
            await new Promise((resolve) => setTimeout(resolve, 300))
            await this.initMap()
        },

        // Continuar para a tela da câmera
        async continuar() {
            if (!this.podeContinuar) return

            this.clearWatch()
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
