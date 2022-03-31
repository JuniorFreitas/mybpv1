{{--{{$data}}--}}
<table>
    <thead>
    <tr>
        <td>Id</td>
        <td>Nome</td>
        <td>CPF</td>
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
        <td>Fez teste prático</td>
        <td>Data e Hora da Realização</td>
        <td>Responsável pelo Teste</td>
        <td>Qual o Teste foi aplicado</td>
        <td>Parecer Final do Teste</td>
        <td>Parecer Final do Teste Nota</td>
        <td>Entrevistado por</td>
        <td>Encaminhado para Documentos</td>
        <td>Data</td>
        <td>Encaminhado para Exame</td>
        <td>Data</td>
        <td>Encaminhado para Treinamentos</td>
        <td>Data</td>
        <td>Excessão</td>
        <td>Autorizado por</td>
        <td>Usuario Cadastrou</td>
        <td>Responsavel pelo envio</td>
        <td>Observação</td>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>

            <td>{{$row->Curriculo->id}}</td>
            <td>{{$row->Curriculo->nome}}</td>
            <td>{{$row->Curriculo->cpf}}</td>
            <td>{{$row->parecerRh ? $row->parecerRh->destro : ''}}</td>
            <td>{{$row->Curriculo->nascimento}}</td>
            <td>{{$row->Curriculo->idade}}</td>
            <td>{{$row->Curriculo->logradouro}}, {{$row->Curriculo->bairro}}, {{$row->Curriculo->municipio}}
                - {{$row->Curriculo->uf}}</td>
            <td>{{$row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado'}}</td>
            <td>{{$row->Curriculo->email}}</td>
            <td>{{$row->Cliente->cnpj ? $row->Cliente->nome_fantasia : $row->Cliente->nome}}</td>
            <td>{{$row->VagaAberta->VagaSelecionada->nome . ' - ' . $row->VagaAberta->Municipio->uf}}</td>
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
            <td>{{$row->parecerRota ? $row->parecerRota->rota_tipo : ''}}</td>
            <td>{{$row->parecerRota ? $row->parecerRota->quem_entrevistou : ''}}</td>
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
            <td>{{$row->parecerTeste ? $row->parecerTeste->fez_teste ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->fez_teste ? $row->parecerTeste->data_horario_realizacao : '' : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->responsavel_pelo_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->qual_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->parecer_final_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : ''}}</td>
            <td>{{$row->parecerTeste ? $row->parecerTeste->quem_entrevistou : ''}}</td>

            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->documentos_entregue ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->documentos_entregue ? $row->ResultadoIntegrado->documentos_entregue_data : '' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->encaminhado_exame ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->encaminhado_exame ? $row->ResultadoIntegrado->encaminhado_exame_data : '' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->encaminhado_treinamento ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->encaminhado_treinamento ? $row->ResultadoIntegrado->encaminhado_treinamento_data : '' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->excessao ? 'Sim' : 'Não' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->excessao ? $row->ResultadoIntegrado->autorizado_por : '' : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->Usuario->nome : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->responsavel_envio : ''}}</td>
            <td>{{$row->ResultadoIntegrado ? $row->ResultadoIntegrado->obs : ''}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
