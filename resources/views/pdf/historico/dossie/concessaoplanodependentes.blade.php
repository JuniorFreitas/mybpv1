<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Autorização para inclusão de dependente no Plano de Saúde</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 14mm 10mm;
        }
        html, body { font-size: 10pt; }
        .titulo { font-size: 11pt; margin: 0 0 10pt; line-height: 1.3; }
        .paragrafo { font-size: 10pt; line-height: 1.4; text-indent: 8mm; margin: 0 0 7pt; }
        .paragrafo--sem-recuo { text-indent: 0; }
        .tabela-dep {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10pt;
            font-size: 9.5pt;
        }
        .tabela-dep td {
            border: 0.6pt solid #333;
            padding: 3pt 6pt;
            line-height: 1.35;
        }
        .tabela-dep td.label { width: 32%; font-weight: bold; }
        .doc-fechamento { margin-top: 10pt; }
        .doc-fechamento__assinantes--unico { margin-top: 14pt; }
        .doc-fechamento__assinantes--unico .doc-fechamento__espaco { height: 26pt; }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nome = $colaborador->Curriculo->nome ?? '—';
    $cpf = $colaborador->Curriculo->cpf ?? '';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Autorização para inclusão de dependente<br>no Plano de Saúde – HAPVIDA</p>

    <p class="paragrafo paragrafo--sem-recuo">
        Eu, <strong>{{ $nome }}</strong>, inscrito(a) no CPF sob o nº <strong>{{ $cpf }}</strong>,
        venho por meio desta declarar que é de meu interesse incluir os meus dependentes abaixo no
        <strong>PLANO DE SAÚDE – HAPVIDA</strong>, oferecido pela empresa
        <strong>{{ $razaoSocial }}</strong>, onde irei pagar o valor integral por dependente.
    </p>

    <p class="paragrafo">
        Fico ciente de que, em caso de afastamento <strong>NÃO REMUNERADO</strong> por qualquer motivo
        (incluindo doença, acidente ou qualquer outro tipo de licença), os dependentes incluídos no plano de saúde
        serão automaticamente excluídos, permanecendo apenas a cobertura do titular.
        A reinclusão dos dependentes poderá ser solicitada após o retorno do colaborador às suas atividades laborais.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        O custo do plano de saúde do dependente é exclusivo do funcionário.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>Valor por dependente:</strong> R$ 301,63<br>
        Lembrando que, no primeiro mês, é pago uma taxa de adesão no valor de <strong>R$ 22,80</strong> por dependente.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>Nome do funcionário:</strong> {{ $nome }}
    </p>

    <p class="paragrafo paragrafo--sem-recuo"><strong>Dados do dependente para inclusão:</strong></p>

    <table class="tabela-dep" cellpadding="0" cellspacing="0">
        <tr>
            <td class="label">Nome completo</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Parentesco</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Data de nascimento</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
            <td class="label">CPF</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Estado civil</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Nome da mãe</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $nome, 'subtitulo' => 'COLABORADOR(A)'],
        ],
        'testemunhas' => 0,
        'novaPagina' => false,
    ])
</div>
</body>
</html>
