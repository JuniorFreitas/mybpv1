<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <modal :fechar="!preloadSalvar" id="janelaPromocao" size="g" modal-pai="janelaHistorico"
               titulo="Promoção">
            <template slot="conteudo">
                <p class=" mt-2" v-if="preloadSalvar">
                    <i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...
                </p>
                <fieldset v-show="!preloadSalvar">
                    <legend>Outras Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Novo Cargo</label>
                                <input type="text" v-model="form.novo_cargo" class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Salário</label>
                                <input type="text" v-model="form.novo_salario" class="form-control"
                                       v-mascara:dinheiro
                                       onblur="valida_campo_vazio(this,1)">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Percentual</label>
                                <input type="text" v-model="form.percentual" class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select v-model="form.tipo"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)" class="custom-select">
                                    <option value="">Selecione</option>
                                    <option value="promocao">Promoção</option>
                                    <option value="reajuste">Reajuste</option>
                                    <option value="acordocoletivo">Acordo Coletivo</option>
                                    <option value="merito">Mérito</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Motivo da Promoção</label>
                                <textarea type="text" rows="3" v-model="form.motivo" class="form-control"
                                          onblur="valida_campo_vazio(this,1)"></textarea>
                            </div>
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

        <div v-if="!preload" :id="`form_${hash}`">

            <button class="btn btn-sm btn-primary mb-3"
                    data-toggle="modal"
                    data-target="#janelaPromocao"
                    @click="addPromocao">
                <i class="fa fa-plus"></i> Adicionar Promoção
            </button>

            <fieldset v-if="promocao.length > 0">
                <legend>Promoções</legend>
                <div class="table-responsive">
                    <table class="tabela">
                        <thead>
                        <tr class="bg-default">
                            <td class="text-center">Novo Cargo</td>
                            <td class="text-center">Motivo</td>
                            <td class="text-center">Percentual</td>
                            <td class="text-center">Tipo</td>
                            <td class="text-center">Tipo</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in promocao">
                            <td class="text-center">{{ item.novo_cargo }}</td>
                            <td class="text-center">{{ item.motivo }}</td>
                            <td class="text-center">{{ item.percentual }}</td>
                            <td class="text-center">{{ item.tipo }}</td>
                            <td class="text-center">{{ item.tipo }}</td>
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
                URL_ADMIN,

                promocao: [],

                form: {
                    feedback_id: '',
                    novo_cargo: '',
                    novo_salario: '',
                    motivo: '',
                    percentual: '',
                    tipo: '',
                },
                formDefault: null,
            }
        },
        mounted() {
            this.atualizar();
        },
        methods: {
            addPromocao() {
                this.formDefault = _.cloneDeep(this.form);
                this.form.feedback_id = this.feedback_id;
                this.preloadSalvar = false;
                formReset();
                setupCampo();
            },
            salvar() {
                $(`#janelaPromocao :input:visible`).trigger('blur');
                if ($(`#janelaPromocao :input:visible.is-invalid`).length) {
                    mostraErro('', 'Verifique os erros.')
                    return false;
                }

                this.preloadSalvar = true;
                //criar
                axios.post(`${URL_ADMIN}/historico/promocao/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preloadSalvar = false;
                            mostraSucesso('Promoção adicionada com sucesso.');
                            $('#janelaPromocao').modal('hide');
                            this.form = _.cloneDeep(this.formDefault);
                            // this.cadastrado = true;
                            this.atualizar();
                        }
                    })
                    .catch(error => (this.preloadSalvar = false));

            },
            atualizar() {
                this.preload = true;
                axios.get(`${URL_ADMIN}/historico/promocao/atualizar/${this.feedback_id}`).then(res => {
                    let data = res.data;
                    this.form.feedback_id = data.feedback;
                    this.promocao = data.promocoes;
                    this.formDefault = _.cloneDeep(this.form);
                    this.preload = false;
                })
            }
        }
    }
</script>

<style scoped>

</style>
