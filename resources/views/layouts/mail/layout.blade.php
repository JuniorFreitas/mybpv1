<!doctype html>
<html lang="pt-br">
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
<body style="margin: 20px;padding: 0;background: #f5f5f5;">
<div>
    @php
        $empresa = null;
        if (isset($dados['empresa_id']) && !empty($dados['empresa_id'])){
            $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
        } elseif (auth()->check() && auth()->user() && isset(auth()->user()->empresa_id)){
            $empresa = \App\Models\Cliente::withoutGlobalScopes()->find(auth()->user()->empresa_id);
        }
    @endphp
    <table border="0" align="center" cellpadding="0" width="615"
           style="mso-table-lspace:0;mso-table-rspace:0;background-color:#fff;color:#000;width:615px">
        <tr>
            <td align="middle">
                @if(isset($empresa) && !empty($empresa))
                    <img src="{{env('AWS_URL')}}/public/email_{{$empresa->apelido}}.jpg"
                         style="width: 100%" alt=""> <br>
                @else
                    <img src="{{env('AWS_URL')}}/public/email_bpse.jpg" style="width: 100%" alt=""> <br>
                @endif
            </td>
        </tr>
    </table>

    <table border="0" align="center" cellpadding="0" width="615"
           style="mso-table-lspace:0;mso-table-rspace:0;background-color:#fff;color:#000;width:615px">
        <tr style="margin-top: 20px;">
            <td valign="top">
                <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                    @yield('conteudo')
                </div>

                @if(isset($empresa) && !empty($empresa))
                    <div style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                        <div
                            style="background: #f3f3f3; padding: 13px;line-height: 37px;border-radius: 0px;text-align: center">
                            <div style="text-align: center">
                                <strong style="text-transform: uppercase; color: #072433">
                                    {{$empresa->razao_social}}
                                    <br>
                                </strong>
                            </div>
                            {{$empresa->endereco_completo}}<br>
                        </div>
                        <br>
                        <span
                            style="font-size: 11px;color: #696969;margin-left: 22px;">Enviado pelo sistema MyBP.</span><br>
                        <span style="font-size: 11px;color: #696969;margin-left: 22px;">E-mail automático. Por favor, não responda.</span>
                        <br><br>
                    </div>
                @endif
            </td>
        </tr>
    </table>
    <table border="0" align="center" cellpadding="0" width="615"
           style="mso-table-lspace:0;mso-table-rspace:0;background-color:#fff;color:#000;width:615px">
        <tr style="background-color: #072433; padding-top: 15px; padding-bottom: 15px">
            <td align="middle" style="padding: 7px">
                <img src="{{env('AWS_URL')}}/logo_mybp.png" alt="" style="height: 40px"> <br>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
