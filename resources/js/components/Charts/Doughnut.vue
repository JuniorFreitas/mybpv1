<template>
    <div class="aso-chart-wrap">
        <Doughnut v-if="temDados" :data="chartData" :options="options" />
        <p v-else class="text-muted text-center mb-0 py-4">Sem dados para o gráfico</p>
    </div>
</template>

<script>
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

export default {
    name: 'ChartsDoughnut',
    components: { Doughnut },
    props: {
        labels: { type: Array, default: () => [] },
        values: { type: Array, default: () => [] },
        colors: { type: Array, default: () => [] },
        title: { type: String, default: '' }
    },
    computed: {
        temDados() {
            return this.values.some((v) => Number(v) > 0)
        },
        chartData() {
            return {
                labels: this.labels,
                datasets: [
                    {
                        data: this.values,
                        backgroundColor: this.colors.length
                            ? this.colors
                            : ['#dc3545', '#fd7e14', '#28a745', '#6c757d'],
                        borderWidth: 1,
                        borderColor: '#fff'
                    }
                ]
            }
        },
        options() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 14, font: { size: 12 } }
                    },
                    title: {
                        display: !!this.title,
                        text: this.title,
                        font: { size: 14, weight: '600' },
                        padding: { bottom: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + Number(b), 0) || 1
                                const val = Number(ctx.raw) || 0
                                const pct = ((val / total) * 100).toFixed(1)
                                return ` ${ctx.label}: ${val} (${pct}%)`
                            }
                        }
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
    height: 280px;
}
</style>
