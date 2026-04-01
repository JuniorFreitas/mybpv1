@extends('layouts.mail.layout')

@section('titulo', $dados['subject'])

@section('conteudo')
    <table border="0" cellpadding="0" width="100%" style="width: 100%;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Prezado(a) <strong>{{ $dados['nome'] }}</strong>,<br><br>

                {{ $dados['mensagem'] }}<br><br>

                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100%; background:#f7fafc; border:1px solid #d9e2ec; border-radius:12px;">
                    <tr>
                        <td style="padding: 18px 20px;">
                            <div style="font-size:12px; color:#6b7a88; text-transform:uppercase; letter-spacing:0.04em; font-weight:700; margin-bottom:10px;">
                                Informações da avaliação
                            </div>

                            <div style="margin-bottom:8px;"><strong>Avaliação:</strong> {{ $dados['avaliacao'] }}</div>
                            <div style="margin-bottom:8px;"><strong>Colaborador:</strong> {{ $dados['funcionario'] }}</div>
                            <div style="margin-bottom:8px;"><strong>Etapa:</strong> {{ $dados['etapa'] }}</div>

                            @if(!empty($dados['prazo_final']))
                                <div><strong>Prazo final:</strong> {{ $dados['prazo_final'] }}</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <br>
                {{ $dados['complemento'] }}

                <br><br>
                Atenciosamente,<br>
                <strong>Equipe MyBP</strong>
            </td>
        </tr>
    </table>
@endsection
