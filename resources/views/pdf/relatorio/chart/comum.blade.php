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
        const app = Vue.createApp({})

        app.component('donutchart', {
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
                    grupos : [],
                }
            },

            mounted() {
                this.renderChart({
                    labels: [
                        'CURRÍCULOS RECEBIDOS',
                        'CURRÍCULOS ABERTOS',
                        'CURRÍCULOS SELECIONADOS',
                        'LINKS DE PROVAS ENVIADOS',
                        'APROVADOS PROVA CONHECIMENTOS GERAIS E AGENDADOS PROVA INFORMÁTICA',
                        'PRESENTES EM PROVA DE INFORMÁTICA',
                        'APROVADOS PROVA DE INFORMÁTICA',
                        'APROVADOS EM ENTREVISTA INDIVIDUAL',
                        'APROVADOS RH 55',
                        'APROVADOS GESTOR 55'
                    ],
                    datasets: [
                        {
                            label: 'Curriculos',
                            data:
                                [
                                    {{$curriculoQnt}},
                                    {{$curriculoAbertos}},
                                    {{$curriculosSelecionados}},
                                    {{$curriculosSelecionados}},
                                    {{$aprovadosConhecimento}},
                                    {{$presentesProvaInformatica}},
                                    {{$aprovadosProvaInformatica}},
                                    {{$aprovadosEntrevistaIndividual}},
                                    {{$aprovadosEntrevistaRh}},
                                    {{$aprovadosGestor}}
                                ]
                            ,
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

        })

        if (window.registerGlobals) {
            window.registerGlobals(app)
        }
        app.mount('#app')
    </script>
@endpush
