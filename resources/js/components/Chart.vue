<template>
    <div>
        <donutchart></donutchart>
    </div>
</template>

<script>
    export default {
        extends: VueChartJs.Doughnut,
        props: {
            labels: {
                type: Array,
                required: true,
            },
            grupos: {
                type: Array,
                required: true,
            }
        },
        mounted() {
            this.renderChart({
                labels: this.labels,
                datasets: [
                    {
                        label: 'Curriculos',
                        data: this.grupos,
                        backgroundColor: this.cores(11)
                    }
                ]
            }, {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: "bottom",
                    fullWidth: true
                }
            })
        },
        methods: {
            cores(qnt) {
                let cor = []
                for (var i = 0; i <= qnt; i++) {
                    cor.push(this.colors[i])
                    // cor.push(this.colors[this.colors.length + Math.floor((qnt - this.colors.length) * Math.random())])
                }
                return cor;
            }
        }

    }
</script>

<style scoped>

</style>
