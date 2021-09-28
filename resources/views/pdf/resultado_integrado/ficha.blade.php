@extends('layouts.pdf')
@section('title','RESULTADO INTEGRADO')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    @php
        $curriculo = $dados->Curriculo;
        $rh = $dados->ParecerRh;
        $transporte = $dados->parecerRota;
        $tecnica = $dados->parecerTecnica;
        $teste = $dados->parecerTeste;
    @endphp
    <h5 class="text-center">RESULTADO INTEGRADO</h5>

    <div class="h5">
        <fieldset>
            <legend>Dados Gerais</legend>
        </fieldset>
    </div>

    <div class="h5">
        Nome: <span>{{ $curriculo->nome }}</span> <br>
        Data de Nascimento: <span>{{ $curriculo->nascimento }}</span> - <span>{{ $curriculo->idade }} anos</span> <br>
        PCD: <span>{{ $curriculo->pcd ? 'Sim' : 'Não' }}</span>
        @if($curriculo->pcd)
            | CID <span>{{ $curriculo->cid }}</span>
        @endif
        <br>
        Disponibilidade para Viajar: <span>{{ $curriculo->viajar ? 'Sim' : 'Não' }}</span> <br>
        Escolaridade:
        <span>{{ $curriculo->Formacao->tipo }} {{$curriculo->formacao_curso ? "($curriculo->formacao_curso)" : null}} </span>
        <br>
        Endereço: <span>{{ $curriculo->endereco_completo }}</span><br>
        Contato:
        <span>{{ $dados->TelPrincipal ? $dados->TelPrincipal->Formatado() : 'não informado' }}</span>
        |
        E-mail: <span>{{ $curriculo->email }}</span>
        <br/>
        Cliente:
        <span>{{ $dados->Cliente->tipo == \App\Models\Cliente::TIPO_PESSOA_JURIDICA ? $dados->Cliente->razao_social : $dados->Cliente->nome}}</span>
        <br> Vaga:<span> {{ $dados->VagaSelecionada->nome }}</span> |
        UF Vaga: <span>{{ $curriculo->uf_vaga }}</span> | Ex Funcionário:<span> {{ $rh->ex_funcionario }}</span>
    </div>

    <h5 class="titulo">PARECER RH</h5>

    <div class="h5">
        Rota/Bairro: <span>{{ $rh->rota_bairro }}</span> <br>
        <fieldset>
            <legend>EPI Pontuação</legend>
        </fieldset>
        Camisa de Meia: <span>{{ $rh->camisa_meia }}</span> |
        Camisa Proteção: <span>{{ $rh->camisa_protecao }}</span> |
        Calça: <span>{{ $rh->calca }}</span> <br>
        <fieldset>
            <legend>Histórico Familiar e Social</legend>
        </fieldset>
        Mora com quem: <span>{{ $rh->mora_com_quem }}</span> <br>
        Casado: <span>{{ $rh->casado ? 'Sim' : 'Não' }}</span>
        @if($rh->casado)
            | Tempo de convivência: <span>{{ $rh->tempodeconvivencia }}</span>
            <br>
            Esposa ou Marido Trabalha? <span>{{ $rh->conjuge_trabalha ? 'Sim' : 'Não' }}</span>
            @if($rh->conjuge_trabalha)
                | Em quê? <span>{{ $rh->trabalho_conjuge }}</span>
            @endif
        @endif
        <br> Filho(s): <span>{{ $rh->filhos ? 'Sim' : 'Não' }}</span>

        @if($rh->filhos)
            | Quantos : <span>{{ $rh->qnt_filhos }}</span>
        @endif
        <br>
        Fuma : <span>{{ $rh->fuma  ? 'Sim' : 'Não' }}</span>
        @if ($rh->fuma )
            | Frequência <span>{{ $rh->frequencia_fuma }}</span>
        @endif
        <br>
        Bebe : <span>{{ $rh->bebe  ? 'Sim' : 'Não' }}</span>
        @if ($rh->bebe )
            | Frequência <span>{{ $rh->frequencia_bebe }}</span>
        @endif
        <br>
        Indicado por alguém : <span>{{ $rh->indicacao  ? 'Sim' : 'Não' }}</span>
        @if ($rh->indicacao )
            | Quem: <span>{{ $rh->indicacao }}</span>
        @endif
        <br>
        <fieldset>
            <legend>Outras informações</legend>
        </fieldset>

        Experiência na ALUMAR: <span>{{ $rh->alumar_experiencia ? 'Sim' : 'Não' }}</span>
        @if ($rh->alumar_experiencia )
            | Qual área: <span>{{ $rh->alumar_experiencia_area }}</span>
        @endif
        <br>
        Experiência em outra industria: <span>{{ $rh->outra_industria_experiencia ? 'Sim' : 'Não' }}</span>
        @if ($rh->outra_industria_experiencia )
            | Qual: <span>{{ $rh->outra_industria_nome }}</span>
        @endif
        <br>
        Grau de instrução: <span>{{ $rh->grau_instrucao }}</span>
        <br>
        Disponibilidade de hora extra: <span>{{ $rh->horaextra ? 'Sim' : 'Não' }}</span>
        | Disponibilidade para turnos 6X2: <span>{{ $rh->turnos_seis_por_dois ? 'Sim' : 'Não' }}</span>
        | Disponibilidade para noturno: <span>{{ $rh->noturno ? 'Sim' : 'Não' }}</span>
        <br>
        Acidente de trabalho anterior: <span>{{ $rh->acidente_trabalho ? 'Sim' : 'Não' }}</span>
        @if ($rh->acidente_trabalho )
            | Qual: <span>{{ $rh->acidente_trabalho_qual }}</span>
        @endif
        <br>
        Afastamento INSS anterior: <span>{{ $rh->afastamento_inss ? 'Sim' : 'Não' }}</span>
        @if ($rh->afastamento_inss )
            | Qual: <span>{{ $rh->afastamento_inss_qual }}</span>
        @endif
        <br>
        Especifique situações de saúde: <span>{{ $rh->situacao_saude }}</span>
        <br>
        Certificado NR 10: <span>{{ $rh->nr_dez }}</span>
        @if ($rh->nr_dez == 'sim')
            <br>
            <fieldset>
                <legend>Informações Certificado NR10</legend>
            </fieldset>
            @foreach($rh->Nr as $item)
                Instituição: <span>{{ $item->instituicao }}</span> <br>
                Data de Emissão: <span>{{ \MasterTag\DataHora::dataFormatada($item->nr_dez_emissao) }}</span> <br>
                Data de Validade: <span>{{ \MasterTag\DataHora::dataFormatada($item->nr_dez_validade) }}</span> <br>
                <hr>
            @endforeach
        @endif
        <br>
        <fieldset>
            <legend>Cursos de Formação</legend>
        </fieldset>
        @if($dados->CursosFormacao)
            @foreach($dados->CursosFormacao as $item)
                Curso: <span>{{ $item->curso }}</span> <br>
                Instituição: <span>{{ $item->instituicao }}</span> <br>
                Data de Emissão: <span>{{ \MasterTag\DataHora::dataFormatada($item->emissao) }}</span> <br>
                Data de Validade: <span>{{ \MasterTag\DataHora::dataFormatada($item->validade) }}</span> <br>
                <hr>
            @endforeach
        @else
            Nenhum Curso Adicionado
        @endif

        {{--        @forelse($dados->CursosFormacao as $item)--}}

        {{--        @empty--}}
        {{--           --}}
        {{--        @endforelse--}}

        <br>
        <div style="height: 1px; border-top: 1px solid #333; width: 100%; margin-top: 3px; margin-bottom: 3px;"></div>
        Comportamento Seguro: <span>{{ $rh->comportamento_seguro  }}</span> |
        Energia para o trabalho: <span>{{ $rh->energia_para_trabalho  }}</span> |
        Postura: <span>{{ $rh->postura  }}</span>
        <br>
        <fieldset>
            <legend>Histórico Profissional</legend>
        </fieldset>
        Quais as suas últimas experiências? Nome das empresas, cargos ocupados, tempo de permanência nas funções e os
        motivos de saída:
        <br>
        <span>{{ $rh->historico_profissional  }}</span>
        <br>
        <fieldset>
            <legend>Histórico Educacional</legend>
        </fieldset>
        Fale-me sobre sua formação educacional e cursos: <br>
        <span>{{ $rh->historico_educacional  }}</span>
        <br>
        <fieldset>
            <legend>Parecer Final RH</legend>
        </fieldset>
        Parecer: <span>{{ $rh->parecer_final }}</span> | Resultado: <span>{{ $rh->parecer_final_um }}</span> | Nota:
        <span>{{ $rh->nota }}</span>
        <br>
        Entrevistado por <span>{{ $rh->quem_entrevistou }}</span> <br>
        Comentários: <span>{{ $rh->comentarios }}</span> <br>
        @php
            $dataEntrevista = new \MasterTag\DataHora($rh->created_at);
        @endphp
        Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>
    </div>

    <h5 class="titulo">PARECER ROTA TRANSPORTE</h5>

    @if ($transporte)
        <div class="h5">
            Tem Rota que atende:
            <span>{{ $transporte ? $transporte->tem_rota ? 'Sim' : 'Não' : 'Não Informado' }}</span>
            @if($transporte->tem_rota)
                | Qual: <span>{{ $transporte->qual }}</span>
            @endif
            <br>
            Bairro / Rota: <span>{{ $transporte->bairro_rota }}</span> <br>
            Ponto de referência Rota: <span>{{ $transporte->ponto_referencia_rota }}</span> <br>
            Informado sobre ponto de referência: <span>{{ $transporte->pega_onibus ? 'Sim' : 'Não' }}</span>
            @if($transporte->pega_onibus)
                | Qual: <span>{{ $transporte->pega_onibus_qual_ponto }}</span>
            @endif
            <br>
            Bairro Residência: <span>{{ $transporte->bairro_residencia }}</span><br>
            Ponto de referência Residência: <span>{{ $transporte->ponto_referencia_residencia }}</span><br>
            Autorizado Vale Transporte: <span>{{ $transporte->vale_transporte ? 'Sim' : 'Não' }}</span><br>
            <fieldset>
                <legend>Rota Disponivel para qual turno</legend>
            </fieldset>
            Turno A: <span>{{ $transporte->rota_disponivel_turno_a ? 'Sim' : 'Não' }}</span> |
            Turno B: <span>{{ $transporte->rota_disponivel_turno_b ? 'Sim' : 'Não' }}</span> |
            Turno C: <span>{{ $transporte->rota_disponivel_turno_c ? 'Sim' : 'Não' }}</span>
            @if ($transporte->rota_disponivel_turno_o)
                | Outros: <span>{{ $transporte->rota_disponivel_turno_o ? 'Sim' : 'Não' }}</span>
                | Quais: <span>{{ $transporte->rota_disponivel_outros }}</span>
            @endif
            <br>
            Observação: <span>{{ $transporte->observacao }}</span>
            <br>
            <fieldset>
                <legend>Parecer Final Transporte</legend>
            </fieldset>
            Rota Atende: <span>{{ $transporte->rota_atende ? 'Sim' : 'Não' }}</span><br>
            Tipo de Contratação: {{ $transporte->rota_tipo }} <br>
            Entrevistado por {{ $transporte->quem_entrevistou }}<br>
            @php
                $dataEntrevista = new \MasterTag\DataHora($transporte->created_at);
            @endphp
            Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>
        </div>
    @else
        <h4>NÃO INFORMADO</h4>
    @endif


    <h5 class="titulo">PARECER ENTREVISTA TÉCNICA</h5>

    @if ($tecnica)
        <div class="h5">

            Tempo na função: <span>{{ $tecnica->tempo_funcao }}</span><br>
            Já trabalhou na ALUMAR: <span>{{ $tecnica->trabalhou_alumar ? 'Sim' : 'Não' }}</span><br>
            Tem Rota: <span>{{ $tecnica->rota ? 'Sim' : 'Não' }}</span><br>
            Disponibilidade para turno: <span>{{ $tecnica->turno ? 'Sim' : 'Não' }}</span><br>
            Indicado: <span>{{ $tecnica->indicado ? 'Sim' : 'Não' }}</span>
            @if($tecnica->indicado)
                | Indicado por: <span>{{ $tecnica->indicado_por }}</span>
            @endif
            <br>
            Conhece e prática as normas de SSMA: <span>{{ $tecnica->ssma ? 'Sim' : 'Não' }}</span>
            @if($tecnica->ssma)
                | Expecifique: <span>{{ $tecnica->ssma_ex }}</span>
            @endif
            <br>
            Já trabalhou como mecânico de manutenção:
            <span>{{ $tecnica->trabalhou_mecanico_manutencao ? 'Sim' : 'Não' }}</span>
            @if($tecnica->trabalhou_mecanico_manutencao)
                | Expecifique: <span>{{ $tecnica->trabalhou_mecanico_manutencao_ex }}</span>
            @endif
            <br>
            Já inverteu raquete com produto químico dentro:
            <span>{{ $tecnica->trabalhou_raquete_produto_quimico ? 'Sim' : 'Não' }}</span>
            @if($tecnica->trabalhou_raquete_produto_quimico)
                | Expecifique: <span>{{ $tecnica->trabalhou_raquete_produto_quimico_ex }}</span>
            @endif
            <br>
            Quais os tipos de talha: <span>{{ $tecnica->tipos_de_talha }}</span>
            <br>
            Sabe como iniciar a abertura e fechamento Flange com uso de Pneutorque ou chave de bater:
            <span>{{ $tecnica->fechamento_flange ? 'Sim' : 'Não' }}</span>
            @if($tecnica->fechamento_flange)
                | Expecifique: <span>{{ $tecnica->fechamento_flange_ex }}</span>
            @endif
            <br>
            Quantos milimetros tem uma polegada: <span>{{ $tecnica->milimetros_polegada }}</span>
            <br>
            Tem experiência com manuseio de maçarico: <span>{{ $tecnica->manuseio_macarico ? 'Sim' : 'Não' }}</span>
            @if($tecnica->manuseio_macarico)
                | Expecifique: <span>{{ $tecnica->manuseio_macarico_ex }}</span>
            @endif
            <br>
            Já trocou valvulas ou tubulação com uso de talha corrente ou catraca:
            <span>{{ $tecnica->trocou_valvulas ? 'Sim' : 'Não' }}</span>
            @if($tecnica->trocou_valvulas)
                | Expecifique: <span>{{ $tecnica->trocou_valvulas_ex }}</span>
            @endif
            <br>
            Quais as ferramentas utilizadas para elevação de carga:
            <span>{{ $tecnica->ferramentas_elevacao_carga }}</span>
            <br>
            Opera Plataforma Móvel: <span>{{ $tecnica->opera_plat_movel ? 'Sim' : 'Não' }}</span>
            @if($tecnica->opera_plat_movel)
                | Expecifique: <span>{{ $tecnica->opera_plat_movel_ex }}</span>
            @endif
            <br>
            Opera Ponte Rolante: <span>{{ $tecnica->opera_plat_ponte ? 'Sim' : 'Não' }}</span>
            @if($tecnica->opera_plat_ponte)
                | Expecifique: <span>{{ $tecnica->opera_plat_onte_ex }}</span>
            @endif
            <br>
            Tem experiência com movimentação de Cargas/Rigger:
            <span>{{ $tecnica->experiencia_cargas_rigger ? 'Sim' : 'Não' }}</span>
            @if($tecnica->experiencia_cargas_rigger)
                | Expecifique: <span>{{ $tecnica->experiencia_cargas_rigger_ex }}</span>
            @endif
            <br>
            Já trabalhou em Overhaul antes: <span>{{ $tecnica->trabalhou_overhaul ? 'Sim' : 'Não' }}</span>
            @if($tecnica->trabalhou_overhaul)
                | Expecifique: <span>{{ $tecnica->trabalhou_overhaul_ex }}</span>
            @endif
            <br>
            <fieldset>
                <legend>Parecer Final</legend>
            </fieldset>
            Resultado Final: <span>{{ $tecnica->resultado_final }}</span> | Nota <span>{{ $tecnica->nota }}</span> <br>
            Entrevistado por <span>{{ $tecnica->quem_entrevistou }}</span> <br>
            @php
                $dataTecnica = new \MasterTag\DataHora($tecnica->created_at);
            @endphp
            Data da Entrevista: <span>{{ $dataTecnica->dataCompleta() }} às {{ $dataTecnica->hora() }}:{{ $dataTecnica->minuto() }}h</span>

        </div>
    @else
        <h4>NÃO INFORMADO</h4>
    @endif

    <h5 class="titulo">PARECER TESTE PRÁTICO</h5>

    @if ($teste)
        <div class="h5">
            Fez teste prático: <span>{{ $teste->fez_teste }}</span>
            @if($teste->fez_teste)
                <br>
                Data e Hora da Realização:
                <span>{{ \MasterTag\DataHora::dataFormatada($teste->data_horario_realizacao) }}</span><br>
                Responsável pelo Teste: <span>{{ $teste->responsavel_pelo_teste }}</span><br>
                Qual o Teste foi aplicado: <span>{{ $teste->qual_teste }}</span><br>
                Qual o Resultado do teste: <span>{{ $teste->resultado_teste }}</span><br>
            @endif
            <br>
            <fieldset>
                <legend>Parecer Final do Teste Prático</legend>
            </fieldset>
            Parecer do Teste: <span>{{ $teste->parecer_final_teste }}</span> |
            Nota: <span>{{ $teste->nota_teste == 0 ? 'Não se Aplica' : $teste->nota_teste }}</span><br>
            Entrevistado por <span>{{ $teste->quem_entrevistou }}</span> <br>
            @php
                $dataEntrevista = new \MasterTag\DataHora($teste->created_at);
            @endphp
            Data da Entrevista: <span>{{ $dataEntrevista->dataCompleta() }} às {{ $dataEntrevista->hora() }}:{{ $dataEntrevista->minuto() }}h</span>

        </div>
    @else
        <h4>NÃO INFORMADO</h4>
    @endif


    <h5 class="titulo">ENCAMINHAMENTO PARA ADMISSÃO</h5>

    <div class="h5">

        Encaminhado para Documentos: <span>{{$dados->documentos_entregue ? 'Sim' : 'Não'}}</span>
        @if ($dados->documentos_entregue)
            | Data do Encaminhamento:
            <span>{{ \MasterTag\DataHora::dataFormatada($dados->documentos_entregue_data) }}</span>
        @endif
        <br>
        Encaminhado para Exame: <span>{{$dados->encaminhado_exame ? 'Sim' : 'Não'}}</span>
        @if ($dados->encaminhado_exame)
            | Data do Encaminhamento:
            <span>{{ \MasterTag\DataHora::dataFormatada($dados->encaminhado_exame_data) }}</span>
        @endif
        <br>
        Encaminhado para Treinamento: <span>{{$dados->encaminhado_treinamento ? 'Sim' : 'Não'}}</span>
        @if ($dados->encaminhado_treinamento)
            | Data do Encaminhamento:
            <span>{{ \MasterTag\DataHora::dataFormatada($dados->encaminhado_treinamento_data) }}</span>
        @endif
        <br>
        @if ($dados->excessao)
            Exceção: <span>{{$dados->excessao ? 'Sim' : 'Não'}}</span> |
            Autorizado por <span>{{ $dados->autorizado_por }}</span><br>
        @endif
        Responsável pelo envio: <span>{{$dados->responsavel_envio}}</span> <br>
        Obs.: <span>{{$dados->obs}}</span>

        <br>
        <br>
        Data da Emissão da ficha:
        <span>{{ (new \MasterTag\DataHora())->dataCompleta()}} às {{ (new \MasterTag\DataHora())->horaCompleta()}}</span><br>
        Usuário que emitiu a ficha: <span>{{ auth()->user()->nome }}</span>
    </div>
@endsection
