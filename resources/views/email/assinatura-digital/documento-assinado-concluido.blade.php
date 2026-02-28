@extends('layouts.mail.layout')
@section('titulo', 'Documento assinado')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Prezado(a) <strong>{{ $nome }}</strong>,<br><br>

                O documento <strong>{{ $nomeDocumento }}</strong> foi assinado por todos os signatários e está concluído.<br><br>

                Segue em anexo uma cópia do documento assinado para seus arquivos.<br><br>

                @if($nomeEmpresa)
                    Atenciosamente,<br><br>
                    Equipe {{ $nomeEmpresa }}<br><br>
                @endif
            </td>
        </tr>
    </table>
@endsection
