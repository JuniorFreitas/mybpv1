<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SGIBPSE :: REVISÃO PARA APROVAÇÃO</title>
    <style type="text/css">
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
<table border="0" cellpadding="0" width="787" style="margin-top: 15px">
    <tr style="background:#072433; padding-top: 15px; padding-bottom: 15px;">
        <td align="middle" style="padding: 21px">
            <img src="http://site.bpse.com.br/img/logo.png" alt=""> <br>
        </td>
    </tr>
</table>

<table border="0" cellpadding="0" width="787" style="margin-top: 15px">
    <tr style="margin-top: 20px;">
        <td valign="top">
            <p style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                Olá, <strong>{{$nome}}</strong>! <br>
                <strong>{{ $quem_enviou }}</strong> enviou o item <strong>{{$caminho}}</strong> no CLOUD para sua
                aprovação.
                <br><br>

                {{ $texto_livre }} <br><br>


                <br><br>
                <span style="font-size: 11px; color: #696969">E-mail automático. Por favor, não responda.</span>

            </p>
        </td>
    </tr>
</table>
</body>
</html>
