<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Autorização de Descontos Vale Transporte</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        .opcoes {
            font-size: 11pt;
            line-height: 1.8;
            margin: 0 0 18pt;
            padding-left: 12.5mm;
        }
        .secao-label {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 8pt;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nomeEmpregado = $colaborador->Curriculo->nome ?? '—';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $cnpj = $dados_empresa['cnpj'] ?? '';
    $endereco = $dados_empresa['endereco_completo'] ?? '';
    $contato = $cliente->contato ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Autorização de Descontos Vale Transporte</p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPRESA:</strong><br>
        <strong>{{ $razaoSocial }}</strong>, inscrita no CNPJ/MF sob n.º <strong>{{ $cnpj }}</strong>, com atividade
        localizada na {{ $endereco }}, neste ato representado(a) por
        <span style="text-transform: uppercase">{{ $contato }}</span>.
    </p>

    <p class="paragrafo">
        Autorizo descontarem mensalmente e por tempo indeterminado, dos meus vencimentos, as importâncias relativas
        aos itens assinalados com SIM.
    </p>

    <p class="secao-label">De acordo:</p>
    <div class="opcoes">
        (&nbsp;&nbsp;&nbsp;) SIM<br>
        (&nbsp;&nbsp;&nbsp;) NÃO
    </div>

    <p class="secao-label">Identificação do Desconto:</p>
    <p class="paragrafo paragrafo--sem-recuo">
        Vale Transporte na forma prevista no D.L. 95.247/87.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $nomeEmpregado],
        ],
        'testemunhas' => 0,
        'novaPagina' => false,
    ])
</div>
</body>
</html>
