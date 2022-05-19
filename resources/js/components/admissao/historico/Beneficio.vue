<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <modal :fechar="!preload" id="janelaBeneficio" size="g" modal-pai="janelaHistorico"
               titulo="Benefício">
            <template slot="conteudo">
                <p class=" mt-2" v-if="preload">
                    <i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...
                </p>
                <fieldset v-show="!preload">
                    <legend>Outras Informações</legend>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Benefício</label>
                            <select v-model="form.beneficio_id" class="form-control"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)">
                                <option value="">Selecione</option>
                                <option v-for="item in beneficio" :value="item.id">{{ item.nome }}</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button class="btn btn-primary" v-if="!preload" @click="salvar">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">

            <button class="btn btn-primary mb-3"
                    data-toggle="modal"
                    data-target="#janelaBeneficio"
                    @click="addBeneficio">
                <i class="fa fa-plus"></i> Adicionar Benefício
            </button>

            <fieldset v-if="listaBeneficio.length > 0">
                <legend>Benefício</legend>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed">
                        <thead>
                        <tr class="bg-default">
                            <td class="text-center">Data</td>
                            <td class="text-center">Nome do Benefício</td>
                            <td class="text-center">Valor do Benefício</td>
                            <td class="text-center">Periodicidade</td>
                            <td class="text-center">Valor Descontado</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in listaBeneficio">
                            <td class="text-center">{{ item.created_at }}</td>
                            <td class="text-center">{{ item.beneficio.nome }}</td>
                            <td class="text-center">R$ {{ item.beneficio.valor_format }}</td>
                            <td class="text-center">{{ item.beneficio.periodicidade }}</td>
                            <td class="text-center">R$ {{ item.beneficio.valordescontado_format }}</td>
                            <!--                            <td class="text-center">-->
                            <!--                                <button class="btn btn-outline-default" @click="gerarPdf(item)"><i-->
                            <!--                                    class="fas fa-file-pdf"></i> GERAR PDF-->
                            <!--                                </button>-->
                            <!--                            </td>-->
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
            type: Array
        },
        hash: {
            type: String,
            default: `mastertag_${parseInt((Math.random() * 999999))}`
        }
    },
    components: {
        DatePicker
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

            hoje: "",

            beneficio: [],
            listaBeneficio: [],

            form: {
                beneficio_id: "",
                feedback_id: ""
            },
            formDefault: null
        };
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
    },
    methods: {
        addBeneficio() {
            this.form = _.cloneDeep(this.formDefault);
            this.form.feedback_id = this.feedback_id;
            this.preload = false;
            formReset();
            setupCampo();
        },
        salvar() {
            $(`#janelaBeneficio :input:visible`).trigger("blur");
            if ($(`#janelaBeneficio :input:visible.is-invalid`).length) {
                mostraErro("", "Verifique os erros.");
                return false;
            }

            this.preload = true;
            //criar
            axios.post(`${URL_ADMIN}/historico/beneficio/${this.feedback_id}`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preload = false;
                        mostraSucesso("Benefício adicionado com sucesso.");
                        $("#janelaBeneficio").modal("hide");
                        this.form = _.cloneDeep(this.formDefault);
                        // this.cadastrado = true;
                        this.atualizar();
                    }
                })
                .catch(error => (this.preload = false));

        },
        // gerarPdf(item) {
        //     let link = `${URL_ADMIN}/historico/ferias/${item.id}/${item.feedback_id}/pdf`;
        //     open(link, 'blank');
        // },
        atualizar() {
            this.preload = true;
            axios.get(`${URL_ADMIN}/historico/beneficio/${this.feedback_id}`).then(res => {
                let data = res.data;
                this.form.feedback_id = data.feedback_id;
                this.beneficio = data.beneficio;
                this.listaBeneficio = data.listaBeneficio;
                this.formDefault = _.cloneDeep(this.form);
                this.preload = false;
            });
        }
    }
};
</script>

<style scoped>

</style>
