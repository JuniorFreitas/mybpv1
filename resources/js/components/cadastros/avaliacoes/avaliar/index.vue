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
                        <table class="table ma-resultado-competencia-table" v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado" :key="index">
                            <thead>
                                <tr>
                                    <!-- CORREÇÃO APLICADA: Mudança de item[index] para item[0] com guard -->
                                    <th class="ma-resultado-competencia-table__head ma-resultado-competencia-table__head--criterio">
                                        {{ (item[0] || {}).topico_pai || '' }}
                                    </th>
                                    <!-- CORREÇÃO APLICADA: Adicionado guard para avaliadores -->
                                    <th
                                        class="text-center ma-resultado-competencia-table__head"
                                        v-for="(avaliador, id) in (item[0] || {}).avaliadores || []"
                                        :key="avaliador.id"
                                    >
                                        <span>
                                            {{ tituloEtapaFluxoColuna(id, avaliador) }}
                                        </span>
                                    </th>
                                    <th class="text-center ma-resultado-competencia-table__head ma-resultado-competencia-table__head--media">Média</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- CORREÇÃO APLICADA: Substituído $index por subIndex -->
                                <tr v-for="(sub, subIndex) in item" :key="sub.id || subIndex">
                                    <td style="width: 39%" class="ma-resultado-competencia-table__criterio">{{ sub.subtopico }}</td>
                                    <!-- CORREÇÃO APLICADA: Adicionado guard para avaliadores e filtro casasDecimais -->
                                    <td style="width: 14%" v-for="(avaliador, avalIndex) in sub.avaliadores || []" :key="avaliador.id || avalIndex">
                                        <div class="ma-resultado-nota" :class="classeNotaResultado(avaliador.nota)">
                                            <span class="ma-resultado-nota__numero">{{ formatarDecimal(avaliador.nota) }}</span>
                                            <span class="ma-resultado-nota__texto">{{ textoNotaResultado(avaliador.nota) }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 9%" class="text-center">
                                        <div class="ma-resultado-nota ma-resultado-nota--media" :class="classeNotaResultado(sub.media)">
                                            <span class="ma-resultado-nota__numero">{{ formatarDecimal(sub.media) }}</span>
                                            <span class="ma-resultado-nota__texto">{{ textoNotaResultado(sub.media) }}</span>
                                        </div>
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
                        <div class="ma-modal-comments-panel">
                            <div
                                v-for="(avaliador, avalIndex) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                :key="avaliador.id || avalIndex"
                                class="ma-modal-comment-item"
                            >
                                <div class="ma-modal-comment-item__head">
                                    <div class="ma-modal-comment-item__title">
                                        <i class="fa fa-user mr-2 text-primary"></i>
                                        <span>{{ tituloEtapaFluxoColuna(avalIndex, avaliador) }}</span>
                                    </div>
                                </div>

                                <div class="ma-modal-comment-item__body">
                                    {{ avaliador.comentario || 'Sem comentário registrado nesta etapa.' }}
                                </div>
                            </div>
                        </div>
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

                        <div class="col-12 col-md-6 col-lg-3 mt-2 mt-lg-0">
                            <div class="form-group mb-2 mb-md-0">
                                <label class="ma-label" for="ma-filtro-legenda-input">Fluxo da avaliação</label>
                                <combobox-auto-complete
                                    ref="comboLegenda"
                                    instance-id="legenda"
                                    v-model="controle.dados.campoLegenda"
                                    :options="legendaComboboxOpcoes"
                                    :disabled="controle.carregando"
                                    input-id="ma-filtro-legenda-input"
                                    empty-message="Nenhuma etapa encontrada."
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectLegendaCombobox"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 mt-2 mt-lg-0">
                            <div class="form-group mb-2 mb-md-0">
                                <label class="ma-label" for="ma-filtro-avaliador-input">Avaliador</label>
                                <combobox-auto-complete
                                    ref="comboAvaliador"
                                    instance-id="avaliador"
                                    v-model="controle.dados.campoAvaliador"
                                    :options="avaliadoresComboboxOpcoes"
                                    :disabled="controle.carregando"
                                    input-id="ma-filtro-avaliador-input"
                                    empty-message="Nenhum avaliador encontrado."
                                    :max-results="100"
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectAvaliadorCombobox"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 mt-2 mt-lg-0">
                            <div class="form-group mb-2 mb-md-0">
                                <label class="ma-label" for="ma-filtro-colaborador-input">Colaborador</label>
                                <combobox-auto-complete
                                    ref="comboColaborador"
                                    instance-id="colaborador"
                                    v-model="controle.dados.campoColaborador"
                                    :options="colaboradoresComboboxOpcoes"
                                    :disabled="controle.carregando"
                                    input-id="ma-filtro-colaborador-input"
                                    empty-message="Nenhum colaborador encontrado."
                                    :max-results="100"
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectColaboradorCombobox"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 mt-2 mt-lg-0">
                            <div class="form-group mb-2 mb-md-0">
                                <label class="ma-label" for="ma-filtro-como-input">Como</label>
                                <combobox-auto-complete
                                    ref="comboComo"
                                    instance-id="como"
                                    v-model="controle.dados.campoComo"
                                    :options="comoComboboxOpcoes"
                                    :disabled="controle.carregando"
                                    input-id="ma-filtro-como-input"
                                    empty-message="Nenhum tipo de avaliador encontrado."
                                    :max-results="100"
                                    @opening="fecharOutrosFiltros($event)"
                                    @select="onSelectComoCombobox"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-lg-12 mt-3 d-flex flex-wrap justify-content-start">
                            <button type="button" class="btn btn-sm btn-primary ma-btn-atualizar px-3" :disabled="controle.carregando" @click="atualizar">
                                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                                Atualizar lista
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary ma-btn-limpar px-3 ml-2"
                                :disabled="controle.carregando"
                                @click="limparFiltros"
                            >
                                <i class="fa fa-eraser mr-1"></i>
                                Limpar filtros
                            </button>

                            <button
                                v-if="tem_privilegio_gestao_rh && selecionadaAvaliacao && selecionadaAvaliacao.status === 'Aberta'"
                                type="button"
                                class="btn btn-sm btn-primary ma-btn-atualizar px-3 ml-2"
                                :disabled="controle.carregando || notificandoPendentes"
                                @click="notificarPendentes"
                            >
                                <i :class="notificandoPendentes ? 'fa fa-bell fa-spin mr-1' : 'fa fa-bell mr-1'"></i>
                                Notificar pendentes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div
                class="card ma-card ma-legenda shadow border-0 mb-3"
                v-if="!controle.carregando && selecionadaAvaliacao && selecionadaAvaliacao.auto_avaliacao"
            >
                <!-- <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="font-weight-bold mr-2 mb-1 w-100">Legenda:</span>
                        <span class="badge ma-legend-pill ma-legend-pill--danger mb-1 mr-1">Pendente autoavaliação</span>
                        <span class="badge ma-legend-pill ma-legend-pill--pink mb-1 mr-1">Pendente autoavaliação colaborador</span>
                        <span class="badge ma-legend-pill ma-legend-pill--warning mb-1 mr-1">Pendente avaliação do par</span>
                        <span class="badge ma-legend-pill ma-legend-pill--gestor-wait mb-1 mr-1">Pendente avaliação gestor</span>
                        <span class="badge ma-legend-pill ma-legend-pill--warn-final mb-1 mr-1">Falta avaliação final</span>
                        <span class="badge ma-legend-pill ma-legend-pill--success mb-1 mr-1">Completa</span>
                    </div>
                </div> -->
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

            <div class="alert alert-light border text-center ma-empty rounded shadow-sm" v-show="!controle.carregando && listaExibicao.length === 0">
                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                <span class="">Nenhum registro encontrado para os filtros selecionados.</span>
            </div>

            <div v-show="!controle.carregando && listaExibicao.length > 0">
                <div class="" v-if="selecionadaAvaliacao">
                    <div class="">
                        <div v-for="card in cardsColaboradoresExibicao" :key="`col-${card.funcionario.id}`" class="card ma-colaborador-card shadow-sm">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1 pr-2 ma-min-w-0">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div class="ma-colaborador-head mb-1">
                                                <span class="ma-colaborador-nome">{{ card.funcionario.nome }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <span class="d-inline-block mr-2">{{ card.avaliacao.avaliacao_tipo.nome }}</span>
                                            <span class="text-nowrap"><i class="fa fa-calendar-alt mr-1"></i>{{ card.avaliacao.data_fim_prazo }}</span>
                                        </div>
                                        <div class="mt-2 d-flex flex-wrap align-items-center">
                                            <span class="ma-status-inline mr-2 mb-1">{{ statusResumoCard(card) }}</span>
                                            <span v-if="mostrarDetalhesFluxoCard(card)" class="ma-fluxo-inline mb-1">{{
                                                statusFluxoCard(card.principalItem)
                                            }}</span>
                                            <span
                                                v-if="mostrarDetalhesFluxoCard(card) && statusPdiExtra(card.principalItem)"
                                                class="ma-fluxo-inline ma-fluxo-inline--pending mb-1"
                                                >{{ statusPdiExtra(card.principalItem) }}</span
                                            >
                                            <span class="ma-progress-chip mb-1">{{ percentualConclusaoCard(card) }}% concluído</span>
                                        </div>
                                        <div class="ma-card-summary mt-3">
                                            <div class="ma-card-summary__item">
                                                <span class="ma-card-summary__label">Progresso</span>
                                                <span class="ma-card-summary__value">{{ etapasConcluidasResumo(card) }}</span>
                                            </div>
                                            <div class="ma-card-summary__item" v-if="mostrarProximoFluxoCard(card)">
                                                <span class="ma-card-summary__label">Próximo no fluxo</span>
                                                <span class="ma-card-summary__value">{{ resumoProximoFluxoCard(card) }}</span>
                                            </div>
                                            <div class="ma-card-summary__item" v-if="mostrarDetalhesFluxoCard(card) && statusPdiExtra(card.principalItem)">
                                                <span class="ma-card-summary__label">PDI</span>
                                                <span class="ma-card-summary__value">{{ statusPdiExtra(card.principalItem) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-start justify-content-end">
                                        <button
                                            v-if="feedbackVisualizavelDoUsuario(card) && !tem_privilegio_gestao_rh"
                                            type="button"
                                            class="btn btn-sm btn-primary ma-btn-atualizar mr-2 mb-2"
                                            @click="abrirModalAvaliacao(feedbackVisualizavelDoUsuario(card), true)"
                                        >
                                            <i class="fa fa-eye mr-1"></i>
                                            Visualizar sua avaliação
                                        </button>
                                        <button
                                            v-if="podeEditarPdi(card.principalItem)"
                                            type="button"
                                            class="btn btn-sm btn-primary ma-btn-atualizar mr-2 mb-2"
                                            @click="abrirModalEdicaoPdi(card.principalItem)"
                                        >
                                            <i class="fa fa-tasks mr-1"></i>
                                            Acompanhar PDI
                                        </button>
                                        <button
                                            v-if="podeVisualizarAvaliacaoFinal(card.principalItem)"
                                            type="button"
                                            class="btn btn-sm btn-primary ma-btn-atualizar mr-2 mb-2"
                                            @click="abrirModalAvaliacaoFinal(card.principalItem, true)"
                                        >
                                            <i class="fa fa-clipboard-list mr-1"></i>
                                            PDI
                                        </button>
                                        <a
                                            v-if="podeImprimirAvaliacaoFinal(card.principalItem)"
                                            class="btn btn-sm btn-primary ma-btn-atualizar mb-2"
                                            :href="`${urlImpressao}/${card.principalItem.token}`"
                                            target="_blank"
                                        >
                                            <i class="fa fa-print mr-1"></i>
                                            Imprimir
                                        </a>
                                    </div>
                                </div>

                                <div v-if="mostrarDetalhesFluxoCard(card)" class="ma-colaborador-fluxo">
                                    <div class="ma-etapas-strip">
                                        <div
                                            v-for="etapa in fluxoEtapasVisiveisColaborador(card)"
                                            :key="`${card.funcionario.id}-${etapa.key}`"
                                            class="ma-etapa-chip"
                                            :class="[
                                                `ma-etapa-chip--${etapa.state}`,
                                                {
                                                    'ma-etapa-chip--current': etapa.isCurrent,
                                                    'ma-etapa-chip--active': etapaAtivaColaborador(card)?.key === etapa.key
                                                }
                                            ]"
                                            @click="setEtapaAtiva(card, etapa)"
                                        >
                                            <span class="ma-etapa-chip__dot"></span>
                                            <span class="ma-etapa-chip__label">{{ etapa.label }}</span>
                                            <span class="ma-etapa-chip__badge">{{ etapa.badge }}</span>
                                        </div>
                                    </div>

                                    <div
                                        v-if="etapaAtivaColaborador(card)"
                                        class="ma-etapa-card"
                                        :class="[
                                            `ma-etapa-card--${etapaAtivaColaborador(card).state}`,
                                            { 'ma-etapa-card--current': etapaAtivaColaborador(card).isCurrent }
                                        ]"
                                    >
                                        <div class="ma-etapa-card__head">
                                            <div>
                                                <span class="ma-etapa-card__title">{{ etapaAtivaColaborador(card).label }}</span>
                                                <span class="ma-etapa-card__subtitle">{{ etapaAtivaColaborador(card).resumo }}</span>
                                                <span
                                                    v-if="mostrarProximoFluxoCard(card) && etapaAtivaColaborador(card).proximoTexto"
                                                    class="ma-etapa-card__next"
                                                    >Próximo: {{ etapaAtivaColaborador(card).proximoTexto }}</span
                                                >
                                                <span v-else-if="resumoConclusaoPessoal(card)" class="ma-etapa-card__next">{{
                                                    resumoConclusaoPessoal(card)
                                                }}</span>
                                                <span
                                                    v-if="mostrarProximoFluxoCard(card) && etapaAtivaColaborador(card).faltantesTexto"
                                                    class="ma-etapa-card__missing"
                                                    >Faltam: {{ etapaAtivaColaborador(card).faltantesTexto }}</span
                                                >
                                            </div>
                                            <div class="ma-etapa-card__meta">
                                                <button
                                                    v-if="etapaAtivaColaborador(card).podeExpandir"
                                                    type="button"
                                                    class="btn btn-link btn-sm ma-etapa-toggle"
                                                    @click="toggleEtapaExpandida(card, etapaAtivaColaborador(card))"
                                                >
                                                    {{ etapaExpandida(card, etapaAtivaColaborador(card)) ? 'Recolher' : 'Expandir' }}
                                                </button>
                                                <span
                                                    class="ma-etapa-badge"
                                                    :class="[
                                                        `ma-etapa-badge--${etapaAtivaColaborador(card).state}`,
                                                        { 'ma-etapa-badge--current': etapaAtivaColaborador(card).isCurrent }
                                                    ]"
                                                    >{{ etapaAtivaColaborador(card).badge }}</span
                                                >
                                            </div>
                                        </div>

                                        <div v-if="etapaAtivaColaborador(card).avaliacoes.length" class="ma-etapa-lista">
                                            <div
                                                v-for="avaliacaoItem in itensVisiveisEtapa(card, etapaAtivaColaborador(card))"
                                                :key="avaliacaoItem.id"
                                                class="ma-etapa-linha"
                                                :class="{ 'ma-etapa-linha--next': isPrimeiroPendenteDaEtapa(etapaAtivaColaborador(card), avaliacaoItem) }"
                                            >
                                                <div class="ma-etapa-linha__info">
                                                    <span class="ma-etapa-linha__nome">
                                                        <i
                                                            :class="iconeEstadoLinha(etapaAtivaColaborador(card), avaliacaoItem)"
                                                            class="ma-etapa-linha__icone mr-2"
                                                        ></i
                                                        >{{ avaliacaoItem.avaliador.nome }}
                                                    </span>
                                                    <span class="ma-etapa-linha__como">{{ rotuloComoEtapa(avaliacaoItem) }}</span>
                                                </div>

                                                <div class="ma-etapa-linha__acoes">
                                                    <span class="ma-etapa-linha__status">{{ statusLinhaFluxo(avaliacaoItem) }}</span>

                                                    <button
                                                        v-if="tem_privilegio_gestao_rh && podeNotificarPendente(avaliacaoItem)"
                                                        type="button"
                                                        class="btn btn-sm btn-primary ma-btn-atualizar mr-1 mb-1"
                                                        :disabled="notificandoFeedbackIds[avaliacaoItem.id]"
                                                        @click="notificarPendente(avaliacaoItem)"
                                                    >
                                                        <i
                                                            :class="notificandoFeedbackIds[avaliacaoItem.id] ? 'fa fa-bell fa-spin mr-1' : 'fa fa-bell mr-1'"
                                                        ></i>
                                                        Notificar
                                                    </button>

                                                    <button
                                                        v-if="etapaAtivaColaborador(card).key !== 'final' && podeAvaliarItem(avaliacaoItem)"
                                                        type="button"
                                                        class="btn btn-sm btn-primary ma-btn-atualizar mr-1 mb-1"
                                                        @click="abrirModalAvaliacao(avaliacaoItem)"
                                                    >
                                                        <i class="fa fa-pen mr-1"></i>
                                                        Avaliar
                                                    </button>

                                                    <button
                                                        v-if="etapaAtivaColaborador(card).key !== 'final' && podeVisualizarItem(avaliacaoItem)"
                                                        type="button"
                                                        class="btn btn-sm btn-primary ma-btn-atualizar mr-1 mb-1"
                                                        @click="abrirModalAvaliacao(avaliacaoItem, true)"
                                                    >
                                                        <i class="fa fa-eye mr-1"></i>
                                                        Visualizar
                                                    </button>

                                                    <button
                                                        v-if="podeFazerAvaliacaoFinal(avaliacaoItem)"
                                                        type="button"
                                                        class="btn btn-sm btn-primary ma-btn-atualizar mr-1 mb-1"
                                                        @click="abrirModalAvaliacaoFinal(avaliacaoItem)"
                                                    >
                                                        <i class="fa fa-route mr-1"></i>
                                                        Plano de acao (PDI)
                                                    </button>
                                                </div>
                                            </div>

                                            <div
                                                v-if="etapaAtivaColaborador(card).podeExpandir && !etapaExpandida(card, etapaAtivaColaborador(card))"
                                                class="ma-etapa-collapse-hint"
                                            >
                                                Mostrando {{ itensVisiveisEtapa(card, etapaAtivaColaborador(card)).length }} de
                                                {{ etapaAtivaColaborador(card).avaliacoes.length }} avaliador(es).
                                            </div>
                                        </div>

                                        <div v-else class="ma-etapa-vazia">Nenhum avaliador definido para esta etapa do fluxo.</div>
                                    </div>
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
        currentUserId: {
            type: Number,
            required: false,
            default: null
        },
        currentUserName: {
            type: String,
            required: false,
            default: ''
        },
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
            lista_fluxo_completo: [],
            lista_topicos: [],
            lista_avaliacoes_tipos: [],
            lista_avaliacoes: [],
            lista_anos: [],
            lista_status: [],
            lista_avaliadores_filtro: [],
            lista_colaboradores_filtro: [],
            lista_como_filtro: [],

            lista_avaliacoes_por_ano: [],

            avaliacaoSelecionada: null,

            dropdownAbertoKey: null,
            etapasAtivasPorCard: {},
            etapasExpandidas: {},
            notificandoPendentes: false,
            notificandoFeedbackIds: {},

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
                    campoLegenda: '',
                    campoAvaliador: '',
                    campoColaborador: '',
                    campoComo: '',
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
        gruposListaAuto() {
            return this.agruparPorStatus(this.listaExibicao)
        },
        gruposListaGestor() {
            return this.agruparPorStatus(this.listaExibicao.filter((i) => !i.avaliacao.auto_avaliacao && i.principal))
        },
        totalRegistrosListaGestor() {
            return this.listaExibicao.filter((i) => !i.avaliacao.auto_avaliacao && i.principal).length
        },
        listaExibicao() {
            return this.lista || []
        },
        cardsColaboradoresExibicao() {
            const gruposPermitidos = new Set(this.listaExibicao.map((item) => `${item.avaliacao_id}-${item.funcionario_id}`))

            const baseFluxo = (this.lista_fluxo_completo || []).filter((item) => gruposPermitidos.has(`${item.avaliacao_id}-${item.funcionario_id}`))

            return this.agruparPorColaborador(baseFluxo)
        },
        feedbacksPermitidosMap() {
            return (this.lista || []).reduce((acc, item) => {
                acc[item.id] = true
                return acc
            }, {})
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
        legendaComboboxOpcoes() {
            const todos = { value: '', label: 'Todas as etapas do fluxo' }
            const lista = this.selecionadaAvaliacao?.auto_avaliacao
                ? [
                      { value: 'autoavaliacao_pendente', label: 'Passo 1: falta autoavaliação' },
                      { value: 'autoavaliacao_realizada', label: 'Passo 1: autoavaliação concluída' },
                      { value: 'avaliacao_par_pendente', label: 'Passo 2: falta avaliação do par' },
                      { value: 'avaliacao_par_realizada', label: 'Passo 2: avaliação do par concluída' },
                      { value: 'avaliacao_gestor_pendente', label: 'Passo 3: falta avaliação do gestor' },
                      { value: 'avaliacao_gestor_realizada', label: 'Passo 3: avaliação do gestor concluída' },
                      { value: 'fluxo_concluido', label: 'Fluxo concluído' },
                      { value: 'acompanhamento_plano_acao', label: 'Acompanhamento plano de ação' }
                  ]
                : [
                      { value: 'avaliacao_gestor_pendente', label: 'Passo 1: falta avaliação do gestor' },
                      { value: 'avaliacao_gestor_realizada', label: 'Passo 1: avaliação do gestor concluída' },
                      { value: 'fluxo_concluido', label: 'Fluxo concluído' },
                      { value: 'acompanhamento_plano_acao', label: 'Acompanhamento plano de ação' }
                  ]
            return [todos, ...lista]
        },
        avaliadoresComboboxOpcoes() {
            return this.ordenarOpcoesPessoaComUsuarioLogadoPrimeiro(this.mapearPessoasParaCombobox(this.lista_avaliadores_filtro, 'avaliador'))
        },
        colaboradoresComboboxOpcoes() {
            return this.ordenarOpcoesPessoaComUsuarioLogadoPrimeiro(this.mapearPessoasParaCombobox(this.lista_colaboradores_filtro, 'funcionario'))
        },
        comoComboboxOpcoes() {
            return [{ value: '', label: 'Todos os tipos' }, ...(this.lista_como_filtro || [])]
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
            if (avaliador && avaliador.tipo) {
                return avaliador.tipo
            }
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
        classeNotaResultado(nota) {
            const v = Math.round(Number(nota))
            if (v >= 1 && v <= 5) {
                return `ma-resultado-nota--${v}`
            }
            return 'ma-resultado-nota--neutro'
        },
        textoNotaResultado(nota) {
            const v = Math.round(Number(nota))
            if (v === 1) return 'Muito abaixo'
            if (v === 2) return 'Abaixo'
            if (v === 3) return 'Atingiu'
            if (v === 4) return 'Superou'
            if (v === 5) return 'Superou muito'
            return 'Sem nota'
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
        agruparPorColaborador(itens) {
            const grupos = _.groupBy(itens || [], 'funcionario_id')

            return Object.values(grupos)
                .map((grupo) => {
                    const ordenados = [...grupo].sort((a, b) => {
                        if (a.principal && !b.principal) return 1
                        if (!a.principal && b.principal) return -1
                        return String(a.avaliador?.nome || '').localeCompare(String(b.avaliador?.nome || ''))
                    })

                    const principalItem = grupo.find((item) => item.principal) || grupo[0]

                    return {
                        funcionario: principalItem.funcionario,
                        avaliacao: principalItem.avaliacao,
                        principalItem,
                        itens: ordenados
                    }
                })
                .sort((a, b) => {
                    const aEhUsuarioLogado = this.isPropriaAvaliacaoColaborador(a)
                    const bEhUsuarioLogado = this.isPropriaAvaliacaoColaborador(b)

                    if (aEhUsuarioLogado && !bEhUsuarioLogado) {
                        return -1
                    }

                    if (!aEhUsuarioLogado && bEhUsuarioLogado) {
                        return 1
                    }

                    return String(a.funcionario?.nome || '').localeCompare(String(b.funcionario?.nome || ''))
                })
        },
        pesoStatusColaborador(item) {
            if (!item) return 99
            if (item.status === 'Pendente') return 1
            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) return 2
            if (item.status === 'Avaliada') return 3
            if (item.status === 'Finalizada') return 4
            return 99
        },
        fluxoEtapasColaborador(card) {
            const etapas = []
            const avaliacao = this.selecionadaAvaliacao

            if (!avaliacao) {
                return etapas
            }

            if (avaliacao.auto_avaliacao) {
                const autoItems = card.itens.filter((item) => item.origem_feedback === 'Funcionario' && !item.principal)
                const etapaAuto = this.montarEtapaColaborador('auto', 'Autoavaliação', autoItems, 'O colaborador inicia este ciclo com a autoavaliação.')
                if (etapaAuto) {
                    etapas.push(etapaAuto)
                }
            }

            ;(avaliacao.fluxo || []).forEach((etapa, index) => {
                const labelBase = etapa.principal && !String(etapa.label || '').includes('(Avaliador Final)') ? `${etapa.label} (Avaliador Final)` : etapa.label
                const label = this.rotuloEtapaCurto(labelBase)
                const itensEtapa = card.itens.filter(
                    (item) => Number(item.avaliacao_tipo_id) === Number(etapa.id) && Boolean(item.principal) === Boolean(etapa.principal)
                )

                const etapaFluxo = this.montarEtapaColaborador(`fluxo-${index}`, label, itensEtapa, 'Etapa definida no fluxo configurado da avaliação.')
                if (etapaFluxo) {
                    etapas.push(etapaFluxo)
                }
            })

            const indiceAtual = etapas.findIndex((etapa) => etapa.state === 'pending')
            const indiceFallback = indiceAtual === -1 ? etapas.map((etapa) => etapa.state).lastIndexOf('done') : -1

            return etapas.map((etapa, index) => ({
                ...etapa,
                isCurrent: index === indiceAtual || (indiceAtual === -1 && indiceFallback === index)
            }))
        },
        montarEtapaColaborador(key, label, itensEtapa, fallbackResumo) {
            if (!itensEtapa.length) {
                return null
            }

            const pendentes = itensEtapa.filter((item) => item.status === 'Pendente').length
            const concluidas = itensEtapa.length - pendentes
            const faltantes = itensEtapa
                .filter((item) => item.status === 'Pendente')
                .map((item) => item.avaliador?.nome)
                .filter(Boolean)
            const primeiroPendente = itensEtapa.find((item) => item.status === 'Pendente')

            return {
                key,
                label,
                resumo: pendentes ? `${concluidas}/${itensEtapa.length} concluída(s)` : 'Todos os avaliadores desta etapa já concluíram',
                badge: pendentes ? `${pendentes} pendente(s)` : 'Concluída',
                state: pendentes ? 'pending' : 'done',
                avaliacoes: itensEtapa,
                proximoTexto: primeiroPendente ? this.proximoResponsavelEtapa(primeiroPendente) : '',
                faltantesTexto: faltantes.join(', '),
                podeExpandir: itensEtapa.length > 2
            }
        },
        percentualConclusaoCard(card) {
            const etapas = this.fluxoEtapasVisiveisColaborador(card)
            if (!etapas.length) {
                return 0
            }

            const concluidas = etapas.filter((etapa) => etapa.state === 'done').length
            return Math.round((concluidas / etapas.length) * 100)
        },
        chaveEtapaAtiva(card) {
            return `${card.avaliacao.id}-${card.funcionario.id}`
        },
        isPropriaAvaliacaoColaborador(card) {
            return Boolean(this.currentUserId && card?.funcionario?.id && Number(this.currentUserId) === Number(card.funcionario.id))
        },
        fluxoEtapasVisiveisColaborador(card) {
            const etapas = this.fluxoEtapasColaborador(card)

            if (this.tem_privilegio_gestao_rh || this.isPropriaAvaliacaoColaborador(card)) {
                return etapas
            }

            const acessiveis = this.feedbacksAcessiveisDoCard(card)
            const maiorIndiceAcessivel = etapas.reduce((maior, etapa, indice) => {
                const temFeedbackAcessivel = (etapa.avaliacoes || []).some((item) => acessiveis.some((acessivel) => acessivel.id === item.id))
                return temFeedbackAcessivel ? indice : maior
            }, -1)

            if (maiorIndiceAcessivel === -1) {
                return etapas.slice(0, 1)
            }

            return etapas.slice(0, maiorIndiceAcessivel + 1)
        },
        feedbacksDoUsuarioNoCard(card) {
            if (this.tem_privilegio_gestao_rh || this.isPropriaAvaliacaoColaborador(card)) {
                return []
            }

            return (card?.itens || []).filter((item) => Boolean(this.feedbacksPermitidosMap[item.id]))
        },
        etapaAtivaColaborador(card) {
            const etapas = this.fluxoEtapasVisiveisColaborador(card)
            if (!etapas.length) {
                return null
            }

            const etapasAtivasPorCard = this.etapasAtivasPorCard || {}
            const chaveSalva = etapasAtivasPorCard[this.chaveEtapaAtiva(card)]
            return etapas.find((etapa) => etapa.key === chaveSalva) || etapas.find((etapa) => etapa.isCurrent) || etapas[0]
        },
        setEtapaAtiva(card, etapa) {
            this.etapasAtivasPorCard = {
                ...this.etapasAtivasPorCard,
                [this.chaveEtapaAtiva(card)]: etapa.key
            }
        },
        etapasConcluidasResumo(card) {
            const etapas = this.fluxoEtapasVisiveisColaborador(card)
            const concluidas = etapas.filter((etapa) => etapa.state === 'done').length
            return `${concluidas} de ${etapas.length} etapas concluídas`
        },
        proximoResponsavelCard(card) {
            const etapas = this.fluxoEtapasVisiveisColaborador(card)
            const etapaAtual = etapas.find((etapa) => etapa.isCurrent)

            if (!etapaAtual) {
                return 'Fluxo concluído'
            }

            return etapaAtual.proximoTexto || 'Fluxo concluído'
        },
        feedbacksAcessiveisDoCard(card) {
            return (card?.itens || []).filter((item) => this.usuarioPodeAcessarFeedback(item))
        },
        feedbackVisualizavelDoUsuario(card) {
            const feedbacks = this.feedbacksAcessiveisDoCard(card).filter((item) => item.status !== 'Pendente')

            if (!feedbacks.length) {
                return null
            }

            const doUsuarioLogado = feedbacks.find((item) => Number(item.avaliador_id) === Number(this.currentUserId))
            return doUsuarioLogado || feedbacks[0]
        },
        usuarioTemAcaoPendenteNoCard(card) {
            return this.feedbacksAcessiveisDoCard(card).some(
                (item) => this.podeAvaliarItem(item) || this.podeFazerAvaliacaoFinal(item) || this.podeEditarPdi(item)
            )
        },
        resumoConclusaoPessoal(card) {
            if (this.tem_privilegio_gestao_rh || this.isPropriaAvaliacaoColaborador(card)) {
                return ''
            }

            const feedbacksDoUsuario = this.feedbacksDoUsuarioNoCard(card)
            const concluiuAlgo = feedbacksDoUsuario.some((item) => item.status !== 'Pendente')

            if (feedbacksDoUsuario.length && concluiuAlgo && !this.usuarioTemAcaoPendenteNoCard(card)) {
                return 'Sua avaliação foi realizada'
            }

            return ''
        },
        mostrarDetalhesFluxoCard(card) {
            return !this.resumoConclusaoPessoal(card)
        },
        statusResumoCard(card) {
            return this.resumoConclusaoPessoal(card) || this.statusCardLabel(card.principalItem)
        },
        mostrarProximoFluxoCard(card) {
            return card?.principalItem?.status !== 'Finalizada' && !this.resumoConclusaoPessoal(card)
        },
        resumoProximoFluxoCard(card) {
            return this.resumoConclusaoPessoal(card) || this.proximoResponsavelCard(card)
        },
        chaveEtapaExpandida(card, etapa) {
            return `${card.avaliacao.id}-${card.funcionario.id}-${etapa.key}`
        },
        etapaExpandida(card, etapa) {
            return Boolean(this.etapasExpandidas[this.chaveEtapaExpandida(card, etapa)])
        },
        toggleEtapaExpandida(card, etapa) {
            const chave = this.chaveEtapaExpandida(card, etapa)
            this.etapasExpandidas = {
                ...this.etapasExpandidas,
                [chave]: !this.etapaExpandida(card, etapa)
            }
        },
        itensVisiveisEtapa(card, etapa) {
            if (!etapa.podeExpandir || this.etapaExpandida(card, etapa)) {
                return etapa.avaliacoes
            }

            return etapa.avaliacoes.slice(0, 2)
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
            const comboLegenda = this.$refs.comboLegenda
            const comboAvaliador = this.$refs.comboAvaliador
            const comboColaborador = this.$refs.comboColaborador
            const comboComo = this.$refs.comboComo
            if (comboAno && typeof comboAno.containsTarget === 'function' && comboAno.containsTarget(event.target)) {
                return
            }
            if (comboAvaliacao && typeof comboAvaliacao.containsTarget === 'function' && comboAvaliacao.containsTarget(event.target)) {
                return
            }
            if (comboLegenda && typeof comboLegenda.containsTarget === 'function' && comboLegenda.containsTarget(event.target)) {
                return
            }
            if (comboAvaliador && typeof comboAvaliador.containsTarget === 'function' && comboAvaliador.containsTarget(event.target)) {
                return
            }
            if (comboColaborador && typeof comboColaborador.containsTarget === 'function' && comboColaborador.containsTarget(event.target)) {
                return
            }
            if (comboComo && typeof comboComo.containsTarget === 'function' && comboComo.containsTarget(event.target)) {
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
            if (manter !== 'legenda' && this.$refs.comboLegenda && typeof this.$refs.comboLegenda.close === 'function') {
                this.$refs.comboLegenda.close()
            }
            if (manter !== 'avaliador' && this.$refs.comboAvaliador && typeof this.$refs.comboAvaliador.close === 'function') {
                this.$refs.comboAvaliador.close()
            }
            if (manter !== 'colaborador' && this.$refs.comboColaborador && typeof this.$refs.comboColaborador.close === 'function') {
                this.$refs.comboColaborador.close()
            }
            if (manter !== 'como' && this.$refs.comboComo && typeof this.$refs.comboComo.close === 'function') {
                this.$refs.comboComo.close()
            }
        },
        onSelectAnoCombobox() {
            this.controle.dados.campoLegenda = ''
            this.onAnoFiltroChange()
        },
        onSelectAvaliacaoCombobox() {
            this.controle.dados.campoLegenda = ''
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        onSelectLegendaCombobox() {
            this.fecharDropdown()
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        onSelectAvaliadorCombobox() {
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        onSelectColaboradorCombobox() {
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        onSelectComoCombobox() {
            this.$nextTick(() => {
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.buscar()
                }
            })
        },
        limparFiltros() {
            const anoAtual = new Date().getFullYear()
            const anoPadrao = this.listaAnosOrdenados.includes(anoAtual) ? anoAtual : this.listaAnosOrdenados[0] || anoAtual

            this.controle.dados.campoBusca = ''
            this.controle.dados.campoLegenda = ''
            this.controle.dados.campoAvaliador = ''
            this.controle.dados.campoColaborador = ''
            this.controle.dados.campoComo = ''
            this.controle.dados.ano_avaliacao = anoPadrao

            const avaliacoesAno = this.lista_avaliacoes.filter((a) => Number(a.ano_avaliacao) === Number(anoPadrao))
            this.controle.dados.campoAvaliacao = avaliacoesAno[0]?.id || ''

            this.$nextTick(() => {
                this.fecharOutrosFiltros(null)
                if (this.$refs.componente && this.$refs.componente.buscar) {
                    this.$refs.componente.atual = 1
                    this.$refs.componente.buscar()
                }
            })
        },
        mapearPessoasParaCombobox(lista, campo) {
            const pessoas = []
            const vistos = new Set()

            ;(lista || []).forEach((item) => {
                const pessoa = item && item[campo] ? item[campo] : item
                if (!pessoa || !pessoa.id || vistos.has(pessoa.id)) {
                    return
                }
                vistos.add(pessoa.id)
                pessoas.push({
                    value: pessoa.id,
                    label: pessoa.nome,
                    raw: pessoa
                })
            })

            if (this.currentUserId && this.currentUserName && !vistos.has(this.currentUserId)) {
                pessoas.push({
                    value: this.currentUserId,
                    label: this.currentUserName,
                    raw: { id: this.currentUserId, nome: this.currentUserName }
                })
            }

            return [{ value: '', label: campo === 'avaliador' ? 'Todos os avaliadores' : 'Todos os colaboradores' }, ...pessoas]
        },
        ordenarOpcoesPessoaComUsuarioLogadoPrimeiro(opcoes) {
            if (!this.currentUserId) {
                return opcoes
            }

            const [todos, ...demais] = opcoes
            demais.sort((a, b) => {
                if (a.value === this.currentUserId) return -1
                if (b.value === this.currentUserId) return 1
                return String(a.label || '').localeCompare(String(b.label || ''))
            })

            return todos ? [todos, ...demais] : demais
        },
        correspondeLegendaSelecionada(item) {
            const legenda = this.controle.dados.campoLegenda
            if (!legenda) {
                return true
            }

            switch (legenda) {
                case 'autoavaliacao_pendente':
                    return !!item.pendente_autoavaliacao
                case 'autoavaliacao_realizada':
                    return item.origem_feedback === 'Funcionario' && !item.principal && item.status !== 'Pendente'
                case 'avaliacao_par_pendente':
                    return !!item.pendente_avaliacao_par
                case 'avaliacao_par_realizada':
                    return item.origem_feedback === 'Avaliador' && !item.principal && item.status !== 'Pendente'
                case 'avaliacao_gestor_pendente':
                    return !!item.pendente_avaliacao_gestor
                case 'avaliacao_gestor_realizada':
                    return item.principal && !item.pendente_avaliacao_gestor
                case 'fluxo_concluido':
                    return (
                        !item.pendente_autoavaliacao &&
                        !item.pendente_autoavaliacao_colaborador &&
                        !item.pendente_avaliacao_par &&
                        !item.pendente_avaliacao_gestor
                    )
                default:
                    return true
            }
        },
        statusCardLabel(item) {
            if (item.status === 'Finalizada') {
                return 'Completa'
            }
            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return 'Falta avaliação final'
            }
            if (!item.avaliacao?.auto_avaliacao && item.status === 'Avaliada') {
                return 'Avaliada pelo gestor'
            }
            if (item.pendente_autoavaliacao) {
                return 'Pendente autoavaliação'
            }
            if (item.pendente_autoavaliacao_colaborador) {
                return 'Pendente autoavaliação colaborador'
            }
            if (item.pendente_avaliacao_par) {
                return 'Pendente avaliação do par'
            }
            if (item.pendente_avaliacao_gestor) {
                return item.avaliacao?.auto_avaliacao ? 'Pendente avaliação gestor' : 'Pendente avaliação do gestor'
            }
            return item.status || 'Não informado'
        },
        fluxoCardAtual(item) {
            if (item.status === 'Finalizada') {
                return 'Avaliação final concluída'
            }

            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return 'Avaliações do fluxo concluídas'
            }

            if (item.principal && item.status === 'Avaliada') {
                return 'Avaliação do gestor realizada'
            }

            if (item.origem_feedback === 'Avaliador' && item.status === 'Avaliada') {
                return `${this.rotuloComoAvaliacao(item)} realizada`
            }

            if (item.origem_feedback === 'Funcionario' && !item.principal && item.status === 'Avaliada') {
                return 'Autoavaliação realizada'
            }

            return 'Aguardando início desta etapa'
        },
        fluxoEtapasCard(item) {
            const etapas = []

            if (item.avaliacao?.auto_avaliacao) {
                etapas.push({
                    key: 'auto',
                    label: 'Autoavaliação',
                    state: item.pendente_autoavaliacao ? 'pending' : 'done'
                })

                etapas.push({
                    key: 'par',
                    label: 'Par',
                    state: item.pendente_avaliacao_par ? 'pending' : this.etapaParConcluida(item) ? 'done' : 'idle'
                })
            }

            etapas.push({
                key: 'gestor',
                label: 'Gestor',
                state: item.pendente_avaliacao_gestor ? 'pending' : this.etapaGestorConcluida(item) ? 'done' : 'idle'
            })

            etapas.push({
                key: 'final',
                label: 'Avaliação final',
                state: item.status === 'Finalizada' ? 'done' : item.status === 'Avaliada' && item.fazer_avaliacao_final ? 'pending' : 'idle'
            })

            return etapas
        },
        etapaParConcluida(item) {
            return item.avaliacao?.auto_avaliacao && item.origem_feedback === 'Avaliador' && !item.principal && item.status !== 'Pendente'
        },
        etapaGestorConcluida(item) {
            if (item.status === 'Finalizada') {
                return true
            }

            return item.principal && item.status === 'Avaliada'
        },
        fluxoCardPendente(item) {
            if (item.status === 'Finalizada') {
                return 'Nenhuma pendência no fluxo'
            }

            if (item.pendente_autoavaliacao) {
                return 'Falta a autoavaliação'
            }

            if (item.pendente_autoavaliacao_colaborador) {
                return 'Falta a autoavaliação do colaborador'
            }

            if (item.pendente_avaliacao_par) {
                return 'Falta a avaliação do par'
            }

            if (item.pendente_avaliacao_gestor) {
                return item.avaliacao?.auto_avaliacao ? 'Falta a avaliação do gestor' : 'Falta a avaliação do gestor principal'
            }

            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return 'Falta a avaliação final'
            }

            return 'Sem pendência imediata'
        },
        fluxoObrigatorioPendente(item) {
            if (item.status === 'Finalizada') {
                return 'Fluxo concluído'
            }

            if (item.pendente_autoavaliacao) {
                return 'Falta a autoavaliação'
            }

            if (item.pendente_autoavaliacao_colaborador) {
                return 'Falta a autoavaliação do colaborador'
            }

            if (item.pendente_avaliacao_par) {
                return 'Falta a avaliação do par'
            }

            if (item.pendente_avaliacao_gestor) {
                return item.avaliacao?.auto_avaliacao ? 'Falta a avaliação do gestor' : 'Falta a avaliação do gestor principal'
            }

            return 'Fluxo concluído'
        },
        statusFluxoCard(item) {
            return this.fluxoObrigatorioPendente(item)
        },
        statusPdiExtra(item) {
            if (!item) {
                return ''
            }

            if (item.status === 'Finalizada') {
                return 'Plano de acao concluido'
            }

            if (item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return 'Plano de acao pendente'
            }

            return ''
        },
        rotuloComoEtapa(item) {
            if (item.origem_feedback === 'Funcionario' && !item.principal) {
                return 'Autoavaliação'
            }

            return this.rotuloComoAvaliacao(item)
        },
        statusLinhaFluxo(item) {
            if (item.status === 'Finalizada') {
                return 'Fluxo concluído'
            }
            if (item.principal && item.status === 'Avaliada' && item.fazer_avaliacao_final) {
                return 'Avaliou, falta plano de ação (PDI)'
            }
            if (item.status === 'Avaliada') {
                return 'Avaliação realizada'
            }
            return 'Pendente'
        },
        isPrimeiroPendenteDaEtapa(etapa, avaliacaoItem) {
            if (!etapa || !avaliacaoItem) {
                return false
            }

            const primeiroPendente = (etapa.avaliacoes || []).find((item) => item.status === 'Pendente')
            return Boolean(primeiroPendente && primeiroPendente.id === avaliacaoItem.id)
        },
        iniciaisPessoa(nome) {
            const partes = String(nome || '')
                .trim()
                .split(/\s+/)
                .filter(Boolean)

            if (!partes.length) {
                return '--'
            }

            if (partes.length === 1) {
                return partes[0].slice(0, 2).toUpperCase()
            }

            return `${partes[0][0] || ''}${partes[partes.length - 1][0] || ''}`.toUpperCase()
        },
        proximoResponsavelEtapa(item) {
            if (!item) {
                return ''
            }

            if (item.origem_feedback === 'Funcionario' && !item.principal) {
                return 'Autoavaliação'
            }

            return item.avaliador?.nome || this.rotuloComoEtapa(item)
        },
        rotuloEtapaCurto(label) {
            return String(label || '')
                .replace('(Avaliador Final)', '(Final)')
                .replace('Auto Avaliação', 'Autoavaliação')
                .trim()
        },
        iconeEstadoEtapa(etapa) {
            if (etapa.state === 'done') {
                return 'fa fa-check-circle'
            }

            if (etapa.isCurrent || etapa.state === 'pending') {
                return 'fa fa-clock'
            }

            return 'fa fa-minus-circle'
        },
        iconeEstadoLinha(etapa, item) {
            if (item?.status === 'Finalizada' || item?.status === 'Avaliada') {
                return 'fa fa-check-circle'
            }

            if (this.isPrimeiroPendenteDaEtapa(etapa, item) || etapa?.state === 'pending') {
                return 'fa fa-clock'
            }

            return 'fa fa-minus-circle'
        },
        usuarioPodeAcessarFeedback(item) {
            if (!item) {
                return false
            }

            return Boolean(this.feedbacksPermitidosMap[item.id]) || this.tem_privilegio_gestao_rh
        },
        parseDataAvaliacaoPrazo(data) {
            if (!data) {
                return null
            }

            if (/^\d{2}\/\d{2}\/\d{4}$/.test(data)) {
                const [dia, mes, ano] = data.split('/').map(Number)
                return new Date(ano, mes - 1, dia, 23, 59, 59)
            }

            const parsed = new Date(data)
            return Number.isNaN(parsed.getTime()) ? null : parsed
        },
        avaliacaoPermiteAcao(item) {
            if (!item?.avaliacao) {
                return false
            }

            if (item.avaliacao.status !== 'Aberta') {
                return false
            }

            const prazo = this.parseDataAvaliacaoPrazo(item.avaliacao.data_fim_prazo)
            if (!prazo) {
                return true
            }

            const hoje = new Date()
            hoje.setHours(0, 0, 0, 0)

            return prazo >= hoje
        },
        podeAvaliarItem(item) {
            if (!item || !this.usuarioPodeAcessarFeedback(item) || !this.avaliacaoPermiteAcao(item)) {
                return false
            }

            if (item.avaliacao?.auto_avaliacao) {
                return (
                    (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) ||
                    (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par) ||
                    (item.status === 'Pendente' && !item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id)
                )
            }

            return item.status === 'Pendente' && item.principal
        },
        podeVisualizarItem(item) {
            return this.usuarioPodeAcessarFeedback(item) && (item.status === 'Avaliada' || (item.status === 'Finalizada' && !item.fazer_avaliacao_final))
        },
        podeFazerAvaliacaoFinal(item) {
            return this.usuarioPodeAcessarFeedback(item) && item.status === 'Avaliada' && item.fazer_avaliacao_final && item.principal
        },
        podeEditarPdi(item) {
            return this.usuarioPodeAcessarFeedback(item) && item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal
        },
        podeVisualizarAvaliacaoFinal(item) {
            return (
                this.usuarioPodeAcessarFeedback(item) &&
                item.status === 'Finalizada' &&
                !item.fazer_avaliacao_final &&
                (item.principal || this.tem_privilegio_gestao_rh)
            )
        },
        podeImprimirAvaliacaoFinal(item) {
            return (
                this.usuarioPodeAcessarFeedback(item) &&
                item.status === 'Finalizada' &&
                !item.fazer_avaliacao_final &&
                (item.principal || this.tem_privilegio_gestao_rh)
            )
        },
        podeNotificarPendente(item) {
            if (!this.tem_privilegio_gestao_rh || !item || item.avaliacao?.status !== 'Aberta') {
                return false
            }

            if (item.avaliacao?.auto_avaliacao) {
                return (
                    (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) ||
                    (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par) ||
                    (item.status === 'Pendente' && !item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id)
                )
            }

            return item.status === 'Pendente' && item.principal
        },
        async notificarPendente(item) {
            if (!this.podeNotificarPendente(item)) {
                return
            }

            this.notificandoFeedbackIds = {
                ...this.notificandoFeedbackIds,
                [item.id]: true
            }

            try {
                const { data } = await axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${item.id}/notificar-pendente`)
                mostraSucesso('', data.msg || 'Notificação enviada com sucesso')
            } catch (error) {
                toastr.error(error?.response?.data?.msg || 'Não foi possível enviar a notificação', 'Erro!')
            } finally {
                this.notificandoFeedbackIds = {
                    ...this.notificandoFeedbackIds,
                    [item.id]: false
                }
            }
        },
        async notificarPendentes() {
            this.notificandoPendentes = true

            try {
                const { data } = await axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/notificar-pendentes`, this.controle.dados)
                mostraSucesso('', data.msg || 'Notificações enviadas com sucesso')
            } catch (error) {
                toastr.error(error?.response?.data?.msg || 'Não foi possível enviar as notificações', 'Erro!')
            } finally {
                this.notificandoPendentes = false
            }
        },
        valorFiltroComo(item) {
            if (item.origem_feedback === 'Funcionario' && !item.principal) {
                return 'autoavaliacao'
            }

            if (!item.tipo_avaliador || !item.tipo_avaliador.id) {
                return ''
            }

            return item.principal ? `tipo:${item.tipo_avaliador.id}:principal` : `tipo:${item.tipo_avaliador.id}`
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
                modo === 'editar-pdi' ? `Acompanhar PDI — ${avaliacaoFeedback.avaliacao.titulo}` : `Plano de ação (PDI) — ${avaliacaoFeedback.avaliacao.titulo}`
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
            this.lista_fluxo_completo = dados.itens_fluxo_completo || dados.itens || []
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos
            this.lista_status = dados.lista_status
            this.lista_avaliacoes_por_ano = dados.lista_avaliacoes_por_ano
            this.lista_avaliadores_filtro = dados.lista_avaliadores || []
            this.lista_colaboradores_filtro = dados.lista_colaboradores || []
            this.lista_como_filtro = dados.lista_como || []
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

.ma-status-inline {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    background: rgba(0, 55, 85, 0.08);
    color: #003755;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.ma-fluxo-inline {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    background: rgba(25, 135, 84, 0.12);
    color: #155724;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.ma-fluxo-inline--pending {
    background: rgba(255, 193, 7, 0.18);
    color: #856404;
}

.ma-progress-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    font-size: 0.78rem;
    font-weight: 700;
}

.ma-btn-card {
    min-height: 38px;
    padding: 0.48rem 0.9rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    line-height: 1.1;
    transition: 0.18s ease;
}

.ma-btn-card--primary {
    border: 1px solid rgba(0, 55, 85, 0.22);
    background: rgba(0, 55, 85, 0.06);
    color: #003755;
}

.ma-btn-card--secondary {
    border: 1px solid rgba(108, 117, 125, 0.22);
    background: rgba(108, 117, 125, 0.06);
    color: #42515d;
}

.ma-btn-card--ghost {
    border: 1px solid rgba(44, 62, 80, 0.2);
    background: #fff;
    color: #2f4556;
}

.ma-btn-card:hover,
.ma-btn-card:focus {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(23, 52, 73, 0.08);
}

.ma-card-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 0.65rem;
}

.ma-card-summary__item {
    padding: 0.7rem 0.8rem;
    border-radius: 12px;
    background: rgba(0, 55, 85, 0.04);
    border: 1px solid rgba(0, 55, 85, 0.08);
}

.ma-card-summary__label {
    display: block;
    color: #6b7a87;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.ma-card-summary__value {
    display: block;
    margin-top: 0.25rem;
    color: #1f3442;
    font-size: 0.9rem;
    font-weight: 800;
    line-height: 1.3;
}

.ma-fluxo-card-inline {
    background: linear-gradient(180deg, rgba(0, 55, 85, 0.04), rgba(0, 55, 85, 0.01));
    border: 1px solid rgba(0, 55, 85, 0.1);
    border-radius: 12px;
    padding: 0.85rem;
}

.ma-fluxo-card-inline__head {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
}

.ma-fluxo-track {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.65rem;
}

.ma-fluxo-step {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 0.75rem;
    border-radius: 10px;
    border: 1px solid #d9e2e8;
    background: #f8fafb;
    min-height: 48px;
}

.ma-fluxo-step__dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #adb5bd;
    flex: 0 0 10px;
}

.ma-fluxo-step__label {
    font-size: 0.8rem;
    font-weight: 700;
    color: #324b5c;
    line-height: 1.2;
}

.ma-fluxo-step--done {
    background: rgba(25, 135, 84, 0.1);
    border-color: rgba(25, 135, 84, 0.22);
}

.ma-fluxo-step--done .ma-fluxo-step__dot {
    background: #198754;
}

.ma-fluxo-step--pending {
    background: rgba(255, 193, 7, 0.16);
    border-color: rgba(255, 193, 7, 0.35);
}

.ma-fluxo-step--pending .ma-fluxo-step__dot {
    background: #ffc107;
}

.ma-fluxo-step--idle {
    background: #f8fafb;
    border-color: #d9e2e8;
}

.ma-colaborador-card {
    width: 100%;
    border: 1px solid rgba(0, 55, 85, 0.14);
    border-left: 4px solid rgba(0, 55, 85, 0.38);
    border-radius: 14px;
    margin: 0;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 253, 0.98));
    box-shadow: 0 10px 24px rgba(15, 33, 49, 0.05) !important;
}

