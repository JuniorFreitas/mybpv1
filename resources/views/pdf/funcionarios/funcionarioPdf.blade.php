@extends('layouts.pdf')
@section('empresa')
    @include('layouts.cabecalioEmpresa',['empresa' => $funcionario[0]->empresa])
@endsection
@section('title','Ficha de Funcionário')
@section('conteudo')
    <h2 class="text-center" style="margin-top: 0px; font-size: 13pt;">DADOS DO TRABALHADOR</h2>
    <div class="tg-wrap" style="margin-bottom: 0px; padding-bottom: 0px">
        <table class="tg">
            <tr>
                <th class="tg-0lax" colspan="2">
                    <p style="text-align:justify; font-size: 8.5pt; line-height: 16px; text-align: justify; border: 1px solid #333333; height: 3.90cm; padding: 2px 10px 2px 10px; margin-left: -5px">
                        <strong>{{$funcionario[0]->nome}}</strong>, <strong>{{\MasterTag\DataHora::diferencaAnos($funcionario[0]->nascimento,\Carbon\Carbon::now())}} anos</strong>,  <strong>{{$funcionario[0]->estadocivil}}</strong>, portador
                        da C.T.P.S. n.º <strong>{{$funcionario[0]->ctps_numero}}</strong>
                        série <strong>{{$funcionario[0]->ctps_serie}}</strong>, CPF/CIC n.º:
                        <strong>{{$funcionario[0]->cpf}}</strong>,
                        Titulo de Eleitor n.º <strong>{{$funcionario[0]->tituloeleitoral}}</strong> da
                        <strong>{{$funcionario[0]->tituloeleitoral_zona}}</strong>
                        zona;
                        @php
                            $doc = explode(' -',$funcionario[0]->doc_tipo);
                        @endphp
                        <strong>{{$doc[0]}}</strong>
                        n.º
                        <strong>{{$funcionario[0]->doc_numero}}</strong>, data de emissão <strong>{{$funcionario[0]->doc_emissao}}</strong>,  foi admitido
                        em <strong>{{\App\Models\Sistema::dataExtensa($funcionario[0]->admissao)}}</strong>
                        para exercer a função de <strong>{{mb_strtoupper($funcionario[0]->cargo->titulo)}}</strong>,
                        na empresa <strong>{{$funcionario[0]->empresa->razao_social}}</strong> no
                        posto
                        <strong>{{$funcionario[0]->cliente->tipo == 'pessoa_juridica' ? $funcionario[0]->cliente->clientepj->razaosocial.' ('.$funcionario[0]->cliente->clientepj->nomefantasia.')' : $funcionario[0]->cliente->clientepf->nome }}</strong>
                        , com o salário de
                        R$ <strong>{{$funcionario[0]->remuneracao_valor}}
                            ({{ucfirst(valorPorExtenso($funcionario[0]->remuneracao_valor))}})</strong>
                        por {{$funcionario[0]->forma_pagamento == 'Mensal' ? 'mês' : $funcionario[0]->forma_pagamento}}.
                    </p>




                    {{--<p style="text-decoration: underline; font-weight: bold">Caracteristicos--}}
                        {{--físicos</p>--}}
                    {{--<p style="font-size: 8pt; line-height: 16px">Cor:--}}
                        {{--<strong>{{$funcionario[0]->racaecor ? mb_strtoupper($funcionario[0]->racaecor) : '.............................'}}</strong>--}}
                        {{--<br/>--}}
                        {{--Cabelos:--}}
                        {{--<strong>{{$funcionario[0]->cabelos ? $funcionario[0]->cabelos : '..........................................'}}</strong>--}}
                        {{--<br/>--}}
                        {{--Olhos:--}}
                        {{--<strong>{{$funcionario[0]->olhos ? $funcionario[0]->olhos : '..........................................'}}</strong>--}}
                        {{--<br/>--}}
                        {{--Altura:--}}
                        {{--<strong>{{$funcionario[0]->altura ? $funcionario[0]->altura.'cm' : '..........................................'}}</strong>--}}
                        {{--<br/>--}}
                        {{--Peso:--}}
                        {{--<strong>{{$funcionario[0]->peso ? $funcionario[0]->peso.'kg' : '..........................................'}}</strong>--}}
                        {{--<br/>--}}
                        {{--Sinais:--}}
                        {{--<strong>{{$funcionario[0]->sinais ? $funcionario[0]->sinais : '..........................................'}}</strong>--}}
                        {{--<br/>--}}
                        {{--Deficiente: {{$funcionario[0]->deficiente ? 'Sim' : 'Não' }}<br>--}}
                        {{--@if($funcionario[0]->deficiente)--}}
                            {{--Deficiencia obs.: {{$funcionario[0]->deficiencia_obs}}<br>--}}
                        {{--@endif--}}
                    {{--</p>--}}
                </th>
                {{--<th class="tg-0lax" style="text-align: left">--}}
                    {{--<p style="text-decoration: underline; font-weight: bold">Informações adicionais</p>--}}
                    {{--<p style="font-size: 8pt; line-height: 16px">--}}
                        {{--Camisa: <strong>{{$funcionario[0]->camisa}}</strong><br/>--}}
                        {{--Calça: <strong>{{$funcionario[0]->calca}}</strong><br/>--}}
                        {{--Sapato: <strong>{{$funcionario[0]->sapato}}</strong><br/>--}}
                        {{--Idade: <strong>{{\MasterTag\DataHora::diferencaAnos($funcionario[0]->nascimento,\Carbon\Carbon::now())}}--}}
                            {{--anos</strong>--}}
                    {{--</p>--}}
                {{--</th>--}}
                <th class="tg-0lax" style="text-align: right">
                    <img
                        src="{{count($funcionario[0]->fotos)>0 ? \App\Models\Sistema::convertBase('app/g/arquivos/fotos-funcionarios/'.$funcionario[0]->fotos[0]->file): \App\Models\Sistema::convertBase('app/g/arquivos/fotos-funcionarios/semfoto.jpg') }}"
                        style="height: 4cm">
                </th>
            </tr>
        </table>

        {{--<div style="border: 1px solid #000000; padding: 4px;">--}}
            {{--<p style="font-size: 8.5pt; line-height: 16px; text-align: justify;padding-bottom: -10px">--}}
                {{--<strong>{{$funcionario[0]->nome}}</strong>, <strong>{{\MasterTag\DataHora::diferencaAnos($funcionario[0]->nascimento,\Carbon\Carbon::now())}} anos</strong>,  <strong>{{$funcionario[0]->estadocivil}}</strong> portador--}}
                {{--da C.T.P.S. n.º <strong>{{$funcionario[0]->ctps_numero}}</strong>--}}
                {{--série <strong>{{$funcionario[0]->ctps_serie}}</strong>, CPF/CIC n.º:--}}
                {{--<strong>{{$funcionario[0]->cpf}}</strong>,--}}
                {{--Titulo de Eleitor n.º <strong>{{$funcionario[0]->tituloeleitoral}}</strong> da--}}
                {{--<strong>{{$funcionario[0]->tituloeleitoral_zona}}</strong>--}}
                {{--zona;--}}
                {{--@php--}}
                    {{--$doc = explode(' -',$funcionario[0]->doc_tipo);--}}
                {{--@endphp--}}
                {{--<strong>{{$doc[0]}}</strong>--}}
                {{--n.º--}}
                {{--<strong>{{$funcionario[0]->doc_numero}}</strong>, foi admitido--}}
                {{--em <strong>{{\App\Models\Sistema::dataExtensa($funcionario[0]->admissao)}}</strong>--}}
                {{--para exercer a função de <strong>{{mb_strtoupper($funcionario[0]->cargo->titulo)}}</strong>,--}}
                {{--na empresa <strong>{{$funcionario[0]->empresa->razao_social}}</strong> no--}}
                {{--posto--}}
                {{--<strong>{{$funcionario[0]->cliente->tipo == 'pessoa_juridica' ? $funcionario[0]->cliente->clientepj->razaosocial.' ('.$funcionario[0]->cliente->clientepj->nomefantasia.')' : $funcionario[0]->cliente->clientepf->nome }}</strong>--}}
                {{--, com o salário de--}}
                {{--R$ <strong>{{$funcionario[0]->remuneracao_valor}}--}}
                    {{--({{ucfirst(valorPorExtenso($funcionario[0]->remuneracao_valor))}})</strong>--}}
                {{--por {{$funcionario[0]->forma_pagamento == 'Mensal' ? 'mês' : $funcionario[0]->forma_pagamento}}.--}}
            {{--</p>--}}
        {{--</div>--}}

    </div>


   {{-- @if($funcionario[0]->fgts)--}}
        {{--<div style="border: 1px solid #000; text-align: center;padding: 2px;margin-top: 2px;">--}}
            {{--<strong style="font-size: 8pt; text-transform: uppercase;">Situação perante o fundo de garantia do tempo de--}}
                {{--serviço</strong>--}}
        {{--</div>--}}
        {{--<div--}}
            {{--style="border-bottom: 1px solid #000;border-right: 1px solid #000; border-left: 1px solid #000; width: 100%">--}}
            {{--<div class="txtdiv"--}}
                 {{--style="border-right: 1px solid #000; width: 15%; line-height: 14px; font-size: 8.5pt; text-align: center; float: left">--}}
                {{--É optante ?--}}
                {{--<strong>{{$funcionario[0]->fgts ? 'Sim' : 'Não'}}</strong>--}}
            {{--</div>--}}

            {{--<div class="txtdiv"--}}
                 {{--style="border-right: 1px solid #000; width: 20%; line-height: 14px; font-size: 8.5pt; text-align: center; float: left">--}}
                {{--Data da opção:--}}
                {{--<strong>{{$funcionario[0]->fgts_admissao}}</strong>--}}
            {{--</div>--}}

            {{--<div class="txtdiv"--}}
                 {{--style="border-right: 1px solid #000; font-size: 8.5pt; line-height: 14px; width: 22%; text-align: center; float: left">--}}
                {{--Data da retratação:--}}
                {{--<strong>{{$funcionario[0]->fgts_retratacao}}</strong>--}}
            {{--</div>--}}

            {{--<div class="txtdiv"--}}
                 {{--style=" width: 45%; font-size: 8.5pt; text-align: left; line-height: 14px; margin-left: 10px; float: left">--}}
                {{--Banco depositário:--}}
                {{--<strong>{{$funcionario[0]->fgts_banco ? $funcionario[0]->fgts_banco : 'Não Informado' }}</strong>--}}
            {{--</div>--}}
            {{--<div style="clear: both"></div>--}}
        {{--</div>--}}
    {{--@endif--}}

    <div
        style="border: 1px solid #000; width: 100%; margin-top: -17px; ">
        <div class="txtdiv"
             style="border-right: 1px solid #000; width: 44%; font-size: 8.5pt; height: 9cm; line-height: 16px; padding: 5px; float: left">
            Nacionalidade: <strong>{{$funcionario[0]->nacionalidade}}</strong><br/>
            Grau de instrução: <strong>{{substr($funcionario[0]->escolaridade->tipo,5)}}</strong>
            <br/>Filho
            de
            <strong>{{$funcionario[0]->pai ? $funcionario[0]->pai.' e de '.$funcionario[0]->mae : $funcionario[0]->mae}}</strong>
            Nascido em <strong>{{$funcionario[0]->naturalidade}}</strong> no
            dia <strong>{{\App\Models\Sistema::dataExtensa($funcionario[0]->nascimento)}}</strong>. <br/>
            @if($funcionario[0]->estadocivil == 'Casado' || $funcionario[0]->estadocivil == 'União Estável')
                Conjunge: <strong>{{$funcionario[0]->conjuge}}</strong><br>
                CPF: <strong>{{$funcionario[0]->conjuge_cpf}}</strong><br>
                RG: <strong>{{$funcionario[0]->conjuge_rg}}</strong><br>
                Nascimento: <strong>{{$funcionario[0]->conjuge_nascimento}}</strong><br>
            @endif
            Residente: <strong>{{$funcionario[0]->logradouro}}, {{$funcionario[0]->end_numero}}
                , {{$funcionario[0]->bairro}}
                , {{$funcionario[0]->complemento ? $funcionario[0]->complemento : ''}},
                CEP: {{$funcionario[0]->cep}}
                , {{$funcionario[0]->municipio}}-{{$funcionario[0]->uf}}</strong>
            <br/>
            @if($funcionario[0]->sexo=='Masculino')
                Certificado Militar n.º <strong>{{$funcionario[0]->reservista}}</strong>
                <br>
                Categoria: <strong>{{$funcionario[0]->reservista_categoria}}</strong>
            @endif
            <br/>
            Primeiro emprego: <strong>{{$funcionario[0]->primeiro_emprego ? 'Sim' : 'Não'}}</strong><br>
            PIS: <strong>{{$funcionario[0]->nis}}</strong><br/>
            Exame Admissional: <strong>{{$funcionario[0]->admissional}}</strong><br/>
            CRM Admissional: <strong>{{$funcionario[0]->admissional_crm}}</strong><br/>
            E-mail: <strong>{{mb_strtolower($funcionario[0]->email)}}</strong><br/>
            @if(count($funcionario[0]->telefones) > 0 )
                @foreach($funcionario[0]->telefones as $tel)
                    {{ucfirst($tel['tipo'])}}: <strong>{{$tel['numero']}}</strong> |
                @endforeach
                @else
                NENHUM CONTATO CADASTRADO
            @endif


        </div>

        <div class="txtdiv" style="border-right: 1px solid #000; width: 27%; height: 9.3cm;  float: left">
            {{--<div style="border-bottom: 1px solid #000; text-align: center; padding: 4px">--}}
            {{--<strong style="font-size: 10pt; text-transform: uppercase;">Quando Estrangeiro</strong>--}}
            {{--</div>--}}
            {{--<div style="padding: 5px">--}}
            {{--Carteira modelo 19 n.º ...............................<br/>--}}
            {{--.................................................................... <br/>--}}
            {{--N.º Registro Geral ......................................<br/>--}}
            {{--....................................................................<br/>--}}
            {{--Casado(a) c/ brasileira(o)? ........................<br/>--}}
            {{--Nome do cônjunge ....................................<br/>--}}
            {{--....................................................................<br/>--}}
            {{--....................................................................<br/>--}}
            {{--Tem filhos brasileiros? ..............................<br/>--}}
            {{--Quantos? ....................................................<br/>--}}
            {{--Data da chegada ao Brasil<br/>--}}
            {{--....................de......................de...................<br/>--}}
            {{--Naturalizado................................................<br/>--}}
            {{--Decreto n.º..................................................<br/>--}}
            {{--</div>--}}

            {{--<div style="border-bottom: 1px solid #000; text-align: center; padding: 2px">--}}
            {{--<strong style="font-size: 8.5pt; text-transform: uppercase;">Horários</strong>--}}
            {{--</div>--}}
            <div style="padding: 3px; font-size: 8.5pt;">

                @foreach($funcionario[0]->horarios as $c=>$h)
                    <p class="text-center"
                       style="margin-bottom: 5px;padding-bottom: 0px; font-size: 8.5pt; line-height: 13px"><strong>Horário {{$c+1}}</strong>
                    </p>
                    Turno
                    1:
                    <strong>{{$h->saida_turnoum == '00:00' ? $h->entrada_turnoum.'h' : $h->entrada_turnoum.'h às '. $h->saida_turnoum.'h' }}</strong>
                    @if(($h->entrada_turnodois === '00:00:00' && $h->saida_turnodois === '00:00:00') || empty($h->entrada_turnodois) && empty($h->saida_turnodois))
                        <br>
                    @else
                        <br>
                        Turno
                        2:
                        <strong>{{$h->entrada_turnodois === '00:00' ? $h->saida_turnodois.'h'  : $h->saida_turnodois.'h'  }}</strong>
                        <br>
                    @endif
                    Dias: <strong>
                        {{$h->dom ? 'Domingo, ':''}}
                        {{$h->seg ? 'Segunda-feira, ':''}}
                        {{$h->ter ? 'Terça-feira, ':''}}
                        {{$h->qua ? 'Quarta-feira, ':''}}
                        {{$h->qui ? 'Quinta-feira, ':''}}
                        {{$h->sex ? 'Sexta-feira, ':''}}
                        {{$h->sab ? 'Sábado, ':''}}</strong>

                    <br/>
                    Jornada de Trabalho: <strong>{{$h->jornada}}</strong>

                    <hr style="border-bottom: 0.1px solid #000000; height: 0.1px">
                @endforeach


            </div>

        </div>

        <div class="txtdiv" style="width: 27.3%; height: 9cm;  float: left">
            <div
                style="font-size: 8pt; border-bottom: 1px solid #000; text-align: center; padding-top: 2px;padding-bottom: 2px">
                <strong style="font-size: 8pt; text-transform: uppercase;">Dados Bancários</strong>
            </div>
            <div style="padding: 3px; font-size: 8.5pt; line-height: 16px">
                Banco: <strong>{{$funcionario[0]->banco->nome}}</strong><br>
                Nº da agência: <strong>{{$funcionario[0]->agencia}}</strong><br>
                Tipo de conta: <strong>{{$funcionario[0]->tipo_conta}}</strong><br>
                Nº da conta: <strong>{{$funcionario[0]->numero}}</strong><br>
            </div>

            <div
                style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: center; padding-top: 2px;padding-bottom: 2px">
                <strong style="font-size: 8pt; text-transform: uppercase;">Benefícios</strong>
            </div>

            <div style="padding: 5px; font-size: 8.5pt; line-height: 16px">
                Vale transporte: <strong>{{$funcionario[0]->vale_transporte ? 'Sim' : 'Não'}}</strong><br>
                {!! $funcionario[0]->vale_transporte_linhaum != '0,00' ? 'Linha 1 valor: R$ <strong>'.$funcionario[0]->vale_transporte_linhaum.'</strong><br>' : ''!!}
                {!! $funcionario[0]->vale_transporte_linhadois != '0,00' ? 'Linha 2 valor: R$ <strong>'.$funcionario[0]->vale_transporte_linhadois.'</strong><br>' : ''!!}
                Desconto do plano de saúde:
                <strong>{{$funcionario[0]->desconto_plano_saude ? 'Sim' : 'Não'}}</strong><br/>
                @if($funcionario[0]->desconto_plano_saude)
                    Valor do desconto do plano de saúde:
                    <strong>{{$funcionario[0]->desconto_plano_saude_valor}}</strong><br/>
                @endif
                Desconto VR/VA: <strong>{{$funcionario[0]->descontar_vr_va ? 'Sim' : 'Não'}}</strong><br/>
                Valor pago VR/VA R$:
                <strong>{{$funcionario[0]->valor_desconto_vr ? $funcionario[0]->valor_desconto_vr : 'Não informado'}}</strong>
            </div>
        </div>

        <div style="clear: both"></div>
    </div>


    @if(count($funcionario[0]->dependentes)>0)
        <div style="border: 1px solid #000; text-align: center;padding: 2px; margin-top: 3px;">
            <strong style="font-size: 9pt; text-transform: uppercase;">Dependentes filhos menores de 14 anos</strong>
        </div>
        @foreach($funcionario[0]->dependentes as $b)
            <div
                style="border-bottom: 1px solid #000; font-size: 7pt;border-right: 1px solid #000; border-left: 1px solid #000; width: 100%">
                <div class="txtdiv" style="padding: 3px; font-size: 8.5pt">
                    Nome: <strong>{{$b->nome}}</strong> {!!  $b->cpf ? '| CPF: <strong>'.$b->cpf.'</strong>' : '' !!} |
                    Nascimento: <strong>{{$b->nascimento}}</strong> |
                    Idade: <strong>{{\MasterTag\DataHora::diferencaAnos($b->nascimento,\Carbon\Carbon::now())}}
                        anos</strong>
                </div>
            </div>
        @endforeach
    @endif

    <div
        style="border: 1px solid #000; margin-top: 5px; font-size: 7pt; width: 100%">
        <div class="txtdiv" style="padding: 3px; font-size: 8.5pt">
            Cor: <strong>{{$funcionario[0]->racaecor ? mb_strtoupper($funcionario[0]->racaecor) : 'Não informado'}}</strong>,
            Cabelos:
            <strong>{{$funcionario[0]->cabelos ? $funcionario[0]->cabelos : 'Não informado'}}</strong>,
            Olhos:
            <strong>{{$funcionario[0]->olhos ? $funcionario[0]->olhos : 'Não informado'}}</strong>,
            Altura:
            <strong>{{$funcionario[0]->altura ? $funcionario[0]->altura.'cm' : 'Não informado'}}</strong>,
            Peso:
            <strong>{{$funcionario[0]->peso ? $funcionario[0]->peso.'kg' : 'Não informado'}}</strong>,
            Sinais:
            <strong>{{$funcionario[0]->sinais ? $funcionario[0]->sinais : 'Não informado'}}</strong>,
            Deficiente: <strong>{{$funcionario[0]->deficiente ? 'Sim' : 'Não' }}</strong>
            @if($funcionario[0]->deficiente)
                , Deficiencia obs.: <strong>{{$funcionario[0]->deficiencia_obs}}</strong><br>
            @endif
        </div>
    </div>

    <div
        style="border-bottom: 1px solid #000; font-size: 7pt;border-right: 1px solid #000; border-left: 1px solid #000; width: 100%">
        <div class="txtdiv" style="padding: 3px; font-size: 8.5pt">
            Camisa: <strong>{{$funcionario[0]->camisa}}</strong>,
            Calça: <strong>{{$funcionario[0]->calca}}</strong>,
            Sapato: <strong>{{$funcionario[0]->sapato}}</strong>,
        </div>
    </div>

    <p style="margin-top: 10px; font-size: 8.5pt;">
        Obs.: {{$funcionario[0]->observacoes}}
    </p>

    <p style="margin-top: 10px; font-size: 8.5pt;">
        São Luís-MA, {{\App\Models\Sistema::dataExtensa($funcionario[0]->admissao)}}
        <br>
        <br>
        <span style="margin-left: 28%;">
            _______________________________________________ <br>
        </span>
        <span style="margin-left: 40%;">
            Assinatura do Empregado</span>
    </p>


