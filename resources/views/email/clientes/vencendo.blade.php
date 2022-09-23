<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SGIBPSE :: Serviços de Clientes Vencidos ou próximo ao vencimento</title>
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
            <img src="https://bpse.com.br/img/logo.png" alt=""> <br>
        </td>
    </tr>
</table>


<table border="0" cellpadding="0" width="787" style="margin-top: 15px">
    <tr style="margin-top: 20px;">
        <td valign="top">
            <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; padding: 20px; color: #555555">
                Olá, segue a lista de Serviços de Clientes vencidos ou próximo ao vencimento!
                <br>
                <br>

                @foreach($dados as $linha)
                    <div
                        style="border: 1px solid #666666; padding: 10px; margin-bottom: 10px;font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                        Cliente: <strong>{{$linha->razao_social ?$linha->razao_social : $linha->nome}}</strong> <br><br>
                        @foreach($linha->ServicosCliente as $serv)
                            Serviço: <strong>{{ $serv->Servico->titulo }}</strong> <br>
                            Data de Encerramento:
                            <strong
                                style="color: red">{{ \MasterTag\DataHora::dataFormatada($serv->data_encerramento) }}</strong>
                            <br>
                            {{--                        Dias Vencidos:--}}
                            {{--                        <strong>{{ \MasterTag\DataHora::diferencaDias((new \MasterTag\DataHora())->dataCompleta(),\MasterTag\DataHora::dataFormatada($serv->data_encerramento)) }}--}}
                            {{--                            dias--}}
                            {{--                        </strong> --}}
                            <br>
                        @endforeach
                    </div>
                @endforeach

                <br><br>
                <span style="font-size: 11px; color: #696969">E-mail automático. Por favor, não responda.</span>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
