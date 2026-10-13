<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Documentos para Admissão</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 14mm 10mm;
        }
        html, body {
            font-size: 9.5pt;
        }
        .titulo {
            font-size: 11pt;
            margin: 0 0 10pt;
            line-height: 1.3;
        }
        .checklist {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10pt;
            font-size: 9pt;
        }
        .checklist th,
        .checklist td {
            border: 0.6pt solid #333;
            padding: 3pt 5pt;
            vertical-align: middle;
            line-height: 1.3;
        }
        .checklist th {
            text-align: center;
            font-weight: bold;
            background: #f3f3f3;
        }
        .checklist td.status {
            width: 18%;
            text-align: center;
        }
        .obs-data {
            font-size: 9.5pt;
            text-align: center;
            margin: 0 0 10pt;
            line-height: 1.4;
        }
        .recebido {
            font-size: 9.5pt;
            line-height: 1.7;
            margin: 0 0 10pt;
        }
        .lgpd {
            font-size: 8.5pt;
            line-height: 1.35;
            text-align: justify;
            margin: 0;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $itens = [
        'Carteira Profissional (Somente no dia da ADMISSÃO)',
        '03 fotos 3x4 recentes',
        'Cópia da Carteira de Identidade',
        'Cópia do CPF',
        'Cópia do Título de Eleitor',
        'Cópia da Carteira Profissional (frente, verso)',
        'Cópia do Comprovante de Residência (contas de água, luz ou telefone) com CEP',
        'Cópia do Cartão de PIS (Cartão do Cidadão)',
        'Cópia da Carteira Nacional de Habilitação (CNH)',
        'Cópia da Certidão de Nascimento, Casamento ou Declaração de União Estável Pública',
        'Cópia do Certificado de Reservista/Alistamento no Serviço Militar',
        'Cópia da Carteira de Vacina (Colaborador) (Febre/Hepatite B)',
        'Cópia do Certificado de Conclusão/Declaração ou Histórico Escolar',
        'Cópias dos Certificados dos Cursos de Capacitação',
    ];
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Documentos para Admissão</p>

    <table class="checklist" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th>Documento</th>
            <th class="status">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($itens as $item)
            <tr>
                <td>{{ $item }}</td>
                <td class="status">&nbsp;</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p class="obs-data">
        Favor aprensentar todos os documentos no dia ____/____ /_______.
    </p>

    <p class="recebido">
        Recebido por: __________________________________________________<br>
        Em: ________________________________________________________
    </p>

    <p class="lgpd">
        * Em atendimento a Lei nº 13.709/2018, Lei Geral de Proteção de Dados, viemos através deste solicitar o
        consentimento do uso dos dados acima para fins de processsamento da admissão
        na {{ $razaoSocial }} por prazo indeterminado assim como registro nos órgãos competetes e permitir a gestão de
        pessoas por parte da empresa. Comunicamos ainda que as cópias das documentações solicitadas serão de uso
        exclusivo da contabilidade e responsável pela recursos humanos da empresa. Após o processo de desligamento, a
        documentação será armazenada como funcionário inativo, pelo período de legal de 30 anos de forma digital e
        física. Pode ocorrer que precisemos compartilhar seus dados com terceiros para cumprir exigências legais,
        regulatórias os fiscais envolvendo a divulgação dos seus dados pessoais a terceiros, a um tribunal, reguladores
        ou agencias governamentais.
    </p>
</div>
</body>
</html>
