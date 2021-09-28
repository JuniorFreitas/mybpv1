{{--{{$data}}--}}
<table>
    <thead>
    <tr>
        <td>Id</td>
        <td>Nome</td>
        <td>Destro/Canhoto</td>
        <td>Nascimento</td>
        <td>Idade</td>
        <td>Endereço</td>
        <td>Contato</td>
        <td>E-mail</td>
        <td>Cliente</td>
        <td>Vaga</td>
        <td>Escolaridade</td>
        <td>Ex Funcionário</td>
        <td>CNH</td>
        <td>Rota Bairro</td>
        <td>Camisa Meia</td>
        <td>Camisa Proteção</td>
        <td>Calça</td>
        <td>Bota</td>
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
        <td>Experiência na ALUMAR</td>
        <td>Qual área</td>
        <td>Experiência em outra industia</td>
        <td>Qual</td>
        <td>Grau de instrução</td>
        <td>Disponibilidade de hora extra</td>
        <td>Disponibilidade para turnos 6X2</td>
        <td>Disponibilidade para noturno</td>
        <td>Acidente de trabalho anterior</td>
        <td>Especifique</td>
        <td>Afastamento INSS anterior</td>
        <td>Especifique</td>
        <td>Especifique situações de saúde</td>
        <td>Comportamento Seguro</td>
        <td>Energia para trabalho</td>
        <td>Postura</td>
        {{--        <td>Certificado NR 10</td>--}}
        {{--        <td>Curso de Formação</td>--}}
        <td>Histórico Profissional</td>
        <td>Histórico Educacional</td>
        <td>Parecer Final RH</td>
        <td>Parecer RH Resultado</td>
        <td>Parecer RH Nota</td>
        <td>Parecer RH Entrevistado Por</td>

        <td>Data Entrevista</td>
        <td>Local Entrevista</td>
        <td>Rota Transporte</td>
        <td>Entrevista Técnica Nota</td>
        <td>Teste Prático Nota</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Curriculo->id}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->destro : ''}}</td>
            <td>{{$row->Curriculo->nascimento}}</td>
            <td>{{$row->Curriculo->idade}}</td>
            <td>{{$row->Curriculo->logradouro}}, {{$row->Curriculo->bairro}}, {{$row->Curriculo->municipio}}
                - {{$row->Curriculo->uf}}</td>
            <td>{{$row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->Cliente->cnpj ? $row->Cliente->nome_fantasia : $row->Cliente->nome}}</td>
            <td>{{$row->VagaSelecionada->nome}}</td>
            <td>{{$row->Curriculo->Formacao->tipo}} {{$row->Curriculo->FormacaoCurso ? '('. $row->Curriculo->FormacaoCurso. ')' : '' }} </td>
            <td>{{$row->parecerRh ? $row->parecerRh->ex_funcionario : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->cnh_tipo : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->rota_bairro : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->camisa_meia : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->camisa_protecao : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->calca : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->bota : ''}}</td>
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
            <td>{{$row->parecerRh ? $row->parecerRh->alumar_experiencia ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->alumar_experiencia_area : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->outra_industria_experiencia ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->outra_industria_nome : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->grau_instrucao : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->horaextra ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->turnos_seis_por_dois ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->noturno ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->acidente_trabalho ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->acidente_trabalho_qual : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->afastamento_inss ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->afastamento_inss_qual : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->situacao_saude : ''}}</td>
            {{--        <td>Certificado NR 10</td>--}}
            {{--        <td>Curso de Formação</td>--}}
            <td>{{$row->parecerRh ? $row->parecerRh->comportamento_seguro : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->energia_para_trabalho : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->postura : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->historico_profissional : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->historico_educacional : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->parecer_final : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->parecer_final_um : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->nota : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->quem_entrevistou : ''}}</td>

            <td>{{$row->data_entrevista}}</td>
            <td>{{$row->local_entrevista}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->tem_rota ? 'Sim' : 'Não' : 'aguardando'}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->nota : 'aguardando'}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'aguardando'}}</td>

        </tr>
    @endforeach
    </tbody>
</table>
