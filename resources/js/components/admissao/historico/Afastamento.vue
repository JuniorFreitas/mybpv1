<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <div v-if="!preload" :id="`form_${hash}`">

            <button class="btn btn-sm btn-primary mb-3" @click="addLIMedida">
                <i class="fa fa-plus"></i> Adicionar Afastamento
            </button>

            <fieldset class=" mb-2" v-if="form.afastamento.length > 0"
                      v-for="(obj, index) in form.afastamento" :key="index">
                <legend>#{{ index + 1 }}</legend>
                <div class="row">

                    <div class="col-md-3">
                        <date-picker label="Período" range v-model="obj.periodo" formsm
                                     :disabled="!obj.novo"></date-picker>
                    </div>

                    <div class="col-12"></div>

                    <div class="col-md-12 mb-3">
                        <label>Motivo</label>
                        <input type="text" class="form-control form-control-sm" v-model="obj.motivo"
                               :disabled="!obj.novo"
                               onblur="valida_campo_vazio(this,1)">
                    </div>

                    <div class="col-md-12">
                        <label>Observação</label>
                        <textarea type="text" rows="3" v-model="obj.observacao"
                                  :disabled="!obj.novo"
                                  class="form-control form-control-sm"></textarea>
                    </div>

                    <div class="col-12">
                        <fieldset>
                            <legend>Anexo</legend>
                            <upload :model="obj.anexos"
                                    :model-delete="obj.anexosDel"
                                    :url="url_anexo"
                                    label="Selecionar"
                                    :leitura="!obj.novo"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                            <div class="alert alert-warning mt-2 text-center mb-0" v-if="obj.anexos.length === 0 && !obj.novo">
                                Nenhum anexo encontrado.
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-12 mt-3" v-show="obj.novo">
                        <button class="btn btn-sm btn-danger" @click="removerLIMedida(index)"><i
                            class="fa fa-times"></i> Remover
                        </button>

                        <button class="btn btn-sm btn-primary" v-if="obj.novo" @click="salvar">
                            <i class="fa fa-save"></i> Salvar
                        </button>

<!--                        <button class="btn btn-sm btn-primary mt" @click="addLIMedida" v-show="index >=1">-->
<!--                            <i class="fa fa-plus"></i> Adicionar-->
<!--                        </button>-->
                    </div>

                </div>
            </fieldset>


        </div>
    </div>
</template>

<script>
import DatePicker from "../../DatePicker";
import Upload from "../../Upload";

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
        DatePicker,
        Upload
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

            hoje: "",
            url_anexo: `${URL_ADMIN}/historico/afastamento-historico/uploadAnexos`,
            anexoUploadAndamento: false,

            form: {
                afastamento: [],
                afastamentoDelete: []
            },
            formDefault: null

        };
    },
    mounted() {
        this.atualizar();
    },
    methods: {
        padTo2Digits(num) {
            return num.toString().padStart(2, "0");
        },
        addLIMedida() {
            const obj = {};
            obj.novo = true;
            obj.feedback_id = this.feedback_id;
            obj.motivo = "";
            obj.observacao = "";
            obj.periodo = this.hoje;
            obj.anexos = [];
            obj.anexosDel = [];

            this.form.afastamento.unshift(obj);
        },
        removerLIMedida(index) {
            if (this.editando) {
                this.form.afastamentoDelete.push(this.form.afastamento[index].id);
            }
            this.form.afastamento.splice(index, 1);
        },
        salvar() {
            formReset();
            $(`#form_${this.hash} :input:visible`).trigger("blur");
            if ($(`#form_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro("", "Verifique os erros");
                return false;
            }

            this.preload = true;

            if (this.form.afastamento[0].id) { //alterar
                axios.put(`${URL_ADMIN}/historico/afastamento-historico/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preload = false;
                            // this.cadastrado = true;
                            mostraSucesso("Afastamento alterado com sucesso");
                            this.atualizar();
                        }
                    }).catch(error => (this.preload = false));
            } else { //criar
                axios.post(`${URL_ADMIN}/historico/afastamento-historico/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 200) {
                            this.preload = false;
                            mostraSucesso("Afastamento criado com sucesso");
                            // this.cadastrado = true;
                            this.atualizar();
                        }
                    }).catch(error => (this.preload = false));
            }
        },
        atualizar() {
            this.preload = true;
            this.form.afastamento = [];
            this.form.afastamentoDelete = [];
            axios.get(`${URL_ADMIN}/historico/afastamento-historico/${this.feedback_id}`).then(res => {
                let data = res.data;
                this.form.afastamento = data.afastamentos;
                this.hoje = data.hoje;
                this.preload = false;
            });
        }
    }
};
</script>

<style scoped>

</style>
