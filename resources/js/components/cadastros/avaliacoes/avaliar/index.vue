<template>
    <div :id="hash">
        <modal id="janelaAvaliacaoFinal" :titulo="titulo_janela_final" :size="90">
            <template slot="conteudo">
                <preload v-show="preloadAvalFinal"></preload>
                <div v-if="!preloadAvalFinal">
                    <fieldset>
                        <legend>DADOS</legend>
                        <div class="row">
                            <div class="col-12 col-lg-4"><strong>Nome:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.nome }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Matrícula:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.matricula }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Admissão:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.data_admissao }}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 col-lg-4"><strong>Cargo:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.cargo }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Área:</strong>
                                {{ formAvaliarFinal.dados_do_funcionario.area }}
                            </div>
                        </div>
                    </fieldset>


                    <table class="table" v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado"
                           :key="index">
                        <thead>
                        <tr>
                            <th>{{ item[index].topico_pai }}</th>
                            <th class="text-center" v-for="(avaliador, id) in item[0].avaliadores" :key="avaliador.id">
                                Avaliador {{id + 1}}
                            </th>
                            <th class="text-center">MÉDIA</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="sub in item">
                            <td style="width: 33%">{{ sub.subtopico }}</td>
                            <td style="width: 15%" v-for="avaliador in sub.avaliadores">
                                <input type="number" class="form-control form-control-sm text-center"
                                       readonly="readonly" min="0" max="5"
                                       step="0.1" :value="avaliador.nota | casasDecimais">
                            </td>
                            <td style="width: 7%" class="text-center">
                                <input type="number" class="form-control form-control-sm text-center"
                                       readonly="readonly" min="0" max="5"
                                       step="0.1" :value="sub.media | casasDecimais">
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="table" v-if="formAvaliarFinal.result_topico_pai_agrupado.length > 0">
                        <thead>
                        <tr>
                            <th class="text-center"
                                v-for="(avaliador,id) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                :key="avaliador.id">Avaliador {{ id+1 }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td v-for="avaliador in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                                :key="avaliador.id">
                                <label>Considerações</label>
                                <textarea rows="5" class="form-control form-control-sm" readonly="readonly">{{ avaliador.comentario }}</textarea>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="row justify-content-center mt-5">
                        <div v-for="(chart, index) in formAvaliarFinal.resultChart" :key="index" class="col-md-4">
                            <h4 class="text-center">{{ chart.name }}</h4>
                            <RadarChart :id="chart.name" :chart-data="chart.data"/>
                            <h4 class="text-center">Média:
                                {{ formAvaliarFinal.resultado_topico_pai[chart.name].media | casasDecimais }}</h4>
                        </div>
                        <div class="col-md-12 text-center">
                            <h4>Nota final: {{ formAvaliarFinal.nota_final | casasDecimais }}</h4>
                        </div>
                    </div>

                    <fieldset>
                        <legend>Oportunidades de Melhoria / Plano de Ação</legend>

                        <button class="btn btn-sm btn-primary mb-2" @click="addPlanoAcao($event.target)"
                                v-show="!visualizando">
                            <i class="fa fa-plus"></i> Adicionar Plano
                        </button>

                        <fieldset v-for="(item, index) in formAvaliarFinal.planos_acoes" :key="index">
                            <legend>Plano - {{ index + 1 }}</legend>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Competência/Desempenho</label>
                                        <select class="form-control form-control-sm validacampo"
                                                v-model="item.topico_id"
                                                :disabled="visualizando"
                                                @blur.prevent="valida_campo_vazio($event.target, 1)"
                                                @change.prevent="valida_campo_vazio($event.target, 1)">
                                            <option value="">Selecione</option>
                                            <option v-for="(topico, topico_id) in formAvaliarFinal.result_topico"
                                                    :key="topico_id" :value="topico_id">{{ topico.topico_pai }} -
                                                {{ topico.subtopico }}
                                            </option>
                                        </select>
                                        <h5 class="my-3 text-danger" v-if="item.topico_id">Média:
                                            {{
                                                formAvaliarFinal.result_topico[item.topico_id].media | casasDecimais
                                            }}</h5>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="">Plano de Ação</label>
                                        <textarea rows="5" class="form-control form-control-sm validacampo"
                                                  v-model="item.plano_de_acao"
                                                  @blur.prevent="valida_campo_vazio($event.target, 1)"
                                                  :disabled="visualizando"></textarea>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <date-picker formsm label="Início" v-model="item.inicio"
                                                     :disabled="visualizando"></date-picker>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <date-picker formsm label="Término" v-model="item.termino"
                                                     :disabled="visualizando"></date-picker>
                                    </div>
                                </div>

                                <div class="col-lg-12" v-show="!visualizando">
                                    <button class="btn btn-sm btn-danger"
                                            @click="removerPlanoAcao(index)">
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
                        v-show="editando && !visualizando && !preload && formAvaliarFinal.planos_acoes.length > 0"
                        @click="salvarAvaliacaoFinal()">
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
                        <div class="row">
                            <div class="col-12 col-lg-4"><strong>Nome:</strong>
                                {{ formAvaliar.dados_do_funcionario.nome }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Matrícula:</strong>
                                {{ formAvaliar.dados_do_funcionario.matricula }}
                            </div>
                            <div class="col-12 col-lg-4"><strong>Admissão:</strong>
                                {{ formAvaliar.dados_do_funcionario.data_admissao }}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 col-lg-4"><strong>Cargo:</strong>
                                {{ formAvaliar.dados_do_funcionario.cargo }}
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

                    <fieldset v-for="item in lista_topicos">
                        <legend>{{ item.topico }}</legend>
                        <div class="alert alert-info" v-if="item.topico_explicacao">
                            {{ item.topico_explicacao }}
                        </div>
                        <fieldset v-for="(subtopico,index) in item.subtopicos">
                            <legend>{{ subtopico.topico }}</legend>
                            <p class="quebra_linha_textarea">{{ subtopico.topico_explicacao }}</p>
                            <div class="form-group">
                                <label>{{ visualizando ? "Nota" : "Informe sua nota" }}</label>
                                <select :disabled="visualizando" class="form-control validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        v-model="formAvaliar.respostas[item.id][index].nota">
                                    <option value="">Selecione</option>
                                    <option v-for="resp in 5" :value="resp">{{ resp }}</option>
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
                                  placeholder="Se desejar, faça considerações" rows="4"></textarea>

                        <h5 class="mt-3" v-if="formAvaliar.principal">Considerações do
                            colaborador: {{ formAvaliar.comentario_funcionario }}</h5>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !preload && !visualizando"
                        @click="salvar()">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div id="conteudo">

            <div class="row mt-2 pt-1 pb-1 border-bottom">
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

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="table table-bordered">
                    <thead class="bg-white">
                    <tr class="bg-white">
                        <td class="text-center">Título</td>
                        <td class="text-center">Tipo</td>
                        <td class="text-center">Avaliar até</td>
                        <td class="text-center">Funcionário</td>
                        <td class="text-center">Avaliar Como</td>
                        <td class="text-center">Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista" :class="{
                        'bg-danger text-white': item.pendente_autoavaliacao,
                        'bg-pink': item.pendente_autoavaliacao_colaborador,
                        'bg-warning': item.pendente_avaliacao_par && item.status !== 'Finalizada',
                        'bg-info text-white': !item.pendente_avaliacao_par && item.status !== 'Finalizada',
                        'bg-success text-white': item.status === 'Finalizada'
                    }"
                    >
                        <td class="text-center">{{ item.avaliacao.titulo }}</td>
                        <td class="text-center">{{ item.avaliacao.avaliacao_tipo.nome }}</td>
                        <td class="text-center">{{ item.avaliacao.data_fim_prazo }}</td>
                        <td class="text-center">
                            <i class="fa fa-user" v-if="item.avaliador_id === item.funcionario_id"></i>
                            {{ item.funcionario.nome }}
                        </td>
                        <td class="text-center">
                            <span v-show="item.origem_feedback === 'Funcionario' && !item.principal">Autoavaliação</span>
                            <span v-show="item.origem_feedback === 'Avaliador' && !item.principal">Avaliador Par</span>
                            <span v-show="item.origem_feedback === 'Avaliador' && item.principal">Avaliador Gestor (Principal)</span>
                        </td>
                        <td class="text-center">

                            <div class="dropdown show"
                                 v-show="
                                  (item.status === 'Pendente' && item.fez_auto_avaliacao && !item.principal) // Autoavaliacao Par
                                  || (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par) // Autoavaliacao Gestor
                                  || (item.status === 'Pendente' && (!item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id) // autoavailiacao
                                  || item.status === 'Avaliada' || (item.status === 'Avaliada' && item.fazer_avaliacao_final) // Avaliacao final
                                  || (item.status === 'Finalizada' && !item.fazer_avaliacao_final)) // successo
                                ">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Avaliar"
                                       data-toggle="modal" data-target="#janelaCadastrar" @click="avaliarForm(item)"
                                       v-if="(item.status === 'Pendente' && item.fez_auto_avaliacao  && !item.principal) || (item.status === 'Pendente' && item.fez_auto_avaliacao && item.principal && !item.pendente_avaliacao_par)">
                                        Avaliar
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Avaliar"
                                       data-toggle="modal" data-target="#janelaCadastrar" @click="avaliarForm(item)"
                                       v-if="item.status === 'Pendente' && (!item.fez_auto_avaliacao && item.avaliador_id === item.funcionario_id)">
                                        Avaliar
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação"
                                       data-toggle="modal" data-target="#janelaCadastrar"
                                       @click="avaliarForm(item, true)" v-if="item.status === 'Avaliada'">
                                        Visualizar Avaliação
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação"
                                       data-toggle="modal" data-target="#janelaCadastrar"
                                       @click="avaliarForm(item, true)"
                                       v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final">
                                        Visualizar Avaliação
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Fazer Avaliação Final"
                                       data-toggle="modal" data-target="#janelaAvaliacaoFinal"
                                       @click="avaliarFinalForm(item)"
                                       v-if="item.status === 'Avaliada' && item.fazer_avaliacao_final">
                                        Fazer Avaliação Final
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar Avaliação Final"
                                       data-toggle="modal" data-target="#janelaAvaliacaoFinal"
                                       @click="avaliarFinalForm(item, true)"
                                       v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal">
                                        Visualizar Avaliação Final
                                    </a>

                                    <a class="dropdown-item" :href="`${urlImpressao}/${item.token}`" target="_blank" title="Imprimir Avaliação Final"
                                       v-if="item.status === 'Finalizada' && !item.fazer_avaliacao_final && item.principal">
                                        Imprimir Avaliação Final
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="urlPaginacao" :por-pagina="qntPag"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from "../../../ControlePaginacao";
import modal from "../../../Modal";
import DatePicker from "../../../DatePicker";
import RadarChart from "../../../Charts/Radar"
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
    mounted() {
        this.atualizar();
        this.formAvaliarDefault = _.cloneDeep(this.formAvaliar);
        this.formAvaliarFinalDefault = _.cloneDeep(this.formAvaliarFinal);
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

            chartsRadares: [],

            formAvaliar: {
                respostas: [],
                respostasFunc: [],
                dados_do_funcionario: [],
                comentario: '',
                comentario_funcionario: ''
            },

            formAvaliarFinal: {
                dados_do_funcionario: [],
                avaliador_principal: '',
                status_avaliacao: '',
                total_aval: '',
                media_aval: '',
                nota_final: 0,
                resultado_topico_pai: [],
                result_topico_pai_agrupado: [],
                result_topico: [],
                result_subtopico: [],
                resultChart: [],
                planos_acoes: [],
                planos_acoes_delete: [],
            },

            formAvaliarDefault: null,
            formAvaliarFinalDefault: null,

            lista: [],
            lista_topicos: [],
            lista_avaliacoes_tipos: [],
            lista_status: [],

            avaliacaoSelecionada: null,

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/atualizar`,
            urlImpressao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/impressao`,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                }
            }
        };
    },
    filters: {
        casasDecimais(valor) {
            return valor.toFixed(1);
        }
    },
    methods: {
        addPlanoAcao() {
            let obj = {
                nova: true,
                avaliacao_feedback_id: this.formAvaliarFinal.avaliacao_feedback_id,
                avaliacao_feedback_id_avaliador: this.formAvaliarFinal.avaliacao_feedback_id_avaliador,
                gestor_id: this.formAvaliarFinal.gestor_id,
                topico_id: '',
                responsavel: this.formAvaliarFinal.dados_do_funcionario.nome,
                plano_de_acao: '',
                inicio: '',
                termino: '',
                status: '',
                dados_extras: {}
            };
            this.formAvaliarFinal.planos_acoes.push(obj);
        },
        removerPlanoAcao(index) {
            if (this.formAvaliarFinal.planos_acoes[index].id) {
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
                    this.editando = true;
                    setupCampo();
                    this.preload = false;
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        avaliarFinalForm(avaliacaoFeedback, visualizando = false) {
            this.visualizando = visualizando;
            this.editando = true;
            this.titulo_janela = `Avaliação Final: ${avaliacaoFeedback.avaliacao.titulo}`;
            this.preloadAvalFinal = true;

            this.formAvaliarFinal = _.cloneDeep(this.formAvaliarFinalDefault); //copia
            formReset();

            axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/${avaliacaoFeedback.id}/final`)
                .then(({data}) => {
                    Object.assign(this.formAvaliarFinal, data);
                    this.editando = true;
                    setupCampo();
                    this.preloadAvalFinal = false;
                }).catch(
                error => (this.preloadAvalFinal = false)
            );

        },

        salvarAvaliacaoFinal() {

            this.validaBlur();
            let countErro = document.querySelectorAll(".is-invalid").length
            if (countErro > 0) {
                toastr.error("Verifique os campos", "Atenção!")
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
            let countErro = document.querySelectorAll(".is-invalid").length
            if (countErro > 0) {
                toastr.error("Verifique os campos", "Atenção!")
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
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos;
            this.lista_status = dados.lista_status;
            this.controle.carregando = false;
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

</style>
