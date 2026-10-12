@extends('layouts.sistema')
@section('title', 'Documentos - Carta Oferta')
@section('content_header')
    <h4 class="text-default">Documentos - Carta Oferta</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal ref="janelaRecusar" modal-pai="janelaVisualizar" titulo="Recusar Carta Oferta"
           :fechar="!atualizando"
           label-fechar="NÃO"
           id="janelaRecusar">
        <template #conteudo>
            <preload v-if="atualizando"></preload>
            <div v-if="objopen" class="text-center">
                <h5>Você tem certeza que deseja recusar a carta oferta de <br>
                    <strong class="text-danger">@{{ nomeCandidato(objopen) }}?</strong>
                </h5>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-outline-danger" v-if="!atualizando"
                    @click.prevent="responder(objopen,'Recusado pelo RH')"
            > sim, recusar
            </button>
        </template>
    </modal>

    <modal ref="janelaVisualizar" id="janelaVisualizar" :titulo="tituloJanela" size="g">
        <template #conteudo>
            <preload class="mt-2 text-center" v-if="preload"></preload>

            <div class="mybp-modal-form mybp-filtros-compactos" v-if="objopen && !preload">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap: 8px;">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="mybp-badge-id">#@{{ objopen.id }}</span>
                        <mybp-status-badge
                            :variante="chaveStatus(objopen.status)"
                            :texto="textoStatus(objopen.status)"
                        ></mybp-status-badge>
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 6px;" v-if="objopen.link_publico || objopen.anexo">
                        <button
                            v-if="objopen.link_publico"
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            @click.prevent="copiarLinkPublico(objopen)"
                        >
                            <i class="fas fa-link"></i> Copiar link
                        </button>
                        <a
                            v-if="objopen.anexo?.urlDownload"
                            class="btn btn-sm btn-outline-primary"
                            :href="objopen.anexo.urlDownload"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="fas fa-download"></i> Baixar PDF
                        </a>
                    </div>
                </div>

                <fieldset class="mybp-modal-secao">
                    <legend>Candidato</legend>
                    <div class="mybp-card-row">
                        <mybp-card-campo
                            icon="fas fa-user"
                            label="Nome"
                            :valor="nomeCandidato(objopen)"
                            :forte="true"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-id-card"
                            label="CPF"
                            :valor="cpfExibicao(objopen)"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-envelope"
                            label="E-mail"
                            :valor="emailExibicao(objopen)"
                        ></mybp-card-campo>
                    </div>
                    <div class="mybp-card-row mt-2">
                        <mybp-card-campo
                            icon="fas fa-phone"
                            label="Telefone"
                            :valor="telefoneExibicao(objopen)"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-user-edit"
                            label="Criado por"
                            :valor="textoCampo(objopen.criador_nome)"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-clock"
                            label="Envio"
                            :valor="textoCampo(objopen.envio_em)"
                        ></mybp-card-campo>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Oferta</legend>
                    <div class="mybp-card-row">
                        <mybp-card-campo
                            icon="fas fa-briefcase"
                            label="Cargo"
                            :valor="nomeCargo(objopen)"
                            :forte="true"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-building"
                            label="Setor"
                            :valor="textoCampo(objopen.setor)"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-money-bill-wave"
                            label="Salário"
                            :valor="textoCampo(objopen.salario)"
                        ></mybp-card-campo>
                    </div>
                    <div class="mybp-card-row mt-2">
                        <mybp-card-campo
                            icon="fas fa-calendar-day"
                            label="Data início"
                            :valor="textoCampo(objopen.data_inicio_exibicao)"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-project-diagram"
                            label="Projeto"
                            :valor="nomeProjeto(objopen)"
                        ></mybp-card-campo>
                        <mybp-card-campo
                            icon="fas fa-sync-alt"
                            label="Atualização"
                            :valor="textoCampo(objopen.atualizacao_exibicao)"
                        ></mybp-card-campo>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Documento PDF</legend>
                    <div v-if="abriupdf && objopen.anexo?.url">
                        <visualizador-pdf
                            :key="`carta-pdf-${objopen.id}-${objopen.anexo.url}`"
                            :url="objopen.anexo.url"
                            :urldownload="objopen.anexo.urlDownload || objopen.anexo.url"
                        ></visualizador-pdf>
                    </div>
                    <div v-else class="alert alert-secondary mb-0">
                        <i class="fas fa-file-pdf mr-1"></i>
                        Ainda não há PDF anexado/assinado nesta carta.
                    </div>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-success"
                    v-if="objopen?.status === 'Aguardando RH' && !atualizando"
                    @click.prevent="responder(objopen,'Aceito pelo RH')">
                <i class="fa fa-check"></i> Aprovar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-danger"
                    v-if="objopen?.status === 'Aguardando RH' && !atualizando"
                    @click="$refs.janelaRecusar?.abrirModal()"
            >
                <i class="fa fa-times"></i> Recusar
            </button>
        </template>
    </modal>

    <modal ref="janelaNovaCarta" id="janelaNovaCarta" titulo="Nova Carta Oferta" size="m"
           :fechar="!criando">
        <template #conteudo>
            <form class="mybp-modal-form mybp-filtros-compactos" @submit.prevent="criarCarta">
                <fieldset class="mybp-modal-secao">
                    <legend>Dados da oferta</legend>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="mybp-label">Candidato <span class="text-danger">*</span></label>
                            <autocomplete
                                :caminho="caminhoCurriculos"
                                v-model="nova.autocomplete_curriculo_label"
                                :disabled="criando"
                                placeholder="Buscar por nome ou CPF"
                                @onselect="selecionaCurriculo"
                            ></autocomplete>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="mybp-label">Vaga aberta <span class="text-danger">*</span></label>
                            <autocomplete
                                caminho="autocomplete/todas-vagas-ativas"
                                v-model="nova.autocomplete_vaga_label"
                                :disabled="criando"
                                placeholder="Buscar vaga"
                                @onselect="selecionaVagaNova"
                            ></autocomplete>
                        </div>
                        <div class="col-12 mb-2" v-if="(opcoesProjetoNova || []).length">
                            <label class="mybp-label">Projeto</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :input-id="'nova-projeto-' + hash"
                                    v-model="nova.vaga_projeto_id"
                                    :options="opcoesProjetoNova"
                                    :disabled="criando"
                                    placeholder-blur="Opcional — selecione o projeto"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="mybp-label">Setor</label>
                            <input type="text" class="form-control form-control-sm" v-model="nova.setor" :disabled="criando">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="mybp-label">Salário</label>
                            <input
                                type="text"
                                class="form-control form-control-sm text-right"
                                v-model="nova.salario"
                                v-mascara:dinheiro
                                :disabled="criando"
                                placeholder="0,00"
                            >
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-group mybp-filtro-campo mybp-modal-campo-data mb-0">
                                <label class="mybp-label">Data de início</label>
                                <datepicker
                                    formsm
                                    label=""
                                    class="corrigiDatepicker"
                                    v-model="nova.data_inicio"
                                    :disabled="criando"
                                    placeholder="dd/mm/aaaa"
                                ></datepicker>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        É necessário ter um template <strong>publicado</strong> em Administração → Carta Oferta.
                        Com assinatura digital habilitada, o PDF é enviado para o candidato assinar.
                    </p>
                </fieldset>
            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-primary" :disabled="criando" @click.prevent="criarCarta">
                <i class="fa" :class="criando ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                Criar e enviar
            </button>
        </template>
    </modal>

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        titulo="Filtros"
        :desabilitado="controle.carregando"
        :mostrar-limpar-filtros="temFiltrosAtivos"
        @submit="onSubmitFiltro"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <date-range-filter
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="controle.carregando"
                :id-suffix="'carta-' + hash"
                label="Período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="carta-filtro-busca">Buscar</label>
                    <input
                        id="carta-filtro-busca"
                        type="text"
                        placeholder="Nome ou CPF"
                        autocomplete="off"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        v-model="controle.dados.campoBusca"
                    >
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="carta-filtro-status">Status</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroStatus"
                            instance-id="filtro-status"
                            input-id="carta-filtro-status"
                            :options="filtroStatusOpcoes"
                            v-model="controle.dados.status"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="20"
                            @opening="fecharOutrosComboboxes('filtro-status')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="carta-filtro-order">Ordenar por</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroOrder"
                            instance-id="filtro-order"
                            input-id="carta-filtro-order"
                            :options="filtroOrderOpcoes"
                            v-model="controle.dados.order"
                            :disabled="controle.carregando"
                            placeholder-blur="Data de atualização"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('filtro-order')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="carta-filtro-pages">Exibir</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPages"
                            instance-id="filtro-pages"
                            input-id="carta-filtro-pages"
                            :options="filtroPagesOpcoes"
                            v-model="controle.dados.pages"
                            :disabled="controle.carregando"
                            placeholder-blur="50"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('filtro-pages')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
        </template>
        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                Buscar
            </button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="controle.carregando" @click="abrirNovaCarta">
                <i class="fa fa-plus"></i> Nova carta
            </button>
        </template>
    </filtro-listagem>

    <preload v-if="controle.carregando"></preload>

    <div id="conteudo" class="mt-3">
        <div class="alert alert-warning" v-show="!controle.carregando && !lista.length">
            <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado
        </div>

        <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length">
            <div
                class="mybp-card"
                v-for="item in lista"
                :key="item.id"
            >
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#@{{ item.id }}</span>
                        <div class="mybp-card-titulo mybp-card-titulo--stacked">
                            <strong>@{{ nomeCandidato(item) }}</strong>
                            <div class="text-muted small mybp-card-subtitulo" v-if="cpfExibicao(item)">
                                CPF @{{ cpfExibicao(item) }}
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <mybp-status-badge
                            :variante="chaveStatus(item.status)"
                            :texto="textoStatus(item.status)"
                        ></mybp-status-badge>
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                :id="`dropdownMenuLink_carta_${item.id}`"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(item.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(item.id) }"
                                :aria-labelledby="`dropdownMenuLink_carta_${item.id}`"
                                @click="fecharDropdown"
                            >
                                <a class="dropdown-item" href="javascript://"
                                   @click.prevent="formVisualizar(item); $refs.janelaVisualizar?.abrirModal()">
                                    <i class="fa fa-search-plus mr-1"></i> Ver / PDF
                                </a>
                                <a v-if="item.link_publico" class="dropdown-item" href="javascript://"
                                   @click.prevent="copiarLinkPublico(item)">
                                    <i class="fas fa-link mr-1"></i> Copiar link do candidato
                                </a>
                                <a v-if="item.link_publico" class="dropdown-item" :href="item.link_publico" target="_blank">
                                    <i class="fas fa-external-link-alt mr-1"></i> Abrir link público
                                </a>
                                <div class="dropdown-divider"
                                     v-if="item.status === 'Aguardando RH' || (assinaturaDigitalHabilitada && item.status === 'Pendente Anexo')"></div>
                                <a v-if="item.status === 'Aguardando RH'" class="dropdown-item text-success" href="javascript://"
                                   @click.prevent="formVisualizar(item); $refs.janelaVisualizar?.abrirModal()">
                                    <i class="fa fa-check mr-1"></i> Aprovar / Recusar
                                </a>
                                <a v-if="assinaturaDigitalHabilitada && item.status === 'Pendente Anexo' && temDocumentoAssinatura(item)"
                                   class="dropdown-item" href="javascript://"
                                   @click.prevent="abrirGerenciamentoAssinaturaCartaOferta(item)">
                                    <i class="fas fa-cog mr-1"></i> Gerenciar assinatura
                                </a>
                                <a v-else-if="assinaturaDigitalHabilitada && item.status === 'Pendente Anexo'"
                                   class="dropdown-item" href="javascript://"
                                   @click.prevent="abrirEnvioAssinaturaCartaOferta(item)">
                                    <i class="fas fa-pen-fancy mr-1"></i> Enviar assinatura
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mybp-card-corpo" :class="classeCorpoStatus(item.status)">
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Cargo"
                                :valor="textoCampo(item.cargo_nome)"
                                :forte="true"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-building"
                                label="Setor"
                                :valor="textoCampo(item.setor)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-money-bill-wave"
                                label="Salário"
                                :valor="textoCampo(item.salario)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-calendar-day"
                                label="Data início"
                                :valor="textoCampo(item.data_inicio_exibicao)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-project-diagram"
                                label="Projeto"
                                :valor="textoCampo(item.projeto_nome)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-clock"
                                label="Atualização"
                                :valor="textoCampo(item.atualizacao_exibicao)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-envelope"
                                label="E-mail"
                                :valor="textoCampo(item.contato_email)"
                            ></mybp-card-campo>
                            <div aria-hidden="true"></div>
                            <mybp-card-campo
                                icon="fas fa-phone"
                                label="Telefone"
                                :valor="textoCampo(item.contato_telefone)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao mybp-card-secao--fluxo">
                        <div class="mybp-card-secao__titulo">
                            <i class="fas fa-project-diagram" aria-hidden="true"></i> Fluxo da carta
                        </div>
                        <mybp-fluxo-aprovacao :steps="fluxoSteps(item)"></mybp-fluxo-aprovacao>
                    </section>
                </div>
            </div>
        </div>

        <acao-assinatura-documento
            ref="acaoAssinaturaCartaOferta"
            :id-prefix="`carta_oferta_${hash}`"
            :titulo-enviar="'Enviar para assinatura digital'"
            :get-nome-documento="getNomeDocumentoAssinaturaCartaOferta"
            :get-signatarios-iniciais="getSignatariosIniciaisAssinaturaCartaOferta"
            :enviar-handler="enviarAssinaturaCartaOferta"
            :atualizar-handler="atualizar">
        </acao-assinatura-documento>

        <controle-paginacao class="d-flex justify-content-center mt-3" id="controle" ref="componente"
                            :url="urlPaginacao"
                            :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/documentos/cartaoferta/app.js')}}"></script>
@endpush
