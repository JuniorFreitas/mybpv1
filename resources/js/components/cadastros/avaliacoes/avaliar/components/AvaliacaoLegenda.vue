<template>
    <div>
        <!-- Legenda para avaliações com autoavaliação -->
        <div
            class="row mt-2 pt-1 pb-1 border-bottom"
            v-if="!loading && avaliacao && avaliacao.auto_avaliacao"
        >
            <div class="col-12">
                <p class="bg-white p-3 rounded">
                    <i class="fas fa-circle text-danger"></i> Pendente autoavaliação
                    <i class="fas fa-circle text-pink ml-2"></i> Pendente autoavaliação colaborador
                    <i class="fas fa-circle text-warning ml-2"></i> Pendente avaliação do par
                    <i class="fas fa-circle text-info ml-2"></i> Pendente avaliação gestor
                    <i class="fas fa-circle text-success ml-2"></i> Completa
                </p>
            </div>
        </div>

        <!-- Legenda para avaliações sem autoavaliação -->
        <div
            class="row mt-2 pt-1 pb-1 border-bottom"
            v-if="!loading && avaliacao && !avaliacao.auto_avaliacao"
        >
            <div class="col-12">
                <p class="bg-white p-3 rounded">
                    <i class="fas fa-circle ml-2" style="color: #f1f1f1"></i> Pendente Avaliação do Gestor
                    <i class="fas fa-circle text-info ml-2"></i> Avaliada pelo Gestor
                    <i class="fas fa-circle text-success ml-2"></i> Completa
                </p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AvaliacaoLegenda',
    props: {
        avaliacao: {
            type: Object,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped>
.text-pink {
    color: pink !important;
}
</style>
