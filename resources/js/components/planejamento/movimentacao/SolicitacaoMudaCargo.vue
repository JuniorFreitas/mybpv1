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
                            <div class="col-12 col-md-6" v-if="cliente_id === 0">
                                <div class="form-group">
                                    <label>Selecione um cliente</label>
                                    <autocomplete :formsm="false" :caminho="controle.dados.caminho_cliente_autocomplete"
                                                  :disabled="visualizar || editando"
                                                  :valido="form.cliente_id !== ''"
                                                  v-model="form.autocomplete_label_cliente_modal"
                                                  :id="`cliente_modal_${hash}`"
                                                  placeholder="Digite o nome cliente"
                                                  @onblur="resetaCampoClienteModal"
                                                  @onselect="selecionaClienteModal"></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Centro de Custo</label>
                                    <select v-model="form.centro_custo_id" class="form-control"
                                            :disabled="visualizar || form.cliente_id === ''"
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
                                    <autocomplete :caminho="`autocomplete/colaboradores/${form.cliente_id}`"
                                                  :formsm="false"
                                                  :valido="form.colaborador_id !== ''"
                                                  v-model="form.autocomplete_label_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  :disabled="visualizar || form.cliente_id === '' || editando"
                                                  :id="`colaborador_${hash}`"
                                                  @onblur="resetaCampoColaborador"
                                                  @onselect="selecionaColaborador"></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Cargo Anterior</label>
                                    <autocomplete :caminho="`autocomplete/cargosEmpresa/${form.cliente_id}`"
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
                                           :disabled="visualizar || form.cliente_id === ''"
                                           v-model="form.salario_anterior_format">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Novo Cargo</label>
                                    <autocomplete :caminho="`autocomplete/cargosEmpresa/${form.cliente_id}`"
                                                  :formsm="false"
                                                  :valido="form.novo_cargo_id !== ''"
                                                  v-model="form.autocomplete_label_novo_cargo"
                                                  placeholder="Selecione um cargo"
                                                  :disabled="visualizar || form.cliente_id === '' || editando"
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
                                           :disabled="visualizar || form.cliente_id === ''"
                                           v-model="form.novo_salario_format">
                                </div>
                            </div>


