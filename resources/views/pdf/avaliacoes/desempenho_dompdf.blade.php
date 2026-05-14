<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Avaliação de desempenho</title>
    <style>
        * { box-sizing: border-box; }
        /*
         * Padrão único de fonte: o DomPDF usa serif (ex.: Times) como fallback em várias
         * células de tabela e em trechos com estilo herdado; forçamos DejaVu em tudo.
         */
        html, body, .pdf-doc-root, .pdf-doc-root * {
            font-family: DejaVu Sans, sans-serif !important;
        }
        @page {
            margin: 10mm 8mm 14mm 8mm;
            size: A4 portrait;
        }
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-size: 8.5pt;
            color: #222;
            display: flex;
            flex-direction: column;
        }
        /* Empurra o rodapé para o rodapé visual da última folha quando há espaço (flex; DomPDF 2+) */
        .pdf-doc-root {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            width: 100%;
        }
        #pdf-main {
            flex: 1 1 auto;
            min-height: 0;
            padding-bottom: 0;
        }
        /* Cabeçalho centralizado (coluna única), sem margens negativas */
        .pdf-cabecalho {
            margin: 0 0 5mm;
            padding: 0 0 3.5mm;
            border-bottom: 2px double #1e293b;
            page-break-inside: avoid;
            text-align: center;
        }
        .pdf-cabecalho__logoRow {
            margin: 0 auto 2.5mm;
        }
        .pdf-cabecalho__logoRow img {
            display: block;
            margin: 0 auto;
            max-height: 18mm;
            max-width: 52mm;
            width: auto;
            height: auto;
        }
        .pdf-cabecalho__logoRow--compact img {
            max-height: 12mm;
        }
        .pdf-cabecalho__razao {
            margin: 0 0 1.5mm;
            font-size: 11.5pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.25;
            text-align: center;
        }
        .pdf-cabecalho__meta {
            margin: 0 auto;
            max-width: 100%;
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.4;
            text-align: center;
        }
        h1.doc-titulo {
            text-align: center;
            font-size: 12pt;
            margin: 6px 0 12px;
            color: #20384a;
        }
        fieldset.dados-box {
            border: 1.5px solid #333;
            padding: 10px 12px;
            margin-bottom: 14px;
        }
        fieldset.dados-box legend {
            font-weight: bold;
            font-size: 9pt;
            padding: 0 6px;
        }
        table.dados-tbl { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 8.5pt; }
        table.dados-tbl td { padding: 4px 6px; vertical-align: top; font-size: 8.5pt; }
        table.dados-tbl .lbl { font-weight: bold; width: 28%; color: #333; font-size: 8.5pt; }
        .secao-titulo {
            font-size: 11pt;
            font-weight: bold;
            color: #20384a;
            margin: 16px 0 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }
        .secao-titulo--pdi {
            font-size: 10pt;
            font-weight: bold;
            color: #003755;
            margin: 14px 0 6px;
            border-bottom: 1px solid rgba(0, 55, 85, 0.12);
            padding-bottom: 3px;
        }
        .secao-lead {
            font-size: 8pt;
            color: #64748b;
            line-height: 1.45;
            margin: 0 0 10px;
        }
        /* av-table: regras explícitas (DomPDF ignora herança em th/td com frequência) */
        table.av-table,
        table.av-table caption,
        table.av-table thead,
        table.av-table tbody,
        table.av-table tr,
        table.av-table th,
        table.av-table td {
            font-family: DejaVu Sans, sans-serif !important;
            font-size: 8.5pt !important;
        }
        table.av-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.av-table th, table.av-table td {
            border: 1px solid #cfd8e0;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
        }
        table.av-table thead th {
            font-weight: bold !important;
        }
        table.av-table tbody td {
            font-weight: normal !important;
        }
        table.av-table thead th.comp {
            text-align: left;
            width: 38%;
            background-color: #2c3e50;
            color: #ffffff;
        }
        table.av-table thead th.avaliador-th {
            background-color: #34495e;
            color: #ffffff;
        }
        table.av-table thead th.med {
            background-color: #c0392b;
            color: #ffffff;
        }
        table.av-table tbody tr:nth-child(even) td { background-color: #f4f7f9; }
        table.av-table tbody tr:nth-child(odd) td { background-color: #ffffff; }
        table.av-table td.comp {
            text-align: left;
            font-weight: normal !important;
            color: #1a252f;
        }
        table.av-table .pill {
            font-family: DejaVu Sans, sans-serif !important;
            font-size: 8.5pt !important;
            font-weight: bold !important;
        }
        .pill {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 8.5pt;
            color: #ffffff;
            min-width: 28px;
            text-align: center;
        }
        .pill--nota {
            background-color: #3498db;
            border: 1px solid #2980b9;
        }
        .pill--media {
            background-color: #e74c3c;
            border: 1px solid #c0392b;
        }
        /* Resultado por competência — mesmo padrão visual do modal PDI */
        table.av-table.av-table--pdi-resultado thead th.comp,
        table.av-table.av-table--pdi-resultado thead th.avaliador-th,
        table.av-table.av-table--pdi-resultado thead th.med {
            background-color: #ffffff !important;
            color: #4e5a66 !important;
            border-top: none !important;
            border-bottom: 2px solid rgba(0, 55, 85, 0.08) !important;
            font-weight: bold !important;
            font-size: 8.2pt !important;
        }
        table.av-table.av-table--pdi-resultado thead th.comp {
            text-align: left !important;
            color: #4a5560 !important;
        }
        table.av-table.av-table--pdi-resultado thead th.med {
            color: #003755 !important;
        }
        table.av-table.av-table--pdi-resultado tbody tr:nth-child(even) td {
            background-color: #fbfcfd;
        }
        table.av-table.av-table--pdi-resultado tbody tr:nth-child(odd) td {
            background-color: #ffffff;
        }
        table.av-table.av-table--pdi-resultado td.comp {
            font-weight: 600 !important;
            color: #334754 !important;
            font-size: 8.6pt !important;
            line-height: 1.45;
        }
        .rc-nota {
            text-align: center;
            padding: 3px 4px 4px;
            border-radius: 8px;
            border: 1.5px solid #d6dde3;
            font-weight: bold;
            background: #f5f7f9;
            color: #4d5b67;
        }
        .rc-nota__num {
            font-size: 9pt;
            font-weight: bold;
            line-height: 1.05;
        }
        .rc-nota__lbl {
            font-size: 5.6pt;
            font-weight: bold;
            letter-spacing: 0.02em;
            line-height: 1.12;
            margin-top: 1px;
        }
        .rc-nota--media {
            box-shadow: inset 0 0 0 0.5px rgba(0, 55, 85, 0.08);
        }
        .rc-nota--5 { background: #effaf4; border-color: #8fd1b0; color: #0f6a46; }
        .rc-nota--4 { background: #eef8f4; border-color: #a7d8bf; color: #15714d; }
        .rc-nota--3 { background: #fbf8ec; border-color: #dcc36f; color: #7a6615; }
        .rc-nota--2 { background: #fcf2ec; border-color: #e7b28a; color: #a4541d; }
        .rc-nota--1 { background: #fdf0f0; border-color: #e29d9d; color: #9d2a2a; }
        .rc-nota--neutro { background: #f5f7f9; border-color: #d6dde3; color: #4d5b67; }
        .graf-item {
            margin: 0 auto 20px;
            text-align: center;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .graf-item h4 {
            margin: 12px 0 8px;
            font-size: 10.5pt;
            color: #2c3e50;
            font-weight: bold;
            text-transform: uppercase;
        }
        .graf-svg-wrap {
            display: block;
            margin: 8px auto 6px;
            text-align: center;
        }
        .graf-svg-wrap svg { display: block; margin: 0 auto; }
        .graf-media-linha {
            margin: 4px 0 16px;
            font-size: 10pt;
            font-weight: bold;
            color: #e74c3c;
        }
        .page-break { page-break-before: always; }
        .consideracao-bloco {
            border: 1px solid #ccc;
            margin-bottom: 10px;
            break-inside: avoid;
        }
        .consideracao-bloco h3 {
            margin: 0;
            padding: 8px 10px;
            font-size: 9pt;
            background-color: #34495e;
            color: #ffffff;
        }
        .consideracao-bloco .txt {
            padding: 10px;
            font-size: 9pt;
            line-height: 1.45;
            text-align: justify;
        }
        .graf-resumo table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5pt; }
        .graf-resumo th, .graf-resumo td { border: 1px solid #cfd8e0; padding: 5px 7px; }
        .graf-resumo thead th {
            background-color: #ecf0f1;
            color: #2c3e50;
            text-align: left;
            font-weight: bold;
        }
        .graf-resumo tbody tr:nth-child(even) td { background-color: #f8fafc; }
        /* Informativo da escala 1–5 (DomPDF: tabela mais previsível que flex) */
        .escala-informativo {
            border: 1.5px solid rgba(0, 55, 85, 0.12);
            border-radius: 8px;
            padding: 8px 10px 10px;
            margin: 0 0 14px;
            background: #fbfcfd;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .escala-informativo__titulo {
            font-size: 9pt;
            font-weight: bold;
            color: #003755;
            margin: 0 0 4px;
            letter-spacing: 0.02em;
        }
        .escala-informativo__intro {
            margin: 0 0 6px;
            font-size: 7.5pt;
            line-height: 1.45;
            color: #334754;
            text-align: justify;
        }
        table.escala-informativo-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            line-height: 1.42;
            color: #1a252f;
        }
        table.escala-informativo-tbl td {
            padding: 3px 0;
            vertical-align: top;
            border: none;
        }
        table.escala-informativo-tbl td.escala-informativo-tbl__nota {
            width: 34px;
            padding-right: 6px;
            text-align: center;
        }
        .rc-nota.rc-nota--mini {
            padding: 2px 4px 3px;
            min-width: 0;
        }
        .rc-nota.rc-nota--mini .rc-nota__num {
            font-size: 7.5pt;
            line-height: 1.1;
        }
        .rc-nota.rc-nota--mini .rc-nota__lbl {
            display: none;
        }
        .nota-final {
            text-align: center;
            margin: 14px 0;
            padding: 12px 14px;
            border: 1.5px solid rgba(0, 55, 85, 0.12);
            border-radius: 8px;
            background: #fbfcfd;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .nota-final__titulo {
            font-size: 10pt;
            font-weight: bold;
            color: #003755;
            margin: 0 0 8px;
            letter-spacing: 0.02em;
        }
        .nota-final__nota {
            display: inline-block;
            min-width: 72px;
            text-align: center;
        }
        .plano-bloco {
            border: 1.5px solid #34495e;
            margin-bottom: 12px;
            padding: 10px;
            break-inside: avoid;
        }
        .plano-bloco .lbl { font-weight: bold; color: #2c3e50; font-size: 8.5pt; }
        .plano-html { margin-top: 6px; line-height: 1.45; text-align: justify; font-size: 9pt; }
        .plano-html p { margin: 0 0 0.4em; }
        .termo-box {
            border: 1.5px solid #2c3e50;
            padding: 12px;
            margin-top: 12px;
            margin-bottom: 3mm;
            break-inside: avoid;
            font-size: 9.5pt;
            line-height: 1.5;
            text-align: justify;
        }
        /* Rodapé: discreto + margin-top auto cola na base do .pdf-doc-root quando sobra altura */
        .pdf-doc-footer {
            flex: 0 0 auto;
            margin-top: auto;
            margin-bottom: 0;
            padding: 2mm 0 0;
            border-top: 0.25pt solid #e2e8f0;
            background-color: transparent;
            font-size: 6.5pt;
            line-height: 1.35;
            color:rgb(82, 82, 83);
            text-align: center;
            font-weight: normal;
        }
        .pdf-doc-footer p {
            margin: 0;
            max-width: 100%;
            letter-spacing: 0.01em;
        }
    </style>
</head>
<body>
<div class="pdf-doc-root">
<div id="pdf-main">
    @php
        $empresaPdf = $dados['dados_empresa'] ?? [];
        if (! is_array($empresaPdf)) {
            $empresaPdf = json_decode(json_encode($empresaPdf), true) ?: [];
        }
        $razaoPdf = (string) ($empresaPdf['razao_social'] ?? '');
        $logoPdf = $empresaPdf['logo'] ?? null;
        $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
    @endphp
    <header class="pdf-cabecalho">
        @if(!empty($logoPdf))
            <div class="pdf-cabecalho__logoRow {{ $logoCompacto ? 'pdf-cabecalho__logoRow--compact' : '' }}">
                <img src="{{ $logoPdf }}" alt="{{ $razaoPdf }}">
            </div>
        @endif
        @if($razaoPdf !== '')
            <div class="pdf-cabecalho__razao">{{ $razaoPdf }}</div>
        @endif
        @if(!empty($empresaPdf['cnpj']) || !empty($empresaPdf['endereco_completo']))
            <div class="pdf-cabecalho__meta">
                @if(!empty($empresaPdf['cnpj']))
                    <span>CNPJ: {{ $empresaPdf['cnpj'] }}</span>
                @endif
                @if(!empty($empresaPdf['cnpj']) && !empty($empresaPdf['endereco_completo']))
                    <br>
                @endif
                @if(!empty($empresaPdf['endereco_completo']))
                    <span>{{ $empresaPdf['endereco_completo'] }}</span>
                @endif
            </div>
        @endif
    </header>

    <h1 class="doc-titulo">AVALIAÇÃO DE DESEMPENHO — {{ $dados['titulo_avaliacao'] ?? '' }}</h1>

    <fieldset class="dados-box">
        <legend>{{ $tipo_pj ? 'DADOS DO FORNECEDOR' : 'DADOS DO COLABORADOR' }}</legend>
        @php
            $df = json_decode(json_encode($dados['dados_do_funcionario'] ?? []), true) ?: [];
        @endphp
        @if(!empty($df['cnpj_lotacao']['razao_social']))
            <table class="dados-tbl">
                <tr>
                    <td class="lbl">Empresa</td>
                    <td>{{ $df['cnpj_lotacao']['razao_social'] ?? '' }}
                        @if(!empty($df['pertence_filial']))
                            (Filial)
                        @else
                            (Matriz)
                        @endif
                    </td>
                </tr>
            </table>
        @endif
        <table class="dados-tbl">
            <tr><td class="lbl">Nome completo</td><td>{{ $df['nome'] ?? '' }}</td></tr>
            @if(!$tipo_pj)
                <tr><td class="lbl">Matrícula</td><td>{{ $df['matricula'] ?? '' }}</td></tr>
                <tr><td class="lbl">Data de admissão</td><td>{{ $df['data_admissao'] ?? '' }}</td></tr>
                <tr><td class="lbl">Cargo</td><td>{{ $df['cargo'] ?? '' }}</td></tr>
            @endif
            <tr><td class="lbl">Centro de custo</td><td>{{ $df['centro_custo'] ?? '' }}</td></tr>
            <tr><td class="lbl">Área</td><td>{{ $df['area'] ?? '' }}</td></tr>
        </table>
    </fieldset>

    <div class="secao-titulo secao-titulo--pdi">Resultado por competência</div>
    <p class="secao-lead">Consolidado das notas informadas em cada etapa do fluxo, com a média calculada por critério.</p>

    <div class="escala-informativo">
        <div class="escala-informativo__titulo">Escala de avaliação (notas de 1 a 5)</div>
        <p class="escala-informativo__intro">
            <strong>Para esta avaliação, considere as atribuições abaixo, conforme a nota atribuída a cada critério.</strong>
        </p>
        <table class="escala-informativo-tbl" role="presentation">
            @foreach(\App\Support\AvaliacaoDesempenhoPdfViewData::itensEscalaInformativoDesempenhoPdf() as $ei)
                @php $sfxEscala = \App\Support\AvaliacaoDesempenhoPdfViewData::sufixoClasseNotaResultadoPdf($ei['nota']); @endphp
                <tr>
                    <td class="escala-informativo-tbl__nota">
                        <div class="rc-nota rc-nota--{{ $sfxEscala }} rc-nota--mini">
                            <div class="rc-nota__num">{{ $ei['nota'] }}</div>
                        </div>
                    </td>
                    <td>{{ $ei['texto'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    @foreach(collect($dados['result_topico_pai_agrupado'] ?? []) as $grupo)
        @php $linhas = collect($grupo); $head = $linhas->first(); @endphp
        @if($head && !empty($head['avaliadores']))
            <table class="av-table av-table--pdi-resultado">
                <thead>
                    <tr>
                        <th class="comp">{{ $head['topico_pai'] ?? '' }}</th>
                        @foreach($head['avaliadores'] as $idx => $avaliador)
                            <th class="avaliador-th">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::tituloEtapaFluxoColunaPdf((int) $idx, is_array($avaliador) ? $avaliador : [], $dados['fluxo_etapas'] ?? []) }}</th>
                        @endforeach
                        <th class="med">Média</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($linhas as $sub)
                        <tr>
                            <td class="comp">{{ $sub['subtopico'] ?? '' }}</td>
                            @foreach($sub['avaliadores'] ?? [] as $av)
                                @php $nAv = is_array($av) ? ($av['nota'] ?? null) : null; $sfxAv = \App\Support\AvaliacaoDesempenhoPdfViewData::sufixoClasseNotaResultadoPdf($nAv); @endphp
                                <td>
                                    <div class="rc-nota rc-nota--{{ $sfxAv }}">
                                        <div class="rc-nota__num">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::formatarDecimalNotaPdf($nAv) }}</div>
                                        <div class="rc-nota__lbl">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::textoNotaResultadoMaiusculoPdf($nAv) }}</div>
                                    </div>
                                </td>
                            @endforeach
                            @php $nMed = $sub['media'] ?? null; $sfxMed = \App\Support\AvaliacaoDesempenhoPdfViewData::sufixoClasseNotaResultadoPdf($nMed); @endphp
                            <td>
                                <div class="rc-nota rc-nota--media rc-nota--{{ $sfxMed }}">
                                    <div class="rc-nota__num">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::formatarDecimalNotaPdf($nMed) }}</div>
                                    <div class="rc-nota__lbl">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::textoNotaResultadoMaiusculoPdf($nMed) }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="secao-titulo">VISÃO GRÁFICA DO DESEMPENHO</div>
    <p style="text-align:center;font-size:8.5pt;color:#64748b;margin:0 0 14px">Radar por grupo de competências (escala 0 a 5), equivalente ao gráfico da avaliação na tela.</p>

    @forelse($radarCharts ?? [] as $chart)
        <div class="graf-item">
            <h4>{{ $chart['name'] }}</h4>
            <div class="graf-svg-wrap">
                <img
                    src="data:image/svg+xml;base64,{{ base64_encode($chart['svg']) }}"
                    width="{{ (int) ($chart['img_w'] ?? 340) }}"
                    height="{{ (int) ($chart['img_h'] ?? 300) }}"
                    alt=""
                    style="display:block;margin:0 auto;max-width:100%;"
                />
            </div>
            @php $mp = ($dados['resultado_topico_pai'] ?? [])[$chart['name']] ?? null; @endphp
            @if(is_array($mp) && array_key_exists('media', $mp))
                <p class="graf-media-linha">Média: {{ number_format((float) $mp['media'], 1, '.', '') }}</p>
            @endif
        </div>
    @empty
        <p style="text-align:center;color:#64748b">Não há dados para gráfico radar.</p>
    @endforelse

    @php
        $nFinal = $dados['nota_final'] ?? null;
        $sfxFinal = \App\Support\AvaliacaoDesempenhoPdfViewData::sufixoClasseNotaResultadoPdf($nFinal);
    @endphp
    <div class="nota-final">
        <div class="nota-final__titulo">NOTA FINAL</div>
        <div class="nota-final__nota rc-nota rc-nota--media rc-nota--{{ $sfxFinal }}">
            <div class="rc-nota__num">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::formatarDecimalNotaPdf($nFinal) }}</div>
            <div class="rc-nota__lbl">{{ \App\Support\AvaliacaoDesempenhoPdfViewData::textoNotaResultadoMaiusculoPdf($nFinal) }}</div>
        </div>
    </div>


    @if(count($comentariosPdf) > 0)
        {{-- <div class="page-break"></div> --}}
        <div class="secao-titulo">COMENTÁRIOS POR ETAPA</div>
        @foreach($comentariosPdf as $row)
            @php $av = $row['avaliador']; $idx = $row['indice']; @endphp
            <div class="consideracao-bloco">
                <h3>{{ \App\Support\AvaliacaoDesempenhoPdfViewData::tituloConsideracoesPdf($idx, $av, $dados['fluxo_etapas'] ?? []) }}</h3>
                <div class="txt">{{ $av['comentario'] ?? '' }}</div>
            </div>
        @endforeach
    @endif


    <div class="page-break"></div>
    <div class="secao-titulo">OPORTUNIDADES DE MELHORIA / PLANO DE AÇÃO</div>
    @forelse($dados['planos_acoes'] ?? [] as $plano)
        @php
            $rt = $resultTopicoPorId->get($plano->topico_id);
        @endphp
        <div class="plano-bloco">
            <div><span class="lbl">COMPETÊNCIA/DESEMPENHO:</span>
                {{ $rt['subtopico'] ?? optional($plano->Topico)->topico ?? '' }}
                @if($rt && isset($rt['media']))
                    <span style="color:#c0392b;font-weight:bold"> (Média: {{ number_format((float) $rt['media'], 1, '.', '') }})</span>
                @endif
            </div>
            <div style="margin-top:8px"><span class="lbl">PLANO DE AÇÃO:</span></div>
            <div class="plano-html">{!! $plano->plano_de_acao !!}</div>
            <div style="margin-top:8px"><span class="lbl">PRAZO:</span> {{ $plano->inicio }} à {{ $plano->termino }}</div>
        </div>
    @empty
        <p>Nenhum plano de ação registrado.</p>
    @endforelse

    @if(!$tipo_pj)
        <div class="termo-box">
            <p>
                Eu, _________________________________,
                comprometo-me a realizar as ações acima listadas até _____/_____/__________
                a fim de melhorar meu desempenho e contribuir com a empresa da melhor forma.
            </p>
            <p style="text-align:center;margin-top:14px">São Luís-MA, _______ de _________________ de _________.</p>
            <p style="margin-top:20px;text-align:center">_________________________<br><small>Assinatura do Colaborador</small></p>
            <p style="margin-top:16px;text-align:center">_________________________<br><small>Assinatura do Gestor</small></p>
        </div>
    @endif

</div>
<footer class="pdf-doc-footer">
    <p>
        Esse documento foi gerado automaticamente por {{ $dados['solicitante'] ?? '' }}:<br>
        Sistema Integrado BPIN by MyBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        &agrave;s {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
</footer>
</div>
</body>
</html>
