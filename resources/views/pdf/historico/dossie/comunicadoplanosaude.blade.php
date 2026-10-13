<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comunicado – Plano de Saúde Corporativo</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 14mm 10mm;
        }
        html, body { font-size: 10pt; }
        .titulo { font-size: 11pt; margin: 0 0 10pt; line-height: 1.3; }
        .paragrafo { font-size: 9.5pt; line-height: 1.35; text-indent: 8mm; margin: 0 0 6pt; }
        .paragrafo--sem-recuo { text-indent: 0; }
        .lista {
            font-size: 9.5pt;
            line-height: 1.4;
            margin: 0 0 6pt 8mm;
            padding: 0;
        }
        .doc-fechamento { margin-top: 8pt; }
        .doc-fechamento__espaco { height: 22pt; }
        .doc-fechamento__local { margin: 0 0 10pt; font-size: 9.5pt; }
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

    <p class="titulo">Comunicado – Plano de Saúde Corporativo</p>

    <p class="paragrafo paragrafo--sem-recuo">
        A empresa <strong>{{ $razaoSocial }}</strong>, inscrita no CNPJ sob o nº <strong>{{ $cnpj }}</strong>,
        informa que disponibiliza aos seus colaboradores a possibilidade de inclusão de dependentes no plano de saúde
        corporativo, conforme as regras vigentes da operadora e as políticas internas da empresa.
    </p>

    <p class="paragrafo">
        Fica ciente o colaborador de que, em caso de afastamento por qualquer motivo (incluindo doença, acidente ou
        qualquer outro tipo de licença, remunerada ou não), os dependentes incluídos no plano de saúde serão
        automaticamente excluídos, permanecendo apenas a cobertura do titular. A reinclusão dos dependentes poderá ser
        solicitada após o retorno do colaborador às suas atividades laborais.
    </p>

    <p class="paragrafo">
        Comunicamos ainda que, a partir do dia <strong>1º de setembro de 2025</strong>, será implementada a
        coparticipação no plano de saúde para internações psiquiátricas, conforme previsto em contrato e em conformidade
        com a Resolução Normativa nº 465/2021 da ANS (Agência Nacional de Saúde Suplementar) – Subseção III, Art. 19.
    </p>

    <p class="paragrafo">
        A coparticipação será devida somente quando o tempo de internação ou o número de consultas ultrapassar 30
        (trinta) dias, consecutivos ou não, dentro do ano contratual de cada beneficiário (contado a partir da data de
        adesão ao plano).
    </p>

    <p class="paragrafo">
        O valor correspondente à coparticipação será cobrado através de boleto bancário gerado pela operadora do plano
        de saúde, sendo de responsabilidade do colaborador o seu pagamento.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        Exemplos de procedimentos psiquiátricos que poderão gerar coparticipação:
    </p>
    <ul class="lista">
        <li>Internações hospitalares para tratamento psiquiátrico;</li>
        <li>Consultas médicas com psiquiatra;</li>
        <li>Sessões de psicoterapia (com psicólogos ou terapeutas credenciados);</li>
        <li>Atendimentos em pronto-socorro psiquiátrico.</li>
    </ul>

    <p class="paragrafo">
        Ressaltamos que esta medida segue rigorosamente as normas estabelecidas pela operadora do plano de saúde,
        visando garantir a continuidade e a sustentabilidade deste benefício a todos os colaboradores.
    </p>

    <p class="paragrafo">
        Para esclarecimentos adicionais sobre valores, regras de coparticipação e demais informações, orientamos que os
        colaboradores entrem em contato com o setor de Recursos Humanos.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $razaoSocial, 'subtitulo' => 'EMPRESA'],
            ['nome' => $nome, 'subtitulo' => 'COLABORADOR(A)'],
        ],
        'testemunhas' => 0,
        'novaPagina' => false,
    ])
</div>
</body>
</html>
