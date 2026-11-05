@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">

                Olá, <strong>{{ $usuario->nome }}</strong>.<br><br>
                Estes ASOs chegaram hoje ({{ $dados['data_br'] ?? '' }}) a um aviso de vencimento.
                Encaminhe os exames a tempo para manter a empresa em dia com a NR-7.<br><br>

                <table border="1" cellpadding="4" cellspacing="0" width="100%" style="width: 100%; border-collapse: collapse;">
                    <thead>
                    <tr>
                        <th style="text-align: left">Colaborador</th>
                        <th style="text-align: left">Cargo</th>
                        <th style="text-align: center">Vencimento</th>
                        <th style="text-align: center">Aviso</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dados['itens'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['colaborador'] }}</td>
                            <td>{{ $item['cargo'] ?: 'Não informado' }}</td>
                            <td style="text-align: center">{{ $item['vencimento_br'] }}</td>
                            <td style="text-align: center">{{ $item['marco'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <br>
                <a href="{{ $dados['link'] ?? '#' }}" style="font-weight: bold;">Abrir a Conformidade de ASO e encaminhar</a>
                <br><br>
                <small style="color: #666;">Você recebe este e-mail porque marcou "Vencimento ASO" nas suas preferências de notificação.</small>
            </td>
        </tr>
    </table>
@endsection
