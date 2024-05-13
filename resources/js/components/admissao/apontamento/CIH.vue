<template>
    <div>
        <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
            <template slot="conteudo">
                <preload label="Aguarde..." v-show="preloadAjax"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado || atualizado">
                    <h4><i class="icon fa fa-check"></i>Ocorrrência {{ cadastrado ? "cadastrada" : "atualizada" }} com
                        sucesso!</h4>
                </div>
                <form v-if="!preloadAjax && !cadastrado && !atualizado" id="form" onsubmit="return false;">
                    <fieldset>
                        <legend>Lançamento</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Data da Ocorrência</label>
                                    <date-picker
                                        label=""
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        formsm
                                        v-model="form.data_lancamento"
                                        style="margin-top: -19px"
                                        :max="hoje"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.tag_id"
                                        class="form-control form-control-sm validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                    >
                                        <option value="">Selecione...</option>
                                        <option v-for="item in listaTags" :value="item.id" :key="item.id"
                                                v-text="item.label"></option>
                                        <option :value="0">Outro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.tag_id === 0">
                                <div class="form-group">
                                    <label>Especifique</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.outra_tag"
                                    />
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-md-6" v-if="this.config_modelo_cih === 'area'">
                                <div class="form-group">
                                    <label>Área</label>
                                    <select
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.area_id"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        class="form-control form-control-sm validacampo"
                                    >
                                        <option value="">Selecione...</option>
                                        <option v-for="item in listaAreas" :value="item.id" :key="item.id"
                                                v-text="item.label"></option>
                                        <option :value="0">Outra</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.area_id === 0">
                                <div class="form-group">
                                    <label>Especifique</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.outra_area"
                                    />
                                </div>
                            </div>

                            <div class="col-md-6" v-if="this.config_modelo_cih === 'centro_de_custo'">
                                <div class="form-group">
                                    <label>Centro de Custo</label>
                                    <select
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.centro_custo_id"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        class="form-control form-control-sm validacampo"
                                    >
                                        <option value="">Selecione...</option>
                                        <option v-for="item in centros_de_custo" :value="item.id" :key="item.id">{{
                                                item.gestor == null ? item.label + ' - Gestor não informado' : item.label +
                                                    ' - ' + item.gestor.nome
                                            }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Colaborador(es)</label>
                                    <autocomplete
                                        :caminho="colaborador_ativo"
                                        formsm
                                        :valido="form.feedback_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="aprovando || aprovandoRh || visualizar"
                                        :id="`colaborador_${hash}`"
                                        @onselect="selecionaColaborador"
                                        v-if="!editando"
                                    ></autocomplete>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-condensed bg-white"
                                           v-if="form.colaboradores.length">
                                        <thead>
                                        <tr class="bg-default">
                                            <th class="text-center" width="50%">Nome</th>
                                            <th class="text-center" width="40%">Cargo</th>
                                            <th class="text-center" v-if="!editando">Remover</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(colaborador, index) in form.colaboradores">
                                            <!--                                            <td class="text-center">{{ colaborador.curriculo.nome }}</td>
                                                                                        <td class="text-center">{{ colaborador.vaga_aberta.vaga.nome }}</td> -->
                                            <td class="text-center">
                                                {{ !editando ? colaborador.label : colaborador.curriculo.nome }}
                                            </td>
                                            <td class="text-center">
                                                {{ !editando ? colaborador.cargo : colaborador.vaga_aberta.vaga.nome }}
                                            </td>
                                            <td class="text-center" v-if="!editando">
                                                <a href="javascript://" class="btn btn-sm btn-danger"
                                                   @click.prevent="removerLIColaborador(index)">
                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <gestoraprovacao :model="form" :verifica="aprovando || aprovandoRh || visualizar"
                                             :hash="hash" v-if="this.config_modelo_cih === 'area'"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Ação</label>
                                    <textarea
                                        class="form-control"
                                        rows="3"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                        v-model="form.acao"
                                    ></textarea>

                                </div>
                            </div>

                            <div class="col-12">
                                <fieldset>
                                    <legend>Anexo (Evidência)</legend>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :leitura="!!form.id"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="anexoUploadAndamento = false"
                                    ></upload>
                                </fieldset>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" :disabled="visualizar || aprovandoRh || aprovando"
                                              v-model="form.obs_lancamento"
                                              cols="5" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="(visualizar && !form.responsavel_aprovacao) || aprovando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo GESTOR!
                    </div>

                    <fieldset v-if="!cadastrando">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.responsavel_aprovacao" class="col-12">
                                <legend>{{ form.status }}
                                    por: {{ form.responsavel_aprovacao.nome }} em
                                    {{ form.data_aprovacao }}
                                </legend>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" :disabled="!aprovando || aprovandoRh"
                                              v-model="form.obs_aprovacao" cols="5" rows="5"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select
                                        :disabled="!aprovando || aprovandoRh"
                                        v-model="form.status"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                        class="form-control form-control-sm validacampo"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovado</option>
                                        <option value="reprovado">Reprovado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="(visualizar && !form.rh_aprovacao) || aprovandoRh">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo RH!
                    </div>

                    <fieldset v-if="visualizar || aprovandoRh">
                        <legend>Aprovação RH</legend>
                        <div class="row">

                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                <legend>{{ form.resposta_rh }}
                                    por: {{ form.rh_aprovacao.nome }} em
                                    {{ form.data_aprovacao_rh }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm"
                                              :disabled="visualizar && !aprovando && !aprovandoRh"
                                              v-model="form.obs_rh"
                                              cols="5" rows="5"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select :disabled="visualizar && !aprovando && !aprovandoRh"
                                            v-model="form.resposta_rh"
                                            class="form-control form-control-sm validacampo"
                                            @change.prevent="valida_campo_vazio($event.target, 1)"
                                            onblur="valida_campo_vazio(this, 1)">
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovado</option>
                                        <option value="reprovado">Reprovado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="(aprovando || aprovandoRh) && !preloadAjax"
                        @click="aprovar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!aprovando && !aprovandoRh && !cadastrado && !atualizado && !visualizar && !preloadAjax"
                        @click="cadastrar">
                    <i class="fa fa-save"></i> Lançar
                </button>
            </template>
        </modal>

        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                               @change.prevent="atualizar()"
                               id="filtroIntervalo"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label=""
                                    @onselect="atualizar()"
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                                @change="atualizar()" :disabled="controle.carregando">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Em aberto</option>
                            <option value="aprovado_gestor">Aprovado Gestor</option>
                            <option value="aprovado_rh">Aprovado Rh</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select v-model="controle.dados.campoTags" :disabled="controle.carregando" @change="atualizar()"
                                class="form-control form-control-sm">
                            <option value="">Todos os tipos</option>
                            <option v-for="item in listaTags" :value="item.id" :key="item.id"
                                    v-text="item.label"></option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="this.config_modelo_cih === 'area'">
                    <div class="form-group">
                        <label>Área</label>
                        <select v-model="controle.dados.campoAreas" :disabled="controle.carregando"
                                @change="atualizar()" class="form-control form-control-sm">
                            <option value="">Todas as áreas</option>
                            <option v-for="item in listaAreas" :value="item.id" :key="item.id"
                                    v-text="item.label"></option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="this.config_modelo_cih === 'centro_de_custo'">
                    <div class="form-group">
                        <label>Centros de Custo</label>
                        <select v-model="controle.dados.campoCentrosDeCusto" :disabled="controle.carregando"
                                @change="atualizar()" class="form-control form-control-sm">
                            <option value="">Todas os centros de custo</option>
                            <option v-for="item in centros_de_custo" :value="item.id" :key="item.id"
                                    v-text="item.label"></option>
                        </select>
                    </div>
                </div>

                <!--                <div class="col-12 col-md-4" v-if="permissoes.admissao_cih_privilegio_adm">-->
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Gestores</label>
                        <select v-model="controle.dados.campoGestores" :disabled="controle.carregando"
                                @change="atualizar()" class="form-control form-control-sm">
                            <option value="">Todas os gestores</option>
                            <option v-for="item in gestores" :value="item.gestor_aprovacao.id"
                                    :key="item.gestor_aprovacao.id"
                                    v-text="item.gestor_aprovacao.nome"></option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar()">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                        <span>{{ controle.carregando ? "Buscando..." : "Buscar" }}</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-toggle="modal"
                        :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click.pre.prevent="formNovo(); cadastrando = true; aprovandoRh = false; aprovando = false; visualizar = false;"
                        v-if="permissoes.admissao_cih_lancar"
                    >
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>

                    <button type="button" class="btn btn-sm btn-primary"
                            @click.prevent="exportaPdf()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0) ">
                        <i class="fas fa-file-pdf"></i> EXPORTAR PDF
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length && !selecionados.length)"
                    >
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                        <span class="badge badge-light" v-show="selecionados.length"
                              v-text="selecionados.length"></span>
                    </button>
                </div>
            </form>
        </fieldset>

        <div class="mb-2 mt-2 pt-1 pb-1 border-bottom bg-white" v-show="!controle.carregando && lista.length > 0">
            <span class="text-right ml-2">
                Legenda:
                <i class="fas fa-circle text-light ml-2"></i> Em aberto <i
                class="fas fa-circle text-warning ml-2"></i> Aprovado pelo Gestor
                <i class="fas fa-circle text-success ml-2"></i> Aprovado pelo RH <i
                class="fas fa-circle text-danger ml-2"></i> Reprovado
            </span>
        </div>

        <preload v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && !lista.length">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length">
                <table class="table table-centered bg-white">
                    <thead>
                    <tr>
                        <th class="text-center">CÓD</th>
                        <th class="text-center">Colaborador</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Data Ocorrência</th>
                        <th class="text-center">Lançamento</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="item in lista"
                        :key="item.id"
                    >
                        <td class="text-center vertical-align-middle" v-text="item.id"></td>
                        <td class="text-center vertical-align-middle">{{
                                item.varios_colaboradores ? "Varios colaboradores" : item.colaboradores[0].curriculo.nome
                            }}
                        </td>
                        <td class="text-center vertical-align-middle">
                            {{ item.tag ? item.tag.label : item.outra_tag }}
                        </td>
                        <td class="text-center vertical-align-middle" v-text="item.data_lancamento"></td>
                        <td class="text-center vertical-align-middle">
                            Lançado por {{ item.responsavel_lancamento.nome }}
                            em<br/>{{ item.created_at }}
                        </td>
                        <td class="text-center font-weight-bold vertical-align-middle"
                            :class="{
                            'bg-danger text-white': item.status === 'reprovado' || item.resposta_rh === 'reprovado',
                            'bg-success': item.status === 'aprovado' || (item.resposta_rh === 'aprovado'),
                            'bg-warning': item.status === 'aprovado' && item.resposta_rh === null,
                            'bg-light': item.status === 'aberto' && item.resposta_rh === null,
                        }">
                            <span class="text-capitalize" v-if="item.responsavel_aprovacao || item.rh_aprovacao">
                                <span
                                    v-if="item.status === 'aprovado' && item.resposta_rh === null">
                                    {{ item.status }} em <br/>{{ item.data_aprovacao }}<br/>
                                    Por gestor(a): {{ item.responsavel_aprovacao.nome }}
                                </span>
                                <span
                                    v-if="item.resposta_rh === 'aprovado'">
                                    {{ item.status }} em <br/>{{ item.data_aprovacao_rh }}<br/>
                                    Por RH: {{ item.rh_aprovacao.nome }}
                                </span>
                                <span v-if="item.status === 'reprovado' && item.resposta_rh === null">
                                    {{ item.status }} em <br/>{{ item.data_aprovacao }}<br/>
                                    Por gestor(a): {{ item.responsavel_aprovacao.nome }}
                                </span>
                                <span v-if="item.resposta_rh === 'reprovado'">
                                    {{ item.resposta_rh }} em <br/>{{ item.data_aprovacao_rh }}<br/>
                                    Por RH: {{ item.rh_aprovacao.nome }}
                                </span>
                            </span>
                            <span class="text-capitalize" v-else> EM ABERTO </span>
                        </td>
                        <td class="text-center vertical-align-middle">
                            <div class="dropdown show">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                     aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Aprovação Gestor"
                                       data-toggle="modal"
                                       data-target="#janelaCadastrar"
                                       @click.prevent="formAprovar(item.id); visualizar = false; aprovando = true; aprovandoRh = false; cadastrando = false;"
                                       v-if="item.user_aprovacao_id === null && item.status === 'aberto' && aprovaGestor">
                                        Aprovação Gestor
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Aprovação RH"
                                       data-toggle="modal"
                                       data-target="#janelaCadastrar"
                                       @click.prevent="formAprovar(item.id); visualizar = false; aprovando = false; aprovandoRh = true; cadastrando = false;"
                                       v-if="item.status === 'aprovado' && item.user_rh_id === null && aprovaRh">
                                        Aprovação Rh
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar"
                                       data-toggle="modal"
                                       data-target="#janelaCadastrar"
                                       @click.prevent="formAprovar(item.id); visualizar = true; aprovando = false; aprovandoRh = false; cadastrando = false;">
                                        Visualizar
                                    </a>
                                </div>
                            </div>


                            <!--                            <a-->
                            <!--                                v-if="permissoes.admissao_cih_aprovar"-->
                            <!--                                v-show="item.status.includes('aberto')"-->
                            <!--                                href="javascript://"-->
                            <!--                                class="btn btn-sm btn-primary"-->
                            <!--                                content="Aprovar/Reprovar"-->
                            <!--                                v-tippy-->
                            <!--                                @click.prevent="formAprovar(item.id); leitura = false"-->
                            <!--                                data-toggle="modal"-->
                            <!--                                data-target="#janelaCadastrar"-->
                            <!--                            >-->
                            <!--                                <i class="fa fa-check"></i>-->
                            <!--                            </a>-->

                            <!--                            <a-->
                            <!--                                v-show="item.status.includes('aprovado') || item.status.includes('reprovado')"-->
                            <!--                                href="javascript://"-->
                            <!--                                class="btn btn-sm btn-primary"-->
                            <!--                                content="Visualizar"-->
                            <!--                                v-tippy-->
                            <!--                                @click.prevent="formAprovar(item.id); leitura = true"-->
                            <!--                                data-toggle="modal"-->
                            <!--                                data-target="#janelaCadastrar"-->
                            <!--                            >-->
                            <!--                                <i class="fa fa-search"></i>-->
                            <!--                            </a>-->
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            >
            </controle-paginacao>
        </div>
    </div>
