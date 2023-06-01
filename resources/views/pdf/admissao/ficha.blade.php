@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')

        <h2 class="text-center">FICHA DO EMPREGADO</h2>

        <table class="table_ficha">
            <tr>
                <th colspan="4">DADOS PESSOAIS</th>
                <th>Foto 3x4</th>
            </tr>
            <tr>
                <td style="width: 187px">
                    Nome Completo:<br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->nome }}</strong>
                </td>
                <td>
                    Data de Nascimento: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->nascimento }}
                        ({{ $dados['dados_colaborador']->Curriculo->idade }} anos)</strong>
                </td>
                <td>
                    Sexo: <br> <strong>{{ $dados['dados_colaborador']->Curriculo->sexo ?? 'Não informado' }}</strong>
                </td>
                <td>
                    Estado Civil: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->estado_civil ?? 'Não informado' }}</strong>

                </td>
                <td rowspan="4" style="width: 3cm; height: 4cm">
                    @if (count($dados['dados_colaborador']->Curriculo->FotoTres)>0)
                        <img
                            src="{{env('AWS_URL')}}/arquivos/{{  $dados['dados_colaborador']->Curriculo->FotoTres[0]->disco }}/{{ $dados['dados_colaborador']->Curriculo->FotoTres[0]->thumb }}"
                            style="height: 4cm; ">
                    @else
                        <img
                            src="data:image/jpeg;base64,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"
                            style="height: 4cm; ">
                    @endif
                </td>
            </tr>
            <tr>
                <td>E-mail: <br><span
                        style="text-transform: lowercase"><strong>{{ $dados['dados_colaborador']->Curriculo->email }}</strong></span>
                </td>
                <td>Contato: <br>
                    <strong>{{ \App\Models\Curriculo::getTelPrincipal($dados['dados_colaborador']->curriculo_id) ?? 'Não informado' }}</strong>
                </td>
                <td>PCD: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->pcd ? 'Sim ' : 'Não' }}
                        @if ($dados['dados_colaborador']->Curriculo->pcd)
                            ({{ $dados['dados_colaborador']->Curriculo->cid }})
                        @endif
                    </strong>
                </td>
                <td>
                    Naturalidade: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->naturalidade ?? 'Não informado' }}</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Mãe: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->filiacao_mae ?? 'Não informado' }}</strong>
                </td>
                <td colspan="2">
                    Pai: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->filiacao_pai ?? 'Não informado' }}</strong>
                </td>
                <td>Ex Funcionário: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRh->ex_funcionario == true ? 'Sim' : 'Não' }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr>
                <th colspan="4">ENDEREÇO</th>
                <th></th>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center">
                    <strong>{{ $dados['dados_colaborador']->Curriculo->endereco_completo }}</strong></td>
            </tr>

            @if($dados['dados_colaborador']->Curriculo->Dependentes && $dados['dados_colaborador']->Curriculo->Dependentes->count() > 0)
                <tr>
                    <th colspan="4">DEPENDENTES</th>
                    <th></th>
                </tr>
                @foreach($dados['dados_colaborador']->Curriculo->Dependentes as $dependentes)
                    <tr>
                        <td>
                            Tipo: <br>
                            <strong>{{ $dependentes->tipo == 'outro' ? $dependentes->outro_tipo : \App\Models\UsuarioDependente::TIPOS_DEPENDENTES[$dependentes->tipo] }}</strong>
                        </td>
                        <td colspan="2">
                            Nome: <br>
                            <strong>{{ $dependentes->nome}}</strong>
                        </td>
                        <td>
                            CPF: <br>
                            <strong>{{ $dependentes->cpf ?? 'Não informado'}}</strong>
                        </td>
                        <td>
                            Data de Nascimento: <br> <strong>{{ $dependentes->nascimento ?? 'Não informado'}}</strong>
                        </td>
                    </tr>
                @endforeach
            @endif

            <tr>
                <th colspan="4">DADOS EPI</th>
                <th></th>
            </tr>
            <tr>
                <td>Calça: <br> <strong>{{ $dados['dados_colaborador']->ParecerRh->calca ?? 'Não informado' }}</strong>
                </td>
                <td>C. Meia: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRh->camisa_meia ?? 'Não informado' }}</strong></td>
                <td>Bota: <br> <strong>{{ $dados['dados_colaborador']->ParecerRh->bota ?? 'Não informado'   }}</strong>
                </td>
                <td>C. Proteção: <br> <strong>{{ $dados['dados_colaborador']->ParecerRh->camisa_protecao }}</strong>
                </td>
                <td></td>
            </tr>
            <tr>
                <th colspan="4">OUTRAS INFORMAÇÕES</th>
                <th></th>
            </tr>
            <tr>
                <td>
                    Turnos 6x2: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRh->turnos_seis_por_dois ? 'Sim' : 'Não' }}</strong>
                </td>
                <td>
                    Indicado: <br>
                    {{ $dados['dados_colaborador']->ParecerRh->indicado ? 'Sim' : 'Não'}}
                    @if ($dados['dados_colaborador']->ParecerRh->indicado)
                        <br>Por: <strong>{{ $dados['dados_colaborador']->ParecerRh->indicado_por}}</strong>
                    @endif
                </td>
                <td>Indicado área: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerTecnica->indicado_area ?? 'Não informado' }}</strong>
                </td>
                <td>
                    Bairro Rota: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRota ? $dados['dados_colaborador']->ParecerRota->bairro_rota : 'Não Informado' }}</strong>
                </td>
                <td>
                    Ponto Ref. Rota: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRota ? $dados['dados_colaborador']->ParecerRota->ponto_referencia_rota : 'Não Informado' }}</strong>
                </td>
            </tr>

            <tr>
                <td>Ponto Ref. Bairro: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRota ? $dados['dados_colaborador']->ParecerRota->ponto_referencia_residencia : 'Não Informado' }}</strong>
                </td>
                <td>
                    Teste aplicado: <br>
                    <strong>{{$dados['dados_colaborador']->ParecerTeste && $dados['dados_colaborador']->ParecerTeste->parecer_final_teste ?: 'Não Informado' }}</strong>
                </td>
                <td>
                    Resultado Teste Prático: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerTeste && $dados['dados_colaborador']->ParecerTeste->parecer_final_teste ?: 'Não Informado' }}</strong>
                </td>
                <td>
                    Rigger: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerTeste && $dados['dados_colaborador']->ParecerTeste->experiencia_cargas_rigger ?: 'Não Informado' }}</strong>
                </td>
                <td>
                    Plataforma Movél: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerTeste && $dados['dados_colaborador']->ParecerTeste->opera_plat_movel ?: 'Não Informado' }}</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Ponte Rolante: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerTeste && $dados['dados_colaborador']->ParecerTeste->opera_plat_ponte ?: 'Não Informado' }}</strong>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <table class="table_ficha" style="margin-top: -33px">
            <tr>
                <th colspan="6">DADOS DE ADMISSÃO</th>
            </tr>
            <tr>
                <td>
                    Centro de Custo: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->CentroCusto->label ?? 'Não Informado' }}</strong>
                </td>
                <td>
                    Área: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->AreaEtiqueta->label ?? 'Não Informado' }}</strong>
                </td>
                <td>
                    Função: <br> <strong>{{ $dados['dados_colaborador']->Admissao->funcao ?? 'Não informado' }}</strong>
                </td>
                <td>
                    Cargo: <br> <strong>{{ $dados['dados_colaborador']->Admissao->cargo ?? 'Não informado' }}</strong>
                </td>
                <td>
                    Salário R$: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->salario ?? 'Não informado'}}</strong>
                </td>
                <td>
                    Documento: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->documento ?? 'Não informado'}}</strong>
                </td>
            </tr>

            <tr>
                <td>
                    Documento Portaria: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->documento_portaria ?? 'Não informado' }}</strong>
                </td>
                <td>
                    Tipo de admissão: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->tipo_admissao ?? 'Não informado' }}</strong>
                </td>
                <td>Treinamento: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->treinamento ?? 'Não informado' }}</strong></td>
                <td>Tipo de Treinamento: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->tipo_treinamento ?? 'Não informado' }}</strong>
                </td>
                <td>Data Treinamento: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->data_treinamento ?? 'Não informado' }}</strong>
                </td>
                <td>Número Crachá: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->numero_cracha ?? 'Não informado' }}</strong></td>
            </tr>
            <tr>
                <td>Data do ASO: <br>
                    <strong>{{ $dados['dados_colaborador']->UltimoAso->data_realizacao ?? 'Não informado' }}</strong>
                </td>
                <td>Data da Admissão: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->data_admissao ?? 'Não Informado'}}</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <th colspan="6">DOCUMENTOS</th>
            </tr>
            <tr>
                <td>CPF: <br> <strong>{{ $dados['dados_colaborador']->Curriculo->cpf ?? 'Não informado' }}</strong></td>
                <td>RG: <br> <strong>{{ $dados['dados_colaborador']->Curriculo->rg ?? 'Não informado' }}</strong></td>
                <td>Orgão Emissor: <br>
                    <strong>{{ $dados['dados_colaborador']->Curriculo->orgao_emissor ?? 'Não informado' }}</strong>
                </td>
                <td>
                    PIS: <br> <strong>{{$dados['dados_colaborador']->Admissao->pis ?? 'Não informado'}}</strong>
                </td>
                <td>
                    CTPS: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    CTPS Série: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_serie ?? 'Não Informado'}}</strong>
                </td>

            </tr>

            <tr>
                <td>
                    CTPS UF: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_uf ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    Título de Eleitor: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->titulo_eleitor_numero ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    Título Sessão: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->titulo_eleitor_sessao ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    Título Zona: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->titulo_eleitor_zona ?? 'Não Informado'}}</strong>
                </td>
                <td> Cert. Reservista Nº: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->cert_reservista_num ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    Cert. Reservista Cat: <br>
                    <strong>{{ $dados['dados_colaborador']->Admissao->DadosAdmissoes->cert_reservista_categoria ?? 'Não Informado'}}</strong>
                </td>
            </tr>
            <tr>
                <td>
                    CNH: <br>
                    <strong>{{ $dados['dados_colaborador']->ParecerRh->cnh ? $dados['dados_colaborador']->ParecerRh->cnh_tipo : 'Não possui' }}</strong>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <th colspan="6">DADOS BANCÁRIOS</th>
            </tr>

            <tr>
                <td colspan="2">
                    Chave PIX: <br>
                    @if(isset($dados['dados_colaborador']->BancoConta) && $dados['dados_colaborador']->BancoConta->pix)
                        <strong>{{ $dados['dados_colaborador']->BancoConta->chavepix ?? 'Não Informado'}} -
                            ({{ $dados['dados_colaborador']->BancoConta->tipochavepix ?? 'Não Informado'}})</strong>
                    @else
                        <strong>Não possui chave cadastrada</strong>
                    @endif
                </td>
                <td colspan="2">
                    Banco: <br>
                    <strong>{{ $dados['dados_colaborador']->BancoConta->banco ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    Agência: <br>
                    <strong>{{ $dados['dados_colaborador']->BancoConta->agencia ?? 'Não Informado'}}</strong>
                </td>
                <td>
                    Conta: <br>
                    <strong>{{ $dados['dados_colaborador']->BancoConta->agencia ?? 'Não Informado'}}</strong>
                </td>
            </tr>

        </table>



        <div class="f12" style="line-height: 16pt;text-align: center; font-size: 9pt !important; ">
            <br>
            <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%; border:none; border-top: 1px solid #333">
            {{$dados['dados_colaborador']->Curriculo->nome}}
        </div>
        <div style="position:fixed; bottom: 35px">
            @include('layouts.rodapePdfFilialJob')
        </div>
    </div>
@stop

@push('style')
    <style>
        .table_ficha {
            border-collapse: collapse;
            width: 98.6%;
            margin-bottom: 20px;
            margin-left: -10px;
        }

        .table_ficha, .table_ficha th, .table_ficha td {
            border: 1px solid black;
            padding: 5px;
        }

        .table_ficha th {
            background-color: #f2f2f2;
        }

        .table_ficha img {
            height: 4cm;
            width: 3cm;
            float: right;
            /*margin-right: 50px;*/
        }
    </style>
@endpush
