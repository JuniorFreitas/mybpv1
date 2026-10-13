<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Termo de Responsabilidade — Salário Família</title>
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
            margin: 0 0 4pt;
            line-height: 1.3;
        }
        .titulo-sub {
            text-align: center;
            font-size: 9pt;
            font-weight: normal;
            text-transform: none;
            line-height: 1.3;
            margin: 0 0 12pt;
        }
        .paragrafo {
            font-size: 10pt;
            line-height: 1.4;
            text-indent: 8mm;
            margin: 0 0 8pt;
        }
        .paragrafo--sem-recuo {
            text-indent: 0;
        }
        .lista-fatos {
            font-size: 10pt;
            line-height: 1.5;
            margin: 0 0 8pt;
            padding-left: 8mm;
            text-transform: uppercase;
        }
        .campo-linha {
            font-size: 10pt;
            line-height: 1.7;
            margin: 0 0 8pt;
        }
        .doc-fechamento {
            margin-top: 12pt;
        }
        .doc-fechamento__local {
            margin: 0 0 10pt;
            font-size: 10pt;
        }
        .doc-fechamento__assinantes--unico {
            margin-top: 18pt;
        }
        .doc-fechamento__assinantes--unico .doc-fechamento__espaco {
            height: 28pt;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $colaborador = $dados['dados_colaborador'] ?? null;
    $nomeEmpregado = $colaborador->Curriculo->nome ?? '—';
    $rg = $colaborador->rg ?? ($colaborador->Curriculo->rg ?? '');
    $ctps = $colaborador->Admissao->DadosAdmissoes->ctps_numero ?? '';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $cnpj = $dados_empresa['cnpj'] ?? '';
    $endereco = $dados_empresa['endereco_completo'] ?? '';
    $contato = $cliente->contato ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Termo de Responsabilidade</p>
    <p class="titulo-sub">(Concessão de Salário Família - Portaria nº MPAS - 3.040/82)</p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPRESA:</strong><br>
        <strong>{{ $razaoSocial }}</strong>, inscrita no CNPJ/MF sob n.º <strong>{{ $cnpj }}</strong>, com atividade
        localizada na {{ $endereco }}, neste ato representado(a) por
        <span style="text-transform: uppercase">{{ $contato }}</span>.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPREGADO:</strong> {{ $nomeEmpregado }}<br>
        <strong>RG:</strong> {{ $rg }}<br>
        <strong>CTPS:</strong> {{ $ctps }}
    </p>

    <div class="campo-linha">
        Nome do filho: _______________________________________________________________________<br>
        Data de nascimento: __________________________________________________________________
    </div>

    <p class="paragrafo">
        Pelo presente TERMO DE RESPONSABILIDADE, declaro estar ciente de que deverei comunicar de imediato a
        ocorrência dos seguintes fatos, que determinam a perda do direito ao salário família.
    </p>

    <div class="lista-fatos">
        - Óbito do filho;<br>
        - Cessação invalidez de filho inválido;<br>
        - Sentença judicial para pagamento a outrem.
    </div>

    <p class="paragrafo">
        Estou ciente, ainda, de que a falta de cumprimento do compromisso ora assumido, além de obrigar a devolução
        das importâncias recebidas indevidamente, sujeitar-me-á às penalidades previstas no artigo 171 do código penal e
        à rescisão do contrato de trabalho, por justa causa, nos termos do artigo 482 da CLT.
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
