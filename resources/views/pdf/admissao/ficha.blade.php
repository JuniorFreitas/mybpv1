@extends('layouts.pdf')
@section('title','Ficha de Admissão')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <h5 class="text-center">FICHA DE ADMISSÃO</h5>
    <h5 style="margin-top: 5px; margin-bottom: 5px;">INFORMAÇÕES:</h5>


    <table width="100%" style="border: 1px solid #666666; padding: 8px 17px 15px">
        <tr>
            <td>
                <p style="line-height: 17pt; font-size: 8.5pt;">

                    Nome: <strong>{{ $dados->Curriculo->nome }}</strong>

                    - <strong>{{ $dados->Curriculo->idade }} anos</strong>
                    | PCD: <strong>{{ $dados->Curriculo->PCD ? 'Sim' : 'Não' }}</strong>
                    | CNH: <strong>{{ $dados->ParecerRh->cnh ? $dados->ParecerRh->cnh_tipo : 'Não possui' }}</strong>
                    <br>
                    RG: <strong>{{ $dados->Curriculo->rg ?? 'Não informado' }}</strong>
                    | RG Data Emissão:
                    <strong>{{ $dados->Curriculo->rg_data_emissao ?? 'Não informado' }}</strong>
                    | Naturalidade:
                    <strong>{{ $dados->Curriculo->naturalidade ?? 'Não informado' }}</strong>
                    <br>
                    Calça: <strong>{{ $dados->ParecerRh->calca }}</strong> |
                    Bota: <strong>{{ $dados->ParecerRh->bota }}</strong> | C.
                    Meia: <strong>{{ $dados->ParecerRh->camisa_meia }}</strong> | C.
                    Proteção: <strong>{{ $dados->ParecerRh->camisa_protecao }}</strong>
                    <br> Vaga: <strong>{{ $dados->VagaAberta->VagaSelecionada->nome  . ' - ' . $dados->VagaAberta->Municipio->uf}}</strong>
                    <br>
                    Contato:
                    <strong>{{ $dados->TelPrincipal ? $dados->TelPrincipal->numero: 'Não informado' }}</strong>
                    | E-mail: <span
                        style="text-transform: lowercase"><strong>{{ $dados->Curriculo->email }}</strong></span> |
                    Ex Funcionário:
                    <strong>{{ $dados->ParecerRh->ex_funcionario == true ? 'Sim' : 'Não' }}</strong>
                    <br>
                    Disponibilidade para turnos 6X2:
                    <strong>{{ $dados->ParecerRh->turnos_seis_por_dois ? 'Sim' : 'Não' }}</strong> |
                    @if ($dados->ParecerRh->indicado)
                        Indicado por:
                        <strong>{{ $dados->ParecerRh->indicado_por}}</strong>
                        |
                    @endif
                    Indicado para qual área:
                    <strong>{{ $dados->ParecerTecnica->indicado_area ?? 'Não informado' }}</strong>
                    <br>
                    Endereço: <strong>{{ $dados->Curriculo->logradouro }}, {{ $dados->Curriculo->bairro }}
                        , {{ $dados->Curriculo->municipio }}/{{ $dados->Curriculo->uf }}</strong>
                    <br>
                    Bairro Rota:
                    <strong>{{ $dados->ParecerRota ? $dados->ParecerRota->bairro_rota : 'Não Informado' }}</strong>
                    | Ponto
                    Referência
                    Rota:
                    <strong>{{ $dados->ParecerRota ? $dados->ParecerRota->ponto_referencia_rota : 'Não Informado' }}</strong>
                    | Ponto
                    Referência
                    Bairro:
                    <strong>{{ $dados->ParecerRota ? $dados->ParecerRota->ponto_referencia_residencia : 'Não Informado' }}</strong>
                    <br>
                    Teste aplicado:
                    <strong>{{ $dados->ParecerTeste && $dados->ParecerTeste->parecer_final_teste ?: 'Não Informado' }}</strong>
                    | Resultado Teste Prático:
                    <strong>{{ $dados->ParecerTeste && $dados->ParecerTeste->parecer_final_teste ?: 'Não Informado' }}</strong>
                    <br>
                    Rigger:
                    <strong>{{ $dados->ParecerTeste && $dados->ParecerTeste->experiencia_cargas_rigger ?: 'Não Informado' }}</strong>
                    |
                    Plataforma Movél:
                    <strong>{{ $dados->ParecerTeste && $dados->ParecerTeste->opera_plat_movel ?: 'Não Informado' }}</strong>
                    |
                    Ponte Rolante:
                    <strong>{{ $dados->ParecerTeste && $dados->ParecerTeste->opera_plat_ponte ?: 'Não Informado' }}</strong>
                </p>
            </td>
            <td width="4.5cm" style="border-left: 1px solid #666666;" align="center">

                @if (count($dados->Curriculo->FotoTres)>0)
                    <img
                        src="{{\App\Models\Sistema::convertBase('app/g/arquivos/disco-fotocurriculo/'.$dados->Curriculo->FotoTres[0]->file)}}"
                        style="height: 4cm; ">
                @else
                    <img
                        src="data:image/jpeg;base64,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"
                        style="height: 4cm; ">
                @endif
            </td>
        </tr>
    </table>

    <br>
    <h5 style="margin-top: 5px; margin-bottom: 5px;">ADMISSÃO:</h5>
    {{--    <hr style="border: transparent; background: #666666; height: 1px;margin-bottom: 5px;">--}}

    <table style="border: 1px solid #666666; padding: 8px 17px 15px" width="100%">
        <tr>
            <td>
                <p style="line-height: 15pt; font-size: 8.5pt;">
                    Contrato: <strong>{{ $dados->Admissao->contrato }}</strong> <br>
                    Função: <strong>{{ $dados->Admissao->funcao }}</strong> <br>
                    Cargo: <strong>{{ $dados->Admissao->cargo }}</strong> <br>
                    Salário R$: <strong>{{ $dados->Admissao->salario }}</strong> <br>
                    Documento: <strong>{{ $dados->Admissao->documento }}</strong> <br>
                    Documento Portaria: <strong>{{ $dados->Admissao->documento_portaria }}</strong> <br>
                    Tipo de admissão: <strong>{{ $dados->Admissao->tipo_admissao }}</strong> <br>
                    Treinamento: <strong>{{ $dados->Admissao->treinamento }}</strong> <br>
                    Tipo de Treinamento: <strong>{{ $dados->Admissao->tipo_treinamento }}</strong> <br>
                    Data Treinamento: <strong>{{ $dados->Admissao->data_treinamento }}</strong> <br>
                    NR 33: <strong>{{ $dados->Admissao->nr_trinta_tres }}</strong> <br>
                    Data NR 33: <strong>{{ $dados->Admissao->data_nr_trinta_tres }}</strong> <br>
                    NR 35: <strong>{{ $dados->Admissao->nr_trinta_cinco }}</strong> <br>
                    Data NR 35: <strong>{{ $dados->Admissao->data_nr_trinta_cinco }}</strong> <br>
                    3260: <strong>{{ $dados->Admissao->trinta_dois_sessenta }}</strong> <br>
                    Data 3260: <strong>{{ $dados->Admissao->data_trinta_dois_sessenta }}</strong> <br>
                    Número Crachá: <strong>{{ $dados->Admissao->numero_cracha }}</strong> <br>
                    Data do ASO: <strong>{{ $dados->Admissao->data_aso }}</strong> <br>
                    CTPS:
                    <strong>{{ $dados->Admissao->DadosAdmissoes ? $dados->Admissao->DadosAdmissoes->ctps_numero :'Não Informado'}}</strong>
                    | CTPS Série:
                    <strong>{{ $dados->Admissao->DadosAdmissoes ? $dados->Admissao->DadosAdmissoes->ctps_serie :'Não Informado'}}</strong>
                    | CTPS Data Emissão:
                    <strong>{{ $dados->Admissao->DadosAdmissoes ? $dados->Admissao->DadosAdmissoes->ctps_data_emissao :'Não Informado'}}</strong>
                    <br>
                    Título de Eleitor:
                    <strong>{{ $dados->Admissao->DadosAdmissoes ? $dados->Admissao->DadosAdmissoes->titulo_eleitor_numero :'Não Informado'}}</strong>
                    | Título de Eleitor Sessão:
                    <strong>{{ $dados->Admissao->DadosAdmissoes ? $dados->Admissao->DadosAdmissoes->titulo_eleitor_sessao :'Não Informado'}}</strong>
                    | Título de Eleitor Zona:
                    <strong>{{ $dados->Admissao->DadosAdmissoes ? $dados->Admissao->DadosAdmissoes->titulo_eleitor_zona :'Não Informado'}}</strong>
                    <br>
                    Status Carteira de Treinamento e Etiqueta:
                    <strong>{{ $dados->Admissao->status_carteira_treinamento }}</strong> <br>
                    Status: <strong>{{ $dados->Admissao->status }}</strong> <br>
                    Data da Admissão: <strong>{{ $dados->Admissao->data_admissao }}</strong> <br>
                </p>
            </td>
        </tr>
    </table>
    <br>
    <p style="font-size: 9pt; color: #666666">Data da Emissão da ficha: {{ (new \MasterTag\DataHora())->dataCompleta()}}
        às {{ (new \MasterTag\DataHora())->horaCompleta()}}</p>
    <p style="font-size: 9pt; color: #666666">Usuario que emitou a
        ficha: {{ \Illuminate\Support\Facades\Auth::user()->nome }}</p>

@endsection
