<template>
    <div>
        <p class=" mt-2" v-if="controle.carregando">
            <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
        </p>
        <div v-if="!controle.carregando" :id="`form_${hash}`">
            <div class="alert alert-warning" v-show="!controle.carregando && log_historico.length===0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>
            <div class="table-responsive" v-if="!controle.carregando && log_historico.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Usuário</td>
                        <td class="text-center">Ação</td>
                        <td class="text-center">Data</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in log_historico">
                        <td class="text-center">{{ item.usuario.nome }}</td>
                        <td class="text-center">{{ item.acao }}</td>
                        <td class="text-center">{{ item.data }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            :url='urlPaginacao' :por-pagina="qntPag"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
</template>

<script>
import Utils from "../../../mixins/Utils";
import controlePaginacao from "../../ControlePaginacao.vue";
export default {
    components: {
        controlePaginacao
    },
    name: "LogHistorico",
    mixins: [Utils],
    props: {
        feedback_id: {
            type: Number,
            required: true
        },
        hash: {
            type: String,
            default: `mybp_${parseInt((Math.random() * 999999))}`
        }
    },
    data() {
        return {
            log_historico: [],
            urlPaginacao: `${URL_ADMIN}/historico/log-historico/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    feedback_id: this.feedback_id
                }
            }
        };
    },
    mounted() {
       this.atualizar()
    },
    methods: {
        carregou(dados) {
            this.log_historico = dados.itens;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1);
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null;
        }
    }
};
</script>

<style scoped>

</style>
