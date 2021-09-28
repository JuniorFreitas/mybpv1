{{--{{$data}}--}}
<table>
    <thead>
    <tr>
        {{--        Nome, CNH, CPF, data de nascimento, email, bairro,   contato,  formação, experiencias (2),--}}
        <td>Id</td>
        <td>Nome</td>
        <td>CNH</td>
        <td>CPF</td>
        <td>Nascimento</td>
        <td>E-mail</td>
        <td>Telefone</td>
        <td>Bairro</td>
        <td>Formação</td>
        <td>Formação Instituição</td>
        <td>Formação Curso</td>
        <td>Formação Status</td>

        <td>Vaga Pretendida</td>

        <td>Qualificação Curso</td>
        <td>Qualificação Instituição</td>
        <td>Qualificação Mês Conclusão</td>
        <td>Qualificação Ano Conclusão</td>

        <td>Qualificação Curso 2</td>
        <td>Qualificação Instituição 2</td>
        <td>Qualificação Mês Conclusão 2</td>
        <td>Qualificação Ano Conclusão 2</td>

        <td>Experiência Empresa</td>
        <td>Experiência Cargo</td>
        <td>Experiência Atividades</td>
        <td>Experiência Data Inicio</td>
        <td>Experiência Data Fim</td>
        <td>Experiência Referência Nome</td>
        <td>Experiência Referência Telefone</td>

        <td>Experiência Empresa 2</td>
        <td>Experiência Cargo 2</td>
        <td>Experiência Atividades 2</td>
        <td>Experiência Data Inicio 2</td>
        <td>Experiência Data Fim 2</td>
        <td>Experiência Referência Nome 2</td>
        <td>Experiência Referência Telefone 2</td>

        <td>Data do Cadastro</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->id}}</td>
            <td>{{$row->nome}}</td>
            <td>{{$row->cnh}}</td>
            <td>{{$row->cpf}}</td>
            <td>{{$row->nascimento}}</td>
            <td>{{$row->email}}</td>
            <td>
                @foreach($row->Telefones as $tel)
                    {{$tel->numero}} / {{$tel->tipoText}}
                @endforeach
            </td>
            <td>{{$row->bairro}}</td>
            <td>{{$row->Formacao->tipo}}</td>
            <td>{{$row->formacao_instituicao}}</td>
            <td>{{$row->formacao_curso}}</td>
            <td>{{$row->formacao_status}}</td>

            <td>{{$row->Vaga->nome}}</td>

            <td>{{isset($row->Qualificacoes[0]) ? $row->Qualificacoes[0]->nome : ''}}</td>
            <td>{{isset($row->Qualificacoes[0]) ? $row->Qualificacoes[0]->instituicao : ''}}</td>
            <td>{{isset($row->Qualificacoes[0]) ? $row->Qualificacoes[0]->mes_conclusao : ''}}</td>
            <td>{{isset($row->Qualificacoes[0]) ? $row->Qualificacoes[0]->ano_conclusao : ''}}</td>

            <td>{{isset($row->Qualificacoes[1]) ? $row->Qualificacoes[1]->nome : ''}}</td>
            <td>{{isset($row->Qualificacoes[1]) ? $row->Qualificacoes[1]->instituicao : ''}}</td>
            <td>{{isset($row->Qualificacoes[1]) ? $row->Qualificacoes[1]->mes_conclusao : ''}}</td>
            <td>{{isset($row->Qualificacoes[1]) ? $row->Qualificacoes[1]->ano_conclusao : ''}}</td>

            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->empresa : ''}}</td>
            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->cargo : ''}}</td>
            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->principais_atv : ''}}</td>
            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->data_inicio : ''}}</td>
            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->data_fim : ''}}</td>
            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->referencia_nome : ''}}</td>
            <td>{{isset($row->Experiencias[0]) ? $row->Experiencias[0]->referencia_telefone : ''}}</td>

            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->empresa : ''}}</td>
            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->cargo : ''}}</td>
            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->principais_atv : ''}}</td>
            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->data_inicio : ''}}</td>
            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->data_fim : ''}}</td>
            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->referencia_nome : ''}}</td>
            <td>{{isset($row->Experiencias[1]) ? $row->Experiencias[1]->referencia_telefone : ''}}</td>

            <td>{{$row->created_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
