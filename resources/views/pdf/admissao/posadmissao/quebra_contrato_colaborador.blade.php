<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $dados->motivoRescisao->descricao ?? 'Quebra de contrato' }}</title>
    <style>
        /*
         * Mesmo padrão visual do comunicado de demissão.
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
            word-spacing: normal;
            letter-spacing: normal;
        }
        .fechamento {
            margin-top: 36pt;
        }
        .atenciosamente {
            font-size: 11pt;
            line-height: 1.5;
            text-align: left;
            margin: 0 0 40pt;
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
    $empresaPdf = is_array($dados_empresa ?? null)
        ? $dados_empresa
        : (json_decode(json_encode($dados_empresa ?? []), true) ?: []);
    $razaoPdf = (string) ($empresaPdf['razao_social'] ?? ($dados->Feedback->Empresa->razao_social ?? ''));
    $logoPdf = $empresaPdf['logo'] ?? null;
    $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
    $empregadoNome = $dados->Feedback->Curriculo->nome ?? '—';
    $empresaNome = $razaoPdf !== ''
        ? $razaoPdf
        : (string) ($dados->Feedback->Empresa->razao_social ?? '—');
    $cnpj = (string) ($empresaPdf['cnpj'] ?? ($dados->User->DadosEmpresa->cnpj ?? ''));
    $dataDesligamento = $dados->data_desmobilizacao;
    $dataCiencia = $dataDesligamento
        ? (new \MasterTag\DataHora($dataDesligamento))->dataCompleta()
        : (new \MasterTag\DataHora())->dataCompleta();
    $dataTermino = $dataDesligamento
        ? (new \MasterTag\DataHora($dataDesligamento))->addDia(15)
        : '_______/_______/______________';
    $dataAdmissao = $dados->Feedback->Admissao->data_admissao ?? '—';
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

    <p class="titulo">Quebra de contrato — Empregado</p>

    <p class="paragrafo">
        A Empresa <strong>{{ $empresaNome }}</strong>@if($cnpj !== ''), inscrita no CNPJ/MF sob n.º
        <strong>{{ $cnpj }}</strong>@endif, pela presente, notifica que a partir do dia
        <strong>{{ $dataCiencia }}</strong>, fica rescindido o contrato de trabalho de
        <strong>{{ $empregadoNome }}</strong> com a empresa, iniciado em
        <strong>{{ $dataAdmissao }}</strong>, com término previsto para o dia
        <strong>{{ $dataTermino }}</strong>.
    </p>

    <div class="fechamento">
        <p class="atenciosamente">Atenciosamente,</p>

        <div class="assinatura">
            <hr class="assinatura__linha">
            <strong>{{ $empregadoNome }}</strong>
        </div>
    </div>
</div>
</body>
</html>
