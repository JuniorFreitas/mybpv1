@extends('layouts.sistema')
@section('content_header', ' Grupos de Usuários')
@section('breadcrumb')
    <li class="breadcrumb-item active">Configurações - Grupos de Usuários</li>
@endsection
@section('content')
    <!-- Modal formulario -->
    <modal id="janelaCadastrar" :titulo="tituloJanela" size="g">
        <template #conteudo>
            <span v-show="preloadAjax">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
            </span>
            {{--            <div class="alert alert-success alert-dismissible" v-show="cadastrado">--}}
            {{--                <h4>--}}
            {{--                    <i class="icon fa fa-check"></i>--}}
            {{--                    Papel cadastrado com sucesso!--}}
            {{--                </h4>--}}
            {{--            </div>--}}
            {{--            <div class="alert alert-success alert-dismissible" v-show="atualizado">--}}
            {{--                <h4>--}}
            {{--                    <i class="icon fa fa-check"></i>--}}
            {{--                    Papel alterado com sucesso!--}}
            {{--                </h4>--}}
            {{--            </div>--}}
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="nav-item">
                        <a href="#abaIdentificacao" class="nav-link active" id="aba-identificacao-tab" aria-controls="home" role="tab"
                           data-toggle="tab">Identificação</a>
                    </li>
                    <li role="presentation">
                        <a href="#abaHabilidades" class="nav-link" aria-controls="profile" role="tab"
                           data-toggle="tab">Habilidades</a>
                    </li>
                    <li role="presentation" v-if="editando">
                        <a href="#abaUsuarios" class="nav-link" aria-controls="usuarios" role="tab"
                           data-toggle="tab">Usuários Vinculados</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="abaIdentificacao">
                        <div class="col-12 py-3">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.nome"
                                       placeholder="Nome do grupo"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                            </div>

                            <div class="form-group">
                                <label>Descrição</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.descricao"
                                       placeholder="Descrição do papel"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                            </div>

                            <div class="form-group">
                                <label>Ativo</label>
                                <select v-model="form.ativo" class="form-control form-control-sm">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane p-1" id="abaHabilidades">
                        <!-- Filtros para habilidades -->
                        <div class="row mb-3 py-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           v-model="filtroHabilidades" 
                                           @input="filtrarHabilidades"
                                           placeholder="Buscar habilidades..." 
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select v-model="categoriaFiltro" 
                                        @change="filtrarHabilidades" 
                                        class="form-control form-control-sm">
                                    <option value="">Todas as categorias</option>
                                    <option v-for="categoria in categoriasHabilidades" 
                                            :key="categoria" 
                                            :value="categoria">
                                        @{{ categoria }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-info">
                                    @{{ habilidadesFiltradas.length }} de @{{ listaDeHabilidades.length }} habilidades
                                </span>
                                <span class="badge badge-success ml-2">
                                    @{{ habilidadesSelecionadas }} selecionadas
                                </span>
                            </div>
                        </div>
                        
                        <!-- Botões de seleção rápida por categoria -->
                        <div class="row mb-3" v-if="categoriasHabilidades.length > 0">
                            <div class="col-12">
                                <div class="card border-light">
                                    <div class="card-body py-2">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <span class="text-muted small font-weight-bold mr-3 mb-2 mb-md-0">
                                                <i class="fa fa-magic mr-1"></i>Seleção Rápida:
                                            </span>
                                            <div class="d-flex flex-wrap" style="gap: 4px;">
                                                <button type="button" 
                                                        v-for="categoria in categoriasHabilidades" 
                                                        :key="categoria"
                                                        @click.prevent="selecionarPorCategoria(categoria)"
                                                        class="btn btn-sm btn-outline-primary"
                                                        style="margin-bottom: 4px; white-space: nowrap;">
                                                    <i class="fa fa-check mr-1"></i>@{{ categoria }}
                                                </button>
                                                <div class="dropdown-divider mx-2 my-1" style="height: 20px; border-left: 1px solid #dee2e6;"></div>
                                                <button type="button" 
                                                        @click.prevent="selecionarTodas()" 
                                                        class="btn btn-sm btn-success"
                                                        style="margin-bottom: 4px; white-space: nowrap;">
                                                    <i class="fa fa-check-double mr-1"></i>Todas
                                                </button>
                                                <button type="button" 
                                                        @click.prevent="desmarcarTodas()" 
                                                        class="btn btn-sm btn-danger"
                                                        style="margin-bottom: 4px; white-space: nowrap;">
                                                    <i class="fa fa-times mr-1"></i>Nenhuma
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                <tr>
                                    <th class="py-3">
                                        <i class="fa fa-key mr-2"></i>Nome da Habilidade
                                    </th>
                                    <th class="py-3">
                                        <i class="fa fa-info-circle mr-2"></i>Descrição
                                    </th>
                                    <th class="py-3 text-center" style="width: 120px;">
                                        <a class="btn btn-sm btn-outline-success" href="javascript://"
                                           @click.prevent="selecionarTodas()" v-if="!todasHabilidades"
                                           title="Selecionar todas as habilidades">
                                            <span class="fa fa-check" aria-hidden="true"></span> Todas
                                        </a>
                                        <a class="btn btn-sm btn-outline-danger" href="javascript://"
                                           @click.prevent="selecionarTodas()" v-if="todasHabilidades"
                                           title="Desmarcar todas as habilidades">
                                            <span class="fa fa-times" aria-hidden="true"></span> Todas
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="habilidade in habilidadesPaginadas" :key="habilidade.id" class="align-middle">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-shield-alt text-primary mr-2"></i>
                                            <strong class="text-dark">@{{habilidade.nome}}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted small">@{{habilidade.descricao}}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-success" 
                                                    @click.prevent="habilidade.acesso=!habilidade.acesso"
                                                    v-if="habilidade.acesso"
                                                    title="Clique para desmarcar">
                                                <span class="fa fa-check" aria-hidden="true"></span>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary" 
                                                    @click.prevent="habilidade.acesso=!habilidade.acesso"
                                                    v-if="!habilidade.acesso"
                                                    title="Clique para marcar">
                                                <span class="fa fa-times" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="habilidadesFiltradas.length === 0">
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <div class="py-4">
                                            <i class="fa fa-search fa-2x mb-3"></i>
                                            <p class="mb-0">Nenhuma habilidade encontrada</p>
                                            <small>Tente ajustar o filtro de busca</small>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Controles de paginação para habilidades -->
                        <div class="row mt-3 py-3" v-if="totalPaginasHabilidades > 1">
                            <div class="col-md-6">
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaHabilidades = 1"
                                            :disabled="paginaHabilidades === 1">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaHabilidades--"
                                            :disabled="paginaHabilidades === 1">
                                        <i class="fa fa-angle-left"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaHabilidades++"
                                            :disabled="paginaHabilidades === totalPaginasHabilidades">
                                        <i class="fa fa-angle-right"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaHabilidades = totalPaginasHabilidades"
                                            :disabled="paginaHabilidades === totalPaginasHabilidades">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="text-muted">
                                    Página @{{ paginaHabilidades }} de @{{ totalPaginasHabilidades }}
                                    (@{{ habilidadesFiltradas.length }} habilidades)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Aba de Usuários Vinculados -->
                    <div role="tabpanel" class="tab-pane p-1" id="abaUsuarios">
                        <!-- Filtros para usuários -->
                        <div class="row mb-3 py-3">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           v-model="filtroUsuarios" 
                                           @input="filtrarUsuarios"
                                           placeholder="Buscar usuários..." 
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="badge badge-info">
                                    @{{ usuariosFiltrados.length }} usuários vinculados
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-light">
                                <tr>
                                    <th class="py-3">
                                        <i class="fa fa-user mr-2"></i>Nome
                                    </th>
                                    <th class="py-3">
                                        <i class="fa fa-envelope mr-2"></i>Email
                                    </th>
                                    <th class="py-3 text-center">
                                        <i class="fa fa-toggle-on mr-2"></i>Status
                                    </th>
                                    <th class="py-3 text-center">
                                        <i class="fa fa-calendar mr-2"></i>Último Acesso
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="usuario in usuariosPaginados" :key="usuario.id" class="align-middle">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-user-circle text-primary mr-2"></i>
                                            <strong class="text-dark">@{{usuario.nome}}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted">@{{usuario.email}}</span>
                                    </td>
                                   
                                    <td class="py-3 text-center">
                                        <span v-if="usuario.ativo" class="badge badge-success">
                                            <i class="fa fa-check mr-1"></i>Ativo
                                        </span>
                                        <span v-else class="badge badge-danger">
                                            <i class="fa fa-times mr-1"></i>Inativo
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <small class="text-muted">
                                            @{{usuario.ultimo_acesso ? usuario.ultimo_acesso : 'Nunca'}}
                                        </small>
                                    </td>
                                </tr>
                                <tr v-if="usuariosFiltrados.length === 0">
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <div class="py-4">
                                            <i class="fa fa-users fa-2x mb-3"></i>
                                            <p class="mb-0">Nenhum usuário vinculado a este papel</p>
                                            <small>Os usuários aparecerão aqui quando forem associados a este papel</small>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Controles de paginação para usuários -->
                        <div class="row mt-3 py-3" v-if="totalPaginasUsuarios > 1">
                            <div class="col-md-6">
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaUsuarios = 1"
                                            :disabled="paginaUsuarios === 1">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaUsuarios--"
                                            :disabled="paginaUsuarios === 1">
                                        <i class="fa fa-angle-left"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaUsuarios++"
                                            :disabled="paginaUsuarios === totalPaginasUsuarios">
                                        <i class="fa fa-angle-right"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            @click="paginaUsuarios = totalPaginasUsuarios"
                                            :disabled="paginaUsuarios === totalPaginasUsuarios">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="text-muted">
                                    Página @{{ paginaUsuarios }} de @{{ totalPaginasUsuarios }}
                                    (@{{ usuariosFiltrados.length }} usuários)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </template>
        <template #rodape>
            <div v-show="!preloadAjax">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado"
                        @click="alterar()">Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado"
                        @click="cadastrar()">Cadastrar
                </button>
            </div>
        </template>

    </modal>

    <!-- Modal confirmar -->
    <modal id="janelaConfirmar" titulo="Apagar papel">
        <template #conteudo>
            <span v-show="preloadAjax">
               <preload></preload>
            </span>

            <div class="alert alert-success alert-dismissible" v-show="apagado">

                <h4>
                    <i class="icon fa fa-check"></i>
                    Papel apagado com sucesso!
                </h4>

            </div>

            <h4 v-show="!apagado && !preloadAjax">
                Tem certeza que deseja apagar este papel?
            </h4>
        </template>
        <template #rodape>
            <div v-show="!preloadAjax">
                <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend>Filtragem por</legend>
        <div class="row">
            <div class="col-md-4 column">
                <form id="formBusca" @submit.prevent="$refs.componente.buscar()">
                    <div class="form-group">
                        <label>Buscar:</label>
                        <div class="input-group input-group-sm">
                        <span class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></span>
                        </span>
                            <input type="text" id="campoBusca" v-model="controle.dados.campoBusca" placeholder="Nome do papel" autocomplete="off"
                                   class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <button type="button" class="btn btn-sm btn-success" @click.prevent="atualizar()">Atualizar</button>
        @can('configuracao_papel_insert')
            <button type="button" class="btn btn-sm btn-primary" id="btnFormCadastrar" data-toggle="modal"
                    data-target="#janelaCadastrar" @click="formNovo()">Cadastrar
            </button>
        @endcan
    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <preload></preload>
    </p>

    <div id="conteudo">
        <h4 v-show="!controle.carregando && lista.length==0"></h4>
        <div class="table-responsive">

            <table class="tabela" v-if="!controle.carregando && lista.length > 0">
                <thead>
                <tr class="bg-default">
                    {{--<th>Cód.</th>--}}
                    <th class="text-center">Nome</th>
                    <th class="text-center">Descrição</th>
                    <th class="text-center">Qnt Usuários</th>
                    <th class="text-center">Status</th>
                    <th>Ações</th>
                </tr>
                </thead>

                <tbody>

                <tr v-for="papel in lista">
                    {{--<td>@{{ab.id}}</td>--}}
                    <td data-label="Nome" class="text-center">@{{papel.nome}}</td>
                    <td data-label="Descrição" class="text-center">@{{papel.descricao}}</td>
                    <td data-label="Qnt Usuários" class="text-center">@{{papel.usuariosVinculados}}</td>
                    <td data-label="Status" class="text-center" v-if="papel.master !== true">
                        <bt-ativo :rota="`papeis/${papel.id}/ativa-desativa`" :model="papel"></bt-ativo>
                    </td>
                    <td v-else></td>
                    <td class="text-center" v-if="papel.master !== true">
                        @can('configuracao_papel_update')
                            <a class="btn btn-sm btn-success btnFormAlterar" href="javascript://"
                               @click.prevent="formAlterar(papel.id)" data-toggle="modal"
                               data-target="#janelaCadastrar">
                                <i class="fa fa-edit"></i>
                            </a>
                        @endcan

                    </td>
                    <td v-else></td>
                </tr>

                </tbody>
            </table>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.configuracoes.papeis.atualizar')}}" por-pagina="10"
                            :dados="controle.dados" v-on:carregou="carregou" v-on:carregando="carregando">

        </controle-paginacao>
    </div>
@stop

@push('js')
    <script src="{{mix('js/g/papeis/app.js')}}"></script>
@endpush
