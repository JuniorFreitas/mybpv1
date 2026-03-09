@extends('layouts.sistema')
@section('title', 'Controle de ponto: Configurações')
@section('content_header', 'Controle de ponto: configurações')
@section('content')
    {{--Janela confirmar pagar--}}
    <modal id="janelaConfirmar" titulo="Apagar perimetro">
        <template #conteudo>
            <preload v-show="formPerimetro.preload" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="formPerimetro.save">
                <h4><i class="icon fa fa-check"></i> Perímetro apagado com sucesso!</h4>
            </div>
            <h4 v-show="!formPerimetro.save && !formPerimetro.preload">Atenção! Deseja realmente apagar perímetro:</h4>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagarPerimetro()"
                    v-show="!formPerimetro.save && !formPerimetro.preload">Apagar
            </button>
        </template>
    </modal>

    <!--Janela de Adicionar/Editar Perimetro-->
    <modal id="janelaFormPerimetro" :titulo="formPerimetro.titulo" :fechar="!formPerimetro.preload" size="g">
        <template #conteudo>
            <h4 class="text-success text-center" v-if="!formPerimetro.preload && formPerimetro.save">
                <i class="fas fa-check fa-2x"></i><br>
                Perímetro
                <span v-if="formPerimetro.editando">atualizado</span>
                <span v-else>cadastrado</span>
            </h4>
            <preload v-if="formPerimetro.preload" label="Aguarde..."></preload>

            <div v-show="!formPerimetro.preload && !formPerimetro.save">
                <div class="form-group">
                    <label>Descrição:</label>
                    <input type="text" class="form-control" placeholder="Nome para o local"
                           onblur="valida_campo_vazio(this,3)" v-model="formPerimetro.descricao">
                </div>
                <div class="form-group">
                    <label>Registrar ponto em um local específico:</label>
                    <select class="form-control" v-model="formPerimetro.obrigatorio"
                            @change="formPerimetro.obrigatorio ? initMapAfterChange() : false">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                    <small>
                        Define se será obrigatório registrar ponto dentro do perímetro espeficicado abaixo. Deixe com
                        valor <strong>Não</strong> para casos de home office
                    </small>
                </div>
                <div class="row" v-if="formPerimetro.obrigatorio">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Latitude:</label>
                            <input type="text" :disabled="true" class="form-control" v-model="formPerimetro.lat">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Longitude:</label>
                            <input type="text" :disabled="true" class="form-control" v-model="formPerimetro.long">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Raio:</label>
                            <input type="text" :disabled="true" class="form-control" v-model="formPerimetro.perimetro">
                            <small>Em metros</small>
                        </div>
                    </div>
                </div>
                <div class="form-group" v-if="formPerimetro.obrigatorio">
                    <label>Buscar por endereço</label>
                    <div class="endereco-search-container">
                        <div class="input-group">
                            <input type="text" :autocomplete="`mastertag_${parseInt((Math.random() * 999999))}`"
                                   class="form-control enderecoGoogle" placeholder="Buscar por endereço"
                                   style="width: 89%;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" @click="buscarEndereco">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="search-results"></div>
                    </div>
                </div>

                <!-- Mapa -->
                <div id="mapaPrimetro" style="width:100%; height:500px; margin-bottom:15px;"
                     v-if="formPerimetro.obrigatorio"></div>

                <!-- Controles de ajuste de perímetro -->
                <div class="perimeter-controls" v-if="formPerimetro.obrigatorio">
                    <div class="form-group">
                        <label>Ajuste do perímetro:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button class="btn btn-outline-secondary decrease-size" type="button"
                                        @click="formPerimetro.perimetro = Math.max(10, formPerimetro.perimetro - 10); perimetro.setRadius(formPerimetro.perimetro)">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                            <input type="number" class="form-control" v-model="formPerimetro.perimetro"
                                   @change="perimetro && perimetro.setRadius(formPerimetro.perimetro)">
                            <div class="input-group-append">
                                <span class="input-group-text">metros</span>
                                <button class="btn btn-outline-secondary increase-size" type="button"
                                        @click="formPerimetro.perimetro = formPerimetro.perimetro + 10; perimetro.setRadius(formPerimetro.perimetro)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Arraste o círculo no mapa para mover a localização ou use o controle acima para ajustar o
                            tamanho do perímetro.
                        </small>
                    </div>
                </div>
            </div>
        </template>
        <template #rodape>
            <button v-if="!formPerimetro.preload && !formPerimetro.save" class="btn btn-sm mr-1 btn-success" type="button"
                    @click="salvarPerimetro">
                <span v-if="formPerimetro.editando">Alterar</span>
                <span v-else>Cadastrar</span>
            </button>
        </template>
    </modal>

    <!--Janela de Associar Perimetro-->
    <modal id="janelaAssociarPerimetro" titulo="Associar perímetros" :fechar="!formPerimetroFuncionarios.preload"
           @fechou="resetFuncionariosSelecionados">
        <template #conteudo>
            <h4 class="text-success text-center"
                v-if="!formPerimetroFuncionarios.preload && formPerimetroFuncionarios.update">
                <i class="fas fa-check fa-2x"></i><br>
                Perímetro
                <span v-if="formPerimetroFuncionarios.perimetro_id > 0">associado a</span>
                <span v-if="formPerimetroFuncionarios.perimetro_id === 0">removido</span>
                @{{ formPerimetroFuncionarios.funcionariosSelecionados.length }} colaborador(es).
            </h4>
            <p class="text-center">
                <preload v-if="formPerimetroFuncionarios.preload" label="Aguarde..."></preload>
            </p>
            <div v-if="!formPerimetroFuncionarios.preload && !formPerimetroFuncionarios.update">
                <h4 v-if="listaPerimetros.length === 0" class="text-center">
                    <i class="fas fa-map-marked-alt fa-2x"></i><br>
                    Nenhum perímetro cadastrado
                </h4>
                <h5 v-if="listaPerimetros.length > 0" class="text-danger">
                    <i class="fas fa-users fa-2x"></i> selecionado(s) @{{
                    formPerimetroFuncionarios.funcionariosSelecionados.length }} coladorador(es)
                </h5>
                <div class="form-group">
                    <h4>Selecione um ou mais perímetros para associar:</h4>
                    <div class="custom-control custom-switch mt-2" v-for="(p,index) in listaTodosPerimetros"
                         :key="index">
                        <input type="checkbox" v-model="p['selecionado']" :value="p.id" :id="index"
                               class="custom-control-input" @click="selecionarPerimetro(p)">
                        <label :for="index" class="custom-control-label">@{{ p.descricao }}</label>
                    </div>
                </div>
            </div>
        </template>
        <template #rodape>
            <button :disabled="listaPerimetros.length === 0"
                    v-if="!formPerimetroFuncionarios.preload && !formPerimetroFuncionarios.update"
                    class="btn btn-sm mr-1 btn-success" type="button" @click="assosicarPerimetros">
                <i class="fas fa-link"></i> Aplicar
            </button>
        </template>
    </modal>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs mt-5" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#config_frequencia" role="tab"
                       aria-controls="home" aria-selected="true">Controle de ponto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#perimetros" role="tab"
                       aria-controls="profile" aria-selected="false">Perimetros</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active p-2 " id="config_frequencia" role="tabpanel"
                     aria-labelledby="config_frequencia-tab">
                    <div class="row">
                        <preload v-if="preload"></preload>
                        <div class="col-12 col-md-4" v-else>
                            <div class="form-group">
                                <label>Regime de compensação de horários</label>
                                <select :disabled="preloadConfig" class="form-control"
                                        v-model="formConfig.tipo_frequencia">
                                    <option value="hora_extra">Horas extras</option>
                                    <option value="banco_horas">Banco de horas</option>
                                    <option value="hibrido">Híbrido</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tempo de tolerância para entrada e saída</label>
                                <input :disabled="preloadConfig" type="number" class="form-control"
                                       placeholder="Em minutos" v-mascara:numero value="15"
                                       v-model="formConfig.limite_tolerancia" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Tempo de tolerância para considerar hora extra ou banco de
                                    horas</small>
                            </div>
                            <div class="form-group">
                                <label>Tempo limite para falta</label>
                                <input :disabled="preloadConfig" type="number" class="form-control"
                                       placeholder="Em minutos" v-mascara:numero value="60"
                                       v-model="formConfig.tempo_limite_falta" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Tempo máximo em minutos até ser considerado falta do
                                    colaborador</small>
                            </div>
                            <div class="form-group">
                                <label>Tempo limite para saída</label>
                                <input :disabled="preloadConfig" type="number" class="form-control"
                                       placeholder="Em minutos" v-mascara:numero value="60"
                                       v-model="formConfig.tempo_limite_saida" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Tempo em minutos após o fim da jornada que deve ser verificado
                                    pelo gestor.</small>
                            </div>
                            <div class="form-group">
                                <label>Início de nova frequência</label>
                                <input :disabled="preloadConfig" type="number" max="25" min="1" class="form-control"
                                       placeholder="Dia do mês" v-mascara:numero value="1"
                                       v-model="formConfig.dia_nova_frequencia" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Dia do mês em que incia uma nova ficha de frequência</small>
                            </div>
                            <button v-if="config_empresa" type="button" class="btn btn-success btn-sm"
                                    :disabled="preloadConfig" @click="salvarConfiguracoes">
                                <preload v-if="preloadConfig" label="Salvar"></preload>
                                <span v-else>Salvar</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade p-2" id="perimetros" role="tabpanel" aria-labelledby="perimetros-tab">
                    <h4 v-show="!paginacaoPerimetros.carregando && listaPerimetros.length===0" class="text-center mt-3">
                        Sem perimetros cadastrados</h4>
                    <h4 v-show="!paginacaoPerimetros.carregando && listaPerimetros.length > 0" class="mt-3"> Perímetros
                        cadastrados </h4>
                    <p class="text-right" v-if="perimetros_insert">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#janelaFormPerimetro" @click="formNovoPerimetro">
                            <i class="fas fa-map-marker-alt"></i> Adicionar perímetro
                        </button>
                    </p>
                    <form @submit.prevent="atualizarListaPeriemetros">
                        <div class="form-row align-items-center">
                            <div class="col-sm-3 my-1">
                                <label class="sr-only">Buscar</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" placeholder="Descrição"
                                           v-model="paginacaoPerimetros.dados.campoBusca"
                                           @keyup="paginacaoPerimetros.dados.campoBusca===''? atualizarListaPeriemetros():false">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <preload v-if="paginacaoPerimetros.carregando"></preload>
                    <table class="tabela"
                           v-if="!paginacaoPerimetros.carregando && listaPerimetros.length > 0">
                        <thead>
                        <tr class="bg-default">
                            <th>Descrição</th>
                            <th>Editar</th>
                            <th>Excluir</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="pointer" v-for="peri in listaPerimetros">
                            <td data-label="descrição">@{{peri.descricao}}</td>
                            <td data-label="editar">
                                <a v-if="perimetros_update" href="javascript://" data-toggle="modal"
                                   data-target="#janelaFormPerimetro" class="btn btn-sm mr-1 btn-success"
                                   @click="formEditarPerimetro(peri)"><i aria-hidden="true" class="fa fa-edit"></i>
                                    Editar
                                </a>
                            </td>
                            <td data-label="excluir">
                                <a v-if="perimetros_delete" href="javascript://" data-toggle="modal"
                                   data-target="#janelaConfirmar" class="btn btn-sm mr-1 btn-danger"
                                   @click="formApagarPerimetro(peri.id)"><i aria-hidden="true" class="fa fa-trash"></i>
                                    Excluir
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <controle-paginacao class="d-flex justify-content-center" ref="paginacaoPerimetros"
                                        url="{{route('g.controle-ponto.perimetros.atualizarPerimetros')}}"
                                        por-pagina="10"
                                        :dados="paginacaoPerimetros.dados"
                                        v-on:carregou="carregouPerimetros"
                                        v-on:carregando="carregandoPerimetros"></controle-paginacao>


                    <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length===0"
                        class="text-center mt-3"> Sem colaboradores cadastrados</h4>
                    <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length > 0" class="mt-3">
                        Associar perímetro aos colaboradores</h4>
                    <form @submit.prevent="atualizarListaFuncionarios">
                        <div class="form-row align-items-center">
                            <div class="col-sm-3 my-1">
                                <label class="sr-only">Buscar</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" placeholder="Nome colaborador"
                                           v-model="paginacaoFuncionarios.dados.campoBusca"
                                           @keyup="paginacaoFuncionarios.dados.campoBusca===''? atualizarListaFuncionarios():false">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mb-2">
                                <button v-if="perimetros_funcionarios" type="button" class="btn btn-secondary"
                                        :disabled="formPerimetroFuncionarios.funcionariosSelecionados.length===0"
                                        data-toggle="modal" data-target="#janelaAssociarPerimetro"
                                        @click="formAssociarPerimetro">
                                    <i class="fas fa-link"></i> Associar perímetro
                                </button>
                            </div>
                        </div>
                    </form>
                    <preload v-if="paginacaoFuncionarios.carregando"></preload>
                    <table class="tabela"
                           v-if="!paginacaoFuncionarios.carregando && listaFuncionarios.length > 0">
                        <thead>
                        <tr class="bg-default">
                            <th>
                                <div class="form-check" v-if="perimetros_funcionarios">
                                    <input type="checkbox" class="form-check-input"
                                           v-model="todosFuncionariosSelecionados"
                                           @change="selecionarTodosFuncionarios">
                                    <label class="form-check-label" style="visibility: hidden"></label>
                                </div>
                            </th>
                            <th>Nome</th>
                            <th>Perímetro</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="pointer" v-for="funcionario in listaFuncionarios"
                            @click="selecionarFuncionario(funcionario)">
                            <td data-label="id" class="text-center" width="10%">
                                <div class="form-check" v-if="perimetros_funcionarios">
                                    <input type="checkbox" :value="funcionario.id" class="form-check-input"
                                           v-model="formPerimetroFuncionarios.funcionariosSelecionados">
                                    <label class="form-check-label" style="visibility: hidden"></label>
                                </div>
                            </td>
                            <td data-label="nome">@{{funcionario.nome}}</td>
                            <td data-label="perimetro">
                                <span class="badge badge-secondary ml-1 p-1"
                                      v-if="funcionario.perimetros_funcionario.length"
                                      v-for="perimetros in funcionario.perimetros_funcionario">
                                    @{{perimetros.descricao}}
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <controle-paginacao class="d-flex justify-content-center" ref="paginacaoFuncionarios"
                                        url="{{route('g.controle-ponto.configuracoes.atualizarFuncionarios')}}"
                                        por-pagina="10"
                                        :dados="paginacaoFuncionarios.dados"
                                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <!-- Adicionar Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Script para autocomplete -->
    <script>
        // Variáveis globais para o autocomplete
        let searchTimeout = null;
        let searchResults = [];

        // Função para inicializar o autocomplete nos campos de busca de endereço
        function initAddressAutocomplete() {
            // Seleciona todos os campos com a classe enderecoGoogle
            const searchInputs = document.querySelectorAll('.enderecoGoogle');

            searchInputs.forEach(input => {
                // Adiciona o evento de input ao campo
                input.addEventListener('input', function () {
                    const query = this.value.trim();

                    // Limpa o timeout anterior para evitar múltiplas requisições
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }

                    // Fecha o dropdown de resultados se o campo estiver vazio
                    if (!query) {
                        closeSearchResults();
                        return;
                    }

                    // Define um timeout para não fazer requisições a cada tecla digitada
                    searchTimeout = setTimeout(() => {
                        fetchAddressSuggestions(query, this);
                    }, 300);
                });

                // Impede que a tecla Enter submeta o formulário
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();

                        // Se houver resultados, seleciona o primeiro
                        const resultsContainer = document.querySelector('.search-results');
                        if (resultsContainer && resultsContainer.style.display !== 'none' && searchResults.length > 0) {
                            selectSearchResult(searchResults[0], this);
                        }
                    }
                });
            });

            // Fecha o dropdown quando clicar fora dele
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.search-results') && !e.target.closest('.enderecoGoogle')) {
                    closeSearchResults();
                }
            });
        }

        // Função para buscar sugestões de endereços usando a API Nominatim
        function fetchAddressSuggestions(query, inputElement) {
            // URL da API Nominatim para busca de endereços
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;

            // Faz a requisição
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    searchResults = data;
                    displaySearchResults(data, inputElement);
                })
                .catch(error => {
                    console.error('Erro ao buscar sugestões de endereço:', error);
                });
        }

        // Função para exibir os resultados da busca
        function displaySearchResults(results, inputElement) {
            // Fecha qualquer resultado anterior
            closeSearchResults();

            if (results.length === 0) {
                return;
            }

            // Encontra o container de resultados dentro do elemento pai
            let resultsContainer = inputElement.closest('.endereco-search-container').querySelector('.search-results');

            // Limpa o conteúdo atual
            resultsContainer.innerHTML = '';

            // Preenche o container com os resultados
            results.forEach((result, index) => {
                const resultElement = document.createElement('div');
                resultElement.className = 'search-result-item';
                resultElement.innerHTML = result.display_name;
                resultElement.addEventListener('click', () => {
                    selectSearchResult(result, inputElement);
                });
                resultsContainer.appendChild(resultElement);
            });

            // Exibe o container
            resultsContainer.style.display = 'block';
        }

        // Função para selecionar um resultado
        function selectSearchResult(result, inputElement) {
            // Atualiza o valor do campo de busca
            inputElement.value = result.display_name;

            // Obtém a referência para a instância Vue
            const vueInstance = window.vueInstance;

            if (vueInstance && vueInstance.map) {
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                // Atualiza o mapa e o perímetro
                vueInstance.map.setView([lat, lng], 19);
                if (vueInstance.perimetro) {
                    vueInstance.perimetro.setLatLng([lat, lng]);
                }

                // Atualiza os valores no formulário
                vueInstance.formPerimetro.lat = lat;
                vueInstance.formPerimetro.long = lng;

                // Atualiza o handler de redimensionamento
                if (typeof vueInstance.updateResizeHandlerPosition === 'function') {
                    vueInstance.updateResizeHandlerPosition();
                }
            }

            // Fecha o dropdown de resultados
            closeSearchResults();
        }

        // Função para fechar o dropdown de resultados
        function closeSearchResults() {
            const resultsContainers = document.querySelectorAll('.search-results');
            resultsContainers.forEach(container => {
                container.style.display = 'none';
                container.innerHTML = '';
            });
        }

        // Expõe a função para uso global
        window.closeSearchResults = closeSearchResults;
        window.initAddressAutocomplete = initAddressAutocomplete;

        // Evento para quando o modal for aberto
        $(document).ready(function () {
            $('#janelaFormPerimetro').on('shown.bs.modal', function (e) {
                // Quando o modal estiver completamente aberto,
                // isso ajuda a garantir que o mapa seja inicializado corretamente
                if (window.vueInstance && typeof window.vueInstance.ensureMapInitialized === 'function') {
                    setTimeout(() => {
                        window.vueInstance.ensureMapInitialized();
                    }, 300);
                }
            });
        });
    </script>

    <script src="{{mix('js/g/controle-ponto/configuracoes/app.js')}}"></script>
