<template>
    <div>
        <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
            <template slot="conteudo">
                <preload label="Aguarde..." v-show="preloadAjax"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado || atualizado">
                    <h4><i class="icon fa fa-check"></i>Ocorrrência {{ cadastrado ? "cadastrada" : "atualizada" }} com
                        sucesso!</h4>
                </div>
                <form v-if="!preloadAjax && !cadastrado && !atualizado" id="form" onsubmit="return false;">
                    <fieldset>
                        <legend>Lançamento</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Data da Ocorrência</label>
                                    <date-picker
                                        label=""
                                        :disabled="aprovando"
                                        v-model="form.data_lancamento"
                                        style="margin-top: -19px"
                                        :max="hoje"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select
                                        :disabled="aprovando"
                                        v-model="form.tag_id"
                                        class="form-control validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                    >
                                        <option value="">Selecione...</option>
                                        <option v-for="item in listaTags" :value="item.id" :key="item.id"
                                                v-text="item.label"></option>
                                        <option :value="0">Outro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.tag_id === 0">
                                <div class="form-group">
                                    <label>Especifique</label>
                                    <input
                                        type="text"
                                        class="form-control validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        :disabled="aprovando"
                                        v-model="form.outra_tag"
                                    />
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Área</label>
                                    <select
                                        :disabled="aprovando"
                                        v-model="form.area_id"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        class="form-control validacampo"
                                    >
                                        <option value="">Selecione...</option>
                                        <option v-for="item in listaAreas" :value="item.id" :key="item.id"
                                                v-text="item.label"></option>
                                        <option :value="0">Outra</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.area_id === 0">
                                <div class="form-group">
                                    <label>Especifique</label>
                                    <input
                                        type="text"
                                        class="form-control validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                        :disabled="aprovando"
                                        v-model="form.outra_area"
                                    />
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Colaborador(es)</label>
                                    <autocomplete
                                        :caminho="colaborador_ativo"
                                        :formsm="false"
                                        :valido="form.feedback_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="aprovando"
                                        :id="`colaborador_${hash}`"
                                        @onselect="selecionaColaborador"
                                        v-if="!editando"
                                    ></autocomplete>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-condensed bg-white"
                                           v-if="form.colaboradores.length">
                                        <thead>
                                        <tr class="bg-default">
                                            <th class="text-center" width="50%">Nome</th>
                                            <th class="text-center" width="40%">Cargo</th>
                                            <th class="text-center" v-if="!editando">Remover</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(colaborador, index) in form.colaboradores">
                                            <td class="text-center">{{ colaborador.curriculo.nome }}</td>
                                            <td class="text-center">{{ colaborador.vaga_aberta.vaga.nome }}</td>
                                            <td class="text-center" v-if="!editando">
                                                <a href="javascript://" class="btn btn-sm btn-danger"
                                                   @click.prevent="removerLIColaborador(index)">
                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Ação</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :disabled="aprovando"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                        v-model="form.acao"
                                    />
                                </div>
                            </div>

                            <div class="col-12">
                                <fieldset>
                                    <legend>Anexo (Evidência)</legend>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :leitura="form.id ? true : false"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="anexoUploadAndamento = false"
                                    ></upload>
                                </fieldset>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" :disabled="aprovando" v-model="form.obs_lancamento"
                                              cols="5" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="aprovando">
                        <legend>Aprovação</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" :disabled="form.data_aprovacao"
                                              v-model="form.obs_aprovacao" cols="5" rows="5"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select
                                        :disabled="form.data_aprovacao"
                                        v-model="form.status"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        class="form-control validacampo"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovar</option>
                                        <option value="reprovado">Reprovar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !leitura && form.status !== '' && !atualizado && !preloadAjax"
                        @click="aprovar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!aprovando && !cadastrado && !preloadAjax" @click="cadastrar">
                    <i class="fa fa-save"></i> Lançar
                </button>
            </template>
        </modal>

        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                               @change.prevent="atualizar()"
                               id="filtroIntervalo"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label=""
                                    @onselect="atualizar()"
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                                @change="atualizar()" :disabled="controle.carregando">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Aberto</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select v-model="controle.dados.campoTags" :disabled="controle.carregando" @change="atualizar()"
                                class="form-control form-control-sm">
                            <option value="">Todos os tipos</option>
                            <option v-for="item in listaTags" :value="item.id" :key="item.id"
                                    v-text="item.label"></option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Área</label>
                        <select v-model="controle.dados.campoAreas" :disabled="controle.carregando"
                                @change="atualizar()" class="form-control form-control-sm">
                            <option value="">Todas as áreas</option>
                            <option v-for="item in listaAreas" :value="item.id" :key="item.id"
                                    v-text="item.label"></option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar()">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                        <span>{{ controle.carregando ? "Buscando..." : "Buscar" }}</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-toggle="modal"
                        :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click="formNovo()"
                        v-if="permissoes.admissao_cih_lancar"
                    >
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>

                    <form method="post" style="display: contents" target="_blank"
                          :action="urlPdf">
                        <input type="hidden" name="_token" :value="csrf">
                        <input type="hidden" name="filtroPeriodo" :value="controle.dados.filtroPeriodo">
                        <input type="hidden" name="periodo" :value="controle.dados.periodo">
                        <input type="hidden" name="campoStatus" :value="controle.dados.campoStatus">
                        <input type="hidden" name="campoTags" :value="controle.dados.campoTags">
                        <input type="hidden" name="campoAreas" :value="controle.dados.campoAreas">
                        <button class="btn btn-sm btn-primary" :disabled="controle.carregando"><i
                            class="fas fa-file-pdf"></i> Gerar PDF
                        </button>
                    </form>

                    <button
                        type="button"
                        class="btn btn-sm btn-primary mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length && !selecionados.length)"
                    >
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                        <span class="badge badge-light" v-show="selecionados.length"
                              v-text="selecionados.length"></span>
                    </button>
                </div>
            </form>
        </fieldset>

        <div class="col-12 mb-2 mt-2 pt-1 pb-1 border-bottom">
            <p>
                Legenda:
                <i class="fas fa-circle text-warning"></i> Aberto <i class="fas fa-circle text-success ml-2"></i>
                Aprovado
                <i class="fas fa-circle text-danger ml-2"></i> Reprovado
            </p>
        </div>

        <preload v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && !lista.length">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div v-show="!controle.carregando && lista.length">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center">ID</th>
                        <th>Colaborador</th>
                        <th>Tipo</th>
                        <th class="text-center">Data Ocorrência</th>
                        <th class="text-center">Lançamento</th>
                        <th class="text-center">Aprovação</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="item in lista"
                        :key="item.id"
                        :class="{
                                'table-warning': item.status.includes('aberto'),
                                'table-danger': item.status.includes('reprovado'),
                                'table-success': item.status.includes('aprovado')
                            }"
                    >
                        <td class="text-center" v-text="item.id"></td>
                        <td>{{ item.varios_colaboradores ? "Varios colaboradores" : item.colaboradores[0].curriculo.nome
                            }}
                        </td>
                        <td class="text-center">
                            {{ item.tag ? item.tag.label : item.outra_tag }}
                        </td>
                        <td class="text-center" v-text="item.data_lancamento"></td>
                        <td class="text-center">
                            Lançado por {{ item.responsavel_lancamento.nome }}<br />
                            em {{ item.created_at }}
                        </td>
                        <td class="text-center">
                            <template v-if="item.status.includes('aprovado') || item.status.includes('reprovado')">
                                {{ item.status | capitalize() }} por {{ item.responsavel_aprovacao.nome }} <br />
                                em {{ item.updated_at }}
                            </template>
                        </td>
                        <td class="text-center">{{ item.status | capitalize() }}</td>
                        <td class="text-center">
                            <a
                                v-if="permissoes.admissao_cih_aprovar"
                                v-show="item.status.includes('aberto')"
                                href="javascript://"
                                class="btn btn-sm btn-primary"
                                content="Aprovar/Reprovar"
                                v-tippy
                                @click.prevent="formAprovar(item.id); leitura = false"
                                data-toggle="modal"
                                data-target="#janelaCadastrar"
                            >
                                <i class="fa fa-check"></i>
                            </a>

                            <a
                                v-show="item.status.includes('aprovado') || item.status.includes('reprovado')"
                                href="javascript://"
                                class="btn btn-sm btn-primary"
                                content="Visualizar"
                                v-tippy
                                @click.prevent="formAprovar(item.id); leitura = true"
                                data-toggle="modal"
                                data-target="#janelaCadastrar"
                            >
                                <i class="fa fa-search"></i>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            >
            </controle-paginacao>
        </div>
    </div>
