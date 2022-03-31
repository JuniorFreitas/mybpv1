@extends('layouts.pdf')
@section('title','PARECER RH')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    @php
        //$dados->FeedbackCurriculo->Curriculo = $dados->Curriculo;
        //$dados = $dados;
        //dd()
    @endphp
    <h5 class="text-center">PARECER RH</h5>

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
        <span>{{ $dados->FeedbackCurriculo->Curriculo->Formacao->tipo }} {{$dados->FeedbackCurriculo->Curriculo->formacao_curso ? $dados->FeedbackCurriculo->Curriculo->formacao_curso : null}} </span>
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
        Funcionário:<span> {{ $dados->ex_funcionario }}</span>
    </div>

    <div class="h5">
        <fieldset>
            <legend>Parecer RH</legend>
        </fieldset>
        Rota/Bairro: <span>{{ $dados->rota_bairro }}</span> <br>
        <fieldset>
            <legend>EPI Pontuação</legend>
        </fieldset>
        Camisa de Meia: <span>{{ $dados->camisa_meia }}</span> |
        Camisa Proteção: <span>{{ $dados->camisa_protecao }}</span> |
        Calça: <span>{{ $dados->calca }}</span> <br>
        <fieldset>
            <legend>Histórico Familiar e Social</legend>
        </fieldset>
        Mora com quem: <span>{{ $dados->mora_com_quem }}</span> <br>
        Casado: <span>{{ $dados->casado ? 'Sim' : 'Não' }}</span>
        @if($dados->casado)
            | Tempo de convivência: <span>{{ $dados->tempodeconvivencia }}</span>
            <br>
            Esposa ou Marido Trabalha? <span>{{ $dados->conjuge_trabalha ? 'Sim' : 'Não' }}</span>
            @if($dados->conjuge_trabalha)
                | Em quê? <span>{{ $dados->trabalho_conjuge }}</span>
            @endif
        @endif
        <br> Filho(s): <span>{{ $dados->filhos ? 'Sim' : 'Não' }}</span>

        @if($dados->filhos)
            | Quantos : <span>{{ $dados->qnt_filhos }}</span>
        @endif
        <br>
        Fuma : <span>{{ $dados->fuma  ? 'Sim' : 'Não' }}</span>
        @if ($dados->fuma )
            | Frequência <span>{{ $dados->frequencia_fuma }}</span>
        @endif
        <br>
        Bebe : <span>{{ $dados->bebe  ? 'Sim' : 'Não' }}</span>
        @if ($dados->bebe )
            | Frequência <span>{{ $dados->frequencia_bebe }}</span>
        @endif
        <br>
        Indicado por alguém : <span>{{ $dados->indicacao  ? 'Sim' : 'Não' }}</span>
        @if ($dados->indicacao )
            | Quem: <span>{{ $dados->indicacao }}</span>
        @endif
        <br>
        <fieldset>
            <legend>Outras informações</legend>
        </fieldset>

        Experiência na ALUMAR: <span>{{ $dados->alumar_experiencia ? 'Sim' : 'Não' }}</span>
        @if ($dados->alumar_experiencia )
            | Qual área: <span>{{ $dados->alumar_experiencia_area }}</span>
        @endif
        <br>
        Experiência em outra industria: <span>{{ $dados->outra_industria_experiencia ? 'Sim' : 'Não' }}</span>
        @if ($dados->outra_industria_experiencia )
            | Qual: <span>{{ $dados->outra_industria_nome }}</span>
        @endif
        <br>
        Grau de instrução: <span>{{ $dados->grau_instrucao }}</span>
        <br>
        Disponibilidade de hora extra: <span>{{ $dados->horaextra ? 'Sim' : 'Não' }}</span>
        | Disponibilidade para turnos 6X2: <span>{{ $dados->turnos_seis_por_dois ? 'Sim' : 'Não' }}</span>
        | Disponibilidade para noturno: <span>{{ $dados->noturno ? 'Sim' : 'Não' }}</span>
        <br>
        Acidente de trabalho anterior: <span>{{ $dados->acidente_trabalho ? 'Sim' : 'Não' }}</span>
        @if ($dados->acidente_trabalho )
            | Qual: <span>{{ $dados->acidente_trabalho_qual }}</span>
        @endif
        <br>
        Afastamento INSS anterior: <span>{{ $dados->afastamento_inss ? 'Sim' : 'Não' }}</span>
        @if ($dados->afastamento_inss )
            | Qual: <span>{{ $dados->afastamento_inss_qual }}</span>
        @endif
        <br>
        Especifique situações de saúde: <span>{{ $dados->situacao_saude }}</span>
        <br>
        Certificado NR 10: <span>{{ $dados->nr_dez }}</span>
        @if ($dados->nr_dez == 'sim')
            <br>
            <fieldset>
                <legend>Informações Certificado NR10</legend>
            </fieldset>
            @foreach($dados->Nr as $item)
                Instituição: <span>{{ $item->instituicao }}</span> <br>
                Data de Emissão: <span>{{ \MasterTag\DataHora::dataFormatada($item->nr_dez_emissao) }}</span> <br>
                Data de Validade: <span>{{ \MasterTag\DataHora::dataFormatada($item->nr_dez_validade) }}</span> <br>
                <hr>
            @endforeach
        @endif
        <br>
        <fieldset>
            <legend>Cursos de Formação</legend>
        </fieldset>

        @forelse($dados->FeedbackCurriculo->CursosFormacoes as $item)
            Curso: <span>{{ $item->curso }}</span> <br>
            Instituição: <span>{{ $item->instituicao }}</span> <br>
            Data de Emissão: <span>{{ \MasterTag\DataHora::dataFormatada($item->emissao) }}</span> <br>
            Data de Validade: <span>{{ \MasterTag\DataHora::dataFormatada($item->validade) }}</span> <br>
            <hr>
        @empty
            Nenhum Curso Adicionado
        @endforelse
        <br>
        <div style="height: 1px; border-top: 1px solid #333; width: 100%; margin-top: 3px; margin-bottom: 3px;"></div>
        Comportamento Seguro: <span>{{ $dados->comportamento_seguro  }}</span> |
        Energia para o trabalho: <span>{{ $dados->energia_para_trabalho  }}</span> |
        Postura: <span>{{ $dados->postura  }}</span>
        <br>
        <fieldset>
            <legend>Histórico Profissional</legend>
        </fieldset>
        Quais as suas últimas experiências? Nome das empresas, cargos ocupados, tempo de permanência nas funções e os
        motivos de saída:
        <br>
        <span>{{ $dados->historico_profissional  }}</span>
        <br>
        <fieldset>
            <legend>Histórico Educacional</legend>
        </fieldset>
        Fale-me sobre sua formação educacional e cursos: <br>
        <span>{{ $dados->historico_educacional  }}</span>
        <br>
        <fieldset>
            <legend>Parecer Final RH</legend>
        </fieldset>
        Parecer: <span>{{ $dados->parecer_final }}</span> | Resultado: <span>{{ $dados->parecer_final_um }}</span> |
        Nota:
        <span>{{ $dados->nota }}</span>
        <br>
        Entrevistado por <span>{{ $dados->quem_entrevistou }}</span> <br>
        Comentários: <span>{{ $dados->comentarios }}</span> <br>
        Data da Entrevista: <span>{{ $dados->data_entrevista }}</span>
    </div>


    <div class="h5">

        <br>
        <br>
        Data da Emissão da ficha:
        <span>{{ (new \MasterTag\DataHora())->dataCompleta()}} às {{ (new \MasterTag\DataHora())->horaCompleta()}}</span><br>
        Usuário que emitiu a ficha: <span>{{ auth()->user()->nome }}</span>
    </div>

@endsection
