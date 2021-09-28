@extends('layouts.pdf')
@section('title','ENTREVISTA TÉCNICA')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">ENTREVISTA TÉCNICA</h5>

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
        <span>{{ $dados->FeedbackCurriculo->Cliente->tipo == \App\Models\Clientes::TIPO_PESSOA_JURIDICA ? $dados->FeedbackCurriculo->Cliente->razao_social : $dados->FeedbackCurriculo->Cliente->nome}}</span>
        <br> Vaga:<span> {{ $dados->FeedbackCurriculo->VagaSelecionada->nome }}</span> |
        UF Vaga: <span>{{ $dados->FeedbackCurriculo->Curriculo->uf_vaga }}</span> | Ex
        Funcionário:<span> {{ $dados->FeedbackCurriculo->ParecerRh->ex_funcionario }}</span>
    </div>

    <div class="h5">
        <fieldset>
            <legend>Parecer Entrevista Técnica</legend>
        </fieldset>
        Tempo na função: <span>{{ $dados->tempo_funcao }}</span><br>
        Já trabalhou na ALUMAR: <span>{{ $dados->trabalhou_alumar ? 'Sim' : 'Não' }}</span><br>
        Tem Rota: <span>{{ $dados->rota ? 'Sim' : 'Não' }}</span><br>
        Disponibilidade para turno: <span>{{ $dados->turno ? 'Sim' : 'Não' }}</span><br>
        Indicado: <span>{{ $dados->indicado ? 'Sim' : 'Não' }}</span>
        @if($dados->indicado)
            | Indicado por: <span>{{ $dados->indicado_por }}</span>
        @endif
        <br>
        Conhece e prática as normas de SSMA: <span>{{ $dados->ssma ? 'Sim' : 'Não' }}</span>
        @if($dados->ssma)
            | Expecifique: <span>{{ $dados->ssma_ex }}</span>
        @endif
        <br>
        @if($dados->tipo_contratacao == 'Operacional')
            Já trabalhou como mecânico de manutenção:
            <span>{{ $dados->trabalhou_mecanico_manutencao ? 'Sim' : 'Não' }}</span>
            @if($dados->trabalhou_mecanico_manutencao)
                | Expecifique: <span>{{ $dados->trabalhou_mecanico_manutencao_ex }}</span>
            @endif
            <br>
            Já inverteu raquete com produto químico dentro:
            <span>{{ $dados->trabalhou_raquete_produto_quimico ? 'Sim' : 'Não' }}</span>
            @if($dados->trabalhou_raquete_produto_quimico)
                | Expecifique: <span>{{ $dados->trabalhou_raquete_produto_quimico_ex }}</span>
            @endif
            <br>
            Quais os tipos de talha: <span>{{ $dados->tipos_de_talha }}</span>
            <br>
            Sabe como iniciar a abertura e fechamento Flange com uso de Pneutorque ou chave de bater:
            <span>{{ $dados->fechamento_flange ? 'Sim' : 'Não' }}</span>
            @if($dados->fechamento_flange)
                | Expecifique: <span>{{ $dados->fechamento_flange_ex }}</span>
            @endif
            <br>
            Quantos milimetros tem uma polegada: <span>{{ $dados->milimetros_polegada }}</span>
            <br>
            Tem experiência com manuseio de maçarico: <span>{{ $dados->manuseio_macarico ? 'Sim' : 'Não' }}</span>
            @if($dados->manuseio_macarico)
                | Expecifique: <span>{{ $dados->manuseio_macarico_ex }}</span>
            @endif
            <br>
            Já trocou valvulas ou tubulação com uso de talha corrente ou catraca:
            <span>{{ $dados->trocou_valvulas ? 'Sim' : 'Não' }}</span>
            @if($dados->trocou_valvulas)
                | Expecifique: <span>{{ $dados->trocou_valvulas_ex }}</span>
            @endif
            <br>
            Quais as ferramentas utilizadas para elevação de carga:
            <span>{{ $dados->ferramentas_elevacao_carga }}</span>
            <br>
            Opera Plataforma Móvel: <span>{{ $dados->opera_plat_movel ? 'Sim' : 'Não' }}</span>
            @if($dados->opera_plat_movel)
                | Expecifique: <span>{{ $dados->opera_plat_movel_ex }}</span>
            @endif
            <br>
            Opera Ponte Rolante: <span>{{ $dados->opera_plat_ponte ? 'Sim' : 'Não' }}</span>
            @if($dados->opera_plat_ponte)
                | Expecifique: <span>{{ $dados->opera_plat_onte_ex }}</span>
            @endif
            <br>
            Tem experiência com movimentação de Cargas/Rigger:
            <span>{{ $dados->experiencia_cargas_rigger ? 'Sim' : 'Não' }}</span>
            @if($dados->experiencia_cargas_rigger)
                | Expecifique: <span>{{ $dados->experiencia_cargas_rigger_ex }}</span>
            @endif
            <br>
            Já trabalhou em Overhaul antes: <span>{{ $dados->trabalhou_overhaul ? 'Sim' : 'Não' }}</span>
            @if($dados->trabalhou_overhaul)
                | Expecifique: <span>{{ $dados->trabalhou_overhaul_ex }}</span>
            @endif
        @endif
        @if($dados->tipo_contratacao == 'Administrativa')
            <strong>Me Fale sobre você e suas experiências profissionais:</strong><br>
            {!! $dados->texto_livre !!}
        @endif
        <br>
        <fieldset>
            <legend>Parecer Final</legend>
        </fieldset>
        Resultado Final: <span>{{ $dados->resultado_final }}</span> | Nota <span>{{ $dados->nota }}</span> <br>
        Entrevistado por <span>{{ $dados->quem_entrevistou }}</span> <br>
        @php
            $dataTecnica = new \MasterTag\DataHora($dados->created_at);
        @endphp
        Data da Entrevista: <span>{{ $dataTecnica->dataCompleta() }} às {{ $dataTecnica->hora() }}:{{ $dataTecnica->minuto() }}h</span>

    </div>

    <div class="h5">

        <br>
        <br>
        Data da Emissão da ficha:
        <span>{{ (new \MasterTag\DataHora())->dataCompleta()}} às {{ (new \MasterTag\DataHora())->horaCompleta()}}</span><br>
        Usuário que emitou a ficha: <span>{{ \Illuminate\Support\Facades\Auth::user()->nome }}</span>
    </div>
@endsection
