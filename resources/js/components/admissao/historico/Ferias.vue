<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <modal :fechar="!preloadSalvar" id="janelaFerias" size="g" modal-pai="janelaHistorico"
               titulo="Férias">
            <template slot="conteudo">
                <p class=" mt-2" v-if="preloadSalvar">
                    <i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...
                </p>
                <fieldset v-show="!preloadSalvar">
                    <legend>Outras Informações</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Ano</label>
                            <input v-model="form.ano" type="text" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Comprar Férias?</label>
                            <select v-model="form.comprada" class="form-control">
                                <option value="">Selecione</option>
                                <option :value="true">Sim</option>
                                <option :value="false">Não</option>
                            </select>
                        </div>
                        <div class="col-md-4" v-if="form.comprada === true">
                            <label>Quantidade de Dias Comprados</label>
                            <input v-model="form.dias_comprados" type="text" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <date-picker formsm label="Data Início" v-model="form.data_inicio"></date-picker>
                        </div>
                        <div class="col-md-6">
                            <date-picker formsm label="Data Fim" v-model="form.data_fim"></date-picker>
                        </div>
                        <div class="col-md-6">
                            <label>Valor</label>
                            <input v-model="form.valor" type="text" v-mascara:dinheiro class="form-control">
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button class="btn btn-sm btn-primary" v-if="!preloadSalvar" @click="salvar">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <modal :fechar="!preloadSalvarAfastamento" id="janelaAfastamento" size="g" modal-pai="janelaHistorico"
               titulo="Afastamento">
            <template slot="conteudo">
                <p class=" mt-2" v-if="preloadSalvarAfastamento">
                    <i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...
                </p>
                <fieldset v-show="!preloadSalvarAfastamento">
                    <legend>Outras Informações</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <date-picker formsm label="Data Início" v-model="formAfastamento.data_inicio"></date-picker>
                        </div>
                        <div class="col-md-6">
                            <date-picker formsm label="Data Fim"  v-model="formAfastamento.data_fim"></date-picker>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button class="btn btn-sm btn-sm btn-primary" v-if="!preloadSalvarAfastamento" @click="salvarAfastamento">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">

            <button class="btn btn-sm btn-primary mb-3"
                    data-toggle="modal"
                    data-target="#janelaFerias"
                    @click="addFerias">
                <i class="fa fa-plus"></i> Adicionar Férias
            </button>
            <button class="btn btn-sm btn-primary mb-3"
                    data-toggle="modal"
                    data-target="#janelaAfastamento"
                    @click="addAfastamento">
                <i class="fa fa-plus"></i> Adicionar Afastamento
            </button>

            <fieldset v-if="ferias.length > 0">
                <legend>Férias</legend>
                <div class="table-responsive">
                    <table class="tabela">
                        <thead>
                        <tr class="bg-default">
                            <td class="text-center">Ano</td>
                            <td class="text-center">Data Início</td>
                            <td class="text-center">Data Fim</td>
                            <td class="text-center">Quem Cadastrou</td>
                            <td class="text-center">PDF</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in ferias">
                            <td class="text-center">{{ item.ano }}</td>
                            <td class="text-center">{{ item.data_inicio }}</td>
                            <td class="text-center">{{ item.data_fim }}</td>
                            <td class="text-center">{{ item.usuario.nome }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-default" @click="gerarPdf(item)"><i
                                    class="fas fa-file-pdf"></i> GERAR PDF
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
            <fieldset v-if="afastamento.length > 0">
                <legend>Afastamento</legend>

                <div class="table-responsive">
                    <table class="tabela">
                        <thead>
                        <tr class="bg-default">
                            <td class="text-center">Data Início</td>
                            <td class="text-center">Data Fim</td>
                            <td class="text-center">Quem Cadastrou</td>
                            <td class="text-center">PDF</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in afastamento">
                            <td class="text-center">{{ item.data_inicio }}</td>
                            <td class="text-center">{{ item.data_fim }}</td>
                            <td class="text-center">{{ item.usuario.nome }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-default" @click="gerarPdfAfastamento(item)"><i
                                    class="fas fa-file-pdf"></i> GERAR PDF
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
</template>

<script>

    import DatePicker from "../../DatePicker";

    export default {
        props: {
            feedback_id: {
                type: Number,
                required: true
            },
            model: {
                type: Array,
            },
            hash: {
                type: String,
                default: `mastertag_${parseInt((Math.random() * 999999))}`
            }
        },
        components: {
            DatePicker,
        },
        data() {
            return {
                preload: false,
                preloadSalvar: false,
                preloadSalvarAfastamento: false,
                URL_ADMIN,

                hoje: '',

                ferias: [],
                afastamento: [],

                form: {
                    feedback_id: '',
                    comprada: '',
                    dias_comprados: '',
                    data_inicio: '',
                    data_fim: '',
                    valor: '',
                },
                formDefault: null,

                formAfastamento: {
                    feedback_id: '',
                    data_inicio: '',
                    data_fim: '',
                },
                formAfastamentoDefault: null,

            }
        },
        mounted() {
            this.atualizar();
        },
        methods: {
            addFerias() {
                this.formDefault = _.cloneDeep(this.form);
                this.form.feedback_id = this.feedback_id;
                this.preloadSalvar = false;
                formReset();
                setupCampo();
            },
            addAfastamento() {
                this.formAfastamentoDefault = _.cloneDeep(this.formAfastamento);
                this.formAfastamento.feedback_id = this.feedback_id;
                this.preloadSalvarAfastamento = false;
                formReset();
                setupCampo();
            },
            salvar() {
                $(`#janelaFerias :input:visible`).trigger('blur');
                if ($(`#janelaFerias :input:visible.is-invalid`).length) {
                    mostraErro('', 'Verifique os erros.')
                    return false;
                }

                this.preloadSalvar = true;
                //criar
                axios.post(`${URL_ADMIN}/historico/ferias/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preloadSalvar = false;
                            mostraSucesso('Férias adicionada com sucesso.');
                            $('#janelaFerias').modal('hide');
                            this.form = _.cloneDeep(this.formDefault);
                            // this.cadastrado = true;
                            this.atualizar();
                        }
                    })
                    .catch(error => (this.preloadSalvar = false));

            },
            salvarAfastamento() {
                $(`#janelaAfastamento :input:visible`).trigger('blur');
                if ($(`#janelaAfastamento :input:visible.is-invalid`).length) {
                    mostraErro('', 'Verifique os erros.')
                    return false;
                }

                this.preloadSalvarAfastamento = true;
                //criar
                axios.post(`${URL_ADMIN}/historico/afastamento/${this.feedback_id}`, this.formAfastamento)
                    .then(response => {
                        if (response.status === 201) {
                            this.preloadSalvarAfastamento = false;
                            mostraSucesso('Afastamento adicionado com sucesso.');
                            $('#janelaAfastamento').modal('hide');
                            this.formAfastamento = _.cloneDeep(this.formAfastamentoDefault);
                            // this.cadastrado = true;
                            this.atualizar();
                        }
                    })
                    .catch(error => (this.preloadSalvarAfastamento = false));

            },
            gerarPdf(item) {
                let link = `${URL_ADMIN}/historico/ferias/${item.id}/${item.feedback_id}/pdf`;
                open(link, 'blank');
            },
            gerarPdfAfastamento(item) {
                let link = `${URL_ADMIN}/historico/afastamento/${item.id}/${item.feedback_id}/pdf`;
                open(link, 'blank');
            },
            atualizar() {
                this.preload = true;
                axios.get(`${URL_ADMIN}/historico/ferias/${this.feedback_id}`).then(res => {
                    let data = res.data;
                    this.form.feedback_id = data.feedback;
                    this.formAfastamento.feedback_id = data.feedback;
                    this.hoje = data.hoje;
                    this.ferias = data.ferias;
                    this.afastamento = data.afastamento;
                    this.formDefault = _.cloneDeep(this.form);
                    this.formAfastamentoDefault = _.cloneDeep(this.formAfastamento);
                    this.preload = false;
                })
            }
        }
    }
</script>

<style scoped>

</style>
