@extends('layouts.mail.layout')
@section('titulo', 'CIH')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>
                <strong>{{ $dados['nome_de'] }}</strong>, fez uma nova atualização no apontamento de CIH. <br>
                ID: <strong>{{$dados['cih_id']}}</strong>. <br>
                Tipo: <strong>{{$dados['tipo']}}</strong>. <br>
                Área: <strong>{{$dados['area']}}</strong>. <br>
                Status: <strong>{{$dados['status']}}</strong>. <br>
                <br><br>
                Para visualizar acesse o sistema <a href="{{ route('g.admissao.cih.cih.index') }}">clique aqui</a> .

            </td>
        </tr>
    </table>
@endsection
