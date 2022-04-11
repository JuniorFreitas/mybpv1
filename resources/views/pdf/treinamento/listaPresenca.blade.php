@extends('layouts.pdf')
@section('title','Lista de Presença')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center" style="text-transform: uppercase;">LISTA DE PRESENÇA {{$dados->TreinamentoSgi->nome}}</h5>
    <h6 class="text-center">De {{$dados->data_inicio}} à {{$dados->data_fim}}</h6>

    <h5 style="text-transform: uppercase; text-decoration: underline">Local treinamento:</h5>
    <h5 style="text-transform: uppercase; margin-top: 6pt">{{$dados->EmpresaTreinamento->nome}}</h5>

    <h5 style="text-transform: uppercase; text-decoration: underline; margin-top: 6pt">Instrutor(es):</h5>
    @foreach($dados->InstrutoresEvento as $instrutor)
        <h5 style="text-transform: uppercase; margin-top: 6pt">{{$instrutor->nome}}</h5>
    @endforeach
    <br>
    <h5 style="text-transform: uppercase; text-decoration: underline;margin-bottom: 6pt">Presença:</h5>
    <table border="1" cellpadding="0" cellspacing="0" class="tabela" style="font-size: 0.85em; width: 100%">
        <thead>
        <tr class="bg-default">
            <th class="text-center">Ordem</th>
            <th class="text-center">Empresa</th>
            <th class="text-center">Nome</th>
            <th class="text-center">CPF</th>
            <th class="text-center">E-mail</th>
            <th class="text-center">Contato</th>
            <th class="text-center">Nota</th>
        </tr>
        </thead>
        <tbody>
        @php($conta = 1)
        @foreach($dados->PessoasEvento as $pessoa)
            <tr>
                <td class="text-center">{{$conta ++}}</td>
                <td class="text-center">{{$pessoa->Cliente->nome_fantasia}}</td>
                <td class="text-center">{{$pessoa->nome}}</td>
                <td class="text-center">{{$pessoa->cpf}}</td>
                <td class="text-center">{{$pessoa->email}}</td>
                <td class="text-center">{{$pessoa->telefone}}</td>
                <td class="text-center">{{$pessoa->nota}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('layouts.rodapePdf')
@endsection
