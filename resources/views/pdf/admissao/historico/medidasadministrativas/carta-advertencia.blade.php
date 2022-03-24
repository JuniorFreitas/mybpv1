@extends('layouts.pdfCartaMedidas')
@section('title',$medida->tipo)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14"
       style="text-align: center; font-weight: bold; margin-top: 1cm; margin-bottom: 1.5cm; text-transform: uppercase">
        CARTA DE {{$medida->tipo}}</p>
    <p class="f12" style="">De: <strong>{{$medida->Feedback->Empresa->nome_fantasia}}</strong></p>
    <p class="f12" style="">Para: {{$medida->Feedback->Curriculo->nome}}</p>
    <br>
    <p class="f12" style="text-transform: uppercase">NESTA <br>
        REF.: {{$medida->causa}}.
    </p>
    <br>
    <p class="f12">
        Prezado Senhor: <strong>{{$medida->Feedback->Curriculo->nome}}</strong>
    </p>
    <br>
    <div class="f12" style="line-height: 22pt;text-align: justify">
        O Senhor trabalhando nesta empresa desde {{$medida->Feedback->Admissao->data_admissao}}, esta sendo advertido no
        dia {{$medida->data_solicitacao}}, em virtude de <strong>{{$medida->definicao}}</strong>
        ({{$medida->motivo}}) com retorno no dia ({{$medida->data_retorno}}). Em razão disso e na forma do artigo 482
        alínea h, o Sr., está sendo advertido, para que
        repense suas atitudes e passe a se adequar nas regras internas da empresa, evitando a reincidência, que poderá
        provocar outras medidas disciplinares.
        <br>
        <br>
        Sem mais <br>
        Assino a presente,
        <br>
        <br>
        São Luís-MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}
        <br>
        <br>
        <br>
        <hr style="width: 10cm;  border:none; border-top: 1px solid #333">
        {{$medida->Feedback->Empresa->razao_social}}
        <br>
        <br>
        Ciente em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        <br><br>
        <hr style="width: 10cm; margin-top: 5px;  border:none; border-top: 1px solid #333">
        {{$medida->Feedback->Curriculo->nome}}
    </div>

    <div class="footer">
        <p class="obs">
            Esse documento foi gerado automaticamente pelo usuário {{ auth()->user()->nome }}: <br>
            Sistema Integrado MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
            às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
        </p>
        <div style="width: 10cm;">
            <hr style="width: 10cm; border:none; border-top: 1px solid #999">
            {{$medida->Feedback->Empresa->razao_social}}
            <br>
            CNPJ: {{$medida->Feedback->Empresa->cnpj}} <br>
            {{$medida->Feedback->Empresa->endereco_completo}}
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
