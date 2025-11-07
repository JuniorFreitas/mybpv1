@extends('layouts.sistema')
@section('title', 'Relatório de Avaliação 90 Dias')
@section('content_header', 'Relatório de Avaliação 90 Dias')
@section('content')
    <!-- Loader -->
    <div id="pageLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Carregando...</span>
            </div>
            <div class="mt-3">
                <h5>Carregando relatório...</h5>
            </div>
        </div>
    </div>

    <div class="py-3" id="mainContent" style="display: none;">
        @php($temPermissaoGestaoRh = in_array('privilegio_gestao_rh', auth()->user()->listaDeHabilidades()))
        @php($ehGestorGlobal = false)
        @foreach($vencimentos as $v)
            @php($ehGestorGlobal = $ehGestorGlobal || (auth()->id() === ($v['gestor_id'] ?? null)))
        @endforeach
        <input type="hidden" id="currentUserId" value="{{ auth()->id() }}">
        <input type="hidden" id="userCanGestaoRh" value="{{ $temPermissaoGestaoRh ? 1 : 0 }}">
        <input type="hidden" id="isGestorGlobal" value="{{ $ehGestorGlobal ? 1 : 0 }}">
    <input type="hidden" id="avaliacao90BaseUrl" value="{{ url('g/relatorios/avaliacao-90-dias') }}">

        <!-- Toggle Cards de Resumo -->
        <div class="mb-3">
            <button class="btn btn-sm btn-outline-secondary" id="toggleResumo" onclick="toggleCardsResumo()">
                <i class="fas fa-eye-slash" id="iconToggleResumo"></i>
                <span id="textToggleResumo">Ocultar Resumo</span>
            </button>
        </div>

        <!-- Cards de Resumo -->
        <div id="cardsResumo">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total de Avaliações
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Vencidas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['vencidos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Vence Hoje
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['vence_hoje'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    A Vencer
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['a_vencer'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda linha de cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Sem Avaliação
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['sem_avaliacao'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Com Uma Avaliação
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['uma_avaliacao'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Completas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['completas'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-double fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terceira linha de cards (Gestores) -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-dark shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Gestores Envolvidos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['gestores_unicos'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-dark shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Sem Gestor
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumo['sem_gestor'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div><!-- Fim #cardsResumo -->

        <!-- @if(($topGestores ?? collect())->count() > 0) -->
        <!-- Top 5 Gestores com Mais Pendências -->
        <!-- <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold" style="color: #003755;">
                    Top 5 Gestores com Mais Pendências
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Gestor</th>
                                <th class="text-center">Total</th>
                                <th class="text-center text-danger">Vencidas</th>
                                <th class="text-center text-warning">Vence Hoje</th>
                                <th class="text-center text-info">A Vencer</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topGestores as $g)
                                <tr>
                                    <td>
                                        {{ $g['gestor_nome'] }}
                                        @if(!empty($g['gestor_login']))
                                            <small class="text-muted d-block">{{ $g['gestor_login'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center"><strong>{{ $g['total'] }}</strong></td>
                                    <td class="text-center text-danger">{{ $g['vencidos'] }}</td>
                                    <td class="text-center text-warning">{{ $g['vence_hoje'] }}</td>
                                    <td class="text-center text-info">{{ $g['a_vencer'] }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" data-gestor-id="{{ $g['gestor_id'] }}" onclick="setFiltroGestor(this)">
                                            <i class="fas fa-filter"></i> Filtrar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div> -->
        <!-- @endif -->

        <!-- Filtros -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold" style="color: #003755;">Filtros</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtroStatus">Status</label>
                            <select class="form-control form-control-sm" id="filtroStatus">
                                <option value="">Todos</option>
                                <option value="VENCIDO">Vencido</option>
                                <option value="VENCE HOJE">Vence Hoje</option>
                                <option value="A VENCER">A Vencer</option>
                                <option value="COMPLETA">Completa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroNome">Colaborador</label>
                            <input type="text" class="form-control form-control-sm" id="filtroNome"
                                   placeholder="Digite o nome..."
                            >
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroCentroCusto">Centro de Custo</label>
                            <select class="form-control form-control-sm" id="filtroCentroCusto">
                                <option value="">Todos</option>
                                <option value="__SEM_CENTRO__">Sem Centro de Custo</option>
                                @foreach($centrosCusto as $cc)
                                    <option value="{{ $cc }}">{{ $cc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtroGestor">Gestor</label>
                            <select class="form-control form-control-sm" id="filtroGestor">
                                <option value="">Todos</option>
                                <option value="__SEM_GESTOR__">Sem Gestor</option>
                                @foreach($gestores as $gestor)
                                    <option value="{{ $gestor['id'] }}">{{ $gestor['nome'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtroAvaliacoes">Avaliações</label>
                            <select class="form-control form-control-sm" id="filtroAvaliacoes">
                                <option value="">Todas</option>
                                <option value="0">Sem Avaliação</option>
                                <option value="1">Uma Avaliação</option>
                                <option value="2">Duas Avaliações</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroCargo">Cargo</label>
                            <select class="form-control form-control-sm" id="filtroCargo">
                                <option value="">Todos</option>
                                @foreach($cargos as $cargo)
                                    <option value="{{ $cargo }}">{{ $cargo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtroFuncao">Função</label>
                            <select class="form-control form-control-sm" id="filtroFuncao">
                                <option value="">Todos</option>
                                @foreach($funcoes as $funcao)
                                    <option value="{{ $funcao }}">{{ $funcao }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button class="btn btn-block btn-sm" style="background-color: #003755; color: white;"
                                    onclick="limparFiltros()"
                            >
                                <i class="fas fa-eraser"></i> Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Avaliações -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold" style="color: #003755;">
                    Avaliações Pendentes
                    <small class="text-muted">(Gerado em: {{ $dataGeracao }})</small>
                </h6>
                <div>
                    <!-- <button class="btn btn-sm" style="background-color: #003755; color: white;" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </button> -->
                </div>
            </div>
            <div class="card-body">
                <!-- Informações Adicionais -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informações:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Os links de avaliação são gerados automaticamente e têm validade de 60 dias.</li>
                        <li>Cada colaborador pode realizar no máximo 2 avaliações de 90 dias.</li>
                        <li>Links expirados ou já utilizados não podem ser reutilizados.</li>
                        <li>Você pode copiar os links individualmente ou todos de uma vez para compartilhar com os gestores.
                        </li>
                    </ul>
                </div>

                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Exibindo <span id="infoExibindo">0</span> de <span id="infoTotal">0</span> registros</span>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($temPermissaoGestaoRh || $ehGestorGlobal)
                            <button id="btnGerarLinksTodos" class="btn btn-sm btn-outline-primary mr-2" onclick="gerarLinksTodosEmLote()">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span class="label">Gerar todos os links</span>
                            </button>
                        @endif
                        <label class="mb-0 mr-2">Itens por página:</label>
                        <select id="itensPorPagina" class="form-control form-control-sm" style="width: auto; display: inline-block;">
                            <option value="40">40</option>
                            <option value="80">80</option>
                            <option value="100">100</option>
                            <option value="999999">Todos</option>
                        </select>
                    </div>
                </div>

                <!-- Loader da tabela -->
                <div id="tabelaLoader" style="display: none; position: relative; min-height: 200px;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Filtrando...</span>
                        </div>
                        <div class="mt-2 text-muted">Processando filtros...</div>
                    </div>
                </div>

                <div class="table-responsive" id="tabelaContainer">
                    <table class="table table-bordered table-hover" id="tabelaAvaliacoes" style="width: 100%;">
                        <thead style="background-color: #003755; color: white;">
                        <tr>
                            <th>Status</th>
                            <th>Colaborador</th>
                            <th>Gestor</th>
                            <th>Cargo</th>
                            <th>Função</th>
                            <th>Centro de Custo</th>
                            <th>Vencimento</th>
                            <th>Dias</th>
                            <th>Avaliações</th>
                            <th>Link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($vencimentos as $vencimento)
                            <tr data-status="{{ $vencimento['status'] }}"
                                data-nome="{{ strtolower($vencimento['colaborador']) }}"
                                data-avaliacoes="{{ $vencimento['qnt_avaliacoes'] }}"
                                data-gestor="{{ $vencimento['gestor_id'] ?? '' }}"
                                data-centro-custo="{{ $vencimento['centro_custo'] ?? '' }}"
                                data-cargo="{{ $vencimento['cargo'] ?? '' }}"
                                data-funcao="{{ $vencimento['funcao'] ?? '' }}"
                            >
                                <td>
                                    @if($vencimento['status'] == 'VENCIDO')
                                        <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $vencimento['status'] }}
                                            </span>
                                    @elseif($vencimento['status'] == 'VENCE HOJE')
                                        <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> {{ $vencimento['status'] }}
                                            </span>
                                    @elseif($vencimento['status'] == 'COMPLETA')
                                        <span class="badge badge-success">
                                                <i class="fas fa-check"></i> {{ $vencimento['status'] }}
                                            </span>
                                    @else
                                        <span class="badge badge-info">
                                                <i class="fas fa-calendar-alt"></i> {{ $vencimento['status'] }}
                                            </span>
                                    @endif
                                </td>
                                <td>{{ $vencimento['colaborador'] }}</td>
                                <td>
                                    @if(!empty($vencimento['gestor_nome']))
                                        {{ $vencimento['gestor_nome'] }}
                                        @if(!empty($vencimento['gestor_login']))
                                            <small class="text-muted d-block">{{ $vencimento['gestor_login'] }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $vencimento['cargo'] ?? '-' }}</td>
                                <td>{{ $vencimento['funcao'] ?? '-' }}</td>
                                <td>{{ $vencimento['centro_custo'] ?? '-' }}</td>
                                <td>{{ $vencimento['prazo_vencido'] }}</td>
                                <td class="text-center">
                                    @if($vencimento['status'] == 'A VENCER')
                                        <span class="badge badge-info">{{ $vencimento['dias_para_vencer'] }} dias</span>
                                    @elseif($vencimento['status'] == 'VENCE HOJE')
                                        <span class="badge badge-warning">Hoje</span>
                                    @elseif($vencimento['status'] == 'COMPLETA')
                                        <span class="badge badge-secondary">-</span>
                                    @else
                                        <span class="badge badge-danger"
                                        >{{ $vencimento['dias_atraso'] }} dias atrás</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($vencimento['qnt_avaliacoes'] == 0)
                                        <span class="badge badge-secondary">
                                                <i class="fas fa-times"></i> Nenhuma
                                            </span>
                                    @elseif($vencimento['qnt_avaliacoes'] == 1)
                                        <span class="badge badge-primary">
                                                <i class="fas fa-check"></i> 1 Avaliação
                                            </span>
                                    @else
                                        <span class="badge badge-success">
                                                <i class="fas fa-check-double"></i> {{ $vencimento['qnt_avaliacoes'] }} Avaliações
                                            </span>
                                    @endif
                                </td>
                                <td class="text-center" id="celula-link-{{ $vencimento['feedback_id'] ?? 'x' }}">
                                    @php($ehGestor = auth()->id() === ($vencimento['gestor_id'] ?? null))
                                    @if(empty($vencimento['link_avaliacao']))
                                        @if($ehGestor || $temPermissaoGestaoRh)
                        <button class="btn btn-sm btn-outline-primary"
                            data-feedback-id="{{ $vencimento['feedback_id'] ?? '' }}"
                            onclick="gerarLinkAvaliacao(this)"
                            title="Gerar link de avaliação"
                        >
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                <span class="label">Gerar link</span>
                                            </button>
                                        @else
                                            <span class="text-muted">Restrito ao gestor</span>
                                        @endif
                                    @else
                                        <a href="{{ $vencimento['link_avaliacao'] }}"
                                           target="_blank"
                                           class="btn btn-sm btn-success ml-1"
                                           title="Abrir avaliação em nova aba"
                                        >
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">Restrito ao gestor</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div id="paginacaoContainer" class="d-flex justify-content-center mt-3">
                    <nav>
                        <ul class="pagination" id="paginacao"></ul>
                    </nav>
                </div>
            </div>
        </div>


    </div><!-- Fim #mainContent -->

@stop

@push('css')
    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }

        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }

        .border-left-secondary {
            border-left: 0.25rem solid #858796 !important;
        }

        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 55, 85, 0.05);
        }
    </style>
@endpush

@push('js')
    <script>
        // Mostra loader ao iniciar
        document.getElementById('pageLoader').style.display = 'flex';

        const app = new Vue({
            el: "#app",
            data: {
                check: false
            }
        });
        // Variáveis de paginação
        let paginaAtual = 1;
        let itensPorPagina = 40;

        $(document).ready(function() {
            const uidEl = document.getElementById("currentUserId");
            window.currentUserId = uidEl ? parseInt(uidEl.value) : null;
            const canEl = document.getElementById("userCanGestaoRh");
            window.userCanGestaoRh = canEl ? parseInt(canEl.value) === 1 : false;
            const gestEl = document.getElementById("isGestorGlobal");
            window.isGestorGlobal = gestEl ? parseInt(gestEl.value) === 1 : false;

            // Filtros
            $("#filtroStatus, #filtroNome, #filtroAvaliacoes, #filtroCentroCusto, #filtroGestor, #filtroCargo, #filtroFuncao").on("change keyup", function() {
                paginaAtual = 1; // Reset para primeira página ao filtrar
                filtrarTabela();
            });

            // Mudança de itens por página
            $("#itensPorPagina").on("change", function() {
                itensPorPagina = parseInt($(this).val());
                paginaAtual = 1;
                filtrarTabela();
            });

            // Inicializa a tabela
            filtrarTabela();

            // Oculta loader e mostra conteúdo após tudo carregar
            setTimeout(function() {
                $('#pageLoader').fadeOut(300, function() {
                    $('#mainContent').fadeIn(300);
                });
            }, 100);
        });

        function filtrarTabela() {
            // Mostra loader e oculta tabela
            $("#tabelaLoader").show();
            $("#tabelaContainer").css('opacity', '0.3');

            // Aguarda um momento para o loader aparecer
            setTimeout(function() {
                const status = $("#filtroStatus").val().toUpperCase();
                const nome = $("#filtroNome").val().toLowerCase();
                const avaliacoes = $("#filtroAvaliacoes").val();
                const centroCusto = $("#filtroCentroCusto").val();
                const gestor = $("#filtroGestor").val();
                const cargo = $("#filtroCargo").val();
                const funcao = $("#filtroFuncao").val();

                // Primeiro, filtra os registros
                const linhasFiltradas = [];

            $("#tabelaAvaliacoes tbody tr").each(function() {
                const row = $(this);
                const rowStatus = row.data("status");
                const rowNome = row.data("nome");
                const rowAvaliacoes = row.data("avaliacoes").toString();
                const rowCentroCusto = row.data("centro-custo");
                const rowGestor = row.data("gestor").toString();
                const rowCargo = row.data("cargo");
                const rowFuncao = row.data("funcao");

                let mostrar = true;

                if (status && rowStatus !== status) {
                    mostrar = false;
                }

                if (nome && rowNome.indexOf(nome) === -1) {
                    mostrar = false;
                }

                if (avaliacoes && rowAvaliacoes !== avaliacoes) {
                    mostrar = false;
                }

                if (centroCusto) {
                    if (centroCusto === '__SEM_CENTRO__') {
                        const hasCc = (rowCentroCusto || '').toString().trim() !== '';
                        if (hasCc) {
                            mostrar = false;
                        }
                    } else if (rowCentroCusto !== centroCusto) {
                        mostrar = false;
                    }
                }

                if (gestor) {
                    if (gestor === '__SEM_GESTOR__') {
                        const hasGestor = (rowGestor || '').toString().trim() !== '';
                        if (hasGestor) {
                            mostrar = false;
                        }
                    } else if (rowGestor !== gestor) {
                        mostrar = false;
                    }
                }

                if (cargo && rowCargo !== cargo) {
                    mostrar = false;
                }

                if (funcao && rowFuncao !== funcao) {
                    mostrar = false;
                }

                if (mostrar) {
                    linhasFiltradas.push(row);
                }
            });

            // Calcula paginação
            const totalRegistros = linhasFiltradas.length;
            const totalPaginas = Math.ceil(totalRegistros / itensPorPagina);

            // Ajusta página atual se necessário
            if (paginaAtual > totalPaginas && totalPaginas > 0) {
                paginaAtual = totalPaginas;
            }
            if (paginaAtual < 1) {
                paginaAtual = 1;
            }

            const inicio = (paginaAtual - 1) * itensPorPagina;
            const fim = inicio + itensPorPagina;

            // Esconde todas as linhas
            $("#tabelaAvaliacoes tbody tr").hide();

            // Mostra apenas as linhas da página atual
            linhasFiltradas.forEach(function(row, index) {
                if (index >= inicio && index < fim) {
                    row.show();
                }
            });

            // Atualiza info de registros
            const exibindo = Math.min(totalRegistros, fim) - inicio;
            $("#infoExibindo").text(exibindo);
            $("#infoTotal").text(totalRegistros);

            // Renderiza paginação
            renderizarPaginacao(totalPaginas);

            // Oculta loader e mostra tabela
            $("#tabelaLoader").hide();
            $("#tabelaContainer").css('opacity', '1');
            }, 50); // Fecha setTimeout
        }

        function renderizarPaginacao(totalPaginas) {
            const paginacaoHtml = [];

            if (totalPaginas <= 1) {
                $("#paginacao").html('');
                $("#paginacaoContainer").hide();
                return;
            }

            $("#paginacaoContainer").show();

            // Botão Anterior
            paginacaoHtml.push(`
                <li class="page-item ${paginaAtual === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="mudarPagina(${paginaAtual - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `);

            // Páginas
            const maxBotoes = 5;
            let inicioPaginas = Math.max(1, paginaAtual - Math.floor(maxBotoes / 2));
            let fimPaginas = Math.min(totalPaginas, inicioPaginas + maxBotoes - 1);

            if (fimPaginas - inicioPaginas < maxBotoes - 1) {
                inicioPaginas = Math.max(1, fimPaginas - maxBotoes + 1);
            }

            if (inicioPaginas > 1) {
                paginacaoHtml.push(`
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="mudarPagina(1); return false;">1</a>
                    </li>
                `);
                if (inicioPaginas > 2) {
                    paginacaoHtml.push(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }

            for (let i = inicioPaginas; i <= fimPaginas; i++) {
                paginacaoHtml.push(`
                    <li class="page-item ${i === paginaAtual ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="mudarPagina(${i}); return false;">${i}</a>
                    </li>
                `);
            }

            if (fimPaginas < totalPaginas) {
                if (fimPaginas < totalPaginas - 1) {
                    paginacaoHtml.push(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
                paginacaoHtml.push(`
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="mudarPagina(${totalPaginas}); return false;">${totalPaginas}</a>
                    </li>
                `);
            }

            // Botão Próximo
            paginacaoHtml.push(`
                <li class="page-item ${paginaAtual === totalPaginas ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="mudarPagina(${paginaAtual + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `);

            $("#paginacao").html(paginacaoHtml.join(''));
        }

        function mudarPagina(novaPagina) {
            paginaAtual = novaPagina;
            filtrarTabela();

            // Scroll suave para o topo da tabela
            $('html, body').animate({
                scrollTop: $("#tabelaAvaliacoes").offset().top - 100
            }, 300);
        }

        // ----- Geração em lote para todas as linhas elegíveis -----
        function gerarLinksTodosEmLote() {
            try {
                if (!(window.userCanGestaoRh || window.isGestorGlobal)) {
                    toastr.warning('Você não tem permissão para gerar links em lote.');
                    return;
                }

                const $btnHeader = $('#btnGerarLinksTodos');
                const $spin = $btnHeader.find('.spinner-border');
                const $label = $btnHeader.find('.label');

                // Coleta IDs elegíveis em TODAS as linhas (não só visíveis)
                const feedbackIds = [];
                $('#tabelaAvaliacoes tbody tr').each(function() {
                    const $tr = $(this);
                    const gestorId = ($tr.data('gestor') || '').toString();

                    // Restrição por perfil: RH pega todos; gestor pega apenas os seus
                    if (!window.userCanGestaoRh) {
                        if (!window.currentUserId || gestorId !== window.currentUserId.toString()) {
                            return; // pula linhas que não são do gestor logado
                        }
                    }

                    // Se já existe link, não precisa gerar
                    if ($tr.find('td:last a[href]')[0]) return;

                    const $btn = $tr.find('td:last button.btn-outline-primary[data-feedback-id]').first();
                    if ($btn.length) {
                        const fid = parseInt($btn.data('feedback-id'), 10);
                        if (fid && !isNaN(fid)) {
                            feedbackIds.push(fid);
                        }
                    }
                });

                if (feedbackIds.length === 0) {
                    toastr.info('Nenhuma linha requer geração de link.');
                    return;
                }

                // UI header
                $btnHeader.prop('disabled', true);
                $spin.removeClass('d-none');
                $label.text('Processando...');

                const baseUrl = $('#avaliacao90BaseUrl').val() || '/g/relatorios/avaliacao-90-dias';
                const urlLote = baseUrl.replace(/\/$/, '') + '/gerar-links-lote';

                $.ajax({
                    url: urlLote,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    contentType: 'application/json',
                    data: JSON.stringify({ feedback_ids: feedbackIds }),
                    success: function(resp) {
                        $btnHeader.prop('disabled', false);
                        $spin.addClass('d-none');
                        $label.text('Gerar todos os links');
                        const total = resp.total || feedbackIds.length;
                        toastr.info(
                            total + ' link(s) enfileirado(s) para geração em segundo plano.<br><strong>Atualize a página em alguns minutos para ver os links.</strong>',
                            'Processando em background',
                            {
                                timeOut: 8000,
                                extendedTimeOut: 3000,
                                closeButton: true,
                                progressBar: true
                            }
                        );
                    },
                    error: function(xhr) {
                        $btnHeader.prop('disabled', false);
                        $spin.addClass('d-none');
                        $label.text('Gerar todos os links');
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao processar lote';
                        toastr.error(msg);
                    }
                });
            } catch (e) {
                console.warn('gerarLinksTodosEmLote error', e);
            }
        }

        function limparFiltros() {
            $("#filtroStatus").val("");
            $("#filtroNome").val("");
            $("#filtroAvaliacoes").val("");
            $("#filtroCentroCusto").val("");
            $("#filtroGestor").val("");
            $("#filtroCargo").val("");
            $("#filtroFuncao").val("");
            filtrarTabela();
        }

        function setFiltroGestor(el) {
            try {
                var id = el && el.dataset ? el.dataset.gestorId : '';
                if (!id) return;
                $("#filtroGestor").val(id).trigger('change');
                if ($.isFunction($.fn.animate)) {
                    $('html, body').animate({ scrollTop: $('#tabelaAvaliacoes').offset().top - 80 }, 300);
                }
            } catch (e) { console.warn('setFiltroGestor error', e) }
        }

        function copiarLink(btn) {
            if (!window.userCanGestaoRh) {
                toastr.warning("Você não tem permissão para copiar links (RH Gestão).");
                return;
            }
            const link = btn.dataset.link;
            // Cria um elemento temporário para copiar
            const temp = document.createElement("textarea");
            temp.value = link;
            temp.style.position = "fixed";
            temp.style.opacity = "0";
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            document.body.removeChild(temp);

            // Feedback visual
            const originalHtml = btn.innerHTML;
            btn.innerHTML = "<i class=\"fas fa-check\"></i> Copiado!";
            btn.classList.remove("btn-primary");
            btn.classList.add("btn-success");

            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove("btn-success");
                btn.style.backgroundColor = "#003755";
                btn.style.color = "white";
            }, 2000);

            // Notificação
            toastr.success("Link copiado para a área de transferência!");
        }

        function copiarTodosLinks() {
            if (!(window.userCanGestaoRh || window.isGestorGlobal)) {
                toastr.warning("Você não tem permissão para copiar todos os links.");
                return;
            }
            const links = [];
            $("#tabelaAvaliacoes tbody tr:visible").each(function() {
                const gestorId = $(this).data("gestor");
                if (!gestorId || parseInt(gestorId) !== parseInt(window.currentUserId)) {
                    return; // pula linhas que não pertencem ao gestor logado
                }
                const btn = $(this).find("button[data-link]");
                const url = btn.data("link");
                if (url) {
                    const nome = $(this).find("td:eq(1)").text();
                    links.push(`${nome}: ${url}`);
                }
            });

            if (links.length === 0) {
                toastr.warning("Nenhum link disponível para copiar! (Apenas do seu centro de custo)");
                return;
            }

            const texto = links.join("\n");
            const temp = document.createElement("textarea");
            temp.value = texto;
            temp.style.position = "fixed";
            temp.style.opacity = "0";
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            document.body.removeChild(temp);

            toastr.success(`${links.length} links copiados para a área de transferência!`);
        }

        function exportarExcel() {
            // Implementar exportação Excel se necessário
            toastr.info("Funcionalidade de exportação em desenvolvimento...");
        }

        function toggleCardsResumo() {
            const cardsResumo = $("#cardsResumo");
            const icon = $("#iconToggleResumo");
            const text = $("#textToggleResumo");
            const btn = $("#toggleResumo");

            if (cardsResumo.is(":visible")) {
                // Ocultar
                cardsResumo.slideUp(300);
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
                text.text("Mostrar Resumo");
                btn.removeClass("btn-outline-secondary").addClass("btn-outline-primary");
                localStorage.setItem('avaliacao90dias_resumo_oculto', 'true');
            } else {
                // Mostrar
                cardsResumo.slideDown(300);
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
                text.text("Ocultar Resumo");
                btn.removeClass("btn-outline-primary").addClass("btn-outline-secondary");
                localStorage.setItem('avaliacao90dias_resumo_oculto', 'false');
            }
        }

        // Restaura estado ao carregar (executa antes de mostrar conteúdo)
        $(document).ready(function() {
            const resumoOculto = localStorage.getItem('avaliacao90dias_resumo_oculto') === 'true';
            if (resumoOculto) {
                $("#cardsResumo").hide();
                $("#iconToggleResumo").removeClass("fa-eye-slash").addClass("fa-eye");
                $("#textToggleResumo").text("Mostrar Resumo");
                $("#toggleResumo").removeClass("btn-outline-secondary").addClass("btn-outline-primary");
            }
        });

        // Fallback: garante que conteúdo seja exibido mesmo se houver erro
        window.addEventListener('load', function() {
            setTimeout(function() {
                if ($('#pageLoader').is(':visible')) {
                    $('#pageLoader').fadeOut(300);
                    $('#mainContent').fadeIn(300);
                }
            }, 3000);
        });

        // ----- Geração assíncrona de link de avaliação -----
        function gerarLinkAvaliacao(btnEl) {
            try {
                const $btn = $(btnEl);
                const feedbackIdRaw = $btn.data('feedback-id');
                const feedbackId = parseInt(feedbackIdRaw, 10);
                if (!feedbackId || isNaN(feedbackId)) {
                    toastr.error('ID de feedback não encontrado.');
                    return;
                }

                // URLs preferenciais geradas pelo Blade via route()
                const urlPostAttr = $btn.data('url-post');
                const urlGetAttr = $btn.data('url-get');
                const baseUrl = $('#avaliacao90BaseUrl').val() || '/g/relatorios/avaliacao-90-dias';
                const urlPostCandidates = [
                    urlPostAttr || '',
                    baseUrl.replace(/\/$/, '') + '/' + feedbackId + '/gerar-link',
                    '/g/relatorios/avaliacao-90-dias/' + feedbackId + '/gerar-link',
                    '/g/avaliacao-90-dias/' + feedbackId + '/gerar-link'
                ].filter(Boolean);

                const $spinner = $btn.find('.spinner-border');
                const $label = $btn.find('.label');
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $label.text('Gerando...');

                tentativaPost(0);

                function tentativaPost(idx) {
                    if (idx >= urlPostCandidates.length) {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                        $label.text('Gerar link');
                        toastr.error('Não foi possível enfileirar a geração do link.');
                        return;
                    }
                    const urlPost = urlPostCandidates[idx];
                    $.ajax({
                        url: urlPost,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function() {
                            // inicia polling
                            consultarLinkComTentativas(feedbackId, 10, 1000, function(link) {
                                if (link) {
                                    substituirCelulaPorLink(feedbackId, link);
                                    toastr.success('Link gerado com sucesso.');
                                } else {
                                    $btn.prop('disabled', false);
                                    $spinner.addClass('d-none');
                                    $label.text('Gerar link');
                                    toastr.info('Geração enfileirada. O link deve aparecer em instantes.');
                                }
                            });
                        },
                        error: function(xhr) {
                            if (xhr && xhr.status === 404) {
                                // tenta próxima URL candidata
                                tentativaPost(idx + 1);
                                return;
                            }
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                            $label.text('Gerar link');
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao enfileirar geração do link';
                            toastr.error(msg);
                        }
                    });
                }
            } catch (e) {
                console.warn('gerarLinkAvaliacao error', e);
            }
        }

    function consultarLinkComTentativas(feedbackId, tentativas, intervaloMs, callback) {
            const baseUrl = $('#avaliacao90BaseUrl').val() || '/g/relatorios/avaliacao-90-dias';
            // Se houver botão ainda presente, aproveita url-get do atributo
            const $btn = $('#celula-link-' + feedbackId + ' button[data-url-get]');
            const urlGetAttr = $btn.length ? $btn.data('url-get') : null;
            const urlGetCandidates = [
                urlGetAttr || '',
                baseUrl.replace(/\/$/, '') + '/' + feedbackId + '/link',
                '/g/relatorios/avaliacao-90-dias/' + feedbackId + '/link',
                '/g/avaliacao-90-dias/' + feedbackId + '/link'
            ].filter(Boolean);
            let count = 0;
            const timer = setInterval(function() {
                const idx = Math.min(count, urlGetCandidates.length - 1);
                const urlGet = urlGetCandidates[idx];
                $.get(urlGet)
                    .done(function(resp) {
                        const link = resp && resp.link ? resp.link : null;
                        if (link) {
                            clearInterval(timer);
                            callback(link);
                        } else if (++count >= tentativas) {
                            clearInterval(timer);
                            callback(null);
                        }
                    })
                    .fail(function() {
                        if (++count >= tentativas) {
                            clearInterval(timer);
                            callback(null);
                        }
                    });
            }, intervaloMs);
        }

        function substituirCelulaPorLink(feedbackId, link) {
            const $celula = $('#celula-link-' + feedbackId);
            if ($celula.length === 0) return;
            const html = [
                '<a href="' + link + '" target="_blank" class="btn btn-sm btn-success ml-1" title="Abrir avaliação em nova aba">',
                '    <i class="fas fa-external-link-alt"></i>',
                '</a>'
            ].join('');
            $celula.html(html);
        }
    </script>
@endpush