</template>
<script>
import autocomplete from "../../AutoComplete";
import DatePicker from "../../DatePicker";
import Upload from "../../Upload";
import ControlePaginacao from "../../ControlePaginacao";
import ExportacaoMixin from "../../../mixins/Exportacoes";
import Validacoes from "../../../mixins/Validacoes";

export default {
    name: "CIH",
    components: {
        autocomplete,
        DatePicker,
        Upload,
        ControlePaginacao
    },
    mixins: [ExportacaoMixin, Validacoes],
    filters: {
        capitalize(value) {
            if (!value) return "";
            value = value.toString();
            return value.charAt(0).toUpperCase() + value.slice(1);
        }
    },
    data() {
        return {
            tituloJanela: "Cadastrando CIH",
            preloadAjax: false,
            editando: false,
            leitura: false,
            apagado: false,
            aprovando: false,
            preloadExportacao: false,

            csrf: CSRF_token,

            colaborador_ativo: `autocomplete/colaboradorCih`,
            todos_municipios: `autocomplete/todos-municipios`,

            urlPdf: `${URL_ADMIN}/apontamento/cih/gerapdf`,
            urlExportacao: `${URL_ADMIN}/apontamento/cih/export`,
            urlAtualizar: `${URL_ADMIN}/apontamento/cih/atualizar`,
            selecionados: [],

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            datarelatorio: "",
            tipoRelatorio: "pdf",
            cliente_relatorio: "",

            hoje: "",

            permissoes: {
                admissao_cih_lancar: false,
                admissao_cih_aprovar: false
            },

            form: {
                tag_id: "",
                outra_tag: "",
                feedback_id: "",
                colaboradores: [],
                colaboradoresDelete: [],
                autocomplete_label_colaborador: "",
                autocomplete_label_colaborador_anterior: "",
                cliente_id: "",
                area_id: "",
                varios_colaboradores: false,
                colaboradores_avulso: "",
                outra_area: "",
                acao: "",
                user_lancamento_id: "",
                obs_lancamento: "",
                data_lancamento: "",
                user_aprovacao_id: "",
                obs_aprovacao: "",
                data_aprovacao: "",
                status: "",
                status_aprovacao: "",
                anexos: [],
                anexosDel: []
            },

            url_anexo: `${URL_ADMIN}/apontamento/cih/uploadAnexos`,
            anexoUploadAndamento: false,

            formDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            listaTags: [],
            listaAreas: [],
            listaClientes: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    campoStatus: "",
                    campoTags: "",
                    campoAreas: "",
                    filtroPeriodo: false,
                    periodo: "",
                    pages: 50
                }
            }
        };
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.atualizar();
        let inicio_de_mes = moment().startOf("month").format("DD/MM/YYYY");
        let fim_de_mes = moment().add(1, "M").endOf("month").format("DD/MM/YYYY");
        this.controle.dados.periodo = `${inicio_de_mes} até ${fim_de_mes}`;
    },
    computed: {
        // tudoMarcado() {
        //     let totalItens = this.comTeste.length;
        //     let totalEncontrado = 0;
        //
        //     if (totalItens === 0) {
        //         return false;
        //     }
        //
        //     this.comTeste.forEach(item => {
        //         let id = item.curriculo_id;
        //         if (this.selecionados.indexOf(id) >= 0) {
        //             totalEncontrado++;
        //             //faz nada
        //         } else {
        //             return false;
        //         }
        //     });
        //     let resultado = totalItens === totalEncontrado;
        //     this.selecionaTudo = resultado;
        //     return resultado;
        // }
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comTeste.map((item) => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id);
                    }
                });
            } else {
                this.comTeste.map((item) => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1);
                    }
                });
            }
        },
        removerLIColaborador(index) {
            if (!this.form.colaboradores[index].novo) {
                this.form.colaboradoresDelete.push(this.form.colaboradores[index].id);
            }
            this.form.colaboradores.splice(index, 1);
        },
        selecionaColaborador(obj) {
            // this.form.feedback_id = obj.id;
            // this.form.cliente_id = obj.cliente_id;
            // this.form.autocomplete_label_colaborador = obj.label;
            // this.form.autocomplete_label_colaborador_anterior = obj.label;

            const colaborador = {};

            Object.assign(colaborador, obj);
            colaborador.novo = true;


            let atual = this.form.colaboradores.findIndex(val => val.id === colaborador.id);

            if (atual < 0) {//Se não existir ainda no array
                this.form.colaboradores.push(colaborador);
            } else {
                mostraErro("", `O colaborador(a) ${colaborador.nome} já está na lista.`);
                this.form.autocomplete_label_colaborador = "";
                return false;
            }
            this.form.autocomplete_label_colaborador = "";
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = "";
                this.form.autocomplete_label_colaborador = "";
                this.form.feedback_id = "";
                this.form.cliente_id = "";

                setTimeout(() => {
                    if (this.form.feedback_id === "") {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        // $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                        $(`#janelaCadastrar #colaborador_${this.hash}`).focus().trigger("blur");
                        mostraErro("Erro", "O Campo Vaga não pode ficar vazio");
                    }
                }, 100);
            }
        },

        formNovo() {
            formReset();
            setupCampo();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;
            this.tituloJanela = "Cadastrando CIH";
            this.form = _.cloneDeep(this.formDefault); //copia
            this.form.status = "aberto";
        },
        cadastrar() {
            formReset();
            this.validaBlur();
            this.$nextTick(() => {
                $("#janelaCadastrar :input:enabled").trigger("blur");
                if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                    this.mostraErro("", "Existem campos obrigatórios não preenchidos");
                    return false;
                }
                if (!this.form.colaboradores) {
                    this.mostraErro("", "Adcione o colaborador");
                    return false;
                }

                let tag_selecionada = this.form.tag_id !== 0 ? this.listaTags.find((item) => item.id === this.form.tag_id) : 0;
                if (tag_selecionada.anexo_obrigatorio) {
                    if (this.form.anexos.length === 0) {
                        this.mostraErro("", "O Campo Anexo não pode ficar vazio");
                        return false;
                    }
                }
                this.preloadAjax = true;
                this.form.status = "aberto";

                axios
                    .post(`${URL_ADMIN}/apontamento/cih`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            $("#janelaCadastrar").modal("hide");
                            this.mostraSucesso("", "Ocorrência cadastrada com sucesso");
                            this.preloadAjax = false;
                            this.cadastrado = true;
                            this.atualizar();
                        }
                    })
                    .catch((error) => (this.preloadAjax = false));
            });
        },
        formAlterar(id) {
            formReset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Alterando CIH #${id}`;
            this.preloadAjax = true;

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data);
                    // this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                })
                .catch((error) => (this.preloadAjax = false));
        },
        alterar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;

            axios
                .put(`${URL_ADMIN}/apontamento/cih/${this.form.id}`, this.form)
                .then((response) => {
                    $("#janelaCadastrar").modal("hide");
                    mostraSucesso("", "Ocorrência alterada com sucesso!");
                    this.preloadAjax = false;
                    this.atualizado = true;
                    this.atualizar();
                })
                .catch((error) => (this.preloadAjax = false));
        },

        formAprovar(id) {
            formReset();
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Aprovando CIH #${id}`;
            this.preloadAjax = true;

            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data);
                    this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                })
                .catch((error) => (this.preloadAjax = false));
        },
        aprovar() {
            formReset();
            $("#janelaCadastrar :input:enabled").trigger("blur");
            if ($("#janelaCadastrar :input:enabled.is-invalid").length) {
                mostraErro("", "Verificar os erros");
                return false;
            }

            this.form._method = "PUT";
            this.preloadAjax = true;
            axios
                .put(`${URL_ADMIN}/apontamento/cih/aprovar/${this.form.id}`, this.form)
                .then((response) => {
                    $("#janelaCadastrar").modal("hide");
                    mostraSucesso("", "Ocorrência alterada com sucesso!");
                    this.preloadAjax = false;
                    this.atualizado = true;
                    this.atualizar();
                })
                .catch((error) => (this.preloadAjax = false));
        },

        gerarPdf() {
            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/apontamento/cih/gerapdf`).then((response) => {
                this.preloadAjax = false;
                window.open(response.data.url, "_blank");
            }).catch((error) => (this.preloadAjax = false));
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.listaTags = dados.tags;
            this.listaAreas = dados.areas;
            this.datarelatorio = dados.intervalo;
            this.hoje = dados.hoje;
            this.permissoes = dados.permissoes;
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
