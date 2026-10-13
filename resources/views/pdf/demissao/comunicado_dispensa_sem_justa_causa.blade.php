<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $pdfTitle ?? 'Comunicado de dispensa' }}</title>
    <style>
        /*
         * Documento formal — margens em TODAS as páginas via @page:
         * superior 0,5cm | direita 1cm | inferior 1,6cm | esquerda 1cm
         * Logo cola no topo da margem. Fonte 11pt | texto justificado
         *
         * IMPORTANTE (DomPDF): NÃO usar "* { margin:0 }" — isso anula @page margin.
         */
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
        .folha {
            width: 100%;
        }
        .cabecalho {
            text-align: center;
            margin: 0 0 6pt;
            padding: 0 0 5pt;
            border-bottom: 1pt solid #000;
        }
        .cabecalho__logo {
            margin: 0 auto 4pt;
        }
        .cabecalho__logo img {
            display: block;
            margin: 0 auto;
            max-height: 16mm;
            max-width: 48mm;
        }
        .cabecalho__logo--compact img {
            max-height: 12mm;
        }
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
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.4;
            margin: 0 0 6pt;
        }
        .destinatario {
            font-size: 11pt;
            line-height: 1.4;
            margin: 0 0 5pt;
            text-align: left;
        }
        .paragrafo {
            font-size: 11pt;
            line-height: 1.4;
            text-align: justify;
            text-indent: 12.5mm;
            margin: 0 0 6pt;
            word-spacing: normal;
            letter-spacing: normal;
        }
        .opcoes {
            font-size: 11pt;
            line-height: 1.4;
            margin: 0 0 6pt 12.5mm;
            text-align: left;
        }
        .local-data {
            font-size: 11pt;
            line-height: 1.4;
            text-align: left;
            margin: 18pt 0 22pt;
        }
        .assinatura {
            text-align: center;
            font-size: 11pt;
            line-height: 1.35;
            margin: 0 0 16pt;
        }
        .assinatura--empregado {
            margin-top: 18pt;
            margin-bottom: 0;
        }
        .assinatura__linha {
            width: 9cm;
            margin: 0 auto 4pt;
            border: none;
            border-top: 1pt solid #000;
        }
        .ciencia-titulo {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.35;
            margin: 4pt 0 8pt;
        }
        .ciencia-texto {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
@php
    $empresaPdf = is_array($dados_empresa ?? null)
        ? $dados_empresa
        : (json_decode(json_encode($dados_empresa ?? []), true) ?: []);
    $razaoPdf = (string) ($empresaPdf['razao_social'] ?? $razaoSocial ?? '');
    $logoPdf = $empresaPdf['logo'] ?? null;
    $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
    $tipoAvisoLabel = mb_strtoupper($tipoAviso !== '' ? $tipoAviso : ($isIndenizado ? 'INDENIZADO' : 'TRABALHADO'));
    $tipoAvisoTexto = mb_strtolower($tipoAviso !== '' ? $tipoAviso : ($isIndenizado ? 'indenizado' : 'trabalhado'));
    $empresaNome = $razaoPdf !== '' ? $razaoPdf : $razaoSocial;
@endphp

<div class="folha">
    <div class="cabecalho">
        @if(!empty($logoPdf))
            <div class="cabecalho__logo {{ $logoCompacto ? 'cabecalho__logo--compact' : '' }}">
                <img src="{{ $logoPdf }}" alt="{{ $razaoPdf }}">
            </div>
        @endif
        @if($razaoPdf !== '')
            <div class="cabecalho__razao">{{ $razaoPdf }}</div>
        @endif
        @if(!empty($empresaPdf['cnpj']) || !empty($empresaPdf['endereco_completo']))
            <div class="cabecalho__meta">
                @if(!empty($empresaPdf['cnpj']))
                    CNPJ: {{ $empresaPdf['cnpj'] }}
                @endif
                @if(!empty($empresaPdf['cnpj']) && !empty($empresaPdf['endereco_completo']))
                    <br>
                @endif
                @if(!empty($empresaPdf['endereco_completo']))
                    {{ $empresaPdf['endereco_completo'] }}
                @endif
            </div>
        @endif
    </div>

    <p class="titulo">
        Comunicado de dispensa sem justa causa<br>
        Aviso prévio {{ $tipoAvisoLabel }}
    </p>

    <p class="destinatario">
        Ao(À) empregado(a): <strong>{{ $empregadoNome }}</strong>
    </p>

    <p class="paragrafo">
        Por meio deste comunicado, a empresa <strong>{{ $empresaNome }}</strong>
        informa a decisão de rescindir o contrato de trabalho de V.Sa., por iniciativa do empregador,
        <strong>sem justa causa</strong>, a partir de <strong>{{ $dataCiencia }}</strong>.
    </p>

    @if($isIndenizado)
        <p class="paragrafo">
            O aviso prévio será indenizado, ficando o(a) empregado(a) dispensado(a) de seu cumprimento,
            nos termos do artigo 487, § 1º, da Consolidação das Leis do Trabalho (CLT).
        </p>
        <p class="paragrafo">
            O período de aviso prévio indenizado será de <strong>{{ $diasAviso }}</strong> dias,
            observada a proporcionalidade legal conforme o tempo de serviço, nos termos da Lei nº 12.506/2011,
            e será computado para os efeitos legais cabíveis.
        </p>
    @else
        <p class="paragrafo">
            O aviso prévio será trabalhado, nos termos do artigo 487, inciso II, da Consolidação das Leis do Trabalho (CLT).
            O período de aviso prévio será de <strong>{{ $diasAviso }}</strong> dias, observada a proporcionalidade legal
            conforme o tempo de serviço, nos termos da Lei nº 12.506/2011.
        </p>
        <p class="opcoes">
            (&nbsp;&nbsp;&nbsp;) 1ª Opção: Declaro-me ciente, optando pela redução de 02 (duas) horas diárias.<br>
            (&nbsp;&nbsp;&nbsp;) 2ª Opção: Declaro-me ciente, optando pela ausência no trabalho de 07 dias corridos.
        </p>
    @endif

    <p class="paragrafo">
        As verbas rescisórias serão apuradas e pagas no prazo legal, com a disponibilização dos documentos
        pertinentes à rescisão contratual.
    </p>

    <p class="paragrafo">
        Solicita-se a devolução de eventuais equipamentos, documentos, uniformes, crachás e demais bens
        pertencentes à empresa que estejam sob responsabilidade do(a) empregado(a).
    </p>

    <p class="paragrafo">
        A empresa agradece pelos serviços prestados durante o vínculo empregatício.
    </p>

    <div class="fechamento">
        <p class="local-data">
            {{ $cidade }}, {{ $dataExtenso }}.
        </p>

        <div class="assinatura">
            <hr class="assinatura__linha">
            <strong>{{ $empresaNome }}</strong><br>
            Empregador
        </div>

        <p class="ciencia-titulo">Ciência do(a) empregado(a)</p>

        <p class="paragrafo ciencia-texto">
            Declaro ter recebido, nesta data, o presente comunicado de dispensa sem justa causa,
            com aviso prévio {{ $tipoAvisoTexto }}.
        </p>

        <div class="assinatura assinatura--empregado">
            <hr class="assinatura__linha">
            <strong>{{ $empregadoNome }}</strong><br>
            Empregado(a)<br>
            Data da ciência: {{ $dataCiencia }}
        </div>
    </div>
</div>
</body>
</html>
