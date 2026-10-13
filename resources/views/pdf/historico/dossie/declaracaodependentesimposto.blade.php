<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Declaração de Encargos para Imposto de Renda</title>
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
            margin: 0 0 10pt;
            line-height: 1.3;
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
        .campo-linha {
            font-size: 10pt;
            line-height: 1.7;
            margin: 0 0 8pt;
            text-transform: uppercase;
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
    $cpf = $colaborador->Curriculo->cpf ?? '';
    $ctps = $colaborador->Admissao->DadosAdmissoes->ctps_numero ?? '';
    $cargo = $colaborador->Admissao->cargo ?? '';
    $razaoSocial = $dados_empresa['razao_social'] ?? '';
    $cnpj = $dados_empresa['cnpj'] ?? '';
    $endereco = $dados_empresa['endereco_completo'] ?? '';
    $contato = $cliente->contato ?? '';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Declaração de Encargos para Imposto de Renda</p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPRESA:</strong><br>
        <strong>{{ $razaoSocial }}</strong>, inscrita no CNPJ/MF sob n.º <strong>{{ $cnpj }}</strong>, com atividade
        localizada na {{ $endereco }}, neste ato representado(a) por
        <span style="text-transform: uppercase">{{ $contato }}</span>.
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        <strong>EMPREGADO:</strong> {{ $nomeEmpregado }}<br>
        <strong>CPF:</strong> {{ $cpf }}<br>
        <strong>CTPS:</strong> {{ $ctps }}
    </p>

    <p class="paragrafo">
        Em obediência à legislação do Imposto de Renda, declaro pela presente que tenho como encargo de família as
        pessoas abaixo relacionadas.
    </p>

    <div class="campo-linha">
        Nome do dependente: _______________________________________________________________<br>
        Parentesco: _______________________________________________________________________<br>
        Nascimento: _______________________________________________________________________
    </div>

    <p class="paragrafo">
        Declaro sob as penas da lei, que as informações aqui prestadas são verdadeiras e da minha inteira
        responsabilidade, não cabendo a V.S.(s) _____________________________________________________ qualquer
        responsabilidade perante a fiscalização.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'São Luís - MA, ' . (new \MasterTag\DataHora())->dataCompletaExt() . '.',
        'assinantes' => [
            ['nome' => $nomeEmpregado, 'subtitulo' => $cargo],
        ],
        'testemunhas' => 0,
        'novaPagina' => false,
    ])
</div>
</body>
</html>
