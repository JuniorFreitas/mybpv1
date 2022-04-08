<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MyBP</title>
    <style type="text/css">
        .footer--btn-confirm {
            width: 200px;
            height: 50px;
            margin: auto;
            background: #184056;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            padding: 5px;
            font-size: 20px;
            line-height: 50px;
        }

        .footer--btn-confirm a:link {
            color: #fff !important;
        }

        /* visited link */
        .footer--btn-confirm a:visited {
            color: #fff !important;
        }

        /* mouse over link */
        .footer--btn-confirm a:hover {
            color: #ccc !important;
        }

        /* selected link */
        .footer--btn-confirm a:active {
            color: #fff !important;
        }

        a {
            color: #072433 !important;
            text-decoration: none;
        }

        a:hover {
            color: #04415f !important;
            text-decoration: underline;
        }

        .link {
            width: 200px;
            height: 50px;
            margin: auto;
            background: #072433;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            padding: 15px;
            font-size: 0.9rem;
            line-height: 50px;
        }

        .link a:link {
            color: #fff !important;
        }

        /* visited link */
        .link a:visited {
            color: #fff !important;
        }

        /* mouse over link */
        .link a:hover {
            color: #ccc !important;
        }

        /* selected link */
        .link a:active {
            color: #fff !important;
        }

        ii a[href] {
            color: #fff !important;
        }


        .footer--btn-confirm {
            width: 200px;
            height: 50px;
            margin: auto;
            background: #ff5c15;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            padding: 5px;
            font-size: 20px;
            line-height: 50px;
        }

        .footer--btn-confirm a:link {
            color: #fff !important;
        }

        /* visited link */
        .footer--btn-confirm a:visited {
            color: #fff !important;
        }

        /* mouse over link */
        .footer--btn-confirm a:hover {
            color: #ccc !important;
        }

        /* selected link */
        .footer--btn-confirm a:active {
            color: #fff !important;
        }

        ii a[href] {
            color: #fff !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
<div style="margin: 0 auto; padding: 20px">
    @php
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
        $vagaAberta = \App\Models\VagasAbertas::withoutGlobalScopes()->find($dados['vaga_aberta_id']);
    @endphp
    <table border="0" cellpadding="0" width="607" style="margin-top: 15px">
        <tr style="background-color: #cccbcb; padding-top: 15px; padding-bottom: 15px;">
            <td align="middle" style="padding: 21px">
                <img src="{{$empresa->Logo[0]->url}}" alt="" style="height: 100px"> <br>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" width="607" style="margin-top: 15px">
        <tr style="margin-top: 20px;">
            <td valign="top">
                <p style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555; text-align: center">

                    Olá, <strong>{{$dados['nome']}}</strong> Parabéns! Sua inscrição para vaga
                    <strong>{{$vagaAberta->titulo}} - {{$vagaAberta->Municipio->nome}}/{{$vagaAberta->Municipio->uf}}</strong> foi concluída com sucesso! <br><br>
                    Fique atento ao seu e-mail para receber comunicação das próximas etapas do processo! <br><br>
                    Sucesso e esperamos vê-lo(a) em breve! <br><br>
                    Abraços <br><br>

                    <br><br>
                </p>
                <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                    <div
                        style="background: #f3f3f3; padding: 13px; line-height: 37px; border-radius: 9px; text-align: center">
                        <div style="text-align: center">
                            <strong style="text-transform: uppercase; color: #072433">
                                {{$empresa->razao_social}}
                            </strong>
                        </div>
                        <img src="https://sgi.bpse.com.br/imagens/icons/location.png"
                             alt="Endereço" height="22px"> {{$empresa->endereco_completo}}<br>

                    </div>
                    <br><br>

                    <span style="font-size: 11px; color: #696969">Enviado pelo sistema MyBP.</span><br>
                    <span style="font-size: 11px; color: #696969">E-mail automático. Por favor, não responda.</span>
                </div>
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="0" width="607" style="margin-top: 15px">
        <tr style="background-color: #072433; padding-top: 15px; padding-bottom: 15px;">
            <td align="middle" style="padding: 21px">
                <img src="https://mybp-prod.s3.amazonaws.com/logo_mybp.png" alt="" style="height: 100px"> <br>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
