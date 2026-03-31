<template>
    <div :id="hash">
        <modal id="janelaAvaliacaoFinal" :titulo="titulo_janela_final" :size="90" ref="modal_janelaAvaliacaoFinal">
            <template #conteudo>
                <preload v-show="preloadAvalFinal"></preload>
                <div v-if="!preloadAvalFinal">
                    <fieldset>
                        <legend>{{ formAvaliarFinal.tipo_pj ? 'Dados do fornecedor' : 'Dados do colaborador' }}</legend>
                        <p class="ma-modal-lead">
                            {{
                                formAvaliarFinal.tipo_pj
                                    ? 'Identificação utilizada neste ciclo de avaliação.'
                                    : 'Informações cadastrais da pessoa avaliada neste ciclo.'
                            }}
                        </p>
                        <div class="row mb-3" v-if="formAvaliarFinal.dados_do_funcionario.cnpj_lotacao">
                            <div class="col-12">
                                <strong>CNPJ:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.cnpj_lotacao.razao_social }}
                                ({{ formAvaliarFinal.dados_do_funcionario.pertence_filial ? 'Filial' : 'Matriz' }})
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-4">
                                <strong>Nome:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.nome }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliarFinal.tipo_pj">
                                <strong>Matrícula:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.matricula }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliarFinal.tipo_pj">
                                <strong>Admissão:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.data_admissao }}
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 col-lg-4" v-if="!formAvaliarFinal.tipo_pj">
                                <strong>Cargo:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.cargo }}
                            </div>
                            <div class="col-12 col-lg-4">
                                <strong>Centro de Custo:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.centro_custo }}
                            </div>

                            <div class="col-12 col-lg-4">
                                <strong>Área:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.area }}
                            </div>
                        </div>
                    </fieldset>

                    <template v-if="formAvaliarFinal.result_topico_pai_agrupado && formAvaliarFinal.result_topico_pai_agrupado.length > 0">
                        <h6 class="ma-modal-section-title mt-3 mb-2"><i class="fa fa-table mr-2 text-primary"></i>Resultado por competência</h6>
                        <p class="ma-modal-lead ma-modal-lead--tight mb-3">
                            Consolidado das notas informadas em cada etapa do fluxo, com a média calculada por critério.
                        </p>
                        <table class="table" v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado" :key="index">
                            <thead>
                                <tr>
                                    <!-- CORREÇÃO APLICADA: Mudança de item[index] para item[0] com guard -->
                                    <th>{{ (item[0] || {}).topico_pai || '' }}</th>
                                    <!-- CORREÇÃO APLICADA: Adicionado guard para avaliadores -->
                                    <th class="text-center" v-for="(avaliador, id) in (item[0] || {}).avaliadores || []" :key="avaliador.id">
                                        <span>
                                            {{ tituloEtapaFluxoColuna(id, avaliador) }}
                                        </span>
                                    </th>
                                    <th class="text-center">Média</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- CORREÇÃO APLICADA: Substituído $index por subIndex -->
                                <tr v-for="(sub, subIndex) in item" :key="sub.id || subIndex">
                                    <td style="width: 33%">{{ sub.subtopico }}</td>
                                    <!-- CORREÇÃO APLICADA: Adicionado guard para avaliadores e filtro casasDecimais -->
                                    <td style="width: 15%" v-for="(avaliador, avalIndex) in sub.avaliadores || []" :key="avaliador.id || avalIndex">
                                        <input
                                            type="number"
                                            class="form-control form-control-sm text-center"
                                            readonly="readonly"
                                            min="0"
                                            max="5"
                                            step="0.1"
                                            :value="formatarDecimal(avaliador.nota)"
                                        />
                                    </td>
                                    <td style="width: 7%" class="text-center">
                                        <input
                                            type="number"
                                            class="form-control form-control-sm text-center"
                                            readonly="readonly"
                                            min="0"
                                            max="5"
                                            step="0.1"
                                            :value="formatarDecimal(sub.media)"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                    <template
                        v-if="
                            formAvaliarFinal.result_topico_pai_agrupado &&
                            formAvaliarFinal.result_topico_pai_agrupado.length > 0 &&
                            formAvaliarFinal.result_topico_pai_agrupado[0] &&
                            formAvaliarFinal.result_topico_pai_agrupado[0][0] &&
                            formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores
                        "
                    >
                        <h6 class="ma-modal-section-title mt-4 mb-2"><i class="fa fa-comment-dots mr-2 text-primary"></i>Comentários por etapa</h6>
                        <p class="ma-modal-lead ma-modal-lead--tight mb-3">Texto registrado por cada participante ao concluir a sua etapa no fluxo.</p>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th
                                        class="text-center"
                                        v-for="(avaliador, id) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                        :key="avaliador.id"
                                    >
                                        <span>
                                            {{ tituloEtapaFluxoColuna(id, avaliador) }}
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td
                                        v-for="(avaliador, avalIndex) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                        :key="avaliador.id || avalIndex"
                                    >
                                        <label class="ma-modal-label-muted">Comentário nesta etapa</label>
                                        <textarea rows="5" class="form-control form-control-sm" readonly="readonly">{{ avaliador.comentario }}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </template>

                    <div class="row justify-content-center mt-5" v-if="formAvaliarFinal.resultChart && formAvaliarFinal.resultChart.length > 0">
                        <div class="col-12 text-center mb-3">
                            <h6 class="ma-modal-section-title mb-1">
                                <i class="fa fa-chart-area mr-2 text-primary"></i>Visão gráfica por grupo de competências
                            </h6>
                            <p class="ma-modal-lead ma-modal-lead--tight mb-0">Leitura rápida do perfil de desempenho em cada eixo avaliado.</p>
                        </div>
                        <div v-for="(chart, index) in formAvaliarFinal.resultChart" :key="chart.id || index" class="col-md-4">
                            <h4 class="text-center ma-modal-chart-name">{{ chart.name }}</h4>
                            <RadarChart :id="chart.name" :chart-data="chart.data" />
                            <h4 class="text-center ma-modal-chart-media">
                                Média no grupo:
                                {{ getMediaFormatada(chart.name) }}
                            </h4>
                        </div>
                        <div class="col-md-12 text-center mt-2">
                            <p class="ma-modal-nota-final mb-0">
                                <span class="ma-modal-nota-final__rotulo">Nota final consolidada</span>
                                <span class="ma-modal-nota-final__valor">{{ formatarDecimal(formAvaliarFinal.nota_final) }}</span>
                            </p>
                        </div>
                    </div>

                    <PlanosAcao
                        :planos="formAvaliarFinal.planos_acoes || []"
                        :result-topico="formAvaliarFinal.result_topico || {}"
                        :visualizando="visualizando"
                        :titulo="tituloPlanoAcao"
                        :descricao="descricaoPlanoAcao"
                        @adicionar="addPlanoAcao"
                        @remover="removerPlanoAcao"
                    />
                </div>
            </template>
            <template #rodape>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="editando && !visualizando && !preloadAvalFinal && formAvaliarFinal.planos_acoes && formAvaliarFinal.planos_acoes.length > 0"
                    @click="salvarAvaliacaoFinal()"
                >
                    <i :class="isEditandoPdi ? 'fa fa-save' : 'fa fa-check'"></i> {{ textoBotaoSalvarPdi }}
                </button>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload">
                    <fieldset>
                        <legend>{{ formAvaliar.tipo_pj ? 'Dados do fornecedor' : 'Dados do colaborador' }}</legend>
                        <p class="ma-modal-lead">
                            {{
                                formAvaliar.tipo_pj
                                    ? 'Identificação utilizada neste ciclo de avaliação.'
                                    : 'Informações cadastrais de quem está sendo avaliado.'
                            }}
                        </p>
                        <div class="row mb-3" v-if="formAvaliar.dados_do_funcionario.cnpj_lotacao">
                            <div class="col-12">
                                <strong>CNPJ:</strong>
                                {{ formAvaliar.dados_do_funcionario.cnpj_lotacao.razao_social }}
                                ({{ formAvaliar.dados_do_funcionario.pertence_filial ? 'Filial' : 'Matriz' }})
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-4">
                                <strong>Nome:</strong>
                                {{ formAvaliar.dados_do_funcionario.nome }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliar.tipo_pj">
                                <strong>Matrícula:</strong>
                                {{ formAvaliar.dados_do_funcionario.matricula }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliar.tipo_pj">
                                <strong>Admissão:</strong>
                                {{ formAvaliar.dados_do_funcionario.data_admissao }}
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 col-lg-4" v-if="!formAvaliar.tipo_pj">
                                <strong>Cargo:</strong>
                                {{ formAvaliar.dados_do_funcionario.cargo }}
                            </div>
                            <div class="col-12 col-lg-4">
                                <strong>Centro de Custo:</strong>
                                {{ formAvaliar.dados_do_funcionario.centro_custo }}
                            </div>

                            <div class="col-12 col-lg-4">
                                <strong>Área:</strong>
                                {{ formAvaliar.dados_do_funcionario.area }}
                            </div>
                        </div>
                    </fieldset>

                    <div class="escala-avaliacao-minhas" role="region" aria-label="Escala de avaliação de 1 a 5">
                        <div class="escala-cabecalho">
                            <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            <div class="escala-titulo">Escala de 1 a 5</div>
                        </div>
                        <p class="escala-intro">
                            <strong>Para cada critério, escolha a nota que melhor representa o desempenho observado,</strong> usando as descrições abaixo como
                            referência.
                        </p>
                        <div class="escala-item">
                            <span class="nota-badge nota-5">5</span>
                            <span class="escala-texto"
                                ><strong>Superou muito as expectativas:</strong> É percebido por outras áreas/pessoas como alguém com uma atuação excepcional,
                                modelo de referência</span
                            >
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-4">4</span>
                            <span class="escala-texto"
                                ><strong>Superou as expectativas:</strong> Atuação melhor que o esperado com alto padrão de qualidade</span
                            >
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-3">3</span>
                            <span class="escala-texto"
                                ><strong>Atingiu as expectativas:</strong> Atuação adequada ao esperado (satisfatório), atende os padrões de qualidade e
                                produtividade</span
                            >
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-2">2</span>
                            <span class="escala-texto"><strong>Abaixo das expectativas:</strong> Atuação abaixo do esperado (precisa de desenvolvimento)</span>
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-1">1</span>
                            <span class="escala-texto"
                                ><strong>Muito abaixo das expectativas:</strong> Atuação não aceitável, desempenho muito abaixo do que é esperado para a
                                função</span
                            >
                        </div>
                    </div>

                    <fieldset v-for="item in lista_topicos" :key="item.id">
                        <legend>{{ item.topico }}</legend>
                        <div class="alert alert-info" v-if="item.topico_explicacao">
                            {{ item.topico_explicacao }}
                        </div>
                        <fieldset v-for="(subtopico, index) in item.subtopicos" :key="subtopico.id || index">
                            <legend>{{ subtopico.topico }}</legend>
                            <p class="quebra_linha_textarea">{{ subtopico.topico_explicacao }}</p>
                            <div class="form-group">
                                <label>{{ visualizando ? 'Nota registrada' : 'Sua nota neste critério' }}</label>
                                <input
                                    type="hidden"
                                    class="validacampo nota-hidden-input"
                                    :data-item="item.id"
                                    :data-index="index"
                                    :value="formAvaliar.respostas[item.id][index].nota"
                                    autocomplete="off"
                                />
                                <div class="nota-options d-flex flex-wrap">
                                    <div v-for="n in 5" :key="n" class="nota-option">
                                        <input
                                            type="radio"
                                            class="nota-input-hidden"
                                            :id="'avnota_' + item.id + '_' + index + '_' + n"
                                            :name="'avnota_' + item.id + '_' + index"
                                            :value="n"
                                            v-model="formAvaliar.respostas[item.id][index].nota"
                                            :disabled="visualizando"
                                            @change="validaNotaCampo(item.id, index)"
                                        />
                                        <label
                                            v-tippy="tooltipOpcoesNota(n)"
                                            :for="'avnota_' + item.id + '_' + index + '_' + n"
                                            :class="['nota-btn', 'nota-btn-' + n]"
                                        >
                                            <span class="nota-btn-num">{{ n }}</span>
                                            <span class="nota-btn-hint">{{ dicasNotaCurtas[n] }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="painelReferenciaNotasVisivel(item, index)" class="ma-ref-panel">
                                <div class="ma-ref-panel__head">
                                    <i class="fa fa-chart-line" aria-hidden="true"></i>
                                    <span>Referência: notas das etapas anteriores para este critério</span>
                                </div>
                                <div v-if="hasSelfNotaReferencia(item, index)" class="ma-ref-line">
                                    <div class="ma-ref-line__who">
                                        <span class="ma-ref-ico ma-ref-ico--self"><i class="fa fa-user" aria-hidden="true"></i></span>
                                        <div class="ma-ref-line__text">
                                            <span class="ma-ref-line__title">Colaborador</span>
                                            <span class="ma-ref-line__sub">Autoavaliação</span>
                                        </div>
                                    </div>
                                    <span :class="['ma-ref-nota-pill', notaReferenciaClasse(formAvaliar.respostasFunc[item.id][index].nota)]">{{
                                        formAvaliar.respostasFunc[item.id][index].nota
                                    }}</span>
                                </div>
                                <div
                                    v-for="outra in formAvaliar.outras_avaliacoes_notas"
                                    :key="'nota-' + outra.feedback_id + '-' + item.id + '-' + index"
                                    v-show="outraNotaVisivel(outra, item, index)"
                                    class="ma-ref-line"
                                >
                                    <div class="ma-ref-line__who">
                                        <span class="ma-ref-ico ma-ref-ico--peer"><i class="fa fa-user-tie" aria-hidden="true"></i></span>
                                        <div class="ma-ref-line__text">
                                            <span class="ma-ref-line__title">{{ outra.tipo_avaliador_label || 'Avaliador' }}</span>
                                            <span class="ma-ref-line__sub">{{ outra.avaliador_nome }}</span>
                                        </div>
                                    </div>
                                    <span :class="['ma-ref-nota-pill', notaReferenciaClasse(outra.respostas[item.id][index].nota)]">{{
                                        outra.respostas[item.id][index].nota
                                    }}</span>
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                    <fieldset>
                        <legend>Minhas considerações</legend>
                        <p class="ma-modal-lead">Use este espaço para contextualizar a sua avaliação, destacar pontos fortes ou situações relevantes.</p>
                        <textarea
                            :disabled="visualizando"
                            v-model="formAvaliar.comentario"
                            class="form-control"
                            @blur.prevent="valida_campo_vazio($event.target, 1)"
                            @change.prevent="valida_campo_vazio($event.target, 1)"
                            placeholder="Ex.: resultados alcançados, desafios, necessidade de desenvolvimento ou outros comentários que complementem as notas."
                            rows="4"
                        ></textarea>

                        <div
                            v-if="formAvaliar.principal && (formAvaliar.comentario_funcionario || temConsideracoesOutrosAvaliadores)"
                            class="ma-ref-panel ma-ref-panel--coment mt-3"
                        >
                            <div class="ma-ref-panel__head">
                                <i class="fa fa-comments" aria-hidden="true"></i>
                                <span>Referência: comentários já enviados</span>
                            </div>
                            <div v-if="formAvaliar.comentario_funcionario" class="ma-ref-coment">
                                <div class="ma-ref-coment__label"><i class="fa fa-user mr-1 text-primary"></i>Colaborador</div>
                                <p class="ma-ref-coment__text">{{ formAvaliar.comentario_funcionario }}</p>
                            </div>
                            <div
                                v-for="outra in formAvaliar.outras_avaliacoes_notas"
                                :key="'com-' + outra.feedback_id"
                                v-show="outra.comentario && String(outra.comentario).trim()"
                                class="ma-ref-coment"
                            >
                                <div class="ma-ref-coment__label">
                                    <i class="fa fa-user-tie mr-1 text-primary"></i>{{ outra.tipo_avaliador_label || 'Avaliador' }}
                                    <span class="ma-ref-coment__nome">— {{ outra.avaliador_nome }}</span>
                                </div>
                                <p class="ma-ref-coment__text">{{ outra.comentario }}</p>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload && !visualizando" @click="salvar()">
                    <i class="fa fa-paper-plane"></i> Enviar avaliação
                </button>
            </template>
        </modal>

        <div id="conteudo" class="ma-conteudo">
            <div class="card ma-card ma-filtros shadow border-0 mb-3">
                <div class="card-body py-3 ma-filtros-card-body">
                    <h6 class="ma-card-title text-uppercase mb-3"><i class="fa fa-sliders-h mr-2 text-primary"></i>Filtros</h6>
                    <form
                        class="row align-items-end ma-filtros-form"
                        @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null"
                    >
                        <div class="col-12 col-md-4 col-lg-2 ma-filtro-ano-col">
                            <div class="form-group mb-2 mb-md-0 ma-filtro-ano-wrap">
                                <label class="ma-label" for="ma-filtro-ano-input">Ano</label>
                                <combobox-auto-complete
                                    ref="comboAno"
                                    instance-id="ano"
                                    v-model="controle.dados.ano_avaliacao"
                                    :options="anosComboboxOpcoes"
                                    :disabled="controle.carregando || !listaAnosOrdenados.length"
                                    input-id="ma-filtro-ano-input"
                                    inputmode="numeric"
                                    empty-message="Nenhum ano encontrado."
                                    :max-results="50"
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectAnoCombobox"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-md-7 col-lg-7 ma-filtro-avaliacao-col">
                            <div class="form-group mb-2 mb-md-0 ma-filtro-avaliacao-wrap">
                                <label class="ma-label" for="ma-filtro-avaliacao-input">Avaliação</label>
                                <combobox-auto-complete
                                    ref="comboAvaliacao"
                                    instance-id="avaliacao"
                                    v-model="controle.dados.campoAvaliacao"
                                    :options="avaliacoesComboboxOpcoes"
                                    :disabled="controle.carregando || !avaliacoesDoAno.length"
                                    input-id="ma-filtro-avaliacao-input"
                                    empty-message="Nenhuma avaliação encontrada para este filtro."
                                    :max-results="100"
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectAvaliacaoCombobox"
                                >
                                    <template #option="{ option }">
                                        <span class="ma-autocomplete-titulo">{{ option.raw.titulo }}</span>
                                        <span class="ma-autocomplete-meta text-muted">— {{ option.raw.status }}</span>
                                    </template>
                                </combobox-auto-complete>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3 ma-filtro-status-col">
                            <div class="form-group mb-2 mb-md-0 ma-filtro-status-wrap">
                                <label class="ma-label" for="ma-filtro-status-input">Status</label>
                                <combobox-auto-complete
                                    ref="comboStatus"
                                    instance-id="status"
                                    v-model="controle.dados.campoStatus"
                                    :options="statusComboboxOpcoes"
                                    :disabled="controle.carregando"
                                    input-id="ma-filtro-status-input"
                                    empty-message="Nenhum status encontrado."
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectStatusCombobox"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-auto mt-2">
                            <button type="button" class="btn btn-sm btn-primary ma-btn-atualizar px-3" :disabled="controle.carregando" @click="atualizar">
                                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                                Atualizar lista
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div
                class="card ma-card ma-legenda shadow border-0 mb-3"
                v-if="!controle.carregando && selecionadaAvaliacao && selecionadaAvaliacao.auto_avaliacao"
            >
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="font-weight-bold mr-2 mb-1 w-100">Legenda:</span>
                        <span class="badge ma-legend-pill ma-legend-pill--danger mb-1 mr-1">Pendente autoavaliação</span>
                        <span class="badge ma-legend-pill ma-legend-pill--pink mb-1 mr-1">Pendente autoavaliação colaborador</span>
                        <span class="badge ma-legend-pill ma-legend-pill--warning mb-1 mr-1">Pendente avaliação do par</span>
                        <span class="badge ma-legend-pill ma-legend-pill--gestor-wait mb-1 mr-1">Pendente avaliação gestor</span>
                        <span class="badge ma-legend-pill ma-legend-pill--warn-final mb-1 mr-1">Falta avaliação final</span>
                        <span class="badge ma-legend-pill ma-legend-pill--success mb-1 mr-1">Completa</span>
                    </div>
                </div>
            </div>

            <div
                class="card ma-card ma-legenda shadow border-0 mb-3"
                v-if="!controle.carregando && selecionadaAvaliacao && !selecionadaAvaliacao.auto_avaliacao"
            >
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="font-weight-bold mr-2 mb-1 w-100">Legenda:</span>
                        <span class="badge ma-legend-pill ma-legend-pill--light mb-1 mr-1">Pendente avaliação do gestor</span>
                        <span class="badge ma-legend-pill ma-legend-pill--warn-final mb-1 mr-1">Falta avaliação final</span>
                        <span class="badge ma-legend-pill ma-legend-pill--avaliada mb-1 mr-1">Avaliada pelo gestor</span>
                        <span class="badge ma-legend-pill ma-legend-pill--success mb-1 mr-1">Completa</span>
                    </div>
                </div>
            </div>

            <div class="card ma-card ma-fluxo-card shadow border-0 mb-3" v-if="!controle.carregando && selecionadaAvaliacao && fluxoEtapasExibicao.length">
                <div class="ma-fluxo-card__accent" aria-hidden="true"></div>
                <div class="card-body py-3 px-3">
                    <div class="mb-3">
                        <h6 class="ma-fluxo-card__title mb-1"><i class="fa fa-project-diagram mr-2 text-primary"></i>Fluxo da avaliação</h6>
                        <p class="ma-fluxo-card__subtitle mb-0">
                            Ciclo
                            <span class="ma-fluxo-card__titulo-av">{{ selecionadaAvaliacao.titulo }}</span>
                        </p>
                    </div>
                    <div class="ma-fluxo-steps" role="list">
                        <template v-for="(etapa, idx) in fluxoEtapasExibicao" :key="'fluxo-' + idx">
                            <div class="ma-fluxo-chip" role="listitem">
                                <span class="ma-fluxo-chip__num">{{ idx + 1 }}</span>
                                <span class="ma-fluxo-chip__txt">{{ etapa.label }}</span>
                            </div>
                            <i v-if="idx < fluxoEtapasExibicao.length - 1" class="fa fa-chevron-right ma-fluxo-chip__sep" aria-hidden="true"></i>
                        </template>
                    </div>
                </div>
            </div>

            <p class="mt-2 text-center py-4" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-light border text-center ma-empty rounded shadow-sm" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                <span class="">Nenhum registro encontrado para os filtros selecionados.</span>
            </div>

            <div v-show="!controle.carregando && lista.length > 0">
                <div class="card ma-card shadow border-0 mb-3" v-if="selecionadaAvaliacao && selecionadaAvaliacao.auto_avaliacao">
                    <div class="card-header ma-table-card-header d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <i class="fa fa-th-large mr-2 text-primary"></i>
                            <strong>Minhas avaliações</strong>
                            <span class="text-muted d-block d-md-inline ml-md-2 mt-1 mt-md-0" v-if="selecionadaAvaliacao">{{
                                selecionadaAvaliacao.titulo
                            }}</span>
                        </div>
                        <span class="badge badge-pill badge-secondary ma-count-pill">{{ lista.length }} registro(s)</span>
                    </div>
                    <p class="mb-0 px-3 pt-2 ma-agrupado-hint" v-if="lista.length">
                        <i class="fa fa-layer-group mr-1"></i> Agrupado por status: pendentes; em seguida quem falta avaliação final; depois demais avaliadas e
                        finalizadas.
                    </p>
                    <div class="card-body py-3 px-3 ma-cards-lista">
                        <template v-for="grupo in gruposListaAuto" :key="'ga-' + grupo.status">
                            <div class="ma-grupo-header mb-2" :class="grupo.cls">
                                <div class="ma-grupo-label">
                                    <span class="ma-grupo-ico"><i class="fa" :class="grupo.icon"></i></span>
                                    <div class="ma-grupo-texto">
                                        <strong>{{ grupo.titulo }}</strong>
                                        <span class="ma-grupo-sub d-none d-sm-inline">{{ grupo.subtitulo }}</span>
                                    </div>
                                    <span class="badge badge-pill ma-grupo-count">{{ grupo.itens.length }}</span>
                                </div>
                            </div>
                            <div v-for="item in grupo.itens" :key="item.id" class="card ma-item-card border-0 shadow-sm mb-2" :class="classesCardAuto(item)">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                                        <div class="flex-grow-1 pr-2 ma-min-w-0">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <span class="badge badge-pill badge-light border ma-badge-ano mr-2 mb-1">{{
                                                    item.avaliacao.ano_avaliacao
                                                }}</span>
                                                <span class="ma-cell-title mb-0">{{ item.avaliacao.titulo }}</span>
                                            </div>
                                            <div class="mt-1">
                                                <span class="d-inline-block mr-2">{{ item.avaliacao.avaliacao_tipo.nome }}</span>
                                                <span class="text-nowrap"><i class="fa fa-calendar-alt mr-1"></i>{{ item.avaliacao.data_fim_prazo }}</span>
                                            </div>
                                        </div>
                                        <div
                                            class="dropdown flex-shrink-0 ml-auto"
                                            :class="{ show: isDropdownOpen(item.id, 'auto') }"
                                            v-show="
                                                (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) ||
                                                (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par) ||
                                                (item.status === 'Pendente' && !item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id) ||
                                                item.status === 'Avaliada' ||
                                                (item.status === 'Avaliada' && item.fazer_avaliacao_final) ||
                                                (item.status === 'Finalizada' && !item.fazer_avaliacao_final)
                                            "
                                        >
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary ma-btn-acao rounded-circle"
                                                :id="`dropdownMenuLink_${item.id}_auto`"
                                                aria-haspopup="true"
                                                :aria-expanded="isDropdownOpen(item.id, 'auto') ? 'true' : 'false'"
                                                @click.prevent.stop="toggleDropdown(item.id, 'auto')"
                                            >
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>

                                            <div
                                                class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                                :class="{ show: isDropdownOpen(item.id, 'auto') }"
                                                :aria-labelledby="`dropdownMenuLink_${item.id}_auto`"
                                                @click="fecharDropdown"
                                            >
                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Avaliar"
                                                    @click="abrirModalAvaliacao(item)"
                                                    v-if="
                                                        (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) ||
                                                        (item.status === 'Pendente' &&
                                                            item.fez_auto_avaliacao &&
                                                            item.principal &&
                                                            !item.pendente_avaliacao_par)
                                                    "
                                                >
                                                    Avaliar
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Avaliar"
                                                    @click="abrirModalAvaliacao(item)"
                                                    v-if="item.status === 'Pendente' && !item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id"
                                                >
                                                    Avaliar
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação"
                                                    @click="abrirModalAvaliacao(item, true)"
                                                    v-if="item.status === 'Avaliada'"
                                                >
                                                    Visualizar Avaliação
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação"
                                                    @click="abrirModalAvaliacao(item, true)"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final"
                                                >
                                                    Visualizar Avaliação
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Fazer Avaliação Final"
                                                    @click="abrirModalAvaliacaoFinal(item)"
                                                    v-if="item.status === 'Avaliada' && item.fazer_avaliacao_final"
                                                >
                                                    Fazer Avaliação Final
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação Final"
                                                    @click="abrirModalAvaliacaoFinal(item, true)"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                                >
                                                    Visualizar Avaliação Final
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Editar PDI"
                                                    @click="abrirModalEdicaoPdi(item)"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                                >
                                                    Editar PDI
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    :href="`${urlImpressao}/${item.token}`"
                                                    target="_blank"
                                                    title="Imprimir Avaliação Final"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                                >
                                                    Imprimir Avaliação Final
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-3 ma-item-hr" />
                                    <div class="row mx-0">
                                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                                            <span class="ma-k">{{ selecionadaAvaliacao.tipo_pj ? 'Fornecedor' : 'Colaborador' }}</span>
                                            <div class="mt-1">
                                                <span class="ma-valor-destaque">
                                                    <i class="fa fa-user text-primary mr-1" v-if="item.avaliador_id === item.funcionario_id"></i>
                                                    {{ item.funcionario.nome }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="ma-k">Avaliador</span>
                                            <div class="mt-1">
                                                <span class="ma-valor-destaque">{{ item.avaliador.nome }}</span>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2 pt-2 border-top border-light">
                                            <div v-if="item.origem_feedback === 'Avaliador'" class="">
                                                <span class="ma-k ma-como-destaque__rotulo">Como</span>
                                                <span class="ma-valor-destaque d-block mt-1 ma-como-destaque">
                                                    {{ rotuloComoAvaliacao(item) }}
                                                </span>
                                            </div>
                                            <div v-else-if="item.origem_feedback === 'Funcionario' && !item.principal" class="">
                                                <span class="ma-k ma-como-destaque__rotulo">Como</span>
                                                <span class="ma-valor-destaque d-block mt-1 ma-como-destaque ma-como-destaque--auto">Autoavaliação</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card ma-card shadow border-0 mb-3" v-if="selecionadaAvaliacao && !selecionadaAvaliacao.auto_avaliacao">
                    <div class="card-header ma-table-card-header d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <i class="fa fa-th-large mr-2 text-primary"></i>
                            <strong>Minhas avaliações</strong>
                            <span class="text-muted d-block d-md-inline ml-md-2 mt-1 mt-md-0" v-if="selecionadaAvaliacao">{{
                                selecionadaAvaliacao.titulo
                            }}</span>
                        </div>
                        <span class="badge badge-pill badge-secondary ma-count-pill">{{ totalRegistrosListaGestor }} registro(s)</span>
                    </div>
                    <p class="text-muted mb-0 px-3 pt-2 ma-agrupado-hint" v-if="totalRegistrosListaGestor">
                        <i class="fa fa-layer-group mr-1"></i> Agrupado por status (gestor principal): pendentes; falta avaliação final; demais avaliadas;
                        finalizadas.
                    </p>
                    <div class="card-body py-3 px-3 ma-cards-lista">
                        <template v-for="grupo in gruposListaGestor" :key="'gg-' + grupo.status">
                            <div class="ma-grupo-header mb-2" :class="grupo.cls">
                                <div class="ma-grupo-label">
                                    <span class="ma-grupo-ico"><i class="fa" :class="grupo.icon"></i></span>
                                    <div class="ma-grupo-texto">
                                        <strong>{{ grupo.titulo }}</strong>
                                        <span class="ma-grupo-sub d-none d-sm-inline">{{ grupo.subtitulo }}</span>
                                    </div>
                                    <span class="badge badge-pill ma-grupo-count">{{ grupo.itens.length }}</span>
                                </div>
                            </div>
                            <div
                                v-for="item in grupo.itens"
                                :key="'gestor-' + item.id"
                                class="card ma-item-card border-0 shadow-sm mb-2"
                                :class="classesCardGestor(item)"
                            >
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                                        <div class="flex-grow-1 pr-2 ma-min-w-0">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <span class="badge badge-pill badge-light border ma-badge-ano mr-2 mb-1">{{
                                                    item.avaliacao.ano_avaliacao
                                                }}</span>
                                                <span class="ma-cell-title mb-0">{{ item.avaliacao.titulo }}</span>
                                            </div>
                                            <div class="mt-1">
                                                <span class="d-inline-block mr-2">{{ item.avaliacao.avaliacao_tipo.nome }}</span>
                                                <span class="text-nowrap"><i class="fa fa-calendar-alt mr-1"></i>{{ item.avaliacao.data_fim_prazo }}</span>
                                            </div>
                                            <div class="mt-2 d-flex flex-wrap align-items-center">
                                                <span class="badge ma-status-badge ma-status-badge--pend" v-if="item.status === 'Pendente'"
                                                    >Pendente gestor</span
                                                >
                                                <span
                                                    class="badge ma-status-badge ma-status-badge--final-pend"
                                                    v-else-if="item.status === 'Avaliada' && item.fazer_avaliacao_final"
                                                    >Falta avaliação final</span
                                                >
                                                <span class="badge ma-status-badge ma-status-badge--avaliada" v-else-if="item.status === 'Avaliada'"
                                                    >Avaliada pelo gestor</span
                                                >
                                                <span class="badge ma-status-badge ma-status-badge--ok" v-else-if="item.status === 'Finalizada'">Completa</span>
                                            </div>
                                        </div>
                                        <div
                                            class="dropdown flex-shrink-0 ml-auto"
                                            :class="{ show: isDropdownOpen(item.id, 'gestor') }"
                                            v-show="
                                                (item.status === 'Pendente' && item.principal && !item.pendente_avaliacao_par) ||
                                                item.status === 'Avaliada' ||
                                                (item.status === 'Avaliada' && item.fazer_avaliacao_final) ||
                                                (item.status === 'Finalizada' && !item.fazer_avaliacao_final)
                                            "
                                        >
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary ma-btn-acao rounded-circle"
                                                :id="`dropdownMenuLink_${item.id}_gestor`"
                                                aria-haspopup="true"
                                                :aria-expanded="isDropdownOpen(item.id, 'gestor') ? 'true' : 'false'"
                                                @click.prevent.stop="toggleDropdown(item.id, 'gestor')"
                                            >
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>

                                            <div
                                                class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                                :class="{ show: isDropdownOpen(item.id, 'gestor') }"
                                                :aria-labelledby="`dropdownMenuLink_${item.id}_gestor`"
                                                @click="fecharDropdown"
                                            >
                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Avaliar"
                                                    @click="abrirModalAvaliacao(item)"
                                                    v-if="item.status === 'Pendente' && item.principal"
                                                >
                                                    Avaliar
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação"
                                                    @click="abrirModalAvaliacao(item, true)"
                                                    v-if="item.status === 'Avaliada'"
                                                >
                                                    Visualizar Avaliação
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Fazer Avaliação Final"
                                                    @click="abrirModalAvaliacaoFinal(item)"
                                                    v-if="item.status === 'Avaliada' && item.fazer_avaliacao_final"
                                                >
                                                    Fazer Avaliação Final
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação Final"
                                                    @click="abrirModalAvaliacaoFinal(item, true)"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                                >
                                                    Visualizar Avaliação Final
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Editar PDI"
                                                    @click="abrirModalEdicaoPdi(item)"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                                >
                                                    Editar PDI
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    :href="`${urlImpressao}/${item.token}`"
                                                    target="_blank"
                                                    title="Imprimir Avaliação Final"
                                                    v-if="
                                                        item.status === 'Finalizada' ||
                                                        (item.total_avaliacoes_concluidas > 0 && (item.principal || tem_privilegio_gestao_rh))
                                                    "
                                                >
                                                    Imprimir Avaliação Final
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-3 ma-item-hr" />
                                    <div class="row mx-0">
                                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                                            <span class="ma-k">Funcionário</span>
                                            <div class="mt-1">
                                                <span class="ma-valor-destaque">
                                                    <i class="fa fa-user text-primary mr-1" v-if="item.avaliador_id === item.funcionario_id"></i>
                                                    {{ item.funcionario.nome }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="ma-k">Avaliador</span>
                                            <div class="mt-1">
                                                <span class="ma-valor-destaque">{{ item.avaliador.nome }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div v-if="lista.length && !totalRegistrosListaGestor" class="alert alert-light border text-center ma-empty mb-0">
                            Nenhum registro neste fluxo como gestor principal. Outras linhas podem aparecer na visão de autoavaliação ou como avaliador
                            secundário.
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="qntPag"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../../ControlePaginacao'
import modal from '../../../Modal'
import DatePicker from '../../../DatePicker'
import RadarChart from '../../../Charts/Radar'
import ComboboxAutoComplete from '../../../ComboboxAutoComplete'
import PlanosAcao from './components/PlanosAcao.vue'
import validacoes from '../../../../mixins/Validacoes'

export default {
    components: {
        modal,
        controlePaginacao,
        DatePicker,
        RadarChart,
        ComboboxAutoComplete,
        PlanosAcao
    },
    mixins: [validacoes],
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },
        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: {
            // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },
    async mounted() {
        await this.listaAvaliacao()
        this.formAvaliarDefault = _.cloneDeep(this.formAvaliar)
        this.formAvaliarFinalDefault = _.cloneDeep(this.formAvaliarFinal)

        if (this.lista_avaliacoes && this.lista_avaliacoes.length > 0) {
            const first = this.lista_avaliacoes[0]
            const anos = this.listaAnosOrdenados
            // Ano padrão: o mais recente disponível na lista (igual à ordem do combobox), não o primeiro registro bruto da API
            if (anos.length) {
                this.controle.dados.ano_avaliacao = anos[0]
            } else {
                this.controle.dados.ano_avaliacao = Number(first.ano_avaliacao)
            }
            const noAno = this.avaliacoesDoAno
            const pick = noAno.length ? noAno[0] : first
            this.controle.dados.campoAvaliacao = pick.id
        }
        document.addEventListener('click', this.onClickOutside)
        await this.atualizar()
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Avaliação',
            titulo_janela_final: 'Avaliação final',
            preload: false,
            preloadAvalFinal: false,
            editando: false,
            visualizando: false,
            modoAvaliacaoFinal: 'finalizar',
            tem_privilegio_gestao_rh: false,

            chartsRadares: [],

            formAvaliar: {
                respostas: [],
                respostasFunc: [],
                dados_do_funcionario: [],
                comentario: '',
                comentario_funcionario: '',
                outras_avaliacoes_notas: []
            },

            // CORREÇÃO APLICADA: Inicialização melhorada do formAvaliarFinal
            formAvaliarFinal: {
                dados_do_funcionario: {},
                avaliador_principal: '',
                status_avaliacao: '',
                total_aval: '',
                media_aval: '',
                nota_final: 0,
                resultado_topico_pai: {},
                result_topico_pai_agrupado: [],
                result_topico: {},
                result_subtopico: [],
                resultChart: [],
                planos_acoes: [],
                planos_acoes_delete: [],
                fluxo_etapas: []
            },

            formAvaliarDefault: null,
            formAvaliarFinalDefault: null,

            lista: [],
            lista_topicos: [],
            lista_avaliacoes_tipos: [],
            lista_avaliacoes: [],
            lista_anos: [],
            lista_status: [],

            lista_avaliacoes_por_ano: [],

            avaliacaoSelecionada: null,

            dropdownAbertoKey: null,

            textosTooltipNota: {
                5: 'Superou muito as expectativas: É percebido por outras áreas/pessoas como alguém com uma atuação excepcional, modelo de referência.',
                4: 'Superou as expectativas: Atuação melhor que o esperado com alto padrão de qualidade.',
                3: 'Atingiu as expectativas: Atuação adequada ao esperado (satisfatório), atende os padrões de qualidade e produtividade.',
                2: 'Abaixo das expectativas: Atuação abaixo do esperado (precisa de desenvolvimento).',
                1: 'Muito abaixo das expectativas: Atuação não aceitável, desempenho muito abaixo do que é esperado para a função.'
            },
            dicasNotaCurtas: {
                5: 'Superou muito',
                4: 'Superou',
                3: 'Atingiu',
                2: 'Abaixo',
                1: 'Muito abaixo'
            },

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/atualizar`,
            urlImpressao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/impressao`,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoAvaliacao: '',
                    campoStatus: '',
                    ano_avaliacao: new Date().getFullYear(),
                    tipo_avaliacao: ''
                }
            }
        }
    },
    computed: {
        listaKeysAvaliacaoPorAnoOrdenado() {
            return Object.keys(this.lista_avaliacoes_por_ano).sort((a, b) => b - a)
        },
        groupAvaliacaoAno() {
            let group = _.groupBy(this.lista_avaliacoes_por_ano[this.controle.dados.ano_avaliacao], 'avaliacao_tipo_id')

            let array = []
            for (let key in group) {
                if (group[key][0].ativo) {
                    array.push({
                        avaliacao_tipo_id: key,
                        avaliacao_tipo: group[key][0].avaliacao_tipo.nome
                    })
                }
            }
            return array
        },
        selecionadaAvaliacao() {
            return this.lista_avaliacoes.find((item) => item.id === this.controle.dados.campoAvaliacao) ?? null
        },
        statusAvaliacaoSelecionada() {
            let statusSemAutoAvaliacao = [
                { label: 'Pendente avaliação gestor', value: 'Pendente' },
                { label: 'Avaliada pelo Gestor', value: 'Avaliada' },
                { label: 'Completa', value: 'Finalizada' }
            ]
            let statusComAutoAvaliacao = [
                { label: 'Pendente', value: 'Pendente' },
                { label: 'Avaliada', value: 'Avaliada' },
                { label: 'Finalizada', value: 'Finalizada' }
            ]

            let status = this.selecionadaAvaliacao?.auto_avaliacao ? statusComAutoAvaliacao : statusSemAutoAvaliacao
            return status ?? []
        },
        gruposListaAuto() {
            return this.agruparPorStatus(this.lista)
        },
        gruposListaGestor() {
            return this.agruparPorStatus(this.lista.filter((i) => !i.avaliacao.auto_avaliacao && i.principal))
        },
        totalRegistrosListaGestor() {
            return this.lista.filter((i) => !i.avaliacao.auto_avaliacao && i.principal).length
        },
        listaAnosOrdenados() {
            let anos = []
            const raw = this.lista_anos
            if (raw && (Array.isArray(raw) ? raw.length : Object.keys(raw).length)) {
                const arr = Array.isArray(raw) ? raw : Object.values(raw)
                anos = arr.map((a) => Number(a)).filter((n) => !Number.isNaN(n))
            } else if (this.lista_avaliacoes && this.lista_avaliacoes.length) {
                anos = [...new Set(this.lista_avaliacoes.map((a) => Number(a.ano_avaliacao)))].filter((n) => !Number.isNaN(n))
            }
            return anos.sort((a, b) => b - a)
        },
        avaliacoesDoAno() {
            const ano = Number(this.controle.dados.ano_avaliacao)
            if (!this.lista_avaliacoes || !this.lista_avaliacoes.length || Number.isNaN(ano)) {
                return []
            }
            return this.lista_avaliacoes.filter((a) => Number(a.ano_avaliacao) === ano)
        },
        anosComboboxOpcoes() {
            return this.listaAnosOrdenados.map((a) => ({
                value: a,
                label: String(a)
            }))
        },
        avaliacoesComboboxOpcoes() {
            return this.avaliacoesDoAno.map((a) => ({
                value: a.id,
                label: `${a.titulo || ''} — ${a.status || ''}`,
                raw: a
            }))
        },
        statusComboboxOpcoes() {
            const todos = { value: '', label: 'Todos os status' }
            const lista = (this.statusAvaliacaoSelecionada || []).map((o) => ({
                value: o.value,
                label: o.label,
                meta: o.value !== '' && o.value !== null && o.value !== undefined ? String(o.value) : undefined
            }))
            return [todos, ...lista]
        },
        /** Etapas do fluxo (mesma regra de Avaliacao::fluxoAvaliacao) para exibir na tela Minhas avaliações */
        fluxoEtapasExibicao() {
            const a = this.selecionadaAvaliacao
            if (!a) {
                return []
            }
            const raw = a.fluxo
            const steps = Array.isArray(raw)
                ? raw.map((item) => ({
                      label: (item.label || '') + (item.principal ? ' (Avaliador Final)' : '')
                  }))
                : []
            if (a.auto_avaliacao) {
                return [{ label: 'Auto Avaliação' }, ...steps]
            }
            return steps
        },
        temConsideracoesOutrosAvaliadores() {
            return (this.formAvaliar.outras_avaliacoes_notas || []).some((o) => o.comentario && String(o.comentario).trim() !== '')
        },
        isEditandoPdi() {
            return this.modoAvaliacaoFinal === 'editar-pdi'
        },
        textoBotaoSalvarPdi() {
            return this.isEditandoPdi ? 'Salvar PDI' : 'Concluir avaliação final'
        },
        tituloPlanoAcao() {
            return this.isEditandoPdi ? 'PDI e acompanhamento' : 'Plano de ação e oportunidades de melhoria'
        },
        descricaoPlanoAcao() {
            return this.isEditandoPdi
                ? 'Atualize as ações, prazos e responsáveis para manter o acompanhamento do desenvolvimento após a finalização da avaliação.'
                : 'Registre ações objetivas, prazos e responsáveis para apoiar o desenvolvimento nos pontos que precisam evoluir.'
        }
    },
    methods: {
        /** Rótulo da coluna de avaliador na avaliação final (modal / alinhado ao PDF quando houver fluxo_etapas) */
        tituloEtapaFluxoColuna(indice, avaliador) {
            const etapas = this.formAvaliarFinal.fluxo_etapas
            if (etapas && etapas[indice] && etapas[indice].label) {
                return etapas[indice].label
            }
            if (avaliador && avaliador.origem === 'Funcionario') {
                return 'Autoavaliação'
            }
            return 'Avaliador ' + (indice + 1)
        },
        hasSelfNotaReferencia(item, index) {
            const n = this.formAvaliar.respostasFunc[item.id]?.[index]?.nota
            return n !== '' && n !== null && n !== undefined
        },
        outraNotaVisivel(outra, item, index) {
            const n = outra.respostas?.[item.id]?.[index]?.nota
            return n !== '' && n !== null && n !== undefined
        },
        painelReferenciaNotasVisivel(item, index) {
            if (!this.formAvaliar.principal) {
                return false
            }
            if (this.hasSelfNotaReferencia(item, index)) {
                return true
            }
            return (this.formAvaliar.outras_avaliacoes_notas || []).some((o) => this.outraNotaVisivel(o, item, index))
        },
        notaReferenciaClasse(n) {
            const v = Math.round(Number(n))
            if (v >= 1 && v <= 5) {
                return 'ma-ref-nota-pill--' + v
            }
            return ''
        },
        /** Rótulo do campo "Como" na lista — inclui (Avaliador Final) quando aplicável */
        rotuloComoAvaliacao(item) {
            if (!item.tipo_avaliador || !item.tipo_avaliador.label) {
                return '---'
            }
            const base = String(item.tipo_avaliador.label).trim()
            if (item.principal && !base.includes('(Avaliador Final)')) {
                return `${base} (Avaliador Final)`
            }
            return base
        },
        agruparPorStatus(itens) {
            const ordem = ['Pendente', 'AvaliacaoFinalPendente', 'Avaliada', 'Finalizada']
            const meta = {
                Pendente: {
                    titulo: 'Pendente',
                    subtitulo: 'Itens que precisam da sua ação',
                    icon: 'fa-clock',
                    cls: 'ma-grupo--pend'
                },
                AvaliacaoFinalPendente: {
                    titulo: 'Falta avaliação final',
                    subtitulo: 'Gestor já avaliou; conclua a avaliação final para encerrar',
                    icon: 'fa-flag-checkered',
                    cls: 'ma-grupo--pend-final'
                },
                Avaliada: {
                    titulo: 'Avaliada',
                    subtitulo: 'Sem avaliação final pendente neste fluxo',
                    icon: 'fa-user-check',
                    cls: 'ma-grupo--avaliada'
                },
                Finalizada: {
                    titulo: 'Finalizada',
                    subtitulo: 'Ciclo encerrado nesta avaliação',
                    icon: 'fa-check-circle',
                    cls: 'ma-grupo--final'
                }
            }
            const map = { Pendente: [], AvaliacaoFinalPendente: [], Avaliada: [], Finalizada: [] }
            itens.forEach((item) => {
                let k = null
                if (item.status === 'Pendente') {
                    k = 'Pendente'
                } else if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                    k = 'AvaliacaoFinalPendente'
                } else if (item.status === 'Avaliada') {
                    k = 'Avaliada'
                } else if (item.status === 'Finalizada') {
                    k = 'Finalizada'
                }
                if (k && map[k]) {
                    map[k].push(item)
                }
            })
            return ordem
                .filter((key) => map[key].length)
                .map((key) => ({
                    status: key,
                    ...meta[key],
                    itens: map[key]
                }))
        },
        toggleDropdown(itemId, tipo) {
            const key = `${tipo}:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId, tipo) {
            return this.dropdownAbertoKey === `${tipo}:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (!this.$el || this.$el.contains(event.target)) {
                return
            }
            const comboAno = this.$refs.comboAno
            const comboAvaliacao = this.$refs.comboAvaliacao
            const comboStatus = this.$refs.comboStatus
            if (comboAno && typeof comboAno.containsTarget === 'function' && comboAno.containsTarget(event.target)) {
                return
            }
            if (comboAvaliacao && typeof comboAvaliacao.containsTarget === 'function' && comboAvaliacao.containsTarget(event.target)) {
                return
            }
            if (comboStatus && typeof comboStatus.containsTarget === 'function' && comboStatus.containsTarget(event.target)) {
                return
            }
            this.dropdownAbertoKey = null
            this.fecharOutrosFiltros(null)
        },
        fecharOutrosFiltros(manter) {
            if (manter !== 'ano' && this.$refs.comboAno && typeof this.$refs.comboAno.close === 'function') {
                this.$refs.comboAno.close()
            }
            if (manter !== 'avaliacao' && this.$refs.comboAvaliacao && typeof this.$refs.comboAvaliacao.close === 'function') {
                this.$refs.comboAvaliacao.close()
            }
            if (manter !== 'status' && this.$refs.comboStatus && typeof this.$refs.comboStatus.close === 'function') {
                this.$refs.comboStatus.close()
            }
        },
        onSelectAnoCombobox() {
            this.onAnoFiltroChange()
        },
        onSelectAvaliacaoCombobox() {
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        onSelectStatusCombobox() {
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        selecionarAvaliacaoFiltro(item) {
            if (!item) {
                return
            }
            this.controle.dados.campoAvaliacao = item.id
            this.$nextTick(() => {
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            })
        },
        onAnoFiltroChange() {
            const itens = this.avaliacoesDoAno
            if (itens.length) {
                const atualOk = itens.some((a) => a.id === this.controle.dados.campoAvaliacao)
                if (!atualOk) {
                    this.selecionarAvaliacaoFiltro(itens[0])
                } else {
                    this.$nextTick(() => {
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    })
                }
            } else {
                const fb = this.lista_avaliacoes[0]
                if (fb) {
                    this.controle.dados.ano_avaliacao = Number(fb.ano_avaliacao)
                    this.$nextTick(() => {
                        const list = this.avaliacoesDoAno
                        if (list.length) {
                            this.selecionarAvaliacaoFiltro(list[0])
                        }
                    })
                } else {
                    this.controle.dados.campoAvaliacao = ''
                    this.$nextTick(() => {
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    })
                }
            }
        },
        linhaClasseAuto(item) {
            if (item.status === 'Finalizada') {
                return ['ma-tr', 'ma-tr--done']
            }
            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return ['ma-tr', 'ma-tr--pend-avaliacao-final']
            }
            if (item.status === 'Avaliada') {
                return ['ma-tr', 'ma-tr--avaliada-sem-final-pend']
            }
            if (item.pendente_autoavaliacao) {
                return ['ma-tr', 'ma-tr--pend-auto']
            }
            if (item.pendente_autoavaliacao_colaborador) {
                return ['ma-tr', 'ma-tr--pend-auto-colab']
            }
            if (item.pendente_avaliacao_par && item.status !== 'Finalizada') {
                return ['ma-tr', 'ma-tr--pend-par']
            }
            if (!item.pendente_avaliacao_par && item.status !== 'Finalizada') {
                return ['ma-tr', 'ma-tr--pend-gestor']
            }
            return ['ma-tr']
        },
        linhaClasseGestor(item) {
            if (item.status === 'Finalizada') {
                return ['ma-tr', 'ma-tr--gestor-done']
            }
            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return ['ma-tr', 'ma-tr--gestor-pend-final']
            }
            if (item.status === 'Avaliada') {
                return ['ma-tr', 'ma-tr--gestor-avaliada']
            }
            if (item.status === 'Pendente') {
                return ['ma-tr', 'ma-tr--gestor-pend']
            }
            return ['ma-tr']
        },
        classesCardAuto(item) {
            return [...this.linhaClasseAuto(item).filter((c) => c !== 'ma-tr'), 'ma-item-card']
        },
        classesCardGestor(item) {
            return [...this.linhaClasseGestor(item).filter((c) => c !== 'ma-tr'), 'ma-item-card']
        },
        tooltipOpcoesNota(n) {
            const content = this.textosTooltipNota[n]
            if (!content) {
                return {}
            }
            return {
                content,
                maxWidth: 340,
                interactive: true,
                appendTo: () => document.body,
                zIndex: 10050,
                delay: [120, 40],
                touch: ['hold', 450]
            }
        },
        validaNotaCampo(itemId, index) {
            this.$nextTick(() => {
                const el = this.$el.querySelector(`.nota-hidden-input[data-item="${itemId}"][data-index="${index}"]`)
                if (el && typeof window.valida_campo_vazio === 'function') {
                    window.valida_campo_vazio(el, 1)
                }
            })
        },
        // CORREÇÃO PRINCIPAL: Substituição do filtro por métodos
        formatarDecimal(valor) {
            if (valor === null || valor === undefined || isNaN(valor)) {
                return '0.0'
            }
            return Number(valor).toFixed(1)
        },

        getMediaFormatada(chartName) {
            if (
                this.formAvaliarFinal.resultado_topico_pai &&
                this.formAvaliarFinal.resultado_topico_pai[chartName] &&
                this.formAvaliarFinal.resultado_topico_pai[chartName].media !== undefined
            ) {
                return this.formatarDecimal(this.formAvaliarFinal.resultado_topico_pai[chartName].media)
            }
            return '0.0'
        },

        getMediaTopico(topicoId) {
            if (
                this.formAvaliarFinal.result_topico &&
                this.formAvaliarFinal.result_topico[topicoId] &&
                this.formAvaliarFinal.result_topico[topicoId].media !== undefined
            ) {
                return this.formatarDecimal(this.formAvaliarFinal.result_topico[topicoId].media)
            }
            return '0.0'
        },

        async listaAvaliacao() {
            await axios
                .get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/lista/listavaliacoes`)
                .then((response) => {
                    this.lista_avaliacoes = response.data.lista_avaliacoes
                    this.lista_anos = response.data.lista_anos
                })
                .catch((error) => (this.preloadAjax = false))
        },

        // CORREÇÃO APLICADA: Melhorada a função addPlanoAcao
        addPlanoAcao() {
            // Garante que o array planos_acoes existe
            if (!this.formAvaliarFinal.planos_acoes) {
                this.formAvaliarFinal.planos_acoes = []
            }

            let obj = {
                nova: true,
                avaliacao_feedback_id: this.formAvaliarFinal.avaliacao_feedback_id || '',
                avaliacao_feedback_id_avaliador: this.formAvaliarFinal.avaliacao_feedback_id_avaliador || '',
                gestor_id: this.formAvaliarFinal.gestor_id || '',
                topico_id: '',
                responsavel: (this.formAvaliarFinal.dados_do_funcionario && this.formAvaliarFinal.dados_do_funcionario.nome) || '',
                plano_de_acao: '',
                inicio: '',
                termino: '',
                status: '',
                dados_extras: {}
            }
            this.formAvaliarFinal.planos_acoes.push(obj)
        },

        // CORREÇÃO APLICADA: Melhorada a função removerPlanoAcao
        removerPlanoAcao(index) {
            if (!this.formAvaliarFinal.planos_acoes || index < 0 || index >= this.formAvaliarFinal.planos_acoes.length) {
                return
            }

            if (this.formAvaliarFinal.planos_acoes[index].id) {
                if (!this.formAvaliarFinal.planos_acoes_delete) {
                    this.formAvaliarFinal.planos_acoes_delete = []
                }
                this.formAvaliarFinal.planos_acoes_delete.push(this.formAvaliarFinal.planos_acoes[index].id)
            }
            this.formAvaliarFinal.planos_acoes.splice(index, 1)
        },

        abrirModalAvaliacao(avaliacaoFeedback, visualizando = false) {
            this.avaliarForm(avaliacaoFeedback, visualizando)
            this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.abrirModal()
        },

        abrirModalAvaliacaoFinal(avaliacaoFeedback, visualizando = false) {
            this.avaliarFinalForm(avaliacaoFeedback, visualizando)
            this.$refs.modal_janelaAvaliacaoFinal && this.$refs.modal_janelaAvaliacaoFinal.abrirModal()
        },

        abrirModalEdicaoPdi(avaliacaoFeedback) {
            this.editarPdiForm(avaliacaoFeedback)
            this.$refs.modal_janelaAvaliacaoFinal && this.$refs.modal_janelaAvaliacaoFinal.abrirModal()
        },

        avaliarForm(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando
            this.editando = true
            this.modoAvaliacaoFinal = 'finalizar'
            this.titulo_janela = `Avaliar — ${avaliacaoFeedback.avaliacao.titulo}`
            this.preload = true

            this.formAvaliar = _.cloneDeep(this.formAvaliarDefault) //copia
            formReset()

            axios
                .get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/edit`)
                .then((response) => {
                    this.lista_topicos = response.data.topicos
                    this.formAvaliar.respostas = response.data.respostas
                    this.formAvaliar.respostasFunc = response.data.respostas_funcionario
                    this.formAvaliar.comentario = response.data.comentario
                    this.formAvaliar.comentario_funcionario = response.data.comentario_funcionario
                    this.formAvaliar.dados_do_funcionario = response.data.dados_do_funcionario
                    this.formAvaliar.avaliacao_feedback_id = response.data.avaliacao_feedback_id
                    this.formAvaliar.origem_feedback = response.data.origem_feedback
                    this.formAvaliar.principal = response.data.principal
                    this.formAvaliar.tipo_pj = response.data.tipo_pj
                    this.formAvaliar.outras_avaliacoes_notas = response.data.outras_avaliacoes_notas || []
                    this.editando = true
                    setupCampo()
                    this.preload = false
                })
                .catch((error) => (this.preloadAjax = false))
        },

        // CORREÇÃO APLICADA: Melhorada a função avaliarFinalForm
        avaliarFinalForm(avaliacaoFeedback, visualizando = false, modo = 'finalizar') {
            this.visualizando = visualizando
            this.editando = true
            this.modoAvaliacaoFinal = modo
            this.titulo_janela_final =
                modo === 'editar-pdi' ? `Editar PDI — ${avaliacaoFeedback.avaliacao.titulo}` : `Avaliação final — ${avaliacaoFeedback.avaliacao.titulo}`
            this.preloadAvalFinal = true

            // Reset para um estado seguro
            this.formAvaliarFinal = _.cloneDeep(this.formAvaliarFinalDefault)

            // Garante que arrays críticos estão inicializados
            if (!this.formAvaliarFinal.result_topico_pai_agrupado) {
                this.formAvaliarFinal.result_topico_pai_agrupado = []
            }
            if (!this.formAvaliarFinal.resultChart) {
                this.formAvaliarFinal.resultChart = []
            }
            if (!this.formAvaliarFinal.resultado_topico_pai) {
                this.formAvaliarFinal.resultado_topico_pai = {}
            }
            if (!this.formAvaliarFinal.planos_acoes) {
                this.formAvaliarFinal.planos_acoes = []
            }

            formReset()

            axios
                .get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/final`)
                .then(({ data }) => {
                    // Atribuição segura dos dados com fallbacks
                    Object.assign(this.formAvaliarFinal, {
                        ...data,
                        result_topico_pai_agrupado: data.result_topico_pai_agrupado || [],
                        resultChart: data.resultChart || [],
                        resultado_topico_pai: data.resultado_topico_pai || {},
                        planos_acoes: data.planos_acoes || [],
                        result_topico: data.result_topico || {},
                        fluxo_etapas: data.fluxo_etapas || []
                    })

                    this.editando = true
                    setupCampo()
                    this.preloadAvalFinal = false
                })
                .catch((error) => {
                    console.error('Erro ao carregar avaliação final:', error)
                    this.preloadAvalFinal = false
                    toastr.error('Erro ao carregar avaliação final', 'Erro!')
                })
        },

        editarPdiForm(avaliacaoFeedback) {
            this.avaliarFinalForm(avaliacaoFeedback, false, 'editar-pdi')
        },

        salvarAvaliacaoFinal() {
            this.validaBlur()
            let countErro = document.querySelectorAll('.is-invalid').length
            if (countErro > 0) {
                toastr.error('Verifique os campos', 'Atenção!')
                return false
            }

            this.preloadAvalFinal = true

            axios
                .put(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${this.formAvaliarFinal.avaliacao_feedback_id}/final`, this.formAvaliarFinal)
                .then((response) => {
                    this.$refs.modal_janelaAvaliacaoFinal && this.$refs.modal_janelaAvaliacaoFinal.fecharModal()
                    mostraSucesso('', this.isEditandoPdi ? 'PDI salvo com sucesso' : 'Avaliação final salva com sucesso')
                    this.preloadAvalFinal = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAvalFinal = false))
        },

        salvar() {
            this.validaBlur()
            let countErro = document.querySelectorAll('.is-invalid').length
            if (countErro > 0) {
                toastr.error('Verifique os campos', 'Atenção!')
                return false
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${this.formAvaliar.avaliacao_feedback_id}`, this.formAvaliar)
                .then((response) => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    mostraSucesso('', 'Avaliação enviada com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.itens
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos
            this.lista_status = dados.lista_status
            this.lista_avaliacoes_por_ano = dados.lista_avaliacoes_por_ano
            this.tem_privilegio_gestao_rh = dados.tem_privilegio_gestao_rh
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        async atualizar() {
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            ;(await this) && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
}
</script>

<style scoped>
.card-header {
    background-color: white;
}

.btn-link {
    font-weight: 400;
    color: white;
    text-decoration: none;
}

.btn-link:hover {
    color: #dddddd;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.text-pink {
    color: pink !important;
}

.bg-pink {
    background: pink !important;
}

.text-azul {
    color: powderblue !important;
}

.bg-azul {
    background: powderblue !important;
}

.bg-cinza {
    background: #f1f1f1 !important;
}

/* Minhas Avaliações — escala e botões de nota (alinhado à Avaliação de Experiência) */
.escala-avaliacao-minhas {
    position: relative;
    background: linear-gradient(135deg, #f0f7fb 0%, #ffffff 55%, #f5fafc 100%);
    border: 1px solid rgba(0, 55, 85, 0.18);
    border-left: 5px solid #003755;
    border-radius: 12px;
    padding: 1.25rem 1.35rem 1.35rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 8px 24px rgba(0, 55, 85, 0.1);
}
.escala-avaliacao-minhas::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    border-radius: 12px 12px 0 0;
    background: linear-gradient(90deg, #003755, #1a5f7a);
}
.escala-avaliacao-minhas .escala-cabecalho {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 2px solid rgba(0, 55, 85, 0.12);
}
.escala-avaliacao-minhas .escala-cabecalho .fa {
    font-size: 1.35rem;
    color: #003755;
}
.escala-avaliacao-minhas .escala-titulo {
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    color: #003755;
    margin: 0;
    text-transform: uppercase;
}
.escala-avaliacao-minhas .escala-intro {
    font-size: 0.95rem;
    line-height: 1.5;
    color: #1a1a1a;
    margin-bottom: 1rem;
    padding: 0.65rem 0.75rem;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 8px;
    border: 1px dashed rgba(0, 55, 85, 0.25);
}
.escala-avaliacao-minhas .escala-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 0.65rem;
    padding: 0.35rem 0.25rem;
    border-radius: 8px;
}
.escala-avaliacao-minhas .escala-item:last-child {
    margin-bottom: 0;
}
.escala-avaliacao-minhas .nota-badge {
    flex-shrink: 0;
    min-width: 2rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 55, 85, 0.25);
}
.escala-avaliacao-minhas .nota-badge.nota-5 {
    background: linear-gradient(145deg, #0d6e4a, #1a9966);
}
.escala-avaliacao-minhas .nota-badge.nota-4 {
    background: linear-gradient(145deg, #0d7a5c, #1a9e72);
}
.escala-avaliacao-minhas .nota-badge.nota-3 {
    background: linear-gradient(145deg, #c9a227, #d4af37);
    color: #1a1a1a;
}
.escala-avaliacao-minhas .nota-badge.nota-2 {
    background: linear-gradient(145deg, #c45c26, #d97736);
}
.escala-avaliacao-minhas .nota-badge.nota-1 {
    background: linear-gradient(145deg, #a83232, #c44545);
}
.escala-avaliacao-minhas .escala-texto {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #2c3e50;
    padding-top: 2px;
}

.nota-options {
    gap: 10px;
}
.nota-option {
    position: relative;
    flex: 1;
    min-width: 72px;
}
.nota-input-hidden {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.nota-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 6px;
    min-height: 76px;
    width: 100%;
    margin: 0;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s ease;
    font-weight: 600;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.nota-btn .nota-btn-num {
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1;
}
.nota-btn .nota-btn-hint {
    display: block;
    font-size: 0.62rem;
    font-weight: 700;
    line-height: 1.15;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    max-width: 100%;
}
.nota-btn-5 {
    border-color: rgba(26, 153, 102, 0.45);
    color: #0d5a3f;
    background: rgba(26, 153, 102, 0.07);
}
.nota-btn-4 {
    border-color: rgba(26, 158, 114, 0.45);
    color: #0d6b50;
    background: rgba(26, 158, 114, 0.07);
}
.nota-btn-3 {
    border-color: rgba(201, 162, 39, 0.55);
    color: #6b5a12;
    background: rgba(212, 175, 55, 0.12);
}
.nota-btn-2 {
    border-color: rgba(196, 92, 38, 0.5);
    color: #8b3d12;
    background: rgba(217, 119, 54, 0.1);
}
.nota-btn-1 {
    border-color: rgba(168, 50, 50, 0.5);
    color: #8b2020;
    background: rgba(196, 69, 69, 0.08);
}
.nota-btn:hover {
    filter: brightness(0.97);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 55, 85, 0.12);
}
.nota-option input[type='radio']:checked + .nota-btn-5 {
    background: linear-gradient(145deg, #0d6e4a, #1a9966);
    color: #fff;
    border-color: #0d6e4a;
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(13, 110, 74, 0.35);
}
.nota-option input[type='radio']:checked + .nota-btn-4 {
    background: linear-gradient(145deg, #0d7a5c, #1a9e72);
    color: #fff;
    border-color: #0d7a5c;
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(13, 122, 92, 0.35);
}
.nota-option input[type='radio']:checked + .nota-btn-3 {
    background: linear-gradient(145deg, #c9a227, #d4af37);
    color: #1a1a1a;
    border-color: #b8941f;
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(201, 162, 39, 0.4);
}
.nota-option input[type='radio']:checked + .nota-btn-3 .nota-btn-hint {
    color: #2d2510;
}
.nota-option input[type='radio']:checked + .nota-btn-2 {
    background: linear-gradient(145deg, #c45c26, #d97736);
    color: #fff;
    border-color: #c45c26;
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(196, 92, 38, 0.35);
}
.nota-option input[type='radio']:checked + .nota-btn-1 {
    background: linear-gradient(145deg, #a83232, #c44545);
    color: #fff;
    border-color: #a83232;
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(168, 50, 50, 0.35);
}
.nota-option input[type='radio']:checked + .nota-btn .nota-btn-hint {
    opacity: 0.95;
}
.nota-option input[type='radio']:checked + .nota-btn-3 .nota-btn-hint {
    color: #2d2510;
}
.nota-option input[type='radio']:disabled + .nota-btn {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}
@media (max-width: 768px) {
    .nota-options {
        flex-direction: column;
    }
    .nota-option {
        min-width: 100%;
    }
}

/* Minhas Avaliações — listagem (cards, tabela, legenda) */
.ma-conteudo {
    max-width: 100%;
    overflow: visible;
}
.ma-card {
    border-radius: 10px;
    overflow: hidden;
}
/* Card de filtros: overflow visível para o dropdown do autocomplete não ser cortado */
.ma-card.ma-filtros {
    overflow: visible;
}
.ma-card.ma-filtros > .ma-filtros-card-body {
    overflow: visible;
}
.ma-filtros-form {
    overflow: visible;
}
.ma-filtro-avaliacao-col,
.ma-filtro-ano-col,
.ma-filtro-status-col {
    overflow: visible;
    position: relative;
    z-index: 50;
}
.ma-filtro-avaliacao-wrap,
.ma-filtro-ano-wrap,
.ma-filtro-status-wrap {
    position: relative;
    z-index: 51;
}
.ma-card-title {
    letter-spacing: 0.04em;
    color: #6c757d;
}
.ma-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}
.ma-select {
    border-radius: 8px;
    border-color: #dee2e6;
}
.ma-filtro-combo .form-control {
    border-radius: 8px 0 0 8px;
}
.ma-filtro-combo-toggle {
    border-radius: 0 8px 8px 0 !important;
    padding-left: 0.65rem;
    padding-right: 0.65rem;
    line-height: 1.25;
}
.ma-btn-atualizar {
    border-radius: 8px;
    font-weight: 600;
}
.ma-legenda .ma-legend-pill {
    font-weight: 600;
    font-size: 0.75rem;
    padding: 0.4em 0.65em;
    border-radius: 6px;
}
.ma-legend-pill--danger {
    background: rgba(220, 53, 69, 0.15);
    color: #721c24;
    border: 1px solid rgba(220, 53, 69, 0.35);
}
.ma-legend-pill--pink {
    background: rgba(255, 105, 180, 0.2);
    color: #7d1b4d;
    border: 1px solid rgba(255, 105, 180, 0.45);
}
.ma-legend-pill--warning {
    background: rgba(255, 193, 7, 0.25);
    color: #856404;
    border: 1px solid rgba(255, 193, 7, 0.5);
}
/* Aguardando ação do gestor (autoavaliação) — índigo, distinto de “Avaliada” */
.ma-legend-pill--gestor-wait {
    background: rgba(92, 77, 157, 0.14);
    color: #3e2a6b;
    border: 1px solid rgba(92, 77, 157, 0.45);
}
/* Gestor já avaliou (checkpoint) — azul royal, distinto do ciano e do índigo */
.ma-legend-pill--avaliada {
    background: rgba(21, 101, 192, 0.12);
    color: #0d47a1;
    border: 1px solid rgba(21, 101, 192, 0.4);
}
.ma-legend-pill--success {
    background: rgba(40, 167, 69, 0.15);
    color: #155724;
    border: 1px solid rgba(40, 167, 69, 0.35);
}
.ma-legend-pill--light {
    background: #f8f9fa;
    color: #495057;
    border: 1px solid #dee2e6;
}
.ma-legend-pill--warn-final {
    background: rgba(253, 126, 20, 0.18);
    color: #7c2d12;
    border: 1px solid rgba(253, 126, 20, 0.45);
}
.ma-table-card-header {
    background: linear-gradient(135deg, #f8fafb 0%, #fff 100%);
    border-bottom: 1px solid rgba(0, 55, 85, 0.08);
    padding: 0.75rem 1rem;
}
.ma-count-pill {
    font-size: 0.75rem;
    font-weight: 600;
}
.ma-table-responsive {
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}
.ma-table {
    font-size: 0.9rem;
}
.ma-table thead th {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
    color: #495057;
    background: #f1f3f5;
    border-bottom: 2px solid #dee2e6 !important;
    vertical-align: middle;
    padding: 0.65rem 0.5rem;
}
.ma-table tbody td {
    vertical-align: middle;
    border-color: #eee;
    padding: 0.65rem 0.5rem;
}
.ma-table tbody tr:hover {
    background: rgba(0, 55, 85, 0.03);
}
.ma-cell-title {
    font-weight: 600;
    color: #212529;
}
.ma-col-acao {
    width: 56px;
    white-space: nowrap;
}
.ma-btn-acao {
    width: 34px;
    height: 34px;
    padding: 0;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ma-tr {
    transition: background 0.15s ease;
}
.ma-item-card {
    border-radius: 10px;
    overflow: hidden;
    transition:
        background 0.15s ease,
        box-shadow 0.15s ease;
}
.ma-item-card:hover {
    box-shadow: 0 0.25rem 0.65rem rgba(0, 55, 85, 0.1) !important;
}
.ma-cards-lista {
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}
.ma-grupo-header {
    border-radius: 8px;
    overflow: hidden;
}
.ma-k {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
    display: block;
    margin-bottom: 0.15rem;
}
.ma-valor-destaque {
    font-size: 0.8rem;
    font-weight: 600;
    color: #003755;
    line-height: 1.35;
    letter-spacing: 0.01em;
}
.ma-valor-destaque--inline {
    display: inline;
}
.ma-como-destaque {
    padding: 0.55rem 0.85rem;
    border-radius: 8px;
    border: 1px solid rgba(0, 55, 85, 0.18);
    border-left: 4px solid #003755;
    background: rgba(0, 55, 85, 0.06);
    box-shadow: 0 1px 2px rgba(0, 55, 85, 0.06);
    width: max-content;
}
.ma-como-destaque--auto {
    border-color: rgba(21, 31, 33, 0.35);
    border-left-color: #003755;
    background: rgba(23, 162, 184, 0.08);
    box-shadow: 0 1px 2px rgba(23, 162, 184, 0.08);
    width: max-content;
}
.ma-como-destaque__rotulo {
    display: block;
    margin-bottom: 0;
    color: #333;
}
.ma-badge-ano {
    font-weight: 600;
    font-size: 0.75rem;
}
.ma-item-hr {
    border-color: rgba(0, 55, 85, 0.08);
}
.ma-min-w-0 {
    min-width: 0;
}
.ma-item-card .ma-status-badge {
    margin-right: 0.35rem;
    margin-bottom: 0.25rem;
}
.ma-tr--pend-auto,
.ma-item-card.ma-tr--pend-auto {
    border-left: 4px solid #dc3545;
    background: rgba(220, 53, 69, 0.06);
}
.ma-tr--pend-auto-colab,
.ma-item-card.ma-tr--pend-auto-colab {
    border-left: 4px solid #e83e8c;
    background: rgba(232, 62, 140, 0.08);
}
.ma-tr--pend-par,
.ma-item-card.ma-tr--pend-par {
    border-left: 4px solid #ffc107;
    background: rgba(255, 193, 7, 0.1);
}
.ma-tr--pend-gestor,
.ma-item-card.ma-tr--pend-gestor {
    border-left: 4px solid #5c4d9d;
    background: rgba(92, 77, 157, 0.07);
}
.ma-tr--done,
.ma-item-card.ma-tr--done {
    border-left: 4px solid #28a745;
    background: rgba(40, 167, 69, 0.08);
}
.ma-tr--gestor-pend,
.ma-item-card.ma-tr--gestor-pend {
    border-left: 4px solid #adb5bd;
    background: rgba(173, 181, 189, 0.12);
}
.ma-tr--gestor-avaliada,
.ma-item-card.ma-tr--gestor-avaliada {
    border-left: 4px solid #1565c0;
    background: rgba(21, 101, 192, 0.07);
}
.ma-tr--gestor-pend-final,
.ma-item-card.ma-tr--gestor-pend-final {
    border-left: 4px solid #fd7e14;
    background: rgba(253, 126, 20, 0.1);
}
.ma-tr--pend-avaliacao-final,
.ma-item-card.ma-tr--pend-avaliacao-final {
    border-left: 4px solid #fd7e14;
    background: rgba(253, 126, 20, 0.1);
}
.ma-tr--avaliada-sem-final-pend,
.ma-item-card.ma-tr--avaliada-sem-final-pend {
    border-left: 4px solid #1565c0;
    background: rgba(21, 101, 192, 0.07);
}
.ma-tr--gestor-done,
.ma-item-card.ma-tr--gestor-done {
    border-left: 4px solid #28a745;
    background: rgba(40, 167, 69, 0.08);
}
.ma-status-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.35em 0.65em;
    border-radius: 6px;
    white-space: normal;
    max-width: 160px;
    line-height: 1.2;
}
.ma-status-badge--pend {
    background: #e9ecef;
    color: #495057;
    border: 1px solid #ced4da;
}
.ma-status-badge--avaliada {
    background: rgba(21, 101, 192, 0.12);
    color: #0d47a1;
    border: 1px solid rgba(21, 101, 192, 0.4);
}
.ma-status-badge--final-pend {
    background: rgba(253, 126, 20, 0.2);
    color: #7c2d12;
    border: 1px solid rgba(253, 126, 20, 0.45);
    max-width: 200px;
}
.ma-status-badge--ok {
    background: rgba(40, 167, 69, 0.15);
    color: #155724;
    border: 1px solid rgba(40, 167, 69, 0.35);
}
.ma-empty {
    padding: 2rem 1rem;
}

.ma-agrupado-hint {
    border-bottom: 1px solid rgba(0, 55, 85, 0.06);
}
.ma-grupo-tr {
    background: transparent !important;
}
.ma-grupo-tr:hover {
    background: transparent !important;
}
.ma-grupo-td {
    border-top: none !important;
    border-bottom: none !important;
    padding-top: 0.35rem !important;
    padding-bottom: 0 !important;
}
.ma-grupo-label {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.85rem;
    border-radius: 8px;
    font-size: 0.82rem;
}
.ma-grupo-ico {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(0, 55, 85, 0.08);
    color: #003755;
}
.ma-grupo-texto {
    flex: 1;
    min-width: 0;
}
.ma-grupo-texto strong {
    color: #212529;
}
.ma-grupo-sub {
    display: block;
    font-size: 0.72rem;
    color: #6c757d;
    font-weight: 500;
    margin-top: 0.15rem;
}
@media (min-width: 576px) {
    .ma-grupo-sub {
        display: inline;
        margin-left: 0.5rem;
        margin-top: 0;
    }
}
.ma-grupo-count {
    font-weight: 700;
    font-size: 0.75rem;
    background: rgba(0, 55, 85, 0.12) !important;
    color: #003755 !important;
}
.ma-grupo--pend .ma-grupo-label {
    background: rgba(255, 193, 7, 0.14);
    border-left: 4px solid #ffc107;
}
.ma-grupo--pend .ma-grupo-ico {
    background: rgba(255, 193, 7, 0.35);
    color: #856404;
}
.ma-grupo--pend-final .ma-grupo-label {
    background: rgba(253, 126, 20, 0.14);
    border-left: 4px solid #fd7e14;
}
.ma-grupo--pend-final .ma-grupo-ico {
    background: rgba(253, 126, 20, 0.3);
    color: #7c2d12;
}
.ma-grupo--avaliada .ma-grupo-label {
    background: rgba(21, 101, 192, 0.1);
    border-left: 4px solid #1565c0;
}
.ma-grupo--avaliada .ma-grupo-ico {
    background: rgba(21, 101, 192, 0.2);
    color: #0d47a1;
}
.ma-grupo--final .ma-grupo-label {
    background: rgba(40, 167, 69, 0.15);
    border-left: 4px solid #28a745;
}
.ma-grupo--final .ma-grupo-ico {
    background: rgba(40, 167, 69, 0.25);
    color: #155724;
}
/* Painel de referência (avaliador final — notas e comentários anteriores) */
.ma-ref-panel {
    margin-top: 1rem;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #f5f9fc 0%, #ffffff 55%, #f8fafc 100%);
    border: 1px solid rgba(0, 55, 85, 0.12);
    border-left: 4px solid #003755;
    box-shadow: 0 4px 14px rgba(0, 55, 85, 0.06);
}
.ma-ref-panel--coment {
    border-left-color: #1565c0;
    background: linear-gradient(135deg, #f3f7fb 0%, #ffffff 60%, #f9fbfd 100%);
}
.ma-ref-panel__head {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #003755;
    margin-bottom: 0.65rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(0, 55, 85, 0.1);
}
.ma-ref-panel__head .fa {
    opacity: 0.9;
}
.ma-ref-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.45rem 0;
    border-bottom: 1px dashed rgba(0, 55, 85, 0.08);
}
.ma-ref-line:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.ma-ref-line__who {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    min-width: 0;
}
.ma-ref-line__text {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.ma-ref-line__title {
    font-size: 0.88rem;
    font-weight: 600;
    color: #212529;
    line-height: 1.25;
}
.ma-ref-line__sub {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.2;
}
.ma-ref-ico {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}
.ma-ref-ico--self {
    background: rgba(21, 101, 192, 0.12);
    color: #1565c0;
}
.ma-ref-ico--peer {
    background: rgba(0, 55, 85, 0.1);
    color: #003755;
}
.ma-ref-nota-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.35rem;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
    font-weight: 800;
    font-size: 0.95rem;
    line-height: 1;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.ma-ref-nota-pill--1 {
    background: #fdecea;
    color: #c62828;
    border: 1px solid rgba(198, 40, 40, 0.25);
}
.ma-ref-nota-pill--2 {
    background: #fff3e0;
    color: #e65100;
    border: 1px solid rgba(230, 81, 0, 0.2);
}
.ma-ref-nota-pill--3 {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid rgba(46, 125, 50, 0.2);
}
.ma-ref-nota-pill--4 {
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid rgba(21, 101, 192, 0.2);
}
.ma-ref-nota-pill--5 {
    background: #e8eaf6;
    color: #283593;
    border: 1px solid rgba(40, 53, 147, 0.25);
}
.ma-ref-coment {
    padding: 0.5rem 0 0.35rem;
}
.ma-ref-coment + .ma-ref-coment {
    border-top: 1px dashed rgba(0, 55, 85, 0.1);
    margin-top: 0.35rem;
    padding-top: 0.75rem;
}
.ma-ref-coment__label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #495057;
    margin-bottom: 0.35rem;
}
.ma-ref-coment__nome {
    font-weight: 500;
    text-transform: none;
    letter-spacing: normal;
    color: #6c757d;
}
.ma-ref-coment__text {
    font-size: 0.88rem;
    line-height: 1.45;
    color: #343a40;
    margin: 0;
    padding: 0.55rem 0.65rem;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 8px;
    border: 1px solid rgba(0, 55, 85, 0.08);
}

/* Card fluxo da avaliação */
.ma-fluxo-card {
    overflow: hidden;
    position: relative;
}
.ma-fluxo-card__accent {
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #003755, #1a5f7a, #1565c0);
    border-radius: 0;
}
.ma-fluxo-card__title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #212529;
    letter-spacing: -0.01em;
}
.ma-fluxo-card__subtitle {
    font-size: 0.8rem;
    color: #6c757d;
}
.ma-fluxo-card__titulo-av {
    color: #003755;
    font-weight: 600;
}
.ma-fluxo-steps {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.25rem;
}
.ma-fluxo-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.65rem 0.35rem 0.35rem;
    background: #fff;
    border: 1px solid rgba(0, 55, 85, 0.14);
    border-radius: 999px;
    box-shadow: 0 2px 8px rgba(0, 55, 85, 0.06);
}
.ma-fluxo-chip__num {
    width: 1.65rem;
    height: 1.65rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 800;
    color: #fff;
    background: linear-gradient(145deg, #003755, #1a5f7a);
    flex-shrink: 0;
}
.ma-fluxo-chip__txt {
    font-size: 0.8rem;
    font-weight: 600;
    color: #212529;
    max-width: 14rem;
    line-height: 1.25;
}
.ma-fluxo-chip__sep {
    color: rgba(0, 55, 85, 0.35);
    font-size: 0.65rem;
    margin: 0 0.1rem;
}
@media (max-width: 575.98px) {
    .ma-fluxo-chip__txt {
        max-width: 11rem;
    }
}

/* Modais — hierarquia de textos */
.ma-modal-fieldset {
    margin-bottom: 1.35rem;
}
.ma-modal-legend {
    float: none;
    width: auto;
    font-size: 1rem;
    font-weight: 700;
    color: #003755;
    letter-spacing: 0.01em;
    padding: 0 0 0.35rem 0;
    margin-bottom: 0.35rem;
    border-bottom: 2px solid rgba(0, 55, 85, 0.12);
}
.ma-modal-legend--sub {
    font-size: 0.88rem;
    font-weight: 600;
    color: #495057;
    border-bottom-width: 1px;
    border-bottom-color: rgba(0, 55, 85, 0.08);
}
.ma-modal-lead {
    font-size: 0.84rem;
    color: #6c757d;
    line-height: 1.5;
    margin: 0 0 1rem 0;
}
.ma-modal-lead--tight {
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
}
.ma-modal-section-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #212529;
    letter-spacing: -0.01em;
}
.ma-modal-label-muted {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin-bottom: 0.35rem;
}
.ma-modal-chart-name {
    font-size: 1.05rem;
    font-weight: 700;
    color: #003755;
    margin-bottom: 0.5rem;
}
.ma-modal-chart-media {
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
    margin-top: 0.75rem;
}
.ma-modal-nota-final {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    padding: 0.85rem 1.25rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #f5f9fc 0%, #ffffff 100%);
    border: 1px solid rgba(0, 55, 85, 0.14);
    box-shadow: 0 6px 20px rgba(0, 55, 85, 0.08);
}
.ma-modal-nota-final__rotulo {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6c757d;
}
.ma-modal-nota-final__valor {
    font-size: 1.65rem;
    font-weight: 800;
    color: #003755;
    line-height: 1.1;
}
.ma-modal-plano-item {
    margin-top: 1rem;
    padding: 1rem 1rem 0.5rem;
    border-radius: 10px;
    border: 1px solid rgba(0, 55, 85, 0.1);
    background: rgba(255, 255, 255, 0.95);
}
</style>
