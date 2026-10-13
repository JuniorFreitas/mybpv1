<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comunicado aos Colaboradores</title>
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
        .lista {
            font-size: 10pt;
            line-height: 1.45;
            margin: 0 0 8pt 8mm;
            padding: 0;
        }
        .doc-fechamento { margin-top: 12pt; }
        .doc-fechamento__espaco { height: 28pt; }
        .doc-fechamento__local { margin: 0 0 12pt; font-size: 10pt; }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nome = $colaborador->Curriculo->nome ?? '—';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $cnpj = $dados_empresa['cnpj'] ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Comunicado aos Colaboradores</p>

    <p class="paragrafo paragrafo--sem-recuo">
        A empresa <strong>{{ $razaoSocial }}</strong>, inscrita no CNPJ sob o nº <strong>{{ $cnpj }}</strong>,
        informa que, conforme previsto na legislação trabalhista brasileira, os colaboradores têm direito à ausência
        justificada para realização de exames preventivos de saúde, podendo utilizar até <strong>3 (três) dias</strong>,
        dentro do período de <strong>12 meses</strong>.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">Para utilização do benefício, orientamos que:</p>
    <ul class="lista">
        <li>O gestor imediato seja comunicado com antecedência, sempre que possível;</li>
        <li>O colaborador apresente comprovante de comparecimento ou atestado emitido pela clínica/hospital;</li>
        <li>As ausências sejam destinadas exclusivamente à realização de exames preventivos de saúde.</li>
    </ul>

    <p class="paragrafo">
        Essa medida reforça a importância do cuidado com a saúde e do acompanhamento médico preventivo.
    </p>

    <p class="paragrafo">
        Em caso de dúvidas, o setor de Recursos Humanos permanece à disposição.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $razaoSocial, 'subtitulo' => 'RECURSOS HUMANOS'],
            ['nome' => $nome, 'subtitulo' => 'COLABORADOR(A) – CIENTE'],
        ],
        'testemunhas' => 0,
        'novaPagina' => false,
    ])
</div>
</body>
</html>