</template>
<script>
import gestoraprovacao from "../../GestorAprovacao";
import autocomplete from "../../AutoComplete";
import DatePicker from "../../DatePicker";
import Upload from "../../Upload";
import ControlePaginacao from "../../ControlePaginacao";
import ExportacaoMixin from "../../../mixins/Exportacoes";
import Validacoes from "../../../mixins/Validacoes";

export default {
    name: "CIH",
    components: {
        autocomplete,
        DatePicker,
        Upload,
        ControlePaginacao,
        gestoraprovacao
    },
    mixins: [ExportacaoMixin, Validacoes],
    filters: {
        capitalize(value) {
            if (!value) return "";
            value = value.toString();
            return value.charAt(0).toUpperCase() + value.slice(1);
        }
    },
    data() {
        return {
            tituloJanela: "Cadastrando CIH",
            preloadAjax: false,
            editando: false,
            leitura: false,
            apagado: false,
            cadastrando: false,
            visualizar: false,
            aprovando: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            preloadExportacao: false,

            csrf: CSRF_token,

            colaborador_ativo: `autocomplete/colaboradorCih`,
            todos_municipios: `autocomplete/todos-municipios`,

            urlPdf: `${URL_ADMIN}/apontamento/cih/gerapdf`,
            urlExportacao: `${URL_ADMIN}/apontamento/cih/export`,
            urlAtualizar: `${URL_ADMIN}/apontamento/cih/atualizar`,
            selecionados: [],

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            datarelatorio: "",
            tipoRelatorio: "pdf",
            cliente_relatorio: "",

            hoje: "",

            permissoes: {
                admissao_cih_lancar: false,
                admissao_cih_aprovar: false,
                admissao_cih_privilegio_adm: false,
                aprovar_por_gestor: false,
                aprovar_por_rh: false
            },

            config_modelo_cih: "",
            centros_de_custo: [],
            gestores: [],

            form: {
                tag_id: "",
                outra_tag: "",
                feedback_id: "",
                colaboradores: [],
                colaboradoresDelete: [],
                autocomplete_label_colaborador: "",
                autocomplete_label_colaborador_anterior: "",
                cliente_id: "",
                area_id: "",
                centro_custo_id: "",
                varios_colaboradores: false,
                colaboradores_avulso: "",
                outra_area: "",
                acao: "",
                user_lancamento_id: "",
                obs_lancamento: "",
                data_lancamento: "",
                user_aprovacao_id: "",
                obs_aprovacao: "",
                data_aprovacao: "",
                status: "",
                status_aprovacao: "",
                resposta_rh: "",
                obs_rh: "",
                anexos: [],
                anexosDel: [],

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',
            },

            url_anexo: `${URL_ADMIN}/apontamento/cih/uploadAnexos`,
            anexoUploadAndamento: false,

            formDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            listaTags: [],
            listaAreas: [],
            listaClientes: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    campoStatus: "",
                    campoTags: "",
                    campoAreas: "",
                    campoCentrosDeCusto: "",
                    campoGestores: "",
                    filtroPeriodo: false,
                    periodo: "",
                    pages: 50
                }
            }
        };
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.atualizar();
        let inicio_de_mes = moment().startOf("month").format("DD/MM/YYYY");
        let fim_de_mes = moment().add(1, "M").endOf("month").format("DD/MM/YYYY");
        this.controle.dados.periodo = `${inicio_de_mes} até ${fim_de_mes}`;
    },
    computed: {
        // tudoMarcado() {
        //     let totalItens = this.comTeste.length;
        //     let totalEncontrado = 0;
        //
        //     if (totalItens === 0) {
        //         return false;
        //     }
        //
        //     this.comTeste.forEach(item => {
        //         let id = item.curriculo_id;
        //         if (this.selecionados.indexOf(id) >= 0) {
        //             totalEncontrado++;
        //             //faz nada
        //         } else {
        //             return false;
        //         }
        //     });
        //     let resultado = totalItens === totalEncontrado;
        //     this.selecionaTudo = resultado;
        //     return resultado;
        // }
        paramsExport() {
            return {
                campoCentrosDeCusto: this.controle.dados.campoCentrosDeCusto,
                campoStatus: this.controle.dados.campoStatus,
                campoTags: this.controle.dados.campoTags,
                campoAreas: this.controle.dados.campoAreas,
                campoGestores: this.controle.dados.campoGestores,
                filtroPeriodo: this.controle.dados.filtroPeriodo,
                periodo: this.controle.dados.periodo,
            }
        },
    },
    methods: {
        exportaExcel() {
            if (!this.controle.dados.filtroPeriodo) {
                mostraErro("", "Selecione um periodo por favor!");
                return false;
            }
            this.preloadExportacao = true;
            mostraSucesso("Estamos gerando seu arquivo excel, assim que finalizado você será notificado.");
            setTimeout(() => {
                this.preloadExportacao = false;
            }, 500);
            axios.post(`${this.urlExportacao}`,
                this.paramsExport
            ).then(({data}) => {
                this.preloadExportacao = false;
            }).catch(erro => {
                mostraErro(erro);
                this.preloadExportacao = false;
            });
        },

        exportaPdf() {
            if (!this.controle.dados.filtroPeriodo) {
                mostraErro("", "Selecione um periodo por favor!");
                return false;
            }
            this.preloadExportacao = true;
            mostraSucesso("Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.");
            setTimeout(() => {
                this.preloadExportacao = false;
            }, 500);
            axios.post(`${this.urlPdf}`,
                this.paramsExport
            ).then(({data}) => {
                this.preloadExportacao = false;
            }).catch(erro => {
                mostraErro(erro);
                this.preloadExportacao = false;
            });
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comTeste.map((item) => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id);
                    }
                });
            } else {
                this.comTeste.map((item) => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1);
                    }
                });
            }
        },
        removerLIColaborador(index) {
            if (!this.form.colaboradores[index].novo) {
                this.form.colaboradoresDelete.push(this.form.colaboradores[index].id);
            }
            this.form.colaboradores.splice(index, 1);
        },
        selecionaColaborador(obj) {
            // this.form.feedback_id = obj.id;
            // this.form.cliente_id = obj.cliente_id;
            // this.form.autocomplete_label_colaborador = obj.label;
            // this.form.autocomplete_label_colaborador_anterior = obj.label;

            const colaborador = {};

            Object.assign(colaborador, obj);
            colaborador.novo = true;


            let atual = this.form.colaboradores.findIndex(val => val.id === colaborador.id);

            if (atual < 0) {//Se não existir ainda no array
                this.form.colaboradores.push(colaborador);
            } else {
                mostraErro("", `O colaborador(a) ${colaborador.nome} já está na lista.`);
                this.form.autocomplete_label_colaborador = "";
                return false;
            }
            this.form.autocomplete_label_colaborador = "";
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = "";
                this.form.autocomplete_label_colaborador = "";
                this.form.feedback_id = "";
                this.form.cliente_id = "";

                setTimeout(() => {
                    if (this.form.feedback_id === "") {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        // $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                        $(`#janelaCadastrar #colaborador_${this.hash}`).focus().trigger("blur");
                        mostraErro("Erro", "O Campo Vaga não pode ficar vazio");
                    }
                }, 100);
            }
        },

        formNovo() {
            formReset();
            setupCampo();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;
            this.tituloJanela = "Cadastrando CIH";
            this.form = _.cloneDeep(this.formDefault); //copia
            this.form.status = "aberto";
        },
        cadastrar() {
            formReset();
            this.validaBlur();
            this.$nextTick(() => {
                $("#janelaCadastrar :input:enabled").trigger("blur");
                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }
                if (!this.form.colaboradores) {
                    this.mostraErro("", "Adcione o colaborador");
                    return false;
                }

                let tag_selecionada = this.form.tag_id !== 0 ? this.listaTags.find((item) => item.id === this.form.tag_id) : 0;
                if (tag_selecionada.anexo_obrigatorio) {
                    if (this.form.anexos.length === 0) {
                        this.mostraErro("", "O Campo Anexo não pode ficar vazio");
                        return false;
                    }
                }
                this.preloadAjax = true;
                this.form.status = "aberto";

                axios
                    .post(`${URL_ADMIN}/apontamento/cih`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            $("#janelaCadastrar").modal("hide");
                            this.mostraSucesso("", "Ocorrência cadastrada com sucesso");
                            this.preloadAjax = false;
                            this.cadastrado = true;
                            this.atualizar();
                        }
                    })
                    .catch((error) => (this.preloadAjax = false));
            });
        },
        formAlterar(id) {
            formReset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Alterando CIH #${id}`;
            this.preloadAjax = true;

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data);
                    // this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                })
                .catch((error) => (this.preloadAjax = false));
        },
        alterar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;

            axios
                .put(`${URL_ADMIN}/apontamento/cih/${this.form.id}`, this.form)
                .then((response) => {
                    $("#janelaCadastrar").modal("hide");
                    mostraSucesso("", "Ocorrência alterada com sucesso!");
                    this.preloadAjax = false;
                    this.atualizado = true;
                    this.atualizar();
                })
                .catch((error) => (this.preloadAjax = false));
        },

        formAprovar(id) {
            formReset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Aprovando CIH #${id}`;
            this.preloadAjax = true;

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data);
                    this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.form.resposta_rh = this.form.resposta_rh === null ? "" : this.form.resposta_rh;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                })
                .catch((error) => (this.preloadAjax = false));
        },
        aprovar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;
            axios
                .put(`${URL_ADMIN}/apontamento/cih/aprovar/${this.form.id}`, this.form)
                .then((response) => {
                    $("#janelaCadastrar").modal("hide");
                    mostraSucesso("", "Ocorrência alterada com sucesso!");
                    this.preloadAjax = false;
                    this.atualizado = true;
                    this.atualizar();
                })
                .catch((error) => (this.preloadAjax = false));
        },

        gerarPdf() {
            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/apontamento/cih/gerapdf`).then((response) => {
                this.preloadAjax = false;
                window.open(response.data.url, "_blank");
            }).catch((error) => (this.preloadAjax = false));
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.listaTags = dados.tags;
            this.listaAreas = dados.areas;
            this.centros_de_custo = dados.centros_de_custo;
            this.gestores = dados.gestores;
            this.datarelatorio = dados.intervalo;
            this.hoje = dados.hoje;
            this.permissoes = dados.permissoes;
            this.config_modelo_cih = dados.config_modelo_cih;
            this.controle.carregando = false;
            this.aprovaGestor = this.permissoes.aprovar_por_gestor;
            this.aprovaRh = this.permissoes.aprovar_por_rh;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
};
</script>
