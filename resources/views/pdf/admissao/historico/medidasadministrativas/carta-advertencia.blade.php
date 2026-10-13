<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $medida->tipo }}</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 14mm 10mm;
        }
        html, body {
            font-size: 10pt;
        }
        .titulo {
            font-size: 11pt;
            margin: 0 0 12pt;
            line-height: 1.3;
        }
        .meta {
            font-size: 10pt;
            line-height: 1.45;
            margin: 0 0 4pt;
        }
        .corpo {
            font-size: 10pt;
            line-height: 1.45;
            text-align: justify;
            margin: 10pt 0 0;
        }
        .assinatura {
            text-align: center;
            font-size: 10pt;
            line-height: 1.35;
            margin: 18pt 0 0;
        }
        .assinatura__linha {
            width: 9cm;
            margin: 0 auto 4pt;
            border: none;
            border-top: 1pt solid #000;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">CARTA DE {{ $medida->tipo == 'Re-orientação' ? 'Orientação' : $medida->tipo }}</p>

    <p class="meta">PARA: <strong>{{ $medida->Feedback->Curriculo->nome }}</strong></p>
    <p class="meta" style="text-transform: uppercase; margin-top: 8pt;">
        NESTA<br>
        REF.: {{ $medida->causa }}.
    </p>

    <p class="meta" style="margin-top: 10pt;">
        Prezado(a) Senhor(a): <strong>{{ $medida->Feedback->Curriculo->nome }}</strong>
    </p>

    <div class="corpo">
        O(A) Senhor(a) trabalhando nesta empresa desde {{ $medida->Feedback->Admissao->data_admissao }}, esta
        sendo {{ $medida->TipoMedida }} no
        dia {{ $medida->data_solicitacao }}, em virtude de <strong>{{ $medida->definicao }}</strong>
        ({{ $medida->motivo }}
        )
        @if(!in_array($medida->tipo,['Advertência Escrita', 'Advertência Verbal', 'Desligamento', 'Re-orientação',]))
            com retorno no dia ({{ $medida->data_retorno }})
        @endif().
        Em razão disso
        @if($medida->Feedback->Empresa->id == 63122)
            e na forma do artigo 482 alínea h
        @endif
        , o(a) Sr(a)., está sendo advertido(a), para que repense suas
        atitudes e passe a se adequar nas regras internas da empresa, evitando a reincidência, que poderá provocar
        outras medidas disciplinares.
        <br>
        <br>
        Sem mais <br>
        Assino a presente,
        <br>
        <br>
        São Luís-MA, {{ (new \MasterTag\DataHora($medida->data_solicitacao))->dataCompletaExt() }}.
        <br>
        <br>
    </div>

    <div class="assinatura">
        <hr class="assinatura__linha">
        {{ $medida->Feedback->Empresa->razao_social }}
    </div>

    <div class="assinatura" style="margin-top: 22pt;">
        Ciente em {{ (new \MasterTag\DataHora($medida->data_solicitacao))->dataCompleta() }}
        <br><br> <br><br>

        <hr class="assinatura__linha">
        {{ $medida->Feedback->Curriculo->nome }}
    </div>
</div>
</body>
</html>
