@extends('layouts.mail.layout')
@section('titulo', $dados['assunto'])
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

                Olá, <strong>{{ $dados['colaborador'] }}</strong>.<br><br>
                Estamos encaminhando para realizar o exame de ordem {{ $dados['tipoExame'] }}.<br>
                Data de Encaminhamento: <strong>{{ $dados['encaminhamento_data'] }}</strong> <br>
                Data de Realização: <strong>{{ $dados['data_realizacao'] }}</strong> <br><br>
                Local do Exame: <strong>{{ $dados['clinica']['nome'] }}</strong> <br>
                Endereço: <strong>{{ $dados['clinica']['dados']['endereco']['endereco_completo'] }}</strong><br>
                Contato: <strong>{{ $dados['clinica']['dados']['telefone'] }}</strong>
                <br><br>
                <div class="botao">
                    <a href='{{$dados['link']}}'>CLIQUE AQUI PARA ACESSAR A FICHA</a> <br> Se não abrir
                    copie e cole o endereço abaixo em seu navegador.<br><br>
                    {{$dados['link']}}
                <br><br>
                </div>



                <br><br>
            </td>
        </tr>
    </table>
@endsection
