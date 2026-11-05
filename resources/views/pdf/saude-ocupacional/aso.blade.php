<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Atestado de Saúde Ocupacional — ASO</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        .quadro {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10pt;
            font-size: 9.5pt;
        }
        .quadro th, .quadro td {
            border: 0.6pt solid #888;
            padding: 4pt 6pt;
            vertical-align: top;
            text-align: left;
        }
        .quadro th {
            background: #eeeeee;
            font-weight: bold;
        }
        .quadro td.rotulo {
            width: 30%;
            font-weight: bold;
            background: #f7f7f7;
        }
        .secao {
            font-size: 10pt;
            font-weight: bold;
            margin: 8pt 0 4pt;
            text-transform: uppercase;
        }
        .conclusao {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            padding: 6pt;
            border: 1pt solid #333;
            margin: 6pt 0 10pt;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $empregado = $dados['empregado'] ?? [];
    $examinador = $dados['examinador'] ?? [];
    $coordenador = $dados['coordenador'] ?? null;
    $riscos = $dados['riscos'] ?? [];
    $exames = $dados['exames'] ?? [];
    $conclusao = trim((string) ($dados['conclusao'] ?? ''));
    $tipo = trim((string) ($dados['tipo'] ?? ''));
    $tipos = ['Admissional', 'Periódico', 'Retorno Trabalho', 'Mudança de Função', 'Demissional'];
    $marca = fn (bool $sim) => $sim ? '&nbsp;X&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;';
    $aptidao = fn ($valor) => in_array($valor, ['Sim', 'Não'], true) ? ($valor === 'Sim' ? 'Apto' : 'Inapto') : 'Não se aplica';
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Atestado de Saúde Ocupacional — ASO</p>

    <p class="secao">Empresa</p>
    <table class="quadro" cellpadding="0" cellspacing="0">
        <tr><td class="rotulo">Razão social</td><td>{{ $dados_empresa['razao_social'] ?? 'Não informado' }}</td></tr>
        <tr><td class="rotulo">CNPJ</td><td>{{ $dados_empresa['cnpj'] ?? 'Não informado' }}</td></tr>
    </table>

    <p class="secao">Empregado</p>
    <table class="quadro" cellpadding="0" cellspacing="0">
        <tr><td class="rotulo">Nome</td><td>{{ $empregado['nome'] ?? 'Não informado' }}</td></tr>
        <tr><td class="rotulo">CPF</td><td>{{ $empregado['cpf'] ?? 'Não informado' }}</td></tr>
        <tr><td class="rotulo">Função</td><td>{{ $empregado['funcao'] ?: 'Não informado' }}</td></tr>
    </table>

    <p class="secao">Tipo de exame</p>
    <p class="paragrafo paragrafo--sem-recuo">
        @foreach($tipos as $t)
            <span style="white-space: nowrap;">({!! $marca(mb_strtolower($tipo) === mb_strtolower($t)) !!})&nbsp;{{ $t }}</span>&nbsp;&nbsp;
        @endforeach
        @if($tipo !== '' && !in_array(mb_strtolower($tipo), array_map('mb_strtolower', $tipos), true))
            (X) {{ $tipo }}
        @endif
    </p>

    <p class="secao">Perigos ou fatores de risco (PGR)</p>
    <p class="paragrafo paragrafo--sem-recuo">
        {{ count($riscos) ? implode('; ', $riscos) . '.' : 'Ausência de fatores de risco ocupacionais específicos identificados no PGR.' }}
    </p>

    <p class="secao">Exames realizados</p>
    <table class="quadro" cellpadding="0" cellspacing="0">
        <thead><tr><th>Exame</th><th style="width: 25%">Data</th></tr></thead>
        <tbody>
            <tr><td>Exame clínico (anamnese ocupacional e exame físico e mental)</td><td>{{ $dados['data_exame_br'] ?? '' }}</td></tr>
            @foreach($exames as $exame)
                <tr><td>{{ $exame['label'] }}</td><td>{{ $exame['data'] ?? 'Não informado' }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <p class="secao">Conclusão</p>
    <div class="conclusao">{{ $conclusao !== '' ? mb_strtoupper($conclusao) : 'NÃO INFORMADO' }} PARA A FUNÇÃO</div>
    <table class="quadro" cellpadding="0" cellspacing="0">
        <tr><td class="rotulo">Trabalho em altura (NR-35)</td><td>{{ $aptidao($dados['trabalho_altura'] ?? null) }}</td></tr>
        <tr><td class="rotulo">Espaço confinado (NR-33)</td><td>{{ $aptidao($dados['espaco_confinado'] ?? null) }}</td></tr>
    </table>

    <p class="secao">Médicos</p>
    <table class="quadro" cellpadding="0" cellspacing="0">
        <tr>
            <td class="rotulo">Médico responsável pelo PCMSO</td>
            <td>{{ $coordenador ? $coordenador['nome'] . ' — ' . $coordenador['crm'] : 'Não informado' }}</td>
        </tr>
        <tr>
            <td class="rotulo">Médico examinador</td>
            <td>{{ ($examinador['nome'] ?? 'Não informado') }}@if(!empty($examinador['crm'])) — CRM {{ $examinador['crm'] }}/{{ $examinador['crm_uf'] ?? '' }}@endif</td>
        </tr>
    </table>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'Data do exame: ' . ($dados['data_exame_br'] ?? ''),
        'assinantes' => [
            [
                'nome' => $examinador['nome'] ?? 'Médico examinador',
                'papel' => 'Médico examinador' . (!empty($examinador['crm']) ? ' — CRM ' . $examinador['crm'] . '/' . ($examinador['crm_uf'] ?? '') : ''),
            ],
            [
                'nome' => $empregado['nome'] ?? 'Empregado',
                'papel' => 'Empregado — recebi a 1ª via deste ASO',
            ],
        ],
        'testemunhas' => 0,
    ])
</div>
</body>
</html>
