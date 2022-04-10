@extends('layouts.pdf')
@section('title',$dados->motivoRescisao->descricao)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <br>
    <p class="f14"
       style="text-align: center; font-weight: bold; margin-bottom: 1.5cm; text-transform: uppercase">
        MODELO DE DEMISSAO EM COMUM ACORDO<br><br>
        AVISO DE DISTRATO TRABALHISTA
    </p>
    <p class="f12" style="line-height: 22pt;text-align: justify">
        Pelo presente Instrumento Particular, que de um lado celebra a empresa
        <b>{{$dados->User->DadosEmpresa->razao_social}}</b>, inscrita no CNPJ/MF sob n.º
        <b>{{$dados->User->DadosEmpresa->cnpj}}</b>, com atividade localizada
        na {{$dados->User->DadosEmpresa->endereco_completo}}, representada por quem assina abaixo, e de outro lado
        {{$dados->Feedback->Curriculo->nome}} na função {{$dados->Feedback->VagaAberta->Vaga->nome}}, declaram para os
        devidos fins que, em conformidade com o artigo 484-A da CLT, decidem por mútuo interesse e acordo encerrar o
        contrato de trabalho vigente desde {{$dados->Feedback->Admissao->data_admissao}},
        com aviso
        prévio {{$dados->tipoAviso->descricao == 'Trabalhado' ? $dados->tipoAviso->descricao .' de 30 dias a partir desta data.' : $dados->tipoAviso->descricao .' de 15 dias a partir desta data.'}}
    </p>

    <br>
    <br>
    <p class="f12" style="line-height: 22pt;text-align: justify">
        Da mesma forma declara o empregado ter ciência de que a multa do FGTS também será depositada pela metade (20%),
        conforme artigo 484-A, I, “b” da CLT.
        <br>
        <br>
        O empregado poderá efetuar o saque de 80%(oitenta por cento) do saldo do
        FGTS, como estabelece artigo 484-A, 1º da CLT.
    </p>

    <br>
    <div class="f12" style="line-height: 26pt;margin-left: 35px">
        São Luís-MA, {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->dataCompletaExt() }}
    </div>
    <div class="f12" style="line-height: 26pt;text-align: center">
        <br>
        <br>
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$dados->Feedback->Empresa->razao_social}}
        <br><br><br>
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
