@extends('layouts.sistema')
@section('title', 'Curriculo')
@section('content_header')
    <h4 class="text-default">SELECIONADOS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
        <template #conteudo>
            <preload v-show="preloadAjax"></preload>
            <div v-if="!preloadAjax && (!cadastrado && !atualizado) && form.id !== ''">
                <fieldset>
                    <legend>Dados Pessoais</legend>
                    <p>
                        Nome: @{{form.curriculo.nome}} | Nascimento: @{{form.curriculo.nascimento}} |
                        Idade: @{{form.curriculo.idade}} anos<br>
                        Contato: @{{form.tel_principal ? form.tel_principal.numero: 'não informado'}} | E-mail:
                        @{{ form.curriculo.email }} <br/>
                        Vaga: @{{form.vaga_aberta.vaga_selecionada ? form.vaga_aberta.vaga_selecionada.nome +' - '+
                        form.vaga_aberta.municipio.nome +' - '+ form.vaga_aberta.municipio.uf : null}}
                        | PCD: @{{form.curriculo.pcd ? 'Sim' : 'Não'}}
                        <br>
                        Endereco: @{{form.curriculo.endereco_completo}} <br>
                        Escolaridade: @{{form.curriculo.formacao.tipo}} <span
                            v-show="form.formacao_curso">(@{{form.formacao_curso}})</span><br>
                        <br>

                    </p>

                    <fieldset v-if="form.vinculo">
                        <legend>Vínculo</legend>
                        <p>
                            Têm parentes trabalhando no Grupo Equatorial (consanguíneos, por afinidade ou relação
                            íntima)? <strong>@{{ form.vinculo.parente ? 'Sim' : 'Não' }}</strong><br>

                            <span v-if="form.vinculo.parente">
                                Nome: <strong>@{{ form.vinculo.nome }}</strong><br>
                                Função: <strong>@{{ form.vinculo.funcao }}</strong><br>
                                Qual o grau de parentesco com esta pessoa: <strong>@{{ form.vinculo.grau_parentesco }}</strong><br>
                            </span>

                            Já foi empregado no Grupo Equatorial ou empresas parceiras: <strong>@{{
                                form.vinculo.foi_empregado ? 'Sim' : 'Não' }}</strong><br>
                            <span v-if="form.vinculo.foi_empregado">
                                Já foi empregado no Grupo Equatorial ou empresas parceiras? <strong>@{{ form.vinculo.local_empregado }}</strong><br>

                                <span v-if="form.vinculo.local_empregado === 'Outras empresas parceiras'">
                                    Nome da empresa parceira: <strong>@{{ form.vinculo.local_empregado }}</strong><br>
                                </span>
                            </span>
                        </p>
                    </fieldset>

                </fieldset>

                <fieldset v-for="prova in form.simulados">
                    <legend>Teste: @{{ prova.simulado_vaga.simulado.titulo }} - (@{{ prova.simulado_vaga.online ?
                        'Online' : 'Presencial' }})
                    </legend>
                    <div v-show="prova.finalizado">
                        <p>Acertos: @{{ prova.acertos }} | | Tempo restante: @{{
                            prova.duracao_segundos }} min | | Finalizado em @{{ prova.data_finalizacao }}h</p>
                        <div v-show="!prova.status">
                            <classificar :usuario_id="{{intval(auth()->id())}}" :model="form" :simulado_id="prova.id"
                                         @salvou="atualizaClassificacao"></classificar>
                        </div>
                        <div class="text-uppercase alert"
                             :class="prova.status === 'desclassificado' ? 'alert-danger' : 'alert-success' "
                             v-show="prova.status">
                            @{{ prova.status }}
                        </div>
                    </div>

                    <div v-show="!prova.finalizado">
                        <small class="alert alert-warning">Prova em Andamento</small>
                    </div>
                </fieldset>

                <fieldset v-if="form.etapa_status.length > 0">
                    <legend>Histórico</legend>
                    <div class="card mb-2" v-for="etapa in form.etapa_status">
                        <div class="card-header" :class="etapa.status === 'classificado' ? 'bg-primary text-white' : null ||
                                        etapa.status === 'desclassificado' ? 'bg-danger text-white' : null ||
                                        etapa.status === 'admitido' ? 'bg-success' : null
                                        ">
                            @{{ etapa.etapa }}
                        </div>
                        <div class="card-body">
                            <blockquote class="blockquote mb-0">
                                <p>
                                    <strong>Status: @{{ etapa.status }}</strong><br/>
                                    <strong>Preenchido por: @{{ etapa.preenchido_por }}</strong><br/>
                                    <strong>Data: @{{ etapa.created_at }}</strong><br/>
                                    <strong>Observação: @{{ etapa.observacao }}</strong><br/>
                                </p>

                            </blockquote>
                        </div>
                    </div>

                </fieldset>

                <classificar :usuario_id="{{intval(auth()->id())}}" :model="form" janelapai="janelaCadastrar"
                             @salvou="atualizaClassificacao"
                             v-if="form.status == 'classificado'"></classificar>

            </div>
        </template>
    </modal>

    <modal id="janelaWhatsApp" titulo="Enviar Notificação WhatsApp" :fechar="!preloadAjax">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <fieldset v-if="!preloadAjax">
                <legend>Dados Pessoais</legend>
                <p>
                    Nome: @{{form.nome}} | Nascimento: @{{form.nascimento}} |
                    Idade: @{{form.idade}} anos<br>
                    Contato: @{{form.feed_back ? form.feed_back.tel_principal.numero: 'não informado'}} | E-mail:
                    @{{ form.email }}
                </p>

            </fieldset>
        </template>
        <template #rodape>
            <button class="btn btn-primary"
                    @click.prevent="enviarNotificacao(form.feed_back.tel_principal.sonumero, form.nome, form.id, form.feed_back.vaga_selecionada.id, form.etapa_status[0].id)"
                    v-if="!preloadAjax">Enviar Notificação
            </button>
        </template>
    </modal>

    <fieldset>
        <legend>Filtrar por:</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-md-4">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando" id="filtroIntervalo"
                           v-model="controle.dados.filtroPeriodo">
                    <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label="" :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                v-model="controle.dados.periodo"></datepicker>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="form-group">
                    <label>Nome/Cód</label>
                    <input type="text"
                           placeholder="Buscar por nome"
                           autocomplete="off"
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text"
                           placeholder="Buscar por cpf"
                           autocomplete="mastertag"
                           onblur="valida_cpf(this)"
                           v-mascara:cpf
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoCPF">
                </div>
            </div>

            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="form-group">
                    <label>Cargo</label>
                    <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                  :valido="controle.dados.campoVaga !== ''"
                                  v-model="controle.dados.autocomplete_label"
                                  :disabled="controle.carregando"
                                  placeholder="Por cargo"
                                  @onblur="resetaCampo"
                                  @onselect="selecionaVaga"></autocomplete>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Estado</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoUf">
                        <option value="">SEM FILTRO</option>
                        <option value="MA">MA</option>
                        <option value="AC">AC</option>
                        <option value="AL">AL</option>
                        <option value="AP">AP</option>
                        <option value="AM">AM</option>
                        <option value="BA">BA</option>
                        <option value="CE">CE</option>
                        <option value="DF">DF</option>
                        <option value="ES">ES</option>
                        <option value="GO">GO</option>
                        <option value="MT">MT</option>
                        <option value="MS">MS</option>
                        <option value="MG">MG</option>
                        <option value="PA">PA</option>
                        <option value="PB">PB</option>
                        <option value="PR">PR</option>
                        <option value="PE">PE</option>
                        <option value="PI">PI</option>
                        <option value="RJ">RJ</option>
                        <option value="RN">RN</option>
                        <option value="RS">RS</option>
                        <option value="RO">RO</option>
                        <option value="RR">RR</option>
                        <option value="SC">SC</option>
                        <option value="SP">SP</option>
                        <option value="SE">SE</option>
                        <option value="TO">TO</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>PCD</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoPcd">
                        <option value="">Geral</option>
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoStatus">
                        <option value="">Sem filtro</option>
                        <option value="classificado">Classificado</option>
                        <option value="desclassificado">Desclassificado</option>
                        <option value="sem_status">Sem status</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Prova</label>
                    <select class="form-control form-control-sm" @change="filtraProva" :disabled="controle.carregando"
                            v-model="controle.dados.campoProvas">
                        <option value="">Sem filtro</option>
                        <option value="sim">Finalizada</option>
                        <option value="nao">Não realizada</option>
                        <option value="andamento">Não finalizada andamento</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Etapa</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoEtapa">
                        <option value="">Sem filtro</option>
                        <option value="Prova presencial">Classificado Prova presencial</option>
                        <option value="Teste de digitação">Classificado Teste de digitação</option>
                        <option value="Dinâmica de grupo">Classificado Dinâmica de grupo</option>
                        <option value="Entrevista Individual">Classificado Entrevista Individual</option>
                        <option value="Entrevista RH">Classificado Entrevista RH</option>
                        <option value="Entrevista Gestor">Classificado Entrevista Gestor</option>
                        <option value="Apto para Admissao">Apto para Admissao</option>
                        <option value="Aviso Recesso">Aviso Recesso</option>

                        <option value="Desclassificado em prova presencial">Desclassificado em prova online</option>
                        <option value="Desclassificado em prova presencial">Desclassificado em prova presencial
                        </option>
                        <option value="Desclassificado em teste de digitação">Desclassificado em teste de digitação
                        </option>
                        <option value="Desclassificado em dinâmica de grupo">Desclassificado em dinâmica de grupo
                        </option>
                        <option value="Desclassificado em entrevista Individual">Desclassificado em entrevista
                            Individual
                        </option>
                        <option value="Desclassificado em entrevista RH">Desclassificado em entrevista RH</option>
                        <option value="Desclassificado em entrevista Gestor">Desclassificado em entrevista Gestor
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2" v-show="controle.dados.campoProvas === 'sim'">
                <div class="form-group">
                    <label>Nota</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoNota">
                        <option value="">Sem filtro</option>
                        <option value="0">0</option>
                        <option value="1-5">1 à 5</option>
                        <option value="5-7">5 à 7</option>
                        <option value="8-10">8 à 10</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label>Exibir</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.pages">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>


                {{--                <a href="{{\App\Models\Sistema::UrlServidor}}/recrutamentos/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}
                {{--                   class="btn btn-primary"><i--}}
                {{--                        class="fas fa-file-excel"></i>--}}
                {{--                    Exportar Excel</a>--}}
            </div>
        </form>
    </fieldset>


    <preload v-if="controle.carregando" class="text-center"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length==0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th v-if="cliente_id === 1">Cliente</th>
                    <th>Vaga</th>
                    <th>Prova</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody v-for="(feedback,  index) in lista">
                <tr :class='feedback.etapa_status.length > 0 ? feedback.etapa_status[0].status === "desclassificado" ? "table-danger" : "" : ""'>
                    <td class="text-center">
                        @{{feedback.id}}
                    </td>
                    <td>
                        @{{feedback.curriculo.nome}} <br>
                        {{--                        @{{feedback.curriculo.cpf}}--}}
                    </td>
                    <td v-if="cliente_id === 1">
                        @{{feedback.cliente.razao_social}}
                    </td>
                    <td class="text-center">
                        @{{feedback.vaga_aberta.vaga_selecionada.nome +' - '+ feedback.vaga_aberta.municipio.nome +' - '+
                        feedback.vaga_aberta.municipio.uf }}
                    </td>
                    <td class="text-center">
                        @{{feedback.simulados.length > 0 ? "SIM" : "-"}}
                    </td>
                    <td class="text-center">
                        @{{feedback.etapa_status.length > 0 ? feedback.etapa_status[0].etapa : ""}}
                        <br/>
                        @{{feedback.etapa_status.length > 0 ? feedback.etapa_status[0].status : ""}}
                    </td>
                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm btn-primary" content="Exibir" v-tippy
                           @click.prevent="formAlterar(feedback.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </a>

                        <button v-show="feedback.simulados.length > 0" class="btn btn-sm btn-primary"
                                @click="toggle(index); formAlterar(feedback.id)"
                                :content="!opened.includes(index) ? 'Mostrar' : 'Ocultar'" v-tippy
                                style="cursor: pointer">
                            <i :class="!opened.includes(index) ? 'far fa-plus-square' : 'far fa-minus-square'"></i>
                        </button>

                        <span v-if="feedback.tel_principal">
                        <button class="btn btn-sm btn-success"
                                content="enviar Notificação" v-tippy
                                v-if="feedback.etapa_status.length > 0 && feedback.curriculo.tel_principal.tipo === 'whatsapp' && feedback.etapa_status[0].etapa === 'Apto para Admissao'"
                                {{--                        <button class="btn btn-primary" title="Editar" v-if="curriculo.feed_back.tel_principal.tipo === 'whatsapp'"--}}
                                @click.prevent="formAlterar(feedback.id)"
                                data-toggle="modal"
                                :disabled="curriculo.whats_app_notificacao"
                                data-target="#janelaWhatsApp">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        </button>
                        </span>
                    </td>
                </tr>
                <tr v-if="opened.includes(index)">
                    <td class="text-left" :colspan="cliente_id === 1 ? 7 : 6">
                        <fieldset v-for="prova in feedback.simulados">
                            <legend>Teste:
                                @{{ prova.simulado_vaga.simulado.titulo }} -
                                (@{{ prova.simulado_vaga.online ? 'Online' : 'Presencial' }})
                            </legend>
                            <div v-show="prova.finalizado">
                                <p>Acertos: @{{ prova.acertos }} | | Tempo restante: @{{
                                    prova.duracao_segundos }} min | | Finalizado em @{{ prova.data_finalizacao }}h</p>
                                <div v-show="!prova.status">
                                    <classificar :usuario_id="{{intval(auth()->id())}}" :model="feedback"
                                                 :simulado_id="prova.id" @salvou="atualizaClassificacao"></classificar>
                                </div>
                                <div class="text-uppercase alert"
                                     :class="prova.status === 'desclassificado' ? 'alert-danger' : 'alert-success' "
                                     v-show="prova.status">
                                    @{{ prova.status }}
                                </div>
                            </div>

                            <div v-show="!prova.finalizado">
                                <small class="alert alert-warning">Prova em Andamento</small>
                            </div>
                        </fieldset>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.curriculoselecao.curriculos-selecionados.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('css')
    <style type="text/css">
        p {
            font-size: 0.85rem;
            line-height: 1.6rem;
        }

        .alert {
            font-size: 0.85rem;
            line-height: 1.6rem;
        }
    </style>
@endpush
@push('js')
    <script src="{{mix('js/g/selecionados/app.js')}}"></script>
@endpush
