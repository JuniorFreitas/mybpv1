@extends('layouts.mail.layout')
@section('titulo', 'Bem vindo(a) ao MyBP')
@section('conteudo')
    <table border="0" cellpadding="0" width="100%" style="width: 100%;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Encaminhamos o(a) <strong>{{$dados['colaborador']->nome}}</strong>,
                inscrito(a) no CPF sob o nº <strong>{{$dados['colaborador']->cpf}}</strong> e portador(a) do
                RG sob o nº <strong>{{$dados['colaborador']->rg}}</strong>, orgão expedidor <strong>{{$dados['colaborador']->orgao_expeditor}}</strong>,
                para realização do exame médico admissional PCMSO {{$dados['tipo_pcmso']}} no primeiro dia útil
                após recebimento receber esse e-mail (considerar de segunda à sábado).
                <br><br>
                Local do exame:<strong>{{$dados['clinica']->nome}}</strong><br>
                Endereço: {{$dados['clinica']->dados['endereco']['endereco_completo']}}<br>
                Contato: <strong>{{$dados['clinica']->dados['telefone']}}</strong>
                <br><br>
                Atenciosamente,
                Equipe RH
                <br><br>

            </td>
        </tr>
    </table>
@endsection
