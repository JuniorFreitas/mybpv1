<template>
    <div :id="hash">
        <modal id="janelaAvaliacaoFinal" :titulo="titulo_janela_final" :size="90">
            <template slot="conteudo">
                <preload v-show="preloadAvalFinal"></preload>
                <div v-if="!preloadAvalFinal">

                    <fieldset>
                        <legend>DADOS</legend>
                        <div class="row mb-3" v-if="formAvaliarFinal.dados_do_funcionario.cnpj_lotacao">
                            <div class="col-12"><strong>CNPJ:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.cnpj_lotacao.razao_social }}
                                ({{ formAvaliarFinal.dados_do_funcionario.pertence_filial ? "Filial" : "Matriz" }})
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-4"><strong>Nome:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.nome }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliarFinal.tipo_pj"><strong>Matrícula:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.matricula }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliarFinal.tipo_pj"><strong>Admissão:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.data_admissao }}
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 col-lg-4" v-if="!formAvaliarFinal.tipo_pj"><strong>Cargo:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.cargo }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Centro de Custo:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.centro_custo }}
                            </div>

                            <div class="col-12 col-lg-4"><strong>Área:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.area }}
                            </div>
                        </div>
                    </fieldset>

                    <!-- CORREÇÃO APLICADA: Adicionada verificação de segurança -->
                    <table class="table"
                           v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado"
                           v-if="formAvaliarFinal.result_topico_pai_agrupado && formAvaliarFinal.result_topico_pai_agrupado.length > 0"
                           :key="index"
                    >
                        <thead>
                        <tr>
                            <!-- CORREÇÃO APLICADA: Mudança de item[index] para item[0] com guard -->
                            <th>{{ (item[0] || {}).topico_pai || "" }}</th>
                            <!-- CORREÇÃO APLICADA: Adicionado guard para avaliadores -->
                            <th class="text-center" v-for="(avaliador, id) in ((item[0] || {}).avaliadores || [])"
                                :key="avaliador.id"
                            >
                                <span>
                                    {{ avaliador.origem === "Funcionario" ? "Autoavaliação" : "Avaliador " + (id + 1) }}
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
                            <td style="width: 15%" v-for="(avaliador, avalIndex) in (sub.avaliadores || [])"
                                :key="avaliador.id || avalIndex"
                            >
                                <input type="number" class="form-control form-control-sm text-center"
                                       readonly="readonly" min="0" max="5"
                                       step="0.1" :value="formatarDecimal(avaliador.nota)"
                                >
                            </td>
                            <td style="width: 7%" class="text-center">
                                <input type="number" class="form-control form-control-sm text-center"
                                       readonly="readonly" min="0" max="5"
                                       step="0.1" :value="formatarDecimal(sub.media)"
                                >
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- CORREÇÃO APLICADA: Melhorada a verificação de segurança -->
                    <table class="table"
                           v-if="formAvaliarFinal.result_topico_pai_agrupado && formAvaliarFinal.result_topico_pai_agrupado.length > 0 && formAvaliarFinal.result_topico_pai_agrupado[0] && formAvaliarFinal.result_topico_pai_agrupado[0][0] && formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                    >
                        <thead>
                        <tr>
                            <th class="text-center"
                                v-for="(avaliador,id) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                :key="avaliador.id"
                            >
                                <span>
                                    {{ avaliador.origem === "Funcionario" ? "Autoavaliação" : "Avaliador " + (id + 1) }}
                                </span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td v-for="(avaliador, avalIndex) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                :key="avaliador.id || avalIndex"
                            >
                                <label>Considerações</label>
                                <textarea rows="5" class="form-control form-control-sm" readonly="readonly">{{ avaliador.comentario }}</textarea>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- CORREÇÃO APLICADA: Adicionada verificação de segurança para charts -->
                    <div class="row justify-content-center mt-5"
                         v-if="formAvaliarFinal.resultChart && formAvaliarFinal.resultChart.length > 0"
                    >
                        <div v-for="(chart, index) in formAvaliarFinal.resultChart" :key="index" class="col-md-4">
                            <h4 class="text-center">{{ chart.name }}</h4>
                            <RadarChart :id="chart.name" :chart-data="chart.data" />
                            <!-- CORREÇÃO APLICADA: Método em vez de filtro -->
                            <h4 class="text-center">Média:
                                {{ getMediaFormatada(chart.name) }}
                            </h4>
                        </div>
                        <div class="col-md-12 text-center">
                            <h4>Nota final: {{ formatarDecimal(formAvaliarFinal.nota_final) }}</h4>
                        </div>
                    </div>

                    <fieldset>
                        <legend>Oportunidades de Melhoria / Plano de Ação</legend>

                        <button class="btn btn-sm btn-primary mb-2" @click="addPlanoAcao($event.target)"
                                v-show="!visualizando"
                        >
                            <i class="fa fa-plus"></i> Adicionar Plano
                        </button>

                        <!-- CORREÇÃO APLICADA: Adicionado guard para planos_acoes -->
                        <fieldset v-for="(item, index) in (formAvaliarFinal.planos_acoes || [])" :key="index">
                            <legend>Plano - {{ index + 1 }}</legend>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Competência/Desempenho</label>
                                        <select class="form-control form-control-sm validacampo"
                                                v-model="item.topico_id"
                                                :disabled="visualizando"
                                                @blur.prevent="valida_campo_vazio($event.target, 1)"
                                                @change.prevent="valida_campo_vazio($event.target, 1)"
                                        >
                                            <option value="">Selecione</option>
                                            <!-- CORREÇÃO APLICADA: Adicionado guard para result_topico -->
                                            <option
                                                v-for="(topico, topico_id) in (formAvaliarFinal.result_topico || {})"
                                                :key="topico_id" :value="topico_id"
                                            >{{ topico.topico_pai }} -
                                                {{ topico.subtopico }}
                                            </option>
                                        </select>
                                        <!-- CORREÇÃO APLICADA: Método em vez de filtro -->
                                        <h5 class="my-3 text-danger"
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
                                        <textarea rows="5" class="form-control form-control-sm validacampo"
                                                  v-model="item.plano_de_acao"
                                                  @blur.prevent="valida_campo_vazio($event.target, 1)"
                                                  :disabled="visualizando"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <date-picker formsm label="Início" v-model="item.inicio"
                                                     :disabled="visualizando"
                                        ></date-picker>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <date-picker formsm label="Término" v-model="item.termino"
                                                     :disabled="visualizando"
                                        ></date-picker>
                                    </div>
                                </div>

                                <div class="col-lg-12" v-show="!visualizando">
                                    <button class="btn btn-sm btn-danger"
                                            @click="removerPlanoAcao(index)"
                                    >
                                        <i class="fa fa-trash"></i> Apagar
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="editando && !visualizando && !preload && formAvaliarFinal.planos_acoes && formAvaliarFinal.planos_acoes.length > 0"
                        @click="salvarAvaliacaoFinal()"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90">
            <template slot="conteudo">
                <preload v-show="preload"></preload>
                <div v-if="!preload">
                    <fieldset>
                        <legend>DADOS</legend>
                        <div class="row mb-3" v-if="formAvaliar.dados_do_funcionario.cnpj_lotacao">
                            <div class="col-12"><strong>CNPJ:</strong>
                                {{ formAvaliar.dados_do_funcionario.cnpj_lotacao.razao_social }}
                                ({{ formAvaliar.dados_do_funcionario.pertence_filial ? "Filial" : "Matriz" }})
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-4"><strong>Nome:</strong>
                                {{ formAvaliar.dados_do_funcionario.nome }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliar.tipo_pj"><strong>Matrícula:</strong>
                                {{ formAvaliar.dados_do_funcionario.matricula }}
                            </div>
                            <div class="col-12 col-lg-4" v-if="!formAvaliar.tipo_pj"><strong>Admissão:</strong>
                                {{ formAvaliar.dados_do_funcionario.data_admissao }}
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 col-lg-4" v-if="!formAvaliar.tipo_pj"><strong>Cargo:</strong>
                                {{ formAvaliar.dados_do_funcionario.cargo }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Centro de Custo:</strong>
                                {{ formAvaliar.dados_do_funcionario.centro_custo }}
                            </div>

                            <div class="col-12 col-lg-4"><strong>Área:</strong>
                                {{ formAvaliar.dados_do_funcionario.area }}
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>ESCALA DE AVALIAÇÃO</legend>
                        <span><strong>Para esta avaliação considerar as atribuições básicas abaixo, conforme as seguintes notas:</strong></span><br>
                        <span>5 - Superou muito as expectativas: É percebido por outras áreas/pessoas como alguém com uma atuação excepcional, modelo de referência</span><br>
                        <span>4 - Superou as expectativas: Atuação melhor que o esperado com alto padrão de qualidade</span><br>
                        <span>3 -Atingiu as expectativas: Atuação adequada ao esperado (satisfatório), atende os padrões de qualidade e produtividade</span><br>
                        <span>2 - Abaixo das expectativas: Atuação abaixo do esperado (precisa de desenvolvimento)</span><br>
                        <span>1 - Muito abaixo das expectativas: Atuação não aceitável, desempenho muito abaixo do que é esperado para a função</span>
                    </fieldset>

                    <fieldset v-for="item in lista_topicos" :key="item.id">
                        <legend>{{ item.topico }}</legend>
                        <div class="alert alert-info" v-if="item.topico_explicacao">
                            {{ item.topico_explicacao }}
                        </div>
                        <fieldset v-for="(subtopico,index) in item.subtopicos" :key="subtopico.id || index">
                            <legend>{{ subtopico.topico }}</legend>
                            <p class="quebra_linha_textarea">{{ subtopico.topico_explicacao }}</p>
                            <div class="form-group">
                                <label>{{ visualizando ? "Nota" : "Informe sua nota" }}</label>
                                <select :disabled="visualizando" class="form-control validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        v-model="formAvaliar.respostas[item.id][index].nota"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="resp in 5" :value="resp" :key="resp">{{ resp }}</option>
                                </select>
                            </div>
                            <h5 v-if="formAvaliar.principal">Nota do colaborador:
                                {{ formAvaliar.respostasFunc[item.id][index].nota }}</h5>
                        </fieldset>
                    </fieldset>
                    <fieldset>
                        <legend>MINHAS CONSIDERAÇÕES</legend>
                        <textarea :disabled="visualizando" v-model="formAvaliar.comentario" class="form-control"
                                  @blur.prevent="valida_campo_vazio($event.target, 1)"
                                  @change.prevent="valida_campo_vazio($event.target, 1)"
                                  placeholder="Se desejar, faça considerações" rows="4"
                        ></textarea>

                        <h5 class="mt-3" v-if="formAvaliar.principal">Considerações do
                            colaborador: {{ formAvaliar.comentario_funcionario }}</h5>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !preload && !visualizando"
                        @click="salvar()"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div id="conteudo">

            <fieldset>
                <legend>Filtro</legend>
                <form class="row" @submit.prevent="$refs.componente.buscar()">

                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label>Avaliações</label>
                            <select class="form-control form-control-sm" v-model="controle.dados.campoAvaliacao"
                                    :disabled="controle.carregando"
                                    @change="$refs.componente.buscar()"
                            >
                                <option :value="item.id" v-for="item in lista_avaliacoes" :key="item.id">
                                    {{ item.titulo }} - ({{ item.status }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                                    :disabled="controle.carregando"
                                    @change="$refs.componente.buscar()"
                            >
                                <option value="">Todos os Status</option>
                                <option v-for="item in statusAvaliacaoSelecionada" :value="item.value"
                                        :key="item.value"
                                >
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-9">
                        <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                                @click="atualizar"
                        >
                            <i
                                :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"
                            ></i>
                            Atualizar
                        </button>
                    </div>
                </form>
            </fieldset>

            <div class="row mt-2 pt-1 pb-1 border-bottom"
                 v-if="!controle.carregando && selecionadaAvaliacao && selecionadaAvaliacao.auto_avaliacao"
            >
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

            <div class="row mt-2 pt-1 pb-1 border-bottom"
                 v-if="!controle.carregando && selecionadaAvaliacao && !selecionadaAvaliacao.auto_avaliacao"
            >
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

            <p class=" mt-2 text-center" v-if="controle.carregando">
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
                        <td class="text-center">{{ selecionadaAvaliacao.tipo_pj ? "Fornecedor" : "Funcionário" }}</td>
                        <td class="text-center">Avaliador</td>
                        <td class="text-center">Avaliar Como</td>
                        <td class="text-center">Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista" :key="item.id" :class="{
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
                                <span
                                    v-show="item.origem_feedback === 'Funcionario' && !item.principal"
                                >Autoavaliação</span>
                            <span v-if="item.origem_feedback === 'Avaliador'">
                                 {{ item.tipo_avaliador ? item.tipo_avaliador.label : "---" }}
                            </span>


                        </td>
                        <td class="text-center">

                            <div class="dropdown show"
                                 v-show="
                                  (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) // Autoavaliacao Par
                                  || (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par) // Autoavaliacao Gestor
                                  || (item.status === 'Pendente' && (!item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id) // autoavailiacao
                                  || item.status === 'Avaliada' || (item.status === 'Avaliada' && item.fazer_avaliacao_final) // Avaliacao final
                                  || (item.status === 'Finalizada' && !item.fazer_avaliacao_final)) // successo
                                "
                            >
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                     aria-labelledby="dropdownMenuLink"
                                >
                                    <a class="dropdown-item" href="javascript://" title="Avaliar"
                                       data-toggle="modal" data-target="#janelaCadastrar" @click="avaliarForm(item)"
                                       v-if="(item.status === 'Pendente' && item.fez_auto_avaliacao  && !item.principal) || (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par)"
                                    >
                                        Avaliar
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Avaliar"
                                       data-toggle="modal" data-target="#janelaCadastrar" @click="avaliarForm(item)"
                                       v-if="item.status === 'Pendente' && (!item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id)"
                                    >
                                        Avaliar
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação"
                                       data-toggle="modal" data-target="#janelaCadastrar"
                                       @click="avaliarForm(item, true)" v-if="item.status === 'Avaliada'"
                                    >
                                        Visualizar Avaliação
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação"
                                       data-toggle="modal" data-target="#janelaCadastrar"
                                       @click="avaliarForm(item, true)"
                                       v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final"
                                    >
                                        Visualizar Avaliação
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Fazer Avaliação Final"
                                       data-toggle="modal" data-target="#janelaAvaliacaoFinal"
                                       @click="avaliarFinalForm(item)"
                                       v-if="item.status === 'Avaliada' && item.fazer_avaliacao_final"
                                    >
                                        Fazer Avaliação Final
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação Final"
                                       data-toggle="modal" data-target="#janelaAvaliacaoFinal"
                                       @click="avaliarFinalForm(item, true)"
                                       v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                    >
                                        Visualizar Avaliação Final
                                    </a>

                                    <a class="dropdown-item" :href="`${urlImpressao}/${item.token}`" target="_blank"
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
                    <table class="table table-bordered"
                           v-if="selecionadaAvaliacao && !selecionadaAvaliacao.auto_avaliacao"
                    >
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
                        <tr v-if="!item.avaliacao.auto_avaliacao && item.principal" v-for="item in lista" :key="item.id"
                            class="bg-white"
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

                            <td class="text-center" :class="{
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

                                <div class="dropdown show"
                                     v-show="
                                     (item.status === 'Pendente' && item.principal && !item.pendente_avaliacao_par)  || item.status === 'Avaliada' || (item.status === 'Avaliada' && item.fazer_avaliacao_final) // Avaliacao final
                                  || (item.status === 'Finalizada' && !item.fazer_avaliacao_final) // successo
                                "
                                >
                                    <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                       id="dropdownMenuLink"
                                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    >
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                         aria-labelledby="dropdownMenuLink"
                                    >
                                        <a class="dropdown-item" href="javascript://" title="Avaliar"
                                           data-toggle="modal" data-target="#janelaCadastrar" @click="avaliarForm(item)"
                                           v-if="(item.status === 'Pendente' && item.principal)"
                                        >
                                            Avaliar
                                        </a>

                                        <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação"
                                           data-toggle="modal" data-target="#janelaCadastrar"
                                           @click="avaliarForm(item, true)" v-if="item.status === 'Avaliada'"
                                        >
                                            Visualizar Avaliação
                                        </a>

                                        <a class="dropdown-item" href="javascript://" title="Fazer Avaliação Final"
                                           data-toggle="modal" data-target="#janelaAvaliacaoFinal"
                                           @click="avaliarFinalForm(item)"
                                           v-if="item.status === 'Avaliada'  && item.fazer_avaliacao_final"
                                        >
                                            Fazer Avaliação Final
                                        </a>

                                        <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação Final"
                                           data-toggle="modal" data-target="#janelaAvaliacaoFinal"
                                           @click="avaliarFinalForm(item, true)"
                                           v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal"
                                        >
                                            Visualizar Avaliação Final
                                        </a>

                                        <a class="dropdown-item" :href="`${urlImpressao}/${item.token}`" target="_blank"
                                           title="Imprimir Avaliação Final"
                                           v-if="item.status === 'Finalizada' || ( item.total_avaliacoes_concluidas > 0 && ( item.principal || tem_privilegio_gestao_rh) )"
                                        >
                                            Imprimir Avaliação Final
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>
            </div>


            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="urlPaginacao" :por-pagina="qntPag"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from "../../../ControlePaginacao";
import modal from "../../../Modal";
import DatePicker from "../../../DatePicker";
import RadarChart from "../../../Charts/Radar";
import validacoes from "../../../../mixins/Validacoes";

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
        modal: { // modal Pai
            type: String,
            required: false,
            default: ""
        }
    },
    async mounted() {
        await this.listaAvaliacao();
        this.formAvaliarDefault = _.cloneDeep(this.formAvaliar);
        this.formAvaliarFinalDefault = _.cloneDeep(this.formAvaliarFinal);

        // CORREÇÃO APLICADA: Verificação de segurança antes de acessar array
        if (this.lista_avaliacoes && this.lista_avaliacoes.length > 0) {
            this.controle.dados.campoAvaliacao = this.lista_avaliacoes[0].id;
        }
        await this.atualizar();
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: "",
            titulo_janela_final: "Open Feedback - Avaliação Final",
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
                comentario: "",
                comentario_funcionario: ""
            },

            // CORREÇÃO APLICADA: Inicialização melhorada do formAvaliarFinal
            formAvaliarFinal: {
                dados_do_funcionario: {},
                avaliador_principal: "",
                status_avaliacao: "",
                total_aval: "",
                media_aval: "",
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

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/atualizar`,
            urlImpressao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/impressao`,

            fluxoAvaliacao: null,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    campoAvaliacao: "",
                    campoStatus: "",
                    ano_avaliacao: new Date().getFullYear(),
                    tipo_avaliacao: ""
                }
            }
        };
    },
    computed: {
        listaKeysAvaliacaoPorAnoOrdenado() {
            return Object.keys(this.lista_avaliacoes_por_ano).sort((a, b) => b - a);
        },
        groupAvaliacaoAno() {
            let group = _.groupBy(this.lista_avaliacoes_por_ano[this.controle.dados.ano_avaliacao], "avaliacao_tipo_id");

            let array = [];
            for (let key in group) {
                if (group[key][0].ativo) {
                    array.push({
                        avaliacao_tipo_id: key,
                        avaliacao_tipo: group[key][0].avaliacao_tipo.nome
                    });
                }
            }
            return array;
        },
        selecionadaAvaliacao() {
            return (this.lista_avaliacoes).find(item => item.id === this.controle.dados.campoAvaliacao) ?? null;
        },
        statusAvaliacaoSelecionada() {
            let statusSemAutoAvaliacao = [
                { label: "Pendente avaliação gestor", value: "Pendente" },
                { label: "Avaliada pelo Gestor", value: "Avaliada" },
                { label: "Completa", value: "Finalizada" }
            ];
            let statusComAutoAvaliacao = [
                { label: "Pendente", value: "Pendente" },
                { label: "Avaliada", value: "Avaliada" },
                { label: "Finalizada", value: "Finalizada" }
            ];

            let status = this.selecionadaAvaliacao?.auto_avaliacao ? statusComAutoAvaliacao : statusSemAutoAvaliacao;
            return status ?? [];
        }

    },
    methods: {
        // CORREÇÃO PRINCIPAL: Substituição do filtro por métodos
        formatarDecimal(valor) {
            if (valor === null || valor === undefined || isNaN(valor)) {
                return "0.0";
            }
            return Number(valor).toFixed(1);
        },

        getMediaFormatada(chartName) {
            if (this.formAvaliarFinal.resultado_topico_pai &&
                this.formAvaliarFinal.resultado_topico_pai[chartName] &&
                this.formAvaliarFinal.resultado_topico_pai[chartName].media !== undefined) {
                return this.formatarDecimal(this.formAvaliarFinal.resultado_topico_pai[chartName].media);
            }
            return "0.0";
        },

        getMediaTopico(topicoId) {
            if (this.formAvaliarFinal.result_topico &&
                this.formAvaliarFinal.result_topico[topicoId] &&
                this.formAvaliarFinal.result_topico[topicoId].media !== undefined) {
                return this.formatarDecimal(this.formAvaliarFinal.result_topico[topicoId].media);
            }
            return "0.0";
        },

        async listaAvaliacao() {
            await axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/lista/listavaliacoes`)
                .then(response => {
                    this.lista_avaliacoes = response.data.lista_avaliacoes;
                    this.lista_anos = response.data.lista_anos;

                }).catch(
                    error => (this.preloadAjax = false)
                );
        },

        // CORREÇÃO APLICADA: Melhorada a função addPlanoAcao
        addPlanoAcao() {
            // Garante que o array planos_acoes existe
            if (!this.formAvaliarFinal.planos_acoes) {
                this.formAvaliarFinal.planos_acoes = [];
            }

            let obj = {
                nova: true,
                avaliacao_feedback_id: this.formAvaliarFinal.avaliacao_feedback_id || "",
                avaliacao_feedback_id_avaliador: this.formAvaliarFinal.avaliacao_feedback_id_avaliador || "",
                gestor_id: this.formAvaliarFinal.gestor_id || "",
                topico_id: "",
                responsavel: (this.formAvaliarFinal.dados_do_funcionario && this.formAvaliarFinal.dados_do_funcionario.nome) || "",
                plano_de_acao: "",
                inicio: "",
                termino: "",
                status: "",
                dados_extras: {}
            };
            this.formAvaliarFinal.planos_acoes.push(obj);
        },

        // CORREÇÃO APLICADA: Melhorada a função removerPlanoAcao
        removerPlanoAcao(index) {
            if (!this.formAvaliarFinal.planos_acoes || index < 0 || index >= this.formAvaliarFinal.planos_acoes.length) {
                return;
            }

            if (this.formAvaliarFinal.planos_acoes[index].id) {
                if (!this.formAvaliarFinal.planos_acoes_delete) {
                    this.formAvaliarFinal.planos_acoes_delete = [];
                }
                this.formAvaliarFinal.planos_acoes_delete.push(this.formAvaliarFinal.planos_acoes[index].id);
            }
            this.formAvaliarFinal.planos_acoes.splice(index, 1);
        },

        avaliarForm(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando;
            this.editando = true;
            this.titulo_janela = `Avaliação: ${avaliacaoFeedback.avaliacao.titulo}`;
            this.preload = true;

            this.formAvaliar = _.cloneDeep(this.formAvaliarDefault); //copia
            formReset();

            axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/edit`)
                .then(response => {
                    this.lista_topicos = response.data.topicos;
                    this.formAvaliar.respostas = response.data.respostas;
                    this.formAvaliar.respostasFunc = response.data.respostas_funcionario;
                    this.formAvaliar.comentario = response.data.comentario;
                    this.formAvaliar.comentario_funcionario = response.data.comentario_funcionario;
                    this.formAvaliar.dados_do_funcionario = response.data.dados_do_funcionario;
                    this.formAvaliar.avaliacao_feedback_id = response.data.avaliacao_feedback_id;
                    this.formAvaliar.origem_feedback = response.data.origem_feedback;
                    this.formAvaliar.principal = response.data.principal;
                    this.formAvaliar.tipo_pj = response.data.tipo_pj;
                    this.editando = true;
                    setupCampo();
                    this.preload = false;
                }).catch(
                error => (this.preloadAjax = false)
            );
        },

        // CORREÇÃO APLICADA: Melhorada a função avaliarFinalForm
        avaliarFinalForm(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando;
            this.editando = true;
            this.titulo_janela = `Avaliação Final: ${avaliacaoFeedback.avaliacao.titulo}`;
            this.preloadAvalFinal = true;

            // Reset para um estado seguro
            this.formAvaliarFinal = _.cloneDeep(this.formAvaliarFinalDefault);

            // Garante que arrays críticos estão inicializados
            if (!this.formAvaliarFinal.result_topico_pai_agrupado) {
                this.formAvaliarFinal.result_topico_pai_agrupado = [];
            }
            if (!this.formAvaliarFinal.resultChart) {
                this.formAvaliarFinal.resultChart = [];
            }
            if (!this.formAvaliarFinal.resultado_topico_pai) {
                this.formAvaliarFinal.resultado_topico_pai = {};
            }
            if (!this.formAvaliarFinal.planos_acoes) {
                this.formAvaliarFinal.planos_acoes = [];
            }

            formReset();

            axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/final`)
                .then(({ data }) => {
                    // Atribuição segura dos dados com fallbacks
                    Object.assign(this.formAvaliarFinal, {
                        ...data,
                        result_topico_pai_agrupado: data.result_topico_pai_agrupado || [],
                        resultChart: data.resultChart || [],
                        resultado_topico_pai: data.resultado_topico_pai || {},
                        planos_acoes: data.planos_acoes || [],
                        result_topico: data.result_topico || {}
                    });

                    this.editando = true;
                    setupCampo();
                    this.preloadAvalFinal = false;
                }).catch(error => {
                console.error("Erro ao carregar avaliação final:", error);
                this.preloadAvalFinal = false;
                toastr.error("Erro ao carregar avaliação final", "Erro!");
            });
        },

        salvarAvaliacaoFinal() {

            this.validaBlur();
            let countErro = document.querySelectorAll(".is-invalid").length;
            if (countErro > 0) {
                toastr.error("Verifique os campos", "Atenção!");
                return false;
            }

            this.preloadAvalFinal = true;

            axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${this.formAvaliarFinal.avaliacao_feedback_id}/final`, this.formAvaliarFinal).then(response => {
                $("#janelaAvaliacaoFinal").modal("hide");
                mostraSucesso("", "Avaliação Final salva com sucesso");
                this.preloadAvalFinal = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preload = false));
        },

        salvar() {
            this.validaBlur();
            let countErro = document.querySelectorAll(".is-invalid").length;
            if (countErro > 0) {
                toastr.error("Verifique os campos", "Atenção!");
                return false;
            }
            this.preload = true;

            axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${this.formAvaliar.avaliacao_feedback_id}`, this.formAvaliar).then(response => {
                $("#janelaCadastrar").modal("hide");
                mostraSucesso("", "Avaliação enviada com sucesso");
                this.preload = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preload = false));
        },
        carregou(dados) {
            this.lista = dados.itens;
            this.fluxoAvaliacao = dados.itens.length ? dados.itens[0].fluxo : null;
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos;
            this.lista_status = dados.lista_status;
            this.lista_avaliacoes_por_ano = dados.lista_avaliacoes_por_ano;
            this.tem_privilegio_gestao_rh = dados.tem_privilegio_gestao_rh;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        async atualizar() {
            this.$refs.componente.atual = 1;
            await this.$refs.componente.buscar();
        }
    }

};
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

</style>
