@extends('layouts.mail.layout')
@section('titulo', 'Comprovante da Carta Oferta')
@section('conteudo')
    @php
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
    @endphp
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: left">
                Prezado(a) <strong>{{ $dados['nome'] }}</strong>,<br><br>
                Registramos que você <strong>{{ $dados['aceito'] ? 'ACEITOU' : 'RECUSOU' }}</strong> a Carta Oferta
                de {{ $empresa->razao_social ?? $empresa->nome_fantasia ?? 'Não informado' }}.
                O comprovante segue em anexo; guarde este e-mail.
                <br><br>
                <strong>Data e hora:</strong> {{ $dados['data_hora'] ?? 'Não informado' }} (horário de Brasília)<br>
                <strong>Código de verificação:</strong> {{ $dados['codigo_verificacao'] ?? 'Não informado' }}<br>
                <span style="font-family: monospace; font-size: 11px; color: #555555; word-break: break-all;">
                    Hash da evidência (SHA-256): {{ $dados['hash_evidencia'] ?? 'Não informado' }}
                </span>
                <br><br>
                Para conferir a autenticidade, acesse:<br>
                <a href="{{ $dados['url_verificacao'] }}">{{ $dados['url_verificacao'] }}</a>
                <br><br>
                Se você não reconhece este registro, procure o RH.
                <br><br>
                Atenciosamente,<br>
                Equipe {{ $empresa->razao_social ?? 'RH' }}
            </td>
        </tr>
    </table>
@endsection