.ma-colaborador-card + .ma-colaborador-card {
    margin-top: 1.25rem;
}

.ma-card--colaboradores {
    width: 100%;
}

.ma-card--colaboradores > .card-body {
    padding-left: 1.25rem !important;
    padding-right: 1.25rem !important;
}

.ma-colaborador-head {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 0;
}

.ma-colaborador-monograma {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(0, 55, 85, 0.1), rgba(0, 55, 85, 0.04));
    color: #003755;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    flex: 0 0 32px;
}

.ma-colaborador-nome {
    display: block;
    min-width: 0;
    color: #17384d;
    font-size: 1.12rem;
    font-weight: 800;
    letter-spacing: -0.015em;
    line-height: 1.2;
}

.ma-colaborador-fluxo {
    display: grid;
    gap: 0.75rem;
}

.ma-etapas-strip {
    display: flex;
    gap: 0.55rem;
    overflow-x: auto;
    padding-bottom: 0.15rem;
    scrollbar-width: thin;
}

.ma-etapa-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    flex: 0 0 auto;
    min-height: 40px;
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    border: 1px solid #dce5ec;
    background: #f8fafb;
    color: #314a5b;
    transition: 0.15s ease;
}

.ma-etapa-chip__dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #aab6c2;
    flex: 0 0 8px;
}

