<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Concessão ou Dispensa de Adiantamento Salarial</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 14mm 10mm;
        }
        html, body { font-size: 10pt; }
        .titulo { font-size: 11pt; margin: 0 0 12pt; line-height: 1.3; }
        .paragrafo { font-size: 10pt; line-height: 1.4; text-indent: 8mm; margin: 0 0 8pt; }
        .paragrafo--sem-recuo { text-indent: 0; }
        .opcoes { font-size: 10pt; line-height: 1.7; margin: 0 0 10pt; padding-left: 8mm; }
        .atenciosamente { font-size: 10pt; margin: 10pt 0 0; }
        .doc-fechamento { margin-top: 10pt; }
        .doc-fechamento__assinantes--unico { margin-top: 14pt; }
        .doc-fechamento__assinantes--unico .doc-fechamento__espaco { height: 28pt; }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nome = $colaborador->Curriculo->nome ?? '—';
    $cpf = $colaborador->Curriculo->cpf ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Concessão ou Dispensa de Adiantamento Salarial</p>

    <p class="paragrafo paragrafo--sem-recuo">
        Eu, <strong>{{ $nome }}</strong>, inscrito(a) no CPF sob o nº <strong>{{ $cpf }}</strong>,
        declaro que é de meu interesse a opção abaixo referente ao adiantamento salarial correspondente a 40% do meu salário.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">Marque apenas uma opção:</p>
    <div class="opcoes">
        (&nbsp;&nbsp;&nbsp;) SIM, desejo receber o adiantamento salarial de 40%.<br>
        (&nbsp;&nbsp;&nbsp;) NÃO, não desejo receber o adiantamento salarial de 40%.
    </div>

    <p class="atenciosamente">Atenciosamente,</p>

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
