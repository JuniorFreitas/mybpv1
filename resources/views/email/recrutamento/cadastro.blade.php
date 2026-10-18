@extends('layouts.mail.layout')
@section('titulo', 'Candidatura recebida')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%; padding: 25px;">
        <tr>
            <td style="text-align: justify;">
                Olá, <strong>{{ $dados['nome'] }}</strong>!<br><br>

                Recebemos sua candidatura para a vaga
                <strong>{{ $dados['titulo_vaga'] }}@if(!empty($dados['localidade'])) — {{ $dados['localidade'] }}@endif</strong>.
                <br><br>

                Nossa equipe de recrutamento irá analisar o seu perfil.
                Caso você avance nas próximas etapas do processo seletivo,
                entraremos em contato pelo e-mail informado no cadastro.
                <br><br>

                Agradecemos o interesse em fazer parte do nosso time.
                <br><br>

                Atenciosamente,<br>
                <strong>{{ $dados['razao_social'] }}</strong>
            </td>
        </tr>
    </table>
@endsection
