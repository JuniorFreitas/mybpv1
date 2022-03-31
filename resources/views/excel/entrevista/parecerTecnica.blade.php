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
        <td>Tempo na função</td>
        <td>Ja trabalhou na ALUMAR</td>
        <td>Tem Rota</td>
        <td>Disponibilidade para turno</td>
        <td>Indicação</td>
        <td>Indicado por</td>
        <td>Tem experiência com manuseio de maçarico</td>
        <td>Expecifique</td>
        <td>Opera plataforma movel</td>
        <td>Expecifique</td>
        <td>Opera ponte rolante</td>
        <td>Expecifique</td>
        <td>Tem experiencia com movimentação de cargas / rigger</td>
        <td>Expecifique</td>
        <td>Ja trabalhou em overhaul antes</td>
        <td>Qual área</td>
        <td>Parecer Final Entrevista Técnica</td>
        <td>Nota Entrevista Técnica</td>
        <td>Entrevistado por</td>
        <td>Indicado para qual área</td>
        <td>Parecer Final do Teste Nota</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Curriculo->id}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->destro  : ''}}</td>
            <td>{{$row->Curriculo->nascimento}}</td>
            <td>{{$row->Curriculo->idade}}</td>
            <td>{{$row->Curriculo->logradouro}}, {{$row->Curriculo->bairro}}, {{$row->Curriculo->municipio}} - {{$row->Curriculo->uf}}</td>
            <td>{{$row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->Cliente->cnpj ? $row->Cliente->nome_fantasia : $row->Cliente->nome}}</td>
            <td>{{$row->VagaAberta->VagaSelecionada->nome . ' - ' . $row->VagaAberta->Municipio->uf  }}</td>
            <td>{{$row->Curriculo->Formacao->tipo}} {{$row->Curriculo->FormacaoCurso ? '('. $row->Curriculo->FormacaoCurso. ')' : '' }} </td>
            <td>{{$row->parecerRh ? $row->parecerRh->nota : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->rota_atende ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->tempo_funcao : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->trabalhou_alumar ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->rota ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->turno ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->indicado ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->indicado_por : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->manuseio_macarico ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->manuseio_macarico_ex : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->opera_plat_movel ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->opera_plat_movel_ex : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->opera_plat_ponte ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->opera_plat_onte_ex : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->experiencia_cargas_rigger ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->experiencia_cargas_rigger_ex : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->trabalhou_overhaul ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->trabalhou_overhaul_ex : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->resultado_final : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->nota : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->quem_entrevistou : ''}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->indicado_area : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'Aguardando'}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
