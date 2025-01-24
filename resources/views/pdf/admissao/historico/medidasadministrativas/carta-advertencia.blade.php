@extends('layouts.pdfCartaMedidas')
@section('title',$medida->tipo)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14"
       style="text-align: center; font-weight: bold; margin-top: 1cm; margin-bottom: 1.5cm; text-transform: uppercase">
        CARTA DE {{$medida->tipo == 'Re-orientação' ? 'Orientação' : $medida->tipo}}</p>
    <p class="f12" style="">DE: <strong>{{$medida->Feedback->Empresa->nome_fantasia}}</strong></p>
    <p class="f12" style="">PARA: {{$medida->Feedback->Curriculo->nome}}</p>
    <br>
    <p class="f12" style="text-transform: uppercase">NESTA <br>
        REF.: {{$medida->causa}}.
    </p>
    <br>
    <p class="f12">
        Prezado(a) Senhor(a): <strong>{{$medida->Feedback->Curriculo->nome}}</strong>
    </p>
    <br>
    <div class="f12" style="line-height: 22pt;text-align: justify">
        O(A) Senhor(a) trabalhando nesta empresa desde {{$medida->Feedback->Admissao->data_admissao}}, esta
        sendo {{$medida->TipoMedida}} no
        dia {{$medida->data_solicitacao}}, em virtude de <strong>{{$medida->definicao}}</strong>
        ({{$medida->motivo}}
        )
        @if(!in_array($medida->tipo,['Advertência Escrita', 'Advertência Verbal', 'Desligamento', 'Re-orientação',]))
            com retorno no dia ({{$medida->data_retorno}})
        @endif().
        Em razão disso, o(a) Sr(a)., está sendo advertido(a), para que repense suas
        atitudes e passe a se adequar nas regras internas da empresa, evitando a reincidência, que poderá provocar
        outras medidas disciplinares.
        <br>
        <br>
        Sem mais <br>
        Assino a presente,
        <br>
        <br>
        São Luís-MA, {{ (new \MasterTag\DataHora($medida->data_solicitacao))->dataCompletaExt() }}.
        <br>
        <br>
        <br>
        <hr style="width: 10cm;  border:none; border-top: 1px solid #333">
        {{$medida->Feedback->Empresa->razao_social}}
        <br>
        <br>
        Ciente em {{ (new \MasterTag\DataHora($medida->data_solicitacao))->dataCompleta() }}
        <br><br>
        <hr style="width: 10cm; margin-top: 5px;  border:none; border-top: 1px solid #333">
        {{$medida->Feedback->Curriculo->nome}}
    </div>

    @include('layouts.rodapePdf')
@endsection

@push('style')
    <style type="text/css">


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
