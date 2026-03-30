<template>
    <div :id="hash">
        <modal id="janelaAvaliacaoFinal" :titulo="titulo_janela_final" :size="90" ref="modal_janelaAvaliacaoFinal">
            <template #conteudo>
                <preload v-show="preloadAvalFinal"></preload>
                <div v-if="!preloadAvalFinal">
                    <fieldset>
                        <legend>DADOS</legend>
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

                    <!-- CORREÇÃO APLICADA: Adicionada verificação de segurança -->
                    <template  v-if="formAvaliarFinal.result_topico_pai_agrupado && formAvaliarFinal.result_topico_pai_agrupado.length > 0">
                        <table
                            class="table"
                            v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado"
                            :key="index"
                        >
                            <thead>
                                <tr>
                                    <!-- CORREÇÃO APLICADA: Mudança de item[index] para item[0] com guard -->
                                    <th>{{ (item[0] || {}).topico_pai || '' }}</th>
                                    <!-- CORREÇÃO APLICADA: Adicionado guard para avaliadores -->
                                    <th class="text-center" v-for="(avaliador, id) in (item[0] || {}).avaliadores || []" :key="avaliador.id">
                                        <span>
                                            {{ avaliador.origem === 'Funcionario' ? 'Autoavaliação' : 'Avaliador ' + (id + 1) }}
                                        </span>
                                    </th>
                                    <th class="text-center">MÉDIA</th>
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
                    <!-- CORREÇÃO APLICADA: Melhorada a verificação de segurança -->
                    <table
                        class="table"
                        v-if="
                            formAvaliarFinal.result_topico_pai_agrupado &&
                            formAvaliarFinal.result_topico_pai_agrupado.length > 0 &&
                            formAvaliarFinal.result_topico_pai_agrupado[0] &&
                            formAvaliarFinal.result_topico_pai_agrupado[0][0] &&
                            formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores
                        "
                    >
                        <thead>
                            <tr>
                                <th
                                    class="text-center"
                                    v-for="(avaliador, id) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                    :key="avaliador.id"
                                >
                                    <span>
                                        {{ avaliador.origem === 'Funcionario' ? 'Autoavaliação' : 'Avaliador ' + (id + 1) }}
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
                                    <label>Considerações</label>
                                    <textarea rows="5" class="form-control form-control-sm" readonly="readonly">{{ avaliador.comentario }}</textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- CORREÇÃO APLICADA: Adicionada verificação de segurança para charts -->
                    <div class="row justify-content-center mt-5" v-if="formAvaliarFinal.resultChart && formAvaliarFinal.resultChart.length > 0">
                        <div v-for="(chart, index) in formAvaliarFinal.resultChart" :key="chart.id || index" class="col-md-4">
                            <h4 class="text-center">{{ chart.name }}</h4>
                            <RadarChart :id="chart.name" :chart-data="chart.data" />
                            <!-- CORREÇÃO APLICADA: Método em vez de filtro -->
                            <h4 class="text-center">
                                Média:
                                {{ getMediaFormatada(chart.name) }}
                            </h4>
                        </div>
                        <div class="col-md-12 text-center">
                            <h4>Nota final: {{ formatarDecimal(formAvaliarFinal.nota_final) }}</h4>
                        </div>
                    </div>

                    <fieldset>
                        <legend>Oportunidades de Melhoria / Plano de Ação</legend>

                        <button class="btn btn-sm mr-1 btn-primary mb-2" @click="addPlanoAcao($event.target)" v-show="!visualizando">
                            <i class="fa fa-plus"></i> Adicionar Plano
                        </button>

                        <!-- CORREÇÃO APLICADA: Adicionado guard para planos_acoes -->
                        <fieldset v-for="(item, index) in formAvaliarFinal.planos_acoes || []" :key="index">
                            <legend>Plano - {{ index + 1 }}</legend>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Competência/Desempenho</label>
                                        <select
                                            class="form-control form-control-sm validacampo"
                                            v-model="item.topico_id"
                                            :disabled="visualizando"
                                            @blur.prevent="valida_campo_vazio($event.target, 1)"
                                            @change.prevent="valida_campo_vazio($event.target, 1)"
                                        >
                                            <option value="">Selecione</option>
                                            <!-- CORREÇÃO APLICADA: Adicionado guard para result_topico -->
                                            <option v-for="(topico, topico_id) in formAvaliarFinal.result_topico || {}" :key="topico_id" :value="topico_id">
                                                {{ topico.topico_pai }} -
                                                {{ topico.subtopico }}
                                            </option>
                                        </select>
                                        <!-- CORREÇÃO APLICADA: Método em vez de filtro -->
                                        <h5
                                            class="my-3 text-danger"
                                            v-if="item.topico_id && formAvaliarFinal.result_topico && formAvaliarFinal.result_topico[item.topico_id]"
                                        >
                                            Média:
                                            {{ getMediaTopico(item.topico_id) }}
                                        </h5>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="">Plano de Ação</label>
                                        <textarea
                                            rows="5"
                                            class="form-control form-control-sm validacampo"
                                            v-model="item.plano_de_acao"
                                            @blur.prevent="valida_campo_vazio($event.target, 1)"
                                            :disabled="visualizando"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <date-picker formsm label="Início" v-model="item.inicio" :disabled="visualizando"></date-picker>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <date-picker formsm label="Término" v-model="item.termino" :disabled="visualizando"></date-picker>
                                    </div>
                                </div>

                                <div class="col-lg-12" v-show="!visualizando">
                                    <button class="btn btn-sm mr-1 btn-danger" @click="removerPlanoAcao(index)"><i class="fa fa-trash"></i> Apagar</button>
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="editando && !visualizando && !preload && formAvaliarFinal.planos_acoes && formAvaliarFinal.planos_acoes.length > 0"
                    @click="salvarAvaliacaoFinal()"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload">
                    <fieldset>
                        <legend>DADOS</legend>
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
                            <div class="escala-titulo">Escala de avaliação</div>
                        </div>
                        <p class="escala-intro">
                            <strong>Para esta avaliação, considere as atribuições básicas abaixo, conforme as seguintes notas:</strong>
                        </p>
                        <div class="escala-item">
                            <span class="nota-badge nota-5">5</span>
                            <span class="escala-texto"
                                ><strong>Superou muito as expectativas:</strong> É percebido por outras áreas/pessoas como alguém com uma atuação
                                excepcional, modelo de referência</span
                            >
                        </div>
                        <div class="escala-item">
                            <span class="nota-badge nota-4">4</span>
                            <span class="escala-texto"><strong>Superou as expectativas:</strong> Atuação melhor que o esperado com alto padrão de qualidade</span>
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
                                <label>{{ visualizando ? 'Nota' : 'Informe sua nota' }}</label>
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
                            <h5 v-if="formAvaliar.principal">Nota do colaborador: {{ formAvaliar.respostasFunc[item.id][index].nota }}</h5>
                        </fieldset>
                    </fieldset>
                    <fieldset>
                        <legend>MINHAS CONSIDERAÇÕES</legend>
                        <textarea
                            :disabled="visualizando"
                            v-model="formAvaliar.comentario"
                            class="form-control"
                            @blur.prevent="valida_campo_vazio($event.target, 1)"
                            @change.prevent="valida_campo_vazio($event.target, 1)"
                            placeholder="Se desejar, faça considerações"
                            rows="4"
                        ></textarea>

                        <h5 class="mt-3" v-if="formAvaliar.principal">Considerações do colaborador: {{ formAvaliar.comentario_funcionario }}</h5>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload && !visualizando" @click="salvar()">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div id="conteudo">
            <fieldset>
                <legend>Filtro</legend>
                <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label>Avaliações</label>
                            <select
                                class="form-control form-control-sm"
                                v-model="controle.dados.campoAvaliacao"
                                :disabled="controle.carregando"
                                @change="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null"
                            >
                                <option :value="item.id" v-for="item in lista_avaliacoes" :key="item.id">{{ item.titulo }} - ({{ item.status }})</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select
                                class="form-control form-control-sm"
                                v-model="controle.dados.campoStatus"
                                :disabled="controle.carregando"
                                @change="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null"
                            >
                                <option value="">Todos os Status</option>
                                <option v-for="item in statusAvaliacaoSelecionada" :value="item.value" :key="item.value">
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-9">
                        <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                            <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                            Atualizar
                        </button>
                    </div>
                </form>
            </fieldset>

            <div class="row mt-2 pt-1 pb-1 border-bottom" v-if="!controle.carregando && selecionadaAvaliacao && selecionadaAvaliacao.auto_avaliacao">
                <div class="col-12">
                    <p class="bg-white p-3 rounded">
                        <i class="fas fa-circle text-danger"></i>
                        Pendente autoavaliação
                        <i class="fas fa-circle text-pink"></i>
                        Pendente autoavaliação colaborador
                        <i class="fas fa-circle text-warning ml-2"></i>
                        Pendente avaliação do par
                        <i class="fas fa-circle text-info ml-2"></i>
                        Pendente avaliação gestor
                        <i class="fas fa-circle text-success ml-2"></i>
                        Completa
                    </p>
                </div>
            </div>

            <div class="row mt-2 pt-1 pb-1 border-bottom" v-if="!controle.carregando && selecionadaAvaliacao && !selecionadaAvaliacao.auto_avaliacao">
                <div class="col-12">
                    <p class="bg-white p-3 rounded">
                        <i class="fas fa-circle ml-2" style="color: #f1f1f1"></i>
                        Pendente Avaliação do Gestor
                        <i class="fas fa-circle text-info ml-2"></i>
                        Avaliada pelo Gestor
                        <i class="fas fa-circle text-success ml-2"></i>
                        Completa
                    </p>
                </div>
            </div>

            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="table table-bordered" v-if="selecionadaAvaliacao && selecionadaAvaliacao.auto_avaliacao">
                    <thead class="bg-white">
                        <tr class="bg-white">
                            <td class="text-center">Ano Avaliação</td>
                            <td class="text-center">Título</td>
                            <td class="text-center">Tipo</td>
                            <td class="text-center">Avaliar até</td>
                            <td class="text-center">{{ selecionadaAvaliacao.tipo_pj ? 'Fornecedor' : 'Funcionário' }}</td>
                            <td class="text-center">Avaliador</td>
                            <td class="text-center">Avaliar Como</td>
                            <td class="text-center">Ação</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in lista"
                            :key="item.id"
                            :class="{
                                'bg-danger text-white': item.pendente_autoavaliacao,
                                'bg-pink': item.pendente_autoavaliacao_colaborador,
                                'bg-warning': item.pendente_avaliacao_par && item.status !== 'Finalizada',
                                'bg-info text-white': !item.pendente_avaliacao_par && item.status !== 'Finalizada',
                                'bg-success text-white': item.status === 'Finalizada'
                            }"
                        >
                            <td class="text-center">
                                {{ item.avaliacao.ano_avaliacao }}
                            </td>
                            <td class="text-center">
                                {{ item.avaliacao.titulo }}
                            </td>
                            <td class="text-center">{{ item.avaliacao.avaliacao_tipo.nome }}</td>
                            <td class="text-center">{{ item.avaliacao.data_fim_prazo }}</td>
                            <td class="text-center">
                                <i class="fa fa-user" v-if="item.avaliador_id === item.funcionario_id"></i>
                                {{ item.funcionario.nome }}
                            </td>
                            <td class="text-center">
                                {{ item.avaliador.nome }}
                            </td>
                            <td class="text-center">
                                <span v-show="item.origem_feedback === 'Funcionario' && !item.principal">Autoavaliação</span>
                                <span v-if="item.origem_feedback === 'Avaliador'">
                                    {{ item.tipo_avaliador ? item.tipo_avaliador.label : '---' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div
                                    class="dropdown"
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
                                    <a
                                        class="btn btn-secondary dropdown-toggle"
                                        href="#"
                                        role="button"
                                        :id="`dropdownMenuLink_${item.id}_auto`"
                                        aria-haspopup="true"
                                        :aria-expanded="isDropdownOpen(item.id, 'auto') ? 'true' : 'false'"
                                        @click.prevent.stop="toggleDropdown(item.id, 'auto')"
                                    >
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>

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
                                            @click="avaliarForm(item); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                            v-if="
                                                (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) ||
                                                (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par)
                                            "
                                        >
                                            Avaliar
                                        </a>

                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Avaliar"
                                            @click="avaliarForm(item); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                            v-if="item.status === 'Pendente' && !item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id"
                                        >
                                            Avaliar
                                        </a>

                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Visualizar Avaliação"
                                            @click="avaliarForm(item, true); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                            v-if="item.status === 'Avaliada'"
                                        >
                                            Visualizar Avaliação
                                        </a>

                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Visualizar Avaliação"
                                            @click="avaliarForm(item, true); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                            v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final"
                                        >
                                            Visualizar Avaliação
                                        </a>

                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Fazer Avaliação Final"
                                            @click="avaliarFinalForm(item); $refs.modal_janelaAvaliacaoFinal && $refs.modal_janelaAvaliacaoFinal.abrirModal()"
                                            v-if="item.status === 'Avaliada' && item.fazer_avaliacao_final"
                                        >
                                            Fazer Avaliação Final
                                        </a>

                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Visualizar Avaliação Final"
                                            @click="avaliarFinalForm(item, true); $refs.modal_janelaAvaliacaoFinal && $refs.modal_janelaAvaliacaoFinal.abrirModal()"
                                            v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                        >
                                            Visualizar Avaliação Final
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
                            </td>
                        </tr>
                    </tbody>
                </table>

                <template>
                    <table class="table table-bordered" v-if="selecionadaAvaliacao && !selecionadaAvaliacao.auto_avaliacao">
                        <thead class="bg-white">
                            <tr class="bg-white">
                                <td class="text-center">Ano Avaliação</td>
                                <td class="text-center">Título</td>
                                <td class="text-center">Tipo</td>
                                <td class="text-center">Avaliar até</td>
                                <td class="text-center">Funcionário</td>
                                <td class="text-center">Avaliador</td>
                                <td class="text-center">Status</td>
                                <td class="text-center">Ação</td>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="!item.avaliacao.auto_avaliacao && item.principal">
                                <tr v-for="item in lista" :key="item.id" class="bg-white">
                                    <td class="text-center">
                                        {{ item.avaliacao.ano_avaliacao }}
                                    </td>
                                    <td class="text-center">
                                        {{ item.avaliacao.titulo }}
                                    </td>
                                    <td class="text-center">{{ item.avaliacao.avaliacao_tipo.nome }}</td>
                                    <td class="text-center">{{ item.avaliacao.data_fim_prazo }}</td>
                                    <td class="text-center">
                                        <i class="fa fa-user" v-if="item.avaliador_id === item.funcionario_id"></i>
                                        {{ item.funcionario.nome }}
                                    </td>
                                    <td class="text-center">
                                        {{ item.avaliador.nome }}
                                    </td>

                                    <td
                                        class="text-center"
                                        :class="{
                                            'bg-cinza': item.status === 'Pendente',
                                            'bg-info text-white': item.status === 'Avaliada',
                                            'bg-success text-white': item.status === 'Finalizada'
                                        }"
                                    >
                                        <span v-if="item.status === 'Pendente'">Pendente Avaliação do Gestor</span>
                                        <span v-if="item.status === 'Avaliada'">Avaliada pelo Gestor</span>
                                        <span v-if="item.status === 'Finalizada'">Completa</span>
                                    </td>

                                    <td class="text-center">
                                        <div
                                            class="dropdown"
                                            :class="{ show: isDropdownOpen(item.id, 'gestor') }"
                                            v-show="
                                                (item.status === 'Pendente' && item.principal && !item.pendente_avaliacao_par) ||
                                                item.status === 'Avaliada' ||
                                                (item.status === 'Avaliada' && item.fazer_avaliacao_final) || // Avaliacao final
                                                (item.status === 'Finalizada' && !item.fazer_avaliacao_final) // successo
                                            "
                                        >
                                            <a
                                                class="btn btn-secondary dropdown-toggle"
                                                href="#"
                                                role="button"
                                                :id="`dropdownMenuLink_${item.id}_gestor`"
                                                aria-haspopup="true"
                                                :aria-expanded="isDropdownOpen(item.id, 'gestor') ? 'true' : 'false'"
                                                @click.prevent.stop="toggleDropdown(item.id, 'gestor')"
                                            >
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>

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
                                                    @click="avaliarForm(item); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                                    v-if="item.status === 'Pendente' && item.principal"
                                                >
                                                    Avaliar
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação"
                                                    @click="avaliarForm(item, true); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                                    v-if="item.status === 'Avaliada'"
                                                >
                                                    Visualizar Avaliação
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Fazer Avaliação Final"
                                                    @click="avaliarFinalForm(item); $refs.modal_janelaAvaliacaoFinal && $refs.modal_janelaAvaliacaoFinal.abrirModal()"
                                                    v-if="item.status === 'Avaliada' && item.fazer_avaliacao_final"
                                                >
                                                    Fazer Avaliação Final
                                                </a>

                                                <a
                                                    class="dropdown-item"
                                                    href="javascript://"
                                                    title="Visualizar Avaliação Final"
                                                    @click="avaliarFinalForm(item, true); $refs.modal_janelaAvaliacaoFinal && $refs.modal_janelaAvaliacaoFinal.abrirModal()"
                                                    v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                                >
                                                    Visualizar Avaliação Final
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
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
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
import validacoes from '../../../../mixins/Validacoes'

export default {
    components: {
        modal,
        controlePaginacao,
        DatePicker,
        RadarChart
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

        // CORREÇÃO APLICADA: Verificação de segurança antes de acessar array
        if (this.lista_avaliacoes && this.lista_avaliacoes.length > 0) {
            this.controle.dados.campoAvaliacao = this.lista_avaliacoes[0].id
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
            titulo_janela_final: 'Open Feedback - Avaliação Final',
            preload: false,
            preloadAvalFinal: false,
            editando: false,
            visualizando: false,
            tem_privilegio_gestao_rh: false,

            chartsRadares: [],

            formAvaliar: {
                respostas: [],
                respostasFunc: [],
                dados_do_funcionario: [],
                comentario: '',
                comentario_funcionario: ''
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
                planos_acoes_delete: []
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

            fluxoAvaliacao: null,

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
        }
    },
    methods: {
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
            this.dropdownAbertoKey = null
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
                const el = this.$el.querySelector(
                    `.nota-hidden-input[data-item="${itemId}"][data-index="${index}"]`
                )
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

        avaliarForm(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando
            this.editando = true
            this.titulo_janela = `Avaliação: ${avaliacaoFeedback.avaliacao.titulo}`
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
                    this.editando = true
                    setupCampo()
                    this.preload = false
                })
                .catch((error) => (this.preloadAjax = false))
        },

        // CORREÇÃO APLICADA: Melhorada a função avaliarFinalForm
        avaliarFinalForm(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando
            this.editando = true
            this.titulo_janela = `Avaliação Final: ${avaliacaoFeedback.avaliacao.titulo}`
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
                        result_topico: data.result_topico || {}
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
                    mostraSucesso('', 'Avaliação Final salva com sucesso')
                    this.preloadAvalFinal = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
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
            this.fluxoAvaliacao = dados.itens.length ? dados.itens[0].fluxo : null
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
</style>
