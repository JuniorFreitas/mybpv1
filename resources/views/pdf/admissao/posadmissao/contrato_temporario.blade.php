@extends('layouts.pdf')
@section('title',$dados->motivoRescisao->descricao)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14"
       style="text-align: center; font-weight: bold; margin-bottom: 1.5cm; text-transform: uppercase">
        COMUNICADO DE DISPENSA<br>
    </p>
    <p class="f12" style="">
        A(o)<br>
        Prezado(a) funcionário(a): {{$dados->Feedback->Curriculo->nome}}
    </p>
    <br>
    <p class="f12">
        Pela presente, notificamos que a partir do
        dia {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->dataCompleta() }},
        fica encerrado seu contrato de trabalho temporário.
        Solicitamos o seu comparecimento na {{$dados->Feedback->Empresa->razao_social}} em
        {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->addDia(6) }} para as devidas baixas.
        <br><br>
    </p>
    <br>
    <br><br>
    <div class="f12" style="line-height: 26pt">
        São Luís-MA, {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->dataCompletaExt() }}
        <br>
        <br>
        Ciente:
    </div>
    <div class="f12" style="line-height: 26pt;text-align: center">
        <br><br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->Feedback->Curriculo->nome}}
    </div>


    @include('layouts.footerDemissao')
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
