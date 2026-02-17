@extends('layouts.sistema')
@section('title', 'Planejamento - Requisição de Vagas')
@section('content_header','Planejamento - Requisição de Vagas')
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template slot="conteudo">
            <preload v-show="preload" class="text-center"></preload>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
            </div>
            <form v-if="!preload && (!cadastrado && !atualizado) " id="form" onsubmit="return false;">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12"></div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Selecione um cargo <span class="text-danger">*</span></label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_autocomplete"
                                              :disabled="visualizar"
                                              :valido="form.cargo_id !== ''"
                                              v-model="form.autocomplete_label_cargo_modal"
                                              placeholder="Digite o nome do cargo"
                                              :id="`vaga_modal_${hash}`"
                                              @onblur="resetaCampoVagaModal"
                                              @onselect="selecionaVagaModal"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Área <span class="text-danger">*</span></label>
                                <select v-model="form.area_id" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option v-for="item in areas_etiquetas" :value="item.id">@{{ item.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Centro de Custo <span class="text-danger">*</span></label>
                                <select v-model="form.centro_custo_id" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option v-for="item in centro_custos" :value="item.id">@{{ item.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Tipo de Contratação <span class="text-danger">*</span></label>
                                <select v-model="form.tipo_contratacao" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option value="APRENDIZ">APRENDIZ</option>
                                    <option value="FIXO">FIXO</option>
                                    <option value="INTERMITENTE">INTERMITENTE</option>
                                    <option value="PJ">PJ</option>
                                    <option value="ESTÁGIO">ESTÁGIO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Prioridade <span class="text-danger">*</span></label>
                                <select v-model="form.prioridade" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option value="ALTA">ALTA</option>
                                    <option value="MÉDIA">MÉDIA</option>
                                    <option value="URGENTE">URGENTE</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Quantidade <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                       onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                       v-mascara:numero v-model="form.quantidade">
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>DEMAIS INFORMAÇÕES</legend>
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Posição <span class="text-danger">*</span></label>
                                            <select v-model="form.outras_informacoes.posicao" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="Efetiva">Efetiva</option>
                                                <option value="Estágio">Estágio</option>
                                                <option value="Temporária">Temporária</option>
                                                <option value="Aumento de Quadro">Aumento de Quadro</option>
                                                <option value="Substituição">Substituição</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Processo <span class="text-danger">*</span></label>
                                            <select v-model="form.outras_informacoes.processo" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="Externo">Externo</option>
                                                <option value="Confidencial">Confidencial</option>
                                                <option value="Interno">Interno</option>
                                                <option value="Indicação">Indicação</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4"
                                         v-if="form.outras_informacoes.processo === 'Indicação'">
                                        <div class="form-group">
                                            <label>Nome Indicação</label>
                                            <input type="text" class="form-control" :disabled="visualizar"
                                                   onblur="valida_campo_vazio(this,1)"
                                                   v-model="form.outras_informacoes.nome_indicacao">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Tipo <span class="text-danger">*</span></label>
                                            <select v-model="form.outras_informacoes.contrato" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="Admissão">Admissão</option>
                                                <option value="Readmissão">Readmissão</option>
                                                <option value="Reintegração">Reintegração</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!--                                    <div class="col-12 col-md-4">
                                                                                <div class="form-group">
                                                                                    <label>Local de Trabalho</label>
                                                                                    <input type="text" class="form-control"
                                                                                           onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                                                           v-model="form.outras_informacoes.local_trabalho">
                                                                                </div>
                                                                            </div>-->

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Horário</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.horario">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Gestor da vaga <span class="text-danger">*</span></label>
                                            <autocomplete
                                                :caminho="'autocomplete/todos-gestores-ativos'"
                                                :formsm="false"
                                                :valido="form.outras_informacoes.gestor_id !== ''"
                                                v-model="form.outras_informacoes.autocomplete_label_gestor"
                                                placeholder="Digite o nome do(a) gestor(a)"
                                                :disabled="visualizar"
                                                :id="`gestor_${hash}`"
                                                @onblur="resetaCampoGestor"
                                                @onselect="selecionaGestor"></autocomplete>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Cargo está no PPRA e PCMSO? <span class="text-danger">*</span></label>
                                            <select v-model="form.outras_informacoes.ppra" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option :value="true">Sim</option>
                                                <option :value="false">Não</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Salário <span class="text-danger">*</span></label>
                                            <select v-model="form.outras_informacoes.salario" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="conforme">Conforme o plano de cargo</option>
                                                <option value="exceção">Exceção</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4" v-if="form.outras_informacoes.salario === 'exceção'">
                                        <div class="form-group">
                                            <label>Salário exceção</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_dinheiro(this)" :disabled="visualizar"
                                                   v-mascara:dinheiro
                                                   v-model="form.outras_informacoes.salario_valor_format">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Benefício <span class="text-danger">*</span></label>
                                            <select v-model="form.outras_informacoes.beneficio" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="conforme">Conforme o plano da empresa</option>
                                                <option value="exceção">Exceção</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4" v-if="form.outras_informacoes.beneficio === 'exceção'">
                                        <div class="form-group">
                                            <label>Benefício exceção</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.beneficio_excecao">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Treinamento</label>
                                            <select v-model="form.outras_informacoes.treinamento" class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="conforme">Conforme o padrão</option>
                                                <option value="exceção">Exceção</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4"
                                         v-if="form.outras_informacoes.treinamento === 'exceção'">
                                        <div class="form-group">
                                            <label>Treinamento exceção</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.treinamento_excecao">
                                        </div>
                                    </div>


                                </div>
                            </fieldset>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Previsão de Ínicio</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.imediata" class="custom-control-input"
                                           :disabled="visualizar"
                                           id="imediata">
                                    <label class="custom-control-label"
                                           for="imediata">Imediata</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12"></div>
                        <div class="col-12 col-md-4" v-if="!form.imediata">
                            <datepicker label="Previsão" v-model="form.previsao_inicio"
                                        :disabled="visualizar"></datepicker>
                        </div>


                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Solicitante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                       :disabled="visualizar"
                                       v-model="form.solicitante">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea class="form-control" v-model="form.observacao" cols="5" rows="5"
                                          :disabled="visualizar"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="alert alert-warning d-flex align-items-center" v-if="!form.data_aprovacao && !cadastrando">
                    <i class="fas fa-hourglass-half fa-2x mr-3"></i>
                    <div>
                        <strong>Aguardando Aprovação</strong><br>
                        <small>Esta solicitação ainda não foi aprovada ou reprovada pelo gestor.</small>
                    </div>
                </div>

                <fieldset v-if="aprovando || (visualizar && form.status_aprovacao)">
                    <legend>Aprovação Gestor</legend>
                    <div class="row">
                        <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                            <legend>@{{ form.status_aprovacao }} por: @{{ form.user_aprovacao }} em @{{ form.data_aprovacao }}</legend>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea
                                    class="form-control form-control-sm"
                                    :disabled="!aprovando"
                                    v-model="form.obs_aprovacao"
                                    cols="5"
                                    rows="5"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select
                                    :disabled="!aprovando"
                                    v-model="form.status_aprovacao"
                                    class="form-control form-control-sm validacampo"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option value="aprovado">Aprovar</option>
                                    <option value="reprovado">Reprovar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="alert alert-warning" v-if="aprovandoExtra && temAprovacaoExtra">
                    Esta solicitação ainda não foi aprovada ou reprovada pela @{{ nomeAprovacaoExtra }}!
                </div>

                <fieldset v-if="(aprovandoExtra || (visualizar && form.status_aprovacao_extra)) && temAprovacaoExtra">
                    <div v-if="!temAprovacaoExtra" class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                    </div>

                    <legend v-if="temAprovacaoExtra">@{{ nomeAprovacaoExtra }}</legend>
                    <div class="row" v-if="temAprovacaoExtra">
                        <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12">
                            <legend>@{{ form.status_aprovacao_extra }} por: @{{ form.aprovacao_extra_nome }} em @{{ form.data_aprovacao_extra }}</legend>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea
                                    class="form-control form-control-sm"
                                    :disabled="!aprovandoExtra || aprovandoRh"
                                    v-model="form.obs_aprovacao_extra"
                                    cols="5"
                                    rows="5"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select
                                    :disabled="!aprovandoExtra || aprovandoRh"
                                    v-model="form.status_aprovacao_extra"
                                    class="form-control form-control-sm validacampo"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option value="aprovado">Aprovar</option>
                                    <option value="reprovado">Reprovar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="alert alert-warning" v-if="aprovandoRh">
                    Esta solicitação ainda não foi aprovada ou reprovada!
                </div>

                <fieldset v-if="aprovandoRh || (visualizar && form.status_aprovacao_rh)">
                    <legend>Aprovação RH</legend>
                    <div class="row">
                        <div v-if="!aprovandoRh && form.rh_aprovacao_nome" class="col-12">
                            <legend>@{{ form.status_aprovacao_rh }} por: @{{ form.rh_aprovacao_nome }} em @{{ form.data_aprovacao_rh }}</legend>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea
                                    class="form-control form-control-sm"
                                    :disabled="!aprovandoRh"
                                    v-model="form.obs_rh"
                                    cols="5"
                                    rows="5"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select
                                    :disabled="!aprovandoRh"
                                    v-model="form.status_aprovacao_rh"
                                    class="form-control form-control-sm validacampo"
                                    onchange="valida_campo_vazio(this, 1)"
                                    onblur="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option value="aprovado">Aprovar</option>
                                    <option value="reprovado">Reprovar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </form>
        </template>
        <template slot="rodape">
            <div>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="editando && !preload && !cadastrando && !aprovando"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!editando && !preload && cadastrando && !aprovando"
                        @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Cadastrar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !editando && !form.data_aprovacao && !cadastrando"
                        @click.prevent="aprovar">
                    <i class="fa fa-save"></i> Aprovar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovandoExtra && !preload && !cadastrando"
                        @click.prevent="aprovarExtra">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovandoRh && !preload && !cadastrando"
                        @click.prevent="aprovarRh">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend>Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <date-range-filter
                :enabled.sync="controle.dados.filtroPeriodo"
                :start-date.sync="controle.dados.dataInicio"
                :end-date.sync="controle.dados.dataFim"
                :disabled="controle.carregando"
                :id-suffix="hash"
                wrapper-class="col-12 col-md-3">
            </date-range-filter>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Pesquisar</label>
                    <input type="text"
                           placeholder="Buscar por cargo"
                           autocomplete="off"
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                            :disabled="controle.carregando" @change="atualizar()">
                        <option value="">Todos os Status</option>
                        <option value="aberto">Aberto</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="reprovado">Reprovado</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Ordenar por</label>
                    <select class="form-control form-control-sm" v-model="controle.dados.ordenacao"
                            :disabled="controle.carregando" @change="atualizar()">
                        <option value="created_at_desc">Mais Recentes</option>
                        <option value="created_at_asc">Mais Antigos</option>
                        <option value="updated_at_desc">Última Modificação</option>
                    </select>
                </div>
            </div>

            <div class="col-12"></div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click.prevent="formNovo">
                    Solicitar
                </button>
                <button type="button" class="btn btn-sm btn-primary  mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && !lista.length) ">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                </button>
            </div>
        </form>
    </fieldset>

    <preload class="text-center" v-if="controle.carregando"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <!-- Cards Compactos -->
        <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
            <div class="solicitacao-card" v-for="item in lista" :key="item.id">
                <!-- Cabeçalho do Card -->
                <div class="card-header-row">
                    <div class="card-left">
                        <span class="badge-id">#@{{ item.id }}</span>
                        <div class="colaborador-principal">
                            <i class="fas fa-briefcase text-primary mr-1"></i>
                            <strong>@{{ item.cargo.nome }}</strong>
                        </div>
                    </div>
                    <div class="card-right">
                    <span class="status-badge" :class="{
                        'status-reprovado': item.status_aprovacao === 'reprovado' || item.status_aprovacao_extra === 'reprovado',
                        'status-aprovado': item.status_aprovacao === 'aprovado' && (!temAprovacaoExtra || item.status_aprovacao_extra === 'aprovado'),
                        'status-aprovado-extra': temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado',
                        'status-aprovado-gestor': item.status_aprovacao === 'aprovado' && (!temAprovacaoExtra || !item.status_aprovacao_extra),
                        'status-pendente': !item.status_aprovacao,
                    }">
                        <span v-if="item.status_aprovacao === 'reprovado' || item.status_aprovacao_extra === 'reprovado'">
                            <i class="fas fa-times-circle"></i> REPROVADO
                        </span>
                        <span v-else-if="item.status_aprovacao === 'aprovado' && (!temAprovacaoExtra || item.status_aprovacao_extra === 'aprovado')">
                            <i class="fas fa-check-circle"></i> APROVADO
                        </span>
                        <span v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado'">
                            <i class="fas fa-check-circle"></i> APROVADO EXTRA
                        </span>
                        <span v-else-if="item.status_aprovacao === 'aprovado'">
                            <i class="fas fa-check-circle"></i> APROVADO GESTOR
                        </span>
                        <span v-else>
                            <i class="fas fa-clock"></i> EM ABERTO
                        </span>
                    </span>
                        <div class="dropdown show">
                            <a class="btn btn-sm btn-outline-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                @can('privilegio_aprovar_por_gestor')
                                    <a class="dropdown-item" href="javascript://" title="Aprovação Gestor" data-toggle="modal" data-target="#janelaCadastrar"
                                       @click.prevent="formOpen(item.id); visualizar = true; aprovando = true; aprovandoExtra = false; aprovandoRh = false; editando = false; cadastrando = false"
                                       v-if="!item.data_aprovacao && aprovaGestor">
                                        Aprovação Gestor
                                    </a>
                                @endcan
                                <a class="dropdown-item" href="javascript://" :title="nomeAprovacaoExtra" data-toggle="modal" data-target="#janelaCadastrar"
                                   @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; editando = false; cadastrando = false"
                                   v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && aprovaExtra">
                                    @{{ nomeAprovacaoExtra }}
                                </a>
                                <a class="dropdown-item" href="javascript://" title="Aprovação RH" data-toggle="modal" data-target="#janelaCadastrar"
                                   @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; editando = false; cadastrando = false"
                                   v-if="((item.status_aprovacao === 'aprovado' && !temAprovacaoExtra) || (item.status_aprovacao_extra === 'aprovado')) && !item.rh_aprovacao_id && aprovaRh">
                                    Aprovação RH
                                </a>
                                <a v-if="false" class="dropdown-item" href="javascript://" title="Editar" data-toggle="modal" data-target="#janelaCadastrar"
                                   @click.prevent="formOpen(item.id); editando = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; cadastrando = false; visualizar = false"
                                   v-if="item.status_aprovacao !== 'aprovado'">
                                    Editar
                                </a>
                                <a class="dropdown-item" href="javascript://" title="Visualizar" data-toggle="modal" data-target="#janelaCadastrar"
                                   @click.prevent="formOpen(item.id); visualizar = true; editando = false; aprovando = false; aprovandoExtra = false; aprovandoRh = false; cadastrando = false">
                                    Visualizar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalhes do Card -->
                <div class="card-details-row">
                    <div class="detail-item">
                        <i class="fas fa-hashtag text-muted"></i>
                        <span class="detail-label">Quantidade:</span>
                        <span class="detail-value">@{{ item.quantidade }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-user text-muted"></i>
                        <span class="detail-label">Solicitante:</span>
                        <span class="detail-value">@{{ item.solicitante }}</span>
                    </div>
                    <div class="detail-item" v-if="item.OutrasInformacoes && item.OutrasInformacoes.GestorAprovacao">
                        <i class="fas fa-user-tie text-muted"></i>
                        <span class="detail-label">Gestor:</span>
                        <span class="detail-value">@{{ item.OutrasInformacoes.GestorAprovacao.nome }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        <span class="detail-label">Solicitação:</span>
                        <span class="detail-value">@{{ item.data_solicitacao }}</span>
                    </div>
                </div>
                <div class="card-details-row">
                    <div class="detail-item">
                        <i class="fas fa-building text-muted"></i>
                        <span class="detail-label">Centro Custo:</span>
                        <span class="detail-value">@{{ item.centro_custo.label }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-tag text-muted"></i>
                        <span class="detail-label">Área:</span>
                        <span class="detail-value">@{{ (item.area && item.area.label) || '-' }}</span>
                    </div>
                </div>
                <div class="card-details-row">
                    <div class="detail-item">
                        <i class="fas fa-file-contract text-muted"></i>
                        <span class="detail-label">Contratação:</span>
                        <span class="detail-value">@{{ item.tipo_contratacao }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-exclamation-triangle text-muted"></i>
                        <span class="detail-label">Prioridade:</span>
                        <span class="detail-value">@{{ item.prioridade }}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock text-muted"></i>
                        <span class="detail-label">Início:</span>
                        <span class="detail-value" v-if="item.imediata">Imediata</span>
                        <span class="detail-value" v-else>@{{ item.previsao_inicio }}</span>
                    </div>
                </div>

                <!-- Datas de Criação e Modificação -->
                <div class="card-details-row" v-if="false">
                    <div class="detail-item">
                        <i class="fas fa-calendar-plus text-muted"></i>
                        <span class="detail-label">Criação:</span>
                        <span class="detail-value">@{{ item.created_at }}</span>
                    </div>
                    <div class="detail-item" v-if="item.updated_at && item.updated_at !== item.created_at">
                        <span class="mx-2 text-muted">|</span>
                    </div>
                    <div class="detail-item" v-if="item.updated_at && item.updated_at !== item.created_at">
                        <i class="fas fa-calendar-check text-muted"></i>
                        <span class="detail-label">Modificação:</span>
                        <span class="detail-value">@{{ item.updated_at }}</span>
                    </div>
                </div>

                <!-- Fluxo de Aprovação -->
                <div class="card-aprovacao-row">
                    <div class="fluxo-icons">
                        <!-- Solicitante -->
                        <div class="fluxo-step">
                            <i class="fas fa-check-circle text-success"></i>
                            <div class="fluxo-info">
                                <small class="fluxo-etapa">Solicitante</small>
                                <small class="fluxo-aprovador text-success">
                                    @{{ item.solicitante || 'Sem informação' }}
                                </small>
                                <small class="fluxo-data">@{{ item.created_at_br }}</small>
                            </div>
                        </div>

                        <i class="fas fa-chevron-right text-muted mx-2"></i>

                        <!-- Gestor -->
                        <div class="fluxo-step">
                            <i v-if="item.status_aprovacao === 'aprovado'" class="fas fa-check-circle text-success"></i>
                            <i v-else-if="item.status_aprovacao === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                            <i v-else class="fas fa-clock text-muted"></i>
                            <div class="fluxo-info">
                                <small class="fluxo-etapa">Gestor</small>
                                <small v-if="item.user_aprovacao" class="fluxo-aprovador text-success">
                                    @{{ item.user_aprovacao.nome }}
                                </small>
                                <small v-else class="fluxo-status text-warning">Aguardando</small>
                                <small v-if="item.data_aprovacao_br" class="fluxo-data">@{{ item.data_aprovacao_br }}</small>
                            </div>
                        </div>

                        <template v-if="temAprovacaoExtra">
                            <i class="fas fa-chevron-right text-muted mx-2"></i>
                            <!-- Aprovação Extra -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao === 'reprovado'" class="fas fa-ban text-secondary"></i>
                                <i v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else-if="item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra" class="fas fa-clock text-warning"></i>
                                <i v-else class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">@{{ nomeAprovacaoExtra }}</small>
                                    <small v-if="item.status_aprovacao === 'reprovado'" class="fluxo-status text-secondary">Cancelada</small>
                                    <small v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fluxo-aprovador text-success">
                                        @{{ item.aprovacao_extra_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fluxo-aprovador text-danger">
                                        @{{ item.aprovacao_extra_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'aprovado'" class="fluxo-status text-warning">Aguardando</small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_extra" class="fluxo-data">@{{ item.data_aprovacao_extra }}</small>
                                </div>
                            </div>
                        </template>

                        <i class="fas fa-chevron-right text-muted mx-2"></i>

                        <!-- RH -->
                        <div class="fluxo-step">
                            <i v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')" class="fas fa-ban text-secondary"></i>
                            <i v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fas fa-check-circle text-success"></i>
                            <i v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                            <i v-else-if="(temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') || (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')" class="fas fa-clock text-warning"></i>
                            <i v-else class="fas fa-circle text-muted"></i>
                            <div class="fluxo-info">
                                <small class="fluxo-etapa">RH</small>
                                <small v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')" class="fluxo-status text-secondary">Cancelada</small>
                                <small v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fluxo-aprovador text-success">
                                    @{{ item.rh_aprovacao_nome }}
                                </small>
                                <small v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fluxo-aprovador text-danger">
                                    @{{ item.rh_aprovacao_nome }}
                                </small>
                                <small v-else-if="(temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') || (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')" class="fluxo-status text-warning">Aguardando</small>
                                <small v-else class="fluxo-status">Pendente</small>
                                <small v-if="item.data_aprovacao_rh_br || item.data_aprovacao_rh" class="fluxo-data">@{{ item.data_aprovacao_rh_br || item.data_aprovacao_rh }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao
            class="d-flex justify-content-center"
            id="controle"
            ref="componente"
            url="{{route('g.requisicao_vagas.atualizar')}}"
            por-pagina="50"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/planejamento/requisicao-vagas/app.js')}}"></script>
@endpush

@push('css')
    <style>
        /* Cards Lista */
        .cards-lista {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Card Individual */
        .solicitacao-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .solicitacao-card:hover {
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
            border-color: #007bff;
            transform: translateY(-2px);
        }

        /* Header do Card */
        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f3f5;
            margin-bottom: 0.75rem;
        }

        .card-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            overflow: hidden;
        }

        .card-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .badge-id {
            background: #174257;
            color: white;
            padding: 0.25rem 0.625rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.75rem;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .colaborador-principal {
            display: flex;
            align-items: center;
            font-size: 0.938rem;
            color: #212529;
            overflow: hidden;
        }

        .colaborador-principal strong {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Status Badge */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            white-space: nowrap;
            letter-spacing: 0.3px;
        }

        .status-reprovado {
            background: #dc3545;
            color: white;
        }

        .status-aprovado {
            background: #28a745;
            color: white;
        }

        .status-aprovado-gestor {
            background: #ffc107;
            color: #212529;
        }

        .status-aprovado-extra {
            background: #17a2b8;
            color: white;
        }

        .status-pendente {
            background: #e9ecef;
            color: #495057;
        }

        /* Detalhes do Card */
        .card-details-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f3f5;
            margin-bottom: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.813rem;
            min-width: 0;
        }

        .detail-item i {
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        .detail-label {
            font-weight: 500;
            color: #6c757d;
            white-space: nowrap;
        }

        .detail-value {
            color: #212529;
            font-weight: 400;
        }

        /* Fluxo de Aprovação */
        .card-aprovacao-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .fluxo-icons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            flex: 1;
        }

        .fluxo-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .fluxo-step i {
            font-size: 1.125rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .fluxo-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .fluxo-etapa {
            font-weight: 600;
            color: #495057;
            font-size: 0.688rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .fluxo-aprovador,
        .fluxo-status {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .fluxo-data {
            font-size: 0.688rem;
            color: #6c757d;
        }

        /* Dropdown Menu */
        .dropdown-menu-custom {
            min-width: 10rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #dee2e6;
        }

        .dropdown-menu-custom .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .dropdown-menu-custom .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .card-right {
                width: 100%;
                justify-content: space-between;
            }

            .fluxo-icons {
                flex-direction: column;
                align-items: flex-start;
            }

            .fluxo-icons>i.fa-chevron-right {
                transform: rotate(90deg);
                margin: 0;
            }
        }
    </style>
@endpush
