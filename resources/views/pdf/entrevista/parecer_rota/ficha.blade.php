@extends('layouts.pdf')
@section('title','Parecer de Rota')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">PARECER ROTA TRANSPORTE</h5>

    <div class="h5">
        <fieldset>
            <legend>Dados Gerais</legend>
        </fieldset>
    </div>

    <div class="h5">
        Nome: <span>{{ $dados->FeedbackCurriculo->Curriculo->nome }}</span> <br>
        Data de Nascimento: <span>{{ $dados->FeedbackCurriculo->Curriculo->nascimento }}</span> - <span>{{ $dados->FeedbackCurriculo->Curriculo->idade }} anos</span>
        <br>
        PCD: <span>{{ $dados->FeedbackCurriculo->Curriculo->pcd ? 'Sim' : 'Não' }}</span>
        @if($dados->FeedbackCurriculo->Curriculo->pcd)
            | CID <span>{{ $dados->FeedbackCurriculo->Curriculo->cid }}</span>
        @endif
        <br>
        Disponibilidade para Viajar: <span>{{ $dados->FeedbackCurriculo->Curriculo->viajar ? 'Sim' : 'Não' }}</span>
        <br>
        Escolaridade:
        <span>{{ $dados->FeedbackCurriculo->Curriculo->Formacao->tipo }} {{$dados->FeedbackCurriculo->Curriculo->formacao_curso ? "($dados->FeedbackCurriculo->Curriculo->formacao_curso)" : null}} </span>
        <br>
        Endereço: <span>{{ $dados->FeedbackCurriculo->Curriculo->endereco_completo }}</span><br>
        Contato:
        <span>{{ $dados->FeedbackCurriculo->TelPrincipal ? $dados->FeedbackCurriculo->TelPrincipal->Formatado() : 'não informado' }}</span>
        |
        E-mail: <span>{{ $dados->FeedbackCurriculo->Curriculo->email }}</span>
        <br/>
        Cliente:
        <span>{{ $dados->FeedbackCurriculo->Empresa->nome }}</span>
        <br> Vaga:<span> {{ $dados->FeedbackCurriculo->VagaAberta->VagaSelecionada->nome }}</span> |
        UF Vaga: <span>{{ $dados->FeedbackCurriculo->VagaAberta->Municipio->uf }}</span> | Ex
        Funcionário:<span> {{ $dados->FeedbackCurriculo->parecerRh->ex_funcionario }}</span>
    </div>

    <div class="h5">
        <fieldset>
            <legend>Parecer Rota Transporte</legend>
        </fieldset>
        Tem Rota que atende: <span>{{ $dados->FeedbackCurriculo->parecerRota->tem_rota ? 'Sim' : 'Não' }}</span>
        @if($dados->FeedbackCurriculo->parecerRota->tem_rota)
            | Qual: <span>{{ $dados->FeedbackCurriculo->parecerRota->qual }}</span>
        @endif
        <br>
        Bairro / Rota: <span>{{ $dados->FeedbackCurriculo->parecerRota->bairro_rota }}</span> <br>
        Ponto de referência Rota: <span>{{ $dados->FeedbackCurriculo->parecerRota->ponto_referencia_rota }}</span> <br>
        Informado sobre ponto de referência:
        <span>{{ $dados->FeedbackCurriculo->parecerRota->pega_onibus ? 'Sim' : 'Não' }}</span>
        @if($dados->FeedbackCurriculo->parecerRota->pega_onibus)
            | Qual: <span>{{ $dados->FeedbackCurriculo->parecerRota->pega_onibus_qual_ponto }}</span>
        @endif
        <br>
        Bairro Residência: <span>{{ $dados->FeedbackCurriculo->parecerRota->bairro_residencia }}</span><br>
        Ponto de referência Residência:
        <span>{{ $dados->FeedbackCurriculo->parecerRota->ponto_referencia_residencia }}</span><br>
        Autorizado Vale Transporte:
        <span>{{ $dados->FeedbackCurriculo->parecerRota->vale_transporte ? 'Sim' : 'Não' }}</span><br>
        <fieldset>
            <legend>Rota Disponivel para qual turno</legend>
        </fieldset>
        Turno A: <span>{{ $dados->FeedbackCurriculo->parecerRota->rota_disponivel_turno_a ? 'Sim' : 'Não' }}</span> |
        Turno B: <span>{{ $dados->FeedbackCurriculo->parecerRota->rota_disponivel_turno_b ? 'Sim' : 'Não' }}</span> |
        Turno C: <span>{{ $dados->FeedbackCurriculo->parecerRota->rota_disponivel_turno_c ? 'Sim' : 'Não' }}</span>
        @if ($dados->FeedbackCurriculo->parecerRota->rota_disponivel_turno_o)
            | Outros: <span>{{ $dados->FeedbackCurriculo->parecerRota->rota_disponivel_turno_o ? 'Sim' : 'Não' }}</span>
            | Quais: <span>{{ $dados->FeedbackCurriculo->parecerRota->rota_disponivel_outros }}</span>
        @endif
        <br>
        Observação: <span>{{ $dados->FeedbackCurriculo->parecerRota->observacao }}</span>
        <br>
        <fieldset>
            <legend>Parecer Final Transporte</legend>
        </fieldset>
        Rota Atende: <span>{{ $dados->FeedbackCurriculo->parecerRota->rota_atende ? 'Sim' : 'Não' }}</span><br>
        Tipo de Contratação: {{ $dados->FeedbackCurriculo->parecerRota->rota_tipo }} <br>
        Entrevistado por {{ $dados->FeedbackCurriculo->parecerRota->quem_entrevistou }}<br>
        @php
            $dataEntrevista = new \MasterTag\DataHora($dados->FeedbackCurriculo->parecerRota->created_at);
        @endphp
        Data da Entrevista: <span>{{ $dados->data_entrevista }}h</span>
    </div>


    <div class="h5">

        <br>
        <br>
        Data da Emissão da ficha:
        <span>{{ (new \MasterTag\DataHora())->dataCompleta()}} às {{ (new \MasterTag\DataHora())->horaCompleta()}}</span><br>
        Usuário que emitiu a ficha: <span>{{ auth()->user()->nome }}</span>
    </div>
@endsection