.ma-etapa-chip__label {
    font-size: 0.78rem;
    font-weight: 800;
    white-space: nowrap;
}

.ma-etapa-chip__badge {
    font-size: 0.7rem;
    font-weight: 700;
    color: #6a7b88;
    white-space: nowrap;
}

.ma-etapa-chip--done {
    background: rgba(25, 135, 84, 0.08);
    border-color: rgba(25, 135, 84, 0.2);
}

.ma-etapa-chip--done .ma-etapa-chip__dot {
    background: #198754;
}

.ma-etapa-chip--pending {
    background: rgba(255, 193, 7, 0.12);
    border-color: rgba(255, 193, 7, 0.3);
}

.ma-etapa-chip--pending .ma-etapa-chip__dot {
    background: #ffc107;
}

.ma-etapa-chip--active {
    border-color: rgba(13, 110, 253, 0.38);
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.08);
    background: linear-gradient(180deg, rgba(13, 110, 253, 0.06), #f8fbff);
}

.ma-etapa-chip--current .ma-etapa-chip__dot {
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
}

.ma-etapa-chip--done.ma-etapa-chip--current .ma-etapa-chip__dot {
    background: #198754;
    box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.14);
}

.ma-etapa-card {
    border: 1px solid #e6edf2;
    border-radius: 12px;
    background: #fbfdff;
    padding: 0.8rem;
}

