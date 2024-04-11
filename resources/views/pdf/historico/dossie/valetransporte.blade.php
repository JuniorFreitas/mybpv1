@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="margin-left: 9px; width: 96%">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <b>AUTORIZAÇÃO DE DESCONTOS VALE TRANSPORTE</b><br>
        </p>
        <p class="f11">
            EMPRESA: <br><br>

            @include('layouts.dadosEmpresa')

        </p>
        <p class="f11 text-justify" style="">
            Autorizo descontarem mensalmente e por tempo indeterminado, dos meus vencimentos, as importâncias relativas
            aos
            itens assinalados com SIM.
            <br>
            <br>
            De acordo:
            <br>
            <br>
            (&nbsp;&nbsp;&nbsp;) SIM<br><br>
            (&nbsp;&nbsp;&nbsp;) NÃO
            <br><br><br>
            <b>Identificação do Desconto:</b>
            <br>
            <br>
            Vale Transporte na forma prevista no D.L. 95.247/87.<br><br><br>
        </p>
        <div class="f11 text-justify" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        </div>
        <br><br><br><br><br><br>
        <div class="f11" style="line-height: 18pt;text-align: center">
            <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
            {{ $dados['dados_colaborador']->Curriculo->nome }}
        </div>

        <div style="position:fixed; bottom: 35px">
            @include('layouts.rodapePdfFilialJob')
        </div>
    </div>
@stop


@push('style')
    <style type="text/css">


        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
