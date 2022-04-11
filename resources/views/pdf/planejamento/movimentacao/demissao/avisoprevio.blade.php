@extends('layouts.pdf')
@section('title',$demissaoPrevista->tipo_aviso)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14"
       style="text-align: center; font-weight: bold; margin-bottom: 1.5cm; text-transform: uppercase">
        DEMISSÃO SEM JUSTA CAUSA<br> AVISO {{$demissaoPrevista->tipo_aviso}} <br><br>
        AVISO PRÉVIO DO EMPREGADOR PARA DISPENSA DO EMPREGADO
    </p>
    <p class="f12" style="">
        A(o)<br>
        Sr(a). <strong>{{ $demissaoPrevista->Colaborador->nome }}</strong>
        <br><br>
    </p>
    <br>
    <p class="f12" style="text-transform: uppercase">NESTA, <br><br>
    </p>
    <br>
    <p class="f12" style="line-height: 22pt;text-align: justify">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Pelo presente o notificamos que não mais serão utilizados os seus serviços pela nossa empresa após o cumprimento
        do aviso e por isso vimos avisá-lo nesta data, nos termos e para os efeitos do disposto no Artigo 487, Item II,
        Cap. VI, Título IV do Decreto Lei n. 5452 de 01/05/1943 (CLT). O aviso prévio de 30 dias
        será {{$demissaoPrevista->tipo_aviso}}.
    </p>

    @if($demissaoPrevista->tipo_aviso == 'Trabalhado')
        <br><br>
        <p class="f12" style="line-height: 22pt;text-align: justify">
            (&nbsp;&nbsp;&nbsp;) 1ª Opção: Declaro-me ciente, optando pela redução de 02 (duas) horas diárias.<br>
            (&nbsp;&nbsp;&nbsp;) 2ª Opção: Declaro-me ciente, optando pela ausência no trabalho de 07 dias corridos.
        </p>
    @endif

    <br><br>
    <p class="f12" style="line-height: 22pt; text-align: justify">
        Pedimos a devolução do seu <strong>“CIENTE”</strong>.
    </p>
    <br>
    <div class="f12" style="line-height: 26pt;text-align: center">
        São Luís-MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}
        <br>
        <br>
        <br>
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$demissaoPrevista->Colaborador->Feedback->Empresa->razao_social}}
        <br><br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$demissaoPrevista->Colaborador->nome}}
    </div>

@include('layouts.rodapePdf')
@endsection

@push('style')
    <style type="text/css">
        .footer {
            position: absolute;
            bottom: 0px;
            font-size: 8.4pt;
            /*width: 10cm;*/
        }

        .f14 {
            font-size: 14pt;
        }

        .f12 {
            font-size: 12pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
