<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Curriculo - {{ ucwords($recrutamento->nome)}}</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-family: 'Arial', Verdana, sans-serif;
        }

        @page {
            size: 21cm 29.7cm;
            margin: 0;
        }

        h3 {
            font-size: 11.5pt;
            /*line-height: 1cm;*/
            padding: 5pt 0 5pt 0;
            text-decoration: underline;
        }

        p {
            font-size: 10pt;
            line-height: 0.55cm;
        }

        hr {
            border: 0;
            border-top: 1px dashed #CCCCCC;
        }

        /*.pagina {*/
        /*    width: 190mm;*/
        /*    height: 197mm;*/
        /*    background: #999999;*/
        /*}*/
    </style>
</head>
<body style="margin: 1cm">
<div class="pagina">
    @include('layouts.cabecalioEmpresa')
    <h3>Dados Pessoais</h3>
    <p>Nome: <strong>{{ ucwords($recrutamento->nome) }}</strong><br>
        Nascimento: <strong>{{ $recrutamento->nascimento }}</strong> | CPF: <strong>{{ $recrutamento->cpf }}</strong> |
        CNH: <strong>{{ $recrutamento->cnh }}</strong> | PCD: <strong>{{ $recrutamento->pcd ? 'Sim' : 'Não' }}</strong>
        <br> Disponibilidade para viajar: <strong>{{ $recrutamento->viajar ? 'Sim' : 'Não' }}</strong><br>
    </p>

    <h3>Endereço</h3>
{{--    <p>Logradouro: <strong>{{ $recrutamento->logradouro }}, {{ $recrutamento->numero }}, {{ $recrutamento->bairro }}--}}
{{--            , {{ $recrutamento->cep }}, {{ $recrutamento->municipio }} / {{ $recrutamento->uf }} </strong><br>--}}
{{--        Complemento: <strong>{{ $recrutamento->complemento }}</strong></p>--}}
    <p>{{$recrutamento->endereco_completo}}</p>


    <h3>Contatos</h3>
    @foreach($recrutamento->Telefones as $telefone)
        <p><strong>{{ $telefone->numero }} ({{ $telefone->tipoText }})</strong><br>
            @endforeach
            E-mail: <strong>{{ $recrutamento->email }}</strong></p>
        <h3>Formação</h3>
        <p>Formação: <strong>{{ $recrutamento->Formacao->tipo }}</strong><br>
            Curso: <strong>{{ $recrutamento->formacao_curso }} ( {{ $recrutamento->formacao_status }} )</strong><br>
            Instituição: <strong>{{ $recrutamento->formacao_instituicao }}</strong></p>

        @if($recrutamento->Experiencias->count() > 0)
            <h3>Experiências</h3>
            @foreach($recrutamento->Experiencias as $item)
                <p>Empresa: <strong>{{ $item->empresa }}</strong><br>
                    Cargo: <strong>{{ $item->cargo }}</strong><br>
                    Nome Referência: <strong>{{ $item->referencia_nome }} - {{ $item->referencia_telefone }}</strong><br>
                    Principais Atividades: <strong>{{ $item->principais_atv }}</strong><br>
                    Período: <strong>{{ $item->data_inicio }}</strong> à <strong>{{ $item->data_fim }}</strong></p>
                <br>
                <hr>
            @endforeach
        @endif

        @if($recrutamento->Qualificacoes->count() > 0)
            <h3>Qualificações</h3>
            @foreach($recrutamento->Qualificacoes as $item)
                <p>Curso: <strong>{{ $item->nome }}</strong><br>
                    Instituição: <strong>{{ $item->instituicao }}</strong><br>
                    Conclusão: <strong>{{ $item->mes_conclusao }}/{{ $item->ano_conclusao }}</strong></p>
                <hr>
            @endforeach
        @endif

        <h3>Vaga Pretendida</h3>
        <p><strong>{{ $recrutamento->Vaga->nome }}</strong> |
            @if($recrutamento->municipio_id)
                {{ $recrutamento->Cidade->nome }} - {{ $recrutamento->Cidade->uf }}
            @else
                UF:<strong>{{ $recrutamento->uf_vaga }}</strong>
            @endif
        </p>
</div>
</body>
</html>