.ma-etapa-card--current {
    border-color: rgba(13, 110, 253, 0.45);
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.08);
    background: linear-gradient(180deg, rgba(13, 110, 253, 0.06), #fbfdff);
}

.ma-etapa-card__head {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.ma-etapa-card__meta {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.ma-etapa-card__title {
    display: block;
    font-size: 0.92rem;
    font-weight: 800;
    color: #003755;
}

.ma-etapa-card__subtitle {
    display: block;
    margin-top: 0.2rem;
    color: #6c7b88;
    font-size: 0.78rem;
}

.ma-etapa-card__missing {
    display: block;
    margin-top: 0.28rem;
    color: #b26a00;
    font-size: 0.75rem;
    font-weight: 700;
}

.ma-etapa-toggle {
    padding: 0;
    min-height: auto;
    font-size: 0.74rem;
    font-weight: 700;
}

.ma-btn-acao-etapa {
    min-height: 34px;
    padding: 0.38rem 0.75rem;
    border-radius: 9px;
    font-size: 0.76rem;
    font-weight: 700;
    line-height: 1.1;
}

.ma-btn-acao-etapa--primary {
    border: 1px solid transparent;
    background: #0d6efd;
    color: #fff;
}

.ma-btn-acao-etapa--secondary {
    border: 1px solid rgba(108, 117, 125, 0.24);
    background: rgba(108, 117, 125, 0.05);
    color: #495966;
}

.ma-btn-acao-etapa--accent {
    border: 1px solid rgba(0, 55, 85, 0.22);
    background: rgba(0, 55, 85, 0.06);
    color: #003755;
}

.ma-etapa-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.25rem 0.6rem;
    font-size: 0.74rem;
    font-weight: 800;
}

.ma-etapa-badge--done {
    background: rgba(25, 135, 84, 0.12);
    color: #155724;
}

.ma-etapa-badge--pending {
    background: rgba(255, 193, 7, 0.18);
    color: #856404;
}

.ma-etapa-badge--idle {
    background: rgba(108, 117, 125, 0.12);
    color: #5f6b76;
}

.ma-etapa-badge--current {
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.12);
}

