@extends('layouts.pdf')
@section('title','TESTE PRATICO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">TESTE PRATICO</h5>

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
        Empresa:
        <span>{{ $dados->FeedbackCurriculo->Empresa->nome }}</span>
        <br> Vaga:<span> {{ $dados->FeedbackCurriculo->VagaAberta->VagaSelecionada->nome }}</span> |
        UF Vaga: <span>{{ $dados->FeedbackCurriculo->VagaAberta->Municipio->uf }}</span> | Ex
        Funcionário:<span> {{ $dados->FeedbackCurriculo->ParecerRh->ex_funcionario }}</span>
    </div>

    <div class="h5">
        <fieldset>
            <legend>Parecer Teste Prático</legend>
        </fieldset>
        Fez teste prático: <span>{{ $dados->fez_teste }}</span>
        @if($dados->fez_teste)
            <br>
            Data e Hora da Realização:
            <span>{{ \MasterTag\DataHora::dataFormatada($dados->data_horario_realizacao) }}</span><br>
            Responsável pelo Teste: <span>{{ $dados->responsavel_pelo_teste }}</span><br>
            Qual o Teste foi aplicado: <span>{{ $dados->qual_teste }}</span><br>
            Qual o Resultado do teste: <span>{{ $dados->resultado_teste }}</span><br>
        @endif
        <br>
        <fieldset>
            <legend>Parecer Final do Teste Prático</legend>
        </fieldset>
        Parecer do Teste: <span>{{ $dados->parecer_final_teste }}</span> |
        Nota: <span>{{ $dados->nota_teste == 0 ? 'Não se Aplica' : $dados->nota_teste }}</span><br>
        Entrevistado por <span>{{ $dados->quem_entrevistou }}</span> <br>
        @php
            $dataEntrevista = new \MasterTag\DataHora($dados->created_at);
        @endphp
        Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>

    </div>

    @include('layouts.rodapePdf')
@endsection
