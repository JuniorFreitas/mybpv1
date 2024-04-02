<!doctype html>
<html lang="pt-Br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <style type="text/css">

        * {
            margin: 0;
            padding: 0;
            font-family: 'Arial', Verdana, sans-serif;
        }

        @page {
            margin: 0cm 0cm;
            height: 22cm;
        }

        /** Define now the real margins of every page in the PDF **/
        body {
            margin-top: 3cm;
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
        }

        .conteudo {
            margin-top: 0.2cm;
        }

        .h5 {
            font-size: 9.5pt;
            font-weight: bold;
        }

        fieldset {
            height: 10px;
            margin-top: 15px;
            margin-bottom: 0px;
            border: none;
            border-top: 1px solid #333;
        }

        legend {
            background: #d6d6d6;
            margin-left: -0.29cm;
            text-transform: uppercase;
            padding-left: 3px;
            margin-top: -2px;
        }

        .titulo {
            margin-top: 5px;
            margin-bottom: 5px;
            text-decoration: underline
        }

        .h5 span {
            font-weight: normal;
            line-height: 20px
        }

        h5 span {
            font-weight: normal;
            line-height: 20px
        }

        .bg-default {
            background: #0f4c60;
            color: #FFFFFF;
            text-align: center;
        }

        .page_break {
            page-break-before: always;
        }

        .text-center {
            text-align: center;
        }

        p {
            font-size: 9pt;
        }

        .footer {
            font-size: 50px;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            /*z-index: 1;*/
        }

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
</head>
<body style="margin: 1cm">
<div class="conteudo">

    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>CONTRATO DE TRABALHO TEMPORÁRIO</b><br>
    </p>
    <div class="f11">
        <hr>
        <strong>QUADRO I - CONTRATANTE</strong><br>
        <hr>
        Nome: {{ $temporaria->razao_social }}<br>
        Endereço: {{ $temporaria->dados['endereco']['endereco_completo'] }}<br>
        CNPJ: {{ $temporaria->dados['cnpj'] }}<br>
        Neste ato designado simplesmente como <strong>CONTRATANTE</strong>
        <hr>
    </div>
    <div class="f11">
        <strong>QUADRO II - TRABALHADOR TEMPORÁRIO</strong><br>
        <hr>
        Nome: {{$dados['dados_colaborador']->nome}}<br>
        Endereço: {{ $dados['dados_colaborador']->endereco_completo }}<br>
        CTPS: {{ $dados['dados_colaborador']->Admissao->DadosAdmissoes ? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero: 'Não Informado' }}
        ,
        Serie: {{ $dados['dados_colaborador']->Admissao->DadosAdmissoes ? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_serie: 'Não Informado' }}
        <br>
        Função: {{  $dados['dados_colaborador']->Admissao->funcao }}<br>
        Salario: R$ {{ $dados['dados_colaborador']->Admissao->salario }}<br>
        Neste ato designado simplesmente como <strong>CONTRATANTE</strong>
        <hr>
    </div>
    <div class="f11" style=" padding-top: 0.2cm; text-align: justify; word-break: break-all">
        Entre as partes qualificadas nos QUADROS I e II, fica estabelecido o presente <strong>CONTRATO DE
            TRABALHO TEMPORÁRIO</strong>, que se rege pelas seguintes cláusulas:
        <br>
        <div style="padding-top: 0.1cm">
            1. <strong>O TEMPORÁRIO</strong> compromete-se a trabalhar em estabelecimento da empresa cliente da
            <strong>CONTRATANTE, CONSÓRCIO DE ALUMÍNIO DO MARANHÃO</strong> sob
            o regime da Lei nº 6.019/74 regulamentada pelo Decreto nº 73.841/74.
        </div>

        <div style="padding-top: 0.1cm">
            2. O presente contrato poderá vigorar até 90 (noventa) dias, podendo ser prorrogado por igual
            período conforme determinação do Art. 10 da Lei nº 6.019/74, mediante autorização concedida pelo órgão do
            Ministério do Trabalho.
        </div>

        <div style="padding-top: 0.1cm">
            3. <strong>O TEMPORÁRIO</strong> obedecerá ao horário de trabalho determinado pelo cliente da
            <strong>CONTRATANTE</strong> para seus empregados regulares e permanentes ou a outro horário
            diferente, conforme lhe seja informado previamente.
        </div>

        <div style="padding-top: 0.1cm">
            4. Apresente contratação tem a finalidade de atender as necessidades transitórias:<br>
            (X) Acréscimo extraordinário de serviços;<br>
            ( ) Substituição de pessoal regular e permanente
        </div>
    </div>
    <div class="f11" style=" padding-top: 0.2cm; text-align: justify; word-break: break-all">
        <div style="padding-top: 0.1cm">
            <strong>PARÁGRAFO ÚNICO.</strong> Na hipótese de o <strong>TEMPORÁRIO</strong> trabalhar além de 08 ( oito )
            horas
            diárias, exceto no caso de compensação de horário ou de 44 ( quarenta e quatro ) horas
            semanais, mediante acordo de prorrogação de horas de trabalho e de compensação de horário,
            que integra este contrato, fará jus ao adicional de horas extras não inferior ao previsto no Artigo
            7º XVI da Constituição Federal.
        </div>

        <div style=" padding-top: 0.1cm">
            4. <strong>O TEMPORÁRIO</strong> obriga - se no desempenho de suas atividades, acatar integralmente
            todas as ordens, instruções e normas consagradas no Regulamento Interno da empresa
            cliente.
        </div>

        <div style=" padding-top: 0.1cm">
            5. De acordo com o Art. 12 da Lei nº 6.019/74 e legislação complementar ficam asseguradas
            ao <strong>TEMPORÁRIO</strong> os direitos;
            <div style="padding-top: 0.1cm">
                * Remuneração igual à percebida pelos empregados da mesma categoria da empresa
                cliente, calculados à base horária;<br>
                * Acréscimo de no mínimo 50% por hora extra efetivamente trabalhada,obedecendo-se
                o disposto na legislação;<br>
                * Repouso semanal remunerado, nos termos da Lei nº 605/49;<br>
                * Adicional por trabalho noturno na hipótese de sua ocorrência;<br>
                * FGTS, na forma prevista na Lei 8.036/90 e Decreto 99.684/90;<br>
                * Seguro contra acidente do trabalho;<br>
                * Férias proporcionais conforme Artigo 12 Letra "C" da Lei 6.019/74 e acréscimo de 1/3;<br>
                * 13º salário proporcional na forma prevista em Lei;<br>
                * Adicionais de Insalubridade, Peliculosidade nos casos previstos em Lei;<br>
                * Proteção previdenciária conforme reg. dos Benefícios e do custeio da previdência
                social, Lei 8.212/91 e 8.213/91;<br>
                * Anotação na CTPS nos termos da CIRCULAR IAPAS nº 601.005.0 de 11/03/1980.
            </div>
        </div>
    </div>

    <div class="f11" style=" text-align: justify; word-break: break-all">
        <div style="padding-top: 0.1cm">
            <strong>PARÁGRAFO 1º:</strong>
            Para efeitos da Previdência Social, o <strong>TEMPORÁRIO</strong> sofrerá desconto pela
            <strong>CONTRATANTE</strong> da contribuição obrigatoriamente incidente sobre sua remuneração, servindo o
            recibo de pagamento como comprovante perante o INSS.
        </div>
        <div style="padding-top: 0.1cm">
            <strong>PARÁGRAFO 2º:</strong> A remuneração do <strong>TEMPORÁRIO</strong> sofrerá desconto do IRRF quando
            aplicável.
            <br>
            6. Em caso de dano causado pelo <strong>TEMPORÁRIO</strong> em objeto de propriedade ou posse da
            empresa cliente ou da <strong>CONTRATANTE</strong>, fica autorizado o desconto na sua remuneração,
            quer o dano tenha sido provocado por tulo ou culpa em sentido escrito; <br>
            7. A vigência deste contrato se inicia na data de sua assinatura e termina quando cessado,
            quando o motivo justificador da demanda ( art. 2º da Lei nº 6.019/74 ) não podendo
            ultrapassar o prazo no item 02 a menos que haja autorização do Ministério dao Trabalho.
        </div>
        <div style="padding-top: 0.1cm">
            <strong>PARÁGRAFO ÚNICO:</strong> Considera-se-a rescindido de pleno direito, por justa causa, o presente
            contrato, na eventualidade da prática pelo <strong>TEMPORÁRIO</strong> das faltas capituladas no art. 482 da
            CLT, assim como na eventualidade da prática pela empresa Cliente ou pela <strong>CONTRATANTE</strong> de
            quaisquer das faltas capituladas no art. 483 da CLT, conforme disposto no art,. 13 da Lei nº 6.019/74.
            <br>
            8. O contrato, desde que suas atividades tenham compatibilidade horária e não prejudiquem ou
            <strong>TEMPORÁRIO</strong> não fica obrigado a dedicar - se exclusivamente ao trabalho objeto deste
            possam a vir prejudicar a execução do trabalho ora contratado e se façam sempre com
            prévia comunicação a <strong>CONTRATANTE</strong>.
            <br><br>
            E por estarem justas e contratadas as partes assinam o presente Contrato em 02 (duas) vias de
            igual teor e forma, na presença das testemunhas abaixo.
        </div>

    </div>
