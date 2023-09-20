<table>
    <thead>
    <tr>
        <td>ID</td>
        <td>NOME</td>
        <td>CPF</td>
        <td>RG/EMITENTE</td>
        <td>Pai</td>
        <td>Mãe</td>
        <td>PCD</td>
        <td>Destro/Canhoto</td>
        <td>CNH</td>
        <td>Nascimento</td>
        <td>Idade</td>
        <td>Calça</td>
        <td>Bota</td>
        <td>Camisa Meia</td>
        <td>Camisa Proteção</td>
        <td>Cliente</td>
        <td>Vaga</td>
        <td>Ex Funcionário</td>
        <td>Contato</td>
        <td>E-mail</td>
        <td>Data Admissão</td>
        <td>Função</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Curriculo->id}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->Curriculo->cpf}}</td>
            <td>{{$row->Curriculo->rg != '' ? $row->Curriculo->rg.' | '.$row->Curriculo->orgao_expeditor : ''}}</td>
            <td>{{$row->Curriculo->filiacao_pai}}</td>
            <td>{{$row->Curriculo->filiacao_mae}}</td>
            <td>{{$row->Curriculo->pcd = $row->Curriculo->pcd ? 'Sim' : 'Não'}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->destro : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->cnh_tipo : ''}}</td>
            <td>{{$row->Curriculo->nascimento}}</td>
            <td>{{$row->Curriculo->idade}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->calca : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->bota : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->camisa_meia : ''}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->camisa_protecao : ''}}</td>
            <td>{{$row->FeedbackCurriculo->Cliente->cnpj ? $row->FeedbackCurriculo->Cliente->nome_fantasia : $row->FeedbackCurriculo->Cliente->nome}}</td>
            <td>{{$row->FeedbackCurriculo->VagaAberta->VagaSelecionada->nome . ' - ' . $row->FeedbackCurriculo->VagaAberta->Municipio->uf}}</td>
            <td>{{$row->parecerRh->ex_funcionario ? 'Sim' : 'Não'}}</td>
            <td>{{\App\Models\Curriculo::getTelPrincipal($item->FeedbackCurriculo->curriculo_id)}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->Admissao ? $row->Admissao->data_admissao : null}}</td>
            <td>{{$row->Admissao ? $row->Admissao->funcao : null}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
