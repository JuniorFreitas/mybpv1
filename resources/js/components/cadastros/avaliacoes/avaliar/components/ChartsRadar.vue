<template>
    <div class="row justify-content-center mt-5" v-if="hasCharts">
        <div v-for="(chart, index) in charts" :key="chart.id || index" class="col-md-4">
            <h4 class="text-center">{{ chart.name }}</h4>
            <RadarChart :id="chart.name" :chart-data="chart.data" />
            <h4 class="text-center">
                Média: {{ getMediaFormatada(chart.name, resultadoTopicoPai) }}
            </h4>
        </div>
        <div class="col-md-12 text-center">
            <h4>Nota final: {{ formatarDecimal(notaFinal) }}</h4>
        </div>
    </div>
</template>

<script>
import RadarChart from '../../../../Charts/Radar'
import avaliacaoMixin from '../mixins/avaliacaoMixin'

export default {
    name: 'ChartsRadar',
    components: {
        RadarChart
    },
    mixins: [avaliacaoMixin],
    props: {
        charts: {
            type: Array,
            default: () => []
        },
        notaFinal: {
            type: [Number, String],
            default: 0
        },
        resultadoTopicoPai: {
            type: Object,
            default: () => ({})
        }
    },
    computed: {
        hasCharts() {
            return this.hasValidData(this.charts)
        }
    }
}
</script>
