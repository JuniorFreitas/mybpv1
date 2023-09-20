{{--{{$data}}--}}

<table>
    <thead>
    <tr>
        <td>Id</td>
        <td>Nome</td>
        <td>CPF</td>
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
        <td>Disponibilidade para turnos 6X2</td>
        <td>Indicado por quem</td>
        <td>Indicado para qual área</td>
        <td>Endereço</td>
        <td>Tem Rota que atende</td>
        <td>Qual</td>
        <td>Bairro Rota</td>
        <td>Ponto de referência Rota</td>
        <td>Informado sobre ponto de referência</td>
        <td>Qual</td>
        <td>Bairro Residência</td>
        <td>Ponto de referência Residência</td>
        <td>Teste aplicado</td>
        <td>Resultado Teste Prático</td>
        <td>Rigger</td>
        <td>Plataforma Movél</td>
        <td>Ponte Rolante</td>
        {{--        --}}
        <td>Encaminhado para Documentos</td>
        <td>Data Encaminhado para Documentos</td>
        <td>Encaminhado para Exame</td>
        <td>Data Encaminhado para Exame</td>
        <td>Encaminhado para treinamento</td>
        <td>Data Encaminhado para Treinamento</td>
        {{--        --}}
        <td>Contrato</td>
        <td>Função</td>
        <td>Cargo</td>
        <td>Salário R$</td>
        <td>Documento</td>
        <td>Documento Portaria</td>
        <td>Tipo de admissão</td>
        <td>Treinamento</td>
        <td>Tipo de Treinamento</td>
        <td>Data Treinamento</td>
        <td>NR 33</td>
        <td>Data NR 33</td>
        <td>NR 35</td>
        <td>Data NR 35</td>
        <td>3260</td>
        <td>Data 3260</td>
        <td>Número Crachá</td>
        <td>Data do ASO</td>
        <td>Status Carteira de Treinamento e Etiqueta</td>
        <td>Status</td>
        <td>Data da Admissão</td>
        <td>Foto</td>
        <td>Quem Admitiu</td>
        <td>Quem Alterou</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{$row->Curriculo->id}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->Curriculo->cpf}}</td>
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
            <td>{{$row->FeedbackCurriculo->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf}}</td>
            <td>{{$row->parecerRh->ex_funcionario ? 'Sim' : 'Não'}}</td>
            <td>{{\App\Models\Curriculo::getTelPrincipal($item->FeedbackCurriculo->curriculo_id)}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->turnos_seis_por_dois ? 'Sim' : 'Não' : 'NÃO INFORMADO'}}</td>
            <td>{{ $row->FeedbackCurriculo->ParecerRh->indicado_por ? $row->FeedbackCurriculo->ParecerRh->indicado_por : null }}</td>
            <td>{{ $row->FeedbackCurriculo->ParecerTecnica ? $row->FeedbackCurriculo->ParecerTecnica->indicado_area : null}}</td>
            <td>{{$row->Curriculo->endereco_completo}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->tem_rota ? 'Sim' : 'Não' : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->qual : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->bairro_rota : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->ponto_referencia_rota : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->pega_onibus ? 'Sim' : 'Não' : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->pega_onibus_qual_ponto : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->bairro_residencia : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->ponto_referencia_residencia : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->qual_teste : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'Aguardando'}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->experiencia_cargas_rigger ? 'Sim' : 'Não' : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->opera_plat_movel ? 'Sim' : 'Não' : 'NÃO INFORMADO'}}</td>
            <td>{{$row->parecerTecnica ? $row->parecerTecnica->opera_plat_ponte ? 'Sim' : 'Não' : 'NÃO INFORMADO'}}</td>

            <td>{{ $row->documentos_entregue ? 'Sim' : 'Não' }}</td>
            <td>{{ $row->documentos_entregue ? (new \MasterTag\DataHora($row->documentos_entregue_data))->dataCompleta() : '' }}</td>
            <td>{{ $row->encaminhado_exame ? 'Sim' : 'Não' }}</td>
            <td>{{ $row->encaminhado_exame ? (new \MasterTag\DataHora($row->encaminhado_exame_data))->dataCompleta() : '' }}</td>
            <td>{{ $row->encaminhado_treinamento ? 'Sim' : 'Não' }}</td>
            <td>{{ $row->encaminhado_treinamento ? (new \MasterTag\DataHora($row->encaminhado_treinamento_data))->dataCompleta(): '' }}</td>

            <td>{{ $row->contrato ? $row->contrato : null }}</td>
            <td>{{ $row->funcao ? $row->funcao : null }}</td>
            <td>{{ $row->cargo ? $row->cargo : null }}</td>
            <td>{{ $row->salario ? $row->salario : null }}</td>
            <td>{{ $row->documento ? $row->documento : null }}</td>
            <td>{{ $row->documento_portaria ? $row->documento_portaria : null }}</td>
            <td>{{ $row->tipo_admissao ? $row->tipo_admissao : null }}</td>
            <td>{{ $row->treinamento ? $row->treinamento : null }}</td>
            <td>{{ $row->tipo_treinamento ? $row->tipo_treinamento : null }}</td>
            <td>{{ $row->data_treinamento ? $row->data_treinamento : null }}</td>
            <td>{{ $row->nr_trinta_tres ? $row->nr_trinta_tres : null }}</td>
            <td>{{ $row->data_nr_trinta_tres ? $row->data_nr_trinta_tres : null }}</td>
            <td>{{ $row->nr_trinta_cinco ? $row->nr_trinta_cinco : null }}</td>
            <td>{{ $row->data_nr_trinta_cinco ? $row->data_nr_trinta_cinco : null }}</td>
            <td>{{ $row->trinta_dois_sessenta ? $row->trinta_dois_sessenta : null }}</td>
            <td>{{ $row->data_trinta_dois_sessenta ? $row->data_trinta_dois_sessenta : null }}</td>
            <td>{{ $row->numero_cracha ? $row->numero_cracha : null }}</td>
            <td>{{ $row->data_aso ? $row->data_aso : null }}</td>
            <td>{{ $row->status_carteira_treinamento ? $row->status_carteira_treinamento : null }}</td>
            <td>{{ $row->status ? $row->status : null }}</td>
            <td>{{ $row->data_admissao ? $row->data_admissao : null }}</td>
            <td>{{ $row->Anexo ? $row->Anexo->count()>0 ? 'Sim' : 'Não' : null }}</td>
            <td>{{ $row->QuemAdmitiu ? $row->QuemAdmitiu->nome : null }}</td>
            <td>{{ $row->QuemAlterou ? $row->QuemAlterou->nome : null }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

