<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Proxima Etapa</title>
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

    </style>
</head>
<body style="margin: 0; padding: 0;">
<div style="margin: 0 auto; padding: 20px">
    <table border="0" cellpadding="0" width="787" style="margin-top: 15px">
        <tr style="background:#072433; padding-top: 15px; padding-bottom: 15px;">
            <td align="middle" style="padding: 21px">
                <img src="{{ $dados['logo'] }}" style="width: 100%" alt=""> <br>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" width="787" style="margin-top: 15px">
        <tr style="margin-top: 20px;">
            <td valign="top">
                <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
                        <tr>
                            <td style="text-align: justify">
                                Olá, <strong>{{ $dados['nome'] }}</strong> Parabéns!<br><br>
                                Fique atento ao seu e-mail para receber comunicação das próximas etapas do processo!
                                <br><br>

                                @if(isset($dados['local_entrevista']))
                                    <strong>Data da entrevista:</strong> {{ $dados['data_entrevista'] }}<br><br>
                                    <strong>Local da entrevista:</strong> {{ $dados['local_entrevista'] }}<br><br>
                                @endif

                                Sucesso e esperamos vê-lo em breve!<br><br>

                                <br><br>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
