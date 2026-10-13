<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Acordo de Compensação de Horas de Trabalho</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 12mm 10mm;
        }
        html, body {
            font-size: 9.5pt;
        }
        .cabecalho {
            margin: 0 0 10pt;
            padding: 0 0 6pt;
        }
        .cabecalho__logo img {
            max-height: 13mm;
            max-width: 42mm;
        }
        .cabecalho__logo--compact img {
            max-height: 10mm;
        }
        .cabecalho__razao {
            font-size: 9.5pt;
        }
        .cabecalho__meta {
            font-size: 7.5pt;
        }
        .titulo {
            font-size: 11pt;
            margin: 0 0 14pt;
            line-height: 1.35;
        }
        .paragrafo {
            font-size: 9.5pt;
            line-height: 1.45;
            text-indent: 8mm;
            margin: 0 0 8pt;
        }
        .paragrafo--sem-recuo {
            text-indent: 0;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nomeEmpregado = $colaborador->Curriculo->nome ?? '—';
    $cpf = $colaborador->Curriculo->cpf ?? '__________________________';
    $cargo = $colaborador->Admissao->cargo ?? '';
    $ctps = $colaborador->Admissao->DadosAdmissoes->ctps_numero ?? '__________________________';
    $serie = $colaborador->Admissao->DadosAdmissoes->ctps_serie ?? '___________________________';
    $dataAdmissao = $colaborador->Admissao->data_admissao ?? '';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $cnpj = $dados_empresa['cnpj'] ?? '';
    $endereco = $dados_empresa['endereco_completo'] ?? '';
    $contato = $cliente->contato ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Acordo de Compensação de Horas de Trabalho</p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPRESA:</strong><br>
        <strong>{{ $razaoSocial }}</strong>, inscrita no CNPJ/MF sob n.º <strong>{{ $cnpj }}</strong>, com atividade
        localizada na {{ $endereco }}, neste ato representado(a) por
        <span style="text-transform: uppercase">{{ $contato }}</span>.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPREGADO:</strong> {{ $nomeEmpregado }}, {{ $cargo }}, portador da carteira profissional n.º
        {{ $ctps }} Série {{ $serie }} inscrita no CPF sob nº {{ $cpf }}, com admissão em {{ $dataAdmissao }}.
    </p>

    <p class="paragrafo">
        As partes acima identificadas celebram o presente ACORDO DE COMPENSAÇÃO DE HORAS, que se regerá pelas
        cláusulas abaixo:
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        1. A partir do dia ___/___/______ o empregado cumprirá a seguinte jornada normal de trabalho:<br>
        (a) de __________________________________________________, com uma hora de intervalo para refeição e
        descanso.<br>
        Total de horas trabalhadas na semana: _________________________ horas
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        2. Fica convencionado conforme faculta a Lei, que haverá sistema de compensação de horas extras, pelo qual
        as horas extras efetivamente realizadas pelos empregados durante o mês, poderão ser compensadas, no prazo de
        até 180 (cento e oitenta) dias após o mês da prestação da hora, com reduções de jornadas ou folgas
        compensatórias. Na hipótese das horas não serem compensadas, as mesmas serão pagas como horas extras, ou seja,
        o valor nominal da hora normal acrescido do adicional de 50%, conforme previsão da Convenção Coletiva de
        Trabalho.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        3. Na hipótese de rescisão do contrato de trabalho na vigência deste acordo de compensação de horas, sem que
        tenha havido a compensação integral das horas de trabalho, será feito o acerto de contas nas verbas
        rescisórias, ficando certo que, havendo crédito a favor do empregado, este fará jus ao pagamento das horas
        devidas com o adicional de 50% (cinquenta por cento)
    </p>

    <p class="paragrafo">
        O presente Acordo de Compensação de Horas de Trabalho tem prazo indeterminado e é acessório ao Contrato de
        Trabalho celebrado entre as partes.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $razaoSocial, 'papel' => 'EMPRESA'],
            ['nome' => $nomeEmpregado, 'papel' => 'EMPREGADO'],
        ],
        'testemunhas' => 2,
        'novaPagina' => true,
    ])
</div>
</body>
</html>
