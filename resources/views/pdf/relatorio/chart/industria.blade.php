@extends('layouts.sistema')
@section('title', 'DASHBOARD')
@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="text-center">{{$cliente}}</h4>
            <donutchart></donutchart>
        </div>
    </div>
@stop

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
    <script src="https://unpkg.com/vue-chartjs@3.4.0/dist/vue-chartjs.js"></script>
    <script>
        Vue.component('donutchart', {
            extends: VueChartJs.Doughnut,
            data() {
                return {
                    colors: [
                        //    Paletas azuis
                        "#184056", "#1E3B61", "#1E3161", "#1F4692", "#1F5992", "#0F7ACC", "#1096D4", "#0F56CC", "#106AD4", "#1E2B61", "#1F3992", "#0F3ECC", "#104CD4",
                        // Paletas vermelhas
                        "#6a040f", "#9d0208", "#d00000", "#dc2f02", "#f48c06", "#faa307",
                    ],
                    labels: [],
                    grupos: [],
                }
            },

            mounted() {
                this.renderChart({
                    labels: [
                        'CURRÍCULOS RECEBIDOS',
                        'CURRÍCULOS ABERTOS',
                        'CURRÍCULOS SELECIONADOS',
                        'APROVADOS PARECER RH',
                        'ATENDE ROTA',
                        'APROVADOS EM ENTREVISTA TÉCNICA',
                        'APROVADOS EM TESTE PRÁTICO',
                        'RESULTADO INTEGRADO',
                        'TREINADOS',
                    ],
                    datasets: [
                        {
                            label: 'Curriculos',
                            data:
                                [
                                    {{$curriculoQnt}},
                                    {{$curriculoAbertos}},
                                    {{$curriculosSelecionados}},
                                    {{$aprovadosParecerRH}},
                                    {{$aprovadosRota}},
                                    {{$aprovadosTecnica}},
                                    {{$aprovadosTestePratico}},
                                    {{$resultadoIntegrado}},
                                    {{$treinados}},
                                ]
                            ,
                            backgroundColor: this.cores(9)
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

        })
        const app = new Vue({
            el: '#app',
        });
    </script>

@endpush
