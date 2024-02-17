@extends('layouts.pdf_filial')
@section('title','Avaliacao Desempenho')
@section('conteudo')
    <div style="margin-top: 20px; margin-bottom: 20px">
        {{--        <p class="observacao">OBS: Se o download nao iniciar automaticamente clique no botão.</p>--}}
        {{--        <button id="printPageButton" onClick="window.print();">IMPRIMIR</button>--}}
    </div>

    <div id="app">
        @include('layouts.cabecalioFilialEmpresaJob')
        <div style="width: 97%; margin-top: 15px">
            <fieldset>
                <legend>DADOS DO COLABORADOR</legend>
                <div class="row mb-3" v-if="formAvaliarFinal.dados_do_funcionario.cnpj_lotacao">
                    <div class="col-12"><strong>CNPJ:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.cnpj_lotacao.razao_social }}
                        (@{{ formAvaliarFinal.dados_do_funcionario.pertence_filial ? 'Filial' : 'Matriz' }})
                    </div>
                </div>
                <div class="row">
                    <div><strong>Nome:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.nome }}
                    </div>
                    <div class="col-12 col-lg-4"><strong>Matrícula:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.matricula }}
                    </div>
                    <div class="col-12 col-lg-4"><strong>Admissão:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.data_admissao }}
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 col-lg-4"><strong>Cargo:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.cargo }}
                    </div>
                    <div class="col-12 col-lg-4"><strong>Centro de Custo:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.centro_custo }}
                    </div>
                    <div class="col-12 col-lg-4"><strong>Área:</strong>
                        @{{ formAvaliarFinal.dados_do_funcionario.area }}
                    </div>
                </div>


            </fieldset>

            <table class="table2" style="margin-top: 20px"
                   v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado"
                   :key="index">
                <thead>
                <tr>
                    <th style="text-align: left">@{{ item[index].topico_pai }}</th>
                    <th class="text-center" v-for="(avaliador, id) in item[0].avaliadores" :key="avaliador.id">
                        @{{ avaliador.origem === 'Funcionario' ? 'AUTOAVALIAÇÃO' : 'AVALIADOR ' + (id + 1) }}
                    </th>
                    <th class="text-center">MÉDIA</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="sub in item">
                    <td style="width: 33%; line-height: 16px;">@{{ sub.subtopico }}</td>
                    <td class="text-center" style="width: 15%" v-for="avaliador in sub.avaliadores">
                        <input type="number" class="form-control form-control-sm text-center"
                               style="padding-left: 10px;"
                               readonly="readonly" min="0" max="5"
                               step="0.1" :value="avaliador.nota | casasDecimais">
                    </td>
                    <td style="width: 7%" class="text-center">
                        <input type="number" class="form-control form-control-sm text-center"
                               style="padding-left: 10px;"
                               readonly="readonly" min="0" max="5"
                               step="0.1" :value="sub.media | casasDecimais">
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="table" v-if="formAvaliarFinal.result_topico_pai_agrupado.length > 0">
                <tr v-for="(avaliador,id) in formAvaliarFinal.result_topico_pai_agrupado[0][0].avaliadores"
                    :key="avaliador.id">
                    <td
                        style="border-bottom: 1px solid black"
                    >
                        <h3>
                            CONSIDERAÇÕES @{{ avaliador.origem === 'Funcionario' ? 'DA AUTOAVALIAÇÃO' : 'DO AVALIADOR '
                            + (id+1) }}
                        </h3>
                        <p class="texto">
                            @{{ avaliador.comentario }}
                        </p>
                    </td>
                </tr>
            </table>

            <div style="page-break-before: always"></div>

            <div v-for="(chart, index) in formAvaliarFinal.resultChart" :key="index" class="col-md-4">
                <h1 class="text-center">@{{ chart.name }}</h1>
                <div>
                    <radarchart :id="chart.name" :chart-data="chart.data"></radarchart>
                </div>
                <h1 class="text-center">
                    Média: @{{ formAvaliarFinal.resultado_topico_pai[chart.name].media | casasDecimais }}
                </h1>
            </div>
            <div class="col-md-12 text-center">
                <h1>Nota final: @{{ formAvaliarFinal.nota_final | casasDecimais }}</h1>
            </div>

            <div style="page-break-before: always"></div>

            <h2>OPORTUNIDADES DE MELHORIA / PLANO DE AÇÃO</h2>
            <table class="table" style="width: 100%; background:white;"
                   v-for="(item, index) in formAvaliarFinal.planos_acoes" :key="index">
                <tr>
                    <td
                        style="border-bottom: 1px solid black">
                        <p class="texto">
                            <strong>COMPETÊNCIA/DESEMPENHO:</strong> @{{
                            formAvaliarFinal.result_topico[item.topico_id].subtopico}}
                            <span class="text-danger">(Média: @{{ formAvaliarFinal.result_topico[item.topico_id].media | casasDecimais }})</span>
                        </p>

                        <p class="texto">
                            <strong>PLANO DE AÇÃO:</strong> <br>
                            @{{ item.plano_de_acao }}
                        </p>

                        <p class="texto">
                            <strong>PRAZO:</strong> @{{ item.inicio }} à @{{ item.termino }}
                        </p>

                        <p class="my-3 texto " v-if="item.topico_id">
                        </p>
                    </td>
                </tr>
            </table>

            <div style="border: 1px solid black; margin-top: 30px; padding: 20px">

                <p class="texto" style=" line-height: 30px">
                    Eu,___________________________________________________________________________,
                    comprometo-me a realizar as ações acima listada até _____/_____/__________ a fim de melhorar
                    meu desempenho e contribuir com a empresa da melhor forma.
                </p>

                <div class="texto text-center" style="margin-top: 50px;">
                    São Luís-MA, _______ de _________________ de _________.
                </div>

                <div class="texto text-center" style="margin-top: 30px;">
                    _________________________________________________________ <br>
                    Assinatura do colaborador
                </div>

                <div class="texto text-center" style="margin-top: 30px;">
                    _________________________________________________________ <br>
                    Assinatura do Gestor
                </div>

            </div>

        </div>

        <div style="position:relative; bottom: 5px">
            @include('layouts.rodapePdfFilialJob')
        </div>
    </div>
