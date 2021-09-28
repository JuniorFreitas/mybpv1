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
        <td>Parecer RH Nota</td>

        <td>Tem Rota que atende</td>
        <td>Qual</td>
        <td>Bairro Rota</td>
        <td>Ponto de referência Rota</td>
        <td>Informado sobre ponto de referência</td>
        <td>Qual</td>
        <td>Bairro Residência</td>
        <td>Ponto de referência Residência</td>
        <td>Autorizado Vale Transporte</td>
        <td>Turno A</td>
        <td>Turno B</td>
        <td>Turno C</td>
        <td>Outros</td>
        <td>Parecer Final Rota Rota Atende</td>
        <td>Parecer Final Rota Tipo de Contratação</td>
        <td>Parecer Final Rota Entrevistado Por</td>


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
            <td>{{$row->Curriculo->logradouro}}, {{$row->Curriculo->bairro}}, {{$row->Curriculo->municipio}} - {{$row->Curriculo->uf}}</td>
            <td>{{$row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->Cliente->cnpj ? $row->Cliente->nome_fantasia : $row->Cliente->nome}}</td>
            <td>{{$row->VagaSelecionada->nome}}</td>
            <td>{{$row->Curriculo->Formacao->tipo}} {{$row->Curriculo->FormacaoCurso ? '('. $row->Curriculo->FormacaoCurso. ')' : '' }} </td>
            <td>{{$row->parecerRh ? $row->parecerRh->nota : ''}}</td>

            <td>{{$row->parecerRota ? $row->parecerRota->tem_rota ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->qual : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->bairro_rota : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->ponto_referencia_rota : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->pega_onibus ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->pega_onibus_qual_ponto : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->bairro_residencia : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->ponto_referencia_residencia : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->vale_transporte ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_disponivel_turno_a ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_disponivel_turno_b ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_disponivel_turno_c ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_disponivel_turno_outros : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_atende ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_tipo ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->quem_entrevistou : ''}}</td>

            <td>{{$row->parecerTecnica ? $row->parecerTecnica->nota : 'Aguardando'}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_ðteste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'Aguardando'}}</td>

        </tr>
    @endforeach
    </tbody>
</table>
