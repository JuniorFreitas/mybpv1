<table>
    <thead>
    <tr>
        <td>Id</td>
        <td>Nome</td>
        <td>Centro de Custo</td>
        <td>Data Saída</td>
        <td>Data Retorno</td>
        <td>Quantidade de Dias</td>
        <td>Saldo de Dias</td>
        <td>Quem Cadastrou</td>
        <td>Gestor Indicado</td>
        <td>Gestor Aprovação</td>
        <td>Data da Aprovação</td>
        <td>Status</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Colaborador->id}}</td>
            <td>{{$row->Colaborador->nome}}</td>
            <td>{{$row->CentroCusto->label}}</td>
            <td>{{$row->data_saida}}</td>
            <td>{{$row->data_retorno}}</td>
            <td>{{$row->qnt_dias}}</td>
            <td>{{$row->dias_saldo}}</td>
            <td>{{$row->UserCadastrou->nome}}</td>
            <td>{{$row->GestorAprovacao->nome}}</td>
            <td>{{$row->QuemAprovou->nome}}</td>
            <td>{{$row->data_aprovacao}}</td>
            <td>{{$row->status_aprovacao}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
