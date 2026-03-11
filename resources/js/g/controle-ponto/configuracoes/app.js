import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import preload from '../../../components/preload'

const app = createApp({
    components: {
        preload
    },
    data() {
        return {
            URL_ADMIN,
            EMPRESA_ID: null,
            preload: true,
            mapInitialized: false, // Flag para controlar a inicialização do mapa
            mapInitializationInProgress: false, // Flag para evitar inicializações simultâneas

            perimetros_insert: false,
            perimetros_update: false,
            perimetros_delete: false,
            perimetros_funcionarios: false,
            config_empresa: false,

            preloadConfig: false,
            formConfig: {
                tipo_frequencia: '',
                limite_tolerancia: '',
                tempo_limite_falta: '',
                tempo_limite_saida: '',
                dia_nova_frequencia: ''
            },

            paginacaoPerimetros: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            },
            formPerimetro: {
                editando: false,
                titulo: 'Adicionar perímetro',
                id: null,
                descricao: '',
                lat: -2.5919,
                long: -44.2322,
                perimetro: 50,
                obrigatorio: true,
                preload: false,
                save: false
            },
            formPerimetroDefault: null,
            listaPerimetros: [],
            listaPerimetrosDefault: [],
            listaFuncionarios: [],
            paginacaoFuncionarios: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            },
            todosFuncionariosSelecionados: false,

            listaTodosPerimetros: [],
            listaTodosPerimetrosDefault: null,

            formPerimetroFuncionarios: {
                funcionariosSelecionados: [],
                perimetrosSelecionados: [],
                perimetro_id: 0,
                preload: false,
                update: false
            },

            // Objetos do Leaflet
            map: null,
            perimetro: null, // Círculo do Leaflet
            resizeHandler: null,
            atual: null,

            // Para o autocomplete de endereços
            searchResults: []
        }
    },
    mounted() {
        this.formPerimetroDefault = _.cloneDeep(this.formPerimetro)
        this.atualizarListaFuncionarios()
        this.atualizarListaPeriemetros()

        axios
            .get(`${URL_ADMIN}/usuario/autenticado`)
            .then((response) => {
                this.preload = false
                Object.assign(this.formConfig, response.data.config_empresa)
                this.EMPRESA_ID = response.data.empresa_id
                this.getPermissoes()
            })
            .catch((error) => {
                this.preload = false
                console.error('Erro ao autenticar usuário:', error)
            })
    },
    methods: {
        //Configurações ------------------------------------
        getPermissoes() {
            this.preload = true
            axios
                .get(`${URL_ADMIN}/controle-ponto/configuracoes/getPermissoes/`)
                .then((response) => {
                    this.preload = false
                    this.perimetros_insert = response.data.perimetros_insert
                    this.perimetros_update = response.data.perimetros_update
                    this.perimetros_delete = response.data.perimetros_delete
                    this.perimetros_funcionarios = response.data.perimetros_funcionarios
                    this.config_empresa = response.data.config_empresa
                })
                .catch((error) => {
                    this.preload = false
                    console.error('Erro ao obter permissões:', error)
                })
        },
        salvarConfiguracoes() {
            $('#config_frequencia :input:visible:enabled').trigger('blur')
            if ($('#config_frequencia :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadConfig = true
            axios
                .put(`${URL_ADMIN}/controle-ponto/configuracoes/${this.EMPRESA_ID}`, this.formConfig)
                .then((response) => {
                    this.preloadConfig = false
                    mostraSucesso('', 'Configuração salva')
                })
                .catch((error) => {
                    this.preloadConfig = false
                    console.error('Erro ao salvar configurações:', error)
                })
        },

        //Perimetros ---------------------------------------
        carregandoPerimetros: function () {
            this.formPerimetroFuncionarios.preload = true
            this.paginacaoPerimetros.carregando = true
        },
        carregouPerimetros: function (dados) {
            this.listaPerimetros = dados.itens
            this.listaTodosPerimetros = dados.todos_perimetros
            this.listaPerimetrosDefault = _.cloneDeep(dados)
            this.paginacaoPerimetros.carregando = false
            this.formPerimetroFuncionarios.preload = false
        },
        atualizarListaPeriemetros() {
            this.$refs.paginacaoPerimetros.atual = 1
            this.$refs.paginacaoPerimetros.buscar()
        },

        // Função para buscar endereço usando a API de geocodificação do OpenStreetMap
        buscarEndereco() {
            const query = $('.enderecoGoogle').val()
            if (!query) return

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
                .then((response) => response.json())
                .then((data) => {
                    this.searchResults = data
                    if (data.length > 0) {
                        const place = data[0]
                        const lat = parseFloat(place.lat)
                        const lng = parseFloat(place.lon)

                        // Atualiza o mapa e o perímetro
                        this.map.setView([lat, lng], 19)
                        this.perimetro.setLatLng([lat, lng])

                        // Atualiza os valores no formulário
                        this.formPerimetro.lat = lat
                        this.formPerimetro.long = lng
                    }
                })
                .catch((error) => {
                    console.error('Erro ao buscar endereço:', error)
                })
        },

        // Método para selecionar um resultado de busca
        selecionarResultado(result) {
            const lat = parseFloat(result.lat)
            const lng = parseFloat(result.lon)

            // Atualiza o mapa e o perímetro
            this.map.setView([lat, lng], 19)
            this.perimetro.setLatLng([lat, lng])

            // Atualiza os valores no formulário
            this.formPerimetro.lat = lat
            this.formPerimetro.long = lng

            // Fecha o dropdown de resultados
            closeSearchResults()
        },

        /**
         * Inicializa o mapa Leaflet com um círculo de perímetro
         * Esta função é central e gerencia todas as operações relacionadas ao mapa
         */
        initMap() {
            // Verificar se o elemento do mapa existe
            const mapElement = document.getElementById('mapaPrimetro')
            if (!mapElement) {
                console.error('Elemento do mapa não encontrado!')
                return
            }

            // Resetar o mapa se já foi inicializado
            if (this.map) {
                this.map.remove()
                this.map = null
                this.perimetro = null
                this.resizeHandler = null
            }

            // Inicializar o mapa Leaflet
            this.map = L.map('mapaPrimetro').setView([this.formPerimetro.lat, this.formPerimetro.long], 15)

            // Adicionar camada base do OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(this.map)

            // Criar o círculo de perímetro
            this.perimetro = L.circle([this.formPerimetro.lat, this.formPerimetro.long], {
                color: '#ff0000',
                fillColor: '#ff0000',
                fillOpacity: 0.4,
                radius: this.formPerimetro.perimetro
            }).addTo(this.map)

            // Adicionar handler de redimensionamento
            this.addResizeHandler()

            // Tornar o círculo arrastável
            this.makeCircleDraggable()

            // Expor a instância Vue para uso no autocomplete
            window.vueInstance = this

            // Se não estiver editando, obter localização atual
            if (!this.formPerimetro.editando) {
                navigator.geolocation.getCurrentPosition(
                    (dados) => {
                        const lat = dados.coords.latitude
                        const lng = dados.coords.longitude
                        this.atual = { lat, lng }

                        this.formPerimetro.lat = lat
                        this.formPerimetro.long = lng

                        // Centralizar mapa e perímetro na localização atual
                        this.map.setView([lat, lng], 19)
                        this.perimetro.setLatLng([lat, lng])
                    },
                    (error) => {
                        console.warn('Não foi possível obter a localização atual:', error)
                    }
                )
            } else {
                // Se estiver editando, centralizar no ponto existente
                this.map.setView([this.formPerimetro.lat, this.formPerimetro.long], 19)
            }

            // Após a inicialização, atualizar o estado
            this.mapInitialized = true

            // Inicializar o autocomplete
            setTimeout(() => {
                if (typeof initAddressAutocomplete === 'function') {
                    initAddressAutocomplete()
                }
            }, 500)

            // Ajustar quando a janela do modal é redimensionada
            this.map.invalidateSize()
        },

        // Função para adicionar manipulador de redimensionamento ao círculo
        addResizeHandler() {
            // Criar manipulador de redimensionamento
            const resizeHandlerIcon = L.divIcon({
                className: 'resize-handler',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            })

            // Adicionar handler ao mapa
            this.resizeHandler = L.marker([0, 0], {
                icon: resizeHandlerIcon,
                draggable: true,
                zIndexOffset: 1000
            }).addTo(this.map)

            // Posicionar o handler na borda do círculo
            this.updateResizeHandlerPosition()

            // Eventos de drag do handler
            this.resizeHandler.on('drag', (e) => {
                // Calcular nova distância (raio)
                const center = this.perimetro.getLatLng()
                const handlerPos = e.target.getLatLng()
                const distance = center.distanceTo(handlerPos)

                // Atualizar raio do círculo (mínimo de 10 metros)
                const newRadius = Math.max(10, Math.round(distance))
                this.perimetro.setRadius(newRadius)

                // Atualizar o valor no formulário
                this.formPerimetro.perimetro = newRadius
            })

            // Quando o círculo for movido, mover também o handler
            this.perimetro.on('move', this.updateResizeHandlerPosition)

            // Atualizar posição do handler ao dar zoom
            this.map.on('zoomend', this.updateResizeHandlerPosition)
        },

        // Função para atualizar a posição do manipulador de redimensionamento
        updateResizeHandlerPosition() {
            if (!this.perimetro || !this.resizeHandler) return

            const center = this.perimetro.getLatLng()
            const radius = this.perimetro.getRadius()

            // Calcular posição na borda leste do círculo
            const pointOnCircle = this.calculatePointOnCircle(center, radius, 90)
            this.resizeHandler.setLatLng(pointOnCircle)
        },

        // Calcular um ponto na borda do círculo com base em ângulo
        calculatePointOnCircle(center, radius, angle) {
            // Converter ângulo para radianos
            const rad = (angle * Math.PI) / 180

            // Convertendo metros para graus de latitude/longitude
            // Aproximação: 111,320 metros = 1 grau de latitude
            const latOffset = radius / 111320

            // A longitude depende da latitude (mais estreita nos polos)
            const lngFactor = Math.cos((center.lat * Math.PI) / 180)
            const lngOffset = radius / (111320 * lngFactor)

            // Calcular o ponto com base no ângulo
            const lat = center.lat + latOffset * Math.sin(rad)
            const lng = center.lng + lngOffset * Math.cos(rad)

            return L.latLng(lat, lng)
        },

        // Tornar o círculo arrastável
        makeCircleDraggable() {
            let isDragging = false
            let startLatLng = null

            // Evento para quando o usuário clica no círculo
            this.perimetro.on('mousedown', (e) => {
                isDragging = true
                startLatLng = e.latlng

                // Desabilita o pan do mapa durante o drag
                this.map.dragging.disable()

                // Muda o cursor para indicar que está arrastando
                document.body.style.cursor = 'grabbing'

                // Evita propagação do evento
                L.DomEvent.stopPropagation(e)
            })

            // Evento para quando o mouse se move
            this.map.on('mousemove', (e) => {
                if (!isDragging) return

                // Calcula a diferença entre a posição inicial e atual
                const latDiff = e.latlng.lat - startLatLng.lat
                const lngDiff = e.latlng.lng - startLatLng.lng

                // Obtém a posição atual do círculo
                const circleLatLng = this.perimetro.getLatLng()

                // Move o círculo para a nova posição
                const newPos = {
                    lat: circleLatLng.lat + latDiff,
                    lng: circleLatLng.lng + lngDiff
                }

                this.perimetro.setLatLng(newPos)

                // Atualiza os valores no formulário
                this.formPerimetro.lat = newPos.lat
                this.formPerimetro.long = newPos.lng

                // Atualiza a posição inicial para o próximo movimento
                startLatLng = e.latlng

                // Atualiza o manipulador de redimensionamento
                this.updateResizeHandlerPosition()
            })

            // Evento para quando o botão do mouse é solto
            this.map.on('mouseup', () => {
                if (!isDragging) return

                isDragging = false
                this.map.dragging.enable()
                document.body.style.cursor = ''
            })

            // Muda o cursor quando passa sobre o círculo
            this.perimetro.on('mouseover', () => {
                document.body.style.cursor = 'grab'
            })

            this.perimetro.on('mouseout', () => {
                if (!isDragging) {
                    document.body.style.cursor = ''
                }
            })
        },

        // Método para inicializar o mapa após a mudança de obrigatorio para true
        initMapAfterChange() {
            // Esperar o DOM atualizar antes de inicializar o mapa
            setTimeout(() => {
                this.ensureMapInitialized()
            }, 300)
        },

        // Método principal para garantir inicialização do mapa
        ensureMapInitialized() {
            // Evitar inicializações simultâneas
            if (this.mapInitializationInProgress) {
                return false
            }

            // Verificar se o contêiner do mapa está visível
            const mapContainer = document.getElementById('mapaPrimetro')
            if (!mapContainer) {
                console.warn('Container do mapa não encontrado!')
                return false
            }

            // Marcar que uma inicialização está em andamento
            this.mapInitializationInProgress = true

            // Inicializar o mapa se ainda não foi feito
            if (!this.mapInitialized || !this.map) {
                console.log('Inicializando mapa...')
                this.initMap()

                // Resetar a flag após a inicialização
                setTimeout(() => {
                    this.mapInitializationInProgress = false
                }, 500)

                return true
            }

            // Atualizar o tamanho do mapa (necessário após o modal ser exibido)
            this.map.invalidateSize()

            // Resetar a flag
            this.mapInitializationInProgress = false

            return true
        },

        formNovoPerimetro() {
            this.formPerimetro = _.cloneDeep(this.formPerimetroDefault)
            this.mapInitialized = false

            // O mapa será inicializado quando o modal estiver completamente aberto
            // Usamos um temporizador para garantir que o DOM está pronto
            setTimeout(() => {
                this.ensureMapInitialized()
            }, 500)
        },

        formEditarPerimetro(perimetro) {
            this.formPerimetro = _.cloneDeep(this.formPerimetroDefault)
            this.formPerimetro.editando = true
            this.formPerimetro.titulo = 'Editar perímetro'
            this.formPerimetro.preload = true
            this.mapInitialized = false

            axios
                .get(`${URL_ADMIN}/controle-ponto/perimetros/${perimetro.id}/editar`)
                .then((response) => {
                    this.formPerimetro.preload = false
                    Object.assign(this.formPerimetro, response.data)

                    // Agora, após carregar os dados, inicializamos o mapa
                    setTimeout(() => {
                        this.ensureMapInitialized()
                    }, 500)
                })
                .catch((error) => {
                    this.formPerimetro.preload = false
                    console.error('Erro ao editar perímetro:', error)
                })
        },

        salvarPerimetro() {
            $('#janelaFormPerimetro :input:visible:enabled').trigger('blur')
            if ($('#janelaFormPerimetro :input:visible:enabled.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.formPerimetro.preload = true
            if (this.formPerimetro.editando) {
                axios
                    .put(`${URL_ADMIN}/controle-ponto/perimetros/${this.formPerimetro.id}`, this.formPerimetro)
                    .then((response) => {
                        this.formPerimetro.preload = false
                        this.formPerimetro.save = true
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
                    .catch((error) => {
                        this.formPerimetro.preload = false
                        console.error('Erro ao atualizar perímetro:', error)
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
            } else {
                axios
                    .post(`${URL_ADMIN}/controle-ponto/perimetros`, this.formPerimetro)
                    .then((response) => {
                        this.formPerimetro.preload = false
                        this.formPerimetro.save = true
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
                    .catch((error) => {
                        this.formPerimetro.preload = false
                        console.error('Erro ao salvar perímetro:', error)
                        this.atualizarListaPeriemetros()
                        this.atualizarListaFuncionarios()
                    })
            }
        },

        formApagarPerimetro(id) {
            this.formPerimetro.id = id
            this.formPerimetro.save = false
        },

        apagarPerimetro: function () {
            this.formPerimetro.preload = true

            axios
                .delete(`${URL_ADMIN}/controle-ponto/perimetros/${this.formPerimetro.id}`)
                .then((response) => {
                    this.formPerimetro.preload = false
                    this.formPerimetro.save = true
                    this.atualizarListaPeriemetros()
                    this.atualizarListaFuncionarios()
                })
                .catch((error) => {
                    this.formPerimetro.preload = false
                    console.error('Erro ao apagar perímetro:', error)
                    this.atualizarListaPeriemetros()
                    this.atualizarListaFuncionarios()
                })
        },

        //-----Perimetros a funcionarios------------
        carregando: function () {
            this.paginacaoFuncionarios.carregando = true
        },
        carregou: function (dados) {
            this.listaFuncionarios = dados
            this.paginacaoFuncionarios.carregando = false
            this.checarMarcarTodosFuncionarios()
        },
        atualizarListaFuncionarios() {
            this.$refs.paginacaoFuncionarios.atual = 1
            this.$refs.paginacaoFuncionarios.buscar()
        },
        selecionarTodosFuncionarios() {
            if (this.todosFuncionariosSelecionados) {
                this.listaFuncionarios.forEach((user) => {
                    if (!this.formPerimetroFuncionarios.funcionariosSelecionados.includes(user.id)) {
                        this.formPerimetroFuncionarios.funcionariosSelecionados.push(user.id)
                    }
                })
            } else {
                this.listaFuncionarios.forEach((user) => {
                    let index = this.formPerimetroFuncionarios.funcionariosSelecionados.indexOf(user.id)
                    if (index !== -1) {
                        this.formPerimetroFuncionarios.funcionariosSelecionados.splice(index, 1)
                    }
                })
            }
        },
        selecionarFuncionario(user) {
            if (!this.formPerimetroFuncionarios.funcionariosSelecionados.includes(user.id)) {
                this.formPerimetroFuncionarios.funcionariosSelecionados.push(user.id)
            } else {
                let index = this.formPerimetroFuncionarios.funcionariosSelecionados.indexOf(user.id)
                if (index !== -1) {
                    this.formPerimetroFuncionarios.funcionariosSelecionados.splice(index, 1)
                }
            }
            this.checarMarcarTodosFuncionarios()
        },
        selecionarPerimetro(perimetro) {
            if (!this.formPerimetroFuncionarios.perimetrosSelecionados.includes(perimetro.id)) {
                this.formPerimetroFuncionarios.perimetrosSelecionados.push(perimetro.id)
            } else {
                let index = this.formPerimetroFuncionarios.perimetrosSelecionados.indexOf(perimetro.id)
                if (index !== -1) {
                    this.formPerimetroFuncionarios.perimetrosSelecionados.splice(index, 1)
                }
            }
            this.checarMarcarTodosFuncionarios()
            this.formPerimetroFuncionarios.perimetrosSelecionados.length === 0
                ? (this.formPerimetroFuncionarios.perimetro_id = 0)
                : (this.formPerimetroFuncionarios.perimetro_id = null)
        },
        checarMarcarTodosFuncionarios() {
            let quantidade = this.listaFuncionarios.length
            let marcados = this.listaFuncionarios.filter((funcionario) =>
                this.formPerimetroFuncionarios.funcionariosSelecionados.includes(funcionario.id)
            ).length
            this.todosFuncionariosSelecionados = quantidade === marcados
        },
        formAssociarPerimetro() {
            this.formPerimetroFuncionarios.perimetro_id = 0
            this.formPerimetroFuncionarios.perimetrosSelecionados = []

            if (this.formPerimetroFuncionarios.funcionariosSelecionados.length === 1) {
                let funcionarioId = this.formPerimetroFuncionarios.funcionariosSelecionados[0]
                let perimetros = _.filter(this.listaFuncionarios, { id: funcionarioId })[0].perimetros_funcionario

                this.listaTodosPerimetros.forEach((item) => {
                    perimetros.forEach((perimetro) => {
                        if (item.id === perimetro.id) {
                            this.formPerimetroFuncionarios.perimetrosSelecionados.push(perimetro.id)
                            item.selecionado = true
                        }
                    })
                })
                this.formPerimetroFuncionarios.perimetro_id = null
            }

            this.formPerimetroFuncionarios.update = false
        },
        assosicarPerimetros() {
            this.formPerimetroFuncionarios.preload = true
            axios
                .put(`${URL_ADMIN}/controle-ponto/perimetros/assosicarPerimetro`, this.formPerimetroFuncionarios)
                .then((response) => {
                    this.formPerimetroFuncionarios.preload = false
                    this.formPerimetroFuncionarios.update = true
                    this.atualizarListaFuncionarios()
                    this.checarMarcarTodosFuncionarios()
                })
                .catch((error) => {
                    this.formPerimetroFuncionarios.preload = false
                    console.error('Erro ao associar perímetros:', error)
                    this.atualizarListaFuncionarios()
                })
        },
        resetFuncionariosSelecionados() {
            if (this.formPerimetroFuncionarios.update) {
                this.formPerimetroFuncionarios.funcionariosSelecionados = []
                this.todosFuncionariosSelecionados = false
            }
        }
    }
})

registerGlobals(app)
app.mount('#app')
