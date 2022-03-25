@extends('layouts.mail.layout')
@section('titulo', 'E-mail de desclassificacao')
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>

            <td style="text-align: justify">
                Parabéns, <strong>{{ $dados['nome'] }}</strong>. Você foi aprovado(a) selecionado etapa!
                <br><br>
                Você está recebendo um convite para realizar as avaliações abaixo relacionadas ao seu processo seletivo
                para a vaga de {{$dados['vaga']}} através da empresa BPSE.
                <br>
                <br>
                Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo
                para preenchê-los.
                <br><br>

                <h4>Abaixo segue links</h4>
                <ul>
                    @foreach($dados['provas'] as $prova)
                        <li>
                            <a href="{{ route('provas.prova.simulado',[$prova->vaga_id, $prova->simulado_id, $prova->Simulado->slug]) }}"
                               class="link" target="_blank">{{$prova->Simulado->titulo}}</a>
                            <br>
                            <span style="font-size: 11px; color: #696969">Caso não consiga abrir copie e cole esse endereço no navegador: {{ route('provas.prova.simulado',[$prova->vaga_id, $prova->id, $prova->Simulado->slug]) }}</span>
                        </li>
                    @endforeach
                </ul>
                <br>
                Cuidado para não perder o prazo! <br><br>

                Esperamos te ver em breve! <br><br>

                {{--Olá, <strong>{{ $dados['nome'] }}</strong>! Tudo bem?<br><br>

                Gostaríamos de agradecer a sua participação em nosso processo seletivo.<br><br>

                É muito bom ver o interesse de quem quer crescer e se desenvolver com a gente!<br><br>

                Sua participação foi encerrada nessa fase, mas entendemos que nenhuma avaliação deve ser levada em conta
                de maneira isolada. Por esse motivo, vamos manter seu cadastro em nosso banco de dados para futuras
                oportunidades em nossa empesa.<br><br>

                Temos diversas <a href="https://site.bpse.com.br/vagas-abertas">vagas</a>, além desta. Para não perder nenhuma delas, acesse nossa <a href="https://site.bpse.com.br">Plataforma de
                Recrutamento & Seleção</a> e mantenha o seu cadastro atualizado.<br><br>

                Abraços!<br><br>

                Equipe de R&S - BPSE--}}
                <br><br>
            </td>
        </tr>
    </table>
@endsection

@push('css')
    <style type="text/css">
        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-top: 10px;
            margin-bottom: 20px;
        }

    </style>
@endpush
