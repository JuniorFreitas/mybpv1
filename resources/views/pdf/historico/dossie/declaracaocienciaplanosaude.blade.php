<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Declaração de Ciência e Concordância – Plano de Saúde</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 14mm 10mm;
        }
        html, body { font-size: 10pt; }
        .titulo { font-size: 11pt; margin: 0 0 4pt; line-height: 1.3; }
        .titulo-sub {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 12pt;
            line-height: 1.3;
        }
        .paragrafo { font-size: 10pt; line-height: 1.4; text-indent: 8mm; margin: 0 0 7pt; }
        .paragrafo--sem-recuo { text-indent: 0; }
        .secao { font-size: 10pt; font-weight: bold; margin: 8pt 0 6pt; }
        .tabela-valores {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10pt;
            font-size: 9.5pt;
        }
        .tabela-valores td {
            border: 0.6pt solid #333;
            padding: 4pt 6pt;
            line-height: 1.35;
        }
        .doc-fechamento { margin-top: 10pt; }
        .doc-fechamento__assinantes--unico { margin-top: 14pt; }
        .doc-fechamento__assinantes--unico .doc-fechamento__espaco { height: 26pt; }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $admissao = $colaborador->Admissao ?? null;
    $dataAdmissaoRaw = $admissao?->getAttributes()['data_admissao'] ?? null;
    $dataAssinatura = $dataAdmissaoRaw
        ? (new \MasterTag\DataHora($dataAdmissaoRaw))->dataCompletaExt()
        : (new \MasterTag\DataHora())->dataCompletaExt();
    $nome = $colaborador->Curriculo->nome ?? 'NÃO INFORMADO';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Declaração de Ciência e Concordância</p>
    <p class="titulo-sub">Plano de Saúde - HAPVIDA</p>

    <p class="paragrafo paragrafo--sem-recuo">
        Declaro, para os devidos fins, que estou ciente das condições referentes ao Plano de Saúde,
        cujo valor mensal é de <strong>R$ 301,63</strong> (trezentos e um reais e sessenta e três centavos).
    </p>

    <p class="paragrafo">
        Estou ciente de que o valor do plano é dividido igualmente entre a empresa e o colaborador,
        sendo <strong>50%</strong> do valor custeado pela empresa e <strong>50%</strong> pelo colaborador.
    </p>

    <p class="paragrafo">
        Estou de acordo com o desconto da minha participação no valor do plano em folha de pagamento,
        conforme as condições estabelecidas pela empresa.
    </p>

    <p class="secao">VALORES DO PLANO</p>

    <table class="tabela-valores" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 55%;"><strong>Valor total do plano</strong></td>
            <td>R$ 301,63</td>
        </tr>
        <tr>
            <td><strong>Parte custeada pela empresa (50%)</strong></td>
            <td>R$ 150,81</td>
        </tr>
        <tr>
            <td><strong>Parte custeada pelo colaborador (50%)</strong></td>
            <td>R$ 150,82 – DESCONTADO EM FOLHA DE PAGAMENTO.</td>
        </tr>
    </table>

    <p class="paragrafo paragrafo--sem-recuo">
        Declaro estar ciente e de acordo com as condições acima descritas.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís, ' . $dataAssinatura . '.',
        'assinantes' => [
            ['nome' => $nome, 'subtitulo' => 'COLABORADOR(A)'],
        ],
        'testemunhas' => 0,
        'novaPagina' => false,
    ])
</div>
</body>
</html>