@endpush

@push('css')
    <!-- Adicionar CSS do Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <style type="text/css">
        /* Estilos para o mapa Leaflet */
        #mapaPrimetro {
            height: 500px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Estilos para o autocomplete */
        .endereco-search-container {
            position: relative;
            margin-bottom: 15px;
        }

        .search-results {
            position: absolute;
            max-height: 200px;
            overflow-y: auto;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            width: 100%;
            margin-top: 2px;
            display: none;
        }

        .search-result-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-result-item:hover {
            background-color: #f5f5f5;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .pointer {
            cursor: pointer;
        }

        /* Estilos para o manipulador de redimensionamento */
        .resize-handler {
            width: 16px !important;
            height: 16px !important;
            margin-left: -8px !important;
            margin-top: -8px !important;
            background-color: white;
            border: 2px solid #ff0000;
            border-radius: 50%;
            cursor: nwse-resize !important;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.4);
            transition: transform 0.1s;
        }

        .resize-handler:hover {
            transform: scale(1.2);
            background-color: #f0f0f0;
        }

        /* Estilos para o círculo do Leaflet */
        .leaflet-interactive {
            cursor: grab;
        }

        /* Estilos para os controles do perímetro */
        .perimeter-controls {
            margin-top: 10px;
        }
    </style>
@endpush
