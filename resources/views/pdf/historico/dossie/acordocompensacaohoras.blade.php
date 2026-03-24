@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <style>
        @page {
            margin: 10mm 2mm 8mm 2mm;
        }
    </style>
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="position: fixed; left: 20px; bottom: 0; text-align: left; width: 90%; padding-bottom: 2px;">
        @include('layouts.rodapePdfFilialJob')
    </div>
    <div style="margin-left: 2.5%; width: 93%; padding-bottom: 28px;">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <b>ACORDO DE COMPENSAÇÃO DE HORAS DE TRABALHO</b>
        </p>
        <p class="f11 text-justify">
            EMPRESA: <br><br>
            @include('layouts.dadosEmpresa')
        </p>
        <p class="f11 text-justify" style="">
            EMPREGADO: {{$dados['dados_colaborador']->Curriculo->nome}}
            , {{$dados['dados_colaborador']->Admissao->cargo}}
            , portador da carteira profissional n.º
            {{isset($dados['dados_colaborador']->Admissao->DadosAdmissoes)?$dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero : '__________________________'}}
            Série {{isset($dados['dados_colaborador']->Admissao->DadosAdmissoes)?$dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_serie : '___________________________'}}
            inscrita no CPF sob nº {{$dados['dados_colaborador']->Curriculo->cpf ?? '__________________________'}},
            com admissão em {{$dados['dados_colaborador']->Admissao->data_admissao}}.
            <br>
            <br>
            As partes acima identificadas celebram o presente ACORDO DE COMPENSAÇÃO DE HORAS, que se regerá pelas
            cláusulas
            abaixo:
            <br>
            <br>
            1. A partir do dia ___/___/______ o empregado cumprirá a seguinte jornada normal de trabalho:
            <br>
            (a) de __________________________________________________, com uma hora de intervalo para refeição e
            descanso.
            <br>
            Total de horas trabalhadas na semana: _________________________ horas
            <br>
            <br>
            2. Fica convencionado conforme faculta a Lei, que haverá sistema de compensação de horas extras, pelo qual
            as
            horas extras efetivamente realizadas pelos empregados durante o mês, poderão ser compensadas, no prazo de
            até
            180 (cento e oitenta) dias após o mês da prestação da hora, com reduções de jornadas ou folgas
            compensatórias.
            Na hipótese das horas não serem compensadas, as mesmas serão pagas como horas extras, ou seja, o valor
            nominal
            da hora normal acrescido do adicional de 50%, conforme previsão da Convenção Coletiva de Trabalho.
            <br>
            <br>
            3. Na hipótese de rescisão do contrato de trabalho na vigência deste acordo de compensação de horas, sem que
            tenha havido a compensação integral das horas de trabalho, será feito o acerto de contas nas verbas
            rescisórias,
            ficando certo que, havendo crédito a favor do empregado, este fará jus ao pagamento das horas devidas com o
            adicional de 50% (cinquenta por cento)
            <br>
            <br>
            O presente Acordo de Compensação de Horas de Trabalho tem prazo indeterminado e é acessório ao Contrato de
            Trabalho celebrado entre as partes.

        </p>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        </div>
        <div class="f11" style="line-height: 18pt;text-align: center; padding-top: 30px">
            <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
            {{$dados['dados_empresa']['razao_social']}}
            <br>
            <br>
            <br>
            <hr style="width: 10cm;  margin-left: 24%;  border:none; border-top: 1px solid #333">
            {{$dados['dados_colaborador']->Curriculo->nome}}
        </div>
        <br>
        <p class="f11" style="">
            TESTEMUNHAS:
        </p>
    </div>
@stop

@push('style')
    <style type="text/css">

        .page-break {
            display: block;
            page-break-before: always;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
