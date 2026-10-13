<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Carta Oferta</title>
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
        .destinatario {
            font-size: 11pt;
            line-height: 1.6;
            margin: 0 0 18pt;
            text-align: left;
        }
        .paragrafo {
            font-size: 11pt;
            line-height: 1.7;
            text-align: justify;
            text-indent: 12.5mm;
            margin: 0 0 14pt;
        }
        .fechamento { margin-top: 36pt; }
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
    $empresaPdf = is_array($dados_empresa ?? null) ? $dados_empresa : [];
    $razaoPdf = trim((string) ($empresaPdf['razao_social'] ?? $empresaPdf['nome_fantasia'] ?? ''));
    $logoPdf = trim((string) ($empresaPdf['logo'] ?? ''));
    $cnpjPdf = trim((string) ($empresaPdf['cnpj'] ?? ''));
    $endPdf = trim((string) ($empresaPdf['endereco_completo'] ?? ''));
    $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
    $empregadoNome = $cartaOferta->Curriculo->nome ?? '—';
    $empresaNome = $razaoPdf !== ''
        ? $razaoPdf
        : (string) ($cartaOferta->empresa->razao_social ?? $cartaOferta->empresa->nome_fantasia ?? '—');
    $dataExtenso = (new \MasterTag\DataHora())->dataCompletaExt();
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

    <p class="titulo">Carta oferta</p>

    <p class="destinatario">
        Prezado(a) <strong>{{ $empregadoNome }}</strong>,
    </p>

    <p class="paragrafo">
        Conforme processo seletivo, temos o prazer de formalizar a presente carta oferta para o cargo de
        <strong>{{ $nomeCargo }}</strong>.
    </p>

    <p class="paragrafo">
        Ao assinar este documento, você declara estar ciente e de acordo com as condições apresentadas.
    </p>

    <div class="fechamento">
        <p class="local-data">
            Data: {{ $dataExtenso }}
        </p>

        <div class="assinatura">
            <hr class="assinatura__linha">
            <strong>{{ $empresaNome }}</strong>
        </div>
    </div>
</div>
</body>
</html>
