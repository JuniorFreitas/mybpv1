@extends('layouts.pdf')
@section('title','CONTRATO DE TRABALHO A TÍTULO DE EXPERIÊNCIA')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <strong>CONTRATO DE TRABALHO POR PRAZO DETERMINADO</strong><br>
    </p>
    <p class="f12 text-justify">
        Pelo presente Instrumento Particular, que de um lado celebra a empresa
        <strong>{{$dados->User->DadosEmpresa->razao_social}}</strong>, sociedade empresária limitada, inscrita
        no CNPJ/MF sob n.º <strong>{{$dados->User->DadosEmpresa->cnpj}}</strong>, com sede
        localizada: {{$dados->User->DadosEmpresa->endereco_completo}}, representada por quem assina abaixo, doravante
        designada simplesmente <strong>EMPREGADORA</strong> e de outro <strong>{{$dados->nome}}</strong>
        portador(a) Carteira Profissional
        n.º {{$dados->FeedBack->Admissao->DadosAdmissoes? $dados->FeedBack->Admissao->DadosAdmissoes->ctps_numero : 'NÃO INFORMADO'}}
        ,
        Série {{$dados->FeedBack->Admissao->DadosAdmissoes? $dados->FeedBack->Admissao->DadosAdmissoes->ctps_serie: 'NÃO INFORMADO'}}
        a seguir chamado apenas <strong>EMPREGADO(A)</strong>, celebram o presente CONTRATO DE TRABALHO POR PRAZO
        DETERMINADO, com duração de ____ Dias, no período de ____/____/____ a ____/____/____ conforme disposto na
        consolidação da Leis de Trabalho, que regerá pelas cláusulas abaixo e demais disposições legais vigentes:

    </p><br>
    <p class="f12 text-justify">
        <strong>1</strong> – O <strong>EMPREGADO(A)</strong> trabalhará para a <strong>EMPREGADORA</strong>, com duração
        de ____ Dias, no
        período de ____/____/_____ a ____/____/_____ na função de
        <span
            style="text-transform: uppercase; font-weight: bold">{{ $dados->FeedBack->VagaAberta->VagaSelecionada->nome }}</span>
        e mais as funções que vierem a ser objetos de ordens verbais, cartas
        ou avisos, segundo necessidades técnicas da <strong>EMPREGADORA</strong>, em qualquer dos estabelecimentos desta
        última, nos
        termos do Art. 469 da CLT reconhecendo não lhe resultar, direta ou indiretamente nenhum tipo de prejuízo.

    </p><br>
    <p class="f12 text-justify">
        <strong>2</strong> – O <strong>EMPREGADO(A)</strong>, como remuneração pelo trabalho prestado,
        receberá o salário
        normativo da categoria fixado em ACORDO, CONVENÇÃO, DISSÍDIO COLETIVO DE TRABALHO ou tarefa de produção, no
        valor de R$ {{ $dados->FeedBack->Admissao->salario }}
        ({{\App\Models\Sistema::valorPorExtenso($dados->FeedBack->Admissao->salario)}}).
    </p><br>
    <p class="f12 text-justify">
        <strong>3</strong> – JORNADA DE TRABALHO, ACORDO PARA PRORROGAÇÃO:<br>
        O <strong>EMPREGADO(A)</strong>, na vigência do presente contrato, cumprirá sua jornada semanal observando o
        regime de ___ dia(s)
        trabalhados por ___dia(s) de folga, concordando com o trabalho em turnos e com prorrogação da jornada de
        trabalho, inclusive nos dias destinados ao Descanso Remunerado e feriados, observadas as necessidades técnicas
        da <strong>EMPREGADORA</strong>, pelo trabalho em horas suplementares, o <strong>EMPREGADO(A)</strong> perceberá
        os adicionais previstos em ACORDO,
        CONVENÇÃO, Dissídio Coletivo ou na própria legislação vigente.

    </p><br>
    <p class="f12 text-justify">
        <strong>4 – DISPOSIÇÕES GERAIS</strong>
    </p> <br>
    <p class="f12 text-justify">
        <strong>4.1</strong> - O <strong>EMPREGADO(A)</strong> fica ciente das normas de Segurança que regem a sua
        atividade e se compromete
        a fazer uso obrigatório dos EPIS (EQUIPAMENTO DE PROTEÇÃO INDIVIDUAL), fornecidos gratuitamente pela <strong>EMPREGADORA</strong>
        sob pena de ser punido pelo cometimento de falta grave, nos termos da legislação vigente e demais disposições
        inerentes a Segurança e medicina Ocupacional.
    </p>
    <p class="f12 text-justify">
        <strong>4.2</strong> - O <strong>EMPREGADO(A)</strong> se obriga a cumprir as normas e regulamentos internos da
        <strong>EMPREGADORA</strong>
        reconhecendo que seu descumprimento, implica no cometimento de falta grave, capaz de gerar a dissolução
        contratual por motivo justo.<br><br>
        <strong>4.3</strong> - O <strong>EMPREGADO</strong> fica desde já pré-avisado que o término do contrato a seu
        termo, não dependerá de qualquer aviso ou notificação prévia.
    </p><br>
    <p class="f12 text-justify">
        <strong>5 – RESCISÃO ANTECIPADA</strong><br>
        O presente CONTRATO poderá ser rescindido antes de expirado o termo ajustado. Caso seja exercido por qualquer
        das partes, serão aplicados os princípios que regem os contratos a prazo determinado, conforme disposto no art.
        481 da CLT, obrigando-se portanto, a parte que exercer o direito, a pré-avisar a outra, nas condições do art.
        481 e Esta tudo Consolidado.
    </p><br>
    <p class="f12 text-justify">
        E, por estarem de pleno acordo com as condições supra ajustadas, firmam o presente instrumento em 02 via(s) de
        igual teor, ante as testemunhas abaixo.
    </p><br>

    <br><br>
    <div class="f12" style="line-height: 26pt">
        São Luís/MA, {{ (new \MasterTag\DataHora($dados->FeedBack->Admissao->data_admissao))->dataCompletaExt() }}.
        <br>
        <br>
        <br>
    </div>
    <div class="f12" style="line-height: 15pt;text-align: center">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$dados->User->DadosEmpresa->razao_social}}
        <br>
        <br>
        <br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->nome}}
    </div>

    <div class="f12" style="line-height: 15pt;text-align: left">
        <p class="f12" style="margin-bottom: 26pt; margin-top: 30pt">
            TESTEMUNHAS:
        </p>
        <div style="width: 48%; float: left">
            <hr style="  border:none; border-top: 1px solid #333">
        </div>
        <div style="width: 48%; float: right">
            <hr style="  border:none; border-top: 1px solid #333">
        </div>
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
