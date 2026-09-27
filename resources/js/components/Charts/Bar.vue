<template>
    <div class="aso-chart-wrap" :style="{ height: altura + 'px' }">
        <Bar v-if="temDados" :data="chartData" :options="options" />
        <p v-else class="text-muted text-center mb-0 py-4">Sem dados para o gráfico</p>
    </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
    Legend,
    Title
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend, Title)

export default {
    name: 'ChartsBar',
    components: { Bar },
    props: {
        labels: { type: Array, default: () => [] },
        datasets: { type: Array, default: () => [] },
        title: { type: String, default: '' },
        horizontal: { type: Boolean, default: false },
        altura: { type: Number, default: 280 }
    },
    computed: {
        temDados() {
            return this.datasets.some((ds) => (ds.data || []).some((v) => Number(v) > 0))
        },
        chartData() {
            return {
                labels: this.labels,
                datasets: this.datasets.map((ds) => ({
                    ...ds,
                    borderWidth: ds.borderWidth ?? 0,
                    maxBarThickness: ds.maxBarThickness ?? 36
                }))
            }
        },
        options() {
            return {
                indexAxis: this.horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: this.datasets.length > 1,
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12, font: { size: 12 } }
                    },
                    title: {
                        display: !!this.title,
                        text: this.title,
                        font: { size: 14, weight: '600' },
                        padding: { bottom: 10 }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        }
    }
}
</script>

<style scoped>
.aso-chart-wrap {
    position: relative;
    width: 100%;
}
</style>
