/**
 * Avaliação de Experiência (ex Relatório de Avaliação de 90 dias)
 * Monta o componente Vue que consome a API /g/relatorios/avaliacao-de-experiencia/dados
 */
import AvaliacaoExperiencia from '../../../components/relatorios/AvaliacaoExperiencia.vue';

const app = new Vue({
    el: '#app',
    data: {},
    components: {
        AvaliacaoExperiencia
    }
});
