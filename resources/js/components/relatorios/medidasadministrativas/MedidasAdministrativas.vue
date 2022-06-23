<template>
    <div>
        <div>
            <fieldset>
                <legend>Filtro</legend>
                <form class="row">
                    <div class="col-12 col-md-3">
                        <div class="form-check" style="margin-bottom: -11px; margin-left: -15px;">
                            <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                        </div>
                        <div class="form-group">
                            <datepicker range formsm label=""
                                        v-model="periodo"></datepicker>
                            <button class="btn btn-sm btn-primary" @click.prevent="buscarDados()" type="button">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </fieldset>
            <preload v-if="preload"/>
            <template v-if="!preload">
                <div class="alert alert-warning" v-show="!dados.length">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                </div>
                <div v-for="(medidas, index) in dados" :key="index" v-if="dados.length" class="mb-3">
                    <h5 class="text-center">{{medidas.nome}}</h5>
                    <h6 class="text-center">{{medidas.cargo}}</h6>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Motivo: </strong>{{medidas.motivo}}</li>
                        <li class="list-group-item"><strong>Causa: </strong>{{medidas.causa}}</li>
                        <li class="list-group-item"><strong>Data da Solicitação: </strong>{{medidas.data_solicitacao}}</li>
                        <li class="list-group-item"><strong>Tipo: </strong>{{medidas.tipo}}</li>
                        <li class="list-group-item"><strong>Solicitante: </strong>{{medidas.solicitante}}</li>
                    </ul>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import preload from '../../preload.vue';

    export default {
  components: { preload },
        data() {
            return {
                hash: String(Math.random()).substr(2),

                preload: false,
                dados: [],
                periodo: '',
            }
        },
        mounted() {
            let inicio_de_mes = moment().startOf('month').format('DD/MM/YYYY');
            let fim_de_mes = moment().endOf('month').format('DD/MM/YYYY');
            this.periodo = `${inicio_de_mes} até ${fim_de_mes}`
            this.buscarDados();
        },
        methods: {
            buscarDados() {
                this.preload = true;
                axios.post(`${URL_ADMIN}/relatorios/medidasadministrativas`, {periodo:this.periodo}).then(res => {
                    this.dados = res.data;
                    this.preload = false;
                })
            },
            gerarPdf() {
                // let link = `${URL_ADMIN}/relatorios/controleusuarios/pdf/${this.form}`;
                // open(link, '_blank');
            },
        }

    }
</script>

<style scoped>
    .card {
        border: none;
        background: transparent;
    }

    ul.timeline {
        list-style-type: none;
        position: relative;
    }

    ul.timeline:before {
        content: ' ';
        background: #d4d9df;
        display: inline-block;
        position: absolute;
        left: 29px;
        width: 2px;
        height: 100%;
        z-index: 400;
    }

    ul.timeline > li {
        margin: 20px 0;
        padding-left: 20px;
    }

    ul.timeline > li:before {
        content: ' ';
        background: white;
        display: inline-block;
        position: absolute;
        border-radius: 50%;
        border: 3px solid #184056;
        left: 20px;
        width: 20px;
        height: 20px;
        z-index: 400;
    }

    .trackind {
        padding: .5rem .8rem;
        background-color: #f4f4f4;
        border-radius: .5rem;
    }
</style>
