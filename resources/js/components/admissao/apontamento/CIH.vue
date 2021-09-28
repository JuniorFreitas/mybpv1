<template>
    <div>

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
        DatePicker
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

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

            this.form.medidas_administrativas.push(obj);
        },
        removerLIMedida(index) {
            if (this.editando) {
                this.form.medidas_administrativasDelete.push(this.form.medidas_administrativas[index].id);
            }
            this.form.medidas_administrativas.splice(index, 1);
        },
        salvar() {
            formReset();
            $(`form_${this.hash}:input:visible`).trigger('blur');
            if ($(`form_${this.hash} :input:visible.is-invalid`).length) {
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
