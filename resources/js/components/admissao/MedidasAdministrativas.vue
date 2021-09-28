<template>
    <div>
        <p class=" mt-2" v-if="preload">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <div v-if="!preload" :id="`form_${hash}`">

            <button class="btn btn-primary mb-3" @click="addLIMedida">
                <i class="fa fa-plus"></i> Adicionar Medida
            </button>

            <fieldset class=" mb-2" v-if="form.medidas_administrativas.length > 0"
                      v-for="(obj, index) in form.medidas_administrativas" :key="index">
                <legend>#{{ index + 1 }}</legend>
                <div class="row">

                    <div class="col-md-4">
                        <label>Tipo</label>
                        <select class="form-control" v-model="obj.tipo" :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            <option v-for="item in tipos" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Definição</label>
                        <select class="form-control" v-model="obj.definicao" :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            <option v-for="item in definicao" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Causa</label>
                        <select class="form-control" v-model="obj.causa" :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this,1)"
                                onblur="valida_campo_vazio(this,1)">
                            <option value="">Selecione ...</option>
                            <option v-for="item in causas" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Motivo</label>
                        <input type="text" class="form-control" v-model="obj.motivo" :disabled="!obj.novo"
                               onblur="valida_campo_vazio(this,1)">
                    </div>

                    <div class="col-md-4">
                        <label>Solicitante</label>
                        <input type="text" class="form-control" v-model="obj.solicitante" :disabled="!obj.novo"
                               onblur="valida_campo_vazio(this,1)">
                    </div>

                    <div class="col-md-2">
                        <label>Data Solicitação</label>
                        <date-picker v-model="obj.data_solicitacao" :max="hoje" :disabled="!obj.novo"></date-picker>
                    </div>

                    <div class="col-12">
                        <fieldset>
                            <legend>Anexo</legend>
                            <upload :model="obj.anexos"
                                    :model-delete="obj.anexosDel"
                                    :url="url_anexo"
                                    :leitura="!obj.novo"
                                    label="Selecionar"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                        </fieldset>
                    </div>

                    <div class="col-12 mt-3" v-show="obj.novo">
                        <button class="btn btn-danger" @click="removerLIMedida(index)"><i
                            class="fa fa-times"></i> Remover
                        </button>

                        <button class="btn btn-primary mt" @click="addLIMedida" v-show="index >=1">
                            <i class="fa fa-plus"></i> Adicionar
                        </button>
                    </div>

                    <div class="col-12 mt-3"
                         v-show="!obj.novo && obj.tipo === 'Advertência Escrita' ||
                         obj.tipo === 'Suspensão de 1 dia' ||
                         obj.tipo === 'Suspensão de 2 ou 3 dias' ||
                         obj.tipo === 'Suspensão acima de 3 dias'">
                        <button class="btn btn-outline-default" @click="gerarPdf(obj)"><i
                            class="fas fa-file-pdf"></i> GERAR PDF
                        </button>
                    </div>
                </div>
            </fieldset>

            <button class="btn btn-primary mb-3" v-if="form.medidas_administrativas.length > 0" @click="salvar">
                <i class="fa fa-save"></i> Salvar
            </button>
        </div>
    </div>
</template>

<script>
import DatePicker from "../DatePicker";
import Upload from "../Upload";

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
        Upload
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

            url_anexo: `${URL_ADMIN}/storage/uploadAnexos`,
            anexoUploadAndamento: false,

            hoje: '',

            form: {
                medidas_administrativas: [],
                medidas_administrativasDelete: [],
            },
            formDefault: null,

            causas: [],
            tipos: [],
            definicao: [],
        }
    },
    mounted() {
        this.atualizar();
    },
    methods: {
        addLIMedida() {
            const obj = {};
            obj.novo = true;
            obj.feedback_id = this.feedback_id;
            obj.solicitante = '';
            obj.tipo = '';
            obj.causa = '';
            obj.definicao = '';
            obj.motivo = '';
            obj.data_solicitacao = '';
            obj.anexos = [];
            obj.anexosDel = [];

            this.form.medidas_administrativas.push(obj);
        },
        removerLIMedida(index) {
            if (this.editando) {
                this.form.medidas_administrativasDelete.push(this.form.medidas_administrativas[index].id);
            }
            this.form.medidas_administrativas.splice(index, 1);
        },
        gerarPdf(obj) {
            let link = `${URL_ADMIN}/historico/medidas-administrativas/${obj.id}/${obj.feedback_id}/pdf`;
            open(link, 'blank');
        },
        salvar() {
            formReset();
            $(`#form_${this.hash} :input:visible`).trigger('blur');
            if ($(`#form_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }

            this.preload = true;

            if (this.form.medidas_administrativas[0].id) { //alterar
                axios.put(`${URL_ADMIN}/historico/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preload = false;
                            // this.cadastrado = true;
                            mostraSucesso('Medida administrativa alterada com sucesso');
                            this.atualizar();
                        }
                    }).catch(error => (this.preload = false));
            } else { //criar
                axios.post(`${URL_ADMIN}/historico/${this.feedback_id}`, this.form)
                    .then(response => {
                        if (response.status === 201) {
                            this.preload = false;
                            mostraSucesso('Medida administrativa criada com sucesso');
                            // this.cadastrado = true;
                            this.atualizar();
                        }
                    }).catch(error => (this.preload = false));
            }
        },
        atualizar() {
            this.preload = true;
            this.form.medidas_administrativas = [];
            this.form.medidas_administrativasDelete = [];
            axios.get(`${URL_ADMIN}/historico/${this.feedback_id}`).then(res => {
                let data = res.data;
                this.form.medidas_administrativas = data.feedback.medidas_administrativas;
                this.causas = data.causas;
                this.tipos = data.tipos;
                this.definicao = data.definicao;
                this.hoje = data.hoje;
                this.preload = false;
            })
        }
    }
}
</script>

<style scoped>

</style>
