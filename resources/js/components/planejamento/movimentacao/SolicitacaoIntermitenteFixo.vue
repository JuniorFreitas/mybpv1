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

                            <div class="col-12 col-md-6">
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

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Colaborador </label>
                                    <autocomplete :caminho="`autocomplete/colaboradorIntermitente`"
                                                  :formsm="false"
                                                  :valido="form.colaborador_id !== ''"
                                                  v-model="form.autocomplete_label_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  :disabled="visualizar || editando"
                                                  :id="`colaborador_${hash}`"
                                                  @onblur="resetaCampoColaborador"
                                                  @onselect="selecionaColaborador"></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Cargo Anterior</label>
                                    <autocomplete :caminho="`autocomplete/cargosEmpresa`"
                                                  :formsm="false"
                                                  :valido="form.cargo_anterior_id !== ''"
                                                  v-model="form.autocomplete_label_cargoanterior"
                                                  placeholder="Selecione um cargo"
                                                  :disabled="true"
                                                  :id="`cargo_anterior_${hash}`"
                                                  @onblur="resetaCampoCargoAnterior"
                                                  @onselect="selecionaCargoAnterior"></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Salário Anterior</label>
                                    <input type="text" class="form-control" v-mascara:dinheiro
                                           onblur="valida_dinheiro(this)"
                                           :disabled="visualizar"
                                           v-model="form.salario_anterior_format">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Novo Cargo</label>
                                    <autocomplete :caminho="`autocomplete/cargosEmpresa`"
                                                  :formsm="false"
                                                  :valido="form.novo_cargo_id !== ''"
                                                  v-model="form.autocomplete_label_novo_cargo"
                                                  placeholder="Selecione um cargo"
                                                  :disabled="visualizar || editando"
                                                  :id="`novo_cargo_${hash}`"
                                                  @onblur="resetaCampoNovoCargo"
                                                  @onselect="selecionaNovoCargo"></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Novo Salário</label>
                                    <input type="text" class="form-control" v-mascara:dinheiro
                                           onblur="valida_dinheiro(this)"
                                           :disabled="visualizar"
                                           v-model="form.novo_salario_format">
                                </div>
                            </div>

                            <gestoraprovacao :model="form" :verifica="visualizar" :hash="hash"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Motivos para modificação</label>
                                    <textarea class="form-control" v-model="form.motivos" cols="5" rows="5"
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
            </template>
        </modal>

        <modal id="janelaAtualizaStatus" titulo="Deseja APROVAR ou REPROVAR todos os colaboradores selecionados?"
               :centralizada="true" label-fechar="Fechar">
            <template slot="conteudo">
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control"
                                  v-model="formConfirmacao.obs_aprovacao"
                                  cols="5" rows="5"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-sm btn-success" @click="confirmaAtualizacaoStatus('aprovado')">
                        APROVAR
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" @click="confirmaAtualizacaoStatus('reprovado')">
                        REPROVAR
                    </button>
                </div>
            </template>
        </modal>
        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                               :id="`filtroIntervalo_${hash}`"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" :for="`filtroIntervalo_${hash}`">Por
                            período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label=""
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

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                            :disabled="controle.carregando"
                            :data-target="`#${hash}`"
                            @click.prevent="formNovo">
                        Solicitar
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary mr-1" v-show="selecionados.length > 0"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0"
                            data-toggle="modal"
                            data-target="#janelaAtualizaStatus">
                        Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                    </button>
                </div>
            </form>
        </fieldset>

        <div class="mb-2 mt-2 pt-1 pb-1 border-bottom" v-show="!controle.carregando && lista.length > 0">
        <span class="small text-right">
                Legenda:
                <i class="fas fa-circle text-warning"></i> Aguardando
                <i class="fas fa-circle text-success ml-2"></i> Aprovado
                <i class="fas fa-circle text-danger ml-2"></i> Reprovado
            </span>
        </div>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center">
                            <input type="checkbox"
                                   :style="naoAprovados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                                   :disabled="naoAprovados.length === 0" :checked="tudoMarcado"
                                   @click="selecionaTodos">
                        </th>
                        <th>CÓD</th>
                        <th>Solicitação</th>
                        <th>Centro de custo</th>
                        <th>Colaborador</th>
                        <th>Cargo Anterior</th>
                        <th>Salário Anterior</th>
                        <th>Novo Cargo</th>
                        <th>Novo Salário</th>
                        <th>Quem cadastrou</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista"
                        :class="!item.status_aprovacao ? 'table-warning' : item.status_aprovacao === 'reprovado' ? 'table-danger' : item.status_aprovacao === 'aprovado' ? 'table-success' : null">

                        <td class="text-center">
                            <label :for="item.id">
                                <input
                                    type="checkbox"
                                    v-model="selecionados"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="!item.status_aprovacao ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    :title="item.status_aprovacao ? null : 'Não possui aprovação'"
                                    v-if="!item.status_aprovacao"
                                >
                                <input type="checkbox" v-else disabled="disabled" title="Status já atualizado">

                            </label>
                        </td>
                        <td>
                            {{ item.id }}
                        </td>

                        <td>
                            {{ item.user_cadastrou.nome }} <br> {{ item.created_at }}
                        </td>

                        <td>
                            {{ item.centro_custo.label }}
                        </td>

                        <td>
                            {{ item.colaborador ? item.colaborador.nome : '' }}
                        </td>

                        <td>
                            {{ item.cargo_anterior.nome }}
                        </td>


                        <td>
                            {{ item.salario_anterior_format }}
                        </td>
                        <td>
                            {{ item.novo_cargo.nome }}
                        </td>

                        <td>
                            {{ item.novo_salario_format }}
                        </td>

                        <td>
                        <span v-if="item.status_aprovacao !== null">
                            <span class="text-uppercase">{{ item.status_aprovacao }}</span> em {{ item.data_aprovacao }}<br/>
                            Por: {{ item.user_aprovacao.nome }}
                        </span>

                            <span v-else>
                            Aguardando
                        </span>
                        </td>


                        <td class="text-center">
                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Aprovar"
                               v-if="!item.data_aprovacao && aprovar_por_gestor"
                               @click.prevent="formOpen(item.id); visualizar = true; aprovando = true"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-check"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                               v-if="!item.data_aprovacao"
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
import gestoraprovacao from "../../GestorAprovacao";

