@extends('layouts.pdf')
@section('title',$dados->motivoRescisao->descricao)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <br><br>
    <p class="f14"
       style="text-align: center; font-weight: bold; margin-bottom: 1.5cm; text-transform: uppercase">
        {{$dados->motivoRescisao->descricao}}<br>
    </p>
    <p class="f12" style="">
        A(o)<br>
        Prezado(a) funcionário(a): <strong>{{$dados->Feedback->Curriculo->nome}}</strong>
    </p>
    <br>
    <p class="f12" style="line-height: 26pt; text-align: justify">
        Vimos pela presente comunicar-lhe que seu <strong>CONTRATO</strong> de 90 dias termina em
        {{ !isset($dados->Feedback->AvaliacaoNoventaVencimento) ? '___/___/_______' : (new \MasterTag\DataHora($dados->Feedback->AvaliacaoNoventaVencimento->prazo_dia_final))->dataCompleta() }} sendo
        que a partir de então,
        não necessitaremos dos seus trabalhos, devendo, portanto, cessar suas atividades na referida data. Solicitamos o
        seu comparecimento no RH de nossa
        empresa {{ !isset($dados->Feedback->AvaliacaoNoventaVencimento) ? '___/___/_______' : (new \MasterTag\DataHora($dados->Feedback->AvaliacaoNoventaVencimento->prazo_dia_final))->addDia(9) }},
        munido de sua Carteira Profissional para a
        devida baixa, bem como a quitação das parcelas a que faz juz, de acordo com a Legislação vigente artigo 479,
        conforme CLT (Consolidação das Leis Trabalhistas).

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
