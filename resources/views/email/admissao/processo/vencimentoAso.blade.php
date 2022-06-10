@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">

                Olá, <strong>{{ $dados['usuario']->nome }}</strong>, segue a lista de colaboradores que o ASO está
                próximo ao vencimento!<br><br>

                <table border="1" cellpadding="2" cellspacing="0" width="100%" style="width: 100%;">
                    <thead>
                    <tr>
                        <th style="text-align: center">#</th>
                        <th style="text-align: center">Nome</th>
                        <th style="text-align: center">Data de Vencimento</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; ?>
                    @foreach($dados['vencimentos'] as $vencimento)
                        <tr>
                            <td style="text-align: center">{{ $i++ }}</td>
                            <td style="text-align: center">{{ $vencimento['colaborador'] }}</td>
                            <td style="text-align: center">{{ $vencimento['data_vencimento'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <br><br>

            </td>
        </tr>
    </table>
@endsection