@endsection
@push('style')
    {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"--}}
    {{--integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">--}}

    <style type="text/css">
        .clearfix {
            clear: both;
        }

        .p100 {
            width: 100%;
        }

        .p20 {
            width: 20%;
        }

        p {
            margin-top: 0px;
            font-size: 10pt;
        }

        .txtdiv {
            font-size: 10pt;
            line-height: 18px;
        }

        .brd {
            border: 1px solid #444444;
            padding: 7px;
        }

        h2 {
            margin-bottom: 5px;
        }

        h6 {
            margin-top: 5px;
            margin-bottom: 0px;
            text-transform: uppercase;
        }

        h5 {
            margin-top: 0px;
            margin-bottom: 2px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .mt10 {
            margin-top: 10cm;
        }

        .text-center {
            text-align: center;
        }

        .tg {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%
        }

        .tg td {
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 10px 5px;
            border-style: solid;
            border-width: 1px;
            overflow: hidden;
            word-break: normal;
            border-color: transparent;
        }

        .tg th {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            padding: 10px 5px;
            border-style: solid;
            border-width: 1px;
            overflow: hidden;
            word-break: normal;
            border-color: transparent;
        }

        .tg .tg-0lax {
            text-align: left;
            vertical-align: top
        }


        tg1 {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .tg1 td {
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 10px 5px;
            border-style: solid;
            border-width: 1px;
            overflow: hidden;
            word-break: normal;
            border-color: black;
        }

        .tg1 th {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            padding: 10px 5px;
            border-style: solid;
            border-width: 1px;
            overflow: hidden;
            word-break: normal;
            border-color: black;
        }

        .tg1 .tg1-0lax {
            text-align: left;
            vertical-align: top
        }
    </style>
@endpush
