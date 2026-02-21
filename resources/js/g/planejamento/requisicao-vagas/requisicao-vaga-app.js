/**
 * Entry point: Requisição de Vagas como componente Vue.
 * A blade apenas renderiza <requisicao-vaga url-atualizar="..."> e este script monta o Vue.
 */
import RequisicaoVaga from '../../../components/planejamento/requisicao-vagas/RequisicaoVaga.vue';

new Vue({
    el: '#app',
    components: {
        RequisicaoVaga
    }
});
