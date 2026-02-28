@extends('layouts.mail.layout')
@section('titulo', 'Documento para assinatura digital')
@push('css')
    <style>
        .botao a {
            font-size: 15px;
            display: inline-block;
            background: #0F4C60;
            color: #fff !important;
            padding: 10px 0px 6px;
            margin-top: 4px;
            border-radius: 15px;
            text-align: center;
            width: 100%;
            height: 25px;
            transition: .3s;
        }
        .botao a:hover {
            background: #031E2D;
            color: #fff !important;
        }
    </style>
@endpush
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Prezado(a) <strong>{{ $nome }}</strong>,<br><br>

                Você possui um documento pendente de assinatura digital: <strong>{{ $nomeDocumento }}</strong>.<br><br>

                Para assinar digitalmente, acesse o link abaixo. O link é pessoal e intransferível.<br><br>

                <div class="botao">
                    <a href="{{ $linkAssinatura }}">ACESSAR E ASSINAR DOCUMENTO</a><br><br>
                </div>

                Ou copie e cole no navegador:<br>
                <small style="word-break: break-all;">{{ $linkAssinatura }}</small><br><br>

                A assinatura digital possui validade jurídica conforme a legislação brasileira (Lei 14.063/2020 e MP 2.200-2/2001). Ao assinar, você declara que leu e concorda com o conteúdo do documento.<br><br>

                @if($nomeEmpresa)
                    Atenciosamente,<br><br>
                    Equipe {{ $nomeEmpresa }}<br><br>
                @endif
            </td>
        </tr>
    </table>
@endsection
