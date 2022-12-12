<script>
import { Radar, mixins } from "vue-chartjs";
const { reactiveProp } = mixins;

export default {
    extends: Radar,
    mixins: [reactiveProp],
    data: () => ({
        options: {
            responsive: true,
            animation: {
                duration: 2,
            },
            legend: {
                display: false,
                labels: {
                    fontColor: 'rgb(255, 99, 132)'
                }
            },
            tooltips: {
                enabled: false,
            },
            scale: {
                ticks: {
                    beginAtZero: true,
                    max: 5,
                    min: 0,
                    stepSize: 1
                }
            },
        },
    }),
    mounted() {
        // this.chartData is created in the mixin.
        // If you want to pass options please create a local options object
        this.renderChart(this.chartData, this.options);
    },
};
</script>
