@extends('layouts.pdf')
@section('title','CONTRATO DE TRABALHO A TÍTULO DE EXPERIÊNCIA')
@section('empresa')
    @include('layouts.cabecalioEmpresaModelo')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>CONTRATO DE TRABALHO A TÍTULO DE EXPERIÊNCIA</b><br>
    </p>
    <p class="f11">
        Pelo presente Instrumento Particular de Contrato de Trabalho a Título de Experiência, que de um lado celebra a
        empresa <b>{{$dados->User->DadosEmpresa->razao_social}}</b>, inscrita no CNPJ/MF sob n.º
        <b>{{$dados->User->DadosEmpresa->cnpj}}</b>, com atividade
        localizada {{$dados->User->DadosEmpresa->endereco_completo}}, doravante designada simplesmente EMPREGADORA e de
        outro {{$dados->nome}} portador(a)
        Carteira Profissional
        n.º {{isset($dados->FeedBack->Admissao->DadosAdmissoes)?$dados->FeedBack->Admissao->DadosAdmissoes->ctps_numero : '__________________________'}}
        Série {{isset($dados->FeedBack->Admissao->DadosAdmissoes)?$dados->FeedBack->Admissao->DadosAdmissoes->ctps_serie : '___________________________'}}
        a seguir chamado apenas EMPREGADO, é celebrado
        o presente
        CONTRATO DE EXPERIÊNCIA, que terá vigência a partir da data de início da prestação de serviços, de acordo com as
        condições a seguir especificadas:

    </p><br>
    <p class="f11">
        <b>1ª (Função/Salário)</b> – Fica o EMPREGADO admitido no quadro de funcionários da EMPREGADORA para exercer as
        funções
        de {{$dados->FeedBack->Admissao->cargo}} mediante a remuneração mensal de R$
        ___________________________<br>_____________________________. A circunstância, porém, de ser a
        função especificada não importa na intransferibilidade do EMPREGADO para outro serviço, no qual demonstre melhor
        capacidade de adaptação desde que compatível com sua condição pessoal.
    </p><br>
    <p class="f11">
        <b>2ª (Horário)</b> – O horário de trabalho será no horário administrativo ou, especialmente, em horário de
        TURNO,
        cumprindo jornada de __________________________, se comprometendo a trabalhar em regime de compensação e de
        prorrogação de
        horas, quando necessário, e a eventual redução da jornada, por determinação da EMPREGADORA, não inovará este
        ajuste, permanecendo sempre íntegra a obrigação do EMPREGADO de cumprir o horário que lhe for determinado,
        observando o limite legal.
    </p><br>
    <p class="f11">
        <b>3ª (Horas Extras)</b> – Obriga-se também o EMPREGADO a prestar serviços em horas extraordinárias, sempre que
        lhe for
        determinado pela EMPREGADORA, na forma prevista em Lei. Na hipótese desta faculdade pela EMPREGADORA, o
        EMPREGADO receberá as horas extraordinárias com o acréscimo legal, ou será tratado através de acordo de
        compensação de horas, com a consequente redução da jornada de trabalho em outro dia.
    </p><br>
    <p class="f11">
        <b>4ª (Localidade)</b> – Fica ajustado nos termos que dispõe o Parágrafo 10° do Artigo 469, da Consolidação das
        Leis de
        Trabalho, que o EMPREGADO acatará ordem emanada da EMPREGADORA para prestação de serviços tanto da localidade de
        celebração do Contrato de Trabalho, como qualquer outra cidade, quer essa transferência seja transitória ou
        definitiva.
    </p><br>
    <p class="f11">
        <b>5ª (Dano)</b> – Em caso de dano causado pelo EMPREGADO, fica a EMPREGADORA, autorizada a efetivar o desconto
        da
        importância correspondente ao prejuízo, o qual fará, com fundamento no Parágrafo único do artigo 462 da
        Consolidação das Leis de Trabalho, já que essa possibilidade fica expressamente prevista em contrato.
    </p><br>
    <p class="f11">
        <b>6ª (Duração)</b> – O presente contrato vigorará por ________________________________________________<br>
        _________________________________________________________________________
        sendo celebrado para as partes verificarem reciprocamente, a conveniência ou
        não de se vincularem em caráter definitivo a um Contrato de Trabalho. A empresa passando a conhecer as aptidões
        do EMPREGADO e suas qualidades pessoais e morais; o EMPREGADO verificando se o ambiente e os métodos de trabalho
        atendem à sua conveniência.
    </p><br>
    <p class="f11">
        <b>7ª (EPIs)</b> – A EMPREGADORA concederá ao colaborador, uniformes e EPIs padronizados, sendo de uso diário
        obrigatório, objetivando manter imagem e identidade da empresa como também evitar eventuais acidentes. Os mesmos
        deverão ser devolvidos no ato do desligamento ou quando ocorrer substituição por qualquer defeito decorrente do
        uso normal.
    </p><br>
    <p class="f11">
        <b>8ª</b> As intervenções decorrentes das atribuições do Empregado, originadas de pesquisa pura e aplicada, bem
        como
        aquelas oriundas de estudos com a utilização das instalações e equipamentos do local de trabalho, são de
        propriedade exclusiva da EMPREGADORA.
    </p><br>
    <p class="f11">
        <b>9ª (Férias)</b> – Com fundamento no Parágrafo 1° do artigo 134 CLT, concorda o EMPREGADO que EMPREGADORA
        fracione as
        férias anuais em 03 períodos concessivos, na medida em que um dos períodos de férias não poderá ser inferior a
        quatorze dias corridos e os demais não poderão ser inferior a cinco dias corridos.
    </p><br>
    <p class="f11">
        <b>10ª</b> Fica ainda autorizado, de livre e espontânea vontade, para os mesmos fins, a cessão de direitos da
        veiculação das imagens em todo e qualquer material entre imagens de vídeo, fotos e documentos, para ser
        utilizada pela empresa CONTRATANTE destinado a divulgação ao público em geral não recebendo para tanto qualquer
        tipo de remuneração.
    </p><br>
    <p class="f11">
        <b>11ª (Rescisão)</b> – Opera-se a rescisão do presente contrato pela decorrência do prazo supra ou por vontade
        de uma
        das partes; rescindindo-se por vontade do EMPREGADO ou pela EMPREGADORA com justa causa, nenhuma indenização é
        devida; rescindindo-se antes do prazo, pela EMPREGADORA, fica esta obrigada a pagar 50% dos salários devidos até
        o final (metade do tempo combinado restante), nos termos do Artigo 479 da CLT, sem prejuízo do disposto no
        Regimento do FGTS. Nenhum aviso prévio é devido pela rescisão do devido contrato.
    </p><br>
    <p class="f11">
        <b>12ª (Vigência)</b> – Na hipótese deste ajuste transformar-se em contrato de prazo indeterminado, pelo decurso
        do
        tempo, continuarão em plena vigência às cláusulas de 01 (Um) a 06 (Seis), enquanto durarem as relações do
        EMPREGADO com a EMPREGADORA.
    </p><br>
    <p class="f11">
        E por estarem de pleno acordo, as partes contratantes, assinam o presente contrato de experiência em duas vias,
        ficando a primeira em poder da EMPREGADORA, e a segunda com o EMPREGADO, que dela dará o competente recibo.
    </p><br>

    <div class="f11" style="line-height: 26pt">
        {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        <br>
        São Luís, MA.
        <br>
        <br>
    </div>
    <div class="f11" style="line-height: 26pt;text-align: center">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        {{$dados->User->DadosEmpresa->razao_social}}
        <br>
        <br>
        <br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
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
