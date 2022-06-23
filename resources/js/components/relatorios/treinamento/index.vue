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
            <preload v-if="preload" />
            <template v-if="!preload">
                <div v-for="(item, index) in dados" :key="index" class="mb-3">
                    <h5 class="text-center">{{ item.nome }}</h5>
                    <h6 class="text-center">{{ item.cargo }}</h6>
                    <div class="row">
                        <div class="col-md-6" v-for="(tr, idx) in item.treinamentos" :key="idx">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">
                                    <strong>Treinamento: </strong>{{ tr.label }} / ({{ item.tipo }})
                                </li>
                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">
                                    <strong>Data do treinamento: </strong>{{ tr.data_treinamento }}
                                </li>
                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">
                                    <strong>Data de vencimento: </strong>{{ tr.data_vencimento }}
                                </li>
                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">
                                    <strong>Vence em {{ tr.dias_vencer }} dias</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
<script>

export default {
    data() {
        return {
            preload: false,
            dados: [],
            periodo: ""
        };
    },
    mounted() {
        let inicio_de_mes = moment().startOf("month").format("DD/MM/YYYY");
        let fim_de_mes = moment().add(1,'M').endOf("month").format("DD/MM/YYYY");
        this.periodo = `${inicio_de_mes} até ${fim_de_mes}`;
        this.buscarDados();
    },
    methods: {
        buscarDados() {
            this.preload = true;
            axios.post(`${URL_ADMIN}/relatorios/vencimento-treinamento`, { periodo: this.periodo }).then(res => {
                this.dados = res.data;
                this.preload = false;
            });
        }
    }

};
</script>
