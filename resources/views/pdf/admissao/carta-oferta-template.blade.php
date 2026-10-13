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
        .conteudo-carta p {
            font-size: 11pt;
            line-height: 1.7;
            margin: 0 0 14pt;
        }
        .conteudo-carta hr {
            width: 9cm;
            margin: 22pt auto 6pt auto;
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

    <div class="conteudo-carta">
        {!! $html !!}
    </div>
</div>
</body>
</html>
