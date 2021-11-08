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
                                            :disabled="visualizar "
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
                                    <autocomplete :caminho="`autocomplete/colaboradores`"
                                                  :formsm="false"
                                                  :valido="form.colaborador_id !== ''"
                                                  v-model="form.autocomplete_label_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  :disabled="visualizar  || editando"
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
                                           onblur="valida_dinheiro(this,1)"
                                           :disabled="visualizar "
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
                                                  :disabled="visualizar  || editando"
                                                  :id="`novo_cargo_${hash}`"
                                                  @onblur="resetaCampoNovoCargo"
                                                  @onselect="selecionaNovoCargo"></autocomplete>
                                </div>
                            </div>


                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Novo Salário</label>
                                    <input type="text" class="form-control" v-mascara:dinheiro
                                           onblur="valida_dinheiro(this,1)"
                                           :disabled="visualizar "
                                           v-model="form.novo_salario_format">
                                </div>
                            </div>

                            <gestoraprovacao :model="form" :verifica="visualizar" :hash="hash"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" v-model="form.obs" cols="5" rows="5"
                                              :disabled="visualizar "></textarea>
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
                            v-show="editando && !atualizado  && !preload && form.cliente_id !== ''"
                            @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="!editando && !cadastrado  && !preload && form.cliente_id !== ''"
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
                               class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoBusca">
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
                        <th>CÓD</th>
                        <th>Solicitação</th>
                        <th>Centro de custo</th>
                        <th>Colaborador</th>
                        <th>Cargo Anterior</th>
                        <th>Salário Anterior</th>
                        <th>Novo Cargo</th>
                        <th>Novo Salário</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista"
                        :class="!item.status_aprovacao ? 'table-warning' : item.status_aprovacao === 'reprovado' ? 'table-danger' : item.status_aprovacao === 'aprovado' ? 'table-success' : null">
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
                            Por: {{ item.gestor_aprovacao.nome }}
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
import colaborador from "../../Colaborador";
import gestoraprovacao from "../../GestorAprovacao";

export default {
    components: {
        colaborador,
        gestoraprovacao,
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de Mudança de Cargo',
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
                obs: '',

                obs_aprovacao: '',
                status_aprovacao: '',
            },

            formDefault: null,
            lista: [],
            centro_custos: [],

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/muda-cargo-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
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

            this.tituloJanela = "Solicitação de Mudança de Cargo";

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

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/muda-cargo-prevista`, this.form)
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

            axios.get(`${URL_ADMIN}/planejamento/movimentacao/muda-cargo-prevista/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.listaCentroCusto();
                    this.form.centro_custo_id = data.centro_custo_id;

                    this.tituloJanela = `#${id} Solicitação de Mudança de Cargo`;
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

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/muda-cargo-prevista/${this.form.id}`, this.form)
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
            axios.put(`${URL_ADMIN}/planejamento/movimentacao/muda-cargo-prevista/${this.form.id}/aprovar`, this.form)
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
