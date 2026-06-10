@extends('layouts.sistema')
@section('content_header', ' Grupos de Usuários')
@section('breadcrumb')
    <li class="breadcrumb-item active">Configurações - Grupos de Usuários</li>
@endsection
@section('content')
    <!-- Modal formulario -->
    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="94" centralizada
           label-fechar="Cancelar" @fechou="limparEstadoModal">
        <template #conteudo>
            <div v-if="preloadAjax" class="text-center py-5 text-muted">
                <i class="fa fa-spinner fa-pulse fa-2x mb-3 d-block"></i>
                <span class="d-block">@{{ mensagemOverlayModal }}</span>
            </div>
            <form v-else id="form" v-show="!cadastrado && !atualizado">
                <p class="text-muted small mb-3 border-bottom pb-2" v-if="mostrarDicasFluxoNovo">
                    <strong class="text-dark">Passo 1:</strong> nome e situação do grupo.
                    <span class="mx-1">→</span>
                    <strong class="text-dark">Passo 2:</strong> marque as permissões (habilidades) deste grupo.
                </p>
                <p class="text-muted small mb-3 border-bottom pb-2" v-else-if="editando">
                    Ajuste os dados, as permissões e consulte quem usa este grupo. Para <strong>associar ou trocar usuários</strong>, use o cadastro de usuários (campo do grupo).
                </p>
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="nav-item">
                        <a href="#abaIdentificacao" class="nav-link active" id="aba-identificacao-tab" aria-controls="home" role="tab"
                           data-toggle="tab">
                            <i class="fa fa-id-card mr-1"></i>Dados do grupo
                        </a>
                    </li>
                    <li role="presentation" class="nav-item">
                        <a href="#abaHabilidades" class="nav-link" aria-controls="profile" role="tab"
                           data-toggle="tab">
                            <i class="fa fa-key mr-1"></i>Permissões
                            <span class="badge badge-secondary ml-1" v-if="habilidadesSelecionadas">@{{ habilidadesSelecionadas }}</span>
                        </a>
                    </li>
                    <li role="presentation" class="nav-item" v-if="editando">
                        <a href="#abaUsuarios" class="nav-link" aria-controls="usuarios" role="tab"
                           data-toggle="tab">
                            <i class="fa fa-users mr-1"></i>Quem usa este grupo
                            <span class="badge badge-info ml-1" v-if="listaUsuarios.length">@{{ listaUsuarios.length }}</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="abaIdentificacao">
                        <div class="col-12 py-3">
                            <div class="form-group">
                                <label class="font-weight-bold" for="papel_nome">Nome do grupo <span class="text-danger">*</span></label>
                                <input id="papel_nome" type="text" class="form-control" v-model="form.nome"
                                       placeholder="Ex.: Equipe de Recrutamento"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                <small class="form-text text-muted">Nome exibido nas listas e ao escolher o grupo para um usuário.</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold" for="papel_desc">Descrição <span class="text-danger">*</span></label>
                                <input id="papel_desc" type="text" class="form-control" v-model="form.descricao"
                                       placeholder="Resumo do que este grupo pode fazer no sistema"
                                       autocomplete="off" onblur="valida_campo_vazio(this,3)">
                                <small class="form-text text-muted">Ajuda outros administradores a entenderem a finalidade do grupo.</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="d-block font-weight-bold">Situação</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="papel_ativo" v-model="form.ativo">
                                    <label class="custom-control-label" for="papel_ativo">
                                        Grupo <strong>ativo</strong> (usuários deste grupo podem acessar conforme as permissões abaixo)
                                    </label>
                                </div>
                                <small class="form-text text-muted d-block mt-1">Desative para bloquear o uso deste perfil sem apagar o cadastro.</small>
                            </div>

                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane p-1" id="abaHabilidades">
                        <p class="small text-muted mb-2 px-1">
                            As permissões ficam <strong>agrupadas por categoria</strong> (trecho do nome antes de <code>_</code>).
                            Use busca e/ou categoria para reduzir a lista; os botões <strong>Permitir todas</strong> e <strong>Remover todas</strong>
                            respeitam esse recorte quando algum filtro estiver ativo.
                        </p>
                        <!-- Filtros para habilidades -->
                        <div class="row mb-2 py-2 align-items-end">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted mb-1 d-block">Buscar</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           v-model="filtroHabilidades"
                                           @input="filtrarHabilidades"
                                           placeholder="Nome ou descrição…"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="small font-weight-bold text-muted mb-1 d-block">Categoria</label>
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
                            <div class="col-md-4 text-md-right">
                                <span class="badge badge-info">@{{ habilidadesFiltradas.length }} na lista</span>
                                <span v-if="habilidadesFiltroAtivo" class="badge badge-warning text-dark ml-1">filtro ativo</span>
                                <span class="badge badge-success ml-1">@{{ habilidadesSelecionadas }}/@{{ listaDeHabilidades.length }} no grupo</span>
                                <span v-if="habilidadesFiltroAtivo" class="badge badge-light border ml-1" title="Permitidas só entre as linhas visíveis">
                                    @{{ habilidadesPermitidasNoFiltro }}/@{{ habilidadesFiltradas.length }} visíveis
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between bg-light border rounded px-3 py-2 mb-3">
                            <div class="small text-muted mb-2 mb-md-0 pr-md-3" style="max-width: 42rem;">
                                <template v-if="habilidadesFiltroAtivo">
                                    <strong class="text-dark">Escopo: resultado do filtro</strong>
                                    — as ações abaixo alteram só as <strong>@{{ habilidadesFiltradas.length }}</strong> permissões que aparecem na lista agora
                                    (demais itens do grupo não mudam).
                                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline ml-1" @click.prevent="limparFiltroHabilidades()">Limpar filtros</button>
                                </template>
                                <template v-else>
                                    <strong class="text-dark">Escopo: grupo inteiro</strong>
                                    — sem busca nem categoria, as ações abaixo aplicam às <strong>@{{ listaDeHabilidades.length }}</strong> permissões do cadastro.
                                </template>
                            </div>
                            <div class="btn-group btn-group-sm flex-shrink-0">
                                <button type="button"
                                        @click.prevent="marcarTodasNoEscopoAtual()"
                                        class="btn btn-success"
                                        :disabled="habilidadesFiltradas.length === 0">
                                    <i class="fa fa-check-double mr-1"></i>Permitir todas
                                </button>
                                <button type="button"
                                        @click.prevent="desmarcarTodasNoEscopoAtual()"
                                        class="btn btn-outline-danger"
                                        :disabled="habilidadesFiltradas.length === 0">
                                    <i class="fa fa-times mr-1"></i>Remover todas
                                </button>
                            </div>
                        </div>

                        <div v-if="habilidadesFiltradas.length === 0" class="text-center text-muted border rounded py-5 bg-white">
                            <i class="fa fa-search fa-2x mb-3 d-block"></i>
                            <p class="mb-0 font-weight-bold">Nenhuma permissão neste filtro</p>
                            <small>Ajuste a busca ou a categoria.</small>
                        </div>

                        <div v-for="grupo in habilidadesAgrupadasPorCategoria" :key="grupo.categoria" class="card mb-2 shadow-sm border">
                            <div class="card-header py-2 bg-white border-bottom-0">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="fa fa-folder-open text-primary mr-2"></i>@{{ grupo.categoria }}
                                </h6>
                                <small class="text-muted">
                                    @{{ contarPermitidasNoGrupo(grupo.itens) }} de @{{ grupo.itens.length }} permitidas nesta seção
                                </small>
                            </div>
                            <div class="table-responsive border-top">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th class="border-0" style="width: 38%;">Permissão</th>
                                        <th class="border-0">Descrição</th>
                                        <th class="border-0 text-center" style="min-width: 160px;">Situação</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="habilidade in grupo.itens" :key="habilidade.id" class="align-middle">
                                        <td class="py-2">
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-shield-alt text-primary mr-2 mt-1"></i>
                                                <span class="text-dark small"><span class="text-body">@{{ habilidade.nome }}</span></span>
                                            </div>
                                        </td>
                                        <td class="py-2">
                                            <span class="text-muted small">@{{ habilidade.descricao }}</span>
                                        </td>
                                        <td class="py-2 text-center">
                                            <template v-if="ehHabilidadeObrigatoriaAlterarSenha(habilidade)">
                                                <button type="button"
                                                        class="btn btn-sm btn-success px-2"
                                                        disabled
                                                        title="Todos os grupos devem manter esta permissão; não é possível desmarcar.">
                                                    <i class="fa fa-lock mr-1" aria-hidden="true"></i>Permitida (obrigatória)
                                                </button>
                                            </template>
                                            <template v-else>
                                                <button type="button"
                                                        class="btn btn-sm btn-success px-2"
                                                        @click.prevent="definirAcessoHabilidade(habilidade, false)"
                                                        v-if="habilidade.acesso"
                                                        title="Remover esta permissão">
                                                    <i class="fa fa-check mr-1" aria-hidden="true"></i>Permitida
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary px-2"
                                                        @click.prevent="definirAcessoHabilidade(habilidade, true)"
                                                        v-if="!habilidade.acesso"
                                                        title="Conceder esta permissão">
                                                    <i class="fa fa-minus mr-1 text-muted" aria-hidden="true"></i>Não permitida
                                                </button>
                                            </template>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Aba: quem usa este grupo (somente leitura + orientação) -->
                    <div role="tabpanel" class="tab-pane p-1" id="abaUsuarios">
                        <div class="alert alert-info border-info mb-3">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                <div class="mb-2 mb-md-0 pr-md-2">
                                    <strong><i class="fa fa-info-circle mr-1"></i>Como vincular usuários a este grupo</strong>
                                    <p class="small mb-0 mt-1">
                                        O vínculo é feito no <strong>cadastro de usuários</strong>: edite o usuário e escolha este grupo no campo de perfil.
                                        Esta aba só mostra quem já está usando o grupo hoje.
                                    </p>
                                </div>
                                @can('usuario_usuarios')
                                    <a href="{{ route('g.usuarios.usuarios.index') }}" class="btn btn-sm btn-primary text-nowrap">
                                        <i class="fa fa-user-cog mr-1"></i>Ir para usuários
                                    </a>
                                @endcan
                            </div>
                        </div>
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

                        <template v-if="usuariosFiltrados.length">
                            <div v-if="usuariosAgrupadosPorStatus.ativos.length" class="mb-4">
                                <h6 class="d-flex align-items-center text-success mb-2 pb-2 border-bottom">
                                    <i class="fa fa-user-check mr-2"></i>
                                    Usuários ativos
                                    <span class="badge badge-success ml-2">@{{ usuariosAgrupadosPorStatus.ativos.length }}</span>
                                </h6>
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th class="text-center">Último acesso</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="usuario in usuariosAgrupadosPorStatus.ativos" :key="'a-'+usuario.id" class="align-middle">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-user-circle text-primary mr-2"></i>
                                                    <strong class="text-dark small">@{{ usuario.nome }}</strong>
                                                </div>
                                            </td>
                                            <td><span class="text-muted small">@{{ usuario.email }}</span></td>
                                            <td class="text-center">
                                                <small class="text-muted">@{{ formatarData(usuario.ultimo_acesso) }}</small>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div v-if="usuariosAgrupadosPorStatus.inativos.length">
                                <h6 class="d-flex align-items-center text-secondary mb-2 pb-2 border-bottom">
                                    <i class="fa fa-user-slash mr-2"></i>
                                    Usuários inativos
                                    <span class="badge badge-secondary ml-2">@{{ usuariosAgrupadosPorStatus.inativos.length }}</span>
                                </h6>
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th class="text-center">Último acesso</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="usuario in usuariosAgrupadosPorStatus.inativos" :key="'i-'+usuario.id" class="align-middle">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-user-circle text-muted mr-2"></i>
                                                    <strong class="text-dark small">@{{ usuario.nome }}</strong>
                                                </div>
                                            </td>
                                            <td><span class="text-muted small">@{{ usuario.email }}</span></td>
                                            <td class="text-center">
                                                <small class="text-muted">@{{ formatarData(usuario.ultimo_acesso) }}</small>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        <div v-else class="text-center text-muted border rounded py-5 bg-white">
                            <i class="fa fa-users fa-2x mb-3 d-block"></i>
                            <p class="mb-1 font-weight-bold">Ninguém neste grupo ainda</p>
                            <p class="small mb-0">Quando usuários forem associados a este perfil, eles aparecerão aqui, separados por situação.</p>
                        </div>
                    </div>
                </div>
            </form>
        </template>
        <template #rodape>
            <div v-show="!cadastrado && !atualizado" class="d-flex flex-wrap align-items-center justify-content-between w-100">
                <small class="text-muted mb-2 mb-md-0" v-if="!cadastrado && !atualizado">
                    <span v-if="editando">Alterações só são gravadas ao clicar em <strong>Salvar alterações</strong>.</span>
                    <span v-else>Preencha as abas e clique em <strong>Criar grupo</strong> para concluir.</span>
                </small>
                <div class="d-flex flex-wrap align-items-center">
                    <button type="button" class="btn btn-primary ml-md-2 mb-1 mb-md-0"
                            v-show="editando && !atualizado"
                            :disabled="preloadAjax"
                            @click="alterar()">
                        <i class="fa fa-save mr-1" v-if="!preloadAjax"></i>
                        <i class="fa fa-spinner fa-pulse mr-1" v-else></i>
                        Salvar alterações
                    </button>
                    <button type="button" class="btn btn-primary ml-md-2 mb-1 mb-md-0"
                            v-show="!editando && !cadastrado"
                            :disabled="preloadAjax"
                            @click="cadastrar()">
                        <i class="fa fa-check mr-1" v-if="!preloadAjax"></i>
                        <i class="fa fa-spinner fa-pulse mr-1" v-else></i>
                        Criar grupo
                    </button>
                </div>
            </div>
        </template>

    </modal>

    <!-- Modal confirmar -->
    <modal ref="janelaConfirmar" id="janelaConfirmar" titulo="Apagar papel">
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
                <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend>Busca na lista</legend>
        <div class="row align-items-end">
            <div class="col-md-5">
                <form id="formBusca" @submit.prevent="$refs.componente.buscar()">
                    <div class="form-group mb-md-0">
                        <label for="campoBusca" class="font-weight-bold small">Nome do grupo</label>
                        <div class="input-group input-group-sm">
                        <span class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></span>
                        </span>
                            <input type="text" id="campoBusca" v-model="controle.dados.campoBusca" placeholder="Digite para filtrar…" autocomplete="off"
                                   class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-7 text-md-right mt-2 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-1" @click.prevent="atualizar()" title="Recarregar a lista">
                    <i class="fa fa-sync-alt mr-1"></i>Atualizar lista
                </button>
                @can('configuracao_papel_insert')
                    <button type="button" class="btn btn-sm btn-primary" id="btnFormCadastrar"
                            @click="formNovo()">
                        <i class="fa fa-plus-circle mr-1"></i>Novo grupo
                    </button>
                @endcan
            </div>
        </div>
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
                    <th class="text-center">Usuários no grupo</th>
                    <th class="text-center">Situação</th>
                    <th class="text-center" style="min-width: 140px;">Ações</th>
                </tr>
                </thead>

                <tbody>

                <tr v-for="papel in lista">
                    {{--<td>@{{ab.id}}</td>--}}
                    <td data-label="Nome" class="text-center">@{{papel.nome}}</td>
                    <td data-label="Descrição" class="text-center">@{{papel.descricao}}</td>
                    <td data-label="Qnt Usuários" class="text-center">@{{papel.usuariosVinculados}}</td>
                    <td class="text-center" v-if="papel.master !== true">
                        <bt-ativo :rota="`papeis/${papel.id}/ativa-desativa`" :model="papel"></bt-ativo>
                    </td>
                    <td v-else class="text-center text-muted small">—</td>
                    <td class="text-center" v-if="papel.master !== true">
                        @can('configuracao_papel_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btnFormAlterar"
                                    @click.prevent="formAlterar(papel.id)"
                                    title="Alterar nome, permissões e ver quem usa este grupo">
                                <i class="fa fa-edit mr-1"></i>Editar
                            </button>
                        @endcan
                    </td>
                    <td v-else class="text-center text-muted small">Perfil do sistema</td>
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
