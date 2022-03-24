@extends('layouts.pdf')
@section('title','ACORDO DE COMPENSAÇÃO DE HORAS DE TRABALHO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>ACORDO DE COMPENSAÇÃO DE HORAS DE TRABALHO</b><br>
    </p>
    <p class="f11">
        EMPRESA: <br><br>

        @include('layouts.dadosEmpresa')

    </p><br>
    <p class="f11" style="">
        EMPREGADO: {{$dados->nome}}, {{$dados->FeedBack->Admissao->cargo}}, portador da carteira profissional n.º
        {{isset($dados->FeedBack->Admissao->DadosAdmissoes)?$dados->FeedBack->Admissao->DadosAdmissoes->ctps_numero : '__________________________'}}
        Série {{isset($dados->FeedBack->Admissao->DadosAdmissoes)?$dados->FeedBack->Admissao->DadosAdmissoes->ctps_serie : '___________________________'}}
        inscrita no CPF sob nº {{$dados->cpf}},
        com admissão em {{$dados->FeedBack->Admissao->data_admissao}}.
        <br>
        <br>
        As partes acima identificadas celebram o presente ACORDO DE COMPENSAÇÃO DE HORAS, que se regerá pelas cláusulas
        abaixo:
        <br>
        <br>
        1. A partir do dia ___/___/______ o empregado cumprirá a seguinte jornada normal de trabalho:
        <br>
        (a) de __________________________________________________, com uma hora de intervalo para refeição e descanso.
        <br>
        Total de horas trabalhadas na semana: _________________________ horas
        <br>
        <br>
        2. Fica convencionado conforme faculta a Lei, que haverá sistema de compensação de horas extras, pelo qual as
        horas extras efetivamente realizadas pelos empregados durante o mês, poderão ser compensadas, no prazo de até
        180 (cento e oitenta) dias após o mês da prestação da hora, com reduções de jornadas ou folgas compensatórias.
        Na hipótese das horas não serem compensadas, as mesmas serão pagas como horas extras, ou seja, o valor nominal
        da hora normal acrescido do adicional de 50%, conforme previsão da Convenção Coletiva de Trabalho.
        <br>
        <br>
        3. Na hipótese de rescisão do contrato de trabalho na vigência deste acordo de compensação de horas, sem que
        tenha havido a compensação integral das horas de trabalho, será feito o acerto de contas nas verbas rescisórias,
        ficando certo que, havendo crédito a favor do empregado, este fará jus ao pagamento das horas devidas com o
        adicional de 50% (cinquenta por cento)
        <br>
        <br>
        O presente Acordo de Compensação de Horas de Trabalho tem prazo indeterminado e é acessório ao Contrato de
        Trabalho celebrado entre as partes.
        <br>
        <br>
    </p>
    <div class="f11" style="line-height: 26pt">
        {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        <br>
        São Luís, MA.
        <br>
        <br>
    </div>
    <div class="page-break"></div>
    <div class="f11" style="line-height: 18pt;text-align: center; padding-top: 50px">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$dados->User->DadosEmpresa->razao_social}}
        <br>
        <br>
        <br>
        <hr style="width: 10cm;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->nome}}
    </div>
    <br>
    <p class="f11" style="">
        Testemunhas:
    </p>
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
        .page-break {
            display: block;
            page-break-before: always;
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
