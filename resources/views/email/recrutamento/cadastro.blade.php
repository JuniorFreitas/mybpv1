@extends("layouts.mail.layout")
@section("title","Inscrição Concluída")
@section("conteudo")

    <table border="0" cellpadding="0" width="787" style="margin-top: 15px">
        <tr style="margin-top: 20px;">
            <td valign="top">
                <p style="font-size: 13.5px; line-height: 20px; font-family: 'Arial'; color: #555555">
                    Olá, <strong>{{$nome}}</strong> Parabéns! Sua inscrição foi concluída com sucesso! <br><br>
                    Fique atento ao seu e-mail para receber comunicação das próximas etapas do processo! <br><br>
                    Sucesso e esperamos vê-lo(a) em breve! <br><br>
                    Abraços <br><br>

                    <br><br>
                    <span style="font-size: 11px; color: #696969">E-mail automático. Por favor, não responda.</span>

                </p>
            </td>
        </tr>
    </table>

@endsection
@push('css')
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
@endpush
