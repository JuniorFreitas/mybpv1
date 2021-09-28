<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SGIBPSE :: Treinamentos Vencidos ou próximo ao vencimento</title>
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
            <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; padding: 20px; color: #555555">
                Olá, segue a lista de <strong>treinamentos vencidos</strong> ou <strong>próximo ao vencimento</strong>!
                <br>
                <br>


                @foreach($dados as $linha)
                    <div
                        style="border: 1px solid #666666; padding: 10px; margin-bottom: 10px;font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                        Funcionário: <strong>{{$linha->Curriculo->nome}}</strong> <br><br>

                        @foreach($linha->Vencimentos as $item)
                            Treinamento: <strong>{{ $item->label}}</strong> <br>
                            Data do vencimento:
                            <strong
                                style="color: red">{{ \MasterTag\DataHora::dataFormatada($item->pivot->data_vencimento) }}</strong>
                            <br>
                            @if(\MasterTag\DataHora::diferencaDias((new \MasterTag\DataHora())->dataCompleta(),$item->pivot->data_vencimento) >=0)
                                A vencer daqui a
                                <strong>{{ \MasterTag\DataHora::diferencaDias((new \MasterTag\DataHora())->dataCompleta(),$item->pivot->data_vencimento) }}
                                    dias
                                </strong>
                            @endif

                            @if(\MasterTag\DataHora::diferencaDias((new \MasterTag\DataHora())->dataCompleta(),$item->pivot->data_vencimento) < 0)
                                Dias vencidos:
                                <strong>{{ \MasterTag\DataHora::diferencaDias($item->pivot->data_vencimento,(new \MasterTag\DataHora())->dataCompleta()) }}
                                    dias
                                </strong>
                            @endif
                            <br>
                            <hr>
                        @endforeach
                    </div>
                @endforeach


                <br>
                <br>

                BPSE-Business Partners Serviços Empresariais

                <br><br>
                <strong>Sede BPSE:</strong> Luminy Plaza, Av. dos Holandeses, 17 - Olho D'agua, São Luís - MA, 65066-620<br>
                <strong>Telefone:</strong> (98) 3011-0203<br>
                <strong>Site:</strong> www.bpse.com.br<br>
                <br><br>

                Siga-nos nas Redes Sociais:<br><br>
                <a href="https://www.linkedin.com/company/bpse/">
                    <img src="http://site.bpse.com.br/img/likendin.png"
                         alt="Linkedin" style="height: 30px"></a>
                <a href="https://instagram.com/sejabpse"><img src="http://site.bpse.com.br/img/insta.png"
                                                              alt="Instagram" style="height: 30px"></a>
                {{--                    <a href="https://fb.com/bpse1"><img src="{{asset('img/fb.png')}}" alt="Facebook" style="height: 30px"></a>--}}

                <br><br>
                <span style="font-size: 11px; color: #696969">E-mail automático. Por favor, não responda.</span>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
