@extends('layouts.pdf_filial')
@section('title','Avaliacao Desempenho')
@section('conteudo')
<div style="margin-top: 20px; margin-bottom: 20px">
    {{-- <p class="observacao">OBS: Se o download nao iniciar automaticamente clique no botão.</p>--}}
    {{-- <button id="printPageButton" onClick="window.print();">IMPRIMIR</button>--}}
</div>

<div id="app">
    <div style="margin-top: 30px;">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div class="pdf-page-wrap">

        <!-- DADOS DO COLABORADOR MELHORADOS -->
        <div class="dados-funcionario">
            <fieldset>
                <legend>
                    {{ $tipo_pj ? 'DADOS DO FORNECEDOR' : 'DADOS DO COLABORADOR' }}
                </legend>


                <!-- Empresa/CNPJ -->
                <div class="dados-row" v-if="formAvaliarFinal.dados_do_funcionario.cnpj_lotacao">
                    <div class="dados-item full-width">
                        <span class="label">EMPRESA:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.cnpj_lotacao.razao_social }}</span>
                        <span class="badge">(@{{ formAvaliarFinal.dados_do_funcionario.pertence_filial ? 'Filial' : 'Matriz' }})</span>
                    </div>
                </div>

                <!-- Nome completo -->
                <div class="dados-row">
                    <div class="dados-item full-width">
                        <span class="label">NOME COMPLETO:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.nome }}</span>
                    </div>
                </div>

                <!-- Matrícula e Admissão -->
                @if(!$tipo_pj)
                <div class="dados-row">
                    <div class="dados-item">
                        <span class="label">MATRÍCULA:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.matricula }}</span>
                    </div>
                    <div class="dados-item">
                        <span class="label">DATA DE ADMISSÃO:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.data_admissao }}</span>
                    </div>
                </div>
                @endif

                <!-- Cargo, Centro de Custo e Área -->
                <div class="dados-row">
                    @if(!$tipo_pj)
                    <div class="dados-item">
                        <span class="label">CARGO:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.cargo }}</span>
                    </div>
                    @endif
                    <div class="dados-item">
                        <span class="label">CENTRO DE CUSTO:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.centro_custo }}</span>
                    </div>
                </div>

                <div class="dados-row">
                    <div class="dados-item full-width">
                        <span class="label">ÁREA:</span>
                        <span class="value">@{{ formAvaliarFinal.dados_do_funcionario.area }}</span>
                    </div>
                </div>
            </fieldset>
        </div>

        <!-- TABELAS DE AVALIAÇÃO MELHORADAS -->
        <div class="avaliacao-tabelas">
            <div class="secao-header">
                <h2>RESULTADO POR COMPETÊNCIA</h2>
                <p>Consolidado das notas registradas em cada etapa do fluxo, com média por critério avaliado.</p>
            </div>
            <div v-for="(item, index) in formAvaliarFinal.result_topico_pai_agrupado" :key="index"
                class="tabela-grupo">
                <table class="table-avaliacao">
                    <thead>
                        <tr class="header-principal">
                            <th class="competencia-header">@{{ item[index].topico_pai }}</th>
                            <th class="avaliador-header" v-for="(avaliador, id) in item[0].avaliadores"
                                :key="avaliador.id">
                                @{{ tituloEtapaFluxoPdf(id, avaliador) }}
                            </th>
                            <th class="media-header">MÉDIA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sub in item" class="linha-avaliacao">
                            <td class="competencia-desc">@{{ sub.subtopico }}</td>
                            <td class="nota-cell" v-for="avaliador in sub.avaliadores">
                                <div class="nota-display">
                                    @{{ casasDecimais(avaliador.nota) }}
                                </div>
                            </td>
                            <td class="media-cell">
                                <div class="media-display">
                                    @{{ casasDecimais(sub.media) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

            <!-- QUEBRA DE PÁGINA PARA COMENTÁRIOS -->
            <div style="page-break-before: always"></div>

            <!-- CONSIDERAÇÕES DOS AVALIADORES -->
            <div class="consideracoes-section" v-if="comentariosOrdenados().length > 0">
                <div class="consideracoes-section__header">
                    <h2>COMENTÁRIOS POR ETAPA</h2>
                <p>Registros realizados ao longo do fluxo da avaliação, organizados conforme a ordem das etapas.</p>
            </div>

            <div v-for="(avaliador,id) in comentariosOrdenados()"
                :key="`${avaliador.id}-${id}`" class="consideracao-item">
                <div class="consideracao-header">
                    <h3>@{{ tituloConsideracoesPdf(avaliador.__indice, avaliador) }}</h3>
                </div>
                <div class="consideracao-content">
                    <p class="texto">@{{ avaliador.comentario }}</p>
                </div>
            </div>
        </div>

        <!-- QUEBRA DE PÁGINA PARA GRÁFICOS -->
        <div style="page-break-before: always"></div>

        <!-- GRÁFICOS RADAR -->
        <div class="graficos-section">
            <div class="secao-header secao-header--center">
                <h2>VISÃO GRÁFICA DO DESEMPENHO</h2>
                <p>Leitura consolidada por grupo de competências.</p>
            </div>
            <div v-for="(chart, index) in formAvaliarFinal.resultChart" :key="index" class="grafico-item">
                <h2 class="grafico-titulo">@{{ chart.name }}</h2>
                <div class="grafico-container">
                    <radarchart :id="chart.name" :chart-data="chart.data"></radarchart>
                </div>
                <div class="grafico-media">
                    Média: @{{ casasDecimais(formAvaliarFinal.resultado_topico_pai[chart.name].media) }}
                </div>
            </div>

            <div class="nota-final-section">
                <h1>NOTA FINAL: @{{ casasDecimais(formAvaliarFinal.nota_final) }}</h1>
            </div>
        </div>

        <!-- QUEBRA DE PÁGINA PARA PLANO DE AÇÃO -->
        <div style="page-break-before: always"></div>

        <!-- PLANO DE AÇÃO MELHORADO -->
        <div class="plano-acao-section">
            <div class="secao-header secao-header--center">
                <h2>OPORTUNIDADES DE MELHORIA / PLANO DE AÇÃO</h2>
                <p>Ações sugeridas para desenvolvimento e acompanhamento.</p>
            </div>

            <div v-for="(item, index) in formAvaliarFinal.planos_acoes" :key="index" class="plano-item">
                <div class="plano-content">
                    <div class="competencia-info">
                        <span class="label-plano">COMPETÊNCIA/DESEMPENHO:</span>
                        <span class="competencia-nome">@{{ formAvaliarFinal.result_topico[item.topico_id].subtopico}}</span>
                        <span class="media-competencia">(Média: @{{ casasDecimais(formAvaliarFinal.result_topico[item.topico_id].media) }})</span>
                    </div>

                    <div class="plano-info">
                        <span class="label-plano">PLANO DE AÇÃO:</span>
                        <div class="plano-texto" v-html="item.plano_de_acao"></div>
                    </div>

                    <div class="prazo-info">
                        <span class="label-plano">PRAZO:</span>
                        <span class="prazo-periodo">@{{ item.inicio }} à @{{ item.termino }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TERMO DE COMPROMISSO -->
        @if(!$tipo_pj)
        <div class="termo-compromisso">
            <div class="termo-content">
                <p class="termo-texto">
                    Eu, <span class="linha-assinatura"></span>,
                    comprometo-me a realizar as ações acima listadas até <span class="data-compromisso">_____/_____/__________</span>
                    a fim de melhorar meu desempenho e contribuir com a empresa da melhor forma.
                </p>

                <div class="assinaturas">
                    <div class="data-local">
                        São Luís-MA, _______ de _________________ de _________.
                    </div>

                    <div class="assinatura-box">
                        <div class="linha-assinatura-final"></div>
                        <div class="label-assinatura">Assinatura do Colaborador</div>
                    </div>

                    <div class="assinatura-box">
                        <div class="linha-assinatura-final"></div>
                        <div class="label-assinatura">Assinatura do Gestor</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div style="position:relative; bottom: 5px">
        @include('layouts.rodapePdfFilialJob')
    </div>
</div>
@stop

@push('style')
<style>
    /* Reset e configurações gerais */
    * {
        box-sizing: border-box;
    }

    /* Configurações de impressão */
    @page {
        margin: 12mm 10mm 12mm 10mm;
        size: A4;
    }

    #app {
        padding: 0;
        width: 100%;
        overflow: hidden;
    }

    .pdf-page-wrap {
        width: 93%;
        max-width: 93%;
        margin: 0 auto;
        overflow: hidden;
    }

    /* Dados do Funcionário */
    .dados-funcionario {
        margin-bottom: 22px;
    }

    .dados-funcionario fieldset {
        border: 2px solid #333;
        border-radius: 8px;
        padding: 18px 18px 14px;
        background-color: #f9fbfc;
    }

    .dados-funcionario legend {
        font-weight: bold;
        font-size: 14px;
        padding: 0 10px;
        color: #333;
        background-color: white;
        border: 1px solid #333;
        border-radius: 4px;
    }

    .dados-row {
        display: flex;
        margin-bottom: 12px;
        gap: 20px;
        align-items: center;
    }

    .dados-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dados-item.full-width {
        flex: 100%;
    }

    .dados-item .label {
        font-weight: bold;
        font-size: 11px;
        color: #444;
        white-space: nowrap;
        min-width: 120px;
    }

    .dados-item .value {
        font-size: 12px;
        color: #000;
        flex: 1;
    }

    .badge {
        background-color: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: bold;
    }

    /* Tabelas de Avaliação */
    .avaliacao-tabelas {
        margin-top: 24px;
        margin-bottom: 26px;
    }

    .secao-header {
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e6edf2;
    }

    .secao-header--center {
        text-align: center;
    }

    .secao-header h2 {
        margin: 0 0 6px;
        font-size: 18px;
        font-weight: 800;
        color: #20384a;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .secao-header p {
        margin: 0;
        font-size: 12px;
        color: #6b7b87;
        line-height: 1.45;
    }

    .tabela-grupo {
        margin-bottom: 20px;
        break-inside: avoid;
    }

    .table-avaliacao {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 9px;
        box-shadow: none;
        background: white;
        border-radius: 10px;
        overflow: hidden;
    }

    .table-avaliacao thead tr.header-principal {
        background: linear-gradient(135deg, #2c3e50, #34495e);
        color: white;
    }

    .table-avaliacao th {
        padding: 9px 5px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #ddd;
        font-size: 8px;
        line-height: 1.25;
    }

    .competencia-header {
        width: 49%;
        text-align: left !important;
        background: linear-gradient(135deg, #1a252f, #2c3e50);
    }

    .avaliador-header {
        width: 11.5%;
    }

    .media-header {
        width: 16.5%;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .table-avaliacao tbody tr {
        border-bottom: 1px solid #ddd;
    }

    .table-avaliacao tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table-avaliacao tbody tr:hover {
        background-color: #e3f2fd;
    }

    .competencia-desc {
        padding: 8px 9px;
        text-align: left;
        line-height: 1.45;
        font-weight: 500;
        font-size: 9px;
        border-right: 2px solid #e9ecef;
        word-break: break-word;
    }

    .nota-cell,
    .media-cell {
        text-align: center;
        padding: 5px 3px;
        border-left: 1px solid #ddd;
    }

    .nota-display,
    .media-display {
        display: inline-block;
        padding: 4px 7px;
        border-radius: 14px;
        font-weight: bold;
        min-width: 30px;
        max-width: 100%;
    }

    .nota-display {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        border: 1px solid #2980b9;
    }

    .media-display {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        border: 1px solid #c0392b;
        font-size: 10px;
    }

    /* Considerações */
    .consideracoes-section {
        margin: 30px 0 20px;
    }

    .consideracoes-section__header {
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e7edf3;
    }

    .consideracoes-section__header h2 {
        margin: 0 0 6px;
        font-size: 20px;
        font-weight: 800;
        color: #233a4d;
        letter-spacing: 0.02em;
    }

    .consideracoes-section__header p {
        margin: 0;
        font-size: 12px;
        color: #647482;
        line-height: 1.5;
    }

    .consideracao-item {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 12px;
        overflow: hidden;
        break-inside: avoid;
        box-shadow: 0 2px 10px rgba(17, 34, 51, 0.04);
    }

    .consideracao-header {
        background: linear-gradient(135deg, #34495e, #3d556d);
        color: white;
        padding: 14px 18px;
    }

    .consideracao-header h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .consideracao-content {
        padding: 20px 18px;
        background: white;
        border-top: 1px solid rgba(0, 0, 0, 0.04);
    }

    /* Gráficos */
    .graficos-section {
        text-align: center;
        margin: 20px 0 30px;
    }

    .grafico-item {
        margin-bottom: 40px;
        break-inside: avoid;
    }

    .grafico-titulo {
        font-size: 18px;
        color: #2c3e50;
        margin-bottom: 20px;
        text-transform: uppercase;
        font-weight: bold;
    }

    .grafico-container {
        margin: 20px 0;
    }

    canvas {
        max-width: 450px;
        margin: 0 auto;
        border: 2px solid #ecf0f1;
        border-radius: 8px;
    }

    .grafico-media {
        font-size: 16px;
        font-weight: bold;
        color: #e74c3c;
        margin-top: 15px;
    }

    .nota-final-section h1 {
        font-size: 24px;
        color: #27ae60;
        background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
        padding: 20px;
        border-radius: 10px;
        border: 3px solid #27ae60;
        margin-top: 40px;
        text-transform: uppercase;
    }

    /* Plano de Ação */
    .plano-acao-section {
        margin: 25px 0;
    }

    .plano-item {
        margin-bottom: 20px;
        border: 2px solid #34495e;
        border-radius: 8px;
        overflow: hidden;
        break-inside: avoid;
    }

    .plano-content {
        padding: 20px;
        background: white;
    }

    .competencia-info,
    .plano-info,
    .prazo-info {
        margin-bottom: 15px;
    }

    .label-plano {
        font-weight: bold;
        color: #2c3e50;
        font-size: 12px;
        display: inline-block;
    }

    .competencia-nome {
        color: #000;
        font-weight: 500;
    }

    .media-competencia {
        color: #e74c3c;
        font-weight: bold;
        font-size: 11px;
    }

    .plano-texto {
        margin-top: 8px;
        line-height: 1.5;
        text-align: justify;
        color: #444;
    }

    .plano-texto strong,
    .plano-texto b {
        font-weight: 700;
    }

    .plano-texto em,
    .plano-texto i {
        font-style: italic;
    }

    .plano-texto u {
        text-decoration: underline;
    }

    .plano-texto p {
        margin: 0 0 0.5em;
    }

    .plano-texto p:last-child {
        margin-bottom: 0;
    }

    .prazo-periodo {
        color: #27ae60;
        font-weight: bold;
    }

    /* Termo de Compromisso */
    .termo-compromisso {
        margin-top: 26px;
        border: 2px solid #2c3e50;
        border-radius: 8px;
        background: #f8f9fa;
        break-inside: avoid;
    }

    .termo-content {
        padding: 25px;
    }

    .termo-texto {
        text-align: justify;
        line-height: 1.6;
        font-size: 13px;
        margin-bottom: 30px;
        color: #2c3e50;
    }

    .linha-assinatura {
        display: inline-block;
        width: 300px;
        border-bottom: 1px solid #000;
        margin: 0 5px;
    }

    .data-compromisso {
        border-bottom: 1px solid #000;
        padding: 0 5px;
    }

    .assinaturas {
        margin-top: 40px;
    }

    .data-local {
        text-align: center;
        font-size: 13px;
        margin-bottom: 40px;
        color: #2c3e50;
    }

    .assinatura-box {
        margin: 30px 0;
        text-align: center;
    }

    .linha-assinatura-final {
        width: 300px;
        height: 1px;
        background-color: #000;
        margin: 0 auto 8px;
    }

    .label-assinatura {
        font-size: 12px;
        color: #2c3e50;
        font-weight: 500;
    }

    /* Utilitários */
    .text-center {
        text-align: center !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .texto {
        text-align: justify;
        text-justify: inter-word;
        font-size: 13px;
        line-height: 1.4;
        color: #444;
    }

    /* Configurações específicas para impressão */
    @media print {
        .dados-funcionario fieldset {
            background-color: #f9f9f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table-avaliacao thead tr.header-principal {
            background: #2c3e50 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .nota-display {
            background: #3498db !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .media-display {
            background: #e74c3c !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .consideracao-header {
            background: #34495e !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge {
            background-color: #007bff !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Evitar quebras de página inadequadas */
        .tabela-grupo,
        .consideracao-item,
        .plano-item,
        .termo-compromisso {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Ocultar elementos desnecessários na impressão */
        #printPageButton,
        .observacao {
            display: none;
        }

        #app {
            display: block;
        }
    }
</style>
@endpush

@push('script')
<script>
    window.dados = <?php echo json_encode($dados); ?>
</script>
<script src="{{ mix('js/app.js') }}"></script>
<script src="{{mix('js/g/impressao/avaliacao/app.js')}}"></script>
@endpush