<!--                            <div class="col-12 col-md-4">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Solicitante</label>-->
<!--                                    <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"-->
<!--                                           :disabled="visualizar || form.cliente_id === ''  || editando"-->
<!--                                           v-model="form.solicitante">-->
<!--                                </div>-->
<!--                            </div>-->

                            <!--                            <div class="col-12">-->
                            <!--                                <div class="form-group">-->
                            <!--                                    <label>Status</label>-->
                            <!--                                    <select v-model="form.status" class="form-control"-->
                            <!--                                            :disabled="visualizar"-->
                            <!--                                            onchange="valida_campo_vazio(this,1)"-->
                            <!--                                            onblur="valida_campo_vazio(this,1)">-->
                            <!--                                        <option value="">Selecione</option>-->
                            <!--                                        <option value="Cancelado">Cancelado</option>-->
                            <!--                                        <option value="Concluido">Concluido</option>-->
                            <!--                                    </select>-->
                            <!--                                </div>-->
                            <!--                            </div>-->

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" v-model="form.obs" cols="5" rows="5"
                                              :disabled="visualizar || form.cliente_id === ''"></textarea>
                                </div>
                            </div>
                        </div>
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
                        <th v-if="cliente_id === 0">Cliente</th>
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
                    <tr v-for="item in lista">
                        <td>
                            {{ item.id }}
                        </td>

                        <td>
                            {{ item.created_at }}
                        </td>

                        <td v-if="cliente_id === 0">
                            {{ item.cliente.razao_social }}
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
                            {{ item.user_cadastrou.nome }}
                        </td>


                        <td class="text-center">
                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                               @click.prevent="formOpen(item.id); editando = true"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
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
export default {
    props: {
        cliente_id: {
            type: Number | String,
            required: true,
            default: ''
        }
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

            hash: `mastertag_${parseInt((Math.random() * 999999))}`,

            colunasTabela: {
                cliente: false,
            },

            form: {
                cliente_id: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

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

                data_admissao: '',
                salario_anterior_format: '0,00',
                novo_salario_format: '0,00',

                user_id: '',
                solicitante: '',
                status: '',
                obs: '',
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
                    campoCliente: ''
                },
            },
        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form) //copia
        this.colunasTabela.cliente = this.cliente_id === 0;
        this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente;
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

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id;
            this.controle.dados.autocomplete_label_cliente = obj.label;
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label;
            this.controle.carregando = true;
            setTimeout(() => {
                this.$refs.componente.buscar();
            }, 600);
        },
        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = '';
                this.controle.dados.autocomplete_label_cliente = '';
                this.controle.dados.campoCliente = '';
                this.$refs.componente.buscar();
            }
        },

        selecionaClienteModal(obj) {
            this.form.cliente_id = obj.id;
            this.form.autocomplete_label_cliente_modal = obj.label;
            this.form.autocomplete_label_cliente_modal_anterior = obj.label;

            //reseta Colaborador
            this.form.autocomplete_label_colaborador_anterior = '';
            this.form.autocomplete_label_colaborador = '';
            this.form.colaborador_id = '';

            //Cargo Anterior
            this.form.cargo_anterior_id = '';
            this.form.autocomplete_label_cargoanterior = '';
            this.form.autocomplete_label_cargoanterior_anterior = '';

            setTimeout(() => {
                this.listaCentroCusto();
                this.form.centro_custo_id = '';
            }, 100);
        },
        resetaCampoClienteModal() {
            if (this.form.autocomplete_label_cliente_modal_anterior !== this.form.autocomplete_label_cliente_modal) {
                this.form.autocomplete_label_cliente_modal_anterior = '';
                this.form.autocomplete_label_cliente_modal = '';
                this.form.cliente_id = '';
                setTimeout(() => {
                    if (this.form.cliente_id === '') {
                        valida_campo_vazio($('#cliente_modal_' + this.hash), 1);
                        $('#janelaCadastrar #cliente_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Cliente não pode ficar vazio');
                    }
                }, 100);
                //reseta Colaborador
                this.form.autocomplete_label_colaborador_anterior = '';
                this.form.autocomplete_label_colaborador = '';
                this.form.colaborador_id = '';

                //Cargo Anterior
                this.form.cargo_anterior_id = '';
                this.form.autocomplete_label_cargoanterior = '';
                this.form.autocomplete_label_cargoanterior_anterior = '';

                setTimeout(() => {
                    this.listaCentroCusto();
                    this.form.centro_custo_id = '';
                }, 100);
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
            axios.post(`${URL_PUBLICO}/centro-custos/`, {'cliente_id': this.form.cliente_id})
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
            this.tituloJanela = "Solicitação de Mudança de Cargo";

            formReset();
            setupCampo();
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.cliente_id = this.cliente_id === 0 ? this.form.cliente_id : this.cliente_id;
            this.listaCentroCusto();
        },

        cadastrar() {
            if (this.form.cliente_id === '') {
                valida_campo_vazio($(`#cliente_modal_${this.hash}`), 1);
                $(`#${this.hash} #cliente_modal_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo CLIENTE não pode ficar vazio');
                this.resetaCampoClienteModal();
                return false;
            }

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
                    this.editando = true;

                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        alterar() {
            if (this.form.cliente_id === '') {
                valida_campo_vazio($(`#cliente_modal_${this.hash}`), 1);
                $(`#${this.hash} #cliente_modal_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo CLIENTE não pode ficar vazio');
                this.resetaCampoClienteModal();
                return false;
            }

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

        carregou(dados) {
            this.lista = dados.itens;
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
