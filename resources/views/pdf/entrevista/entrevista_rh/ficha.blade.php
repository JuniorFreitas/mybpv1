@extends('layouts.pdf')
@section('title','RESULTADO INTEGRADO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    @php
        $curriculo = $dados->Curriculo;
        $rh = $dados->ParecerRh;
      //  $transporte = $dados->FeedbackCurriculo->parecerRota;
      //  $tecnica = $dados->FeedbackCurriculo->parecerTecnica;
      //  $teste = $dados->FeedbackCurriculo->parecerTeste;
    @endphp
    <h5 class="text-center">RESULTADO INTEGRADO</h5>

    <div class="h5">
        <fieldset>
            <legend>Dados Gerais</legend>
        </fieldset>
    </div>

    <div class="h5">
        Nome: <span>{{ $curriculo->nome }}</span> <br>
        Data de Nascimento: <span>{{ $curriculo->nascimento }}</span> - <span>{{ $curriculo->idade }} anos</span> <br>
        Escolaridade:
        <span>{{ $curriculo->Formacao->tipo }} {{$curriculo->formacao_curso ? "($curriculo->formacao_curso)" : null}} </span>
        <br>
        Endereço: <span>{{ $curriculo->endereco_completo }}</span><br>
        Contato:
        <span>{{ $dados->TelPrincipal ? $dados->TelPrincipal->Formatado() : 'não informado' }}</span>
        |
        E-mail: <span>{{ $curriculo->email }}</span>
        <br> Vaga:<span> {{ $dados->VagaSelecionada->nome }}</span> |UF Vaga: <span>{{ $curriculo->uf_vaga }}</span>
        <br>
        Ex funcionário: <span>{{ $rh->ex_funcionario ? 'Sim' : 'Não' }}</span>
    </div>

    <br>
    <h5 class="titulo">PROVAS</h5>

    <div class="h5">
        @foreach($dados->provas as $prova)
            <fieldset>
                <legend>{{$prova->simulado->titulo}}</legend>
            </fieldset>
            Quantidade de Acertos: <span>{{ $prova->acertos }} de {{ $prova->simulado->qnt_questoes }} questões</span>
            <br/>
            Tempo realizado: <span>{{ $prova->tempo_realizado }} minutos</span><br/>
            Status: <span style="text-transform: capitalize">{{ $prova->status }}</span><br/>
            Finalização: <span>{{ $prova->data_finalizacao }}h</span><br/>
            <br>
        @endforeach
    </div>

    <h5 class="titulo">ENTREVISTA INDIVIDUAL</h5>

    <div class="h5">
        Nota teste digitação: <span>{{ $rh->nota_digitacao }}</span> <br>
        Avaliação Dinamica de Grupo: <span>{{ $rh->dinamicadegrupo }}</span><br>
        Observação: <span>{{ $rh->obs_dinamicadegrupo }}</span><br>
        <br>
        <fieldset>
            <legend>Histórico Familiar e Social</legend>
        </fieldset>
        Mora com quem: <span>{{ $rh->mora_com_quem }}</span> <br>
        Casado: <span>{{ $rh->casado ? 'Sim' : 'Não' }}</span>
        @if($rh->casado)
            | Tempo de convivência: <span>{{ $rh->tempodeconvivencia }}</span>
            <br>
            Esposa ou Marido Trabalha? <span>{{ $rh->conjuge_trabalha ? 'Sim' : 'Não' }}</span>
            @if($rh->conjuge_trabalha)
                | Em quê? <span>{{ $rh->trabalho_conjuge }}</span>
            @endif
        @endif
        <br> Filho(s): <span>{{ $rh->filhos ? 'Sim' : 'Não' }}</span>

        @if($rh->filhos)
            | Quantos : <span>{{ $rh->qnt_filhos }}</span>
        @endif
        <br>
        Fuma : <span>{{ $rh->fuma  ? 'Sim' : 'Não' }}</span>
        @if ($rh->fuma )
            | Frequência <span>{{ $rh->frequencia_fuma }}</span>
        @endif
        <br>
        Bebe : <span>{{ $rh->bebe  ? 'Sim' : 'Não' }}</span>
        @if ($rh->bebe )
            | Frequência <span>{{ $rh->frequencia_bebe }}</span>
        @endif
        <br>
        Indicado por alguém : <span>{{ $rh->indicacao  ? 'Sim' : 'Não' }}</span>
        @if ($rh->indicacao )
            | Quem: <span>{{ $rh->indicacao }}</span>
        @endif
        <br>
        <br>
        <fieldset>
            <legend>Outras informações</legend>
        </fieldset>

        Experiência em Call Center: <span>{{ $rh->experiencia_callcenter ? 'Sim' : 'Não' }}</span><br>
        Observação: <span>{{ $rh->obs_call }}</span><br>
        Grau de instrução: <span>{{ $rh->grau_instrucao }}</span>
        <br>
        Disponibilidade de horários: <span>{{ $rh->disponibilidade_horarios}}</span><br>
        Disponibilidade para turnos 6X1: <span>{{ $rh->turnos_seis_por_um ? 'Sim' : 'Não' }}</span><br>
        Horário Preferencial: <span>{{ $rh->horario_preferencial }}</span><br>
        Obs.: <span>{{ $rh->obs_horario }}</span>
        <br>
        Especifique situações de saúde: <span>{{ $rh->situacao_saude }}</span>
        <br>
        <br>
        <fieldset>
            <legend>Cursos de Formação</legend>
        </fieldset>

        @forelse($rh->CursosFormacao as $item)
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

        Comportamento Seguro: <span>{{ $rh->comportamento_seguro  }}</span><br>
        Energia para o trabalho: <span>{{ $rh->energia_para_trabalho  }}</span><br>
        Postura: <span>{{ $rh->postura  }}</span>
        <br>
        <br>
        <fieldset>
            <legend>Histórico Profissional</legend>
        </fieldset>
        Quais as suas últimas experiências? Nome das empresas, cargos ocupados, tempo de permanência nas funções e os
        motivos de saída:
        <br>
        <span>{{ $rh->historico_profissional  }}</span>
        <br>
        <br>
        <fieldset>
            <legend>Histórico Educacional</legend>
        </fieldset>
        Fale-me sobre sua formação educacional e cursos: <br>
        <span>{{ $rh->historico_educacional  }}</span>
        <br>
        <br>
        <fieldset>
            <legend>OBJETIVOS E EXPECTATIVAS</legend>
        </fieldset>
        {{--        Quais são suas expectativas em relação à essa empresa e ao cargo ao qual você se candidatou? Geralmente, que o--}}
        {{--        motiva e o que você detesta no trabalho? Que seria um ambiente ideal de trabalho? Quais são seus planos--}}
        {{--        profissionais em curto prazo? E a longo? Quais são seus planos pessoais? Porque deveríamos contratá-lo? --}}

        <span>{{ $rh->objetivos_expectativas  }}</span>
        <br>
        <br>
        <fieldset>
            <legend>AUTO-IMAGEM</legend>
        </fieldset>
        {{--        Quais são suas maiores qualidades? Que aspectos da sua vida você precisa melhorar? Qual foi sua maior--}}
        {{--        frustração? Qual é o seu maior sonho? --}}

        <span>{{ $rh->auto_imagem  }}</span>
        <br>
        <br>
        <fieldset>
            <legend>COMPETÊNCIAS</legend>
        </fieldset>
        {{--        Conte-me, com riqueza de detalhes, um fato recente que realmente tenha ocorrido no âmbito profissional. Caso a--}}
        {{--        situação perguntada não faça parte do seu histórico profissional, busque-a na sua formação acadêmica e por fim--}}
        {{--        na sua vida pessoal. <br>--}}
        {{--        Ao contar o fato lembre-se de citar o contexto, a ação e o resultado, ou seja, o momento em que aconteceu, o que--}}
        {{--        você fez para resolvê-la e o resultado da sua ação. --}}
        <span style="text-transform: uppercase">
            @switch($rh->competencias)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>

        <br>
        <fieldset>
            <legend>COMPORTAMENTO ÉTICO</legend>
        </fieldset>
        {{--        Descreva um fato em que alguém solicitou que você procedesse contra uma norma ou regra. <br>--}}
        {{--        Conte-me um fato em que foi solicitado um procedimento fora dos padrões, em que precisaria agir inadequadamente.--}}
        {{--        O que você fez? Quais foram os resultados? <br>--}}
        {{--        Conte uma situação em que lhe foi solicitado agir em desacordo com a política da empresa. --}}
        <span style="text-transform: uppercase">
            @switch($rh->comportamento_etico)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>

        <br>
        <fieldset>
            <legend>COMPROMETIMENTO</legend>
        </fieldset>
        {{--        Descreva uma situação na qual o seu comprometimento com a empresa foi primordial para que um problema fosse--}}
        {{--        resolvido.--}}
        {{--        <br>--}}
        {{--        Descreva um fato no qual a sua disciplina e organização foram essenciais para o sucesso de uma ação. <br>--}}
        {{--        Conte-me uma situação em que você demonstrou disponibilidade para a empresa na qual trabalhava--}}
        {{--        <br>--}}
        <span style="text-transform: uppercase">
            @switch($rh->comprometimento)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>

        <br>
        <fieldset>
            <legend>COMUNICAÇÃO</legend>
        </fieldset>
        {{--        Todos nós já passamos por situações em que não compreendemos o que nos foi comunicado. Por exemplo: um prazo de--}}
        {{--        entrega, instruções complicadas, etc. Conte uma situação vivenciada onde isso aconteceu com você. Como você--}}
        {{--        solucionou?<br>--}}
        {{--        Qual foi o pior problema de comunicação que você já enfrentou? Relate-nos essa experiência.--}}

        <span style="text-transform: uppercase">
            @switch($rh->comunicacao)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>

        <br>
        <fieldset>
            <legend>CULTURA DA QUALIDADE</legend>
        </fieldset>
        {{--        Conte-me um fato em que foi necessário ser obediente aos procedimentos e regras organizacionais, sendo preciso--}}
        {{--        ajustar a ação a ser tomada para que esta se adequasse à política da empresa. <br>--}}
        {{--        Cite uma situação que você recorreu ao sistema de gestão da qualidade para resolver um problema. <br>--}}
        {{--        Cite uma situação que você precisou desenvolver uma nova metodologia ou um novo procedimento para resolver ou--}}
        {{--        evitar algum problema na empresa.--}}

        <span style="text-transform: uppercase">
            @switch($rh->cultura_qualidade)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>

        <br>
        <fieldset>
            <legend>FOCO NO CLIENTE</legend>
        </fieldset>
        {{--        Conte uma situação em que você direcionou seus esforços para satisfazer as necessidades do cliente (interno ou--}}
        {{--        externo). <br>--}}
        {{--        Cite um fato no qual foi necessário agir com atenção, respeito e cortesia para com um cliente (interno ou--}}
        {{--        externo), mesmo já estando irritado com a situação. Você conseguiu controlar a sua irritação e resolver a--}}
        {{--        questão do cliente? <br>--}}
        {{--        Ao tratar com um cliente, cite uma situação na qual você conseguiu identificar a necessidade do cliente,--}}
        {{--        surpreendendo-o com a sua iniciativa. O que você sentiu quando isso aconteceu?--}}

        <span style="text-transform: uppercase">
            @switch($rh->foco_cliente)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>

        <br>
        <fieldset>
            <legend>INICIATIVA</legend>
        </fieldset>
        {{--        Conte uma situação na qual você precisou se antecipar para resolucionar um problema. <br>--}}
        {{--        Fale sobre algum projeto ou idéia que foram aceitos, introduzidos ou realizados com sucesso, principalmente em--}}
        {{--        decorrência de sua iniciativa. <br>--}}
        {{--        O que você fez recentemente para tornar seu trabalho mais interessante, desafiador, motivante?--}}
        <span style="text-transform: uppercase">
            @switch($rh->iniciativa)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>
        <br>
        <fieldset>
            <legend>ORIENTAÇÃO PARA RESULTADOS</legend>
        </fieldset>
        {{--        Descreva algumas metas desafiadoras que você planejou para si mesmo e de que forma agiu frente a elas? Conseguiu--}}
        {{--        atingi-las? <br>--}}
        {{--        Fale sobre a meta mais desafiadora que você teve que alcançar. De que forma agiu? Quais foram os resultados?--}}
        <span style="text-transform: uppercase">
            @switch($rh->orientacao_resultados)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>
        <br>
        <fieldset>
            <legend>TRABALHO EM EQUIPE</legend>
        </fieldset>
        {{--        Explicite um momento que você precisou se adaptar às diversas situações num determinado grupo. O que você fez?--}}
        {{--        Como você reage quando tem que interagir com outros colegas para a conclusão de uma tarefa? Cite uma situação--}}
        {{--        real. Por que o trabalho em equipe traz resultados mais eficazes? Cite uma situação vivida por você que o faz--}}
        {{--        afirmar isso.--}}
        <span style="text-transform: uppercase">
            @switch($rh->trabalho_equipe)
                @case(1)
                Não atende ao desempenho esperado
                @break
                @case(2)
                Atende parcialmente
                @break
                @case(3)
                Atende ao esperado
                @break
                @case(4)
                Supera as expectativas
                @break
            @endswitch
        </span>
        <br>
        <br>

        <fieldset>
            <legend>PARECER FINAL INDIVIDUAL</legend>
        </fieldset>
        Parecer: <span style="text-transform: capitalize">{{ $rh->individualRh->parecer }}</span> | Nota:
        <span>{{ $rh->individualRh->nota }}</span><br>
        @if(auth()->user()->cliente_id ==1)
            Entrevistado por <span>{{ $rh->individualRh->entrevistado_por }}</span><br>
        @endif
        Comentários: <span>{{ $rh->individualRh->comentarios }}</span>
        @if(auth()->user()->cliente_id ==1)
            @php
                $dataEntrevista = new \MasterTag\DataHora($rh->individualRh->created_at);
            @endphp
            <br>
            Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>
        @endif
        <br>
        <br>

        <fieldset>
            <legend>PARECER FINAL RH</legend>
        </fieldset>
        @if($rh->entrevistaRh)
            Parecer: <span style="text-transform: capitalize">{{ $rh->entrevistaRh->parecer }}</span> |
            Nota: <span>{{ $rh->entrevistaRh->nota }}</span><br>
            Indicado para: <span>{{ $rh->entrevistaRh->indicado_para }}</span><br>
            Entrevistado por <span>{{ $rh->entrevistaRh->entrevistado_por }}</span><br>
            Comentários: <span>{{ $rh->entrevistaRh->comentarios }}</span> <br>
            @php
                $dataEntrevista = new \MasterTag\DataHora($rh->entrevistaRh->created_at);
            @endphp
            Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>
        @else
            SEM REGISTRO NO MOMENTO
        @endif
        <br>
        <br>

        <fieldset>
            <legend>PARECER FINAL GESTOR</legend>
        </fieldset>
        @if($rh->gestorRh)
            Parecer: <span style="text-transform: capitalize">{{ $rh->gestorRh->parecer }}</span> | Nota:
            <span>{{ $rh->gestorRh->nota }}</span><br>
            Indicado para: <span>{{ $rh->gestorRh->indicado_para }}</span><br>
            Entrevistado por <span>{{ $rh->gestorRh->entrevistado_por }}</span><br>
            Comentários: <span>{{ $rh->gestorRh->comentarios }}</span> <br>
            @php
                $dataEntrevista = new \MasterTag\DataHora($rh->gestorRh->created_at);
            @endphp
            Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>
        @else
            SEM REGISTRO NO MOMENTO
        @endif
    </div>

    <br>
    <h5>
        Data da Emissão da ficha:
        <span>{{ (new \MasterTag\DataHora())->dataCompleta()}} às {{ (new \MasterTag\DataHora())->horaCompleta()}}</span>
        |
        Usuário que emitou a ficha: <span>{{ \Illuminate\Support\Facades\Auth::user()->nome }}</span>
    </h5>
@endsection