export default {
    components: {
        gestoraprovacao,
    },
    data() {
        return {
            tituloJanela: 'Solicitação de troca de Contrato Intermitente para Fixo',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovar_por_gestor: false,

            hash: `mastertag_${parseInt((Math.random() * 999999))}`,

            colunasTabela: {
                cliente: false,
            },

            selecionados: [],
            selecionaTudo: false,

            formConfirmacao: {
                selecionados: [],
                obs_aprovacao: '',
                status_aprovacao: '',
            },

            formConfirmacaoDefault: null,
            form: {

                colaborador_id: '',
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',

                centro_custo_id: '',
                tipo_contrato: '',

                cargo_anterior_id: '',
                autocomplete_label_cargoanterior: '',
                autocomplete_label_cargoanterior_anterior: '',

                novo_cargo_id: '',
                autocomplete_label_novo_cargo: '',
                autocomplete_label_novo_cargo_anterior: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                data_admissao: '',
                salario_anterior_format: '0,00',
                novo_salario_format: '0,00',

                user_id: '',
                solicitante: '',
                status: '',
                motivos: '',

                obs_aprovacao: '',
                status_aprovacao: '',
            },

            formDefault: null,
            lista: [],
            centro_custos: [],

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    filtroPeriodo: false,
                    periodo: '',
                    campoStatus: '',
                },
            },
        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao);
    },
    computed: {
        naoAprovados() {
            return this.lista.filter(item => {
                if (item.status_aprovacao === null) {
                    return item.id
                }
            })
        },
        tudoMarcado() {
            let totalAprovado = this.naoAprovados.length
            let totalEncontrado = 0

            if (totalAprovado === 0) {
                return false
            }

            this.naoAprovados.forEach(item => {
                let id = item.id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                } else {
                    return false
                }
            })
            let resultado = totalAprovado === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.naoAprovados.map(item => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.naoAprovados.map(item => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        confirmaAtualizacaoStatus(confirmacao) {

            this.preloadAtualizacao = true;
            this.formConfirmacao.status_aprovacao = confirmacao;
            this.formConfirmacao.selecionados.push(this.selecionados)

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/atualizacao-status`, this.formConfirmacao)
                .then(res => {
                    this.preloadAtualizacao = false;
                    $('#janelaAtualizaStatus').modal('hide');
                    mostraSucesso('Status atualizados com sucesso!');
                    this.selecionados = [];
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault) //copia
                    this.$refs.componente.buscar();
                })
                .catch(error => {
                    this.preloadAtualizacao = false;
                });
        },
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

        selecionaColaborador(obj) {
            this.form.colaborador_id = obj.curriculo_id;
            this.form.autocomplete_label_colaborador = obj.label;
            this.form.autocomplete_label_colaborador_anterior = obj.label;

            //Cargo Anterior
            this.form.cargo_anterior_id = obj.vaga_id;
            this.form.autocomplete_label_cargoanterior = obj.vaga_selecionada.nome;
            this.form.autocomplete_label_cargoanterior_anterior = obj.vaga_selecionada.nome;

        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = '';
                this.form.autocomplete_label_colaborador = '';
                this.form.colaborador_id = '';

                //Cargo Anterior
                this.form.cargo_anterior_id = '';
                this.form.autocomplete_label_cargoanterior = '';
                this.form.autocomplete_label_cargoanterior_anterior = '';

                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio');
                    }
                }, 100);
            }
        },

        selecionaCargoAnterior(obj) {
            this.form.cargo_anterior_id = obj.id;
            this.form.autocomplete_label_cargoanterior = obj.label;
            this.form.autocomplete_label_cargoanterior_anterior = obj.label;
        },
        resetaCampoCargoAnterior() {
            if (this.form.autocomplete_label_cargoanterior_anterior !== this.form.autocomplete_label_cargoanterior) {
                this.form.autocomplete_label_cargoanterior_anterior = '';
                this.form.autocomplete_label_cargoanterior = '';
                this.form.cargo_anterior_id = '';

                setTimeout(() => {
                    if (this.form.cargo_anterior_id === '') {
                        valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                        $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Cargo Anterior não pode ficar vazio');
                    }
                }, 100);
            }
        },

        selecionaNovoCargo(obj) {
            this.form.novo_cargo_id = obj.id;
            this.form.autocomplete_label_novo_cargo = obj.label;
            this.form.autocomplete_label_novo_cargo_anterior = obj.label;

            setTimeout(() => {
                if (this.form.novo_cargo_id !== '' && this.form.novo_cargo_id === this.form.cargo_anterior_id) {
                    valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                    $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                    mostraErro('Erro', 'O NOVO CARGO não pode ser igual ao CARGO ANTERIOR');
                    this.form.novo_cargo_id = '';
                    this.form.autocomplete_label_novo_cargo = '';
                    this.form.autocomplete_label_novo_cargo_anterior = '';
                }
            }, 100);
        },
        resetaCampoNovoCargo() {
            if (this.form.autocomplete_label_novo_cargo_anterior !== this.form.autocomplete_label_novo_cargo) {
                this.form.autocomplete_label_novo_cargo_anterior = '';
                this.form.autocomplete_label_novo_cargo = '';
                this.form.novo_cargo_id = '';

                setTimeout(() => {
                    if (this.form.novo_cargo_id === '') {
                        valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1);
                        $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Novo Cargo não pode ficar vazio');
                    }
                }, 100);
            }
        },

        listaCentroCusto() {
            axios.post(`${URL_PUBLICO}/centro-custos/`)
                .then(res => {
                    this.centro_custos = res.data.centro_custos;
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;
            this.visualizar = false;

            this.tituloJanela = "Solicitação de troca de Contrato Intermitente para Fixo";

            formReset();
            setupCampo();
            this.form = _.cloneDeep(this.formDefault) //copia
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
            if (this.form.novo_cargo_id === '') {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1);
                $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio');
                this.resetaCampoNovoCargo();
                return false;
            }
            if (this.form.cargo_anterior_id === '') {
                valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo CARGO ANTERIOR não pode ficar vazio');
                this.resetaCampoCargoAnterior();
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

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista`, this.form)
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

            this.tituloJanela = `#${id}`;

            formReset();
            this.preload = true;

            axios.get(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.listaCentroCusto();
                    this.form.centro_custo_id = data.centro_custo_id;

                    this.tituloJanela = `#${id} Solicitação de troca de Contrato Intermitente para Fixo`;
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao;
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao;
                    }
                    this.editando = true;

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

            if (this.form.novo_cargo_id === '') {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1);
                $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio');
                this.resetaCampoNovoCargo();
                return false;
            }
            if (this.form.cargo_anterior_id === '') {
                valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo CARGO ANTERIOR não pode ficar vazio');
                this.resetaCampoCargoAnterior();
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

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${this.form.id}`, this.form)
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
            axios.put(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${this.form.id}/aprovar`, this.form)
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
            this.aprovar_por_gestor = dados.aprovar_por_gestor;
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