.ma-etapa-lista {
    display: grid;
    gap: 0.6rem;
}

.ma-etapa-linha {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: center;
    border: 1px solid #edf2f6;
    border-radius: 10px;
    background: #fff;
    padding: 0.75rem;
}

.ma-etapa-linha--next {
    border-color: rgba(13, 110, 253, 0.35);
    background: linear-gradient(180deg, rgba(13, 110, 253, 0.05), #fff);
    box-shadow: inset 3px 0 0 #0d6efd;
}

.ma-etapa-linha__info {
    min-width: 0;
    display: grid;
}

.ma-etapa-linha__nome {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-weight: 700;
    color: #243744;
}

.ma-etapa-linha__avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: rgba(0, 55, 85, 0.1);
    color: #003755;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    flex: 0 0 28px;
}

.ma-etapa-linha__icone {
    width: 14px;
    text-align: center;
}

.ma-etapa-card .fa-check-circle {
    color: #198754;
}

.ma-etapa-card .fa-clock {
    color: #d39e00;
}

.ma-etapa-card .fa-minus-circle {
    color: #8a98a5;
}

.ma-etapa-card--current .ma-etapa-linha__icone:not(.fa-check-circle) {
    color: #0d6efd;
}

.ma-etapa-linha--next .ma-etapa-linha__avatar {
    background: rgba(13, 110, 253, 0.12);
    color: #0d6efd;
}

