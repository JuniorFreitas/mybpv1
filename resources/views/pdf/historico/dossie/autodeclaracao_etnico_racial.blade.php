<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Autodeclaração Étnico-Racial</title>
    <style>
        html, body, .folha, .folha * {
            font-family: DejaVu Sans, Arial, sans-serif !important;
        }
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 16mm 10mm;
        }
        html, body {
            font-size: 11pt;
            color: #000;
            background: #fff;
        }
        .folha { width: 100%; }
        .cabecalho {
            text-align: center;
            margin: 0 0 18pt;
            padding: 0 0 8pt;
            border-bottom: 1pt solid #000;
        }
        .cabecalho__logo { margin: 0 auto 4pt; }
        .cabecalho__logo img {
            display: block;
            margin: 0 auto;
            max-height: 16mm;
            max-width: 48mm;
        }
        .cabecalho__logo--compact img { max-height: 12mm; }
        .cabecalho__razao {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.35;
            margin: 0 0 2pt;
        }
        .cabecalho__meta {
            font-size: 8.5pt;
            line-height: 1.35;
            margin: 0;
        }
        .titulo {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.5;
            margin: 0 0 22pt;
        }
        .paragrafo {
            font-size: 11pt;
            line-height: 1.7;
            text-align: justify;
            text-indent: 12.5mm;
            margin: 0 0 14pt;
        }
        .paragrafo--sem-recuo {
            text-indent: 0;
        }
        .opcoes {
            font-size: 11pt;
            line-height: 1.8;
            margin: 0 0 18pt;
            padding-left: 12.5mm;
        }
        .fechamento {
            margin-top: 36pt;
        }
        .local-data {
            font-size: 11pt;
            line-height: 1.5;
            text-align: left;
            margin: 0 0 36pt;
        }
        .assinatura {
            text-align: center;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
        }
        .assinatura__linha {
            width: 9cm;
            margin: 0 auto 6pt;
            border: none;
            border-top: 1pt solid #000;
        }
    </style>
</head>
<body>
@php
    $empresaPdf = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $razaoPdf = trim((string) ($empresaPdf['razao_social'] ?? $empresaPdf['nome_fantasia'] ?? ''));
    $logoPdf = trim((string) ($empresaPdf['logo'] ?? ''));
    $cnpjPdf = trim((string) ($empresaPdf['cnpj'] ?? ''));
    $endPdf = trim((string) ($empresaPdf['endereco_completo'] ?? ''));
    $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nome = $colaborador->Curriculo->nome ?? '—';
    $cpf = $colaborador->Curriculo->cpf ?? '';
@endphp

<div class="folha">
    <div class="cabecalho">
        @if($logoPdf !== '')
            <div class="cabecalho__logo {{ $logoCompacto ? 'cabecalho__logo--compact' : '' }}">
                <img src="{{ $logoPdf }}" alt="{{ $razaoPdf }}">
            </div>
        @endif
        @if($razaoPdf !== '')
            <div class="cabecalho__razao">{{ $razaoPdf }}</div>
        @endif
        @if($cnpjPdf !== '' || $endPdf !== '')
            <div class="cabecalho__meta">
                @if($cnpjPdf !== '')
                    CNPJ: {{ $cnpjPdf }}
                @endif
                @if($cnpjPdf !== '' && $endPdf !== '')
                    <br>
                @endif
                @if($endPdf !== '')
                    {{ $endPdf }}
                @endif
            </div>
        @endif
    </div>

    <p class="titulo">Autodeclaração Étnico-Racial</p>

    <p class="paragrafo paragrafo--sem-recuo">
        Eu, <strong>{{ $nome }}</strong>,
        inscrito no CPF sob o nº <strong>{{ $cpf }}</strong>,
        AUTODECLARO, sob as penas da lei, minha raça/etnia sendo:
    </p>

    <div class="opcoes">
        [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Branca<br>
        [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Preta<br>
        [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Parda<br>
        [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Amarela<br>
        [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Indígena
    </div>

    <p class="paragrafo">
        Esta autodeclaração atende a exigência do art. 39, § 8º, da Lei nº 12.288/2010, alterado pela Lei nº
        14.553/2023 e da Portaria MTE nº 3.784/2023, que obriga a prestação da informação nas inclusões, alterações
        ou retificações cadastrais dos trabalhadores ocorridas a partir de 1º de janeiro de 2024, respeitando o
        critério de autodeclaração do trabalhador, em conformidade com a classificação utilizada pelo Instituto
        Brasileiro de Geografia e Estatística - IBGE.
    </p>

    <p class="paragrafo">
        Por ser expressão da verdade, firmo e assino a presente para que a mesma produza seus efeitos legais e de
        direito.
    </p>

    <div class="fechamento">
        <p class="local-data">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        </p>

        <div class="assinatura">
            <hr class="assinatura__linha">
            {{ $nome }}
        </div>
    </div>
</div>
</body>
</html>
