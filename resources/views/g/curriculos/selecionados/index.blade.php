@extends('layouts.sistema')
@section('title', 'Curriculo')
@section('content_header')
    <h4 class="text-default">SELECIONADOS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
        <template #conteudo>
            <preload v-show="preloadAjax"></preload>
            <div v-if="!preloadAjax && (!cadastrado && !atualizado) && form.id !== ''"
                 class="mybp-modal-form mybp-filtros-compactos">

                <fieldset class="mybp-modal-secao">
                    <legend>Dados pessoais</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nome</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.curriculo?.nome || ''">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nascimento</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.curriculo?.nascimento || ''">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Idade</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.curriculo?.idade ? form.curriculo.idade + ' anos' : ''">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Contato</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.tel_principal ? form.tel_principal.numero : 'não informado'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">E-mail</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.curriculo?.email || ''">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">PCD</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.curriculo?.pcd ? 'Sim' : 'Não'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Vaga</label>
                                <input type="text" class="form-control form-control-sm" disabled :value="vagaLabelModal">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Disponibilidade sábado</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="textoSimNao(form.curriculo?.disponibilidade_sabado)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Disponibilidade domingo</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="textoSimNao(form.curriculo?.disponibilidade_domingo)">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Endereço</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.curriculo?.endereco_completo || ''">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Escolaridade</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="formacaoTipo + (form.formacao_curso ? ' (' + form.formacao_curso + ')' : '')">
                            </div>
                        </div>
                    </div>

                    <fieldset class="mybp-modal-secao mt-2" v-if="form.vinculo">
                        <legend>Vínculo</legend>
                        <p class="mb-1 small">
                            Têm parentes trabalhando no Grupo Equatorial?
                            <strong>@{{ form.vinculo.parente ? 'Sim' : 'Não' }}</strong>
                        </p>
                        <template v-if="form.vinculo.parente">
                            <p class="mb-0 small">Nome: <strong>@{{ form.vinculo.nome }}</strong></p>
                            <p class="mb-0 small">Função: <strong>@{{ form.vinculo.funcao }}</strong></p>
                            <p class="mb-1 small">Grau: <strong>@{{ form.vinculo.grau_parentesco }}</strong></p>
                        </template>
                        <p class="mb-0 small">
                            Já foi empregado no Grupo Equatorial ou empresas parceiras:
                            <strong>@{{ form.vinculo.foi_empregado ? 'Sim' : 'Não' }}</strong>
                        </p>
                        <p class="mb-0 small" v-if="form.vinculo.foi_empregado">
                            Local: <strong>@{{ form.vinculo.local_empregado }}</strong>
                        </p>
                    </fieldset>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-for="prova in form.simulados" :key="'prova-modal-' + prova.id">
                    <legend>
                        Teste: @{{ prova.simulado_vaga?.simulado?.titulo }}
                        (@{{ prova.simulado_vaga?.online ? 'Online' : 'Presencial' }})
                    </legend>
                    <div v-show="prova.finalizado">
                        <p class="small mb-2">
                            Acertos: @{{ prova.acertos }} · Tempo restante: @{{ prova.duracao_segundos }} min ·
                            Finalizado em @{{ prova.data_finalizacao }}h
                        </p>
                        <div v-show="!prova.status">
                            <classificar :usuario_id="{{intval(auth()->id())}}" :model="form" :simulado_id="prova.id"
                                         @salvou="atualizaClassificacao"></classificar>
                        </div>
                        <div class="text-uppercase alert"
                             :class="prova.status === 'desclassificado' ? 'alert-danger' : 'alert-success'"
                             v-show="prova.status">
                            @{{ prova.status }}
                        </div>
                    </div>
                    <div v-show="!prova.finalizado">
                        <small class="alert alert-warning d-inline-block mb-0">Prova em Andamento</small>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="form.etapa_status && form.etapa_status.length > 0">
                    <legend>Histórico</legend>
                    <div class="card mb-2" v-for="etapa in form.etapa_status" :key="'etapa-' + etapa.id">
                        <div class="card-header" :class="headerEtapaClass(etapa)">
                            @{{ etapa.etapa }}
                        </div>
                        <div class="card-body py-2">
                            <p class="mb-0 small">
                                <strong>Status:</strong> @{{ etapa.status }}<br>
                                <strong>Preenchido por:</strong> @{{ etapa.preenchido_por }}<br>
                                <strong>Data:</strong> @{{ etapa.created_at }}<br>
                                <strong>Observação:</strong> @{{ etapa.observacao }}
                            </p>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Carta Oferta</legend>
                    <div v-if="form.carta_resumo" class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Status</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.carta_resumo.texto">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Enviado em</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.carta_resumo.envio_br || 'Não informado'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Resposta</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.carta_resumo.resposta_br || 'Aguardando'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Expirado</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.carta_resumo.expirado ? 'Sim' : 'Não'">
                            </div>
                        </div>
                    </div>
                    <p class="small text-muted mb-2" v-else>Nenhuma carta oferta vinculada a este selecionado.</p>
                    @can('admissao_documentos_carta_oferta')
                        <a class="btn btn-sm btn-outline-primary" :href="urlGerenciarCarta()" target="_blank">
                            <i class="fas fa-file-signature mr-1"></i> Abrir / Gerenciar carta oferta
                        </a>
                    @endcan
                </fieldset>

                <div class="mt-2" v-if="podeClassificar(form)">
                    <classificar :usuario_id="{{intval(auth()->id())}}" :model="form" janelapai="janelaCadastrar"
                                 @salvou="atualizaClassificacao"></classificar>
                </div>
            </div>
        </template>
    </modal>

    <modal ref="janelaWhatsApp" id="janelaWhatsApp" titulo="Enviar Notificação WhatsApp" :fechar="!preloadAjax">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <fieldset class="mybp-modal-secao" v-if="!preloadAjax">
                <legend>Dados pessoais</legend>
                <p class="small mb-0">
                    Nome: @{{form.curriculo?.nome}} | Nascimento: @{{form.curriculo?.nascimento}} |
                    Idade: @{{form.curriculo?.idade}} anos<br>
                    Contato: @{{form.tel_principal ? form.tel_principal.numero : 'não informado'}} |
                    E-mail: @{{form.curriculo?.email}}
                </p>
            </fieldset>
        </template>
        <template #rodape>
            <button class="btn btn-primary"
                    @click.prevent="enviarNotificacao(
                        form.tel_principal?.sonumero,
                        form.curriculo?.nome,
                        form.curriculo_id || form.curriculo?.id,
                        form.vaga_id || form.vaga_aberta?.id,
                        form.etapa_status?.[0]?.id
                    )"
                    v-if="!preloadAjax && form.tel_principal">
                Enviar Notificação
            </button>
        </template>
    </modal>

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
        :desabilitado="controle.carregando"
        @submit="atualizar"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <date-range-filter
                :key="'sel-filtro-periodo'"
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="!!controle.carregando"
                :id-suffix="'periodo-' + hash"
                label="Por período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="sel-filtro-busca">
                        Candidato / CPF
                        <span v-if="buscaUnificadaEhCpf" class="text-muted small">CPF</span>
                    </label>
                    <input
                        id="sel-filtro-busca"
                        type="text"
                        placeholder="Nome, código ou CPF"
                        autocomplete="off"
                        inputmode="search"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        :value="campoBuscaUnificada"
                        @input="onInputBuscaUnificada"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label">Cargo</label>
                    <autocomplete
                        :caminho="controle.dados.caminho_autocomplete"
                        :valido="controle.dados.campoVaga !== ''"
                        :formsm="true"
                        v-model="controle.dados.autocomplete_label"
                        :disabled="controle.carregando"
                        placeholder="Por cargo"
                        @onblur="resetaCampo"
                        @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="sel-filtro-uf">Estado</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroUf"
                            instance-id="sel-filtro-uf"
                            input-id="sel-filtro-uf"
                            v-model="controle.dados.campoUf"
                            :options="filtroUfOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os estados"
                            empty-message="Nenhum estado encontrado."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes('sel-filtro-uf')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="sel-filtro-status">Status</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroStatus"
                            instance-id="sel-filtro-status"
                            input-id="sel-filtro-status"
                            v-model="controle.dados.campoStatus"
                            :options="filtroStatusOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Sem filtro"
                            empty-message="Nenhuma opção."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('sel-filtro-status')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="sel-filtro-pcd">PCD</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPcd"
                            instance-id="sel-filtro-pcd"
                            input-id="sel-filtro-pcd"
                            v-model="controle.dados.campoPcd"
                            :options="filtroSimNaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Geral"
                            empty-message="Nenhuma opção."
                            :max-results="5"
                            @opening="fecharOutrosComboboxes('sel-filtro-pcd')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="sel-filtro-pages">Exibir</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPages"
                            instance-id="sel-filtro-pages"
                            input-id="sel-filtro-pages"
                            v-model="campoPagesCombo"
                            :options="filtroPagesOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="20"
                            empty-message="Nenhuma opção."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('sel-filtro-pages')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div
                class="col-12 mybp-filtros-avancados-shell"
                :class="{ 'is-open': filtrosAvancadosAbertos }"
                :aria-hidden="filtrosAvancadosAbertos ? 'false' : 'true'"
            >
                <div class="mybp-filtros-avancados-shell__inner">
                    <div class="mybp-filtros-avancados">
                        <div class="mybp-filtros-avancados__block">
                            <p class="mybp-filtros-avancados__title">Outros filtros</p>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-prova">Prova</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroProva"
                                                instance-id="sel-filtro-prova"
                                                input-id="sel-filtro-prova"
                                                v-model="controle.dados.campoProvas"
                                                :options="filtroProvaOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('sel-filtro-prova')"
                                                @select="onSelectProva"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4" v-show="controle.dados.campoProvas === 'sim'">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-nota">Nota</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroNota"
                                                instance-id="sel-filtro-nota"
                                                input-id="sel-filtro-nota"
                                                v-model="controle.dados.campoNota"
                                                :options="filtroNotaOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('sel-filtro-nota')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-etapa">Etapa</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroEtapa"
                                                instance-id="sel-filtro-etapa"
                                                input-id="sel-filtro-etapa"
                                                v-model="controle.dados.campoEtapa"
                                                :options="filtroEtapaOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="20"
                                                @opening="fecharOutrosComboboxes('sel-filtro-etapa')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-pendencia">Pendência workflow</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroPendencia"
                                                instance-id="sel-filtro-pendencia"
                                                input-id="sel-filtro-pendencia"
                                                v-model="controle.dados.campoPendenciaWorkflow"
                                                :options="filtroPendenciaWorkflowOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="15"
                                                @opening="fecharOutrosComboboxes('sel-filtro-pendencia')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-carta">Carta Oferta</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroCarta"
                                                instance-id="sel-filtro-carta"
                                                input-id="sel-filtro-carta"
                                                v-model="controle.dados.campoCartaOferta"
                                                :options="filtroCartaOfertaOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('sel-filtro-carta')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-sabado">Disp. sábado</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroSabado"
                                                instance-id="sel-filtro-sabado"
                                                input-id="sel-filtro-sabado"
                                                v-model="controle.dados.campoDisponibilidadeSabado"
                                                :options="filtroSimNaoOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Geral"
                                                empty-message="Nenhuma opção."
                                                :max-results="5"
                                                @opening="fecharOutrosComboboxes('sel-filtro-sabado')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="sel-filtro-domingo">Disp. domingo</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroDomingo"
                                                instance-id="sel-filtro-domingo"
                                                input-id="sel-filtro-domingo"
                                                v-model="controle.dados.campoDisponibilidadeDomingo"
                                                :options="filtroSimNaoOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Geral"
                                                empty-message="Nenhuma opção."
                                                :max-results="5"
                                                @opening="fecharOutrosComboboxes('sel-filtro-domingo')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                Buscar
            </button>
            <button
                type="button"
                class="btn btn-sm mybp-btn-mais-filtros"
                :class="filtrosAvancadosAbertos ? 'btn-primary is-open' : 'btn-outline-primary'"
                :disabled="controle.carregando"
                :aria-expanded="filtrosAvancadosAbertos ? 'true' : 'false'"
                @click="filtrosAvancadosAbertos = !filtrosAvancadosAbertos"
            >
                <i class="fa" :class="filtrosAvancadosAbertos ? 'fa-chevron-up' : 'fa-sliders-h'"></i>
                @{{ filtrosAvancadosAbertos ? 'Menos filtros' : 'Mais filtros' }}
                <span class="badge badge-light ml-1" v-if="totalFiltrosAtivosAvancados">
                    @{{ totalFiltrosAtivosAvancados }}
                </span>
            </button>
        </template>
    </filtro-listagem>

    <p class="text-center" v-if="controle.carregando">
        <preload></preload>
    </p>

    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo" v-show="!controle.carregando && lista.length > 0">
        <div class="mybp-cards-lista">
            <div class="mybp-card" v-for="feedback in lista" :key="feedback.id">
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#@{{ feedback.id }}</span>
                        <div class="mybp-card-titulo">
                            <strong>@{{ feedback.curriculo?.nome || 'Não informado' }}</strong>
                            <span class="text-muted small d-block" v-if="cliente_id === 1">
                                @{{ feedback.cliente?.razao_social }}
                            </span>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <mybp-status-badge
                            :variante="chaveStatusSel(feedback)"
                            :texto="textoStatusSel(feedback)"
                        ></mybp-status-badge>
                        <div class="dropdown" :class="{ show: isDropdownOpen(feedback.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                :id="'sel-acoes-' + feedback.id"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(feedback.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(feedback.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(feedback.id) }"
                                :aria-labelledby="'sel-acoes-' + feedback.id"
                                @click="fecharDropdown"
                            >
                                <a class="dropdown-item" href="javascript://"
                                   @click.prevent="abrirVisualizar(feedback.id)">
                                    <i class="fa fa-eye mr-1"></i> Visualizar / Classificar
                                </a>
                                <a
                                    class="dropdown-item"
                                    :href="urlPdfCurriculo(feedback)"
                                    target="_blank"
                                    v-if="feedback.curriculo_ctoken"
                                >
                                    <i class="far fa-file-pdf mr-1"></i> Gerar PDF
                                </a>
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    @click.prevent="toggle(feedback.id)"
                                >
                                    <i class="far mr-1"
                                       :class="opened.includes(feedback.id) ? 'fa-minus-square' : 'fa-plus-square'"></i>
                                    @{{ opened.includes(feedback.id) ? 'Ocultar detalhes' : 'Expandir detalhes' }}
                                </a>
                                @can('admissao_documentos_carta_oferta')
                                    <a
                                        class="dropdown-item"
                                        :href="urlCartaOferta"
                                        target="_blank"
                                    >
                                        <i class="fas fa-file-signature mr-1"></i> Carta oferta
                                    </a>
                                @endcan
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    v-if="podeWhatsApp(feedback)"
                                    @click.prevent="abrirWhatsApp(feedback)"
                                >
                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mybp-card-corpo" :class="'mybp-card-corpo--' + chaveStatusSel(feedback)">
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-id-card"
                                label="CPF"
                                :valor="feedback.curriculo?.cpf"
                                forte
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-calendar-alt"
                                label="Data seleção"
                                :valor="feedback.data_selecao_br"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Vaga"
                                :valor="labelVagaCard(feedback)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-user-check"
                                label="Selecionado"
                                :valor="textoSelecionadoCard(feedback)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-clipboard-check"
                                label="Prova"
                                :valor="feedback.prova_label || '-'"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-stream"
                                label="Etapa / Status"
                                :valor="textoEtapaStatusCard(feedback)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-phone"
                                label="Contato"
                                :valor="textoContatoCard(feedback)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-envelope"
                                label="E-mail"
                                :valor="feedback.curriculo?.email"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-wheelchair"
                                label="PCD"
                                :valor="feedback.curriculo?.pcd ? 'Sim' : 'Não'"
                                :tom="feedback.curriculo?.pcd ? 'positivo' : 'meta'"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-file-signature"
                                label="Carta Oferta"
                                :valor="textoCartaSel(feedback)"
                                :tom="tomCartaSel(feedback)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-calendar-day"
                                label="Disp. sábado"
                                :valor="textoSimNao(feedback.curriculo?.disponibilidade_sabado)"
                                :tom="tomSimNao(feedback.curriculo?.disponibilidade_sabado)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-calendar-week"
                                label="Disp. domingo"
                                :valor="textoSimNao(feedback.curriculo?.disponibilidade_domingo)"
                                :tom="tomSimNao(feedback.curriculo?.disponibilidade_domingo)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <div class="mb-2" v-if="pendenciasObrigatorias(feedback).length">
                        <small class="text-muted d-block mb-1">Pendências obrigatórias</small>
                        <span
                            class="badge badge-warning mr-1 mb-1"
                            v-for="p in pendenciasObrigatorias(feedback)"
                            :key="'pend-' + feedback.id + '-' + p.id"
                        >
                            @{{ p.nome }}
                        </span>
                    </div>
                    <div class="mb-2" v-else-if="prontoParaAdmissao(feedback)">
                        <span class="badge badge-success">Pronto para admissão</span>
                    </div>

                    <div class="d-flex flex-wrap align-items-center mt-2 mb-1" style="gap: .4rem;">
                        <button type="button" class="btn btn-sm btn-primary"
                                @click.prevent="abrirVisualizar(feedback.id)">
                            <i class="fa fa-eye"></i> Visualizar
                        </button>
                        <button type="button" class="btn btn-sm btn-success"
                                v-if="podeClassificar(feedback)"
                                @click.prevent="abrirVisualizar(feedback.id)">
                            <i class="fa fa-check"></i> Classificar
                        </button>
                        <a
                            class="btn btn-sm btn-outline-info"
                            v-for="cta in ctasSistemaPendentes(feedback)"
                            :key="'cta-' + feedback.id + '-' + cta.chave"
                            :href="urlModuloWorkflow(cta.chave)"
                            target="_blank"
                        >
                            <i class="fa fa-external-link-alt"></i> @{{ cta.nome }}
                        </a>
                        <a class="btn btn-sm btn-outline-primary"
                           :href="urlPdfCurriculo(feedback)"
                           target="_blank"
                           v-if="feedback.curriculo_ctoken">
                            <i class="far fa-file-pdf"></i> PDF
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                @click.prevent="toggle(feedback.id)">
                            <i class="far"
                               :class="opened.includes(feedback.id) ? 'fa-minus-square' : 'fa-plus-square'"></i>
                            @{{ opened.includes(feedback.id) ? 'Ocultar' : 'Detalhes' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-success"
                                v-if="podeWhatsApp(feedback)"
                                @click.prevent="abrirWhatsApp(feedback)">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                    </div>

                    <div class="mt-2" v-if="opened.includes(feedback.id)">
                        <fieldset v-if="feedback.simulados?.length"
                                  v-for="prova in feedback.simulados" :key="'prova-card-' + prova.id">
                            <legend>
                                Teste: @{{ prova.simulado_vaga?.simulado?.titulo }}
                                (@{{ prova.simulado_vaga?.online ? 'Online' : 'Presencial' }})
                            </legend>
                            <div v-show="prova.finalizado">
                                <p class="small mb-2">
                                    Acertos: @{{ prova.acertos }} · Tempo restante: @{{ prova.duracao_segundos }} min ·
                                    Finalizado em @{{ prova.data_finalizacao }}h
                                </p>
                                <div v-show="!prova.status" v-if="podeClassificar(feedback)">
                                    <classificar :usuario_id="{{intval(auth()->id())}}" :model="feedback"
                                                 :simulado_id="prova.id"
                                                 @salvou="atualizaClassificacao"></classificar>
                                </div>
                                <div class="text-uppercase alert"
                                     :class="prova.status === 'desclassificado' ? 'alert-danger' : 'alert-success'"
                                     v-show="prova.status">
                                    @{{ prova.status }}
                                </div>
                            </div>
                            <div v-show="!prova.finalizado">
                                <small class="alert alert-warning d-inline-block mb-0">Prova em Andamento</small>
                            </div>
                        </fieldset>
                        <div class="alert alert-light border small mb-0" v-if="!feedback.simulados?.length">
                            Nenhuma prova vinculada. Use <strong>Visualizar / Classificar</strong> para avançar a etapa.
                        </div>
                        <div class="mt-2" v-if="podeClassificar(feedback) && (!feedback.simulados || !feedback.simulados.length)">
                            <classificar :usuario_id="{{intval(auth()->id())}}" :model="feedback"
                                         @salvou="atualizaClassificacao"></classificar>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.curriculoselecao.curriculos-selecionados.atualizar')}}"
                            :por-pagina="controle.dados.pages"
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
