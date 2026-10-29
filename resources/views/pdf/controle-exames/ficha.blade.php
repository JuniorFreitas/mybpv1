<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ficha de Encaminhamento</title>
    <style>
        html, body, .folha, .folha * {
            font-family: DejaVu Sans, Arial, sans-serif !important;
        }
        @page {
            size: A4 portrait;
            margin: 5mm 10mm 16mm 10mm;
        }
        html, body {
            font-size: 11pt;
            color: #000;
            background: #fff;
        }
        .folha { width: 100%; }
        .cabecalho {
            text-align: center;
            margin: 0 0 18pt;
            padding: 0 0 8pt;
            border-bottom: 1pt solid #000;
        }
        .cabecalho__logo { margin: 0 auto 4pt; }
        .cabecalho__logo img {
            display: block;
            margin: 0 auto;
            max-height: 16mm;
            max-width: 48mm;
        }
        .cabecalho__logo--compact img { max-height: 12mm; }
        .cabecalho__razao {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.35;
            margin: 0 0 2pt;
        }
        .cabecalho__meta {
            font-size: 8.5pt;
            line-height: 1.35;
            margin: 0;
        }
        .titulo {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.5;
            margin: 0 0 22pt;
        }
        .paragrafo {
            font-size: 11pt;
            line-height: 1.7;
            text-align: justify;
            margin: 0 0 14pt;
        }
        .paragrafo--sem-recuo {
            text-indent: 0;
        }
        .clinica-nome {
            font-weight: bold;
            margin: 0 0 2pt;
        }
        .clinica-meta {
            font-size: 10pt;
            line-height: 1.45;
            margin: 0 0 12pt;
        }
        .setor-titulo {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 14pt 0 6pt;
        }
        .setor-caixa {
            width: 100%;
            border: 1pt solid #555;
            padding: 8pt 10pt;
            margin: 0 0 10pt;
            line-height: 1.55;
            font-size: 10.5pt;
        }
        .setor-caixa .item {
            margin: 0 0 4pt;
        }
        .assinatura {
            text-align: center;
            font-size: 11pt;
            line-height: 1.5;
            margin: 42pt 0 0;
        }
        .assinatura__linha {
            width: 9cm;
            margin: 0 auto 6pt;
            border: none;
            border-top: 1pt solid #000;
        }
    </style>
</head>
<body>
@php
    $empresaPdf = is_array($ExameFuncionario->dados_empresa ?? null)
        ? $ExameFuncionario->dados_empresa
        : (is_array($dados_empresa ?? null) ? $dados_empresa : []);
    $razaoPdf = trim((string) ($empresaPdf['razao_social'] ?? $empresaPdf['nome_fantasia'] ?? ''));
    $logoPdf = trim((string) ($empresaPdf['logo'] ?? ''));
    $cnpjPdf = trim((string) ($empresaPdf['cnpj'] ?? ''));
    $endPdf = trim((string) ($empresaPdf['endereco_completo'] ?? ''));
    $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
    $clinica = $ExameFuncionario->EmpresaExame;
    $clinicaDados = is_array($clinica->dados ?? null) ? $clinica->dados : [];
    $clinicaEndereco = $clinicaDados['endereco']['endereco_completo']
        ?? $clinicaDados['endereco_completo']
        ?? '';
    $clinicaTelefone = $clinicaDados['telefone'] ?? '';
    $curriculo = $ExameFuncionario->Feedback->Curriculo ?? null;
    $respostas = is_array($ExameFuncionario->respostas ?? null) ? $ExameFuncionario->respostas : [];
@endphp

<div class="folha">
    <div class="cabecalho">
        @if($logoPdf !== '')
            <div class="cabecalho__logo {{ $logoCompacto ? 'cabecalho__logo--compact' : '' }}">
                <img src="{{ $logoPdf }}" alt="{{ $razaoPdf }}">
            </div>
        @endif
        @if($razaoPdf !== '')
            <div class="cabecalho__razao">{{ $razaoPdf }}</div>
        @endif
        @if($cnpjPdf !== '' || $endPdf !== '')
            <div class="cabecalho__meta">
                @if($cnpjPdf !== '')
                    CNPJ: {{ $cnpjPdf }}
                @endif
                @if($cnpjPdf !== '' && $endPdf !== '')
                    <br>
                @endif
                @if($endPdf !== '')
                    {{ $endPdf }}
                @endif
            </div>
        @endif
    </div>

    <p class="titulo">Ficha de Encaminhamento</p>

    <p class="paragrafo paragrafo--sem-recuo">
        A
    </p>

    @if($clinica)
        <div class="clinica-nome">{{ $clinica->nome }}</div>
        <div class="clinica-meta">
            @if($clinicaEndereco !== '')
                {{ $clinicaEndereco }}<br>
            @endif
            @if($clinicaTelefone !== '')
                FONE: {{ $clinicaTelefone }}
            @endif
        </div>
    @endif

    <p class="paragrafo paragrafo--sem-recuo">
        Estamos encaminhando {{ $curriculo->nome ?? '—' }},
        {{ $curriculo->idade ?? '—' }} ANOS.
    </p>

    @php
        $identificacaoPdf = is_array($identificacao ?? null) ? $identificacao : [];
        $lotacaoPdf = trim((string) ($identificacaoPdf['lotacao'] ?? ''));
        $centroCustoPdf = trim((string) ($identificacaoPdf['centro_custo'] ?? ''));
        $cargoPdf = trim((string) ($identificacaoPdf['cargo'] ?? ''));
    @endphp
    <p class="paragrafo paragrafo--sem-recuo">
        Lotação: {{ $lotacaoPdf !== '' ? $lotacaoPdf : 'Não informado' }}<br>
        Centro de custo: {{ $centroCustoPdf !== '' ? $centroCustoPdf : 'Não informado' }}<br>
        Cargo: {{ $cargoPdf !== '' ? $cargoPdf : 'Não informado' }}
    </p>

    <p class="paragrafo paragrafo--sem-recuo">
        @if($ExameFuncionario->pcmso)
            Para fazer exame de ordem {{ $ExameFuncionario->ExameTipo->label ?? '' }}, conforme
            <strong>PCMSO: {{ $ExameFuncionario->PcmsoDados->label ?? '' }}</strong>
        @else
            Para fazer os seguintes exames:
        @endif
    </p>

    @if(!$ExameFuncionario->pcmso && $ExameFuncionario->Formulario)
        @foreach($ExameFuncionario->Formulario->Setores as $setor)
            <div class="setor-titulo">
                {{ $setor->nome == 'EXAME DE ORDEM' ? 'EXAMES' : $setor->nome }}
            </div>
            <div class="setor-caixa">
                @foreach($setor->Alternativas as $alternativa)
                    @php
                        $chave = 'alternativa_id_' . $alternativa->id;
                        $valor = $respostas[$chave]['valor'] ?? null;
                    @endphp
                    @if($valor)
                        <div class="item">
                            @if($alternativa->tipo == 'select')
                                {{ $alternativa->nome }}:
                                {{ \App\Models\RespostaAlternativas::find($valor)->label ?? $valor }}
                            @endif
                            @if($alternativa->tipo == 'checkbox')
                                {{ $valor ? '(X)' : '( )' }} {{ $alternativa->nome }}
                            @endif
                            @if($alternativa->tipo == 'text')
                                {{ $alternativa->nome }}: {{ $valor }}
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    @endif

    <div class="assinatura">
        <hr class="assinatura__linha">
        Assinatura autorizada
    </div>
</div>
</body>
</html>
