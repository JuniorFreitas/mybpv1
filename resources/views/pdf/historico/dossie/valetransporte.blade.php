@extends('layouts.pdf')
@section('title','AUTORIZAÇÃO DE DESCONTOS VALE TRANSPORTE')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>AUTORIZAÇÃO DE DESCONTOS VALE TRANSPORTE</b><br>
    </p>
    <p class="f11">
        EMPRESA: <br><br>

        @include('layouts.dadosEmpresa')

    </p><br>
    <p class="f11" style="">
        Autorizo descontarem mensalmente e por tempo indeterminado, dos meus vencimentos, as importâncias relativas aos
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
    <div class="f11" style="line-height: 26pt">
        {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        <br>
        São Luís, MA.
        <br>
        <br>
        <br>
        <br>
    </div>
    <div class="f11" style="line-height: 18pt;text-align: center">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
    </div>
    <br>
    <div class="footer">
        <p class="obs">
            Esse documento foi gerado automaticamente pelo usuário {{ auth()->user()->nome }}: <br>
            Sistema Integrado MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
            às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
        </p>
        <div>
            <hr style="border:none; border-top: 1px solid #999">
            {{$dados->User->DadosEmpresa->razao_social}}<br>
            CNPJ: {{$dados->User->DadosEmpresa->cnpj}} <br>
            {{$dados->User->DadosEmpresa->endereco_completo}}
        </div>
    </div>
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

        .f11 {
            font-size: 11pt;
        }

        .f12 {
            font-size: 12pt;
        }

        .f10 {
            font-size: 10pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
