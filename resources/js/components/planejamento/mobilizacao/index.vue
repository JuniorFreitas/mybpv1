<template>
    <div>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Projetos</label>
                        <select2 :settings="settings2" v-model="projeto_id" :options="listProjetos"
                                 @change="abriRelatorio()"></select2>
                    </div>
                </div>
            </form>
        </fieldset>

        <preload v-if="preload"></preload>

        <div class="alert alert-warning mt-2" v-show="!preload && !projeto_id">
            Selecione um projeto
        </div>

        <div v-if="!preload && showRelatorio">
            <h1>Exibindo relatorio</h1>
        </div>
    </div>
</template>

<script>
import Select2 from "../../../components/Select2/Select2";
import configselect2 from "../../../components/Select2/mixSelec2";

export default {
    name: "Mobilizacao",
    mixins: [configselect2],
    components: {
        Select2
    },
    async mounted() {
        this.preload = true;
        await axios.get(`${this.urlBase}/get-projetos`).then(response => {
            this.listProjetos = response.data;
            this.preload = false;
        }).catch(error => {
            this.preload = false;
        });
    },
    computed: {
        urlBase() {
            return `${URL_ADMIN}/planejamento/mobilizacao`;
        }
    },
    data() {
        return {
            preload: false,
            projeto_id: null,
            listProjetos: [],
            showRelatorio: false,
            dados: []
        };
    },
    methods: {
        async abriRelatorio() {
            this.preload = true;
            this.showRelatorio = false;
            if (this.projeto_id == null) {
                this.dados = [];
                this.preload = false;
                this.showRelatorio = false;
                return false;
            }
            await axios.get(`${this.urlBase}/seleciona-projeto/${this.projeto_id}`).then(response => {
                this.dados = response.data;
                this.preload = false;
            }).catch(error => {
                this.preload = false;
            });
        }
    }
};
</script>

<style scoped>

</style>
