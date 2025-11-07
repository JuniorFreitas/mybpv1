<template>
    <div>
        <!-- Usando o componente reutilizável -->
        <avaliacao-noventa-dias
            :feedback-id="feedback_id"
            modal-id="janelaFormulario"
            modal-pai="janelaHistorico"
            :exibir-vencimentos="true"
            :exibir-botao-adicionar="true"
            :exibir-modal="true"
            @adicionar-avaliacao="handleAdicionarAvaliacao"
            @avaliacao-salva="handleAvaliacaoSalva"
            @erro-salvar="handleErroSalvar"
        >
        </avaliacao-noventa-dias>
    </div>
</template>

<script>
import AvaliacaoNoventaDias from '../../shared/AvaliacaoNoventaDias.vue'

export default {
    name: 'FormularioNoventaDias',

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
            default: `mastertag_${parseInt(Math.random() * 999999)}`
        }
    },

    components: {
        AvaliacaoNoventaDias
    },

    data() {
        return {
            URL_ADMIN
        }
    },

    methods: {
        /**
         * Handler quando uma nova avaliação é adicionada
         */
        handleAdicionarAvaliacao(feedbackId) {
            console.log('Adicionando avaliação para feedback:', feedbackId)
        },

        /**
         * Handler quando avaliação é salva com sucesso
         */
        handleAvaliacaoSalva(form) {
            console.log('Avaliação salva com sucesso:', form)
        },

        /**
         * Handler para erros ao salvar
         */
        handleErroSalvar(error) {
            console.error('Erro ao salvar avaliação:', error)
        }
    }
}
</script>

<style scoped></style>
