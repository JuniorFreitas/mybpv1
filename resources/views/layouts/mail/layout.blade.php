<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('titulo')</title>
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
    @stack('css')
</head>
<body style="margin: 0; padding: 0;">
<div style="margin: 0 auto; padding: 20px">
    @php
        if (isset($dados['empresa_id']) && !empty($dados['empresa_id'])){
            $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
    }else{
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find(auth()->user()->empresa_id);
    }
    @endphp
    <table border="0" cellpadding="0" width="787" style="margin-top: 15px">
        <tr>
            <td align="middle" style="padding: 21px">
                @if(isset($empresa) && !empty($empresa))
                    <img src="https://mybp-prod.s3.amazonaws.com/public/email_{{$empresa->apelido}}.jpg"
                         style="width: 100%" alt=""> <br>
                @else
                    <img src="https://site.bpse.com.br/img/logo.png" alt=""> <br>
                @endif
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" width="787" style="margin-top: 15px">
        <tr style="margin-top: 20px;">
            <td valign="top">
                <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                    @yield('conteudo')
                </div>

                @if(isset($empresa) && !empty($empresa))
                    <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                        <div
                            style="background: #f3f3f3; padding: 13px; line-height: 37px; border-radius: 9px; text-align: center">
                            <div style="text-align: center">
                                <strong style="text-transform: uppercase; color: #072433">
                                    {{$empresa->razao_social}}
                                    <br>
                                </strong>
                            </div>
                            <img src="https://sgi.bpse.com.br/imagens/icons/location.png"
                                 alt="Endereço" height="22px"> {{$empresa->endereco_completo}}<br>

                        </div>
                        <br><br>

                        <span style="font-size: 11px; color: #696969">Enviado pelo sistema MyBP.</span><br>
                        <span style="font-size: 11px; color: #696969">E-mail automático. Por favor, não responda.</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="0" width="787" style="margin-top: 15px">
        <tr style="background-color: #072433; padding-top: 15px; padding-bottom: 15px">
            <td align="middle" style="padding: 21px">
                <img src="https://mybp-prod.s3.amazonaws.com/logo_mybp.png" alt="" style="height: 75px"> <br>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
