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
        <td>Parecer RH Nota</td>
        <td>Parecer Final Rota Rota Atende</td>
        <td>Parecer Final do Teste Nota</td>
        <td>Fez teste prático</td>
        <td>Data e Hora da Realização</td>
        <td>Responsável pelo Teste</td>
        <td>Qual o Teste foi aplicado</td>
        <td>Parecer Final do Teste</td>
        <td>Parecer Final do Teste Nota</td>
        <td>Entrevistado por</td>
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
            <td>{{$row->Curriculo->logradouro}}, {{$row->Curriculo->bairro}}, {{$row->Curriculo->municipio}} - {{$row->Curriculo->uf}}</td>
            <td>{{$row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->Cliente->cnpj ? $row->Cliente->nome_fantasia : $row->Cliente->nome}}</td>
            <td>{{$row->VagaSelecionada->nome}}</td>
            <td>{{$row->Curriculo->Formacao->tipo}} {{$row->Curriculo->FormacaoCurso ? '('. $row->Curriculo->FormacaoCurso. ')' : '' }} </td>
            <td>{{$row->parecerRh ? $row->parecerRh->nota : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->fez_teste ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'Aguardando'}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->fez_teste ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->data_horario_realizacao : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->responsavel_pelo_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->qual_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->parecer_final_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'Aguardando'}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->quem_entrevistou : ''}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
