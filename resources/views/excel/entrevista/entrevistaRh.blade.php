<table>
    <thead>
    <tr>
        <td>Id</td>
        <td>Nome</td>
        <td>Nascimento</td>
        <td>Idade</td>
        <td>Endereço</td>
        <td>Contato</td>
        <td>E-mail</td>
        <td>Vaga</td>
        <td>Escolaridade</td>
        <td>Nota teste digitação</td>
        <td>Avaliação Dinamica de Grupo</td>
        <td>Obs Dinamica de Grupo</td>
        <td>Ex Funcionário</td>
        <td>Rota Bairro</td>
        <td>Mora com quem</td>
        <td>Casado</td>
        <td>Tempo de convivencia</td>
        <td>Filhos</td>
        <td>Quantos Filhos</td>
        <td>Esposa ou Marido Trabalha</td>
        <td>Esposa ou Marido em que Trabalha</td>
        <td>Praticante de alguma religião</td>
        <td>Qual religião</td>
        <td>Fuma</td>
        <td>Qual frequencia</td>
        <td>Bebe</td>
        <td>qual frequencia</td>
        <td>Indicado por alguem</td>
        <td>Quem indicou</td>

        <td>Experiência em Call Center</td>
        <td>Observação</td>
        <td>Grau de instrução</td>

        <td>Disponibilidade de horários</td>
        <td>Disponibilidade para turnos 6X1</td>
        <td>Horário Preferencial</td>
        <td>Observação</td>

        <td>Especifique situações de saúde</td>
        <td>Comportamento Seguro</td>
        <td>Energia para trabalho</td>
        <td>Postura</td>

        <td>Histórico Profissional</td>
        <td>Histórico Educacional</td>

        <td>Parecer Individual</td>
        <td>Parecer Individual Nota</td>
        {{--        <td>Parecer Individual Entrevistado Por</td>--}}
        {{--        <td>Parecer Individual Data</td>--}}

        <td>Parecer RH</td>
        <td>Parecer RH indicado para</td>
        <td>Parecer RH Nota</td>
        <td>Parecer RH Entrevistado Por</td>
        <td>Parecer RH Data</td>

        <td>Parecer Gestor</td>
        <td>Parecer Gestor indicado para</td>
        <td>Parecer Gestor Nota</td>
        <td>Parecer Gestor Entrevistado Por</td>
        <td>Parecer Gestor Data</td>

    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Curriculo->id}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->Curriculo->nascimento}}</td>
            <td>{{$row->Curriculo->idade}}</td>
            <td>{{$row->Curriculo->logradouro}}, {{$row->Curriculo->bairro}}, {{$row->Curriculo->municipio}}
                - {{$row->Curriculo->uf}}</td>
            <td>{{$row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->VagaSelecionada->nome}}</td>
            <td>{{$row->Curriculo->Formacao->tipo}} {{$row->Curriculo->FormacaoCurso ? '('. $row->Curriculo->FormacaoCurso. ')' : '' }} </td>
            <td>{{$row->parecerRh ? $row->parecerRh->nota_digitacao : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->dinamicadegrupo : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->obs_dinamicadegrupo : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->ex_funcionario ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->rota_bairro : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->mora_com_quem : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->casado ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->tempodeconvivencia : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->filhos ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->qnt_filhos : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->conjunge_trabalha ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->trabalho_conjunge : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->religioso ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->religiao_praticante : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->fuma ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->frequencia_fuma : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->bebe ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->frequencia_bebe : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->inidicacao ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->indicado_por : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->experiencia_callcenter ? 'Sim' : 'Não' : ''}}</td>
                        <td>{{!is_null($row->parecerRh->obs_call) ? $row->parecerRh->obs_call :''  }}</td>
                        <td>{{$row->parecerRh ? $row->parecerRh->grau_instrucao : ''}}</td>

            <td>{{$row->parecerRh ? $row->parecerRh->disponibilidade_horarios : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->turnos_seis_por_um ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->horario_preferencial : ''}}</td>
            <td>{{$row->parecerRh->obs_horario ? $row->parecerRh->obs_horario : '' }}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->situacao_saude : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->comportamento_seguro : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->energia_para_trabalho : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->postura : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->historico_profissional : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->historico_educacional : ''}}</td>

            <td>{{$row->parecerRh->individualRh ? $row->parecerRh->individualRh->parecer : ''}}</td>
            <td>{{$row->parecerRh->individualRh ? $row->parecerRh->individualRh->nota : ''}}</td>
            {{--            <td>{{$row->parecerRh->individualRh ? $row->parecerRh->individualRh->entrevistado_por : ''}}</td>--}}
            {{--            <td>{{$row->parecerRh->individualRh ? \App\Models\Sistema::dataBrasil($row->parecerRh->individualRh->created_at) : ''}}</td>--}}

            <td>{{$row->parecerRh->entrevistaRh ? $row->parecerRh->entrevistaRh->parecer : ''}}</td>
            <td>{{$row->parecerRh->entrevistaRh ? $row->parecerRh->entrevistaRh->indicado_para : ''}}</td>
            <td>{{$row->parecerRh->entrevistaRh ? $row->parecerRh->entrevistaRh->nota : ''}}</td>
            <td>{{$row->parecerRh->entrevistaRh ? $row->parecerRh->entrevistaRh->entrevistado_por : ''}}</td>
            <td>{{$row->parecerRh->entrevistaRh ? \App\Models\Sistema::dataBrasil($row->parecerRh->entrevistaRh->created_at) : ''}}</td>

            <td>{{$row->parecerRh->gestorRh ? $row->parecerRh->gestorRh->parecer : ''}}</td>
            <td>{{$row->parecerRh->gestorRh ? $row->parecerRh->gestorRh->indicado_para : ''}}</td>
            <td>{{$row->parecerRh->gestorRh ? $row->parecerRh->gestorRh->nota : ''}}</td>
            <td>{{$row->parecerRh->gestorRh ? $row->parecerRh->gestorRh->entrevistado_por : ''}}</td>
            <td>{{$row->parecerRh->gestorRh ? \App\Models\Sistema::dataBrasil($row->parecerRh->gestorRh->created_at) : ''}}</td>

        </tr>
    @endforeach
    </tbody>
</table>
