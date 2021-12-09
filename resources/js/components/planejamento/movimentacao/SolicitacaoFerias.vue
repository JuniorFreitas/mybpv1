<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90">
            <template slot="conteudo">
                <preload v-show="preload" class="text-center"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
                </div>
                <form v-if="!preload && (!cadastrado && !atualizado) " :id="`form_${hash}`" onsubmit="return false;">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">

                            <colaborador :model="form" :verifica="visualizar" :hash="hash"></colaborador>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo</label>
                                    <select v-model="form.centro_custo_id" class="form-control"
                                            :disabled="visualizar"
                                            onchange="valida_campo_vazio(this,1)"
                                            onblur="valida_campo_vazio(this,1)">
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Tem Falta?</label>
                                <select type="text" class="form-control" v-model="form.tem_faltas"
                                        :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option :value="''">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.tem_faltas === true">
                                <label>Quantidade de faltas</label>
                                <input type="text" class="form-control" v-model="form.qnt_faltas"
                                       :disabled="visualizar"
                                       onchange="valida_campo_vazio(this,1)">
                            </div>

                            <div class="col-12 col-md-4 mt-2">
                                <legend>Quantidade de dias disponíveis: {{ qntDias }}</legend>
                                <!--                                <input type="text" class="form-control" v-model="form.qnt_dias"-->
                                <!--                                       :disabled="visualizar">-->
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Informe a quantidade de dias:</label>
                                <input type="text" class="form-control" v-model="form.qnt_dias"
                                       :disabled="visualizar">
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data da saída</label>
                                <datepicker label="" class="corrigiDatepicker" v-model="form.data_saida"
                                            :disabled="visualizar"></datepicker>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data do retorno</label>
                                <datepicker label="" class="corrigiDatepicker" v-model="form.data_retorno"
                                            :disabled="visualizar"></datepicker>
                            </div>

                            <div class="col-12 col-md-4">
                                <legend>Dias de saldo: {{ qntSaldo }}</legend>
                            </div>

                            <gestoraprovacao :model="form" :verifica="visualizar" :hash="hash"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" v-model="form.obs" cols="5" rows="5"
                                              :disabled="visualizar"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">
                            Esta solicitação ainda não foi aprovada ou reprovada!
                        </div>

                        <fieldset v-if="visualizar || editando">
                            <legend>Aprovação</legend>
                            <div class="row">

                                <div v-if="form.data_aprovacao" class="col-12">
                                    <legend>{{ form.status_aprovacao }}
                                        por: {{ form.quem_aprovou.nome }} em
                                        {{ form.data_aprovacao }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea class="form-control" :disabled="form.data_aprovacao || !aprovando"
                                                  v-model="form.obs_aprovacao"
                                                  cols="5" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select :disabled="form.data_aprovacao || !aprovando " v-if="editando"
                                                v-model="form.status_aprovacao"
                                                class="form-control">
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>

                                        <select :disabled="form.data_aprovacao || !aprovando " v-if="!editando"
                                                v-model="form.status_aprovacao"
                                                onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" class="form-control">
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset v-if="(visualizar || editando) && aprovaRH">
                            <legend>Aprovação RH</legend>
                            <div class="row">

                                <div class="col-12" v-if="form.data_aprovacao_rh">
                                    <legend>{{ form.resposta_rh }} por:
                                        {{ form.rh_aprovacao.nome }} em {{
                                            form.data_aprovacao_rh
                                        }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea class="form-control" :disabled="!aprovacao_rh"
                                                  v-model="form.obs_rh"
                                                  cols="5" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select :disabled="!aprovacao_rh" v-if="editando"
                                                v-model="form.resposta_rh"
                                                class="form-control">
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>

                                        <select :disabled="!aprovacao_rh" v-if="!editando"
                                                v-model="form.resposta_rh"
                                                onblur="valida_campo_vazio(this,1)"
                                                onchange="valida_campo_vazio(this,1)" class="form-control">
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                </form>
            </template>
            <template slot="rodape">
                <div v-show="!visualizar">
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="editando && !atualizado  && !preload"
                            @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="!editando && !cadastrado  && !preload"
                            @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !atualizado  && !preload && !form.data_aprovacao"
                        @click.prevent="aprovar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !atualizado && !preload && aprovaRH"
                        @click.prevent="aprovarRH">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" @change="atualizar()"
                               :disabled="controle.carregando"
                               id="filtroIntervalo"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label="" @onselect="atualizar()"
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input type="text"
                               placeholder="Buscar por colaborador"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-4">
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

                <div class="col-12"></div>
            </form>

            <div class="d-flex">
                <button type="button" class="btn btn-sm btn-success mr-1" :disabled="controle.carregando"
                        @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm btn-primary mr-1" data-toggle="modal"
                        :disabled="controle.carregando"
                        :data-target="`#${hash}`"
                        @click.prevent="formNovo">
                    Solicitar
                </button>

                <form method="post" :action="`movimentacao/ferias-prevista/export`" target="_blank">
                    <input type="hidden" name="_token" :value="CSRF_token">
                    <input type="hidden" name="filtroPeriodo" :value="controle.dados.filtroPeriodo">
                    <input type="hidden" name="periodo" :value="controle.dados.periodo">
                    <input type="hidden" name="campoBusca" :value="controle.dados.campoBusca">
                    <input type="hidden" name="campoStatus" :value="controle.dados.campoStatus">
                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">

                    <button type="submit" class="btn btn-sm btn-primary mr-1" data-toggle="modal"
                            :disabled="controle.carregando || !controle.dados.filtroPeriodo">
                        <i class="fa fa-files-pdf"></i> Gerar Excel
                    </button>
                </form>
            </div>
        </fieldset>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mb-2 mt-2 pt-1 pb-1 border-bottom" v-show="!controle.carregando && lista.length > 0">
                <span class="small text-right">
                    Legenda:
                    <i class="fas fa-circle text-warning"></i> Aguardando
                    <i class="fas fa-circle text-success ml-2"></i> Aprovado pelo RH
                    <i class="fas fa-circle text-info ml-2"></i> Aprovado pelo Gestor
                    <i class="fas fa-circle text-danger ml-2"></i> Reprovado
                </span>
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th>CÓD</th>
                        <th>Solicitação</th>
                        <th>Centro de custo</th>
                        <th>Colaborador</th>
                        <th>Data saida</th>
                        <th>Qnt dias</th>
                        <th>Data retorno</th>
                        <th>Dias saldo</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista"
                        :class="!item.status_aprovacao ? 'table-warning'
                        : item.status_aprovacao === 'reprovado' ? 'table-danger'
                        : item.resposta_rh === 'reprovado' ? 'table-danger'
                        : item.status_aprovacao === 'aprovado' && !item.resposta_rh ? 'table-info'
                        : item.status_aprovacao === 'aprovado' && item.resposta_rh === 'aprovado' ? 'table-success'
                        : null">
                        <td>
                            {{ item.id }}
                        </td>

                        <td>
                            {{ item.user_cadastrou.nome }} <br>
                            {{ item.created_at }}
                        </td>

                        <td>
                            {{ item.centro_custo.label }}
                        </td>

                        <td>
                            {{ item.colaborador.nome }}
                        </td>

                        <td>
                            {{ item.data_saida }}
                        </td>

                        <td>
                            {{ item.qnt_dias }}
                        </td>

                        <td>
                            {{ item.data_retorno }}
                        </td>
                        <td>
                            {{ item.dias_saldo }}
                        </td>

                        <td>
                        <span v-if="item.status_aprovacao && item.data_aprovacao_rh === null">
                            <span class="text-uppercase">{{ item.status_aprovacao }}</span> em {{ item.data_aprovacao }}<br/>
                            Por: {{ item.quem_aprovou.nome }}
                        </span>
                            <span v-else-if="item.status_aprovacao && item.data_aprovacao_rh !== null">
                            <span class="text-uppercase">{{ item.resposta_rh }}</span> em {{
                                    item.data_aprovacao_rh
                                }}<br/>
                            Por: {{ item.rh_aprovacao.nome }}
                        </span>
                            <span v-else>
                            Aguardando
                        </span>
                        </td>


                        <td class="text-center">

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Aprovar"
                               v-if="!item.data_aprovacao"
                               @click.prevent="formOpen(item.id); visualizar = true; aprovando = true"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-check"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Aprova RH"
                               v-if="item.data_aprovacao && !item.data_aprovacao_rh && aprova_RH && item.status_aprovacao === 'aprovado'"
                               @click.prevent="formOpen(item.id); visualizar = true; aprovando = true; aprovacao_rh = true;"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-check"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar" v-if="editar"
                               @click.prevent="formOpen(item.id); editando = true"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Visualizar"
                               @click.prevent="formOpen(item.id); visualizar = true"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-search-plus"></i>
                            </a>

                        </td>

                    </tr>
                    </tbody>
                </table>

            </div>

        </div>


        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            :url="urlPaginacao" :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"/>
    </div>
</template>

<script>
import colaborador from "../../Colaborador";
import gestoraprovacao from "../../GestorAprovacao";

export default {
    data() {
        return {
            tituloJanela: 'Solicitacao de férias',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovacao_rh: false,
            aprova_RH: false,

            CSRF_token,

            hash: `mastertag_${parseInt((Math.random() * 999999))}`,
            caminho_gestor: `autocomplete/todos-gestores-ativos`,

            form: {

                colaborador_id: '',
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                centro_custo_id: '',
                data_saida: '',
                qnt_dias: '',
                tem_faltas: '',
                qnt_faltas: '',
                data_retorno: '',
                dias_saldo: '',
                user_id: '',
                solicitante: '',
                status: '',
                obs: '',

                obs_aprovacao: '',
                status_aprovacao: '',

                obs_rh: '',
                resposta_rh: '',
                data_aprovacao_rh: '',

            },

            formDefault: null,
            lista: [],
            centro_custos: [],

            /**
             *
             * aprovaRH -> apenas para mostrar o formulário
             * aprova_RH -> permissão
             *
             * **/

            aprova: false,
            editar: false,
            aprovaRH: false,

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    filtroPeriodo: false,
                    periodo: '',
                    campoBusca: "",
                    campoStatus: "",
                    pages: 50,
                },
            },
        }
    },
    components: {
        colaborador,
        gestoraprovacao,
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();
    },
    computed: {

        qntDias() {
            let total = 0;

            if (this.form.qnt_faltas <= 5) {
                total = 30;
            } else if (this.form.qnt_faltas >= 6 && this.form.qnt_faltas <= 14) {
                total = 24;
            } else if (this.form.qnt_faltas >= 15 && this.form.qnt_faltas <= 23) {
                total = 18;
            } else if (this.form.qnt_faltas >= 24 && this.form.qnt_faltas <= 32) {
                total = 12;
            } else if (this.form.qnt_faltas >= 33) {
                total = 0;
            } else {
                total = 0;
            }
            return total;
        },

        qntSaldo() {
            this.form.dias_saldo = this.qntDias - this.form.qnt_dias;

            return this.form.dias_saldo;
        }

    },
    methods: {
        /***Campos de Filtros ****/
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = '';
                this.controle.dados.autocomplete_label = '';
                this.controle.dados.campoVaga = '';
                this.$refs.componente.buscar();
            }
        },

        listaCentroCusto() {
            axios.post(`${URL_PUBLICO}/centro-custos/`)
                .then(res => {
                    if (this.cadastrando) {
                        this.form.centro_custo_id = '';
                        this.form.autocomplete_label_colaborador_anterior = '';
                        this.form.autocomplete_label_colaborador = '';
                        this.form.colaborador_id = '';
                    }
                    this.centro_custos = res.data.centro_custos;
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        formNovo() {
            this.cadastrando = true;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;
            this.visualizar = false;
            this.tituloJanela = "Solicitação de férias";

            formReset();
            setupCampo();
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.centro_custo_id = '';
            this.listaCentroCusto();
        },

        cadastrar() {

            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio');
                this.resetaCampoColaborador();
                return false;
            }
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1);
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo GESTOR não pode ficar vazio');
                this.resetaCampoGestor();
                return false;
            }

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista`, this.form)
                .then(response => {
                    $(`#${this.hash} `).modal('hide');
                    let data = response.data;
                    mostraSucesso('', 'Solicitação registrada com sucesso!');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        formOpen(id) {
            Object.assign(this.form, this.formDefault);
            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.cadastrando = false;
            this.visualizar = false;
            this.editando = false;
            this.aprovando = false;
            this.aprovacao_rh = false;
            this.aprovaRH = false;

            this.tituloJanela = `#${id}`;

            formReset();
            this.preload = true;

            axios.get(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.listaCentroCusto();
                    this.form.centro_custo_id = data.centro_custo_id;

                    this.tituloJanela = `#${id} Solicitação de férias`;
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao;
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao;
                    }
                    if (this.aprovando && this.aprovacao_rh) {
                        this.form.resposta_rh = data.resposta_rh === null ? '' : data.resposta_rh;
                        this.form.obs_rh = data.resposta_rh === null ? '' : data.obs_rh;
                        this.aprovaRH = true;
                    }
                    if (this.form.data_aprovacao_rh !== null) {
                        this.aprovaRH = true;
                        this.aprovacao_rh = false;
                    }

                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        alterar() {

            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio');
                this.resetaCampoColaborador();
                return false;
            }
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1);
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo GESTOR não pode ficar vazio');
                this.resetaCampoGestor();
                return false;
            }

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}`, this.form)
                .then(response => {
                    $(`#${this.hash} `).modal('hide');
                    let data = response.data;
                    mostraSucesso('', 'Solicitação alterada com sucesso!');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        aprovar() {

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;
            axios.put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovar`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Registro salvo com sucesso!');
                    $(`#${this.hash} `).modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        aprovarRH() {

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;
            axios.put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovarRH`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Registro salvo com sucesso!');
                    $(`#${this.hash} `).modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.aprova = dados.aprovar_por_gestor;
            this.aprova_RH = dados.aprovar_por_rh
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },
    }
}
</script>

<style scoped>

</style>