</div>
<br>
<div class="f11" style="line-height: 26pt; text-align: right">
    São Luís,
    MA. {{ (new \MasterTag\DataHora($dados['dados_colaborador']->Admissao->data_admissao))->dataCompletaExt() }}.
    <br>
    <br>
</div>
<div class="f11" style="line-height: 15pt;text-align: center">
    <div style="width: 48%; float: left">
        <hr style="  border:none; border-top: 1px solid #333">
        TESTEMUNHA
    </div>
    <div style="width: 48%; float: right">
        <hr style="  border:none; border-top: 1px solid #333">
        EMPREGADORA
    </div>
</div>
<div style="clear: both"></div>
<br>
<br>
<div class="f11" style="line-height: 15pt;text-align: center">
    <div style="width: 48%; float: left">
        <hr style="  border:none;margin: 0px; border-top: 1px solid #333">
        TESTEMUNHA
    </div>
    <div style="width: 48%; float: right">
        <hr style="  border:none;margin: 0px; border-top: 1px solid #333">
        TRABALHADOR TEMPORÁRIO
    </div>
</div>
<div style="clear: both"></div>
<br><br>
<hr style="  border:none;margin: 0px; border-top: 1px solid #333">
<br>
<div class="f11" style=" text-align: justify; word-break: break-all">
    <br>
    <p style="text-align: center; font-size: 15px; font-weight: bold">TERMO DE PRORROGAÇÃO</p>
    <br>
    <br>
    <div style="padding-top: 0.1cm">
        Por mútuo acordo entre as partes. fica o Presente Contrato Temporário, que deveria vencer nesta data, prorrogado
        até a data ___/___/_____.
    </div>
    <br><br>
    <div style="padding-top: 0.1cm; text-align: right">
        ________________________________, ____ de _____________________ de ______
        <br>
    </div>
</div>
<br>
<br>
<br>
<div class="f11" style="line-height: 15pt;text-align: center">
    <div style="width: 48%; float: left">
        <hr style="  border:none; border-top: 1px solid #333">
        TESTEMUNHA
    </div>
    <div style="width: 48%; float: right">
        <hr style="  border:none; border-top: 1px solid #333">
        EMPREGADORA
    </div>
</div>
<div style="clear: both"></div>
<br>
<br>
<div class="f11" style="line-height: 15pt;text-align: center">
    <div style="width: 48%; float: left">
        <hr style="  border:none;margin: 0px; border-top: 1px solid #333">
        TESTEMUNHA
    </div>
    <div style="width: 48%; float: right">
        <hr style="  border:none;margin: 0px; border-top: 1px solid #333">
        TRABALHADOR TEMPORÁRIO
    </div>
</div>
<div style="clear: both"></div>
@include('layouts.rodapePdf',['semassinatura' => false])

</body>
</html>

