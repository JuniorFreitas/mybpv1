<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PCMSO — {{ $dados['programa'] ?? '' }}</title>
    <style>
        @include('pdf.partials.documento_formal_styles')
        .quadro {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 12pt;
            font-size: 9.5pt;
        }
        .quadro th, .quadro td {
            border: 0.6pt solid #888;
            padding: 4pt 5pt;
            vertical-align: top;
            text-align: left;
        }
        .quadro th {
            background: #eeeeee;
            font-weight: bold;
        }
        .quadro tr {
            page-break-inside: avoid;
        }
        .ficha td.rotulo {
            width: 32%;
            font-weight: bold;
            background: #f7f7f7;
        }
        .nota {
            font-size: 9pt;
            color: #444;
            margin: 0 0 10pt;
        }
    </style>
</head>
<body>
@php
    $dados_empresa = is_array($dados['dados_empresa'] ?? null) ? $dados['dados_empresa'] : [];
    $versao = $dados['versao'] ?? [];
    $coordenador = $dados['coordenador'] ?? null;
    $cargos = $dados['cargos'] ?? [];
@endphp

<div class="folha">
    @include('pdf.partials.documento_formal_cabecalho', ['dados_empresa' => $dados_empresa])

    <p class="titulo">Programa de Controle Médico de Saúde Ocupacional — PCMSO</p>

    <table class="quadro ficha" cellpadding="0" cellspacing="0">
        <tr><td class="rotulo">Empresa</td><td>{{ $dados_empresa['razao_social'] ?? '—' }} @if(!empty($dados_empresa['cnpj'])) — CNPJ {{ $dados_empresa['cnpj'] }} @endif</td></tr>
        <tr><td class="rotulo">Programa</td><td>{{ $dados['programa'] ?? '—' }}</td></tr>
        <tr><td class="rotulo">Versão</td><td>{{ $versao['versao'] ?? '—' }} ({{ $versao['status'] ?? '—' }})</td></tr>
        <tr>
            <td class="rotulo">Vigência</td>
            <td>
                {{ $versao['vigencia_inicio_br'] ?? 'não publicada' }}
                @if(!empty($versao['vigencia_fim_br'])) a {{ $versao['vigencia_fim_br'] }} @elseif(!empty($versao['vigencia_inicio_br'])) em diante @endif
            </td>
        </tr>
        <tr>
            <td class="rotulo">Médico responsável (coordenador)</td>
            <td>
                @if($coordenador)
                    {{ $coordenador->nome }} — CRM {{ $coordenador->crm }}/{{ $coordenador->crm_uf }}@if($coordenador->rqe) — RQE {{ $coordenador->rqe }}@endif
                @else
                    Não informado
                @endif
            </td>
        </tr>
        <tr>
            <td class="rotulo">Base de riscos (PGR)</td>
            <td>{{ !empty($dados['pgr_vigente']) ? 'PGR versão ' . $dados['pgr_vigente']['versao'] . ' vigente' : 'Sem PGR publicado no sistema' }}</td>
        </tr>
        @if(!empty($versao['observacao']))
            <tr><td class="rotulo">Observações</td><td>{{ $versao['observacao'] }}</td></tr>
        @endif
    </table>

    <p class="paragrafo paragrafo--sem-recuo">
        Além do exame clínico (anamnese ocupacional e exame físico e mental) previsto na NR-7 para todos os tipos de ASO,
        os empregados realizam os exames complementares abaixo, conforme o cargo e os riscos ocupacionais a que estão expostos.
    </p>

    @if(count($cargos) === 0)
        <p class="nota">Nenhum cargo com exames complementares definidos nesta versão.</p>
    @else
        <table class="quadro" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 24%">Cargo</th>
                    <th style="width: 20%">Riscos (PGR)</th>
                    <th style="width: 24%">Exame complementar</th>
                    <th style="width: 20%">Tipos de ASO</th>
                    <th style="width: 12%">Periodicidade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cargos as $cargo)
                    @php $exames = $cargo['exames'] ?: [null]; @endphp
                    @foreach($exames as $i => $exame)
                        <tr>
                            @if($i === 0)
                                <td rowspan="{{ count($exames) }}">{{ $cargo['nome'] }}</td>
                                <td rowspan="{{ count($exames) }}">{{ $cargo['riscos'] ? implode(', ', $cargo['riscos']) : 'Ausência / não mapeado' }}</td>
                            @endif
                            @if($exame)
                                <td>{{ $exame['label'] }}@if(!$exame['obrigatorio']) (opcional)@endif</td>
                                <td>{{ implode(', ', $exame['tipos']) }}</td>
                                <td>{{ $exame['periodicidade'] ?? 'Do ASO' }}</td>
                            @else
                                <td colspan="3">Somente exame clínico</td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="nota">
        Periodicidade "Do ASO" = acompanha a validade do ASO do empregado, calculada pelo sistema conforme a NR-7
        (expostos a risco: anual; demais: até 2 anos) e as regras da empresa.
    </p>

    @include('pdf.partials.documento_formal_fechamento', [
        'localData' => 'Emitido em ' . ($dados['data_emissao'] ?? ''),
        'assinantes' => $coordenador ? [[
            'nome' => $coordenador->nome,
            'papel' => 'Médico coordenador do PCMSO — CRM ' . $coordenador->crm . '/' . $coordenador->crm_uf,
        ]] : [],
        'testemunhas' => 0,
    ])
</div>
</body>
</html>