@stop
@push('style')
    <style>
        canvas {
            max-width: 450px;
            margin: 0 auto;
        }

        #app > table {
            width: 100%;
        }

        fieldset {
            line-height: 18px;
            font-size: 11px;
        }

        .text-center {
            text-align: center !important;
        }

        .table2 tr td {
            border-bottom: 1px solid #000;
        }

        .texto {
            text-align: justify;
            text-justify: inter-word;
            font-size: 13px;
            line-height: 18px;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        #printPageButton {
            padding: 5px 13px 5px 13px;
            cursor: pointer;
            margin-left: 20px;
            margin-bottom: 30px;

            background-color: #184056;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 1s;
        }

        #printPageButton:hover {
            background-color: #045588;
        }

        .observacao {
            padding: 5px 13px 5px 13px;
            width: 600px;
            margin-top: 20px;
            margin-left: 20px;
            background-color: #ffe5d2;
            color: #ff5c15;
            border: #ff5c15;
            font-size: 12px;
            border-radius: 5px;
        }

        @page {
            margin: 0cm 0cm 0cm 0cm;
            margin-top: 15px;
            margin-left: 10px;
            margin-bottom: 10px;
        }

        @media print {
            #app {
                display: block;
            }

            #printPageButton, .observacao {
                display: none;
            }
        }
    </style>
@endpush
@push('script')
    <script> window.dados = {!! json_encode($dados) !!}</script>
    <script src="{{ mix('js/app.js') }}"></script>
    <script src="{{mix('js/g/impressao/avaliacao/app.js')}}"></script>
@endpush
