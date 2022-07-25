<template>
    <div>
        <preload class="mt-2" v-if="preload"></preload>

        <modal :id="hash" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
            <template slot="conteudo">
                <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">
                    <fieldset>
                        <legend>Lançamento</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Data da Ocorrência</label>
                                    <date-picker :disabled="aprovando" v-model="form.data_lancamento"></date-picker>
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                           :disabled="aprovando" v-for="item in lista" v-model="item.tag.label">
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.tag_id === 0">
                                <div class="form-group">
                                    <label>Especifique</label>
                                    <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                           :disabled="aprovando" v-model="form.outra_tag">
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Área</label>
                                    <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                           :disabled="aprovando" v-for="item in lista" v-model="item.area.label">
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.area_id === 0">
                                <div class="form-group">
                                    <label>Especifique</label>
                                    <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                           :disabled="aprovando" v-model="form.outra_area">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Colaborador </label>
                                    <autocomplete :caminho="colaborador_ativo"
                                                  :valido="form.feedback_id !== ''"
                                                  v-model="form.autocomplete_label_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  :disabled="aprovando"
                                                  :id="`colaborador_${hash}`"
                                                  onblur="resetaCampoColaborador"
                                                  onSelect="selecionaColaborador"></autocomplete>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Ação</label>
                                    <input type="text" class="form-control" :disabled="aprovando"
                                           onblur="valida_campo_vazio(this,1)" v-model="form.acao">
                                </div>
                            </div>

                            <div class="col-12">
                                <fieldset>
                                    <legend>Anexo (Evidência)</legend>
                                    <upload :model="form.anexos"
                                            :model-delete="form.anexosDel"
                                            :leitura="form.id ? true : false"
                                            :url="url_anexo"
                                            label="Selecionar"
                                            onProgresso="anexoUploadAndamento=true"
                                            onFinalizado="anexoUploadAndamento=false"></upload>
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
                                              v-model="form.obs_aprovacao"
                                              cols="5" rows="5"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select :disabled="form.data_aprovacao" v-model="form.status_aprovacao"
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
                </form>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">

            <div class="col-12 mb-2 mt-2 pt-1 pb-1 border-bottom">
                <span class="small">
                    Legenda:
                    <i class="fas fa-circle text-warning"></i> Aberto
                    <i class="fas fa-circle text-success ml-2"></i> Aprovado
                    <i class="fas fa-circle text-danger ml-2"></i> Reprovado
                </span>
            </div>

            <div id="conteudo">
                <div class="alert alert-warning" v-show="lista.length===0">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                </div>


                <div class="table-responsive" v-show=" lista.length > 0">
                    <table class="table table-bordered table-hover table-condensed" style="font-size: 0.85em;">
                        <thead>
                        <tr class="bg-default">
                            <th class="text-center">ID</th>
                            <th>Colaborador</th>
                            <th class="text-center">Lançamento</th>
                            <th class="text-center">Aprovação</th>
                            <th class="text-center">Status</th>
                            <th colspan="3"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in lista" :class="{
                            'table-warning': item.status.includes('aberto'),
                            'table-danger': item.status.includes('reprovado'),
                            'table-success': item.status.includes('aprovado'),
                        }">
                            <td class="text-center">
                                {{ item.id }}
                            </td>

                            <td>
                                {{ item.colaborador.curriculo.nome }}
                            </td>

                            <td class="text-center">
                                Lançado por {{ item.responsavel_lancamento.nome }} <br>
                                em {{ item.data_lancamento }}
                            </td>

                            <td class="text-center">
                                <span v-if="item.status.includes('aprovado') || item.status.includes('reprovado')">
                                    {{ item.status | capitalize() }} por {{item.responsavel_aprovacao.nome}} <br>
                                    em {{item.updated_at}}
                                </span>
                            </td>

                            <td class="text-center">
                                {{item.status | capitalize() }}
                            </td>

                            <td class="text-center">
                                <a v-show="item.status === 'aprovado' || item.status === 'reprovado'"
                                   href="javascript://"
                                   class="btn btn-default"
                                   title="Visualizar"
                                   @click.prevent="formAprovar(item.id)"
                                   data-toggle="modal"
                                   :data-target="`#${hash}`">
                                    <i class="fa fa-search"></i> Visualizar
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import autocomplete from "../../AutoComplete";
import DatePicker from "../../DatePicker";
import Upload from "../../Upload";

export default {
    props: {
        fc_token: {
            type: String,
            required: true
        },
        model: {
            type: Array
        },
        hash: {
            type: String,
            default: `mastertag_${parseInt((Math.random() * 999999))}`
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
            preload: false,
            URL_ADMIN,

            colaborador_ativo: `autocomplete/colaboradorCih`,
            todos_municipios: `autocomplete/todos-municipios`,

            //hash: `mastertag_${parseInt((Math.random() * 999999))}`,
            cliente_id: 0,

            hoje: "",
            //feedback_id: '',


            form: {
                tag_id: "",
                outra_tag: "",
                feedback_id: "",
                autocomplete_label_colaborador: "",
                autocomplete_label_colaborador_anterior: "",
                cliente_id: "",
                area_id: "",
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

            url_anexo: `${URL_ADMIN}/storage/uploadAnexos`,
            anexoUploadAndamento: false,

            formDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            listaTags: [],
            listaAreas: [],
            listaClientes: []
        };
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.atualizar();
    },
    filters: {
        capitalize(value) {
            if (!value) return "";
            value = value.toString();
            return value.charAt(0).toUpperCase() + value.slice(1);
        }
    },
    components: {
        autocomplete,
        DatePicker,
        Upload
    },
    methods: {
        selecionaColaborador(obj) {
            this.form.feedback_id = obj.id;
            this.form.cliente_id = obj.cliente_id;
            this.form.autocomplete_label_colaborador = obj.label;
            this.form.autocomplete_label_colaborador_anterior = obj.label;
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

        formAprovar(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = true;
            this.tituloJanela = `Aprovando CIH #${id}`;
            this.preloadAjax = true;
            formReset();


            this.form = _.cloneDeep(this.formDefault); //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        atualizar() {
            this.preload = true;
            axios.get(`${URL_ADMIN}/historico/cih/${this.fc_token}`).then(res => {
                let data = res.data;
                this.lista = data.cih;
                this.preload = false;
            });
        }
    }
};

</script>

<style scoped>

</style>
