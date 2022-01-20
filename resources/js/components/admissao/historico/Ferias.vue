<template>
    <div>
        <preload class="mt-2" v-if="preload"></preload>

        <div v-if="!preload" :id="`form_${hash}`">

            <div class="alert alert-warning" v-if="!listaFeriasDados || listaFeriasDados.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <!--            <div class="mb-2 mt-2 pt-1 pb-1 border-bottom" v-show="listaFeriasDados.length > 0">-->

            <!--            </div>-->

            <div class="table-responsive" v-else>
                 <span class="small text-right mb-3">
                    Legenda:
                    <i class="fas fa-circle text-warning ml-2"></i> Aguardando
                    <i class="fas fa-circle text-success ml-2"></i> Aprovado pelo Gestor
                    <i class="fas fa-circle text-danger ml-2"></i> Reprovado pelo Gestor
                </span>
                <table class="table table-bordered table-hover table-condensed mt-3" style="font-size: 0.85em;">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center">ID</th>
                        <th class="text-center">Solicitação</th>
                        <th class="text-center">Centro de custo</th>
                        <th class="text-center">Data saida</th>
                        <th class="text-center">Qnt dias</th>
                        <th class="text-center">Data retorno</th>
                        <th class="text-center">Dias saldo</th>
                        <th class="text-center">Aprovado Por</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in listaFeriasDados"
                        :class="!item ? 'table-warning'
                        : item.status_aprovacao === 'reprovado' ? 'table-danger'
                        : item.status_aprovacao === 'aprovado' ? 'table-success'
                        : null">
                        <td class="text-center">
                            {{ item.id }}
                        </td>

                        <td>
                            {{ item.user_cadastrou.nome }} <br>
                            {{ item.created_at }}
                        </td>

                        <td>
                            {{ item.centro_custo.label }}
                        </td>

                        <td class="text-center">
                            {{ item.data_saida }}
                        </td>

                        <td class="text-center">
                            {{ item.qnt_dias }}
                        </td>

                        <td class="text-center">
                            {{ item.data_retorno }}
                        </td>

                        <td class="text-center">
                            {{ item.dias_saldo }}
                        </td>

                        <td class="text-center" v-if="item.quem_aprovou">
                            {{ item.quem_aprovou.nome }} em
                            {{ item.data_aprovacao }}
                        </td>
                        <td class="text-center" v-else>
                            AGUARDANDO APROVAÇÃO
                        </td>

                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        feedback_id: {
            type: Number,
            required: true
        },
        curriculo_id: {
            type: Number,
            required: true
        },
        model: {
            type: Array,
        },
        hash: {
            type: String,
            default: `mastertag_ferias_${parseInt((Math.random() * 999999))}`
        }
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,
            hoje: '',
            lista: [],
            listaFeriasDados: [],
        }
    },
    mounted() {
        this.preload = true;
        this.atualizar();
    },
    methods: {
        gerarPdf(item) {
            let link = `${URL_ADMIN}/historico/ferias/${item.id}/${item.feedback_id}/pdf`;
            open(link, 'blank');
        },
        gerarPdfAfastamento(item) {
            let link = `${URL_ADMIN}/historico/afastamento/${item.id}/${item.feedback_id}/pdf`;
            open(link, 'blank');
        },
        atualizar() {

            axios.get(`${URL_ADMIN}/historico/ferias/${this.curriculo_id}`).then(res => {
                let data = res.data;
                this.hoje = data.hoje;
                if (data.ferias.length > 0) {
                    // this.lista = data.ferias
                    this.listaFeriasDados = data.ferias
                }
                this.formDefault = _.cloneDeep(this.form);
                this.preload = false;
            })
        }
    }
}
</script>

<style scoped>

</style>
