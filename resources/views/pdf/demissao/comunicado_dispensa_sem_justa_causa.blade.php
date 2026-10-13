@extends('layouts.pdf')
@section('title', $pdfTitle ?? 'Comunicado de dispensa')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f14" style="text-align: center; font-weight: bold; margin-bottom: 0.8cm; text-transform: uppercase">
        COMUNICADO DE DISPENSA SEM JUSTA CAUSA<br>
        AVISO PRÉVIO {{ mb_strtoupper($tipoAviso !== '' ? $tipoAviso : ($isIndenizado ? 'INDENIZADO' : 'TRABALHADO')) }}
    </p>

    <p class="f12" style="margin-bottom: 0.6cm">
        Ao(À) empregado(a): <strong>{{ $empregadoNome }}</strong>
    </p>

    <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
        Por meio deste comunicado, a empresa <strong>{{ $razaoSocial }}</strong>,
        informa a decisão de rescindir o contrato de trabalho de V.Sa., por iniciativa do empregador,
        <strong>sem justa causa</strong>, a partir de <strong>{{ $dataCiencia }}</strong>.
    </p>

    @if($isIndenizado)
        <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
            O aviso prévio será indenizado, ficando o(a) empregado(a) dispensado(a) de seu cumprimento,
            nos termos do artigo 487, § 1º, da Consolidação das Leis do Trabalho (CLT).
        </p>
        <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
            O período de aviso prévio indenizado será de <strong>{{ $diasAviso }}</strong> dias,
            observada a proporcionalidade legal conforme o tempo de serviço, nos termos da Lei nº 12.506/2011,
            e será computado para os efeitos legais cabíveis.
        </p>
    @else
        <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
            O aviso prévio será trabalhado, nos termos do artigo 487, inciso II, da Consolidação das Leis do Trabalho (CLT).
            O período de aviso prévio será de <strong>{{ $diasAviso }}</strong> dias, observada a proporcionalidade legal
            conforme o tempo de serviço, nos termos da Lei nº 12.506/2011.
        </p>
        <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
            (&nbsp;&nbsp;&nbsp;) 1ª Opção: Declaro-me ciente, optando pela redução de 02 (duas) horas diárias.<br>
            (&nbsp;&nbsp;&nbsp;) 2ª Opção: Declaro-me ciente, optando pela ausência no trabalho de 07 dias corridos.
        </p>
    @endif

    <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
        As verbas rescisórias serão apuradas e pagas no prazo legal, com a disponibilização dos documentos
        pertinentes à rescisão contratual.
    </p>

    <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.45cm">
        Solicita-se a devolução de eventuais equipamentos, documentos, uniformes, crachás e demais bens
        pertencentes à empresa que estejam sob responsabilidade do(a) empregado(a).
    </p>

    <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.8cm">
        A empresa agradece pelos serviços prestados durante o vínculo empregatício.
    </p>

    <p class="f12" style="margin-bottom: 1.2cm">
        {{ $cidade }}, {{ $dataExtenso }}.
    </p>

    <div class="f12" style="line-height: 22pt; text-align: center; margin-bottom: 1.2cm">
        <hr style="width: 10cm; margin-left: auto; margin-right: auto; border: none; border-top: 1px solid #333">
        <strong>{{ $razaoSocial }}</strong><br>
        Empregador
    </div>

    <p class="f12" style="font-weight: bold; text-align: center; margin-bottom: 0.5cm; text-transform: uppercase">
        Ciência do(a) empregado(a)
    </p>

    <p class="f12" style="line-height: 16pt; text-align: justify; margin-bottom: 0.8cm">
        Declaro ter recebido, nesta data, o presente comunicado de dispensa sem justa causa,
        com aviso prévio {{ mb_strtolower($tipoAviso !== '' ? $tipoAviso : ($isIndenizado ? 'indenizado' : 'trabalhado')) }}.
    </p>

    <div class="f12" style="line-height: 22pt; text-align: center">
        <hr style="width: 10cm; margin-left: auto; margin-right: auto; border: none; border-top: 1px solid #333">
        <strong>{{ $empregadoNome }}</strong><br>
        Empregado(a)<br><br>
        Data da ciência: {{ $dataCiencia }}
    </div>

    @include('layouts.rodapePdf')
@endsection

@push('style')
    <style type="text/css">
        .footer {
            position: absolute;
            bottom: 0px;
            font-size: 8.4pt;
        }

        .f14 {
            font-size: 13pt;
        }

        .f12 {
            font-size: 11pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }
    </style>
@endpush