.ma-etapa-linha__como {
    font-size: 0.76rem;
    color: #6d7e8b;
}

.ma-etapa-linha__acoes {
    display: flex;
    gap: 0.35rem;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.ma-etapa-linha__status {
    font-size: 0.76rem;
    font-weight: 700;
    color: #4a5c69;
    margin-right: 0.3rem;
}

.ma-etapa-vazia {
    border-radius: 10px;
    background: #f8fafb;
    color: #7a8894;
    padding: 0.8rem;
    font-size: 0.82rem;
}

.ma-etapa-collapse-hint {
    color: #6d7e8b;
    font-size: 0.75rem;
    padding-top: 0.1rem;
}

@media (max-width: 767px) {
    .ma-etapas-strip {
        margin-right: -0.25rem;
        padding-right: 0.25rem;
    }

    .ma-etapa-linha {
        align-items: flex-start;
        flex-direction: column;
    }

    .ma-etapa-linha__acoes {
        width: 100%;
        justify-content: flex-start;
    }
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
    row-gap: 0.75rem;
}
.ma-filtro-avaliacao-col,
.ma-filtro-ano-col {
    overflow: visible;
    position: relative;
    z-index: 50;
}
.ma-filtro-avaliacao-wrap,
.ma-filtro-ano-wrap {
    position: relative;
    z-index: 51;
}
.ma-filtros-form > [class*='col-'] .form-group {
    width: 100%;
}
.ma-filtros-form .combobox-auto-complete,
.ma-filtros-form .ma-filtro-combo,
.ma-filtros-form .ma-filtro-combo .form-control {
    width: 100%;
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
    min-width: 180px;
}

.ma-btn-limpar {
    border-radius: 8px;
    font-weight: 600;
    min-width: 180px;
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
.ma-card > .card-body > .ma-colaborador-card:first-child {
    margin-top: 0.25rem;
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
.ma-modal-comments-panel {
    border: 1px solid rgba(0, 55, 85, 0.12);
    border-left: 4px solid #0d6efd;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(248, 251, 255, 0.95), rgba(255, 255, 255, 0.98));
    padding: 1rem 1.15rem;
}
.ma-modal-comment-item + .ma-modal-comment-item {
    margin-top: 0.95rem;
    padding-top: 0.95rem;
    border-top: 1px dashed rgba(0, 55, 85, 0.12);
}
.ma-modal-comment-item__head {
    margin-bottom: 0.55rem;
}
.ma-modal-comment-item__title {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
    font-size: 0.94rem;
    font-weight: 800;
    color: #21455d;
    text-transform: uppercase;
}
.ma-modal-comment-item__body {
    border: 1px solid rgba(0, 55, 85, 0.08);
    border-radius: 12px;
    background: #fff;
    padding: 0.9rem 0.95rem;
    color: #3f4f5d;
    font-size: 0.95rem;
    line-height: 1.6;
    white-space: pre-line;
}
.ma-resultado-competencia-table th,
.ma-resultado-competencia-table td {
    vertical-align: middle;
}
.ma-resultado-competencia-table thead th {
    border-top: 0;
    border-bottom: 2px solid rgba(0, 55, 85, 0.08);
    padding-top: 0.8rem;
    padding-bottom: 0.8rem;
}
.ma-resultado-competencia-table tbody td {
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-top: 1px solid rgba(0, 55, 85, 0.06);
}
.ma-resultado-competencia-table__head {
    font-size: 0.82rem;
    font-weight: 800;
    color: #4e5a66;
}
.ma-resultado-competencia-table__head--criterio {
    font-size: 0.86rem;
    color: #4a5560;
}
.ma-resultado-competencia-table__head--media {
    color: #003755;
}
.ma-resultado-competencia-table__criterio {
    color: #334754;
    font-weight: 600;
    font-size: 0.96rem;
    line-height: 1.55;
    letter-spacing: -0.01em;
    padding-right: 1rem;
}
.ma-resultado-competencia-table tbody tr:hover .ma-resultado-competencia-table__criterio {
    color: #17384d;
}
.ma-resultado-nota {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.18rem;
    min-height: 56px;
    border-radius: 10px;
    border: 2px solid #d6dde3;
    font-weight: 700;
    font-size: 0.95rem;
    background: #f5f7f9;
    color: #4d5b67;
    text-align: center;
    padding: 0.35rem 0.45rem;
}
.ma-resultado-nota__numero {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1;
}
.ma-resultado-nota__texto {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    line-height: 1.1;
}
.ma-resultado-nota--media {
    min-width: 84px;
    box-shadow: inset 0 0 0 1px rgba(0, 55, 85, 0.08);
}
.ma-resultado-nota--5 {
    background: #effaf4;
    border-color: #8fd1b0;
    color: #0f6a46;
}
.ma-resultado-nota--4 {
    background: #eef8f4;
    border-color: #a7d8bf;
    color: #15714d;
}
.ma-resultado-nota--3 {
    background: #fbf8ec;
    border-color: #dcc36f;
    color: #7a6615;
}
.ma-resultado-nota--2 {
    background: #fcf2ec;
    border-color: #e7b28a;
    color: #a4541d;
}
.ma-resultado-nota--1 {
    background: #fdf0f0;
    border-color: #e29d9d;
    color: #9d2a2a;
}
.ma-resultado-nota--neutro {
    background: #f5f7f9;
    border-color: #d6dde3;
    color: #4d5b67;
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
