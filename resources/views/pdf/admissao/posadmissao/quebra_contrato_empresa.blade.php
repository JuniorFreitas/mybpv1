@extends('layouts.pdf')
@section('title',$dados->motivoRescisao->descricao)
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14"
       style="text-align: center; font-weight: bold; margin-bottom: 1.5cm; text-transform: uppercase">
        QUEBRA DE CONTRATO<br>
    </p>
    <br>
    <p class="f12" style="">
        Prezado(a) funcionário(a): {{$dados->Feedback->Curriculo->nome}}
        <br>
        <br>
    </p>
    <p class="f12">
        A Empresa {{$dados->Feedback->Empresa->razao_social}} inscrita no CNPJ/MF sob n.º
        <b>{{$dados->User->DadosEmpresa->cnpj}}</b> recebe a notificação que a partir do dia
        Pela presente, notificamos que a partir
        do {{ (new \MasterTag\DataHora($dados->data_desmobilizacao))->dataCompleta() }},
        fica rescindido o contrato de trabalho de <strong>{{$dados->Feedback->Curriculo->nome}}</strong> com a empresa,
        iniciado em
        {{$dados->Feedback->Admissao->data_admissao}}, com término previsto para o
        dia {{ $dados->termino_previsto ? $dados->termino_previsto : '_______/_______/______________' }}. Por isso, vimos avisá-lo, nos
        termos e para efeito do disposto no artigo 479, conforme CLT (Consolidação das Leis Trabalhistas).
        <br><br>
    </p>
    <br><br>
    <div class="f12" style="line-height: 26pt">
        Atenciosamente,
    </div>
    <div class="f12" style="line-height: 26pt;text-align: center">
        <br><br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->Feedback->Empresa->razao_social}}
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
