<template>
    <Radar :data="chartData" :options="options" />
</template>

<script>
import { Radar } from 'vue-chartjs'
import { Chart as ChartJS, RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from 'chart.js'

ChartJS.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

export default {
    name: 'RadarChart',
    components: {
        Radar
    },
    props: {
        chartData: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            options: {
                responsive: true,
                animation: {
                    duration: 2
                },
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: 'rgb(255, 99, 132)'
                        }
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                scales: {
                    r: {
                        min: 0,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        }
    }
}
</script>
